(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */



	jQuery(document).ready(function($) {
    function resizeElementHeight() {
      const element = $('#holaplex-drops'); // Replace 'your-element' with the ID or class of your target element
      const calculatedYPosition = element.offset().top;
      const windowHeight = $(window).height();
      const remainingSpace = windowHeight - calculatedYPosition - 100;
  
      element.height(remainingSpace);
    }
  
    // Call the function on page load
    resizeElementHeight();
  
    // Call the function when the window is resized
    $(window).resize(function() {
      resizeElementHeight();
    });
  });
  
	

})( jQuery );
