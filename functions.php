<?php // (C) Copyright Bobbing Wide 2015, 2016

// Child theme (do not remove) - is this really necessary? 
define( 'CHILD_THEME_NAME', 'TAGS' );
define( 'CHILD_THEME_URL', 'http://www.bobbingwide.com/oik-themes' );
define( 'CHILD_THEME_VERSION', '1.0.0' );

genesis_tags_functions_loaded();




/**
 * Display footer credits for the genesis-tags theme
 * 
 */	
function tags_footer_creds_text( $text ) {
	do_action( "oik_add_shortcodes" );
	$text = "[bw_wpadmin]";
  $text .= '<br />';
	$text .= "[bw_copyright]"; 
	$text .= '<hr />';
	$text .= 'Website designed and developed by [bw_link text="Herb Miller" herbmiller.me] of';
	$text .= ' <a href="//www.bobbingwide.com" title="Bobbing Wide - web design, web development">[bw]</a>';
	$text .= '<br />';
	$text .= '[bw_power] and oik-plugins';
  return( $text );
}

/**
 * Display the post info in our style
 *
 * We only want to display the post date and post modified date plus the post_edit link. 
 * 
 * Note: On some pages the post edit link appeared multiple times - so we had to find a fancy way
 * of turning it off, except when we really wanted it. 
 * Solution was to not use "genesis_post_info" but to expand shortcodes ourselves  
 *
 *
 */
function genesis_tags_post_info() {
	remove_filter( "genesis_edit_post_link", "__return_false" );
	$output = genesis_markup( array(
    'html5'   => '<p %s>',
    'xhtml'   => '<div class="post-info">',
    'context' => 'entry-meta-before-content',
    'echo'    => false,
	) );
	$string = sprintf( __( 'Published: %1$s', 'genesis-oik' ), '[post_date]' );
	$string .= '<span class="splitbar">';
	$string .= ' | ';
	$string .= '</span>';
	$string .= '<span class="lastupdated">';
	$string .= sprintf( __( 'Last updated: %1$s', 'genesis-oik' ), '[post_modified_date]' );
	$string .= '</span>';
  $string .= ' [post_edit]';
	//$output .= apply_filters( 'do_shortcodes', $string);
	$output .= do_shortcode( $string );
	$output .= genesis_html5() ? '</p>' : '</div>';  
	echo $output;
	add_filter( "genesis_edit_post_link", "__return_false" );
}

/**
 * Display the sidebar for the given post type
 *
 * Normally we just append -widget-area but for some post types we override it 
 *
 * Post type  | Sidebar used
 * ---------- | -------------
 * oik_premiumversion | oik_pluginversion-widget-area
 * oik_sc_param | sidebar-alt
 * 
 * 
 */
function genesis_tags_get_sidebar() {
	//* Output primary sidebar structure
	genesis_markup( array(
		'html5'   => '<aside %s>',
		'xhtml'   => '<div id="sidebar" class="sidebar widget-area">',
		'context' => 'sidebar-primary',
	) );
	do_action( 'genesis_before_sidebar_widget_area' );
	$post_type = get_post_type();
	$cpts = array( "oik_premiumversion" => "oik_pluginversion-widget-area" 
							 , "oik_sc_param" => "sidebar-alt"
							 , "attachment" => "sidebar-alt"
							 );
	$dynamic_sidebar = bw_array_get( $cpts, $post_type, "$post_type-widget-area" ); 
	dynamic_sidebar( $dynamic_sidebar );
	do_action( 'genesis_after_sidebar_widget_area' );
	genesis_markup( array(
		'html5' => '</aside>', //* end .sidebar-primary
		'xhtml' => '</div>', //* end #sidebar
	) );
} 

/**
 * Implement 'genesis_tags_pre_get_option_site_layout' filter 
 *
 * The _genesis_layout has not been defined so we need to decide based on the 
 * previous setting for the Artisteer theme.
 *
 * @param string $layout originally null
 * @param string $setting the current default setting 
 * @return string $layout which is either to have a sidebar or not
 */
function genesis_tags_pre_get_option_site_layout( $layout, $setting ) {
	//bw_trace2();
	$artisteer_sidebar = genesis_get_custom_field( "_theme_layout_template_default_sidebar" );
	if ( $artisteer_sidebar ) {	
		$layout = __genesis_return_content_sidebar();
	} else {
		// $layout = __genesis_return_full_width_content();
	}
	return( $layout );
}

/**
 * Register the hooks for this theme
 * 
 */
function genesis_tags_functions_loaded() {
	// Start the engine	- @TODO Is this necessary?
	include_once( get_template_directory() . '/lib/init.php' );
	
	//* Add HTML5 markup structure
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list' ) );

	//* Add viewport meta tag for mobile browsers
	add_theme_support( 'genesis-responsive-viewport' );
	
	// Add support for structural wraps
	add_theme_support( 'genesis-structural-wraps', array(
	 'header',
	//	'nav',
	//        'subnav',
		'site-inner',
		'footer-widgets'
	) );

	//* Add support for custom background
	add_theme_support( 'custom-background' );

	//* Add support for 3-column footer widgets - requires extra CSS
	add_theme_support( 'genesis-footer-widgets', 3 );

	add_filter( 'genesis_pre_get_option_footer_text', "tags_footer_creds_text" );
	
  add_filter( 'genesis_pre_get_option_site_layout', 'genesis_tags_pre_get_option_site_layout', 10, 2 );
	
	remove_action( 'genesis_entry_footer', 'genesis_post_meta' );
	
	// Remove post info
	remove_action( 'genesis_entry_header', 'genesis_post_info', 12 );
	add_action( 'genesis_entry_footer', 'genesis_tags_post_info' );
	add_filter( "genesis_edit_post_link", "__return_false" );
	
  //genesis_tags_register_sidebars(); 
	 	
	/* Replace site title by modified site description
	*/
	//remove_action( 'genesis_site_title', 'genesis_seo_site_title' );
	//add_action( 'genesis_site_title', 'genesis_tags_site_title' );
	remove_action( 'genesis_site_description', 'genesis_seo_site_description' );
	
	// Remove primary menu
	remove_action( 'genesis_after_header', 'genesis_do_nav' );
	add_filter( "genesis_breadcrumb_args", "genesis_tags_breadcrumb_args" );
	
	
	//remove_action( 'genesis_entry_footer', 'genesis_post_meta' );

}

/**
 * 
 */ 
function genesis_tags_site_title( ) {
	echo '<h1 class="site-title">The Anchor Golf Society</h1>';
}

function genesis_tags_breadcrumb_args( $args ) {
	$args['labels']['prefix'] = "";
	return( $args );
}



