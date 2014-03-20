<?php
/**
 * Ridizain Theme Customizer support
 *
 *
 * @package Ridizain
 * @since Ridizain 1.0
 */

/**
 * Implement Theme Customizer additions and adjustments.
 *
 * @since Ridizain 1.0
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function ridizain_customize_register( $wp_customize ) {
	// Add custom description to Colors and Background sections.
	$wp_customize->get_section( 'colors' )->description           = __( 'Background may only be visible on wide screens.', 'ridizain' );
	$wp_customize->get_section( 'background_image' )->description = __( 'Background may only be visible on wide screens.', 'ridizain' );

	// Add postMessage support for site title and description.
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

	// Rename the label to "Site Title Color" because this only affects the site title in this theme.
	$wp_customize->get_control( 'header_textcolor' )->label = __( 'Site Title &amp; Tagline Color', 'ridizain' );

	// Rename the label to "Display Site Title & Tagline" in order to make this option extra clear.
	$wp_customize->get_control( 'display_header_text' )->label = __( 'Display Site Title &amp; Tagline', 'ridizain' );

	// Theme specific notes
    $wp_customize->add_section( 'ridizain_theme_notes' , array(
		'title'      => __('Ridizain Theme Notes','ridizain'),
		'description' => sprintf( __( 'Welcome & thank you for choosing Ridizain as your site theme. In this section you\'ll find some helpful hints and tips to help you configure your site quickly and easily.
		<br/><br/> <b>Social Icons</b> are configurable via a custom menu. To set up your social profile visit the Appearance >><a href="%1$s"> Menu section</a>, enter your profile urls and add them to the "Social" Menu Location.
		<br/><br/><a href="%2$s" target="_blank" />View Theme Demo</a> | <a href="%3$s" target="_blank" />Get Theme Support</a> ', 'ridizain' ), admin_url( '/nav-menus.php' ), esc_url('http://www.wpstrapcode.com/ridizain/'), esc_url('http://wordpress.org/support/theme/ridizain') ),
		'priority'   => 30,
    ) );
	
	$wp_customize->add_section( 'ridizain_general_options' , array(
       'title'      => __('Ridizain General Options','ridizain'),
	   'description' => sprintf( __( 'Use the following settings to set sitewide options.', 'ridizain' )),
       'priority'   => 31,
    ) );
	
	$wp_customize->add_section( 'ridizain_content_options' , array(
       'title'      => __('Ridizain Content Options','ridizain'),
	   'description' => sprintf( __( 'Use the following settings to set content options.', 'ridizain' )),
       'priority'   => 32,
    ) );
	
	// Add the featured content section in case it's not already there.
	$wp_customize->add_section( 'featured_content', array(
		'title'       => __( 'Ridizain Featured Content', 'ridizain' ),
		'description' => sprintf( __( 'Use a <a href="%1$s">tag</a> to feature your posts. If no posts match the tag, <a href="%2$s">sticky posts</a> will be displayed instead.', 'ridizain' ), admin_url( '/edit.php?tag=featured' ), admin_url( '/edit.php?show_sticky=1' ) ),
		'priority'    => 33,
	) );
		
	$wp_customize->add_section( 'ridizain_fitvids_options' , array(
       'title'      => __('Ridizain FitVids Options','ridizain'),
	   'description' => sprintf( __( 'Use the following settings to set fitvids script options. Options are: Enable script, Set selector (Default is .post) and set custom selector (optional) for other areas like .sidebar or a custom section!', 'ridizain' )),
       'priority'   => 34,
    ) );
	
	//  Logo Image Upload
    $wp_customize->add_setting('ridizain_logo_image', array(
        'default-image'  => '',
    ));
 
    $wp_customize->add_control(
        new WP_Customize_Image_Control(
            $wp_customize,
            'ridizain_logo',
            array(
               'label'          => __( 'Upload a logo', 'ridizain' ),
               'section'        => 'title_tagline',
               'settings'       => 'ridizain_logo_image',
            )
        )
    );
	
	$wp_customize->add_setting(
    'ridizain_logo_alt_text', array (
		'sanitize_callback' => 'sanitize_text_field',
    ));
	
	$wp_customize->add_control(
    'ridizain_logo_alt_text',
    array(
        'type' => 'text',
		'default' => '',
        'label' => __('Enter Logo Alt Text Here', 'ridizain'),
        'section' => 'title_tagline',
		'priority' => 20,
        )
    );
	
	// Set Blog feed to full width i.e. hide the content sidebar.
	$wp_customize->add_setting(
        'ridizain_fullwidth_blog_feed', array (
			'sanitize_callback' => 'ridizain_sanitize_checkbox',
		)
    );

    $wp_customize->add_control(
        'ridizain_fullwidth_blog_feed',
    array(
        'type'     => 'checkbox',
        'label'    => __('Check to hide the sidebar on the blog feed', 'ridizain'),
        'section'  => 'ridizain_general_options',
		'priority' => 1,
        )
    );
	
	$wp_customize->add_setting(
    'ridizain_feed_thumb_height',
    array(
        'default' => '572',
		'sanitize_callback' => 'ridizain_sanitize_integer'
    ));
	
	$wp_customize->add_control(
    'ridizain_feed_thumb_height',
    array(
        'label' => __('Set Overall Content Image max-height (numbers only!) - Full Width Blog Feed Only.','ridizain'),
        'section' => 'ridizain_general_options',
		'priority' => 2,
        'type' => 'text',
    ));
	
	// Set Blog feed to full width i.e. hide the content sidebar.
	$wp_customize->add_setting(
        'ridizain_fullwidth_single_post', array (
			'sanitize_callback' => 'ridizain_sanitize_checkbox',
		)
    );

    $wp_customize->add_control(
        'ridizain_fullwidth_single_post',
    array(
        'type'     => 'checkbox',
        'label'    => __('Check to show full width single post', 'ridizain'),
        'section'  => 'ridizain_general_options',
		'priority' => 3,
        )
    );
	
	$wp_customize->add_setting(
        'ridizain_singular_thumb_visibility', array (
			'sanitize_callback' => 'ridizain_sanitize_checkbox',
		)
    );

    $wp_customize->add_control(
        'ridizain_singular_thumb_visibility',
    array(
        'type'     => 'checkbox',
        'label'    => __('Check to hide thumbnail on single post', 'ridizain'),
        'section'  => 'ridizain_general_options',
		'priority' => 4,
        )
    );
			
	$wp_customize->add_setting(
    'ridizain_singular_thumb_height',
    array(
        'default' => '572',
		'sanitize_callback' => 'ridizain_sanitize_integer'
    ));
	
	$wp_customize->add_control(
    'ridizain_singular_thumb_height',
    array(
        'label' => __('Set Single Post Featured Image max-height (numbers only!) - Full Width Posts Only.','ridizain'),
        'section' => 'ridizain_general_options',
		'priority' => 5,
        'type' => 'text',
    ));
	
	// Begin Content Options
	// Blog Feed Category Selector
    $categories = get_categories();
	$cats = array( 'All Categories' );
	$i = 0;
	foreach($categories as $category){
		if($i==0){
			$default = $category->slug;
			$i++;
		}
		$cats[$category->slug] = $category->name;
	}
 
	$wp_customize->add_setting('ridizain_feed_cat', array(
		'default'  => '',
	));
	$wp_customize->add_control( 'ridizain_feed_cat', array(
		'settings' => 'ridizain_feed_cat',
		'label'   => __('Select Blog Feed Category:', 'ridizain'),
		'section'  => 'ridizain_content_options',
		'priority' => 1,
		'type'    => 'select',
		'choices' => $cats,
	));
	
	$wp_customize->add_setting(
    'ridizain_feed_excerpt_length'
    );

    $wp_customize->add_control(
    'ridizain_feed_excerpt_length',
    array(
        'type' => 'text',
		'default' => '85',
		'sanitize_callback' => 'ridizain_sanitize_integer',
        'label' => __('Blog Feed Excerpt Length', 'ridizain'),
        'section' => 'ridizain_content_options',
		'priority' => 2,
        )
    );
	
	$wp_customize->add_setting(
    'ridizain_recentpost_excerpt_length'
    );
	
	$wp_customize->add_control(
    'ridizain_recentpost_excerpt_length',
    array(
        'type' => 'text',
		'default' => '15',
		'sanitize_callback' => 'ridizain_sanitize_integer',
        'label' => __('Recent Post Widget Excerpt Length', 'ridizain'),
        'section' => 'ridizain_content_options',
		'priority' => 3,
        )
    );

	// Add the featured content layout setting and control.
	$wp_customize->add_setting( 'featured_content_location', array(
		'default'           => 'default',
		'sanitize_callback' => 'ridizain_sanitize_location',
	) );

	$wp_customize->add_control( 'featured_content_location', array(
		'label'   => __( 'Featured Location', 'ridizain' ),
		'section' => 'featured_content',
		'priority' => 1,
		'type'    => 'select',
		'choices' => array(
			'default'   => __( 'Default - Above Content/Sidebar',   'ridizain' ),
			'fullwidth' => __( 'Below Menu - Fullwidth', 'ridizain' ),
		),
	) );
	
	$wp_customize->add_setting(
        'featured_content_boxed', array (
			'sanitize_callback' => 'ridizain_sanitize_checkbox',
		)
    );

    $wp_customize->add_control(
        'featured_content_boxed',
    array(
        'type'     => 'checkbox',
        'label'    => __('Give Featured Content The Boxed Look? - Off The Edges!', 'ridizain'),
        'section'  => 'featured_content',
		'priority' => 2,
        )
    );
	
	$wp_customize->add_setting( 'featured_content_layout', array(
		'default'           => 'grid',
		'sanitize_callback' => 'ridizain_sanitize_layout',
	) );

	$wp_customize->add_control( 'featured_content_layout', array(
		'label'   => __( 'Layout', 'ridizain' ),
		'section' => 'featured_content',
		'priority' => 3,
		'type'    => 'select',
		'choices' => array(
			'grid'   => __( 'Grid',   'ridizain' ),
			'slider' => __( 'Slider', 'ridizain' ),
		),
	) );
	
	// Primary post type to be featured.
	$post_types = get_post_types();
	$cpt = array( 'Select Post Type To Feature' );
	    $i = 0;
	    foreach($post_types as $post_type){
		if ( in_array( $post_type, array( 'attachment', 'revision', 'nav_menu_item' ) ) ) continue;
		    if($i==0){
			    $default = $post_type;
			    $i++;
		    }
		    $cpt[$post_type] = $post_type;
	    }
 
	$wp_customize->add_setting('featured_content_custom_type', array(
	    'default'  => 'post',
	));
	
	$wp_customize->add_control( 'featured_content_custom_type', array(
	    'settings' => 'featured_content_custom_type',
	    'label'   => __('Select Post Type - posts, pages & custom post types are supported!', 'ridizain'),
	    'section'  => 'featured_content',
	    'priority' => 4,
	    'type'    => 'select',
	    'choices' => $cpt,
	));
	
	$wp_customize->add_setting(
        'ridizain_num_grid_columns', array (
			'sanitize_callback' => 'ridizain_sanitize_checkbox',
		)
    );

    $wp_customize->add_control(
        'ridizain_num_grid_columns',
            array(
                'type'     => 'checkbox',
                'label'    => __('Switch Featured Grid Columns to 4?', 'ridizain'),
                'section'  => 'featured_content',
		        'priority' => 5,
            )
    );
	
	$wp_customize->add_setting( 'num_posts_grid', array( 
	    'default' => '6',
        'sanitize_callback' => 'ridizain_sanitize_integer'		
	) );
	
	$wp_customize->add_control( 'num_posts_grid', array(
        'label' => __( 'Number of posts for grid', 'text-domain'),
        'section' => 'featured_content',
		'priority' => 6,
        'settings' => 'num_posts_grid',
    ) );
	
    $wp_customize->add_setting( 'num_posts_slider', array( 
	    'default' => '6',
        'sanitize_callback' => 'ridizain_sanitize_integer'		
	) );
	
	$wp_customize->add_control( 'num_posts_slider', array(
        'label' => __( 'Number of posts for slider', 'text-domain'),
        'section' => 'featured_content',
		'priority' => 7,
        'settings' => 'num_posts_slider',
    ) );
	
	$wp_customize->add_setting(
        'ridizain_featured_excerpt_visibility', array (
			'sanitize_callback' => 'ridizain_sanitize_checkbox',
		)
    );

    $wp_customize->add_control(
        'ridizain_featured_excerpt_visibility',
    array(
        'type' => 'checkbox',
        'label' => __('Show Featured Posts Excerpts?', 'ridizain'),
        'section' => 'featured_content',
		'priority' => 8,
        )
    );
	
	$wp_customize->add_setting(
        'ridizain_featured_visibility', array (
			'sanitize_callback' => 'ridizain_sanitize_checkbox',
		)
    );

    $wp_customize->add_control(
        'ridizain_featured_visibility',
    array(
        'type' => 'checkbox',
        'label' => __('Show Featured Posts In Blog Feed?', 'ridizain'),
        'section' => 'featured_content',
		'priority' => 25,
        )
    );
	
	// Begin Slider Options
	$wp_customize->add_setting(
        'ridizain_enable_autoslide', array (
			'sanitize_callback' => 'ridizain_sanitize_checkbox',
		)
    );

    $wp_customize->add_control(
        'ridizain_enable_autoslide',
    array(
        'type'     => 'checkbox',
        'label'    => __('Check to set Slider to Auto Slide', 'ridizain'),
        'section'  => 'featured_content',
		'priority' => 26,
        )
    );
	
	$wp_customize->add_setting( 'ridizain_slider_transition', array(
		'default' => 'slide',
		'sanitize_callback' => 'ridizain_sanitize_transition'
	) );

	
	$wp_customize->add_control( 'ridizain_slider_transition', array(
    'label'   => __( 'Slider Transition', 'ridizain' ),
    'section' => 'featured_content',
	'priority' => 27,
    'type'    => 'radio',
        'choices' => array(
            'slide' => __( 'Slide', 'ridizain' ),
            'fade' => __( 'Fade', 'ridizain' ),
        ),
    ));
	
	$wp_customize->add_setting(
        'ridizain_slider_height',
    array(
        'default' => '500',
		'sanitize_callback' => 'ridizain_sanitize_integer'
    ));
	
	$wp_customize->add_control(
        'ridizain_slider_height',
    array(
        'label' => __('Set Slider max-height (numbers only!) - Default is 500!','ridizain'),
        'section' => 'featured_content',
		'priority' => 28,
        'type' => 'text',
    ));
	
	// Begin Ridizain color settings
	$wp_customize->add_setting('ridizain_site_header_bgcolor', array(
        'default'           => 'ffffff',
        'sanitize_callback' => 'sanitize_hex_color',
        'capability'        => 'edit_theme_options',
    ));
 
    $wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'ridizain_site_header_bgcolor', array(
        'label'    => __('Site Header Background Color', 'ridizain'),
        'section'  => 'colors',
		'priority' => 2,
        'settings' => 'ridizain_site_header_bgcolor',
    )));
	
	$wp_customize->add_setting('ridizain_accent_color', array(
        'default'           => '41a62a',
        'sanitize_callback' => 'sanitize_hex_color',
        'capability'        => 'edit_theme_options',
    ));
 
    $wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'ridizain_accent_color', array(
        'label'    => __('Site Accent Color', 'ridizain'),
        'section'  => 'colors',
		'priority' => 20,
        'settings' => 'ridizain_accent_color',
    )));
	
	$wp_customize->add_setting('ridizain_accent_hover', array(
        'default'           => '41a62a',
        'sanitize_callback' => 'sanitize_hex_color',
        'capability'        => 'edit_theme_options',
    ));
 
    $wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'ridizain_accent_hover', array(
        'label'    => __('Site Accent Hover Color', 'ridizain'),
        'section'  => 'colors',
		'priority' => 21,
        'settings' => 'ridizain_accent_hover',
    )));
		
	$wp_customize->add_setting('ridizain_links_color', array(
        'default'           => '000000',
        'sanitize_callback' => 'sanitize_hex_color',
        'capability'        => 'edit_theme_options',
    ));
 
    $wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'ridizain_links_color', array(
        'label'    => __('Menu Links Color', 'ridizain'),
        'section'  => 'colors',
		'priority' => 23,
        'settings' => 'ridizain_links_color',
    )));
	
	$wp_customize->add_setting('ridizain_links_hover', array(
        'default'           => '41a62a',
        'sanitize_callback' => 'sanitize_hex_color',
        'capability'        => 'edit_theme_options',
    ));
 
    $wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'ridizain_links_hover', array(
        'label'    => __('Menu Links Hover Color', 'ridizain'),
        'section'  => 'colors',
		'priority' => 24,
        'settings' => 'ridizain_links_hover',
    )));
	
	$wp_customize->add_setting('ridizain_links_active_color', array(
        'default'           => '41a62a',
        'sanitize_callback' => 'sanitize_hex_color',
        'capability'        => 'edit_theme_options',
    ));
 
    $wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'ridizain_links_active_color', array(
        'label'    => __('Menu Links Active Color', 'ridizain'),
        'section'  => 'colors',
		'priority' => 25,
        'settings' => 'ridizain_links_active_color',
    )));
	
	$wp_customize->add_setting('ridizain_menu_toggle_color', array(
        'default'           => '000000',
        'sanitize_callback' => 'sanitize_hex_color',
        'capability'        => 'edit_theme_options',
    ));
 
    $wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'ridizain_menu_toggle_color', array(
        'label'    => __('Small Menu Toggle Color', 'ridizain'),
        'section'  => 'colors',
		'priority' => 26,
        'settings' => 'ridizain_menu_toggle_color',
    )));
		
	$wp_customize->add_setting('ridizain_featured_content_color', array(
        'default'           => 'ffffff',
        'sanitize_callback' => 'sanitize_hex_color',
        'capability'        => 'edit_theme_options',
    ));
	
	$wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'ridizain_featured_content_color', array(
        'label'    => __('Featured Content Color', 'ridizain'),
        'section'  => 'colors',
		'priority' => 27,
        'settings' => 'ridizain_featured_content_color',
    )));
	
	$wp_customize->add_setting('ridizain_featured_content_hover', array(
        'default'           => '000000',
        'sanitize_callback' => 'sanitize_hex_color',
        'capability'        => 'edit_theme_options',
    ));
 
    $wp_customize->add_control( new WP_Customize_Color_Control($wp_customize, 'ridizain_featured_content_hover', array(
        'label'    => __('Featured Content Hover Color', 'ridizain'),
        'section'  => 'colors',
		'priority' => 28,
        'settings' => 'ridizain_featured_content_hover',
    )));
	
	// Add FitVids to site
    $wp_customize->add_setting(
        'ridizain_fitvids_enable', array (
			'sanitize_callback' => 'ridizain_sanitize_checkbox',
		)
    );

    $wp_customize->add_control(
        'ridizain_fitvids_enable',
    array(
        'type'     => 'checkbox',
        'label'    => __('Enable FitVids?', 'ridizain'),
        'section'  => 'ridizain_fitvids_options',
		'priority' => 1,
        )
    );
	
	$wp_customize->add_setting(
    'ridizain_fitvids_selector',
    array(
        'default' => '.post',
		'sanitize_callback' => 'sanitize_text_field'
    ));
	
	$wp_customize->add_control(
    'ridizain_fitvids_selector',
    array(
        'label' => __('Enter a selector for FitVids - i.e. .post','ridizain'),
        'section' => 'ridizain_fitvids_options',
		'priority' => 2,
        'type' => 'text',
    ));
	
	$wp_customize->add_setting(
    'ridizain_fitvids_custom_selector',
    array(
        'default' => '',
		'sanitize_callback' => 'sanitize_text_field'
    ));
	
	$wp_customize->add_control(
    'ridizain_fitvids_custom_selector',
    array(
        'label' => __('Enter a custom selector for FitVids - i.e. .sidebar','ridizain'),
        'section' => 'ridizain_fitvids_options',
		'priority' => 3,
        'type' => 'text',
    ));
	
	// Theme Notes section
	$wp_customize->add_setting(
        'ridizain_theme_notice'
	);
	
	$wp_customize->add_control(
    'ridizain_theme_notice',
    array(
        'section' => 'ridizain_theme_notes',
		'type'  => 'read-only',
    ));
}
add_action( 'customize_register', 'ridizain_customize_register' );

/**
 * Sanitize the Featured Content layout value.
 *
 * @since Ridizain 1.0
 *
 * @param string $layout Layout type.
 * @return string Filtered layout type (grid|slider).
 */
function ridizain_sanitize_layout( $layout ) {
	if ( ! in_array( $layout, array( 'grid', 'slider' ) ) ) {
		$layout = 'grid';
	}

	return $layout;
}

function ridizain_sanitize_location( $location ) {
	if ( ! in_array( $location, array( 'default', 'fullwidth' ) ) ) {
		$location = 'default';
	}
	return $location;
}

/**
 * Sanitize checkbox
 */
if ( ! function_exists( 'ridizain_sanitize_checkbox' ) ) :
	function ridizain_sanitize_checkbox( $input ) {
		if ( $input == 1 ) {
			return 1;
		} else {
			return 0;
		}
	}
endif;

/**
 * Sanitize checkbox
 */
 if ( ! function_exists( 'ridizain_sanitize_transition' ) ) :
function ridizain_sanitize_transition( $transition ) {
	if ( ! in_array( $transition, array( 'slide', 'fade' ) ) ) {
		$transition = 'slide';
	}
	return $transition;
}
endif;

/**
 * Sanitize the Integer Input values.
 *
 * @since Ridizain 1.0.09
 *
 * @param string $input Integer type.
 */
function ridizain_sanitize_integer( $input ) {
	return absint( $input );
}

/**
 * Bind JS handlers to make Theme Customizer preview reload changes asynchronously.
 *
 * @since Ridizain 1.0
 */
function ridizain_customize_preview_js() {
$version = '1.0.14';
	wp_enqueue_script( 'ridizain_customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), $version, true );
}
add_action( 'customize_preview_init', 'ridizain_customize_preview_js' );

/**
 * Add contextual help to the Themes and Post edit screens.
 *
 * @since Ridizain 1.0
 *
 * @return void
 */
function ridizain_contextual_help() {
	if ( 'admin_head-edit.php' === current_filter() && 'post' !== $GLOBALS['typenow'] ) {
		return;
	}

	get_current_screen()->add_help_tab( array(
		'id'      => 'ridizain',
		'title'   => __( 'Ridizain', 'ridizain' ),
		'content' =>
			'<ul>' .
				'<p>' . sprintf( __( 'Theme Ridizain by <a href="%1$s" target="_blank">WP Strap Code</a> is based on <a href="%2$s" target="_blank">Twenty Fourteen</a> and works much the same, therefore the information and links below remain valid. If however you find anything broken or not functioning as it should then please report via our on site <a href="%3$s" target="_blank">support system</a>', 'ridizain' ), 'http://www.wpstrapcode.com', 'http://wordpress.org/themes/twentyfourteen', 'http://www.wpstrapcode.com/support' ) .'</p>' .
				'<li>' . sprintf( __( 'The home page features your choice of up to 6 posts prominently displayed in a grid or slider, controlled by the <a href="%1$s">featured</a> tag; you can change the tag and layout in <a href="%2$s">Appearance &rarr; Customize</a>. If no posts match the tag, <a href="%3$s">sticky posts</a> will be displayed instead.', 'ridizain' ), admin_url( '/edit.php?tag=featured' ), admin_url( 'customize.php' ), admin_url( '/edit.php?show_sticky=1' ) ) . '</li>' .
				'<li>' . sprintf( __( 'Enhance your site design by using <a href="%s" target="_blank">Featured Images</a> for posts you&rsquo;d like to stand out (also known as post thumbnails). This allows you to associate an image with your post without inserting it. Ridizain uses featured images for posts and pages&mdash;above the title&mdash;and in the Featured Content area on the home page.', 'ridizain' ), 'http://codex.wordpress.org/Post_Thumbnails#Setting_a_Post_Thumbnail' ) . '</li>' .
				'<li>' . sprintf( __( 'For an in-depth tutorial, and more tips and tricks, visit the <a href="%s" target="_blank">Ridizain documentation</a>.', 'ridizain' ), 'http://codex.wordpress.org/Ridizain' ) . '</li>' .
			'</ul>',
	) );
}
add_action( 'admin_head-themes.php', 'ridizain_contextual_help' );
add_action( 'admin_head-edit.php',   'ridizain_contextual_help' );

function ridizain_accent_css(){
  // Get the customizer settings
  $accent_color       = get_theme_mod( 'ridizain_accent_color' );
  $accent_hover       = get_theme_mod( 'ridizain_accent_hover' );
  $site_header        = get_theme_mod( 'ridizain_site_header_bgcolor' );
  $links_color        = get_theme_mod( 'ridizain_links_color' );
  $links_hover        = get_theme_mod( 'ridizain_links_hover' );
  $links_active_color = get_theme_mod( 'ridizain_links_active_color' );
  $menu_toggle        = get_theme_mod( 'ridizain_menu_toggle_color' );
  $featured_color     = get_theme_mod( 'ridizain_featured_content_color' );
  $featured_hover     = get_theme_mod( 'ridizain_featured_content_hover' );
  
  // Make sure colors are properly formatted
  $accent_color        = '#' . str_replace( '#', '', $accent_color );
  $accent_hover        = '#' . str_replace( '#', '', $accent_hover );
  $site_header         = '#' . str_replace( '#', '', $site_header );
  $links_color         = '#' . str_replace( '#', '', $links_color );
  $links_hover         = '#' . str_replace( '#', '', $links_hover );
  $links_active_color  = '#' . str_replace( '#', '', $links_active_color );
  $menu_toggle         = '#' . str_replace( '#', '', $menu_toggle );
  $featured_color      = '#' . str_replace( '#', '', $featured_color );
  $featured_hover      = '#' . str_replace( '#', '', $featured_hover );
  
  // Apply settings to relevant elements  ?> 
    <style>
		button,
        .contributor-posts-link,
        input[type="button"],
        input[type="reset"],
        input[type="submit"] {
	        background-color: <?php echo $accent_color; ?>;
	        color: #fff;
        }

        button:hover,
        button:focus,
        .contributor-posts-link:hover,
        input[type="button"]:hover,
        input[type="button"]:focus,
        input[type="reset"]:hover,
        input[type="reset"]:focus,
        input[type="submit"]:hover,
        input[type="submit"]:focus {
	        background-color: <?php echo $accent_hover; ?>;
	        color: #fff;
        }
		#masthead {
	        background-color: <?php echo $site_header; ?>;
			color: #fff;
        }
		
		.site-title a:hover {
	        color: <?php echo $links_hover; ?> !important;
        }
		.site-navigation a {
	        color: <?php echo $links_color; ?>;
        }

        .site-navigation a:hover {
	        color: <?php echo $links_hover; ?>;
			
		}
		.site-navigation .current_page_item > a,
        .site-navigation .current_page_ancestor > a,
        .site-navigation .current-menu-item > a,
        .site-navigation .current-menu-ancestor > a {
	        color: <?php echo $links_active_color; ?>;
			background: transparent !important;
        }
		.site-navigation .current_page_item > a:hover,
        .site-navigation .current_page_ancestor > a:hover,
        .site-navigation .current-menu-item > a:hover,
        .site-navigation .current-menu-ancestor > a:hover {
	        color: <?php echo $links_color; ?>;
			background: transparent !important;
        }
		.site-navigation .secondary-navigation a:hover {
	        color: <?php echo $links_hover; ?> !important;
		}
		.site-navigation .primary-navigation a:hover {
		    background-color: <?php echo $accent_hover; ?>;
		}
		.site-navigation #ridizain-social a {
		    color: <?php echo $accent_color; ?>;
		}
		.site-navigation #ridizain-social a:hover {
		    color: <?php echo $accent_hover; ?>;
		}
		.primary-navigation .current_page_item > a:hover {
		    color: <?php echo $accent_hover; ?>;
		}
		.primary-navigation ul ul {
		    background-color: <?php echo $accent_color; ?>;
		}
		.primary-navigation li:hover > a {
		    background-color: <?php echo $accent_color; ?>;
			color: <?php echo $links_hover; ?>;
	    }
		.secondary-navigation ul ul {
		    background-color: <?php echo $accent_color; ?>;
	    }
		.secondary-navigation li:hover > a,
	    .secondary-navigation li.focus > a {
		    background-color: transparent;
		    color: <?php echo $links_hover; ?>;
	    }
        
		.primary-navigation ul ul a:hover,
	    .primary-navigation ul ul li.focus > a {
		    background-color: <?php echo $accent_hover; ?>;
		    color: <?php echo $links_hover; ?>;
	    }
	    .secondary-navigation ul ul a:hover,
	    .secondary-navigation ul ul li.focus > a {
		    background-color: <?php echo $site_header; ?>;
		    color: <?php echo $links_hover; ?>;
	    }
		@media screen and (max-width: 783px) { 
		    .primary-navigation ul ul,
		    .primary-navigation li:hover > a,
		    .primary-navigation ul ul a:hover,
	        .primary-navigation ul ul li.focus > a {
		        background-color: transparent;
				color: <?php echo $links_color; ?>;
	        }
		}
		.menu-toggle:before {
	        color: <?php echo $menu_toggle; ?>;
        }
		
		.search-toggle,
        .search-box,
        .search-toggle.active {
	        background-color: <?php echo $accent_color; ?>;
        }

		.search-toggle:hover {
	        background-color: <?php echo $accent_hover; ?>;
        }
		
		.entry-title a,
        .cat-links a,
        .entry-meta a,
        .widget a,
        .widget-title,
        .widget-title a,
		.site-info a,
        .content-sidebar .widget a,
        .content-sidebar .widget .widget-title a,
        .content-sidebar .widget_ridizain_ephemera .entry-meta a,
        .ridizain-recent-post-widget-alt a {
	        color: <?php echo $accent_color; ?>;
        }

        .entry-title a:hover,
        .cat-links a:hover,
        .entry-meta a:hover,
        .entry-content .edit-link a:hover,
        .page-links a:hover,
        .post-navigation a:hover,
        .image-navigation a:hover,
        .widget a:hover,
        .widget-title a:hover,
		.site-info a:hover,
        .content-sidebar .widget a:hover,
        .content-sidebar .widget .widget-title a:hover,
        .content-sidebar .widget_ridizain_ephemera .entry-meta a:hover,
        .ridizain-recent-post-widget-alt a:hover {
	         color: <?php echo $accent_hover; ?>;
        }
		.content-sidebar .widget .widget-title {
	        border-top: 5px solid <?php echo $accent_color; ?>;
	    }
		.content-sidebar .widget_ridizain_ephemera .widget-title:before	{
	        background-color: <?php echo $accent_color; ?>;
	    }
		.read-more.button{
		    border: 3px solid <?php echo $accent_color; ?>;
			background: <?php echo $accent_color; ?>;
		}
		.read-more.button:hover {
            border: 3px solid <?php echo $accent_hover; ?>;
			background: <?php echo $accent_hover; ?>;
	    }
		.featured-content .entry-header {
	        background-color: <?php echo $accent_color; ?>;
			border-color: <?php echo $accent_color; ?> !important;
        }
        .featured-content a {
	        color: <?php echo $featured_color; ?>;
        }
        .featured-content a:hover {
	        color: <?php echo $featured_hover; ?>;
        }
        .featured-content .entry-meta {
	        color: <?php echo $featured_color; ?>;
        }
		.slider-control-paging a:before {
	        background-color: <?php echo $featured_color; ?>;
		}
		.slider-control-paging a:hover:before {
	        background-color: <?php echo $accent_hover; ?>;
        }
        .slider-control-paging .slider-active:before,
        .slider-control-paging .slider-active:hover:before {
	        background-color: <?php echo $featured_hover; ?>;
        }
		.slider-direction-nav a {
	        background-color: <?php echo $accent_color; ?>;
        }
        .slider-direction-nav a:hover {
	        background-color: <?php echo $accent_hover; ?>;
        }
		.slider-direction-nav a:before {
	        color: <?php echo $featured_color; ?>;
        }
		.slider-direction-nav a:hover:before {
	        color: <?php echo $accent_color; ?>;
        }
		.paging-navigation {
	        border-top: 5px solid <?php echo $accent_color; ?>;
        }
		.paging-navigation .page-numbers.current {
	        border-top: 5px solid <?php echo $accent_hover; ?>;
        }
        .paging-navigation a:hover {
	        border-top: 5px solid <?php echo $accent_hover; ?>;
	        color: <?php echo $links_color; ?>;
        }
		.entry-meta .tag-links a {
            background-color: <?php echo $accent_color; ?>;
        }
		.entry-meta .tag-links a:before { 
		    border-right-color: <?php echo $accent_color; ?>;
		}
        .entry-meta .tag-links a:hover {
            background-color: <?php echo $accent_hover; ?>;
        }
		.entry-meta .tag-links a:hover:before {
	        border-right-color: <?php echo $accent_hover; ?>;
        }
		.entry-meta .tag-links a:hover:after {
		    background-color: <?php echo $links_hover; ?>;
		}
	</style>
<?php }
add_action( 'wp_head', 'ridizain_accent_css', 210 );


if ( get_theme_mod( 'ridizain_slider_height' ) ) {
function ridizain_slider_scripts() {
 
$overall_slider_height = esc_html( get_theme_mod( 'ridizain_slider_height' ));
?>
    <style>
		.slider .featured-content .hentry {
			max-height: <?php echo $overall_slider_height; ?>px;
        }
	</style>
<?php }

add_action( 'wp_head', 'ridizain_slider_scripts', 210 );
}

if ( get_theme_mod( 'ridizain_feed_thumb_height' ) ) {
function ridizain_feed_scripts() {
if ( is_home() || ! is_singular() ) :
$overal_thumb_height = esc_html( get_theme_mod( 'ridizain_feed_thumb_height' ));
?>
    <style>
		.full-width .post-thumbnail img {
	        max-height: <?php echo $overal_thumb_height; ?>px;
	        max-width: 100%;
        }
	</style>
<?php endif; }
add_action( 'wp_head', 'ridizain_feed_scripts', 210 );
}

function ridizain_singular_scripts() {
if ( is_singular() ) :
if ( get_theme_mod( 'ridizain_singular_thumb_height' ) ) {
$singular_thumb_height = esc_html( get_theme_mod( 'ridizain_singular_thumb_height' ));
?>
    <style>
		.full-width .post-thumbnail img {
	        max-height: <?php echo $singular_thumb_height; ?>px;
	        max-width: 100%;
        }
	</style>
<?php }

if ( get_theme_mod( 'ridizain_singular_thumb_visibility' ) != 0 ) {
?>
    <style>
		.post-thumbnail img {
	        display: none;
        }
		.full-width .site-content .has-post-thumbnail .entry-header, 
		.full-width.singular .site-content .hentry.has-post-thumbnail {
            margin-top: 0;
        }
		.site-content .has-post-thumbnail .entry-header {
            margin-top: 0;
			padding-top: 0;
        }
	</style>
<?php }

endif;
}
add_action( 'wp_head', 'ridizain_singular_scripts', 210 );

function ridizain_general_css() {
if ( get_theme_mod( 'ridizain_num_grid_columns' ) ) {
	// Apply custom settings to appropriate element ?>
    <style>
	    @media screen and (min-width: 1008px) {
		    .grid .featured-content .hentry {
		        width: 24.999999975%;
	        }
	        .grid .featured-content .hentry:nth-child( 3n+1 ) {
		        clear: none;
	        }
	        .grid .featured-content .hentry:nth-child( 4n+1 ) {
		        clear: both;
	        }
	    }
	</style>
<?php }
if ( get_theme_mod( 'featured_content_location' ) == 'fullwidth' && ridizain_has_featured_posts() ) {
// Apply custom settings to appropriate element ?>
    <style>@media screen and (min-width: 1008px){.featured-content{margin-top:0;padding-left:0px;z-index:3;}}</style>
<?php }

if ( get_theme_mod( 'featured_content_boxed' ) != 0 ) {
// Apply custom settings to appropriate element ?>
    <style>@media screen and (min-width: 1008px){.featured-content{padding-left:40px;padding-right:40px;}}</style>
<?php }

if ( get_theme_mod( 'featured_content_location' ) == 'header' && ridizain_has_featured_posts() ) {
// Apply custom settings to appropriate element ?>
    <style>@media screen and (min-width: 1008px){.featured-content{margin-top:0;padding-left:0px;z-index:3;}}</style>
	<style>@media screen and (min-width: 1080px){.slider .featured-content { margin-bottom: 0px; }}}</style>
<?php }
}
add_action( 'wp_head', 'ridizain_general_css', 210 );

//dequeue/enqueue scripts
function ridizain_featured_content_scripts() {
$ridizain_theme = wp_get_theme();
$version = $ridizain_theme->get( 'Version' );
if ( get_theme_mod( 'ridizain_enable_autoslide' ) != 0 &&  get_theme_mod( 'featured_content_layout' ) == 'slider' ) {

if ( is_front_page() ) :
    wp_dequeue_script( 'ridizain-slider' );

    wp_enqueue_script( 'ridizain-flexslider', get_template_directory_uri() . '/js/flexslider/jquery.flexslider-min.js', array( 'jquery', ), $version, true );
    wp_localize_script( 'ridizain-flexslider', 'featuredSliderDefaults', array(
        'prevText' => __( 'Previous', 'ridizain' ),
        'nextText' => __( 'Next', 'ridizain' )
    ) );

if ( get_theme_mod( 'ridizain_slider_transition' ) ==  'slide' ) :
    wp_enqueue_script( 'ridizain-slider-slide', get_template_directory_uri() . '/js/flexslider/slider-slide.js', array( 'jquery', ), $version, true );

elseif ( get_theme_mod( 'ridizain_slider_transition' ) == 'fade' ) :
    wp_enqueue_script( 'ridizain-slider-fade', get_template_directory_uri() . '/js/flexslider/slider-fade.js', array( 'jquery', ), $version, true );

endif;

endif;
} else {
    wp_enqueue_script( 'ridizain-slider-fade', get_template_directory_uri() . '/js/flexslider/slider-default.js', array( 'jquery', ), $version, true );
}
}
add_action( 'wp_enqueue_scripts' , 'ridizain_featured_content_scripts' , 210 );
