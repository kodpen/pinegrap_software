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
validate_area_access($user, 'manager');

validate_token_field();

// Do different things depending on mode.
switch ($_GET['mode']) {
    // If user has selected a style from the pick list, then update current style.
    case 'preview':
        $_SESSION['software']['preview_style']['theme_' . $_SESSION['software']['preview_theme_id'] . '_page_' . $_GET['page_id'] . '_' . $_SESSION['software']['device_type']] = $_GET['style_id'];

        break;
    
    // If user clicked the set button, then save style in database.
    case 'set':
        // If the page id, theme id, and device type are known,
        // and the user has selected an option from the pick list,
        // then continue to update style.
        if (
            ($_GET['page_id'])
            && (isset($_SESSION['software']['preview_theme_id']) == true)
            && ($_SESSION['software']['device_type'])
            && (isset($_SESSION['software']['preview_style']['theme_' . $_SESSION['software']['preview_theme_id'] . '_page_' . $_GET['page_id'] . '_' . $_SESSION['software']['device_type']]) == true)
        ) {
            // If the user is currently previewing the activated theme or the "none" theme,
            // then update the activated style for the page.
            if (
                ($_SESSION['software']['preview_theme_id'] == db_value("SELECT id FROM files WHERE activated_" . $_SESSION['software']['device_type'] . "_theme = '1'"))
                || ($_SESSION['software']['preview_theme_id'] == '')
            ) {

                if ($_SESSION['software']['device_type'] == 'desktop') {
                    $sql_style_id_column = "page_style";
                } else {
                    $sql_style_id_column = "mobile_style_id";
                }

                db(
                    "UPDATE page
                    SET
                        $sql_style_id_column = '" . escape($_SESSION['software']['preview_style']['theme_' . $_SESSION['software']['preview_theme_id'] . '_page_' . $_GET['page_id'] . '_' . $_SESSION['software']['device_type']]) . "',
                        page_user = '" . USER_ID . "',
                        page_timestamp = UNIX_TIMESTAMP()
                    WHERE page_id = '" . escape($_GET['page_id']) . "'");

                // If a style was selected (not default/inherit), then get style name.
                if ($_SESSION['software']['preview_style']['theme_' . $_SESSION['software']['preview_theme_id'] . '_page_' . $_GET['page_id'] . '_' . $_SESSION['software']['device_type']]) {
                    $log_style_name = db_value("SELECT style_name FROM style WHERE style_id = '" . escape($_SESSION['software']['preview_style']['theme_' . $_SESSION['software']['preview_theme_id'] . '_page_' . $_GET['page_id'] . '_' . $_SESSION['software']['device_type']]) . "'");

                } else {
                    $log_style_name = 'Default (inherit)';
                }

                log_activity('activated ' . $_SESSION['software']['device_type'] . ' page style (' . $log_style_name . ') was set for page (' . get_page_name($_GET['page_id']) . ')', $_SESSION['sessionusername']);

            // Otherwise the user is not previewing the activated theme or "none" theme,
            // so update the preview style for the page.
            } else {

                // Delete existing record.
                db(
                    "DELETE FROM preview_styles
                    WHERE
                        (page_id = '" . escape($_GET['page_id']) . "')
                        AND (theme_id = '" . escape($_SESSION['software']['preview_theme_id']) . "')
                        AND (device_type = '" . escape($_SESSION['software']['device_type']) . "')");

                // If the user has selected a style, then add record for preview style.
                if ($_SESSION['software']['preview_style']['theme_' . $_SESSION['software']['preview_theme_id'] . '_page_' . $_GET['page_id'] . '_' . $_SESSION['software']['device_type']]) {
                    db(
                        "INSERT INTO preview_styles (
                            page_id,
                            theme_id,
                            style_id,
                            device_type)
                        VALUES (
                            '" . escape($_GET['page_id']) . "',
                            '" . escape($_SESSION['software']['preview_theme_id']) . "',
                            '" . escape($_SESSION['software']['preview_style']['theme_' . $_SESSION['software']['preview_theme_id'] . '_page_' . $_GET['page_id'] . '_' . $_SESSION['software']['device_type']]) . "',
                            '" . escape($_SESSION['software']['device_type']) . "')");
                }

                // If a style was selected (i.e not the activated style), then get style name.
                if ($_SESSION['software']['preview_style']['theme_' . $_SESSION['software']['preview_theme_id'] . '_page_' . $_GET['page_id'] . '_' . $_SESSION['software']['device_type']]) {
                    $log_style_name = db_value("SELECT style_name FROM style WHERE style_id = '" . escape($_SESSION['software']['preview_style']['theme_' . $_SESSION['software']['preview_theme_id'] . '_page_' . $_GET['page_id'] . '_' . $_SESSION['software']['device_type']]) . "'");

                } else {
                    $log_style_name = '[Activated]';
                }

                $log_theme_name = db_value("SELECT name FROM files WHERE id = '" . escape($_SESSION['software']['preview_theme_id']) . "'");

                log_activity('preview ' . $_SESSION['software']['device_type'] . ' page style (' . $log_style_name . ') was set for page (' . get_page_name($_GET['page_id']) . ') and theme (' . $log_theme_name . ')', $_SESSION['sessionusername']);
            }
        }
        
        break;
}

go($_GET['send_to']);
?>