<?php

// disable direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Register Custom Post Type BOOKS with slug book
 */
function bytion_register_cpt_books() {

	$labels = array(
		"name" => __( 'BOOKS', 'bytion-assessment' ),
		"singular_name" => __( 'BOOK', 'bytion-assessment' ),
		"menu_name" => __( 'My Books', 'bytion-assessment' ),
		"all_items" => __( 'All Books', 'bytion-assessment' ),
		"add_new" => __( 'Add New', 'bytion-assessment' ),
		"add_new_item" => __( 'Add New Book', 'bytion-assessment' ),
		"edit_item" => __( 'Edit Book', 'bytion-assessment' ),
		"new_item" => __( 'New Book', 'bytion-assessment' ),
		"view_item" => __( 'View Book', 'bytion-assessment' ),
		"view_items" => __( 'View Books', 'bytion-assessment' ),
		"search_items" => __( 'Search Book', 'bytion-assessment' ),
		"not_found" => __( 'No Books Found', 'bytion-assessment' ),
		"not_found_in_trash" => __( 'No Books found in Trash', 'bytion-assessment' ),
		"parent_item_colon" => __( 'Parent Book:', 'bytion-assessment' ),
		"featured_image" => __( 'Featured image for this Book', 'bytion-assessment' ),
		"set_featured_image" => __( 'Set featured image for this Book', 'bytion-assessment' ),
		"remove_featured_image" => __( 'Remove featured image for this Book', 'bytion-assessment' ),
		"use_featured_image" => __( 'Use as featured image for this Book', 'bytion-assessment' ),
		"archives" => __( 'Book archives', 'bytion-assessment' ),
		"insert_into_item" => __( 'Insert into Book', 'bytion-assessment' ),
		"uploaded_to_this_item" => __( 'Uploaded to this Book', 'bytion-assessment' ),
		"filter_items_list" => __( 'Filter Books list', 'bytion-assessment' ),
		"items_list_navigation" => __( 'Books list navigation', 'bytion-assessment' ),
		"items_list" => __( 'Books list', 'bytion-assessment' ),
		"attributes" => __( 'Books Attributes', 'bytion-assessment' ),
		"parent_item_colon" => __( 'Parent Book:', 'bytion-assessment' ),
	);

	$args = array(
		"label" => __( 'BOOKS', 'bytion-assessment' ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => false,
		"rest_base" => "",
		"has_archive" => false,
		"show_in_menu" => true,
		"exclude_from_search" => false,
		"capability_type" => array('bytion_book','bytion_books'),
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => array( "slug" => "book", "with_front" => true ),
		"query_var" => true,
		"menu_icon" => "dashicons-book",
		"supports" => array( "title", "editor", "thumbnail", "custom-fields", "revisions" ),
	);

	register_post_type( "book", $args );
}

add_action( 'init', 'bytion_register_cpt_books' );

/**
 * Register Custom Taxonomy BOOK CATGORIES with slug book_category
 */
function bytion_register_taxonomy_book_category() {

	$labels = array(
		"name" => __( 'BOOK CATEGORIES', 'bytion-assessment' ),
		"singular_name" => __( 'BOOK CATEGORY', 'bytion-assessment' ),
	);

	$args = array(
		"label" => __( 'BOOK CATEGORIES', 'bytion-assessment' ),
		"labels" => $labels,
		"public" => true,
		"hierarchical" => true,
		"label" => "BOOK CATEGORIES",
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => array( 'slug' => 'book_category', 'with_front' => true, ),
		"show_admin_column" => true,
		"show_in_rest" => false,
		"rest_base" => "",
		"show_in_quick_edit" => true
	);
	register_taxonomy( "book_category", array( "book" ), $args );

/** 
* CREATE / INSERT default NON FICTION term
**/	
	// Check if the term has already exist. If not yet, it will insert it in the database
	$parent_term = term_exists( 'non-fiction', 'book_category' );
	if (is_null($parent_term)) {
		$parent_term_id = $parent_term['term_id']; // get numeric term id
		wp_insert_term(
			'NON FICTION', // the term 
			'book_category', // the taxonomy
			array(
				'description'=> 'Description for NON FICTION TERM for BOOKS.',
				'slug' => 'non-fiction',
				'parent'=> $parent_term_id
			)
		);	
	}
}

add_action( 'init', 'bytion_register_taxonomy_book_category' );

/**
 * Define default terms for custom taxonomies
 */
function bytion_set_default_object_terms( $post_id, $post ) {
	// Specifically define the default term for post type BOOKS
	if ( 'publish' === $post->post_status && 'book' === $post->post_type ) {
		$defaults = array(
			'book_category' => array( 'non-fiction' ), //set default term for book_category
		);
		$taxonomies = get_object_taxonomies( $post->post_type );
		foreach ( (array) $taxonomies as $taxonomy ) {
			$terms = wp_get_post_terms( $post_id, $taxonomy );
			if ( empty( $terms ) && array_key_exists( $taxonomy, $defaults ) ) {
				wp_set_object_terms( $post_id, $defaults[$taxonomy], $taxonomy );
			}
		}
	}
}
add_action( 'save_post', 'bytion_set_default_object_terms', 100, 2 );
	
/** 
* ASSIGN capability to role. Right now it runs only on admin load.
* But this can be enhanced and controlled by admin user from admin dashboard. 
**/
function bytion_add_role_caps() {
 
	// Add the roles who can administer the BOOKS custom post types
	$roles = array('administrator');

	// Loop through each role and assign capabilities
	foreach($roles as $the_role) { 
 
		$role = get_role($the_role);

		$role->add_cap( 'read' );
		$role->add_cap( 'read_bytion_book' );
		$role->add_cap( 'read_private_bytion_books' );
		$role->add_cap( 'edit_bytion_book' );
		$role->add_cap( 'edit_bytion_books' );
		$role->add_cap( 'edit_others_bytion_books' );
		$role->add_cap( 'edit_published_bytion_books' );
		$role->add_cap( 'publish_bytion_books' );
		$role->add_cap( 'delete_bytion_books' );
		$role->add_cap( 'delete_others_bytion_books' );
		$role->add_cap( 'delete_private_bytion_books' );
		$role->add_cap( 'delete_published_bytion_books' );

	}
}
add_action('admin_init','bytion_add_role_caps',999);

/** 
* REMOVE capability from role. Right now it runs only on admin load.
* But this can be enhanced and controlled by admin user from admin dashboard. 
**/
function bytion_remove_role_caps() {
 
	// Add the roles who can administer the BOOKS custom post types
	$roles = array('author');

	// Loop through each role and remove capabilities
	foreach($roles as $the_role) { 
 
		$role = get_role($the_role);

		$role->remove_cap( 'read' );
		$role->remove_cap( 'read_bytion_book' );
		$role->remove_cap( 'read_private_bytion_books' );
		$role->remove_cap( 'edit_bytion_book' );
		$role->remove_cap( 'edit_bytion_books' );
		$role->remove_cap( 'edit_others_bytion_books' );
		$role->remove_cap( 'edit_published_bytion_books' );
		$role->remove_cap( 'publish_bytion_books' );
		$role->remove_cap( 'delete_others_bytion_books' );
		$role->remove_cap( 'delete_private_bytion_books' );
		$role->remove_cap( 'delete_published_bytion_books' );

	}
}
/* 
* It is currently disabled. 
* The only role who can access custom post type BOOKS is user with administrator role
*/
//add_action('admin_init','bytion_remove_role_caps',999);
?>
