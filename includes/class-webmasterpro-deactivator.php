<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://cyberxdc.online
 * @since      1.0.0
 *
 * @package    Webmasterpro
 * @subpackage Webmasterpro/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Webmasterpro
 * @subpackage Webmasterpro/includes
 * @author     CyberXDC <contact@cyberxdc.online>
 */
class Webmasterpro_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		// TODO: Implement deactivate() method.
		if(get_option('cxdc_webmaster_pro_license_status')) {
			update_option('cxdc_webmaster_pro_license_status', 'inactive');
		}
	}

}
