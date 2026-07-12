<?php
class ControllerExtensionModuleDcOmnibus extends Controller {
	private $error = array();

	public function index() {
		$this->ensureEvents();
		$this->load->language('extension/module/dc_omnibus');
		$this->document->setTitle(strip_tags($this->language->get('heading_title')));
		$this->load->model('setting/setting');
		$this->load->model('localisation/language');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$post = $this->normalizePost($this->request->post);
			$this->model_setting_setting->editSetting('module_dc_omnibus', $post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('extension/module/dc_omnibus', 'user_token=' . $this->session->data['user_token'], true));
		}

		$data = $this->formData();
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/dc_omnibus', $data));
	}

	public function install() {
		$this->load->model('setting/setting');
		require_once DIR_SYSTEM . 'library/dc_omnibus/config.php';
		require_once DIR_SYSTEM . 'library/dc_omnibus/cache.php';

		$defaults = DcOmnibusConfig::defaults();
		$this->load->model('localisation/language');

		foreach ($this->model_localisation_language->getLanguages() as $language) {
			$defaults['module_dc_omnibus_label'][$language['language_id']] = DcOmnibusConfig::defaultLabel($language['language_id']);
		}

		$this->model_setting_setting->editSetting('module_dc_omnibus', $defaults);
		DcOmnibusCache::dir();
		$this->registerEvents();
		$this->addPermissions();
	}

	public function uninstall() {
		$this->load->model('setting/setting');
		$this->load->model('setting/event');
		$this->model_setting_event->deleteEventByCode('dc_omnibus');
		$this->model_setting_setting->deleteSetting('module_dc_omnibus');
	}

	public function onProductAdd(&$route, &$args, &$output) {
		require_once DIR_SYSTEM . 'library/dc_omnibus/price_logger.php';

		if (!isset($args[0]) || !is_array($args[0])) {
			return;
		}

		DcOmnibusPriceLogger::logProductSpecials((int)$output, $args[0], $this->config, $this->db);
	}

	public function onProductEdit(&$route, &$args, &$output) {
		require_once DIR_SYSTEM . 'library/dc_omnibus/price_logger.php';

		if (!isset($args[0], $args[1]) || !is_array($args[1])) {
			return;
		}

		DcOmnibusPriceLogger::logProductSpecials((int)$args[0], $args[1], $this->config, $this->db);
	}

	public function sync() {
		$this->load->language('extension/module/dc_omnibus');

		if (!$this->user->hasPermission('modify', 'extension/module/dc_omnibus')) {
			$this->session->data['error_warning'] = $this->language->get('error_permission');
			$this->response->redirect($this->url->link('extension/module/dc_omnibus', 'user_token=' . $this->session->data['user_token'] . '#tab-files', true));
			return;
		}

		require_once DIR_SYSTEM . 'library/dc_omnibus/price_logger.php';

		$result = DcOmnibusPriceLogger::syncAllActiveSpecials($this->db, $this->config);

		$this->session->data['success'] = sprintf(
			$this->language->get('text_sync_success'),
			(int)$result['products'],
			(int)$result['entries']
		);

		$this->response->redirect($this->url->link('extension/module/dc_omnibus', 'user_token=' . $this->session->data['user_token'] . '#tab-files', true));
	}

	private function registerEvents() {
		$this->load->model('setting/event');
		$this->model_setting_event->deleteEventByCode('dc_omnibus');
		$this->model_setting_event->addEvent('dc_omnibus', 'admin/model/catalog/product/addProduct/after', 'extension/module/dc_omnibus/onProductAdd', 1, 0);
		$this->model_setting_event->addEvent('dc_omnibus', 'admin/model/catalog/product/editProduct/after', 'extension/module/dc_omnibus/onProductEdit', 1, 0);
	}

	private function ensureEvents() {
		$this->load->model('setting/event');
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "event` WHERE `code` = 'dc_omnibus'");

		if (!(int)$query->row['total']) {
			$this->registerEvents();
		}
	}

	private function normalizePost(array $post) {
		$checkboxes = array(
			'module_dc_omnibus_label_bold',
			'module_dc_omnibus_label_uppercase',
			'module_dc_omnibus_price_bold',
			'module_dc_omnibus_price_uppercase',
		);

		foreach ($checkboxes as $key) {
			if (!isset($post[$key])) {
				$post[$key] = 0;
			}
		}

		if (!isset($post['module_dc_omnibus_label']) || !is_array($post['module_dc_omnibus_label'])) {
			$post['module_dc_omnibus_label'] = array();
		}

		return $post;
	}

	private function formData() {
		require_once DIR_SYSTEM . 'library/dc_omnibus/config.php';
		require_once DIR_SYSTEM . 'library/dc_omnibus/cache.php';

		$defaults = DcOmnibusConfig::defaults();
		$keys = array_keys($defaults);
		$keys[] = 'module_dc_omnibus_label';

		$data = array();

		foreach ($keys as $key) {
			if (isset($this->request->post[$key])) {
				$data[$key] = $this->request->post[$key];
			} elseif ($this->config->get($key) !== null) {
				$data[$key] = $this->config->get($key);
			} elseif (isset($defaults[$key])) {
				$data[$key] = $defaults[$key];
			} else {
				$data[$key] = '';
			}
		}

		if (!is_array($data['module_dc_omnibus_label'])) {
			$data['module_dc_omnibus_label'] = array();
		}

		$data['languages'] = $this->model_localisation_language->getLanguages();
		$data['cache_files'] = DcOmnibusCache::listFiles();

		foreach ($data['cache_files'] as $key => $file) {
			$data['cache_files'][$key]['history_href'] = $this->url->link(
				'extension/module/dc_omnibus',
				'user_token=' . $this->session->data['user_token'] . '&history_product_id=' . (int)$file['product_id'],
				true
			) . '#tab-history';
		}

		$history_product_id = 0;

		if (isset($this->request->get['history_product_id'])) {
			$history_product_id = (int)$this->request->get['history_product_id'];
		}

		$data['history_product_id'] = $history_product_id;
		$data['history_entries'] = $history_product_id ? DcOmnibusCache::read($history_product_id) : array();

		$data['action'] = $this->url->link('extension/module/dc_omnibus', 'user_token=' . $this->session->data['user_token'], true);
		$data['sync_url'] = $this->url->link('extension/module/dc_omnibus/sync', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
		$data['user_token'] = $this->session->data['user_token'];

		$data['breadcrumbs'] = array(
			array('text' => $this->language->get('text_home'), 'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)),
			array('text' => $this->language->get('text_extension'), 'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)),
			array('text' => strip_tags($this->language->get('heading_title')), 'href' => $this->url->link('extension/module/dc_omnibus', 'user_token=' . $this->session->data['user_token'], true)),
		);

		$lang_keys = array(
			'heading_title', 'text_edit', 'text_success', 'text_enabled', 'text_disabled',
			'entry_status', 'entry_name', 'entry_panel_bg', 'entry_label_color', 'entry_label_size',
			'entry_label_bold', 'entry_label_uppercase', 'entry_price_color', 'entry_price_size',
			'entry_price_bold', 'entry_price_uppercase', 'entry_special_selector', 'entry_display_mode',
			'entry_insert_selector', 'entry_insert_position', 'entry_label_text', 'help_special_selector',
			'help_insert_selector', 'help_display_mode', 'text_display_always', 'text_display_promotion',
			'text_position_before', 'text_position_after', 'tab_general', 'tab_appearance', 'tab_frontend',
			'tab_languages', 'tab_files', 'tab_history', 'text_section_general', 'text_section_appearance',
			'text_section_frontend', 'text_section_languages', 'text_section_files', 'text_section_history',
			'text_no_files', 'text_no_history', 'text_product_id', 'text_entries', 'text_modified', 'text_size',
			'column_date', 'column_price', 'column_currency', 'column_group', 'button_save', 'button_cancel',
			'button_sync_promotions', 'help_sync_promotions', 'text_sync_success', 'text_sync_confirm',
		);

		foreach ($lang_keys as $key) {
			$data[$key] = $this->language->get($key);
		}

		$data['success'] = '';
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		}

		$data['error_warning'] = '';
		if (isset($this->session->data['error_warning'])) {
			$data['error_warning'] = $this->session->data['error_warning'];
			unset($this->session->data['error_warning']);
		} elseif (!empty($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		}

		$this->document->addStyle('view/stylesheet/dc_omnibus/dc-interface.css');
		$this->document->addStyle('view/stylesheet/dc_omnibus/admin.css');
		$this->document->addScript('view/javascript/dc_omnibus/dc-colorpicker.js');
		$this->document->addScript('view/javascript/dc_omnibus/dc-interface.js');
		$this->document->addScript('view/javascript/dc_omnibus/admin.js');

		return $data;
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/dc_omnibus')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	private function addPermissions() {
		$this->load->model('user/user_group');
		$id = $this->user->getGroupId();
		$routes = array('extension/module/dc_omnibus');

		foreach ($routes as $route) {
			$this->model_user_user_group->addPermission($id, 'access', $route);
			$this->model_user_user_group->addPermission($id, 'modify', $route);
		}
	}
}
