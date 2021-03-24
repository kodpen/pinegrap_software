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

$default_inactivity_time = 30;
$default_dialog_time = 15;
$default_dialog_message = 'Do you want to continue?';
$default_continue_button_label = 'Continue';
$default_logout_button_label = 'Exit';

switch ($_GET['action']) {
    default:
        $_SESSION['software']['kiosk']['enabled'] = true;
        $_SESSION['software']['kiosk']['activity'] = false;
        $_SESSION['software']['kiosk']['url'] = $_GET['url'];

        // If an inactivity time was passed in the query string,
        // and the value is a positive integer, then store the value in the session.
        if (
            ($_GET['inactivity_time'] != '')
            && (is_numeric($_GET['inactivity_time']) == true)
            && ($_GET['inactivity_time'] > 0)
            && ($_GET['inactivity_time'] == round($_GET['inactivity_time']))
        ) {
            $_SESSION['software']['kiosk']['inactivity_time'] = $_GET['inactivity_time'];

        // Otherwise store default value in session.
        } else {
            $_SESSION['software']['kiosk']['inactivity_time'] = $default_inactivity_time;
        }

        // If a dialog time was passed in the query string,
        // and the value is a positive integer, then store the value in the session.
        if (
            ($_GET['dialog_time'] != '')
            && (is_numeric($_GET['dialog_time']) == true)
            && ($_GET['dialog_time'] > 0)
            && ($_GET['dialog_time'] == round($_GET['dialog_time']))
        ) {
            $_SESSION['software']['kiosk']['dialog_time'] = $_GET['dialog_time'];

        // Otherwise store default value in session.
        } else {
            $_SESSION['software']['kiosk']['dialog_time'] = $default_dialog_time;
        }

        // If a dialog message was passed in the query string, then store value in session.
        if ($_GET['dialog_message'] != '') {
            $_SESSION['software']['kiosk']['dialog_message'] = $_GET['dialog_message'];

        // Otherwise store default value in session.
        } else {
            $_SESSION['software']['kiosk']['dialog_message'] = $default_dialog_message;
        }

        // If a continue button label was passed in the query string, then store value in session.
        if ($_GET['continue_button_label'] != '') {
            $_SESSION['software']['kiosk']['continue_button_label'] = $_GET['continue_button_label'];
            
        // Otherwise store default value in session.
        } else {
            $_SESSION['software']['kiosk']['continue_button_label'] = $default_continue_button_label;
        }

        // If a logout button label was passed in the query string, then store value in session.
        if ($_GET['logout_button_label'] != '') {
            $_SESSION['software']['kiosk']['logout_button_label'] = $_GET['logout_button_label'];
            
        // Otherwise store default value in session.
        } else {
            $_SESSION['software']['kiosk']['logout_button_label'] = $default_logout_button_label;
        }

        if ($_GET['url'] != '') {
            go($_GET['url']);
        } else {
            go(PATH);
        }

        break;

    case 'update_activity':
        $_SESSION['software']['kiosk']['activity'] = true;
        exit();

        break;

    case 'logout':
        // Remember kiosk properties before we clear session, so we can re-init session
        // with those values after we are done clearing the session.
        $url = $_SESSION['software']['kiosk']['url'];
        $inactivity_time = $_SESSION['software']['kiosk']['inactivity_time'];
        $dialog_time = $_SESSION['software']['kiosk']['dialog_time'];
        $dialog_message = $_SESSION['software']['kiosk']['dialog_message'];
        $continue_button_label = $_SESSION['software']['kiosk']['continue_button_label'];
        $logout_button_label = $_SESSION['software']['kiosk']['logout_button_label'];

        // If kiosk user is logged into a user account, then logout from that user account.
        if ($_SESSION['sessionusername'] != '') {
            logout();

        // Otherwise the kiosk user is not logged into a user account, so just clear session.
        } else {
            session_unset();
            session_destroy();
        }

        $parameters = array();

        // If we know where we should send the visitor, then include URL parameter for that.
        if ($url != '') {
            $parameters['url'] = $url;
        }

        // If inactivity time is not the default, then include URL parameter for that.
        if ($inactivity_time != $default_inactivity_time) {
            $parameters['inactivity_time'] = $inactivity_time;
        }

        // If dialog time is not the default, then include URL parameter for that.
        if ($dialog_time != $default_dialog_time) {
            $parameters['dialog_time'] = $dialog_time;
        }

        // If dialog message is not the default, then include URL parameter for that.
        if ($dialog_message != $default_dialog_message) {
            $parameters['dialog_message'] = $dialog_message;
        }

        // If continue button label is not the default, then include URL parameter for that.
        if ($continue_button_label != $default_continue_button_label) {
            $parameters['continue_button_label'] = $continue_button_label;
        }

        // If logout button label is not the default, then include URL parameter for that.
        if ($logout_button_label != $default_logout_button_label) {
            $parameters['logout_button_label'] = $logout_button_label;
        }

        $init_url = build_url(array(
            'url' => PATH . SOFTWARE_DIRECTORY . '/kiosk.php',
            'parameters' => $parameters));

        // Send visitor to kiosk initialization script.
        go($init_url);

        break;
}
?>