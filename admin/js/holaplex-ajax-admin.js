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
    $('#sync-btn').on('click', function (e) {
        e.preventDefault();
        const drop_id = this.dataset.dropId;
        const dropName = this.dataset.dropName;
        const dropDesc = this.dataset.dropDesc;
        const dropImage = this.dataset.dropImage;
        const nonce = this.dataset.wpNonce;

        $.ajax({
            url: holaplex_wp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'add_product_with_drop_id',
                drop_id: drop_id,
                _wpnonce: nonce,
                drop_name: dropName,
                drop_desc: dropDesc,
                drop_image: dropImage
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


jQuery(document).ready(function($) {
    function resizeElementHeight() {
      const element = $('.holaplex-app'); // Replace 'your-element' with the ID or class of your target element
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
  