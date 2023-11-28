<?php
/*Plugin Name: bS Swiper Gallery
Plugin URI: https://dev.bootscore.me/plugins/bs-swiper/
Description: This plugin extends the bs Swiper plugin to a WordPress Gutenberg image gallery with lightbox in bootScore theme.
Version: 5.1.0
Author: bootScore
Author URI: https://bootscore.me
License: MIT License
*/



// Register Styles and Scripts
add_action( 'wp_enqueue_scripts', 'enqueue_glightbox' );
function enqueue_glightbox() {
  
  wp_enqueue_script( 'glightbox', plugins_url( '/glightbox/glightbox.min.js' , __FILE__ ), array(), false, true );
  wp_enqueue_script( 'glightbox-init', plugins_url( '/glightbox/init.js' , __FILE__ ), array(), false, true );
  
  wp_register_style( 'glightbox-css', plugins_url('/glightbox/glightbox.css', __FILE__) );
  wp_enqueue_style( 'glightbox-css' );
}


/**
 * WP Tutorials : Swiper JS Gallery (WPTSJG)
 *
 * https://wp-tutorials.tech/refine-wordpress/multiple-gallery-effects-with-swiper/
 */

defined('WPINC') || die();

const WPTSJG_CLASS_NAME = 'swiper-gallery';
const WPTSJG_GALLERY_BLOCK_NAME = 'core/gallery';
const WPTSJG_IMAGE_BLOCK_NAME = 'core/image';
const WPTSJG_CLASS_MODE_PREFIX = 'swiper-mode-';
const WPTSJG_DEFAULT_IMAGE_SIZE = 'full';
const WPTJGS_NAVIGATION_CLASS = 'swiper-navigation';
const WPTJGS_PAGINATION_CLASS = 'swiper-pagination';
//const WPTJGS_SWIPER_VERSION = '11.0.5';
const WPTJGS_GLIGHTBOX_VERSION = '3.2.0';

/**
 * Enqueue all the front-end assets for this project.
 */
function wptsjg_enqueue_assets() {
   global $wptsjg_have_styles_been_enqueued;

   if (is_null($wptsjg_have_styles_been_enqueued)) {
      $base_uri = plugins_url() . '/';
      $version = wp_get_theme()->get('Version');
      $handle = 'wptsjg';

      // Enqueue Swiper's front-end assets.
      //wp_enqueue_script('swiperjs', $base_uri . 'bs-swiper-main/js/swiper-bundle.min.js', null, WPTJGS_SWIPER_VERSION);
      //wp_enqueue_style('swiperjs', $base_uri . 'bs-swiper-main/css/swiper-bundle.min.css', null, WPTJGS_SWIPER_VERSION);
      wp_enqueue_script('swiperjs', $base_uri . 'bs-swiper-main/js/swiper-bundle.min.js', null);
      wp_enqueue_style('swiperjs', $base_uri . 'bs-swiper-main/css/swiper-bundle.min.css', null);

      // Enqueue GLightbox's front-end assets..
      wp_enqueue_script('glightbox', $base_uri . 'bs-swiper-gallery-main/glightbox/glightbox.min.js', null, WPTJGS_GLIGHTBOX_VERSION);
      wp_enqueue_style('glightbox', $base_uri . 'bs-swiper-gallery-main/glightbox/glightbox.css', null, WPTJGS_GLIGHTBOX_VERSION);

      wp_enqueue_style(
         $handle,
         $base_uri . 'bs-swiper-gallery-main/wpt-swiper-gallery/wpt-swiper-gallery.css',
         null, // No style dependencies
         $version
      );

      wp_enqueue_script(
         $handle,
         $base_uri . 'bs-swiper-gallery-main/wpt-swiper-gallery/wpt-swiper-gallery.js',
         array('swiperjs', 'glightbox'),
         $version
      );

      $wptsjg_have_styles_been_enqueued = true;
   }
}

/**
 * Configurations for Swiper. You can add your own configurations in here if
 * you want more effects.
 */
function wptsjg_get_swiper_config(string $mode) {
   global $wptsjg_swiper_configs;

   if (is_null($wptsjg_swiper_configs)) {
      $wptsjg_swiper_configs = array(
         'cards' => array(
            'effect' => 'cards',
            'grabCursor' => true,
         ),
         'coverflow' => array(
            'effect' => 'coverflow',
            'grabCursor' => true,
            'centeredSlides' => true,
            'slidesPerView' => 'auto',
            'loop' => true,
            'coverflowEffect' => array(
               'rotate' => 25,
               'stretch' => 50,
               'depth' => 100,
               'modifier' => 1,
               'slideShadows' => true,
            ),
         ),
         'cube' => array(
            'effect' => 'cube',
            'grabCursor' => true,
            'loop' => true,
            'cubeEffect' => array(
               'shadow' => true,
               'slideShadows' => true,
               'shadowOffset' => 20,
               'shadowScale' => 0.94,
            ),
         ),
         'slide' => array(
            'grabCursor' => true,
            'loop' => true,    
            'autoplay' => array(
               'delay' => 4000,
            ),           
         ),        
         'fade' => array(
            'effect' => 'fade',
            'speed' => 1500,
            'grabCursor' => true,
            'loop' => true,
            'fadeEffect' => array(
               'crossFade' => true,
            ),
            'autoplay' => array(
               'delay' => 4000,
            ),              
         ),
      );
   }

   // If $mode is empty, or we don't have a configuration for the requested
   // mode/effect, grab the name of the first configuration that's available.
   if (empty($mode) || !array_key_exists($mode, $wptsjg_swiper_configs)) {
      $mode = array_keys($wptsjg_swiper_configs)[0];
   }

   return $wptsjg_swiper_configs[$mode];
}

/**
 * Intercept when WordPress tries to render a gallery block. Override the output
 * HTML if the gallery block has swiper-gallery in its CSS Class list.
 */
function wptsjg_render_gallery(string $block_content, array $block) {
   if (is_admin() || wp_doing_ajax()) {
      // We're not in the front-end.. Don't do anything.
   } elseif ($block['blockName'] != WPTSJG_GALLERY_BLOCK_NAME) {
      // This isn't a WP Gallery block.
   } elseif (!array_key_exists('attrs', $block)) {
      // The gallery block has no attributes.
   } elseif (!array_key_exists('className', $block['attrs'])) {
      // The className attribute is not specified.
   } elseif (empty($class_name = $block['attrs']['className'])) {
      // The className attribute is empty.
   } elseif (empty($gallery_classes = array_filter(explode(' ', $class_name)))) {
      // The className attribute is empty.
   } elseif (!in_array(WPTSJG_CLASS_NAME, $gallery_classes)) {
      // "swiper-gallery" isn't in the list of CSS Classes for the block.
   } elseif (empty($inner_blocks = $block['innerBlocks'])) {
      // The gallery has no image blocks in it.
   } else {
      $image_size = WPTSJG_DEFAULT_IMAGE_SIZE;
      if (array_key_exists('sizeSlug', $block['attrs'])) {
         $image_size = $block['attrs']['sizeSlug'];
      }

      // Create a unique name/id for this gallery.
      global $wptjgs_gallery_index;
      if (is_null($wptjgs_gallery_index)) {
         $wptjgs_gallery_index = 1;
      }
      $gallery_name = 'wptjgs-gallery-' . $wptjgs_gallery_index;

      // Loop through the CSS Classes assigned to the gallery block, looking for
      // the swiper mode and whether pagination/navigation should be enabled.
      $mode = '';
      $prefix = WPTSJG_CLASS_MODE_PREFIX;
      $prefix_length = strlen(WPTSJG_CLASS_MODE_PREFIX);
      $is_navigation_enabled = false;
      $is_pagination_enabled = false;
      $outer_classes = array();

      foreach ($gallery_classes as $gallery_class) {
         if (strpos($gallery_class, $prefix) === 0) {
            $mode = substr($gallery_class, $prefix_length);
         } elseif ($gallery_class == WPTJGS_NAVIGATION_CLASS) {
            $is_navigation_enabled = true;
         } elseif ($gallery_class == WPTJGS_PAGINATION_CLASS) {
            $is_pagination_enabled = true;
         } elseif ($gallery_class == WPTSJG_CLASS_NAME) {
            // Don't pass this class through - it's our control class.
         } else {
            // Pass the CSS Class through to our outer container.
            $outer_classes[] = $gallery_class;
         }
      }

      // Insert our main CSS Classes to the beginning of the array of classes
      // for our outer container.
      array_unshift($outer_classes, 'wpt-swiper-mode-' . $mode);
      array_unshift($outer_classes, 'wpt-swiper-gallery');

      // Get the Swiper configuration for the mode/effect we want.
      $swiper_config = wptsjg_get_swiper_config($mode);

      // Maybe add navigation to the Swiper configuratoin.
      if ($is_navigation_enabled) {
         $swiper_config['navigation'] = array(
            'nextEl' => sprintf('#%s .swiper-button-next', esc_attr($gallery_name)),
            'prevEl' => sprintf('#%s .swiper-button-prev', esc_attr($gallery_name)),
         );
      }

      // Maybe add pagination to the Swiper configuratoin.
      if ($is_pagination_enabled) {
         $swiper_config['pagination'] = array(
            'el' => sprintf('#%s .swiper-pagination', esc_attr($gallery_name)),
            //'clickable' => 'true',
         );
      }

      // Enqueue all the frontend assets for Swiper, GLightbox
      // and our own CSS/JS.
      wptsjg_enqueue_assets();

      // Start creating our HTML with an ourter DIV and a JSON representation
      // of Swiper's configuration.
      $block_content = sprintf(
         '<div id="%s" class="%s" data-swiper-config="%s">',
         esc_attr($gallery_name),
         esc_attr(implode(' ', $outer_classes)),
         esc_attr(json_encode($swiper_config))
      );
      $block_content .= '<div class="swiper">';
      $block_content .= '<figure class="swiper-wrapper">';

      // Loop through the inner image blocks and pull out the image metas.
      $image_index = 0;
      foreach ($inner_blocks as $inner_block) {
         if ($inner_block['blockName'] != WPTSJG_IMAGE_BLOCK_NAME) {
            // ...
         } elseif (($image_id = intval($inner_block['attrs']['id'])) <= 0) {
            // ...
         } else {
            // Get the image's meta data.
            $thumbnail_url = wp_get_attachment_image_url($image_id, $image_size);
            $fullsize_url = wp_get_attachment_image_url($image_id, 'full');
            $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
            $image_caption = wp_get_attachment_caption($image_id);

            $link_props = '';
            if (!empty($image_caption)) {
               $link_props .= sprintf(' data-title="%s"', esc_attr($image_caption));
            }

            $image_props = '';
            if (!empty($image_alt)) {
               $image_props .= sprintf(' alt="%s"', esc_attr($image_alt));
            }

            // Render the HTML for this image's slide.
            $block_content .= sprintf(
               '<figure class="swiper-slide"><a href="%s" class="glightbox" %s><img src="%s" %s/></a></figure>',
               esc_url($fullsize_url),
               $link_props,
               esc_url($thumbnail_url),
               $image_props
            );
         }

         ++$image_index;
      }

      $block_content .= '</figure>'; // .swiper-wrapper

      // Maybe add navigation to the Swiper configuratoin.
      if ($is_navigation_enabled) {
         $block_content .= '<div class="swiper-button-next"></div>';
         $block_content .= '<div class="swiper-button-prev"></div>';
      }

      // Maybe add pagination to the Swiper configuratoin.
      if ($is_pagination_enabled) {
         $block_content .= '<div class="swiper-pagination"></div>';
      }

      $block_content .= '</div>'; // .swiper
      $block_content .= '</div>'; // .wpt-swiper-gallery

      ++$wptjgs_gallery_index;
   }

   return $block_content;
}
add_filter('render_block', 'wptsjg_render_gallery', 50, 2);
