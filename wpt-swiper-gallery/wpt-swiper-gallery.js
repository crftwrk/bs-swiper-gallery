/**
 * WP Tutorials : Swiper JS Gallery (WPTSJG)
 *
 * https://wp-tutorials.tech/refine-wordpress/multiple-gallery-effects-with-swiper/
 */
 document.addEventListener('DOMContentLoaded', function() {
    'use strict';
 
    // Find all elements that have this property set:
    //    data-swiper-config="{...}"
    /// ...and call Swiper() for each one.
    document.querySelectorAll('[data-swiper-config').forEach(function(outerElement) {
       // Uncomment this to verify we're selecting the gallery elements.
       // console.log('Found a swiper gallery');
 
       // Get the actual element we're going to pass to Swiper.
       const swiperElement = outerElement.querySelector('.swiper');
 
       // Grab the configuration from the data-swiper-config property and
       // convert it from a JSON string into an object.
       const swiperConfig = JSON.parse(outerElement.dataset.swiperConfig);
 
       // Create this Swiper object.
       var swiper = new Swiper(
          swiperElement,
          swiperConfig
       );
 
       // Select all child elements that have the "gslightbox" CSS Class.
       const galleryItems = outerElement.querySelectorAll('.gslightbox');
 
       // Create the GLightbox obejct with this gallery's items.
       const lightbox = new GLightbox(galleryItems);
    });
 
 });