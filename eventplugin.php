<?php
/*
 * Plugin Name: eventplugin
 * Description:  собственно плагин, первый, кривой-косой, но плагин для WP; нужен для работы с событиями;
 * и я пока ума не приложу, как и что он будет делать :-)
 * Version: 0.1
 * Author: TechnoPreacher
 * License: GPLv2 or later
 * Requires at least: 5.0
 * Requires PHP: 8.0
*/

register_activation_hook( __FILE__, 'eventplugin_install' );

function eventplugin_install(){
   
}

register_deactivation_hook( __FILE__, 'eventplugin_deactivate' );

function eventplugin_deactivate()
{ 
	unregister_post_type( 'events' );
}

function eventplugin_activate()
{
    $labels = array(
        'name' => 'Events',
        'singular_name' => 'Events',
        'menu_name' => 'Events',
        'name_admin_bar' => 'Event',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Event',
        'new_item' => 'New Event',
        'edit_item' => 'Edit Event',
        'view_item' => 'View Event',
        'all_items' => 'All Events',
        'search_items' => 'Search Events',
        'parent_item_colon' => 'Parent Events',
        'not_found' => 'No Events Found',
        'not_found_in_trash' => 'No Events Found in Trash'
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_nav_menus' => true,
        'show_in_menu' => true,
        'show_in_admin_bar' => true,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-admin-appearance',
        'capability_type' => 'post',
        'hierarchical' => false,
        'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'),
        'has_archive' => true,
        'rewrite' => array('slug' => 'events'),
        'query_var' => true
    );
    
	register_post_type('events', $args);
	
	// Сбрасываем настройки ЧПУ, чтобы они пересоздались с новыми данными
	flush_rewrite_rules();
}


add_action( 'init', 'eventplugin_activate' );//инициализация


/*



function create_taxonomies() {

    // Add a taxonomy like categories
    $labels = array(
        'name'              => 'Types',
        'singular_name'     => 'Type',
        'search_items'      => 'Search Types',
        'all_items'         => 'All Types',
        'parent_item'       => 'Parent Type',
        'parent_item_colon' => 'Parent Type:',
        'edit_item'         => 'Edit Type',
        'update_item'       => 'Update Type',
        'add_new_item'      => 'Add New Type',
        'new_item_name'     => 'New Type Name',
        'menu_name'         => 'Types',
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'type' ),
    );

    register_taxonomy('sm_project_type',array('sm_project'),$args);

    // Add a taxonomy like tags
    $labels = array(
        'name'                       => 'Attributes',
        'singular_name'              => 'Attribute',
        'search_items'               => 'Attributes',
        'popular_items'              => 'Popular Attributes',
        'all_items'                  => 'All Attributes',
        'parent_item'                => null,
        'parent_item_colon'          => null,
        'edit_item'                  => 'Edit Attribute',
        'update_item'                => 'Update Attribute',
        'add_new_item'               => 'Add New Attribute',
        'new_item_name'              => 'New Attribute Name',
        'separate_items_with_commas' => 'Separate Attributes with commas',
        'add_or_remove_items'        => 'Add or remove Attributes',
        'choose_from_most_used'      => 'Choose from most used Attributes',
        'not_found'                  => 'No Attributes found',
        'menu_name'                  => 'Attributes',
    );

    $args = array(
        'hierarchical'          => false,
        'labels'                => $labels,
        'show_ui'               => true,
        'show_admin_column'     => true,
        'update_count_callback' => '_update_post_term_count',
        'query_var'             => true,
        'rewrite'               => array( 'slug' => 'attribute' ),
    );

    register_taxonomy('sm_project_attribute','sm_project',$args);
}
*/