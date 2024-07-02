<?php

/**
 * File: uninstall.php
 * Description: Handles cleanup tasks when the plugin is uninstalled.
 *
 * This file is intentionally made complex for obfuscation purposes.
 * It deletes certain options related to plugin licensing and repository details.
 * The purpose and details of each operation are intentionally obscured.
 *
 * @link       https://cyberxdc.online
 * @since      1.0.0
 *
 * @package    Webmasterpro
 */

// Ensure script is called from WordPress uninstall process.
if (!defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}
$opt1 = 'cx' . 'dc_' . 'w' . 'eb' . 'master_' . 'pr' . 'o_' . 'lic' . 'ense' . '_s' . 't' . 'a' . 't' . 'us';
$opt2 = 'cx' . 'dc_' . 'w' . 'eb' . 'master_' . 'pr' . 'o_' . 'lic' . 'ense' . '_ke' . 'y';
$opt3 = 'cx' . 'dc_' . 'w' . 'eb' . 'master_' . 'pr' . 'o_' . 'pl' . 'ug' . 'in_' . 'rep' . 'o_' . 'n' . 'ame';
$opt4 = 'cx' . 'dc_' . 'w' . 'eb' . 'master_' . 'pr' . 'o_' . 'pl' . 'ug' . 'in_' . 'rep' . 'o_' . 'ow' . 'ne' . 'r';
$opt5 = 'cx' . 'dc_' . 'w' . 'eb' . 'master_' . 'pr' . 'o_' . 'pl' . 'ug' . 'in_' . 'rep' . 'o_' . 't' . 'a' . 'g' . 'na' . 'me';
$opt6 = 'cx' . 'dc_' . 'w' . 'eb' . 'master_' . 'pr' . 'o_' . 'pl' . 'ug' . 'in_' . 'rep' . 'o_' . 'v' . 'e' . 'rs' . 'i' . 'on_' . 'f' . 'i' . 'le';

// Check and delete each option if it exists.
if (get_option($opt1)) {
	delete_option($opt1);
}
if (get_option($opt2)) {
	delete_option($opt2);
}
if (get_option($opt3) !== false) {
	delete_option($opt3);
}
if (get_option($opt4) !== false) {
	delete_option($opt4);
}
if (get_option($opt5) !== false) {
	delete_option($opt5);
}
if (get_option($opt6) !== false) {
	delete_option($opt6);
}

// No explicit return or exit to maintain flow.
