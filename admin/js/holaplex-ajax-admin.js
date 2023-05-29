jQuery(document).ready(function($) {
  $('#sync-button').click(function() {
      const drop_id = this.dataset.dropId;
      
      $.ajax({
          url: your_plugin_ajax.ajax_url,
          type: 'POST',
          data: {
              action: 'add_product_with_drop_id',
              drop_id: drop_id
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
