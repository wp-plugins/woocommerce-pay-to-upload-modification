<?php
/*
Plugin Name: WooCommerce Pay to Upload
Plugin URI: http://www.patrickgarman.com/wordpress-plugins/woocommerce/woocommerce-pay-to-upload/
Description: Allow customers to pay to upload a file. Developed on the Airy plugin framework
Version: 1.1.1
Author: Patrick Garman
Author URI: http://www.patrickgarman.com/

Copyright: © 2012 Patrick Garman.
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Special thanks to Michael McCarthy PhD ( @WPC4U ) for having this plugin developed and allowing it to be shared.
*/

/**
 * Required files
 **/
require_once('classes/class-airy-framework.php' );
require_once('classes/class-wc-pay-to-upload.php' );

/**
 * Start the engines!
 **/
$WC_Pay_To_Upload = new WC_Pay_To_Upload;