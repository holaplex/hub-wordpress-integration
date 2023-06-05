jQuery(document).ready(function($) {
  $('#create-customer-button').on('click', function(e) {
      e.preventDefault();
      // change text to "Creating..."
      $(e.target).text('Creating...');
      e.target.disabled = true;
      $.ajax({
          url: holaplex_ajax.ajax_url,
          type: 'POST',
          data: {
              action: 'create_customer_wallet'
          },
          success: function(response) {
            $(e.target).text('Complete...');
            e.target.disabled = false;
            location.reload()
          },
          error: function(xhr, status, error) {
              // Handle AJAX error
              console.log(error);
          }
      });
  });

  $('#remove-customer-button').on('click', function(e) {
      e.preventDefault();
      $.ajax({
          url: holaplex_ajax.ajax_url,
          type: 'POST',
          data: {
              action: 'remove_customer_wallet'
          },
          success: function(response) {
            location.reload()
          },
          error: function(xhr, status, error) {
              // Handle AJAX error
              console.log(error);
          }
      });
  });

  $('#create-wallet-button').on('click', function(e) {
      e.preventDefault();
      $.ajax({
          url: holaplex_ajax.ajax_url,
          type: 'POST',
          data: {
              action: 'create_new_wallet'
          },
          success: function(response) {
              location.reload()
          },
          error: function(xhr, status, error) {
              // Handle AJAX error
              console.log(error);
          }
      });
  });
});