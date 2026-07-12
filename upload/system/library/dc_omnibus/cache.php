<?php
class DcOmnibusCache {
	const DAYS = 30;

	public static function dir() {
		$dir = rtrim(DIR_STORAGE, '/') . '/dc_omnibus_cache/';

		if (!is_dir($dir)) {
			@mkdir($dir, 0755, true);
		}

		return $dir;
	}

	public static function filePath($product_id) {
		return self::dir() . (int)$product_id . '.txt';
	}

	public static function read($product_id) {
		$file = self::filePath($product_id);

		if (!is_file($file)) {
			return array();
		}

		$raw = @file_get_contents($file);

		if ($raw === false || $raw === '') {
			return array();
		}

		$data = json_decode($raw, true);

		return is_array($data) ? $data : array();
	}

	public static function write($product_id, array $entries) {
		$entries = self::purgeOld($entries);
		$file = self::filePath($product_id);

		file_put_contents($file, json_encode(array_values($entries), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
	}

	public static function append($product_id, $price, $currency, $customer_group_id = 0) {
		$price = (float)$price;
		$currency = strtoupper(trim((string)$currency));
		$customer_group_id = (int)$customer_group_id;

		if ($price <= 0 || $currency === '') {
			return;
		}

		$entries = self::read($product_id);
		$now = date('Y-m-d H:i:s');
		$last = end($entries);

		if ($last
			&& isset($last['price'], $last['currency'], $last['group'])
			&& (float)$last['price'] === $price
			&& strtoupper($last['currency']) === $currency
			&& (int)$last['group'] === $customer_group_id
			&& isset($last['ts'])
			&& strtotime($last['ts']) > strtotime('-1 hour')
		) {
			return;
		}

		$entries[] = array(
			'ts'       => $now,
			'price'    => $price,
			'currency' => $currency,
			'group'    => $customer_group_id,
		);

		self::write($product_id, $entries);
	}

	public static function purgeOld(array $entries, $days = self::DAYS) {
		$cutoff = strtotime('-' . (int)$days . ' days');
		$out = array();

		foreach ($entries as $entry) {
			if (!isset($entry['ts'])) {
				continue;
			}

			if (strtotime($entry['ts']) >= $cutoff) {
				$out[] = $entry;
			}
		}

		return $out;
	}

	public static function getLowest($product_id, $currency, $customer_group_id, array $extra = array()) {
		$currency = strtoupper(trim((string)$currency));
		$customer_group_id = (int)$customer_group_id;
		$entries = self::purgeOld(self::read($product_id));

		foreach ($extra as $row) {
			if (!isset($row['price'], $row['currency'])) {
				continue;
			}

			$entries[] = array(
				'ts'       => isset($row['ts']) ? $row['ts'] : date('Y-m-d H:i:s'),
				'price'    => (float)$row['price'],
				'currency' => strtoupper($row['currency']),
				'group'    => isset($row['group']) ? (int)$row['group'] : $customer_group_id,
			);
		}

		$lowest = null;

		foreach ($entries as $entry) {
			if (!isset($entry['price'], $entry['currency'], $entry['group'])) {
				continue;
			}

			if (strtoupper($entry['currency']) !== $currency) {
				continue;
			}

			if ((int)$entry['group'] !== $customer_group_id) {
				continue;
			}

			$price = (float)$entry['price'];

			if ($price <= 0) {
				continue;
			}

			if ($lowest === null || $price < $lowest) {
				$lowest = $price;
			}
		}

		return $lowest;
	}

	public static function listFiles() {
		$files = glob(self::dir() . '*.txt');
		$list = array();

		if (!$files) {
			return $list;
		}

		foreach ($files as $file) {
			$product_id = (int)basename($file, '.txt');

			if ($product_id <= 0) {
				continue;
			}

			$entries = self::read($product_id);

			$list[] = array(
				'product_id' => $product_id,
				'entries'    => count($entries),
				'modified'   => date('Y-m-d H:i:s', filemtime($file)),
				'size'       => filesize($file),
			);
		}

		usort($list, function ($a, $b) {
			return $b['product_id'] - $a['product_id'];
		});

		return $list;
	}
}
