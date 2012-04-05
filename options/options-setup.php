<?php
/**
 * audiotheme Options Init
 *
 * Initializes and loads up theme options. Uses the Struts option framework.
 * See https://github.com/jestro/struts
 *
 * @since 2.0
 */
if ( ! function_exists( 'audiotheme_options_init' ) ) :
function audiotheme_options_init() {
	/* Load options class (struts) */
	require_once('classes/struts.php' );
	
	/* Conigure options load */
	Struts::load_config( array(
		// required, set this to the URI of the root Struts directory
		'struts_root_uri' => AUDIOTHEME_DIR . 'options',
		// optional, overrides the Settings API html output
		'use_struts_skin' => true, 
	) );
	
	/* Load options */
	if( current_theme_supports( 'audiotheme-default-options' ) ){
    	include_once( 'options-default.php' );
    }
}
endif;


/**
 * audiotheme Theme Option
 *
 * Function called to get a Theme Option. 
 * The option defaults to false unless otherwise set.
 *
 * @since 2.0
 */
if ( ! function_exists( 'audiotheme_get_option' ) ) :
function audiotheme_get_option( $option_name, $default = false ) {
	global $theme_options;

	$option = $theme_options->get_value( $option_name );

	if ( isset( $option ) && ! empty( $option ) ) {
		return $option;
	}

	return $default;
}
endif;


/**
 * audiotheme Style Option
 *
 * Function called to get a Style Option. 
 * The option defaults to false unless otherwise set.
 *
 * @since 2.0
 */
if ( ! function_exists( 'audiotheme_style' ) ) :
function audiotheme_style( $option_name, $default = false ) {
	global $theme_styles;

	$option = $theme_styles->get_value( $option_name );

	if ( isset( $option ) && ! empty( $option ) ) {
		return $option;
	}

	return $default;
}
endif;


/**
 * Get Category List
 *
 * Utility function to get the category list and 
 * return array of category ID and Name.
 *
 * @retunr Array Category ID and Name
 * @since 2.0
 */
if ( ! function_exists( 'audiotheme_get_category_list' ) ) :
function audiotheme_get_category_list() {
	// Pull all the categories into an array
	$list = array();  
	$categories = get_categories();
	$list[''] = __( 'Select a category:', 'audiotheme' );
	
	foreach ( (array) $categories as $category )
	    $list[$category->cat_ID] = $category->cat_name;
	
	return $list;
}
endif;

?>