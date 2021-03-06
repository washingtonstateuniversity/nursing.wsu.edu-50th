<?php

require_once 'includes/cards-shortcode/cards-shortcode.php';

add_filter( 'spine_child_theme_version', 'nursing_anniversary_theme_version' );
add_action( 'init', 'nursing_anniversary_remove_spine_wp_enqueue_scripts' );
add_action( 'wp_enqueue_scripts', 'nursing_anniversary_spine_wp_enqueue_scripts', 20 );
add_filter( 'nav_menu_item_id', 'nursing_anniversary_nav_menu_id', 20 );

// Disable background and thumbnail images for posts and pages.
add_filter( 'spine_post_supports_background_image', '__return_false' );
add_filter( 'spine_page_supports_background_image', '__return_false' );
add_filter( 'spine_post_supports_thumbnail_image', '__return_false' );
add_filter( 'spine_page_supports_thumbnail_image', '__return_false' );

/**
 * Provides a theme version for use in cache busting.
 *
 * @since 0.0.1
 *
 * @return string
 */
function nursing_anniversary_theme_version() {
	return '0.0.2';
}

/**
 * Dequeues the default Spine assets required for front end page views.
 *
 * @since 0.0.1
 */
function nursing_anniversary_remove_spine_wp_enqueue_scripts() {
	remove_action( 'wp_enqueue_scripts', 'spine_wp_enqueue_scripts', 20 );
}

/**
 * Enqueues assets required for front end page views.
 *
 * @since 0.0.1
 */
function nursing_anniversary_spine_wp_enqueue_scripts() {
	$spine_version = spine_get_option( 'spine_version' );

	// This may be an unnecessary check, but we don't want to screw this up.
	if ( 'develop' !== $spine_version && 0 === absint( $spine_version ) ) {
		$spine_version = 1;
	}

	// Much relies on the main stylesheet provided by the WSU Spine.
	wp_enqueue_style( 'wsu-spine', 'https://repo.wsu.edu/spine/' . $spine_version . '/spine.min.css', array(), spine_get_script_version() );
	wp_enqueue_style( 'spine-theme', get_template_directory_uri() . '/style.css', array( 'wsu-spine' ), spine_get_script_version() );
	wp_enqueue_style( 'spine-theme-child', get_stylesheet_directory_uri() . '/style.css', array( 'wsu-spine' ), spine_get_child_version() );
	wp_enqueue_style( 'spine-theme-print', get_template_directory_uri() . '/css/print.css', array(), spine_get_script_version(), 'print' );

	// All theme styles have been output at this time. Plugins and other themes should print styles here, before blocking
	// Javascript resources are output.
	do_action( 'spine_enqueue_styles' );

	$google_font_css_url = '//fonts.googleapis.com/css?family=';
	$count = 0;
	$spine_open_sans = spine_get_open_sans_options();

	// Build the URL used to pull additional Open Sans font weights and styles from Google.
	if ( ! empty( $spine_open_sans ) ) {
		$build_open_sans_css = '';

		foreach ( $spine_open_sans as $font_option ) {
			if ( 0 === $count ) {
				$build_open_sans_css = 'Open+Sans%3A' . $font_option;
			} else {
				$build_open_sans_css .= '%2C' . $font_option;
			}

			$count++;
		}

		if ( 0 !== $count ) {
			$google_font_css_url .= $build_open_sans_css;
		} else {
			$google_font_css_url = '';
		}
	} else {
		$google_font_css_url = '';
	}

	$spine_open_sans_condensed = spine_get_open_sans_condensed_options();

	$condensed_count = 0;

	if ( ! empty( $spine_open_sans_condensed ) ) {
		if ( 0 !== $count ) {
			$build_open_sans_cond_css = '|Open+Sans+Condensed%3A';
		} else {
			$build_open_sans_cond_css = 'Open+Sans+Condensed%3A';
		}

		foreach ( $spine_open_sans_condensed as $font_option ) {
			if ( 0 === $condensed_count ) {
				$build_open_sans_cond_css .= $font_option;
			} else {
				$build_open_sans_cond_css .= '%2C' . $font_option;
			}

			$count++;
			$condensed_count++;
		}

		$google_font_css_url .= $build_open_sans_cond_css;
	}

	// Only enqueue a custom Google Fonts URL if extra options have been selected for Open Sans.
	if ( '' !== $google_font_css_url ) {
		$google_font_css_url .= '&subset=latin,latin-ext';

		// Deregister the default Open Sans URL provided by WordPress core and instead provide our own.
		wp_deregister_style( 'open-sans' );

		wp_enqueue_style( 'open-sans', $google_font_css_url, array(), false );
	}

	// WordPress core provides much of jQuery UI, but not in a nice enough package to enqueue all at once.
	// For this reason, we'll pull the entire package from the Google CDN.
	wp_enqueue_script( 'wsu-jquery-ui-full', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js', array( 'jquery' ) );

	// Much relies on the main Javascript provided by the WSU Spine.
	wp_enqueue_script( 'wsu-spine', get_stylesheet_directory_uri() . '/js/spine.min.js', array( 'wsu-jquery-ui-full' ), spine_get_child_version(), false );

	// Override default options in the WSU Spine.
	$twitter_text = ( is_front_page() ) ? get_option( 'blogname' ) : trim( wp_title( '', false ) );

	$spineoptions = array(
		'social' => array(
			'share_text' => esc_js( spine_get_title() ),
			'twitter_text' => esc_js( $twitter_text ),
			'twitter_handle' => 'wsupullman',
		),
	);

	// If a Twitter account has been added in the Customizer, use that for the via handle.
	$spine_social_options = spine_social_options();

	if ( isset( $spine_social_options['twitter'] ) ) {
		$twitter_array = array_filter( explode( '/', $spine_social_options['twitter'] ) );
		$twitter_handle = array_pop( $twitter_array );
		$spineoptions['social']['twitter_handle'] = esc_js( $twitter_handle );
	}

	wp_localize_script( 'wsu-spine', 'spineoptions', $spineoptions );

	// Enqueue jQuery Cycle2 and Genericons when a page builder template is used.
	if ( is_page_template( 'template-builder.php' ) ) {
		$has_builder_banner = get_post_meta( get_the_ID(), '_has_builder_banner', true );

		if ( $has_builder_banner ) {
			// Enqueue the compilation of jQuery Cycle2 scripts required for the slider
			wp_enqueue_script( 'wsu-cycle', get_template_directory_uri() . '/js/cycle2/jquery.cycle2.min.js', array( 'jquery' ), spine_get_script_version(), true );
			wp_enqueue_style( 'genericons', get_template_directory_uri() . '/styles/genericons/genericons.css', array(), spine_get_script_version() );
		}
	}

	// Enqueue scripting for the entire parent theme.
	wp_enqueue_script( 'wsu-spine-theme-js', get_template_directory_uri() . '/js/spine-theme.js', array( 'jquery' ), spine_get_script_version(), true );
}

/**
 * Strips menu item IDs as navigation is built.
 *
 * @since 0.0.1
 *
 * @param string $id
 *
 * @return bool
 */
function nursing_anniversary_nav_menu_id( $id ) {
	return false;
}
