<?php 
/*
Plugin Name: WP Shopp REST API 
Version: 0.1 alpha
Description: Adding Shopp endpoints on WP REST API
Author: Mark Delorey
Author URI: http://www.invisiblehandmarketing.com
*/

/**
 * Get the product catalog (smart category: Catalog Products)
 * @return array - List of product objects
 */
function wp_api_v2_shopp_get_catalog () {
	$catalog	=	array();
	
	shopp('storefront','catalog-products', array( 'load' => 'true', 'older' => 'oldest' ));

	if( shopp('collection.has-products') ) : while( shopp('collection.products') ) :
	
		$p	=	new stdClass();
		$p->name	=	shopp('product.name', array( 'return' => true) );
		
		$catalog[]	=	$p;

	endwhile; endif;

    return $catalog;
}

/**
 * Get a product by id or slug
 * @return product - product details
 */
function wp_api_v2_shopp_get_product ( $data ) {
	$product	=	new stdClass();
	
	$p	=	shopp_product( $data['id'] );
	
	if( !$p ) {
		$p	=	shopp_product( $data['id'], 'slug' );
	}
	
	if( $p ) ShoppProduct( $p );

	$product->name	=	shopp('product.name', array( 'return' => true) );

    return $product;
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'shopp/v1', '/catalog', array(
        'methods' => 'GET',
        'callback' => 'wp_api_v2_shopp_get_catalog',
    ) );
    register_rest_route( 'shopp/v1', '/products/(?P<id>[a-zA-Z0-9(-]+)', array(
        'methods' => 'GET',
        'callback' => 'wp_api_v2_shopp_get_product',
    ) );
} );
