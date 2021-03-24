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

// if the passed device type is valid, then continue to update the device type
if (
    ($_GET['device_type'] == 'desktop')
    || ($_GET['device_type'] == 'mobile')
) {
    // update the device type in the session
    $_SESSION['software']['device_type'] = $_GET['device_type'];

    // Set device type in cookie for 10 years so that we can remember the device type
    setcookie('software[device_type]', $_SESSION['software']['device_type'], time() + 315360000, '/');
}

header('Location: ' . URL_SCHEME . HOSTNAME . $_GET['send_to']);
exit();
?>