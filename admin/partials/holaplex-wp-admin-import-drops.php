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
      <h2><?php _e('Import Drops', 'holaplex-wp'); ?></h2>
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
            echo '<div class="col-1">' . showSyncActions($drop, $project['id']) . '</div>';
            echo '</li>';
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