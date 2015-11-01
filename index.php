<?
/*
Plugin Name: YoShop
Plugin URI: http://google.com
Description: Simple plugin for create internet-shop on your wordpress blog
Version: Номер версии плагина, например: 1.0
Author: Aleksandr
Author URI: http://google.com
*/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


 
function install() {  // install plugin
    global $wpdb;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
 
 // create table "products"
 $table = $wpdb->prefix . "plugin_yoshop_products";
  
 if($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {	
	$sql = "CREATE TABLE `" . $table . "` (
	  `product_id` int(9) NOT NULL AUTO_INCREMENT,
	  `product_ip_address` VARCHAR(15) NOT NULL,
      `product_post_id` int(9),
	  `product_date` date,
	  UNIQUE KEY `id` (product_id)
	) DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;";
	  dbDelta($sql);
 }
 
  // create table "orders"
 $table = $wpdb->prefix . "plugin_yoshop_orders";
  if($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {	
	$sql = "CREATE TABLE `" . $table . "` (
	  `order_id` int(9) NOT NULL AUTO_INCREMENT,
	  `order_ip_address` VARCHAR(15) NOT NULL,
      `order_post_id` int(9),
	  `order_date` date,
	  UNIQUE KEY `id` (order_id)
	) DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;";
	  dbDelta($sql);
 } 
}
register_activation_hook( __FILE__,'install');

function uninstall() { // uninstall plugin
 global $wpdb;
 $table = $wpdb->prefix . "plugin_yoshop_products";	
 $wpdb->query("DROP TABLE IF EXISTS $table");
 
 global $wpdb;
 $table = $wpdb->prefix . "plugin_yoshop_orders";	
 $wpdb->query("DROP TABLE IF EXISTS $table");
}
register_deactivation_hook( __FILE__,'uninstall');

function show_buy_button($content) {
	global $post;
	
	$goods = get_post_meta( $post->ID, 'product_cost',true );
	
	if (isset($goods['cost'])) {
	    $content .= 'Cost is ' . $goods['cost'] . ' $ <br>'; 
        $content .= "<b>BUY</b>";
	}
    return $content;  
}
add_action('the_content','show_buy_button');



function my_plugin_menu() {
add_menu_page('YoShop', 'YoShop', 'manage_options', 'YoShop', 'main_page' );
}
add_action('admin_menu', 'my_plugin_menu');

function main_page() { // admin page
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="wrap">';
	echo '<h2>Yo Shop</h2>';
	    
    // get list posts
    global $wpdb;
   
    $posts = get_posts();
    foreach ( $posts as $post ) {
       var_dump($post->post_name);
    }
     

	echo '</div>';
}







add_action( 'add_meta_boxes', 'dynamic_add_custom_box' );

/* Adds a box to the main column on the Post and Page edit screens */
function dynamic_add_custom_box() {
    add_meta_box(
        'yoshop_sectionid',
        __( 'YoShop - Product\'s cost', 'myplugin_textdomain' ),
        'dynamic_inner_custom_box',
        'post');
}

/* Prints the box content */
/* Prints the box content */
function dynamic_inner_custom_box() {
    global $post;
    // Use nonce for verification
    wp_nonce_field( plugin_basename( __FILE__ ), 'dynamicMeta_noncename' );
    ?>
       <div id="meta_inner">
		<?php
   
	   //get the saved meta as an arry
   
	   $product_cost = get_post_meta($post->ID,'product_cost',true);
	   	   
       echo '<p  style="border-bottom:1px solid #f1f1f1">  
          Cost: <input type="text"  name="product_cost[cost]" value="' . (int) $product_cost['cost']  . '" placeholder="Item Name..."  style="margin:0px 15px"/> $
          </p>';
	
}
/* Do something with the data entered */
add_action( 'save_post', 'dynamic_save_postdata' );

/* When the post is saved, saves our custom data */
function dynamic_save_postdata( $post_id ) {
    // verify if this is an auto save routine. 
    // If it is our form has not been submitted, so we dont want to do anything
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
        return;

    // verify this came from the our screen and with proper authorization,
    // because save_post can be triggered at other times
    if ( !isset( $_POST['dynamicMeta_noncename'] ) )
        return;

    if ( !wp_verify_nonce( $_POST['dynamicMeta_noncename'], plugin_basename( __FILE__ ) ) )
        return;

    // OK, we're authenticated: we need to find and save the data
    $product_cost = $_POST['product_cost'];
	$product_cost['cost'] = (int) $product_cost['cost'];

    update_post_meta($post_id,'product_cost',$product_cost);
}


?>