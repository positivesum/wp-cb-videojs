<?php
/*
Plugin Name: WPCB Video JS module 
Plugin URI: http://www.homewood.hstd.org/
Description: WP plugin to enable embeding Video with CB
Version: 0.1
Author: Steven So
Author URI: http://www.odesk.com/users/Web-App-Magento-Joomla-IPhone-Drupal-PHP-MySQL-JQuery-SEO_~~9ed7fd44bbeb4462?sid=49002&tot=1&pos=0
*/
if ( !class_exists( 'wp_cb_videojs_module' ) ) {
	class wp_cb_videojs_module {
		/**
		 * Initializes plugin variables and sets up wordpress hooks/actions.
		 *
		 * @return void
		 */
		function __construct( ) {
			$this->pluginDir		= basename(dirname(__FILE__));
			$this->pluginPath		= WP_PLUGIN_DIR . '/' . $this->pluginDir;
			add_action('cfct-modules-loaded',  array(&$this, 'wp_cb_videojs_module_load'));	
		}

		function wp_cb_videojs_module_load() {
			require_once($this->pluginPath . "/videojs.php");				
		}			
		
	}
	$wp_cb_videojs_module = new wp_cb_videojs_module();	
}
