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

// if user is not logged in, send user to registration entrance screen to login or register
if (validate_login($_SESSION['sessionusername'], $_SESSION['sessionpassword']) == false) {
    header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/registration_entrance.php?send_to=' . urlencode(get_request_uri()));
    exit();
}

// check to see if recipient exists
$query = "SELECT id FROM address_book WHERE id = '" . escape($_GET['id']) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

// if recipient does not exist, output error
if (mysqli_num_rows($result) == 0) {
    output_error('The recipient could not be removed because it does not exist. <a href="javascript:history.go(-1)">Go back</a>.');
}

// get user id in order to see if user has access to remove recipient
$query = "SELECT user_id FROM user WHERE user_username = '" . escape($_SESSION['sessionusername']) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$user_id = $row['user_id'];

// check to see if user has access to remove this recipient
$query = "SELECT id FROM address_book WHERE id = '" . escape($_GET['id']) . "' AND user = $user_id";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

// if recipient does not have access to remove recipient, output error
if (mysqli_num_rows($result) == 0) {
    output_error('Access denied. You do not have access to remove this recipient. <a href="javascript:history.go(-1)">Go back</a>.');
}

// remove recipient from address book
$query = "DELETE FROM address_book WHERE id = '" . escape($_GET['id']) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

go(get_page_type_url('my account'));