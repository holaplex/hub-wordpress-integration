jQuery(document).ready(function ($) {

    $('#mainform').on('submit', function (e) {
        // check if current url has query string ?tab=holaplex_settings
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        if (tab == 'holaplex_settings') {
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
        }
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
    $('#holaplex_tab_submit_data').on('click', function (e) {
        e.preventDefault();
        const ele = $(this);

        let drop_project_id = $('#_holaplex_drop_project_ids').val();
        let holaplex_product_add_to_cart_on_low = $('#holaplex_product_add_to_cart_on_low').val();
        drop_project_id = drop_project_id.split('|');
        const drop_id = drop_project_id[0];
        const projectId = drop_project_id[1];
        // url query param for post
        const urlParams = new URLSearchParams(window.location.search);
        const postId = urlParams.get('post');
        const nonce = this.dataset.wpNonce;
        // show loading
        ele.text('Importing...');

        $.ajax({
            url: holaplex_wp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'add_drop_id_to_product',
                holaplex_product_add_to_cart_on_low,
                drop_id: drop_id,
                _wpnonce: nonce,
                project_id: projectId,
                post_id: postId
            },
            success: function (response) {
                // Handle the successful AJAX response
                // disable button
                ele.prop('disabled', true);
                // change text to imported
                ele.text('Imported');
            },
            error: function (xhr, status, error) {
                ele.text('Failed');
                // Handle AJAX error
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
        const totalSupply = this.dataset.totalSupply;
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
                total_supply: totalSupply,
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

