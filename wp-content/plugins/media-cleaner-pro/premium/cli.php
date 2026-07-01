<?php

class MeowPro_WPMC_CLI extends WP_CLI_Command {

	public function __construct() {
	}

	/**
	 * Memory management helper - forces garbage collection and logs memory usage
	 */
	private function manage_memory( $processed_count = 0, $debug = false ) {
		if ( function_exists( 'gc_collect_cycles' ) ) {
			gc_collect_cycles();
		}
		
		if ( $debug && $processed_count > 0 ) {
			$memory_usage = memory_get_usage( true );
			$memory_mb = round( $memory_usage / 1024 / 1024, 2 );
			$memory_peak = memory_get_peak_usage( true );
			$memory_peak_mb = round( $memory_peak / 1024 / 1024, 2 );
			WP_CLI::line( str_pad( '> Memory Status', 20 ) . ": Current: {$memory_mb}MB, Peak: {$memory_peak_mb}MB (after {$processed_count} items)" );
		}
	}

	/**
	 * Check if we're approaching memory limits
	 */
	private function check_memory_limit() {
		$memory_limit = ini_get( 'memory_limit' );
		if ( $memory_limit !== '-1' ) {
			$memory_limit_bytes = $this->parse_memory_limit( $memory_limit );
			$current_usage = memory_get_usage( true );
			$usage_percentage = ( $current_usage / $memory_limit_bytes ) * 100;
			
			if ( $usage_percentage > 80 ) {
				WP_CLI::warning( "Memory usage is at {$usage_percentage}% of limit ({$memory_limit}). Consider increasing memory_limit." );
			}
		}
	}

	/**
	 * Parse memory limit string to bytes
	 */
	private function parse_memory_limit( $memory_limit ) {
		$memory_limit = trim( $memory_limit );
		$last_char = strtolower( substr( $memory_limit, -1 ) );
		$value = (int) substr( $memory_limit, 0, -1 );
		
		switch ( $last_char ) {
			case 'g':
				$value *= 1024;
			case 'm':
				$value *= 1024;
			case 'k':
				$value *= 1024;
		}
		
		return $value;
	}

	public function issues() {
		global $wpdb;
		$table_name = $wpdb->prefix . "mclean_scan";
		$items = $wpdb->get_results( "SELECT id, type, postId, path, size, ignored, deleted, issue
			FROM $table_name
			WHERE ignored = 0 AND deleted = 0
			ORDER BY path, time
			DESC", OBJECT );
		$issues_count = count( $items );
		if ( !$issues_count ) {
			WP_CLI::line( "No issues found." );
		}
		else {
			WP_CLI::line( "Found {$issues_count} issues:" );
			foreach ( $items as $item ) {
				WP_CLI::line( "- [#{$item->id}] Media {$item->postId}: {$item->path} ({$item->issue})" );
			}
		}
	}

	public function trash() {
		global $wpdb;
		$table_name = $wpdb->prefix . "mclean_scan";
		$items = $wpdb->get_results( "SELECT id, type, postId, path, size, ignored, deleted, issue
			FROM $table_name
			WHERE ignored = 0 AND deleted = 1
			ORDER BY path, time
			DESC", OBJECT );
		$issues_count = count( $items );
		if ( !$issues_count ) {
			WP_CLI::line( "No deleted items found." );
		}
		else {
			WP_CLI::line( "Found {$issues_count} items:" );
			foreach ( $items as $item ) {
				WP_CLI::line( "- [#{$item->id}] Media {$item->postId}: {$item->path} ({$item->issue})" );
			}
		}
	}

	public function delete( $args ) {
		if ( empty( $args ) ) {
			global $wpdb;
			$table_name = $wpdb->prefix . "mclean_scan";
			$items = $wpdb->get_results( "SELECT id, type, postId, path, size, ignored, deleted, issue
				FROM $table_name
				WHERE ignored = 0 AND deleted = 0
				ORDER BY path, time
				DESC", OBJECT );
			$issues_count = count( $items );
			if ( !$issues_count ) {
				WP_CLI::line( "No issues found." );
			}
			else {
				global $wpmc;
				foreach ( $items as $item ) {
					if ( $wpmc->delete( $item->id ) ) {
						WP_CLI::line( "- Deleted Media {$item->postId} ({$item->path}): {$item->issue}" );
					}
					else {
						WP_CLI::line( "- Could not delete Media {$item->postId} ({$item->path}): {$item->issue}" );
					}
				}
			}
		}
		else {
			global $wpmc;
			foreach ( $args as $itemId ) { 
				if ( $wpmc->delete( (int)$itemId ) ) {
					WP_CLI::line( "- Deleted Media $itemId" );
				}
				else {
					WP_CLI::line( "- Could not delete Media $itemId" );
				}
			}
		}
	}

	public function recover( $args ) {
		if ( empty( $args ) ) {
			global $wpdb;
			$table_name = $wpdb->prefix . "mclean_scan";
			$items = $wpdb->get_results( "SELECT id, type, postId, path, size, ignored, deleted, issue
				FROM $table_name
				WHERE ignored = 0 AND deleted = 1
				ORDER BY path, time
				DESC", OBJECT );
			$deleted_count = count( $items );
			if ( !$deleted_count ) {
				WP_CLI::line( "No delete items found." );
			}
			else {
				global $wpmc;
				foreach ( $items as $item ) {
					if ( $wpmc->recover( $item->id ) ) {
						WP_CLI::line( "- Recovered Media {$item->postId} ({$item->path}): {$item->issue}" );
					}
					else {
						WP_CLI::line( "- Could not recover Media {$item->postId} ({$item->path}): {$item->issue}" );
					}
				}
			}
		}
		else {
			global $wpmc;
			foreach ( $args as $itemId ) { 
				if ( $wpmc->recover( (int)$itemId ) ) {
					WP_CLI::line( "- Recovered Media $itemId" );
				}
				else {
					WP_CLI::line( "- Could not recover Media $itemId" );
				}
			}
		}
	}

	public function empty() {
		global $wpdb;
		$table_name = $wpdb->prefix . "mclean_scan";
		$items = $wpdb->get_results( "SELECT id, type, postId, path, size, ignored, deleted, issue
			FROM $table_name
			WHERE ignored = 0 AND deleted = 1
			ORDER BY path, time
			DESC", OBJECT );
		$issues_count = count( $items );
		if ( !$issues_count ) {
			WP_CLI::line( "No issues found." );
		}
		else {
			global $wpmc;
			foreach ( $items as $item ) {
				if ( $wpmc->delete( $item->id ) )
					WP_CLI::line( "- Trashed Media {$item->postId} ({$item->path}): {$item->issue}" );
				else
				WP_CLI::line( "- Could not trash Media {$item->postId} ({$item->path}): {$item->issue}" );
			}
		}
	}

	public function scan( $args ) {
		global $wpmc;
		$debug = false;

		if ( $args && in_array( 'debug', $args ) ) {
			$debug = true;
			WP_CLI::line( ":: Debug mode enabled ::" );
		}
		$options = get_option( 'wpmc_options' );

		WP_CLI::line( "Verifying Database Structure..." );
		wpmc_check_database();

		$method = $options['method'] === 'media' ? 'Media Library' : 'Filesystem';
		$check_library = (bool)$options['media_library'] ? 'yes' : 'no';

		$posts_buffer = $options[ 'posts_buffer' ] ?? 1000;

		$check_content = 'no';
		if ( $method === 'Media Library' ) {
			$check_content = (bool)$options['content'] ? 'yes' : 'no';
		}
		else if ( $method === 'Filesystem' ) {
			$check_content = (bool)$options['filesystem_content'] ? 'yes' : 'no';
		}

		// $orignal_check_live_content = (bool)$options['live_content'];
		// $check_live_content = $orignal_check_live_content ? 'yes' : 'no';

		if ( $args && in_array( 'filesystem', $args ) ) {
			$method = 'Filesystem';
		}
		if ( $args && in_array( 'media', $args ) ) {
			$method = 'Media Library';
		}
		if ( $method === 'Filesystem' && $args && in_array( 'check-media', $args ) ) {
			$check_library = 'yes';
		}
		if ( $args && in_array( 'uncheck-media', $args ) ) {
			$check_library = 'no';
		}
		if ( $args && in_array( 'check-content', $args ) ) {
			$check_content = 'yes';
		}
		if ( $args && in_array( 'uncheck-content', $args ) ) {
			$check_content = 'no';
		}
		// if( $args && in_array( 'check-live', $args ) ) {
		// 	$check_live_content = 'yes';
		// 	update_option( 'wpmc_options', [ ...$options, 'live_content' => true ] );
		// }

		WP_CLI::line( str_pad( "* Method", 20 ) . ": " . $method );
		if ( $method === 'Filesystem' ) {
			WP_CLI::line( str_pad( "* Check Library", 20 ) . ": " . $check_library );
		}
		WP_CLI::line( str_pad( "* Check Content", 20 ) . ": " . $check_content );
		//WP_CLI::line( str_pad( "* Check Live", 20 ) . ": " . $check_live_content );
		
		// Display memory information
		$memory_limit = ini_get( 'memory_limit' );
		$current_memory = round( memory_get_usage( true ) / 1024 / 1024, 2 );
		WP_CLI::line( str_pad( "* Memory Limit", 20 ) . ": " . $memory_limit );
		WP_CLI::line( str_pad( "* Current Memory", 20 ) . ": " . $current_memory . "MB" );
		WP_CLI::line();

		
		$wpmc->catch_timeout = false;
		WP_CLI::line( str_pad( '> Reset Issues', 20 ) . ": Resetting issues and references..." );
		$wpmc->reset_issues();
		$wpmc->reset_references();

		// Check Content
		if ( $check_content === 'yes' ) {
			$total_posts = count( $wpmc->engine->get_posts_to_check( -1, -1 ) );
			$progress = \WP_CLI\Utils\make_progress_bar( str_pad( "> Analyze Content ( $posts_buffer posts/batch )", 20 ) . ":", $total_posts );
			$finished = false;
			$limit = 0;
			$limitSize = $posts_buffer;
			$total_processed_posts = 0;
			while ( !$finished ) {
				$posts_to_process = $wpmc->engine->get_posts_to_check( $limit, $limitSize );
				if ( $debug ) {
					WP_CLI::line( str_pad( '> Analyze Content', 20 ) . ": Processing " . count( $posts_to_process ) . " posts..." );
				}
				$posts_processed = count( $posts_to_process );
				$finished = $wpmc->engine->extractRefsFromContent( $limit, $limitSize );

				for ( $c = 0; $c < $posts_processed; $c++ )
					$progress->tick();
				$limit += $limitSize;
				$total_processed_posts += $posts_processed;
				
				// Memory management every 5000 posts
				if ( $total_processed_posts % 5000 === 0 ) {
					$this->manage_memory( $total_processed_posts, $debug );
					$this->check_memory_limit();
				}
			}
			$progress->finish();
		}

		// Check Library
		if ( $method === 'Filesystem' && $check_library === 'yes' ) {
			$total_media = count( $wpmc->engine->get_media_entries( -1, -1 ) );
			$progress = \WP_CLI\Utils\make_progress_bar( str_pad( '> Analyze Library', 20 ) . ":", $total_media );
			$finished = false;
			$limit = 0;
			$limitSize = 1000;
			while ( !$finished ) {
				$medias_to_process = $wpmc->engine->get_media_entries( $limit, $limitSize );
				$medias_processed = count( $medias_to_process );
				$finished = $wpmc->engine->extractRefsFromLibrary( $limit, $limitSize );
				for ( $c = 0; $c < $medias_processed; $c++ )
					$progress->tick();
				$limit += $limitSize;
			}
			$progress->finish();
		}

		// Method: Filesystem
		if ( $method === 'Filesystem' ) {
			WP_CLI::line( str_pad( '> List Files', 20 ) . ": Scanning directories..." );
			
			// First pass: Count total files for progress bar
			$total_files = 0;
			$dirs_to_count = array( '.' );
			while ( count( $dirs_to_count ) > 0 ) {
				$dir = array_pop( $dirs_to_count );
				$new_files = $wpmc->engine->get_files( $dir );
				foreach ( $new_files as $file ) {
					if ( $file['type'] === 'dir' ) {
						array_push( $dirs_to_count, $file['path'] );
					} else {
						$total_files++;
					}
				}
			}
			WP_CLI::line( str_pad( '> List Files', 20 ) . ": Found " . $total_files . " files" );

			// Second pass: Process files directly without storing all in memory
			$progress = \WP_CLI\Utils\make_progress_bar( str_pad( '> Check Usage', 20 ) . ":", $total_files );
			do_action( 'wpmc_check_file_init' );
			
			$dirs = array( '.' );
			$processed_files = 0;
			while ( count( $dirs ) > 0 ) {
				$dir = array_pop( $dirs );
				$new_files = $wpmc->engine->get_files( $dir );
				
				foreach ( $new_files as $file ) {
					if ( $debug ) {
						WP_CLI::line( str_pad( '> List Files', 20 ) . ": Processing " . $file['path'] );
					}

					if ( $file['type'] === 'dir' ) {
						array_push( $dirs, $file['path'] );
					} else {
						$wpmc->engine->check_file( $file['path'] );
						$progress->tick();
						$processed_files++;
						
						// Memory management: Force garbage collection and check memory every 1000 files
						if ( $processed_files % 1000 === 0 ) {
							$this->manage_memory( $processed_files, $debug );
							$this->check_memory_limit();
						}
					}
				}
			}
			$progress->finish();
		}

		else {
			// Method: Media Library
			$total_media = count( $wpmc->engine->get_media_entries( -1, -1 ) );
			$progress = \WP_CLI\Utils\make_progress_bar( str_pad( '> List Medias', 20 ) . ":", $total_media );
			$finished = false;
			$limit = 0;
			$limitSize = 1000;
			$mediaIds = array();
			while ( !$finished ) {
				$newMediaIds = $wpmc->engine->get_media_entries( $limit, $limitSize );
				$newMediaIdsCount = count( $newMediaIds );
				for ( $c = 0; $c < $newMediaIdsCount; $c++ ) {
					array_push( $mediaIds, $newMediaIds[$c] );
					$progress->tick();
				}
				$limit += $limitSize;
				$finished = $newMediaIdsCount < $limitSize;
			}
			$progress->finish();

			// Final Check
			$mediaCount = count( $mediaIds );
			$progress = \WP_CLI\Utils\make_progress_bar( str_pad( '> Check Usage', 20 ) . ":", $mediaCount );
			$processed_media = 0;
			foreach ( $mediaIds as $mediaId ) {
				$wpmc->check_media( $mediaId );
				$progress->tick();
				$processed_media++;
				
				// Memory management every 1000 media items
				if ( $processed_media % 1000 === 0 ) {
					$this->manage_memory( $processed_media, $debug );
					$this->check_memory_limit();
				}
			}
			$progress->finish();
		}

		WP_CLI::line();
		$this->issues();

		// Reset Live Content
		//　update_option( 'wpmc_options', [ ...$options, 'live_content' => $orignal_check_live_content ] );

	}

}

?>
