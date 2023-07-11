(function ($) {
	'use strict';

})(jQuery);

jQuery(document).ready(function ($) {
	// Get the current URL hash and show the corresponding tab
	let currentHash = window.location.hash;
	if (currentHash && currentHash == '#holaplex-setup' || currentHash == '#holaplex-drops' || currentHash == '#holaplex-gate') {
		showTab(currentHash);
	} else {
		currentHash = '#holaplex-setup'
	}

	// Handle anchor click events
	$('.holaplex-tabs a').click(function (event) {
		event.preventDefault();
		var targetHash = $(this).attr('href');
		showTab(targetHash);

		// Update the URL hash
		history.pushState(null, null, targetHash);
	});

	// Function to show the tab based on the provided hash
	function showTab(hash) {
		$('.holaplex-tab-content').hide();
		$(hash).show();
		$('.holaplex-tabs a').removeClass('active');
		$('.holaplex-tabs a[href="' + hash + '"]').addClass('active');
	}
});

jQuery(document).ready(function ($) {

	// #import-done button should go reload the page to #holaplex-drops
	$('#import-done').click(function () {
		window.location.hash = '#holaplex-drops';
		window.location.reload();
	});

});
