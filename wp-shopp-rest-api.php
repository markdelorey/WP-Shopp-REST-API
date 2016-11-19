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
		
		// simple fields
		if( !empty( $data['fields'] ) ) { // user provided list of desired fields
			
			$fields	=	explode(',', $data['fields']);
			
		} else {
			$fields	=	array(
				'id',
				'name',
				'coverimage',
				'description',
				'freeshipping',
				'price',
				'saleprice',
				'sku',
				'slug',
				'stock',
				'summary',
				'tax-rate',
				'type',
				'url',
				'weight'
			);
		}
		
		foreach( $fields as $f ) {
			$product->{$f}	=	shopp( 'product', 'get'. $f );
		}
		
		// product specs
		$product->specs	=	array();
		if( shopp('product.has-specs') ) :  while( shopp( 'product', 'specs' ) ) :
			$spec	=	new stdClass();
			$spec->name	=	shopp( 'product', 'getspec', 'name' );
			$spec->content	=	shopp( 'product', 'getspec', 'content' );
		
			$product->specs[]	=	$spec;
		endwhile; endif;
		
		// product categories
		$product->categories	=	array();
		if( shopp('product.has-categories') ) :  while( shopp( 'product', 'categories' ) ) :
			$category	=	new stdClass();
			$category->name	=	shopp( 'product', 'category', array( 'return' => true ) );
			$category->id	=	shopp( 'product', 'category', array( 'show' => 'id', 'return' => true ) );
			$category->slug	=	shopp( 'product', 'category', array( 'show' => 'slug', 'return' => true ) );
		
			$product->categories[]	=	$category;
		endwhile; endif;
		
		// product images
		$product->images	=	array();
		if ( shopp( 'product.has-images' ) ) : while( shopp( 'product', 'images' ) ) :
			$image	=	new stdClass();
			$image->src	=	shopp( 'product', 'image', array( 'property' => 'src', 'return' => true ) );
			
			$product->images[]	=	$image;
		endwhile; endif;
		
		$product->fields	=	array(
			$data['fields']
		);
		

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
