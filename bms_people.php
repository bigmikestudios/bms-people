<?php
/**
 * @package BMS_People
 * @author Mike Lathrop
 * @version 0.0.1
 */
/*
Plugin Name: BMS People
Plugin URI: http://bigmikestudios.com
Depends: bms_smart_meta_box/bms_smart_meta_box.php
Description: Adds a 'People' post type and shortcodes
Version: 0.0.1
Author URI: http://bigmikestudios.com
*/

$cr = "\r\n";

// =============================================================================

//////////////////////////
//
// INCLUDES
//
//////////////////////////

wp_register_style('bms_people', plugins_url() .'/bms_people/bms_people.css');
wp_enqueue_style('bms_people');


// =============================================================================

//////////////////////////
//
// CUSTOM POST TYPES
//
//////////////////////////

    add_action( 'init', 'register_cpt_person' );
    function register_cpt_person() {
    $labels = array(
    'name' => _x( 'People', 'person' ),
    'singular_name' => _x( 'Person', 'person' ),
    'add_new' => _x( 'Add New', 'person' ),
    'add_new_item' => _x( 'Add New Person', 'person' ),
    'edit_item' => _x( 'Edit Person', 'person' ),
    'new_item' => _x( 'New Person', 'person' ),
    'view_item' => _x( 'View Person', 'person' ),
    'search_items' => _x( 'Search People', 'person' ),
    'not_found' => _x( 'No people found', 'person' ),
    'not_found_in_trash' => _x( 'No people found in Trash', 'person' ),
    'parent_item_colon' => _x( 'Parent Person:', 'person' ),
    'menu_name' => _x( 'People', 'person' ),
    );
    $args = array(
    'labels' => $labels,
    'hierarchical' => false,
    'supports' => array( 'title', 'editor' ),
    'public' => false,
    'show_ui' => true,
    'show_in_menu' => true,
    'show_in_nav_menus' => true,
    'publicly_queryable' => true,
    'exclude_from_search' => false,
    'has_archive' => false,
    'query_var' => false,
    'can_export' => true,
    'rewrite' => true,
    'capability_type' => 'post',
	'menu_icon' => WP_PLUGIN_URL .'/bms_people/images/bms_people_icon.png', // 16px16
    );
    register_post_type( 'person', $args );
    } 

// =============================================================================

//////////////////////////
//
// ADD META BOX
//
//////////////////////////

if (is_admin()) {
	if (!class_exists('SmartMetaBox')) {
		require_once("../wp-content/plugins/bms_smart_meta_box/SmartMetaBox.php");
	}
	
	new SmartMetaBox('smart_meta_box_people', array(
		'title'     => 'BMS People',
		'pages'     => array('person'),
		'context'   => 'normal',
		'priority'  => 'high',
		'fields'    => array(
			array(
				'name' => 'Title',
				'id' => 'bms_people_title',
				'default' => '',
				'desc' => '',
				'type' => 'text',
			),
			array(
				'name' => 'Image',
				'id' => 'bms_people_image',
				'default' => '',
				'desc' => 'Add an image.',
				'type' => 'file',
			),
		)
	));
}
	
// =============================================================================

//////////////////////////
//
// ADD IMAGE SIZES
//
//////////////////////////

add_image_size( '75x75', 75, 75, true );
add_image_size( '150x9999', 150, 9999 );

// =============================================================================

//////////////////////////
//
// ADD DISPLAY
//
//////////////////////////

add_filter('the_content', 'bms_people_the_content');
function bms_people_the_content($c) {
	global $post;
	
	if ($post->post_type == "person") {
		$img = get_post_meta($post->ID, '_smartmeta_bms_people_image', true);
		$img = wp_get_attachment_image_src( $img, '150x9999');
		$img_src	=$img[0];
		$img_width	=$img[1];
		$img_height	=$img[2];	
		
		$title = get_post_meta($post->ID, '_smartmeta_bms_people_title', true);
		
		$return .= "<div class='bms-people person-".$post->ID."'>"."\r\n";
		$return .= "<p class='bms-people-title'>$title</p>"."\r\n";
		$return .= "<img src='$img_src' width='$img_width' height='$img_height' alt='image' />"."\r\n";
		$return .= "</div>"."\r\n";
		
		$c = $return . $c;
	}
	
	return $c;
}


// =============================================================================

//////////////////////////
//
// SHORT CODES
//
//////////////////////////

// create shortcode for listing:
function bms_people_listing($atts, $content=null) {
	extract( shortcode_atts( array(
		'foo' => 'something',
		'bar' => 'something else',
	), $atts ) );
	
	$return="<ul class='bms-people-listing'>";
	$i = 0;
	
	// get posts
	$args = array('post_type'=>'person', 'orderby'=>'menu_order', 'order'=>'ASC');
	$my_posts = get_posts($args);
	foreach($my_posts as $my_post) {
		$img = get_post_meta($my_post->ID, '_smartmeta_bms_people_image', true);
		$img = wp_get_attachment_image_src( $img, '75x75');
		$img_src	=$img[0];
		$img_width	=$img[1];
		$img_height	=$img[2];	
		
		$title = get_post_meta($my_post->ID, '_smartmeta_bms_people_title', true);
		
		$return .= "<li class='bms-people person-".$my_post->ID."'>"."\r\n";
		$return .= "<a href='".get_permalink($my_post->ID)."'>"."\r\n";
		$return .= "<img src='$img_src' width='$img_width' height='$img_height' alt='image' />"."\r\n";
		$return .= "<span class='bms-people-name'>".$my_post->post_title."</span> "."\r\n";
		$return .= "<span class='bms-people-title'>".$title."</span>"."\r\n";
		$return .= "</a>"."\r\n";
		$return .= "</li>"."\r\n";
		$i++;
	}
	$return .="</ul>";
	
	return $return;
}

add_shortcode('bms_people_listing', 'bms_people_listing');
