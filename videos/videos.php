<?php
/**
 * Set up video-related functionality in the AudioTheme framework.
 *
 * @package AudioTheme_Framework
 * @subpackage Videos
 */

/**
 * Load the video template API.
 */
require( AUDIOTHEME_DIR . 'videos/post-template.php' );

/**
 * Load the admin interface elements and functionality for videos.
 */
if ( is_admin() ) {
	require( AUDIOTHEME_DIR . 'videos/admin/videos.php' );
}

/**
 * Register video post type and attach hooks to load related functionality.
 *
 * @since 1.0.0
 * @uses register_post_type()
 */
function audiotheme_videos_init() {
	// Register the video custom post type.
	register_post_type( 'audiotheme_video', array(
		'has_archive'            => get_audiotheme_videos_rewrite_base(),
		'hierarchical'           => false,
		'labels'                 => array(
			'name'               => _x( 'Videos', 'post type general name', 'audiotheme-i18n' ),
			'singular_name'      => _x( 'Video', 'post type singular name', 'audiotheme-i18n' ),
			'add_new'            => _x( 'Add New', 'video', 'audiotheme-i18n' ),
			'add_new_item'       => __( 'Add New Video', 'audiotheme-i18n' ),
			'edit_item'          => __( 'Edit Video', 'audiotheme-i18n' ),
			'new_item'           => __( 'New Video', 'audiotheme-i18n' ),
			'view_item'          => __( 'View Video', 'audiotheme-i18n' ),
			'search_items'       => __( 'Search Videos', 'audiotheme-i18n' ),
			'not_found'          => __( 'No videos found', 'audiotheme-i18n' ),
			'not_found_in_trash' => __( 'No videos found in Trash', 'audiotheme-i18n' ),
			'all_items'          => __( 'All Videos', 'audiotheme-i18n' ),
			'menu_name'          => __( 'Videos', 'audiotheme-i18n' ),
			'name_admin_bar'     => _x( 'Video', 'add new on admin bar', 'audiotheme-i18n' ),
		),
		'menu_position'          => 514,
		'public'                 => true,
		'publicly_queryable'     => true,
		'register_meta_box_cb'   => 'audiotheme_video_meta_boxes',
		'rewrite'                => array(
			'slug'       => get_audiotheme_videos_rewrite_base(),
			'with_front' => false
		),
		'show_ui'                => true,
		'show_in_menu'           => true,
		'show_in_nav_menus'      => false,
		'supports'               => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'revisions', 'author' ),
		'taxonomies'             => array( 'post_tag' ),
	) );

	add_action( 'template_include', 'audiotheme_video_template_include' );
	add_action( 'delete_attachment', 'audiotheme_video_delete_attachment' );
	add_filter( 'post_class', 'audiotheme_video_archive_post_class' );
}

/**
 * Get the videos rewrite base. Defaults to 'videos'.
 *
 * @since 1.0.0
 *
 * @return string
 */
function get_audiotheme_videos_rewrite_base() {
	$base = get_option( 'audiotheme_video_rewrite_base' );
	return ( empty( $base ) ) ? 'videos' : $base;
}

/**
 * Load video templates.
 *
 * Templates should be included in an /audiotheme/ directory within the theme.
 *
 * @since 1.0.0
 *
 * @param string $template Template path.
 * @return string
 */
function audiotheme_video_template_include( $template ) {
	if ( is_post_type_archive( 'audiotheme_video' ) ) {
		$template = audiotheme_locate_template( 'archive-video.php' );
	} elseif ( is_singular( 'audiotheme_video' ) ) {
		$template = audiotheme_locate_template( 'single-video.php' );
	}

	return $template;
}

/**
 * Delete oEmbed thumbnail post meta if the associated attachment is deleted.
 *
 * @since 1.0.0
 *
 * @param int $attachment_id The ID of the attachment being deleted.
 */
function audiotheme_video_delete_attachment( $attachment_id ) {
	global $wpdb;

	$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_audiotheme_oembed_thumbnail_id' AND meta_value=%d", $attachment_id ) );
	if ( $post_id ) {
		delete_post_meta( $post_id, '_audiotheme_oembed_thumbnail_id' );
		delete_post_meta( $post_id, '_audiotheme_oembed_thumbnail_url' );
	}
}

/**
 * Add classes to video posts on the archive page.
 *
 * Classes serve as helpful hooks to aid in styling across various browsers.
 *
 * - Adds nth-child classes to video posts.
 *
 * @since 1.2.0
 *
 * @param array $classes Default post classes.
 * @return array
 */
function audiotheme_video_archive_post_class( $classes ) {
	global $wp_query;

	if ( $wp_query->is_main_query() && is_post_type_archive( 'audiotheme_video' ) ) {
		$nth_child_classes = audiotheme_nth_child_classes( array(
			'current' => $wp_query->current_post + 1,
			'max'     => 4,
		) );

		$classes = array_merge( $classes, $nth_child_classes );
	}

	return $classes;
}
