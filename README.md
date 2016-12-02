# WP REST API Shopp
WordPress plugin that provides REST endpoints for Shopp ecommerce plugin.

## Requirements
* Shopp ecommerce plugin (https://shopplugin.net/)
* WP REST API plugin (https://wordpress.org/plugins/rest-api/)

## Endpoints
* GET /wp-json/wp/v2/shopp_products - The shopp_product custom post type has been integrated with the WP REST API plugin.
* GET /wp-json/wp/v2/shopp_products/<id>
* GET /wp-json/shopp/v1/catalog - Pulls all products from the default Shopp catalog Smart Collection 'Catalog'
