<?php
//* Start the engine
include_once( get_template_directory() . '/lib/init.php' );

//* Child theme (do not remove)
define( 'CHILD_THEME_NAME', 'Swank Theme' );
define( 'CHILD_THEME_URL', 'http://my.studiopress.com/themes/swank/' );
define( 'CHILD_THEME_VERSION', '1.0.0' );

//* Enqueue scripts and styles
add_action( 'wp_enqueue_scripts', 'swank_enqueue_scripts' );
function swank_enqueue_scripts() {

	wp_enqueue_script( 'swank-responsive-menu', get_stylesheet_directory_uri() . '/lib/js/responsive-menu.js', array( 'jquery' ), '1.0.0', true ); 
	wp_enqueue_style( 'swank-google-fonts', '//fonts.googleapis.com/css?family=Lora:400,700,400italic,700italic|Raleway:500,700,400,200|Satisfy', array(), CHILD_THEME_VERSION );

}

//* Add support for custom header
// add_theme_support( 'custom-header', array(
// 	'width'           => 640,
//	'height'          => 200,
//	'header-selector' => '.site-title a',
//	'header-text'     => false,
//) );

/** Remove Edit Link */
add_filter( 'edit_post_link', '__return_false' );

//* Add HTML5 markup structure
add_theme_support( 'html5' );

//* Add viewport meta tag for mobile browsers
add_theme_support( 'genesis-responsive-viewport' );

//* Add support for custom background
add_theme_support( 'custom-background' );

//* Add support for 3-column footer widgets
add_theme_support( 'genesis-footer-widgets', 2 );

//* Add new image sizes 
add_image_size( 'circles', 200, 200, TRUE );
add_image_size( 'portfolio-featured', 500, 300, TRUE );
add_image_size( 'sidebar', 290, 150, TRUE );

//* Add Top Bar Above Header
add_action( 'genesis_before_header', 'swank_top_bar' );
function swank_top_bar() {
 
	echo '<div class="top-bar"><div class="wrap">';
 
	genesis_widget_area( 'top-bar-left', array(
		'before' => '<div class="top-bar-left">',
		'after' => '</div>',
	) );

	genesis_widget_area( 'top-bar-right', array(
		'before' => '<div class="top-bar-right">',
		'after' => '</div>',
	) );
 
	echo '</div></div>';
 
}

//* Remove the entry meta in the entry footer
remove_action( 'genesis_entry_footer', 'genesis_post_meta' );

//* Customize the entry meta in the entry header
add_filter( 'genesis_post_info', 'swank_post_info_filter' );
function swank_post_info_filter($post_info) {

	$post_info = '[post_date] by [post_author_posts_link] [post_categories] [post_comments]';
	return $post_info;

}

//* Reposition the secondary navigation menu
remove_action( 'genesis_after_header', 'genesis_do_subnav' );
add_action( 'genesis_before_footer', 'genesis_do_subnav' );

//* Reduce the secondary navigation menu to one level depth
add_filter( 'wp_nav_menu_args', 'swank_secondary_menu_args' );
function swank_secondary_menu_args( $args ){

	if( 'secondary' != $args['theme_location'] )
	return $args;

	$args['depth'] = 1;
	return $args;
}

//* Change Avatar Size
add_filter( 'genesis_comment_list_args', 'swank_comment_list_args' );
function swank_comment_list_args( $args ) {

	return array( 'type' => 'comment', 'avatar_size' => 100, 'callback' => 'genesis_comment_callback' );

}

//* Add Support for Comment Numbering
add_action ('genesis_before_comment', 'afn_numbered_comments');
function afn_numbered_comments () {

    if (function_exists('gtcn_comment_numbering'))
    echo gtcn_comment_numbering($comment->comment_ID, $args);

}

//* Change the number of portfolio items to be displayed (props Bill Erickson) 
add_action( 'pre_get_posts', 'swank_portfolio_items' );
function swank_portfolio_items( $query ) {

	if( $query->is_main_query() && !is_admin() && is_post_type_archive( 'portfolio' ) ) {
		$query->set( 'post_parent', '0' );
		$query->set( 'orderby', 'ID' );
		$query->set( 'order', 'ASC' );
		$query->set( 'posts_per_page', '6' );
	} 

}

//* Jetpack tiled mosaic
if ( ! isset( $content_width ) )
    $content_width = 1140;

//* Create portfolio custom post type 
add_action( 'init', 'portfolio_post_type' );
function portfolio_post_type() {
    register_post_type( 'portfolio',
        array(
            'labels' => array(
                'name' => __( 'Gallery' ),
                'singular_name' => __( 'Gallery' ),
            ),
            'exclude_from_search' => true,
            'has_archive' => true,
            'hierarchical' => true,
            'public' => true,
            'rewrite' => array( 'slug' => 'portfolio' ),
            'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'page-attributes', 'genesis-seo' ),
        )
    );
}

//* Create Portfolio Album Template
 function be_portfolio_album_template( $template ) {
	global $post;
	if( is_singular( 'portfolio' ) && 1 == $post->post_parent )
		$template = get_query_template( 'portfolio-album' );
	return $template;
}
add_filter( 'template_include', 'be_portfolio_album_template' );


//* Customize the credits 
add_filter('genesis_footer_creds_text', 'swank_footer_creds_filter');
function swank_footer_creds_filter( $creds ) {

    $creds = 'Copyright [footer_copyright] &middot; <a href="#">Back to Top</a>';
    return $creds;

}

//* Register Widget Areas
genesis_register_sidebar( array(
	'id'          => 'top-bar-left',
	'name'        => __( 'Top Bar Left', 'swank' ),
	'description' => __( 'This is the left side of your top bar.', 'swank' ),
) );

genesis_register_sidebar( array(
	'id'          => 'top-bar-right',
	'name'        => __( 'Top Bar Right', 'swank-' ),
	'description' => __( 'This is the right side of your top bar.', 'swank' ),
) );

genesis_register_sidebar( array(
    'id'          => 'portfolioblurb',
    'name'        => __( 'Portfolio Blurb', 'swank' ),
    'description' => __( 'This is a widget area that can be shown above your portfolio', 'swank' ),
) );

genesis_register_sidebar( array(
	'id'         => 'home-slider',
	'name'       => __( 'Home Page Slider Widget', 'swank' ),
	'description' => __( 'This is the slider widget on your home page', 'swank' ),
) );

genesis_register_sidebar( array(
	'id'          => 'featured-circles',
	'name'        => __( 'Home Page Featured Post Rectangles', 'swank' ),
	'description' => __( 'This is the top section of your home page', 'swank' ),
) );

genesis_register_sidebar( array(
	'id'          => 'home-featured-area',
	'name'        => __( 'Home Featured Widget Area', 'swank' ),
	'description' => __( 'This is the featured posts section of your home page.', 'swank' ),
) );


function fb_home_image( $tags ) {
    if ( is_home() || is_front_page() ) {
        // Remove the default blank image added by Jetpack
        unset( $tags['og:image'] );
 
        $fb_home_img = 'http://hbevents.ca/images/HB_OG.png';
        $tags['og:image'] = esc_url( $fb_home_img );
    }
    return $tags;
}
add_filter( 'jetpack_open_graph_tags', 'fb_home_image' );