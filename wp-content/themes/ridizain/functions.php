<?php
/**
 * Ridizain functions and definitions
 *
 * Set up the theme and provides some helper functions, which are used in the
 * theme as custom template tags. Others are attached to action and filter
 * hooks in WordPress to change core functionality.
 *
 * When using a child theme you can override certain functions (those wrapped
 * in a function_exists() call) by defining them first in your child theme's
 * functions.php file. The child theme's functions.php file is included before
 * the parent theme's file, so the child theme functions would be used.
 *
 * @link http://codex.wordpress.org/Theme_Development
 * @link http://codex.wordpress.org/Child_Themes
 *
 * Functions that are not pluggable (not wrapped in function_exists()) are
 * instead attached to a filter or action hook.
 *
 * For more information on hooks, actions, and filters,
 * @link http://codex.wordpress.org/Plugin_API
 *
 *
 * @package Ridizain
 * @since Ridizain 1.0
 */

/**
 * Set up the content width value based on the theme's design.
 *
 * @see ridizain_content_width()
 *
 * @since Ridizain 1.0
 */
if ( ! isset( $content_width ) ) {
	$content_width = 574;
}

/**
 * Ridizain only works in WordPress 3.6 or later.
 */
if ( version_compare( $GLOBALS['wp_version'], '3.6', '<' ) ) {
	require get_template_directory() . '/inc/back-compat.php';
}

if ( ! function_exists( 'ridizain_setup' ) ) :
/**
 * Ridizain setup.
 *
 * Set up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support post thumbnails.
 *
 * @since Ridizain 1.0
 */
function ridizain_setup() {

	/*
	 * Make Ridizain available for translation.
	 *
	 * Translations can be added to the /languages/ directory.
	 * If you're building a theme based on Ridizain, use a find and
	 * replace to change 'ridizain' to the name of your theme in all
	 * template files.
	 */
	load_theme_textdomain( 'ridizain', get_template_directory() . '/languages' );

	// This theme styles the visual editor to resemble the theme style.
	add_editor_style( array( 'css/editor-style.css', ridizain_font_url() ) );

	// Add RSS feed links to <head> for posts and comments.
	add_theme_support( 'automatic-feed-links' );

	// Enable support for Post Thumbnails, and declare two sizes.
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 672, 372, true );
	add_image_size( 'ridizain-full-width', 1380, 770, true );
	add_image_size( 'ridizain-recentpost-thumb', 372, 186, true );

	// This theme uses wp_nav_menu() in two locations.
	register_nav_menus( array(
		'primary'   => __( 'Top primary menu', 'ridizain' ),
		'secondary' => __( 'Secondary menu in left sidebar', 'ridizain' ),
		'social' => __( 'Social Icon Menu', 'ridizain' ),
	) );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form', 'comment-form', 'comment-list',
	) );

	/*
	 * Enable support for Post Formats.
	 * See http://codex.wordpress.org/Post_Formats
	 */
	add_theme_support( 'post-formats', array(
		'aside', 'image', 'video', 'audio', 'quote', 'link', 'gallery',
	) );

	// Add support for featured content.
	$layout = get_theme_mod( 'featured_content_layout' );
    $max_posts = get_theme_mod( 'num_posts_' . $layout, 2 );
	add_theme_support( 'featured-content', array(
		'featured_content_filter' => 'ridizain_get_featured_posts',
		'max_posts' => $max_posts,
	) );

	// This theme uses its own gallery styles.
	add_filter( 'use_default_gallery_style', '__return_false' );
}
endif; // ridizain_setup
add_action( 'after_setup_theme', 'ridizain_setup' );

/*
 * Add Featured Content functionality.
 *
 * To overwrite in a plugin, define your own Featured_Content class on or
 * before the 'setup_theme' hook.
 */
if ( ! class_exists( 'Featured_Content' ) && 'plugins.php' !== $GLOBALS['pagenow'] ) {
	require get_template_directory() . '/inc/featured-content.php';
}
/*
 * Lets filter the Ridizain Featured content so that we can use pages or a custom post type instead of posts.
 */
function ridizain_filter_featured_posts( $posts ){

$ridizain_options = get_option( 'featured-content' );

if ( $ridizain_options ) {
    $tag_name = $ridizain_options['tag-name'];
} else {
    $tag_name = 'showcase';
}

// At this point in the filter we are recalling the layout and content count.
$layout = get_theme_mod( 'featured_content_layout' );
$max_posts = get_theme_mod( 'num_posts_' . $layout, 2 );

// Here we determine what content type we are going to feature - Posts, Pages or a Custom Post Type.
$content_type = get_theme_mod( 'featured_content_custom_type' );

// Now we put it all together before returning it in a new post array ready for output.
$args = array(
    'tag' => $tag_name,
    'posts_per_page' => $max_posts,
    'post_type' => array( $content_type ),
    'order_by' => 'post_date',
    'order' => 'DESC',
    'post_status' => 'publish',
);

$new_post_array = get_posts( $args );

if ( count($new_post_array) > 0 ) {
    return $new_post_array;
} else {
    return $posts;
}

}
add_filter( 'ridizain_get_featured_posts', 'ridizain_filter_featured_posts', 999, 1 );

/*
 * Now lets add the meta box for tags on Page edit screen.
 */
if( ! function_exists('ridizain_register_taxonomy') ){

    function ridizain_register_taxonomy() {
        register_taxonomy_for_object_type('post_tag', 'page');
    }
    add_action('admin_init', 'ridizain_register_taxonomy');
}

/**
 * Adjust content_width value for image attachment template.
 *
 * @since Ridizain 1.0
 *
 * @return void
 */
function ridizain_content_width() {
	if ( is_attachment() && wp_attachment_is_image() ) {
		$GLOBALS['content_width'] = 915;
	}
}
add_action( 'template_redirect', 'ridizain_content_width' );

/**
 * Getter function for Featured Content Plugin.
 *
 * @since Ridizain 1.0
 *
 * @return array An array of WP_Post objects.
 */
function ridizain_get_featured_posts() {
	/**
	 * Filter the featured posts to return in Ridizain.
	 *
	 * @since Ridizain 1.0
	 *
	 * @param array|bool $posts Array of featured posts, otherwise false.
	 */
	return apply_filters( 'ridizain_get_featured_posts', array() );
}

/**
 * A helper conditional function that returns a boolean value.
 *
 * @since Ridizain 1.0
 *
 * @return bool Whether there are featured posts.
 */
function ridizain_has_featured_posts() {
	return ! is_paged() && (bool) ridizain_get_featured_posts();
}

/**
 * Register three Ridizain widget areas.
 *
 * @since Ridizain 1.0
 *
 * @return void
 */
function ridizain_widgets_init() {
	require get_template_directory() . '/inc/widgets.php';
	register_widget( 'Ridizain_Ephemera_Widget' );
	register_widget( 'Ridizain_RecentPostWidget' );

	register_sidebar( array(
		'name'          => __( 'Primary Sidebar', 'ridizain' ),
		'id'            => 'sidebar-1',
		'description'   => __( 'Main sidebar that appears on the left.', 'ridizain' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h1 class="widget-title">',
		'after_title'   => '</h1>',
	) );
	register_sidebar( array(
		'name'          => __( 'Content Sidebar', 'ridizain' ),
		'id'            => 'sidebar-2',
		'description'   => __( 'Additional sidebar that appears on the right.', 'ridizain' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h1 class="widget-title">',
		'after_title'   => '</h1>',
	) );
	register_sidebar( array(
		'name'          => __( 'Footer Widget Area', 'ridizain' ),
		'id'            => 'sidebar-3',
		'description'   => __( 'Appears in the footer section of the site.', 'ridizain' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h1 class="widget-title">',
		'after_title'   => '</h1>',
	) );
}
add_action( 'widgets_init', 'ridizain_widgets_init' );
add_filter('widget_text', 'do_shortcode');

function ridizain_blog_feed_cat( $query ) {
    if ( is_admin() || ! $query->is_main_query() )
        return;

    if ( is_home() ) {
        // Display posts in ascending order on the blog feed/archive
        $query->set( 'category_name', get_theme_mod( 'ridizain_feed_cat' ));
        return;
    }
}
add_action( 'pre_get_posts', 'ridizain_blog_feed_cat', 1 );

// Ridizain Custom Excerpt
function ridizain_trim_excerpt($ridizain_excerpt) {
  $raw_excerpt = $ridizain_excerpt;
  if ( '' == $ridizain_excerpt ) {
    $ridizain_excerpt = get_the_content(''); // Original Content
    $ridizain_excerpt = strip_shortcodes($ridizain_excerpt); // Minus Shortcodes
    $ridizain_excerpt = apply_filters('the_content', $ridizain_excerpt); // Filters
    $ridizain_excerpt = str_replace(']]>', ']]&gt;', $ridizain_excerpt); // Replace
    
    if (get_theme_mod( 'ridizain_feed_excerpt_length' )) :
		$ridizain_feedlimit = get_theme_mod( 'ridizain_feed_excerpt_length' );
	else :
		$ridizain_feedlimit = '85';
	endif;
    $excerpt_length = apply_filters('excerpt_length', $ridizain_feedlimit); // Length
    $ridizain_excerpt = wp_trim_words( $ridizain_excerpt, $excerpt_length );
    
    // Use First Video as Excerpt
    $postcustom = get_post_custom_keys();
    if ($postcustom){
      $i = 1;
      foreach ($postcustom as $key){
        if (strpos($key,'oembed')){
          foreach (get_post_custom_values($key) as $video){
            if ($i == 1){
              $ridizain_excerpt = $video.$ridizain_excerpt;
            }
          $i++;
          }
        }  
      }
    }
  }
  return apply_filters('ridizain_trim_excerpt', $ridizain_excerpt, $raw_excerpt);
}
remove_filter('get_the_excerpt', 'wp_trim_excerpt');
add_filter('get_the_excerpt', 'ridizain_trim_excerpt');

// Lets do a separate excerpt length for the alternative recent post widget
function ridizain_recentpost_excerpt () {
	$theContent = trim(strip_tags(get_the_content()));
		$output = str_replace( '"', '', $theContent);
		$output = str_replace( '\r\n', ' ', $output);
		$output = str_replace( '\n', ' ', $output);
			if (get_theme_mod( 'ridizain_recentpost_excerpt_length' )) :
			$limit = get_theme_mod( 'ridizain_recentpost_excerpt_length' );
			else : 
			$limit = '15';
			endif;
			$content = explode(' ', $output, $limit);
			array_pop($content);
		$content = implode(" ",$content)."  ";
	return strip_tags($content, ' ');
}


// Lets do a separate excerpt length for the alternative recent post widget
function ridizain_featured_excerpt () {
	$theContent = trim(strip_tags(get_the_content()));
		$output = str_replace( '"', '', $theContent);
		$output = str_replace( '\r\n', ' ', $output);
		$output = str_replace( '\n', ' ', $output);
			$limit = '15';
			$content = explode(' ', $output, $limit);
			array_pop($content);
		$content = implode(" ",$content)."  ";
	return strip_tags($content, ' ');
}

/**
 * Register Lato Google font for Ridizain.
 *
 * @since Ridizain 1.0
 *
 * @return string
 */
function ridizain_font_url() {
	$font_url = '';
	/*
	 * Translators: If there are characters in your language that are not supported
	 * by Lato, translate this to 'off'. Do not translate into your own language.
	 */
	if ( 'off' !== _x( 'on', 'Lato font: on or off', 'ridizain' ) ) {
		$font_url = add_query_arg( 'family', urlencode( 'Lato:300,400,700,900,300italic,400italic,700italic' ), "//fonts.googleapis.com/css" );
	}

	return $font_url;
}

/**
 * Enqueue scripts and styles for the front end.
 *
 * @since Ridizain 1.0
 *
 * @return void
 */
 
function ridizain_ie_support_header() {
    echo '<!--[if lt IE 9]>'. "\n";
    echo '<script src="' . esc_url( get_template_directory_uri() . '/js/html5shiv.js' ) . '"></script>'. "\n";
    echo '<![endif]-->'. "\n";
}
add_action( 'wp_head', 'ridizain_ie_support_header', 1 ); 
 
function ridizain_scripts() {
	global $wp_styles;
	// Bump this when changes are made to bust cache
    $ridizain_theme = wp_get_theme();
    $version = $ridizain_theme->get( 'Version' );
	// Add Lato font, used in the main stylesheet.
	wp_enqueue_style( 'ridizain-lato', ridizain_font_url(), array(), null );

	// Add Genericons font, used in the main stylesheet.
	wp_enqueue_style( 'genericons', get_template_directory_uri() . '/genericons/genericons.css', array(), $version );

	// Load our main stylesheet.
	wp_enqueue_style( 'ridizain-style', get_stylesheet_uri(), array( 'genericons' ) );

	// Load the Internet Explorer specific stylesheet.
	wp_enqueue_style( 'ridizain-ie', get_template_directory_uri() . '/css/ie.css', array( 'ridizain-style', 'genericons' ), $version );
	wp_style_add_data( 'ridizain-ie', 'conditional', 'lt IE 9' );
		
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	if ( is_singular() && wp_attachment_is_image() ) {
		wp_enqueue_script( 'ridizain-keyboard-image-navigation', get_template_directory_uri() . '/js/keyboard-image-navigation.js', array( 'jquery' ), $version );
	}

	if ( is_active_sidebar( 'sidebar-3' ) ) {
		wp_enqueue_script( 'jquery-masonry' );
	}
	
	if ( is_front_page() && 'slider' == get_theme_mod( 'featured_content_layout' ) ) {
		wp_enqueue_script( 'ridizain-slider', get_template_directory_uri() . '/js/slider.js', array( 'jquery' ), $version, true );
		wp_localize_script( 'ridizain-slider', 'featuredSliderDefaults', array(
			'prevText' => __( 'Previous', 'ridizain' ),
			'nextText' => __( 'Next', 'ridizain' )
		) );
	}
	
	wp_enqueue_script( 'ridizain-script', get_template_directory_uri() . '/js/functions.js', array( 'jquery' ), $version, true );
}
add_action( 'wp_enqueue_scripts', 'ridizain_scripts' );

if ( get_theme_mod( 'ridizain_fitvids_enable' ) != 0 ) {

function ridizain_fitvids_scripts() {
    $ridizain_theme = wp_get_theme();
    $version = $ridizain_theme->get( 'Version' );
    wp_enqueue_script( 'ridizain-fitvids', get_template_directory_uri() . '/js/jquery.fitvids.js', array( 'jquery' ), $version, true );
} // end fitvids_scripts
add_action('wp_enqueue_scripts','ridizain_fitvids_scripts', 20);
// selector script
function ridizain_fitthem() { ?>
   	<script type="text/javascript">
   	jQuery(document).ready(function() {
   		jQuery('<?php echo get_theme_mod('ridizain_fitvids_selector'); ?>').fitVids({ customSelector: '<?php echo stripslashes(get_theme_mod('ridizain_fitvids_custom_selector')); ?>'});
   	});
   	</script>
<?php } // End selector script
add_action( 'wp_footer', 'ridizain_fitthem', 210 );
}

function ridizain_extra_scripts() {
if ( is_home() ) : 
if ( get_theme_mod( 'ridizain_fullwidth_blog_feed' ) != 0 ) {
    echo
	    '<style>
	        .content-sidebar { display: none;}
			.full-width.post-thumbnail img {
	            width: 100%;
            }
	    </style>';
    }
endif;

if ( is_singular() && !is_page() ) : 
if ( get_theme_mod( 'ridizain_fullwidth_single_post' ) != 0 ) {
    echo
	    '<style>
	        .content-sidebar { display: none;}
			.full-width.post-thumbnail img {
	            width: 100%;
            }
	    </style>';
    }
endif;
}
add_action( 'wp_head', 'ridizain_extra_scripts' );
/**
 * Enqueue Google fonts style to admin screen for custom header display.
 *
 * @since Ridizain 1.0
 *
 * @return void
 */
function ridizain_admin_fonts() {
	wp_enqueue_style( 'ridizain-lato', ridizain_font_url(), array(), null );
}
add_action( 'admin_print_scripts-appearance_page_custom-header', 'ridizain_admin_fonts' );

if ( ! function_exists( 'ridizain_the_attached_image' ) ) :
/**
 * Print the attached image with a link to the next attached image.
 *
 * @since Ridizain 1.0
 *
 * @return void
 */
function ridizain_the_attached_image() {
	$post                = get_post();
	/**
	 * Filter the default Ridizain attachment size.
	 *
	 * @since Ridizain 1.0
	 *
	 * @param array $dimensions {
	 *     An array of height and width dimensions.
	 *
	 *     @type int $height Height of the image in pixels. Default 810.
	 *     @type int $width  Width of the image in pixels. Default 810.
	 * }
	 */
	$attachment_size     = apply_filters( 'ridizain_attachment_size', array( 810, 810 ) );
	$next_attachment_url = wp_get_attachment_url();

	/*
	 * Grab the IDs of all the image attachments in a gallery so we can get the URL
	 * of the next adjacent image in a gallery, or the first image (if we're
	 * looking at the last image in a gallery), or, in a gallery of one, just the
	 * link to that image file.
	 */
	$attachment_ids = get_posts( array(
		'post_parent'    => $post->post_parent,
		'fields'         => 'ids',
		'numberposts'    => -1,
		'post_status'    => 'inherit',
		'post_type'      => 'attachment',
		'post_mime_type' => 'image',
		'order'          => 'ASC',
		'orderby'        => 'menu_order ID',
	) );

	// If there is more than 1 attachment in a gallery...
	if ( count( $attachment_ids ) > 1 ) {
		foreach ( $attachment_ids as $attachment_id ) {
			if ( $attachment_id == $post->ID ) {
				$next_id = current( $attachment_ids );
				break;
			}
		}

		// get the URL of the next image attachment...
		if ( $next_id ) {
			$next_attachment_url = get_attachment_link( $next_id );
		}

		// or get the URL of the first image attachment.
		else {
			$next_attachment_url = get_attachment_link( array_shift( $attachment_ids ) );
		}
	}

	printf( '<a href="%1$s" rel="attachment">%2$s</a>',
		esc_url( $next_attachment_url ),
		wp_get_attachment_image( $post->ID, $attachment_size )
	);
}
endif;

if ( ! function_exists( 'ridizain_list_authors' ) ) :
/**
 * Print a list of all site contributors who published at least one post.
 *
 * @since Ridizain 1.0
 *
 * @return void
 */
function ridizain_list_authors() {
	$contributor_ids = get_users( array(
		'fields'  => 'ID',
		'orderby' => 'post_count',
		'order'   => 'DESC',
		'who'     => 'authors',
	) );

	foreach ( $contributor_ids as $contributor_id ) :
		$post_count = count_user_posts( $contributor_id );

		// Move on if user has not published a post (yet).
		if ( ! $post_count ) {
			continue;
		}
	?>

	<div class="contributor">
		<div class="contributor-info">
			<div class="contributor-avatar"><?php echo get_avatar( $contributor_id, 132 ); ?></div>
			<div class="contributor-summary">
				<h2 class="contributor-name"><?php echo get_the_author_meta( 'display_name', $contributor_id ); ?></h2>
				<p class="contributor-bio">
					<?php echo get_the_author_meta( 'description', $contributor_id ); ?>
				</p>
				<a class="contributor-posts-link" href="<?php echo esc_url( get_author_posts_url( $contributor_id ) ); ?>">
					<?php printf( _n( '%d Article', '%d Articles', $post_count, 'ridizain' ), $post_count ); ?>
				</a>
			</div><!-- .contributor-summary -->
		</div><!-- .contributor-info -->
	</div><!-- .contributor -->

	<?php
	endforeach;
}
endif;

/**
 * Extend the default WordPress body classes.
 *
 * Adds body classes to denote:
 * 1. Single or multiple authors.
 * 2. Presence of header image.
 * 3. Index views.
 * 4. Full-width content layout.
 * 5. Presence of footer widgets.
 * 6. Single views.
 * 7. Featured content layout.
 *
 * @since Ridizain 1.0
 *
 * @param array $classes A list of existing body class values.
 * @return array The filtered body class list.
 */
function ridizain_body_classes( $classes ) {
global $content_width;

	if ( is_multi_author() ) {
		$classes[] = 'group-blog';
	}

	if ( get_header_image() ) {
		$classes[] = 'header-image';
	} else {
		$classes[] = 'masthead-fixed';
	}

	if ( is_post_type_archive( 'post' ) || is_search() || is_home() ) {
		$classes[] = 'list-view';
	}

	if ( !is_active_sidebar( 'sidebar-2' ) 
	   || is_page_template( 'page-templates/full-width.php' ) 
	   || is_page_template( 'page-templates/contributors.php' ) 
	   || is_attachment() ) {
	    $classes[] = 'full-width';
	}
	if (is_home() ) : 
    if (get_theme_mod( 'ridizain_fullwidth_blog_feed' ) != 0 ) {
	$classes[] = 'full-width';
	} endif;
	
	if (is_singular() && !is_page() ) : 
    if (get_theme_mod( 'ridizain_fullwidth_single_post' ) != 0 ) {
    $classes[] = 'full-width';
    } endif;
	
	if ( is_active_sidebar( 'sidebar-3' ) ) {
		$classes[] = 'footer-widgets';
	}

	if ( is_singular() && ! is_front_page() ) {
		$classes[] = 'singular';
	}

	if ( is_front_page() && 'slider' == get_theme_mod( 'featured_content_layout' ) ) {
		$classes[] = 'slider';
	} elseif ( is_front_page() ) {
		$classes[] = 'grid';
	}

	return $classes;
}
add_filter( 'body_class', 'ridizain_body_classes' );

/**
 * Extend the default WordPress post classes.
 *
 * Adds a post class to denote:
 * Non-password protected page with a post thumbnail.
 *
 * @since Ridizain 1.0
 *
 * @param array $classes A list of existing post class values.
 * @return array The filtered post class list.
 */
function ridizain_post_classes( $classes ) {
	if ( ! post_password_required() && has_post_thumbnail() ) {
		$classes[] = 'has-post-thumbnail';
	}

	return $classes;
}
add_filter( 'post_class', 'ridizain_post_classes' );

/**
 * Create a nicely formatted and more specific title element text for output
 * in head of document, based on current view.
 *
 * @since Ridizain 1.0
 *
 * @param string $title Default title text for current view.
 * @param string $sep Optional separator.
 * @return string The filtered title.
 */
function ridizain_wp_title( $title, $sep ) {
	global $paged, $page;

	if ( is_feed() ) {
		return $title;
	}

	// Add the site name.
	$title .= get_bloginfo( 'name' );

	// Add the site description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) ) {
		$title = "$title $sep $site_description";
	}

	// Add a page number if necessary.
	if ( $paged >= 2 || $page >= 2 ) {
		$title = "$title $sep " . sprintf( __( 'Page %s', 'ridizain' ), max( $paged, $page ) );
	}

	return $title;
}
add_filter( 'wp_title', 'ridizain_wp_title', 10, 2 );

// Implement Custom Header features.
require get_template_directory() . '/inc/custom-header.php';

// Custom template tags for this theme.
require get_template_directory() . '/inc/template-tags.php';

// Add Theme Customizer functionality.
require get_template_directory() . '/inc/customizer.php';

/*
 * Load Theme Hook Alliance files
 */
require( get_template_directory() . '/inc/tha-hooks.php' );