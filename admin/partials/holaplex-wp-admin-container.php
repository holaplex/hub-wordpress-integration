<div class="container-fluid holaplex-app">
  <!-- wp nonce field -->
  <?php 
    // wp nonce
    wp_nonce_field(HOLAPLEX_NONCE, '_holaplex_nonce');
  ?>
  <div class="row">
    <!-- two tabs -->
    <div class="col-md-12 col-sm-12 col-lg-6">
      <div class="holaplex-tabs">
        <a class="holaplex-tablinks active" href="#holaplex-setup">Setup / Config</a>
        <a class="holaplex-tablinks" href="#holaplex-drops">Drops</a>
        <a class="holaplex-tablinks" href="#holaplex-gate">Content Gate</a>
      </div>
    </div>
  </div>
  <div class="row" style="height: 100%;">
    <section id="holaplex-setup" class="col-md-12 col-sm-12 col-lg-12 holaplex-tab-content active">
      <div class="row">
        <div class="col-lg-6">
          <h2><?php esc_html_e('Holaplex Settings', 'holaplex-wp'); ?></h2>
          <table class="form-table">
            <tbody>
              <tr valign="top">
                <th scope="row"><?php esc_html_e('Connection Status', 'holaplex-wp'); ?></th>
                <td>
                  <div class="row">
                    <div class="col-6">
                      <?php echo esc_html($holaplex_status); ?>
                    </div>
                    <?php if ($holaplex_status == '✅ connected') : ?>
                      <div class="col-6">
                        <button id="holaplex-disconnect-btn" class="button button-secondary"><?php esc_html_e('Disconnect', 'holaplex-wp'); ?></button>
                      </div>
                    <?php endif; ?>

                  </div>
                </td>
              </tr>
              <tr valign="top">
                <th scope="row"><?php esc_html_e('Credits', 'holaplex-wp'); ?></th>
                <td>
                  <div class="row">
                    <div class="col-6">
                      <?php echo esc_html($holaplex_credits); ?>
                    </div>
                  </div>
                </td>
              </tr>
              <tr valign="top">
                <th scope="row"><?php esc_html_e('Organization Id', 'holaplex-wp'); ?></th>
                <td>
                  <input type="text" name="holaplex_org_id" value="<?php echo esc_attr(get_option('holaplex_org_id')); ?>">
                  <p class="description"><?php esc_html_e('Enter Organization Id', 'holaplex-wp'); ?></p>
                </td>
              </tr>
              <tr valign="top">
                <th scope="row"><?php esc_html_e('API Token', 'holaplex-wp'); ?></th>
                <td>
                  <input type="text" name="holaplex_api_key" value="<?php echo esc_attr(get_option('holaplex_api_key')); ?>">
                  <p class="description"><?php esc_html_e('Enter the API Token', 'holaplex-wp'); ?></p>
                </td>
              </tr>
            </tbody>
          </table>
          <div class="row">
          <?php wp_nonce_field(HOLAPLEX_NONCE); ?>
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
        <div class="col-lg-9 col-md-11">
          <div class="row">
            <div class="col-md-6">
              <h4 class="description">
                Import your Holaplex Hub Drops as products to make them available to sell or claim through your store.
              </h4>
            </div>
            <div class="col-md-4">
              <div class="header-actions">
                <a href="#drops-modal" class="btn btn-primary align-self-right">Import drops</a>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-10">
              <ul class="responsive-table">
                <li class="table-header">
                  <div class="col-3">Name</div>
                  <div class="col-1">Supply</div>
                  <div class="col-1">Blockchain</div>
                  <div class="col-2">Status</div>
                  <div class="col-2">Import</div>
                </li>
                <?php

                // if $holaplex_products is not empty show the products, else show message
                if (!empty($holaplex_products)) {
                  foreach ($holaplex_products as $product) {

                    $nonce = wp_create_nonce(HOLAPLEX_NONCE);
                    $holaplex_drop_id = $product->get_meta('holaplex_drop_id');
                    $holaplex_project_id = $product->get_meta('holaplex_project_id');
                    $blockchain = $product->get_meta('holaplex_drop_blockchain');
                    // get all project ids 
                    $project_ids = array_map(function ($project) {
                      return $project['id'];
                    }, $holaplex_projects);

                    // check if holaplex_project_id is in project_ids
                    if (!in_array($holaplex_project_id, $project_ids)) {
                      continue;
                    } 

                    $drop = $project_drops[$holaplex_drop_id];
                    $drop_name = $drop['collection']['metadataJson']['name'];
                    $collection_supply = $drop['collection']['supply'] - $drop['collection']['totalMints'];
                    $drop_status = $drop['status'];

                    echo "<li class='table-row'>";
                    echo "<div class='col-3'>
                        <a target='_blank' href='" . esc_url("https://hub.holaplex.com/projects/$holaplex_project_id/drops/$holaplex_drop_id/mints") . "'>" . esc_html($drop_name) .
                      "</a><br /> <p class='product-name'>Product: <a href='".esc_url( get_edit_post_link( $product->get_id() ) )."'>" . esc_html($product->get_name()) . "</a></p></div>";
                    echo "<div class='col-1'>" . esc_html($collection_supply) . "</div>";
                    echo "<div class='col-1'>" . esc_html($blockchain) . "</div>";
                    echo "<div class='col-2'>" . esc_html($drop_status) . "</div>";
                    echo "<div class='col-2'><button data-product-id='" . esc_attr($product->get_id()) . "' data-wp-nonce='" . esc_attr($nonce) . "' class='btn btn-remove'>Remove</button></div>";
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
      <?php include_once(HOLAPLEX_PLUGIN_PATH . 'admin/partials/holaplex-wp-admin-import-drops.php'); ?>
    </section>
    <section id="holaplex-gate" class="col-md-12 col-sm-12 col-lg-12 holaplex-tab-content">
      <?php
        // check if admin permissions to manage this
        if (!current_user_can('manage_options')) {
          wp_die(esc_html('You do not have sufficient permissions to access this page'));
        }

      ?>
      <div class="holaplex-options-wrapper wrap">
        <h1><?php esc_html_e('Content gate settings', 'holaplex-wp'); ?></h1>
        <div class="row">
          <div class="col-lg-4">
              <div class="card">
                <h2><?php esc_html_e('Default replacement content', 'holaplex-wp'); ?></h2>
                <?php
                $custom_text = $holaplex_display_custom_text;

                $editor_settings = array('textarea_name' => 'holaplex_custom_text', 'textarea_rows' => 5);
                wp_editor($custom_text, 'holaplex_custom_text_field', $editor_settings);
                ?>
              </div>

              <div class="card">
                <h2><?php esc_html_e('Shortcode help', 'holaplex-wp'); ?></h2>
                <span class="description"><?php echo 'You can limit the visibility of content in the post options or by using a shortcode - just wrap the content between the opening tag: <br><br><strong>[holaplexcode id="33"]</strong>&nbsp;&nbsp; and closing tag:&nbsp;&nbsp; <strong>[/holaplexcode]</strong>.<br></br> Enter the product id as value of the "id" attribute.'; ?></span>
              </div>
          </div>
          <div class="col-lg-4">
            <div class="card postbox">
              <h2><?php esc_html_e('Excerpt settings*', 'holaplex-wp'); ?></h2>
              <p class="description"><?php esc_html_e('*These options are active when the "Hide content and display only excerpt" option is used', 'holaplex-wp'); ?></p>
              <table class="form-table">
                <tbody>
                  <tr>
                    <th scope="row">
                      <label for="holaplex_excerpt_length"><?php esc_html_e('Excerpt length', 'holaplex-wp'); ?></label>
                    </th>

                    <td>
                      <?php

                      if (get_option("holaplex_fading_excerpt_info")) {
                        $holaplex_fading_excerpt = get_option("holaplex_fading_excerpt_info");
                      } else {
                        $holaplex_fading_excerpt = 1;
                      }
                      ?>
                      <input class="small-text" type="number" min="1" name="holaplex_excerpt_length" value="<?php echo esc_attr($holaplex_excerpt_length) ?>"><span> characters</span>
                      <p class="description"><?php esc_html_e('The length of the automatically generated excerpt. It does not apply to custom post excerpt.', 'holaplex-wp'); ?></p>
                    </td>
                  </tr>

                  <tr>
                    <th scope="row">
                      <label for="holaplex_fading_excerpt_info"><?php esc_html_e('Excerpt fading', 'holaplex-wp'); ?></label>
                    </th>

                    <td>
                      <fieldset>
                        <label>
                          <input style="width: 5px !important;" type="radio" name="holaplex_fading_excerpt_info" value="hide_fading_excerpt" <?php checked('hide_fading_excerpt', $holaplex_fading_excerpt); ?> /> <label for="hide_fading_excerpt" class="selectit"><?php esc_html_e('Disable excerpt fading', 'holaplex-wp'); ?></label><br />
                        </label><br>
                        <label>
                          <input style="width: 5px !important;" type="radio" name="holaplex_fading_excerpt_info" value="show_fading_excerpt" <?php checked('show_fading_excerpt', $holaplex_fading_excerpt); ?><?php echo ($holaplex_fading_excerpt === 1) ? ' checked="checked"' : ''; ?> /> <label for="show_fading_excerpt" class="selectit"><?php esc_html_e('Enable excerpt fading', 'holaplex-wp'); ?></label><br />
                        </label>
                      </fieldset>
                    </td>
                  </tr>

                </tbody>
              </table>
            </div>

          </div>
        </div>

      </div>
    </section>
  </div>
</div>