<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Holaplex_Wp
 * @subpackage Holaplex_Wp/public/partials
 */
?>

<?php




?>


<div class="holaplex-public-app">
  
  <!-- This file should primarily consist of HTML with a little bit of PHP. -->
  <h4>My NFTs</h4>
  <?php
  
  $nfts = $customer_nfts['data']['project']['customer']['mints'];
  
  // if no nfts, show message
  if (empty($nfts)) {
    echo '<p>You have no NFTs yet.</p>';
    return;
  }
  
  ?>
  
  
  <section class="flex flex-col gap-8 items-center mt-8">
    <article class="w-full flex-grow">
      <div class="h-full flex flex-col flex-1">
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
          <?php foreach ($nfts as $nft) { ?>
            <div class="flex flex-col rounded-md p-6">
              <img class="rounded-md w-full aspect-square object-cover" src="<?php echo esc_attr($nft['collection']['metadataJson']['image']); ?>" alt="<?php echo esc_attr($nft['collection']['metadataJson']['name']); ?>">
              <div class="flex justify-between mt-4">
                <ul class="flex flex-col gap-2">
                  <li class="flex flex-col gap-1">
                    <span class="text-gray-400">Name</span>
                    <span>
                    <a  target="_blank" href="https://solscan.io/token/<?php echo esc_attr($nft['address']); ?>">
                      <?php echo esc_attr($nft['collection']['metadataJson']['name']); ?>
                    </a>
                    </span>
                  </li>
                  <li class="flex flex-col gap-1">
                    <span class="text-gray-400">Minted on</span>
                    <span>
                      <?php
                      $date = new DateTime($nft['createdAt']);
                      echo esc_attr($date->format('m/d/Y H:i'));
                      ?>
                    </span>
                  </li>
                </ul>
                <div class="nft-blockchain">
                  <a  target="_blank" href="https://solscan.io/token/<?php echo esc_attr($nft['address']); ?>">
                    <img decoding="auto" width="30" height="30" src="<?php echo esc_attr(plugin_dir_url(__FILE__) . '../images/coins.png'); ?>" alt="Solscan"  />
                  </a>
                </div>
              </div>
            </div>
          <?php } ?>
        </div>
        <div></div>
      </div>
    </article>
  </section>

</div>
