<?php
/*
Plugin Name: EM Produkt liste
Description: 
Version: 0.0.2
GitHub Plugin URI: zeah/EM-product-list
*/

defined('ABSPATH') or die('Blank Space');

// constant for plugin location
define('EM_PRODUCT_LIST_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once 'inc/productlist-posttype.php';
require_once 'inc/productlist-shortcode.php';

function init_emproductlist() {
	Productlist_posttype::get_instance();
	Productlist_shortcode::get_instance();
}
add_action('plugins_loaded', 'init_emproductlist');