<?php

class Nginx_FastCGI_Cache_Purger_Plugin_Deactivator {

	/**
	 * Deletes the logs
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		$nginx_cache_purge_log = GPCP_DIR_PATH . '/purge-logs/nginx-cache-purge.log';

		unlink( $nginx_cache_purge_log );
	}

}
