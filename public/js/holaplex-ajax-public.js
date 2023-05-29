jQuery(document).ready(function($) {
  $('#create-customer-button').on('click', function(e) {
      e.preventDefault();
      $.ajax({
          url: holaplex_ajax.ajax_url,
          type: 'POST',
          data: {
              action: 'create_customer_wallet'
          },
          success: function(response) {
              // Handle the successful AJAX response
              console.log(response);
          },
          error: function(xhr, status, error) {
              // Handle AJAX error
              console.log(error);
          }
      });
  });
});