<?php
/*
Plugin Name: Admin Goto
description: The Admin Goto plugin helps you to navigate through the Wordpress admin faster, inspired by Mac apps Admin_Goto and Spotlight.
Version: 1.0.0
Author: Fabrizio Meinero
Author URI: https://imfaber.me
License: GPL2
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//add_action( 'admin_bar_menu', 'all_toolbar_nodes', 999 );
//
//function all_toolbar_nodes( $wp_admin_bar ) {
//
//
//	$menu    = $GLOBALS['menu'];
//	$submenu = $GLOBALS['submenu'];
//
//	foreach ( $menu as $item ) {
//
//	}
//
//	$a = 1;
//
//	//  $toolbar_nodes = $wp_admin_bar->get_nodes();
//	//  if (!count($toolbar_nodes)) {
//	//    return;
//	//  }
//	//
//	//  $toolbar_nodes_js = [];
//	//  foreach ($toolbar_nodes as $node) {
//	//    if (strip_tags($node->title) && $node->title!=='#' && $node->href) {
//	//      $toolbar_nodes_js[] = [
//	//        'title' => strip_tags($node->title),
//	//        'href' => $node->href,
//	//      ];
//	//    }
//	//  }
//	//
//	//  $admin_goto = [
//	//    'toolbar_nodes' => $toolbar_nodes_js
//	//  ];
//	//
//	//  wp_enqueue_script( 'admin_goto_js', plugin_dir_url(__FILE__) . 'js/admin-goto.js', ['jquery-ui-autocomplete'] );
//	//  wp_localize_script( 'admin_goto_js', 'Admin_Goto', $admin_goto );
//	//  wp_enqueue_style( 'admin_goto_css', plugin_dir_url(__FILE__) . 'css/admin-goto.css',false,'1.0','all');
//}
//
//add_action( 'admin_init', 'admin_goto_add_dependencies' );
//
//function admin_goto_add_dependencies() {
//
//	$menu    = $GLOBALS['menu'];
//	$submenu = $GLOBALS['submenu'];
//
//	foreach ( $menu as $item ) {
//
//	}
//
//	$a = 1;
//}

/**
 * @class Admin_Goto
 */
class Admin_Goto {

	/**
	 * Static property to hold our singleton instance
	 */
	static $instance = FALSE;

	const USER_META_ADMIN_PAGES = '_admin_goto_pages';


	/**
	 * This is our constructor
	 *
	 * @return void
	 */
	private function __construct() {
		add_action( 'admin_init', [ $this, 'save_menu_items' ] );

		add_action( 'wp_enqueue_scripts', [ $this, 'wp_enqueue_scripts' ],
			1000 );
	}

	/**
	 * If an instance exists, this returns it.  If not, it creates one and
	 * retuns it.
	 *
	 * @return Admin_Goto
	 */
	public static function getInstance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Load Scripts.
	 */
	public function wp_enqueue_scripts() {
		$user_id = get_current_user_id();
		if ( $user_id && $admin_goto_menu_items = get_user_meta( $user_id,
				self::USER_META_ADMIN_PAGES, 1 )
		) {
			wp_enqueue_script( 'admin_goto_js',
				plugin_dir_url( __FILE__ ) . 'js/admin-goto.js',
				[ 'jquery-ui-autocomplete' ] );
			$admin_goto = [ 'toolbar_nodes' => $admin_goto_menu_items ];
			wp_localize_script( 'admin_goto_js', 'AdminGoto', $admin_goto );
			wp_enqueue_style( 'admin_goto_css',
				plugin_dir_url( __FILE__ ) . 'css/admin-goto.css', FALSE, '1.0',
				'all' );
		}
	}

	/**
	 * Save admin items in user meta.
	 *
	 * @return void
	 */
	public function save_menu_items() {
		$user_id = get_current_user_id();

		if ( ! $user_id || ! is_admin() ) {
			return;
		}

		if ( $menu_items = $this->get_menu_items() ) {
			update_user_meta( $user_id, self::USER_META_ADMIN_PAGES,
				$menu_items );
		}

	}

	/**
	 * Return menu items.
	 *
	 * @return array
	 */
	private function get_menu_items() {
		$menu    = $GLOBALS['menu'];
		$submenu = $GLOBALS['submenu'];

		$full_menu  = [];
		$menu_items = [];
		foreach ( $menu as $item ) {
			// Empty $item[0] is a separator.
			if ( $item[0] ) {
				$full_menu[ $item[2] ] = [
					'title' => $this->sanitize_item_title( $item[0] ),
					'href'  => get_admin_url() . $item[2],
				];
			}
		}

		foreach ( $submenu as $parent_key => $children ) {
			foreach ( $children as $child ) {
				$full_menu[] = [
					'title' => $full_menu[ $parent_key ]['title'] . ' > ' . $this->sanitize_item_title( $child[0] ),
					'href'  => get_admin_url() . $child[2],
				];
			}
		}

		foreach ( $full_menu as $menu_item ) {
			$menu_items[] = $menu_item;

		}
		return $menu_items;
	}

	/**
	 * Return the given text removing the HTML tags along with their contents.
	 *
	 * @param $text
	 * @param string $tags
	 * @param bool $invert
	 *
	 * @return mixed
	 */
	protected function sanitize_item_title(
		$text,
		$tags = '',
		$invert = FALSE
	) {

		preg_match_all( '/<(.+?)[\s]*\/?[\s]*>/si', trim( $tags ), $tags );
		$tags   = array_unique( $tags[1] );
		$result = $text;

		if ( is_array( $tags ) AND count( $tags ) > 0 ) {
			if ( $invert == FALSE ) {
				$result = preg_replace( '@<(?!(?:' . implode( '|',
						$tags ) . ')\b)(\w+)\b.*?>.*?</\1>@si', '', $text );
			} else {
				$result = preg_replace( '@<(' . implode( '|',
						$tags ) . ')\b.*?>.*?</\1>@si', '', $text );
			}
		} elseif ( $invert == FALSE ) {
			$result = preg_replace( '@<(\w+)\b.*?>.*?</\1>@si', '', $text );
		}

		return trim( strip_tags( $result ) );
	}

	/// end class
}

Admin_Goto::getInstance();
