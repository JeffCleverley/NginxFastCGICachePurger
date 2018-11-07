<?php

class Nginx_Cache_Purger_Admin {

	public $plugin_version;

	public $plugin_textdomain;

	public $plugin_dir_path;

	public $plugin_dir_url;

	public function __construct() {

		$this->plugin_version    = Nginx_Cache_Purger()->plugin_version;
		$this->plugin_textdomain = Nginx_Cache_Purger()->plugin_textdomain;
		$this->plugin_dir_path   = Nginx_Cache_Purger()->plugin_dir_path;
		$this->plugin_dir_url    = Nginx_Cache_Purger()->plugin_dir_url;
	}

	public function init() {

		add_action( 'admin_menu', array( $this, 'admin_menu' ), 150 );
		add_action( "wp_ajax_register_purge", array( $this, "register_purge" ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'purge_scripts' ) );

	}

	/**
	 *
	 * @since 1.0
	 */
	public function admin_menu() {

		global $nfcp;

		$nfcp = add_options_page(
			__( 'Nginx FastCGI Cache Purger', $this->plugin_textdomain ),
			__( 'Nginx FastCGI Cache Purger', $this->plugin_textdomain ),
			'manage_options',
			$this->plugin_textdomain,
			array(
				$this,
				'display_nginx_fastcgi_cache_purger_settings_screen'
			)
		);
	}

	/**
	 *
	 * @since 1.0
	 */
	public function display_nginx_fastcgi_cache_purger_settings_screen() {
		require __DIR__ . "/views/admin-screen.php";
	}

	public function purge_scripts( $hook ) {

		global $nfcp;

		if ( $hook != $nfcp ) {
			return;
		}

		wp_enqueue_script( 'purge-form.js', $this->plugin_dir_url . 'assets/js/purge-form.js', array( 'jquery' ) );

	}

	public function register_purge() {

		$site = get_site_url();
		$find = [ 'http://', 'https://' ];
		$replace = '';
		$host = str_replace( $find, $replace, $site);

		if ( is_ssl() ) {
			
			$purgeurl = $site . '/purgeall' ;
			$curl = curl_init( $purgeurl );
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PURGE" );
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_RESOLVE, array( $host . ":443:127.0.0.1" ));
			
			$response = curl_exec($curl);

			if ($response === false) {

				$response = curl_errno($curl) .': '. curl_error($curl);

			}
			
			curl_close($curl);

		} else {

			$curl = curl_init( "http://127.0.0.1/purgeall" );
			curl_setopt($curl, CURLOPT_HTTPHEADER, array('Host:' . $host ));
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PURGE" );
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
			
			$response = curl_exec($curl);
			
			curl_close($curl);
			
		}
		
		$nginx_fastcgi_cache_purge_log = NFCP_DIR_PATH . '/purge-logs/nginx-fastcgi-cache-purge.log';

		file_put_contents( $nginx_fastcgi_cache_purge_log, $response );

		exit;
	}
}