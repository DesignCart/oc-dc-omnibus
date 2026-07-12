<?php
class DcOmnibusConfig {
	public static function defaults() {
		return array(
			'module_dc_omnibus_status'              => 0,
			'module_dc_omnibus_name'              => 'DC Omnibus',
			'module_dc_omnibus_panel_bg'          => '#f4f7f9',
			'module_dc_omnibus_label_color'       => '#64748b',
			'module_dc_omnibus_label_size'        => 14,
			'module_dc_omnibus_label_bold'        => 0,
			'module_dc_omnibus_label_uppercase'   => 0,
			'module_dc_omnibus_price_color'       => '#0f172a',
			'module_dc_omnibus_price_size'        => 15,
			'module_dc_omnibus_price_bold'        => 1,
			'module_dc_omnibus_price_uppercase'   => 0,
			'module_dc_omnibus_special_selector'    => '.product-price-special',
			'module_dc_omnibus_display_mode'      => 'promotion_only',
			'module_dc_omnibus_insert_selector'   => '.product-rating',
			'module_dc_omnibus_insert_position'   => 'after',
			'module_dc_omnibus_label'             => array(),
		);
	}

	public static function defaultLabel($language_id) {
		$labels = array(
			1 => 'Najniższa cena z 30 dni',
			2 => 'Lowest price in 30 days',
		);

		return isset($labels[$language_id]) ? $labels[$language_id] : 'Najniższa cena z 30 dni';
	}
}
