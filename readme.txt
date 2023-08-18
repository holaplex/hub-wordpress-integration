=== Holaplex Hub ===
Contributors: k0d3d
Tags: woocommerce, holaplex, nft, integration
Requires at least: 6.1
Tested up to: 6.3
Requires PHP: 7.0
Stable tag: 1.0.44
License: MIT License
License URI: https://opensource.org/licenses/MIT

== Description ==
The Holaplex Hub enables seamless integration 
between your WooCommerce-powered online store and Holaplex.com Hub. 
This plugin allows you to sync and sell NFT Drops created on Holaplex.com directly through your Wordpress site. More documentation can be found on 

== Features ==
Sync Organization Drops: Fetch Drops metadata, images, and details from Holaplex Hub and display them on your WooCommerce store as products.
Easy Minting: Mint Drops automatically when a customer buys a product. 
Content Gating: Restrict access to content and posts to users who own specific NFTs.

== Installation ==
- Search for "Holaplex Hub" on Wordpress Plugin Installer and install or Download the zip file and Upload the holaplex-wp folder to the /wp-content/plugins/ directory.
- Activate the plugin through the 'Plugins' menu in WordPress.
- Go to the plugin settings page in your WordPress dashboard (WooCommerce > Settings > Holaplex Hub).
- Enter your Holaplex Organization and API token and save the settings.
- Refresh your Wordpress Permalinks settings

== Usage / How to == 

= Importing Organization Drops =
Importing drops from your Holaplex.com Hub into your Woocommerce store as products will easily allow your customers to mint the drop when they checkout the product on your store. 
   - Navigate to the Holaplex Hub settings page (Woocommerce -> Settings -> Holaplex Hub), click on the "Drops" tab
   - Click on "Import Drops". A dialogue box will appear with a list of all the drops in your connected Holaplex.com Hub organization.
   - Click on "Import" to import the drop into your Woocommerce store as a product.

= Content Gating =
Content gating allows you to restrict access to content and posts to users who own specific NFTs. Unauthroized users will be shown a message instead of the protected content or redirected to a specified page.
    - Navigate to the Holaplex Hub settings page (Woocommerce -> Settings -> Holaplex Hub), click on the "Content Gating" tab
    - Configure a global default message to replace protected content.
    - Create or edit and existing post or page. Scroll to the bottom of the page and click on the "Holaplex Content Gating" tab.
    - Select the rule you want applied to the whole post or page. You can also select a specific imported drop to gate the content to.
    - You can wrap blocks on content / text within [holaplexcode id="xx"] [/holaplexcode] shortcodes to apply the rule to only that block of content. Where "id" refers to the product id of the drop you want to gate the content to.
    - You can also specify a page to redirect unauthorized users to. 


== Support ==
For support and assistance, please visit our GitHub repository and open an issue.

== License ==
This plugin is licensed under the MIT License - see the LICENSE file for details.


== Screenshots ==

1. Connect your Holaplex Hub account. View available credits and drops.
2. Add New and View Imported Drops.
3. Manage Global Content Gating Settings.

== Changelog ==
= 1.0.60 =

Initial release of the Holaplex Integration Plugin for WordPress WooCommerce.

