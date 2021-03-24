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

// If kiosk mode is enabled, then do a special kiosk logout.
if ($_SESSION['software']['kiosk']['enabled'] == true) {
    go(PATH . SOFTWARE_DIRECTORY . '/kiosk.php?action=logout');
}

logout();

// if there is a send to value, then send the user to that page
if ($_REQUEST['send_to'] != '') {
    header('Location: ' . URL_SCHEME . HOSTNAME . $_REQUEST['send_to'] . '?logged_out=true');

// else, print a default logout page
} else {
    // Start the session again because we killed it when we logged out above,
    // and we are going to initialize some session values below.
    session_start();

    // we need to initialize the device type now because the logout function that
    // was called above clears session and device type cookie, and we want to show
    // the correct version of the logout screen
    initialize_device_type();

    // We need to initialize the token in order to have the token available on the screen that is outputted now.
    initialize_token();

    require_once(dirname(__FILE__) . '/get_logout_screen_content.php');
    
    print get_logout_screen(get_logout_screen_content());
}
?>