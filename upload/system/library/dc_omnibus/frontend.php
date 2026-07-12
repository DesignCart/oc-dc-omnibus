<?php
require_once DIR_SYSTEM . 'library/dc_omnibus/cache.php';
require_once DIR_SYSTEM . 'library/dc_omnibus/config.php';

class DcOmnibusFrontend {
	public static function inject($registry, &$data, $product_id) {
		$config = $registry->get('config');

		if (!$config->get('module_dc_omnibus_status')) {
			return;
		}

		$document = $registry->get('document');
		$load = $registry->get('load');
		$language = $registry->get('language');
		$currency = $registry->get('currency');
		$tax = $registry->get('tax');
		$session = $registry->get('session');
		$customer = $registry->get('customer');

		$load->language('extension/module/dc_omnibus');

		$language_id = (int)$config->get('config_language_id');
		$labels = $config->get('module_dc_omnibus_label');

		if (!is_array($labels)) {
			$labels = array();
		}

		$label = isset($labels[$language_id]) && $labels[$language_id] !== ''
			? $labels[$language_id]
			: DcOmnibusConfig::defaultLabel($language_id);

		$customer_group_id = (int)$config->get('config_customer_group_id');

		if ($customer->isLogged()) {
			$customer_group_id = (int)$customer->getGroupId();
		}

		$currency_code = isset($session->data['currency']) ? $session->data['currency'] : $config->get('config_currency');

		$load->model('catalog/product');
		$product_info = $registry->get('model_catalog_product')->getProduct($product_id);

		$regular_raw = 0.0;
		$special_raw = 0.0;
		$has_special = false;

		if ($product_info) {
			$regular_raw = (float)$tax->calculate($product_info['price'], $product_info['tax_class_id'], $config->get('config_tax'));

			if (!is_null($product_info['special']) && (float)$product_info['special'] >= 0) {
				$special_raw = (float)$tax->calculate($product_info['special'], $product_info['tax_class_id'], $config->get('config_tax'));
				$has_special = $special_raw > 0 && $special_raw < $regular_raw;
			}
		}

		$lowest = DcOmnibusCache::getLowest($product_id, $currency_code, $customer_group_id);

		$document->addScript('catalog/view/javascript/dc_omnibus/dc_omnibus.js');
		$document->addStyle('catalog/view/theme/default/stylesheet/dc_omnibus.css');

		$data['dc_omnibus_config'] = array(
			'productId'         => (int)$product_id,
			'label'             => $label,
			'panelBg'           => $config->get('module_dc_omnibus_panel_bg') ?: '#f4f7f9',
			'labelColor'        => $config->get('module_dc_omnibus_label_color') ?: '#64748b',
			'labelSize'         => (int)$config->get('module_dc_omnibus_label_size') ?: 14,
			'labelBold'         => (bool)$config->get('module_dc_omnibus_label_bold'),
			'labelUppercase'    => (bool)$config->get('module_dc_omnibus_label_uppercase'),
			'priceColor'        => $config->get('module_dc_omnibus_price_color') ?: '#0f172a',
			'priceSize'         => (int)$config->get('module_dc_omnibus_price_size') ?: 15,
			'priceBold'         => (bool)$config->get('module_dc_omnibus_price_bold'),
			'priceUppercase'    => (bool)$config->get('module_dc_omnibus_price_uppercase'),
			'specialSelector'   => $config->get('module_dc_omnibus_special_selector') ?: '.product-price-special',
			'displayMode'       => $config->get('module_dc_omnibus_display_mode') ?: 'promotion_only',
			'insertSelector'    => $config->get('module_dc_omnibus_insert_selector') ?: '.product-rating',
			'insertPosition'    => $config->get('module_dc_omnibus_insert_position') ?: 'after',
			'currency'          => $currency_code,
			'customerGroupId'   => $customer_group_id,
			'hasSpecial'        => $has_special,
			'lowestPrice'       => $lowest,
			'lowestFormatted'   => $lowest !== null ? $currency->format($lowest, $currency_code) : '',
		);
	}
}
