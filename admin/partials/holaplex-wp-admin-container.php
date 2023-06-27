<div class="container-fluid holaplex-app">
  <div class="row">
    <!-- two tabs -->
    <div class="col-md-12 col-sm-12 col-lg-6">
      <div class="holaplex-tabs">
        <button class="holaplex-tablinks active" data-tab="#holaplex-setup">Setup / Config</button>
        <button class="holaplex-tablinks" data-tab="#holaplex-drops">Drops</button>
      </div>
    </div>
  </div>
  <div class="row">
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
      <?php
      // if no projects, show a message and return
      if (empty($holaplex_projects)) {
        echo "<h2 class='help-title'>" . esc_html(__('Import Drops Help', 'holaplex-wp')) . "</h2>";
        echo "<p class='description help-mesg'>
								Connect to Holaplex Hub on the SETUP / CONFIG tab before you can import drops.
							</p>";
        return;
      }
      ?>
      <h2><?php _e('Project Drops', 'holaplex-wp'); ?></h2>
      <p class="description">
        Creates a product for each drop in your Holaplex projects.
      </p>

      <ul class="responsive-table">
        <li class="table-header">
          <div class="col-1">Project</div>
          <div class="col-2">Drop name</div>
          <div class="col-1">Supply</div>
          <div class="col-1">Status</div>
          <div class="col-1">Import</div>
        </li>
        <!-- loop through project drops -->
        <?php foreach ($holaplex_projects as $project) {
          foreach ($project['drops'] as $drop) {
            $project_name = $project['name'];
            $drop_id = substr($drop['id'], -6);
            $drop_name = $drop['collection']['metadataJson']['name'];
            $collection_supply = $drop['collection']['supply'] - $drop['collection']['totalMints'];
            $drop_status = $drop['status'];

            echo '<li class="table-row">';
            echo '<div class="col-1">' . esc_html($project_name) . '</div>';
            echo '<div class="col-2">' . esc_html($drop_name) . '</div>';
            echo '<div class="col-1">' . esc_html($collection_supply) . '</div>';
            echo '<div class="col-1">' . esc_html($drop_status) . '</div>';
            echo '<div class="col-1">' . showSyncActions($drop) . '</div>';
            echo '</li>';
          }
        }
        ?>
      </ul>
    </section>
  </div>
</div>