jQuery(document).ready(function ($) {

    $('#mainform').on('submit', function (e) {
        e.preventDefault();
        e.stopPropagation();
        window.onbeforeunload = null;
        $(window).off('beforeunload');


        // get #mainform form data into json
        const data = {};
        $.each($(this).serializeArray(), function (i, field) {
            data[field.name] = field.value || '';
        });       

        $.ajax({
            url: holaplex_wp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'holaplex_connect',
                ...data
            },
            success: function (response) {
                // Handle the successful AJAX response
                console.log(response);
                location.reload();
            },
            error: function (xhr, status, error) {
                // Handle AJAX error
                console.log(error);
            }
        });
    });
    $('#holaplex-disconnect-btn').on('click', function (e) {
        e.preventDefault();
        $.ajax({
            url: holaplex_wp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'holaplex_disconnect',
            },
            success: function (response) {
                // Handle the successful AJAX response
                console.log(response);
                location.reload();
            },
            error: function (xhr, status, error) {
                // Handle AJAX error
                console.log(error);
            }
        });
    });
    $('.import-btn').on('click', function (e) {
        e.preventDefault();
        const ele = $(this);
        const drop_id = this.dataset.dropId;
        const dropName = this.dataset.dropName;
        const dropDesc = this.dataset.dropDesc;
        const dropImage = this.dataset.dropImage;
        const projectId = this.dataset.projectId;
        const nonce = this.dataset.wpNonce;


        // show loading
        ele.text('Importing...');

        $.ajax({
            url: holaplex_wp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'add_product_with_drop_id',
                drop_id: drop_id,
                _wpnonce: nonce,
                drop_name: dropName,
                drop_desc: dropDesc,
                drop_image: dropImage,
                project_id: projectId
            },
            success: function (response) {
                // Handle the successful AJAX response
                // disable button
                ele.prop('disabled', true);
                // change text to imported
                ele.text('Imported');
                window.location.hash = '#holaplex-drops';
                window.location.reload();
            },
            error: function (xhr, status, error) {
                ele.text('Failed');
                // Handle AJAX error
            }
        });
    });


    // #remove-btn should send an ajax request with nonce and product id to wp
	$('.btn-remove').on('click', function (e) {
		e.preventDefault();
		const ele = $(this);
		const product_id = this.dataset.productId;
		const nonce = this.dataset.wpNonce;

		// show confirm dialog
		if (!window.confirm("Are you sure you want to remove this drop? The associated product will be deleted.")) {
			return;
		}

		// show loading
		ele.text('Removing...');

		$.ajax({
			url: holaplex_wp_ajax.ajax_url,
			type: 'POST',
			data: {
				action: 'remove_product_with_product_id',
				product_id: product_id,
				_wpnonce: nonce,
			},
			success: function (response) {
				// Handle the successful AJAX response
				// disable button
				ele.prop('disabled', true);
				// change text to imported
				ele.text('Removed');
                window.location.hash = '#holaplex-drops';
                window.location.reload();
			},
			error: function (xhr, status, error) {
				ele.text('Failed');
				// Handle AJAX error
			}
		});
	});
});

