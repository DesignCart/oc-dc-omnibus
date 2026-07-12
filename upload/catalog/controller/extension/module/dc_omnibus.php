<?php
class ControllerExtensionModuleDcOmnibus extends Controller {
	public function data() {
		$this->response->addHeader('Content-Type: application/json; charset=utf-8');

		require_once DIR_SYSTEM . 'library/dc_omnibus/cache.php';

		$json = array(
			'success' => false,
		);

		if (!$this->config->get('module_dc_omnibus_status')) {
			$json['error'] = 'disabled';
			$this->response->setOutput(json_encode($json));
			return;
		}

		$product_id = isset($this->request->get['product_id']) ? (int)$this->request->get['product_id'] : 0;

		if ($product_id <= 0) {
			$json['error'] = 'product_id';
			$this->response->setOutput(json_encode($json));
			return;
		}

		$currency_code = isset($this->session->data['currency']) ? $this->session->data['currency'] : $this->config->get('config_currency');
		$customer_group_id = (int)$this->config->get('config_customer_group_id');

		if ($this->customer->isLogged()) {
			$customer_group_id = (int)$this->customer->getGroupId();
		}

		$lowest = DcOmnibusCache::getLowest($product_id, $currency_code, $customer_group_id);

		$json['success'] = true;
		$json['lowest'] = $lowest;
		$json['lowest_formatted'] = $lowest !== null ? $this->currency->format($lowest, $currency_code) : '';
		$json['history'] = array_reverse(DcOmnibusCache::purgeOld(DcOmnibusCache::read($product_id)));

		$this->response->setOutput(json_encode($json));
	}
}
