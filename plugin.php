<?php 
/*
Plugin Name: WP REST API Shopp
Version: 0.1 alpha
Description: Adding Shopp endpoints on WP REST API
Author: Mark Delorey
Author URI: http://www.markdelorey.com
*/

/*
 *	Check if Shopp exists
 */
 
function _shopp_exists() { 
	return class_exists( 'Shopp' );
}

/**
 **
 **	shopp_product post type
 **
 **/

/*
 *	Make the shopp_product post type available through the v2 rest api
 */
add_filter( 'init', '_add_shopp_product_post_type_api_arguments', 11 );

function _add_shopp_product_post_type_api_arguments () {
	
	// TODO: add check for WP REST API plugin
	if( _shopp_exists() ) {
	
		global $wp_post_types;
	
		if ( isset( $wp_post_types['shopp_product'] ) ) {
			$wp_post_types['shopp_product']->show_in_rest = true;
			$wp_post_types['shopp_product']->rest_base = 'shopp_products';
			$wp_post_types['shopp_product']->rest_controller_class = 'WP_REST_Posts_Controller';
		}
		
	}
}

/*
 *	Add additional product context data to shopp_product post types
 */

add_filter( 'rest_prepare_shopp_product', 'add_shopp_product_metadata_to_rest_response', 10, 3 );

function add_shopp_product_metadata_to_rest_response ( $response, $post, $request ) {
	
	if( !_shopp_exists() ) return $post;
		
	$product	=	new stdClass();

	$p	=	shopp_product( $post->ID );
	
	if( $p ) ShoppProduct( $p );
		
		// simple fields
		$fields	=	array(
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
		

	$post->shopp_meta	=	$product;
	
	return $post;
	
}

/**
 **
 **	product catalog
 **
 **/
 
/**
 * Get the product catalog (smart category: Catalog Products)
 * @return array - List of product objects
 */

// TODO: Convert to WP_REST_Posts_Controller->get_items()

function wp_api_v2_shopp_get_catalog ( $request ) {
	
	if( !_shopp_exists() ) return new WP_Error( 'shopp_not_found', 'Shopp plugin is not found. Please make sure it is installed and activated.', array( 'status' => 501 ) );
	
	$catalog	=	array();
	
	shopp('storefront','catalog-products', array( 'load' => 'true', 'order' => 'oldest' ));

	if( shopp('collection.has-products') ) :
	
		$controller	=	new WP_REST_Posts_Controller( 'shopp_product' );
	
		while( shopp('collection.products') ) : // TODO: Load test this loop; default product limit is 1000
		
			$post	=	get_post( shopp('product', 'getid') );
			
			$catalog[]	=	$controller->prepare_item_for_response( $post, $request );

		endwhile;
		
	endif;

    return $catalog;
	
	//return $controller->get_items( $request );

}

add_action( 'rest_api_init', function () {
    register_rest_route( 'shopp/v1', '/catalog', array(
        'methods' => 'GET',
        'callback' => 'wp_api_v2_shopp_get_catalog',
    ) );
} );
