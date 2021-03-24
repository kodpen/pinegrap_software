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

validate_token_field();

// delete applied gift card
$query = "DELETE FROM applied_gift_cards WHERE (id = '" . escape($_GET['applied_gift_card_id']) . "') AND (order_id = '" . $_SESSION['ecommerce']['order_id'] . "')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

// send user back to previous screen
header('Location: ' . URL_SCHEME . HOSTNAME . $_GET['send_to']);
?>