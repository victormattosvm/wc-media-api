<?php
/**
 * Plugin Name: WooCommerce Media API
 * Description: Media endpoint for WooCommerce API. Upload and list media files by WooCommerce REST API.
 * Author: woopos
 * Author URI: https://woopos.com
 * Version: 2.1
 * License: GPL2 or later
 */
if( !defined( 'ABSPATH' ) ) exit;

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
	
class WooCommerce_Media_API_By_WooPOS{
	
	public function __construct(){
		add_action( 'rest_api_init', array( $this, 'register_routes' ) , 15 );
	}
	
	public function register_routes(){
		global $wp_version;
		if ( version_compare( $wp_version, 4.4, '<' )) {
			return;
		}
		
		require_once( __DIR__ . '/class-woocommerce-media-api-controller.php' );
		require_once( __DIR__ . '/class-woocommerce-metadata-api-controller.php' );
		$api_classes = array(
			'WC_REST_WooCommerce_Media_API_By_WooPOS_Controller',
			'WC_REST_WooCommerce_Metadata_API_By_WooPOS_Controller'
		);
		foreach ( $api_classes as $api_class ) {
			$controller = new $api_class();
			$controller->register_routes();
		}
	}
}

new WooCommerce_Media_API_By_WooPOS();

function modify_orders_after_query($request) {
    $request['date_query'][0]['column'] = 'post_modified';
    return $request;
}

add_filter( "woocommerce_rest_orders_prepare_object_query", 'modify_orders_after_query' );

function modify_products_after_query($request) {
    $request['date_query'][0]['column'] = 'post_modified';
    return $request;
}

add_filter( "woocommerce_rest_products_prepare_object_query", 'modify_products_after_query' );

