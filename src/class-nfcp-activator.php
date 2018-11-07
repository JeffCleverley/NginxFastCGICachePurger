<?php

class Nginx_FastCGI_Cache_Purger_Plugin_Activator {

	/**
	 * Creates the logs
	 * 
	 * @since    1.0.0
	 */
	public static function activate() {

		$nginx_cache_purge_log = GPCP_DIR_PATH . '/purge-logs/nginx-fastcgi-cache-purge.log';

		fopen( $nginx_cache_purge_log, 'w+' );
	}

}
