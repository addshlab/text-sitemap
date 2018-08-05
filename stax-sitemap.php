<?php
/*
Plugin Name: Stax Sitemap
Plugin URI: 
Description:
Author:
Version: 0.1
Author URI: 
*/

new staxSitemap();

class staxSitemap {

	public function __construct() {

#		// Plugin Activation
#		if ( function_exists( 'register_activation_hook' ) ) {
#			register_activation_hook( __FILE__, array( $this, 'activationHook' ) );
#		}
#
#		// Plugin Uninstall
#		if ( function_exists( 'register_uninstall_hook' ) ) {
#			register_uninstall_hook( __FILE__, 'Obliterate::uninstallHook' );
		# 投稿の状態が変わった時にサイトマップの更新を行う
		# see https://wpdocs.osdn.jp/%E6%8A%95%E7%A8%BF%E3%82%B9%E3%83%86%E3%83%BC%E3%82%BF%E3%82%B9%E3%81%AE%E9%81%B7%E7%A7%BB
		add_action( 'transition_post_status', array( $this, 'generate_file') );
	}

	function post_query( ) {
		global $wpdb;
		$query = "
SELECT * FROM $wpdb->posts
WHERE post_status='publish';
		";
		$rows = $wpdb->get_results($query);
		foreach( $rows as $row ) {
			$permalink[] = get_permalink( $row -> ID ) . "\n";
		}
		return $permalink;
	}

	function generate_file () {
		file_put_contents( ABSPATH . 'sitemap.txt', $this->post_query() );					
	}
}
