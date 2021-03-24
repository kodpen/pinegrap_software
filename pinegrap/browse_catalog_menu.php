<?php

/**
 *
 * liveSite - Enterprise Website Platform
 *
 * @author      Camelback Web Architects
 * @link        https://livesite.com
 * @copyright   2001-2019 Camelback Consulting, Inc.
 * @license     https://opensource.org/licenses/mit-license.html MIT License
 *
 */

// this script is used by the catalog menu to send a visitor to a specific product group that was selected in the catalog menu
include('init.php');

header('Location: ' . URL_SCHEME . HOSTNAME . PATH . encode_url_path(get_page_name($_GET['current_page_id'])) . '/' . encode_url_path(get_catalog_item_address_name_from_id($_GET['product_group_id'], 'product group')) . '?previous_url_id=' . $_GET['previous_url_id']);
exit();
?>