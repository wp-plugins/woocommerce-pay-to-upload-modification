=== WooCommerce Pay to Upload ===
Contributors: wpashokg, patrickgarman 
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=RTAAFGGL53DMG
Tags: woocommerce, file upload
Requires at least: 3.3.1
Tested up to: 4.2.2 
Stable tag: 2.0.2
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html


Allow customers to pay to upload a file.

== Description ==
Hello Friends,<br>
This is a wordpress woocommerce support plugin which will enable the customers to upload files after purchase.<br>

Features:

    Admin can centrally control the number of uploads per product.
    Admin can control the the number of uploads per product on a particular product.
    Admin can enable / disable the file upload for a particular product.
    Admin can set the respective file types to be uploaded.
    This plugin would be in continuous revision and support.


== Installation ==
	
 * Upload the plugin files to the '/wp-content/plugins/' directory
 * Activate the plugin through the 'Plugins' menu in WordPress
 
 == Frequently Asked Questions ==

= Why would I want the user to upload a file once payment has been made rather than during the checkout process? =

If you allowed every user to upload a file without yet making a payment then every time a payment failed or you used a payment method that waits for an IPN response, your disk space will be consumed by files you should not have received. By waiting until after payment this keeps things cleaner.

= Where are files stored? =

Uploaded files are stored in your wp-content/uploads folder, a new folder named "wc-pay-to-upload" will be created and within that each order will have it's own folder based on the order ID.

= How do I access the uploaded files? =

Once files are uploaded you can view them from the admin view of the order. A meta box is added to the side with links to the files.

== Changelog ==
= 2.0.2 =
 * Updated Product Wise upload segregation for both admin and front end.
 * So it would be easy to Identify which file is being uploaded to which product.

= 2.0.1 =
 * Made Some technical changes to make the plugin work with latest woocommerce verrsion.

= 1.2 =
 * Modified to support the latest version of woocommerce.

= 1.1.3 =
 * Added Admin email notification when the user uploads the file.

= 1.1.2 =
 * Fixed the uploader issues which was not working in the newer wordpress versions. Fixed by Ashok G


= 1.1.1 =
BUG: uploader() did not verify that the passable statuses were an array, ie one or no statuses available.
 
= 1.1.0 =
Added multiselect option for selecting statuses that allow for uploads to take place, previously was only if it was *NOT* on-hold or pending.
 
= 1.0.1 =
Fixed minor bugs and notices
 
= 1.0.0 =
First Release
