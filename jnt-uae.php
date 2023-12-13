<?php
/*
Plugin Name: J&T Express UAE
Description: WooCommerce integration for J&T Express UAE Shipping
Author: Liam, AmirhpCom
Author URI: https://amirhp.com
Version: 3.0.0
Requires at least: 5.4
Tested up to: 6.4.1
Requires PHP: 7.1
WC requires at least: 7.0
WC tested up to: 8.3.0
License: GPLv2 or later
Text Domain: jnt
Domain Path: /languages
License URI: http://www.gnu.org/licenses/gpl-2.0.html/*
 * @Last modified by: amirhp-com <its@amirhp.com>
 * @Last modified time: 2023/11/21 03:31:02
*/

defined("ABSPATH") or die("<h2>Unauthorized Access!</h2><hr><small>BlackSwan | J&T Express UAE :: Developed by AmirhpCom (<a href='https://amirhp.com/'>https://amirhp.com/</a>)</small>");
define("JNT_VERSION", "3.0.0");
define("JNT_PLUGIN_DIR", plugin_dir_path(__FILE__));

function activate_jnt() { require_once JNT_PLUGIN_DIR . "includes/class-jnt-activator.php"; Jnt_Activator::activator(); }
register_activation_hook(__FILE__,  "activate_jnt");
function deactivate_jnt() { require_once JNT_PLUGIN_DIR . "includes/class-jnt-deactivate.php"; Jnt_Deactivator::deactivate(); }
register_deactivation_hook(__FILE__, "deactivate_jnt");
require JNT_PLUGIN_DIR . "includes/class-jnt.php";
$plugin = Jnt::init();
$plugin->InitPlugin();

/**
 * get j&t option
 * @param  string $option
 * @param  string $default
 * @return string $option value
 */
function jnt_option($option="enabled", $default=""){
    $setting = get_option("woocommerce_jnt_settings");
    if (isset($setting[$option])) { return $setting[$option]; }
    return $default;
}