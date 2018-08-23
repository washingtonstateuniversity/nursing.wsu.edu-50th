<?php

namespace WSUWP\UI\Cards;

add_action( 'after_setup_theme', __NAMESPACE__ . '\\setup_additional_post_images', 11 );
add_shortcode( 'wsuwp_cards', __NAMESPACE__ . '\\display_wsuwp_cards' );

/**
 * Use the Multiple Post Thumbnails plugin to
 * generate desktop and mobile images for posts.
 *
 * @since 0.0.1
 */
function setup_additional_post_images() {
	if ( ! class_exists( 'MultiPostThumbnails' ) ) {
		return;
	}

	$desktop_image_args = array(
		'label' => 'Desktop Image',
		'id' => 'desktop-image',
		'post_type' => 'post',
	);

	new \MultiPostThumbnails( $desktop_image_args );

	$mobile_image_args = array(
		'label' => 'Mobile Image',
		'id' => 'mobile-image',
		'post_type' => 'post',
	);

	new \MultiPostThumbnails( $mobile_image_args );
}

/**
 * Displays posts as trick-capable cards.
 *
 * @since 0.0.1
 */
function display_wsuwp_cards( $atts ) {

	// Bail now if Multiple Post Thumbnails isn't active.
	if ( ! class_exists( 'MultiPostThumbnails' ) ) {
		return;
	}

	$defaults = array(
		'count' => 10,
		'category' => '',
		'orderby' => '',
		'effects' => '',
	);

	$atts = shortcode_atts( $defaults, $atts );

	$args = array(
		'posts_per_page' => absint( $atts['count'] ),
	);

	$orderby_whitelist = array( 'title', 'rand' );

	if ( $atts['orderby'] && in_array( $atts['orderby'], $orderby_whitelist, true ) ) {
		$args['orderby'] = sanitize_key( $atts['orderby'] );
	}

	if ( $atts['category'] && term_exists( sanitize_key( $atts['category'] ), 'category' ) ) {
		$args['category_name'] = sanitize_key( $atts['category'] );
	}

	$query = new \WP_Query( $args );

	if ( ! $query->have_posts() ) {
		return '';
	}

	wp_enqueue_style( 'wsuwp-card-layout', get_stylesheet_directory_uri() . '/includes/cards-shortcode/layout.css', array(), spine_get_child_version() );
	wp_enqueue_script( 'wsuwp-process-images', get_stylesheet_directory_uri() . '/includes/cards-shortcode/process-images.min.js', array( 'jquery' ), spine_get_child_version(), true );

	$card_effect_whitelist = array(
		'fix-images',
		'fade-images',
	);

	$card_effects = array_map( 'sanitize_key', explode( ',', $atts['effects'] ) );

	$card_classes = '';

	foreach ( $card_effects as $card_effect ) {
		if ( in_array( $card_effect, $card_effect_whitelist, true ) ) {

			// Add the class for handling the effect to the card.
			$card_classes .= ' ui-' . $card_effect;

			// Enqueue the stylesheet for this effect if there is one.
			if ( file_exists( get_stylesheet_directory() . '/includes/cards-shortcode/' . $card_effect . '.css' ) ) {
				wp_enqueue_style( 'wsuwp-card-effect' . $card_effect, get_stylesheet_directory_uri() . '/includes/cards-shortcode/' . $card_effect . '.css', array(), spine_get_child_version() );
			}

			// Enqueue the JavaScript file for this effect if there is one.
			if ( file_exists( get_stylesheet_directory() . '/includes/cards-shortcode/' . $card_effect . '.min.js' ) ) {
				wp_enqueue_script( 'wsuwp-card-effect' . $card_effect, get_stylesheet_directory_uri() . '/includes/cards-shortcode/' . $card_effect . '.min.js', array( 'jquery' ), spine_get_child_version(), true );
			}
		}
	}

	ob_start();

	while ( $query->have_posts() ) {
		$query->the_post();

		$image_desktop_src = \MultiPostThumbnails::get_post_thumbnail_url( 'post', 'desktop-image', get_the_ID(), 'full' );
		$image_mobile_src = \MultiPostThumbnails::get_post_thumbnail_url( 'post', 'mobile-image', get_the_ID(), 'full' );

		if ( ! $image_desktop_src || ! $image_mobile_src ) {
			continue;
		}

		$image_id = \MultiPostThumbnails::get_post_thumbnail_id( 'post', 'desktop-image', get_the_ID() );
		$image_alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
		?>
		<article class="content-card<?php echo esc_attr( $card_classes ); ?>">

			<figure class="content-card--feature-image"
					data-desktop-image="<?php echo esc_url( $image_desktop_src ); ?>"
					data-mobile-image="<?php echo esc_url( $image_mobile_src ); ?>">
				<div class="content-card--feature-image-wrapper">
					<img src="<?php echo esc_url( $image_mobile_src ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" />
				</div>
			</figure>

			<div class="content-card--copy">

				<header>
					<h2><?php the_title(); ?></h2>
				</header>

				<?php
					add_filter( 'the_content', 'wpautop' );
					the_content();
					remove_filter( 'the_content', 'wpautop' );
				?>

			</div>

		</article>
		<?php
	}

	$content = ob_get_clean();

	return $content;
}
