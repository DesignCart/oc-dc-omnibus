<?php
require_once DIR_SYSTEM . 'library/dc_omnibus/cache.php';

class DcOmnibusPriceLogger {
	public static function logProductSpecials($product_id, $data, $config, $db = null) {
		if (!$config->get('module_dc_omnibus_status')) {
			return;
		}

		$product_id = (int)$product_id;

		if ($product_id <= 0) {
			return;
		}

		$currency = $config->get('config_currency');
		$specials = self::resolveSpecials($product_id, $data, $db);

		foreach ($specials as $special) {
			if (!isset($special['price'], $special['customer_group_id'])) {
				continue;
			}

			$price = self::normalizePrice($special['price']);

			if ($price <= 0) {
				continue;
			}

			DcOmnibusCache::append($product_id, $price, $currency, (int)$special['customer_group_id']);
		}
	}

	private static function resolveSpecials($product_id, $data, $db) {
		if (isset($data['product_special']) && is_array($data['product_special']) && $data['product_special']) {
			return $data['product_special'];
		}

		if (!$db) {
			return array();
		}

		$query = $db->query("SELECT customer_group_id, price FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "'");

		return $query->rows;
	}

	private static function normalizePrice($price) {
		$price = str_replace(array(' ', "\xc2\xa0"), '', (string)$price);
		$price = str_replace(',', '.', $price);

		return (float)$price;
	}

	public static function syncAllActiveSpecials($db, $config) {
		DcOmnibusCache::dir();

		$currency = $config->get('config_currency');

		$query = $db->query("
			SELECT ps.product_id, ps.customer_group_id, ps.price
			FROM " . DB_PREFIX . "product_special ps
			INNER JOIN " . DB_PREFIX . "product p ON p.product_id = ps.product_id
			WHERE p.status = '1'
			AND ps.price > 0
			AND (ps.date_start = '0000-00-00' OR ps.date_start <= NOW())
			AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())
			ORDER BY ps.product_id ASC
		");

		$products = array();
		$entries = 0;

		foreach ($query->rows as $row) {
			$product_id = (int)$row['product_id'];
			$price = self::normalizePrice($row['price']);

			if ($product_id <= 0 || $price <= 0) {
				continue;
			}

			DcOmnibusCache::append($product_id, $price, $currency, (int)$row['customer_group_id']);
			$products[$product_id] = true;
			$entries++;
		}

		return array(
			'products' => count($products),
			'entries'  => $entries,
		);
	}
}
