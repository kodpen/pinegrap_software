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

include('init.php');
$user = validate_user();
validate_ecommerce_access($user);

validate_token_field();

$new_product_id = duplicate_product($_GET['id']);

$old_product_name = db_value("SELECT name FROM products WHERE id = '" . escape($_GET['id']) . "'");

log_activity('product (' . $old_product_name . ') was duplicated', $_SESSION['sessionusername']);

include_once('liveform.class.php');
$liveform_edit_product = new liveform('edit_product');
$liveform_edit_product->add_notice('The product has been duplicated, and you are now editing the duplicate.');

go(PATH . SOFTWARE_DIRECTORY . '/edit_product.php?id=' . $new_product_id);
?>