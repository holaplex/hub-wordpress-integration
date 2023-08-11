<div class="modal-wrapper">
  <div id="drops-modal" class="modal">
    <div class="modal__content">
      <?php
      // if no projects, show a message and return
      if (empty($holaplex_projects)) {
        echo "<h2 class='help-title'>" . esc_html(__('Import Drops Help', 'holaplex-wp')) . "</h2>";
        echo "<p class='description help-mesg'>
                  Connect to Holaplex Hub on the SETUP / CONFIG tab before you can import drops.
                </p>";
        echo '<a href="#" class="modal__close">&times;</a>';
        return;
      }
      ?>
      <h2><?php esc_html_e('Import Drops', 'holaplex-wp'); ?></h2>
      <p class="description">
        Creates a product for each drop in your Holaplex projects.
      </p>

      <ul class="responsive-table">
        <li class="table-header">
          <div class="col-1">Project</div>
          <div class="col-2">Drop name</div>
          <div class="col-1">Blockchain</div>
          <div class="col-1">Supply</div>
          <div class="col-1">Status</div>
          <div class="col-1">Import</div>
        </li>

        <!-- loop through project drops -->
        <?php foreach ($holaplex_projects as $project) {
          if (!$project['drops'] || empty($project['drops'])) {
            continue;
          }
          foreach ($project['drops'] as $drop) {
            $project_name = $project['name'];
            $drop_id = substr($drop['id'], -6);
            $blockchain = isset($drop['collection']) && isset($drop['collection']['blockchain']) ? $drop['collection']['blockchain'] : 'N/A';
            $drop_name = isset($drop['collection']) && isset($drop['collection']['metadataJson']) ? $drop['collection']['metadataJson']['name'] : 'N/A';
            $collection_supply = $drop['collection']['supply'] - $drop['collection']['totalMints'];
            $drop_status = $drop['status'];

        ?>

            <li class="table-row">
              <div class="col-1"><?php echo esc_html($project_name) ?></div>
              <div class="col-2"><?php echo esc_html($drop_name) ?></div>
              <div class="col-1"><?php echo esc_html($blockchain) ?></div>
              <div class="col-1"><?php echo esc_html($collection_supply) ?></div>
              <div class="col-1"><?php echo esc_html($drop_status) ?></div>
              <div class="col-1"><?php showSyncActions($drop, $project['id']) ?></div>
            </li>


        <?php

          }
        }
        ?>
      </ul>

      <div class="modal__footer">
        <button class="btn btn-success" id="import-done">Done</button>
      </div>

      <a href="#" class="modal__close">&times;</a>
    </div>
  </div>
</div>