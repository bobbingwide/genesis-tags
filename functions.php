<?php // (C) Copyright Bobbing Wide 2015

genesis_tags_functions_loaded();


genesis_tags_functions_loaded();



//* Child theme (do not remove) - is this really necessary? 
define( 'CHILD_THEME_NAME', 'TAGS' );
define( 'CHILD_THEME_URL', 'http://www.bobbingwide.com/oik-themes' );
define( 'CHILD_THEME_VERSION', '2.2.3' );


/**
 * Display footer credits for the genesis-tags theme
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
	$text .= '[bw_power]';
  return( $text );
}

/**
 * Trace all genesis hooks
 * 
 * So we can attempt to see what hook causes Genesis to do something.
 * Use View source and look for all the genesis hook names inside HTML comments
 * 
 * Notes:
 * - it's not safe to produce HTML comments before the doctype tag has been created
 * - we're only interested hooks prefixed 'genesis_'
 *
 * @param string $tag the action hook or filter
 * @param mixed $args parameters? 
 */
function genesis_all( $tag, $args2=null ) {
	static $ok_to_e_c = false;
	if ( $ok_to_e_c ) {
		if ( 0 === strpos( $tag, "genesis_" ) ) {
			$hooked = genesis_get_hooks( $tag );
			genesis_safe_e_c( $tag, $hooked );
		}
		if ( 0 === strpos( $tag, "the_excerpt" ) ) {
			$hooked = genesis_get_hooks( $tag );
			genesis_safe_e_c( $tag, $hooked );
		}
		
		if ( 0 === strpos( $tag, "the_content" ) ) {
			$hooked = genesis_get_hooks( $tag );
			genesis_safe_e_c( $tag, $hooked );
		}
		
		if ( 0 === strpos( $tag, "the_permalink" ) ) {
			$hooked = genesis_get_hooks( $tag );
			genesis_safe_e_c( $tag, $hooked );
			//bw_trace2( $hooked, $tag );
			//bw_backtrace();
		}
		
	} else {
		if ( "genesis_doctype" === $tag ) {
			$ok_to_e_c = true;
		}
	}
}

/**
 * Only echo comments when safe
 */
function genesis_safe_e_c( $tag, $hooked ) {
	static $deferred = null;
	$hook_type = genesis_trace_get_hook_type( $tag );
	if ( $hook_type === "action" ) {
		if ( $deferred ) {
			_e_c( "deferred $deferred" );
			$deferred = null;
		}
		_e_c( "$hook_type $tag $hooked" );
	
	} else {
		$deferred .= "\n";
		$deferred .= "$hook_type $tag $hooked";
	}
}
	
/** 
 * Return the hook type
 * 
 */ 
function genesis_trace_get_hook_type( $hook ) {
	global $wp_actions;
	if ( isset( $wp_actions[ $hook ] ) ){
		$type = "action";
	} else {
		$type = "filter";
	}
	return( $type );
}

/**
 * Return the current filter summary
 * 
 * Even if current_filter exists the global $wp_current_filter may not be set
 * 
 * @return string current filter array imploded with commas
 */
function genesis_current_filter() {
  global $wp_current_filter;
  if ( is_array( $wp_current_filter ) ) { 
	  $filters = implode( ",",  $wp_current_filter );
	} else {
	  $filters = null;
	}		
  return( $filters );  
}

/**
 * Return the attached hooks
 *
 * Note: It's safe to use foreach over $wp_filter[ $tag ]
 * since this routine's invoked for the 'all' hook
 * not the hook in question.
 * But I've copied the code for bw_trace_get_attached_hooks() anyway
 * since it's more 'complete' 
 *
 * See {@link http://php.net/manual/en/control-structures.foreach.php}
 *
 * @param string $tag the action hook or filter
 * @return string the attached hook information
 *
 */
function genesis_get_hooks( $tag ) {
	global $wp_filter; 
  if ( isset( $wp_filter[ $tag ] ) ) {
		$current_hooks = $wp_filter[ $tag ];
		//bw_trace2( $current_hooks, "current hooks for $tag", false, BW_TRACE_VERBOSE );
		$hooks = null;
		$hooks = genesis_current_filter();
		$hooks .= "\n";
		foreach ( $current_hooks as $priority => $functions ) {
			$hooks .= "\n: $priority  ";
			foreach ( $functions as $index => $args ) {
				$hooks .= " ";
				if ( is_object( $args['function' ] ) ) {
					$object_name = get_class( $args['function'] );
					$hooks .= $object_name; 

				} elseif ( is_array( $args['function'] ) ) {
					//bw_trace2( $args, "args" );
					if ( is_object( $args['function'][0] ) ) { 
						$object_name = get_class( $args['function'][0] );
					}	else {
						$object_name = $args['function'][0];
					}
					$hooks .= $object_name . '::' . $args['function'][1];
				} else {
					$hooks .= $args['function'];
				}
				$hooks .= ";" . $args['accepted_args'];
			}
		}
		
	} else {
		$hooks = null;
	}
	return( $hooks ); 
}

/**
 * Echo a comment
 *
 * @param string $string the text to echo inside the comment
 */
function _e_c( $string ) {
	echo "<!--\n";
	echo $string;
	echo "-->";
}

/**
 * Display the post info in our style
 *
 * We only want to display the post date and post modified date
 * plus the post_edit link. 
 * Note: The post edit link may appear multiple times
 *
 */
function genesis_tags_post_info() {
	$output = genesis_markup( array(
    'html5'   => '<p %s>',
    'xhtml'   => '<div class="post-info">',
    'context' => 'entry-meta-before-content',
    'echo'    => false,
	) );
	$string = sprintf( __( 'Published %1$s', 'genesis-tags' ), '[post_date]' );
	$string .= ' | ';
	$string .= sprintf( __( 'Last updated %1$s', 'genesis-tags' ), '[post_modified_date]' );
  $string .= ' [post_edit]';
	$output .= apply_filters( 'genesis_post_info', $string);
	$output .= genesis_html5() ? '</p>' : '</div>';  
	echo $output;
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
 */
function genesis_tags_functions_loaded() {
	// Start the engine	- @TODO Is this necessary?
	include_once( get_template_directory() . '/lib/init.php' );
	
	if ( defined( "GENESIS_ALL" ) && GENESIS_ALL ) {
  	add_action( "all", "genesis_all", 10, 2 );
	}
	//* Add HTML5 markup structure
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list' ) );

	//* Add viewport meta tag for mobile browsers
	add_theme_support( 'genesis-responsive-viewport' );
	
	// Add support for structural wraps
	add_theme_support( 'genesis-structural-wraps', array(
	 'header',
	//	'nav',
	//        'subnav',
		'site-inner'
	) );

	//* Add support for custom background
	add_theme_support( 'custom-background' );

	//* Add support for 5-column footer widgets - requires extra CSS
	add_theme_support( 'genesis-footer-widgets', 5 );

	add_filter( 'genesis_footer_creds_text', "tags_footer_creds_text" );
	
  add_filter( 'genesis_pre_get_option_site_layout', 'genesis_tags_pre_get_option_site_layout', 10, 2 );
	
	remove_action( 'genesis_entry_footer', 'genesis_post_meta' );
	
	// Remove post info
	remove_action( 'genesis_entry_header', 'genesis_post_info', 12 );
	add_action( 'genesis_entry_footer', 'genesis_tags_post_info' );
	//add_filter( "genesis_edit_post_link", "__return_false" );
	
  //genesis_tags_register_sidebars();
	remove_action( 'genesis_site_title', 'genesis_seo_site_title' );
	add_action( 'genesis_site_title', 'genesis_tags_site_title' );
	remove_action( 'genesis_site_description', 'genesis_seo_site_description' );
	
	

}

function genesis_tags_site_title( ) {
	echo '<h1 class="site-title">The Anchor Golf Society</h1>';
}
