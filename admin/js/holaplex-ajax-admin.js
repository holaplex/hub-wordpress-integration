jQuery(document).ready(function ($) {

    $('.holaplex-tablinks').on('click', function (e) {
        e.preventDefault();
        $('.holaplex-tablinks').removeClass('active');
        $(this).addClass('active');
        $('.holaplex-tab-content').removeClass('active');
        $(this.dataset.tab).addClass('active');
    })


    $('#mainform').on('submit', function (e) {
        e.preventDefault();
        e.stopPropagation();

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
    $('#sync-button').on('click', function () {
        const drop_id = this.dataset.dropId;

        $.ajax({
            url: your_plugin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'add_product_with_drop_id',
                drop_id: drop_id
            },
            success: function (response) {
                // Handle the successful AJAX response
                console.log(response);
            },
            error: function (xhr, status, error) {
                // Handle AJAX error
                console.log(error);
            }
        });
    });
});
