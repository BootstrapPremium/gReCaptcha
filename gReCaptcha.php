<?php
/*
  Plugin Name: gReCaptcha
  Plugin URI: http://bootstrappremium.com
  Description: The new google recaptcha for verifications
  Author: Avinash Bhardwaj
  Author URI: http://bootstrappremium.com/bhardwaja
  Version: 1.0.0
  License: GPLv3
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
   exit;
}

if (!class_exists('bhardwaja_grecaptcha')) {
    /**
     * Main class for recaptcha rendering
     */
   final class bhardwaja_grecaptcha{

	  private static $instance;

	  public static function instance(){
		 if ( ! isset( self::$instance ) && ! ( self::$instance instanceof bhardwaja_grecaptcha ) ) {
			self::$instance = new bhardwaja_grecaptcha;
		 }
		 return self::$instance;
	  }

	  /**
	   * Construct and start the other plug-in functionality
	   */
	  public function __construct(){
		 $this->define_constants();
		 $this->load_dependencies();

		 register_activation_hook(__FILE__, array(&$this, 'activate'));
		 register_deactivation_hook(__FILE__, array(&$this, 'deactivate'));
		 register_uninstall_hook(__FILE__, 'bhardwaja_grecaptcha::uninstall');
		 add_action('init', array(&$this, 'i18n'));
		 add_action('plugins_loaded', array(&$this, 'start'));
	  }

	  /**
	   * Define constants needed across the plug-in.
	   */
	  private function define_constants(){
		 define('PB_BASENAME', plugin_basename(__FILE__));
		 define('PB_DIR', dirname(__FILE__));
		 define('PB_FOLDER', plugin_basename(dirname(__FILE__)));
		 define('PB_ABSPATH', trailingslashit(str_replace("\\", "/", WP_PLUGIN_DIR . '/' . plugin_basename(dirname(__FILE__)))));
		 define('PB_URLPATH', trailingslashit(WP_PLUGIN_URL . '/' . plugin_basename(dirname(__FILE__))));
		 define('PB_ADMINPATH', get_admin_url());
	  }

	  /**
	   * Loads PHP files that required by the plug-in
	   */
	  private function load_dependencies(){
		 // Admin Panel
		 if (is_admin()) {
                    require_once('admin/admin_panel.php');
		 }
		 // Front-End Site
		 if (!is_admin()) {

		 }
	  }

	  /**
	   * Called every time the plug-in is activated.
	   */
	  public function activate(){
	  }

	  /**
	   * Called when the plug-in is deactivated.
	   */
	  public function deactivate(){
	  }

	  /**
	   * Called when the plug-in is uninstalled
	   */
	  static function uninstall(){
	  }
	  /**
	   * Internationalization
	   */
	  public function i18n(){
		 load_plugin_textdomain('bhardwaja', false, basename(dirname(__FILE__)) . '/lang/');
	  }

	  /**
	   * Starts the plug-in main functionality
	   */
	  public function start(){
            require_once('GreCaptchaClass.php');
            $captcha = new GreCaptchaClass();
	  }
   }

}

function bhardwaja_grecaptcha_plugin_init() {
	return bhardwaja_grecaptcha::instance();
}

bhardwaja_grecaptcha_plugin_init();
