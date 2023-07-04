<div class="container-fluid holaplex-app">
  <div class="row">
    <!-- two tabs -->
    <div class="col-md-12 col-sm-12 col-lg-6">
      <div class="holaplex-tabs">
        <a class="holaplex-tablinks active" href="#holaplex-setup">Setup / Config</a>
        <a class="holaplex-tablinks" href="#holaplex-drops">Drops</a>
      </div>
    </div>
  </div>
  <div class="row" style="height: 100%;">
    <section id="holaplex-setup" class="col-md-12 col-sm-12 col-lg-12 holaplex-tab-content active">
      <div class="row">
        <div class="col-lg-6">
          <h2><?php _e('Holaplex Settings', 'holaplex-wp'); ?></h2>
          <table class="form-table">
            <tbody>
              <tr valign="top">
                <th scope="row"><?php _e('Connection Status', 'holaplex-wp'); ?></th>
                <td>
                  <div class="row">
                    <div class="col-6">
                      <?php echo esc_html($holaplex_status); ?>
                    </div>
                    <?php if ($holaplex_status == '✅ connected') : ?>
                      <div class="col-6">
                        <button id="holaplex-disconnect-btn" class="button button-secondary"><?php _e('Disconnect', 'holaplex-wp'); ?></button>
                      </div>
                    <?php endif; ?>

                  </div>
                </td>
              </tr>
              <tr valign="top">
                <th scope="row"><?php _e('Organization Id', 'holaplex-wp'); ?></th>
                <td>
                  <input type="text" name="holaplex_org_id" value="<?php echo esc_attr(get_option('holaplex_org_id')); ?>">
                  <p class="description"><?php _e('Enter Organization Id', 'holaplex-wp'); ?></p>
                </td>
              </tr>
              <tr valign="top">
                <th scope="row"><?php _e('API Token', 'holaplex-wp'); ?></th>
                <td>
                  <input type="text" name="holaplex_api_key" value="<?php echo esc_attr(get_option('holaplex_api_key')); ?>">
                  <p class="description"><?php _e('Enter the API Token', 'holaplex-wp'); ?></p>
                </td>
              </tr>
            </tbody>
          </table>
          <div class="row">
            <button name="save" class="button-primary woocommerce-save-button main-btn" type="submit" value="Save changes">Save changes</button>
          </div>
        </div>
        <div class="col-md-12 col-lg-6">
          <h2 class='help-title'><?php echo esc_html(__('Setup Help', 'holaplex-wp')); ?></h2>
          <p class='description help-mesg'>
            To connect to Holaplex Hub, enter an API token and associated Organization ID below. <br />
            An API token can be generated on the Credentials tab of your Organization's page on Hub: <a target='_blank' href='https://hub.holaplex.com/credentials'>https://hub.holaplex.com/credentials</a>. <br />
            You can find your Organization ID by clicking the menu button in the upper left corner, next to your organization's name. <br />
            For more info, please see <a target='_blank' href='https://docs.holaplex.com/category/guides/woocommerce-plugin'> https://docs.holaplex.com/category/guides/woocommerce-plugin</a><br />
            If you do not already have a Holaplex Hub account, you can create one at <a target='_blank' href='https://hub.holaplex.com/'>https://hub.holaplex.com/</a><br />

          </p>

        </div>
      </div>
    </section>

    <section id="holaplex-drops" class="col-md-12 col-sm-12 col-lg-12 holaplex-tab-content">
      <h2 style="margin-bottom: 0;"><?php echo esc_html(__('Drops', 'holaplex-wp')); ?></h2>
      <div class="row">
        <div class="col-lg-8">
          <div class="row">
            <div class="col-md-6">
              <h4 class="description">
                Import your Holaplex Hub Drops as products to make them available to sell or claim through your store.
              </h4>
            </div>
            <div class="col-md-2">
              <div class="header-actions">
                <a href="#drops-modal" class="btn btn-primary align-self-right">Import drops</a>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-8">
              <ul class="responsive-table">
                <li class="table-header">
                  <div class="col-2">Name</div>
                  <div class="col-1">Supply</div>
                  <div class="col-1">Status</div>
                  <div class="col-2">Import</div>
                </li>
                <?php 
      
                  // if $holaplex_products is not empty show the products, else show message
                  if (!empty($holaplex_products)) {
                    foreach ($holaplex_products as $product) {
                      $nonce = wp_create_nonce(HOLAPLEX_NONCE);

                      echo "<li class='table-row'>";
                      echo "<div class='col-2'><a href=''>" . esc_html($product->get_name()) . "</a></div>";
                      echo "<div class='col-1'>" . '' . "</div>";
                      echo "<div class='col-1'>" . '' . "</div>";
                      echo "<div class='col-2'><button data-product-id='".esc_attr($product->get_id())."' data-wp-nonce='".esc_attr($nonce)."' class='btn btn-remove'>Remove</button></div>";
                      echo "</li>";
                    }
                  } else {
                    echo "<li class='table-row'>";
                    echo "<div class='col-12'><p class='description'>" . esc_html(__('No drops imported.', 'holaplex-wp')) . "</p></div>";
                    echo "</li>";
                  }
      
                ?>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <?php include_once( HOLAPLEX_PLUGIN_PATH . 'admin/partials/holaplex-wp-admin-import-drops.php'); ?>
    </section>
  </div>
</div>