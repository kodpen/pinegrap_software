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

// clear the session
unset($_SESSION['software']['theme_designer'][$_GET['file_id']]);

// if there is a send to being passed then send the user to the next screen
if ($_GET['send_to'] != '') {
    header('Location: ' . URL_SCHEME . HOSTNAME . $_GET['send_to']);
    exit();
}
?>
