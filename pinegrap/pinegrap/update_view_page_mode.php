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

if ($_GET['mode'] == 'edit') {
    $_SESSION['software']['view_page_mode'] = 'edit';

    // force toolbar closed when in edit mode
    $_SESSION['software']['toolbar_enabled'] = false;

} else {
    $_SESSION['software']['view_page_mode'] = 'preview';
}

// If there is not a current cookie or it is not equal to the current
// view page mode, then create/update it.
if ($_COOKIE['software']['view_page_mode'] != $_SESSION['software']['view_page_mode']) {
    setcookie('software[view_page_mode]', $_SESSION['software']['view_page_mode'], time() + 315360000, '/');
}

go($_GET['send_to']);
?>