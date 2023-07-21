<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Holaplex_Wp
 * @subpackage Holaplex_Wp/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<?php

$holaplex_meta_info = get_post_meta($post->ID, 'holaplex_meta_info', true) ? get_post_meta($post->ID, 'holaplex_meta_info', true) : ''; 
$holaplex_selected_page_id = get_post_meta($post->ID, 'holaplex_selected_page_id', true) ? get_post_meta($post->ID, 'holaplex_selected_page_id', true) : '';
$holaplex_selected_product_id = get_post_meta($post->ID, 'holaplex_selected_product_id', true) ? get_post_meta($post->ID, 'holaplex_selected_product_id', true) : '';
?>
<div class="holaplex-app">
  <div class="bootstrap-wrapper">
    <?php 
      wp_nonce_field(HOLAPLEX_NONCE);
    ?>
    <div class="row">
      <div class="col-lg-4">
        <div class="alignleft">
          <input id="holaplex_show_default_meta" type="radio" name="holaplex_meta_info" value="show_default_meta" <?php checked('show_default_meta', $holaplex_meta_info); ?><?php echo esc_html_e(($holaplex_meta_info === 1) ? ' checked="checked"' : ''); ?> /> <label for="show_default_meta" class="selectit"><?php esc_html_e('Show content', 'holaplex-wp'); ?></label><br />
          <input id="holaplex_hide_default_meta" type="radio" name="holaplex_meta_info" value="hide_default_meta" <?php checked('hide_default_meta', $holaplex_meta_info); ?> /> <label for="hide_default_meta" class="selectit"><?php esc_html_e('Hide content and display default placeholder content', 'holaplex-wp'); ?></label><br />
          <input id="holaplex_hide_excerpt_meta" type="radio" name="holaplex_meta_info" value="hide_excerpt_meta" <?php checked('hide_excerpt_meta', $holaplex_meta_info); ?> /> <label for="hide_excerpt_meta" class="selectit"><?php esc_html_e('Hide content and display only excerpt and default content', 'holaplex-wp'); ?></label><br />
          <input id="holaplex_redirect_meta" type="radio" name="holaplex_meta_info" value="redirect_meta" <?php checked('redirect_meta', $holaplex_meta_info); ?> /> <label for="redirect_meta" class="selectit"><?php esc_html_e('Redirect to another page', 'holaplex-wp'); ?></label><br />
        </div>

      </div>

      <div class="col-lg-2">
        <div class="holaplex-selected-product">
          <h2><?php esc_html_e('Required products', 'holaplex-wp'); ?></h2>
          <?php
          holaplex_products_dropdown($post, $holaplex_selected_product_id)
          ?>
        </div>
        <div class="holaplex-selected-page">
          <h2><?php esc_html_e('Redirect page', 'holaplex-wp'); ?></h2>
          <?php
          wp_dropdown_pages(array('name' => 'holaplex_selected_page_id', 'selected' => esc_attr($holaplex_selected_page_id) ));
          ?>
        </div>
      </div>
      <div class="col-lg-3">
        <div class="card">
          <h2><?php esc_html_e('Shortcode help', 'holaplex-wp'); ?></h2>
          <span class="description"><?php echo 'You can limit the visibility of content in the post options or by using a shortcode - just wrap the content between the opening tag: <br><br><strong>[holaplexcode id="33"]</strong>&nbsp;&nbsp; and closing tag:&nbsp;&nbsp; <strong>[/holaplexcode]</strong>.<br></br> Enter the product id as value of the "id" attribute.'; ?></span>
        </div>
      </div>
    </div>
  </div>
</div>

<hr />

<?php

?>