<?php

/**
 * Nginx FastCGI Cache Purger or The Nginx Helper Helper
 *
 * @package         LearningCurve\NginxFastCGICachePurger
 * @since           1.0.0
 * @author          Jeff Cleverley
 * @license         GNU-2.0+
 *
 * @wordpress-plugin
 * Plugin Name:     Nginx FastCGI Cache Purger
 * Description:     Plugin to initiate the FastCGI Cache Purge on a multiuser Nginx LEMP stack
 * Version:         1.0.0
 * Author:          Jeff Cleverley
 * Author URI:      https://github.com/JeffCleverley/NginxFastCGICachePurger
 * License:         GPL-2.0+
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:     nginx-fastcgi-cache-purger
 *
 * Plugin was developed as a collabporation between GridPane and RunCloud 
 * to solve one of Nginx's biggest problems. Great thanks to Patrick and Jebat!
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Hello, Hello, Hello, what\'s going on here then?' );
}

/**
 * Set Constants
 *
 */
define( 'NFCP_VERSION', '1.0.0' );
define( 'NFCP_TEXT_DOMAIN', 'nginx-fastcgi-cache-purger' );
define( 'NFCP_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'NFCP_DIR_PATH', plugin_dir_path( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_nginx_fastcgi_cache_purger() {
	require_once NFCP_DIR_PATH . 'src/class-nfcp-activator.php';
	Nginx_FastCGI_Cache_Purger_Plugin_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_nginx_fastcgi_cache_purger() {
	require_once NFCP_DIR_PATH . 'src/class-gpcp-deactivator.php';
	Nginx_FastCGI_Cache_Purger_Plugin_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_nginx_fastcgi_cache_purger' );
register_deactivation_hook( __FILE__, 'deactivate_nginx_fastcgi_cache_purger' );

/**
 * Autoload the plugin's files.
 *
 * @since 1.0
 *
 * @return void
 */
function autoload_files() {
	$files = array(
		'class-nfcp.php',
	);
	foreach ( $files as $file ) {
		require NFCP_DIR_PATH . '/src/' . $file;
	}
}

/**
 * Launch the plugin.
 *
 * @since 1.0
 *
 * @return void
 */
function launch() {
	autoload_files();
}
launch();
