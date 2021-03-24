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
include_once('liveform.class.php');
$liveform = new liveform('view_pages');
$user = validate_user();
validate_area_access($user, 'user');

// If a theme is being previewed then get the activated themes.
// We will use further below.
if ($_SESSION['software']['preview_theme_id']) {
    $activated_desktop_theme_id = db_value("SELECT id FROM files WHERE activated_desktop_theme = '1'");
    $activated_mobile_theme_id = db_value("SELECT id FROM files WHERE activated_mobile_theme = '1'");
}

// if the form has not been submitted yet, then output form
if (!$_POST) {
    $output_preview_style_warning_row = '';
    $default_style_label = 'Default (inherit)';

    // If the user is currently previewing a theme that is not the activated theme
    if (
        ($_SESSION['software']['preview_theme_id'])
        && ($_SESSION['software']['preview_theme_id'] != $activated_desktop_theme_id)
        && ($_SESSION['software']['preview_theme_id'] != $activated_mobile_theme_id)
    ) {
        $output_preview_style_warning_row =
            '<tr>
                <td colspan="2">
                    <p class="software_notice"><strong>Theme Preview Mode:</strong> You are currently previewing a Theme that is not activated, so your Page Style and
                        Mobile Page Style selections below will update the preview Page Style, and not the activated
                        Page Style.  The preview Page Styles that you set here will be activated when you activate the Theme.</p>
                </td>
            </tr>';

        $default_style_label = '[Activated]';
    }

    // set options for search pick list
    $site_search_options =
        array(
            '' => '',
            'Include' => '1',
            'Exclude' => '0'
        );
    
    // set options for sitemap pick list
    $sitemap_options =
        array(
            '' => '',
            'Include' => '1',
            'Exclude' => '0'
        );

    print
        '<!DOCTYPE html>
        <html lang="'.language_ruler().'">
            <head>
                <meta charset="utf-8">
                <title>Modify Pages</title>
                ' . get_generator_meta_tag() . '
                ' . output_control_panel_header_includes() . '
                <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
                <script type="text/javascript">
                    function edit_pages()
                    {
                        // if there is a value then update field in the form
                        if (document.getElementById("folder").value != "") {
                            opener.document.form.move_to_folder.value = document.getElementById("folder").value;
                        }
                        
                        // if there is a value then update field in the form
                        if (document.getElementById("page_style").value != "") {
                            opener.document.form.edit_page_style.value = document.getElementById("page_style").value;
                        }

                        // if there is a value then update field in the form
                        if (document.getElementById("mobile_style_id").value != "") {
                            opener.document.form.edit_mobile_style_id.value = document.getElementById("mobile_style_id").value;
                        }
                        
                        // if there is a value then update field in the form
                        if (document.getElementById("site_search").value != "") {
                            opener.document.form.edit_site_search.value = document.getElementById("site_search").value;
                        }
                        
                        // if there is a value then update field in the form
                        if (document.getElementById("sitemap").value != "") {
                            opener.document.form.edit_sitemap.value = document.getElementById("sitemap").value;
                        }
                        
                        // close window
                        opener.document.form.submit();
                        window.close();
                    }
                </script>
            </head>
            <body class="pages" style="overflow:auto;">
                <div class="navigation fixed">
                    <div class="title" title="You may update various properties below for the selected pages. You may leave a pick list unselected if you do not want to modify a property.">Modify Pages
                    </div>

                    <ul class="right">
                        <li><a href="#!" class="red-text" onclick="window.close()" ><i class="material-icons">close</i></a></li>
                    </ul>
                </div>
                <div id="content">
                  
                    <table class="field" >
                        <tr>
                            <td>Folder:</td>
                            <td><select id="folder"><option value=""></option>' . select_folder() . '</select></td>
                        </tr>
                        ' . $output_preview_style_warning_row . '
                        <tr>
                            <td>Page Style:</td>
                            <td><select id="page_style"><option value=""></option>' . select_style('', $default_style_label) . '</select></td>
                        </tr>
                        <tr>
                            <td>Mobile Page Style:</td>
                            <td><select id="mobile_style_id"><option value=""></option>' . get_mobile_style_options('', $default_style_label) . '</select></td>
                        </tr>
                        <tr>
                            <td>Site Search:</td>
                            <td>
                                ' . $liveform->output_field(array('type'=>'select', 'id'=>'site_search', 'options'=>$site_search_options)) . '
                            </td>
                        </tr>
                        <tr>
                            <td>sitemap.xml:</td>
                            <td>
                                ' . $liveform->output_field(array('type'=>'select', 'id'=>'sitemap', 'options'=>$sitemap_options)) . '
                            </td>
                        </tr>
                    </table>
                    <div class="buttons">
                        <input type="button" value="Modify Pages" class="submit-primary" onclick="edit_pages()" />
                    </div>
                </div>
            </body>
        </html>';
                
// else the form has been submitted, so process it
} else {
    validate_token_field();
    
    // if at least one page was selected
    if ($_POST['pages']) {
        $number_of_pages = 0;
        
        switch ($_POST['action']) {
            // if pages are being edited, proceed
            case 'edit':
                // if a folder was selected to move the page(s) to
                // and if user does not have access to the folder that he/she is trying to move pages to, output error
                if (($_POST['move_to_folder']) && (check_edit_access($_POST['move_to_folder']) == false)) {
                    output_error('You do not have access to move pages to the folder that you selected. <a href="javascript:history.go(-1);">Go back</a>.');
                }
                
                // loop through each page and process actions
                foreach ($_POST['pages'] as $page_id) {
                    // get folder that page is in, in order to validate access
                    $query = 
                        "SELECT
                            page_folder, 
                            page_search, 
                            page_meta_keywords,
                            page_type
                        FROM page 
                        WHERE page_id = '" . escape($page_id) . "'";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    $row = mysqli_fetch_assoc($result);
                    $original_page_search = $row['page_search'];
                    $original_meta_keywords = $row['page_meta_keywords'];
                    $page_type = $row['page_type'];
                    
                    // if user has access to page then proceed
                    if (check_edit_access($row['page_folder']) == true) {
                        // if a folder was selected to move the page(s) to, then move page
                        if ($_POST['move_to_folder']) {
                            $query = "UPDATE page
                                     SET
                                        page_folder = '" . escape($_POST['move_to_folder']) . "',
                                        page_timestamp = UNIX_TIMESTAMP(),
                                        page_user = '" . $user['id'] . "'
                                     WHERE page_id = '" . escape($page_id) . "'";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        }
                        
                        // if a page style was selected to be applied to the page(s), then apply the style
                        if ($_POST['edit_page_style'] != '') {
                            // If the user is currently previewing a theme and it is not an activated theme,
                            // then update preview style for page instead of activated style.
                            if (
                                ($_SESSION['software']['preview_theme_id'])
                                && ($_SESSION['software']['preview_theme_id'] != $activated_desktop_theme_id)
                                && ($_SESSION['software']['preview_theme_id'] != $activated_mobile_theme_id)
                            ) {
                                // Delete existing record.
                                db(
                                    "DELETE FROM preview_styles
                                    WHERE
                                        (page_id = '" . escape($page_id) . "')
                                        AND (theme_id = '" . escape($_SESSION['software']['preview_theme_id']) . "')
                                        AND (device_type = 'desktop')");

                                // If the user has selected a style (and not the default activated style),
                                // then add record for preview style.
                                if ($_POST['edit_page_style']) {
                                    db(
                                        "INSERT INTO preview_styles (
                                            page_id,
                                            theme_id,
                                            style_id,
                                            device_type)
                                        VALUES (
                                            '" . escape($page_id) . "',
                                            '" . escape($_SESSION['software']['preview_theme_id']) . "',
                                            '" . escape($_POST['edit_page_style']) . "',
                                            'desktop')");
                                }

                            // Otherwise the user is not previewing a theme or it is an activated one,
                            // so update activated style for page.
                            } else {
                                $query = "UPDATE page
                                         SET
                                            page_style = '" . escape($_POST['edit_page_style']) . "',
                                            page_timestamp = UNIX_TIMESTAMP(),
                                            page_user = '" . $user['id'] . "'
                                         WHERE page_id = '" . escape($page_id) . "'";
                                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                            }
                        }

                        // if a mobile page style was selected to be applied to the page(s), then apply the mobile style
                        if ($_POST['edit_mobile_style_id'] != '') {
                            // If the user is currently previewing a theme and it is not an activated theme,
                            // then update preview style for page instead of activated style.
                            if (
                                ($_SESSION['software']['preview_theme_id'])
                                && ($_SESSION['software']['preview_theme_id'] != $activated_desktop_theme_id)
                                && ($_SESSION['software']['preview_theme_id'] != $activated_mobile_theme_id)
                            ) {
                                // Delete existing record.
                                db(
                                    "DELETE FROM preview_styles
                                    WHERE
                                        (page_id = '" . escape($page_id) . "')
                                        AND (theme_id = '" . escape($_SESSION['software']['preview_theme_id']) . "')
                                        AND (device_type = 'mobile')");

                                // If the user has selected a style (and not the default activated style),
                                // then add record for preview style.
                                if ($_POST['edit_mobile_style_id']) {
                                    db(
                                        "INSERT INTO preview_styles (
                                            page_id,
                                            theme_id,
                                            style_id,
                                            device_type)
                                        VALUES (
                                            '" . escape($page_id) . "',
                                            '" . escape($_SESSION['software']['preview_theme_id']) . "',
                                            '" . escape($_POST['edit_mobile_style_id']) . "',
                                            'mobile')");
                                }

                            // Otherwise the user is not previewing a theme or it is an activated one,
                            // so update activated style for page.
                            } else {
                                $query = "UPDATE page
                                         SET
                                            mobile_style_id = '" . escape($_POST['edit_mobile_style_id']) . "',
                                            page_timestamp = UNIX_TIMESTAMP(),
                                            page_user = '" . $user['id'] . "'
                                         WHERE page_id = '" . escape($page_id) . "'";
                                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                            }
                        }
                        
                        // if the site search was selected to be edited, then update site search and update the tag cloud table
                        if ($_POST['edit_site_search'] != '') {
                            $query = "UPDATE page
                                     SET
                                        page_search = '" . escape($_POST['edit_site_search']) . "',
                                        page_timestamp = UNIX_TIMESTAMP(),
                                        page_user = '" . $user['id'] . "'
                                     WHERE page_id = '" . escape($page_id) . "'";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                            
                            // if the site search was off and it is now being turned on, then call the function that updates the tag cloud table to add the keywords
                            if (($original_page_search != 1) && ($_POST['edit_site_search'] == 1)) {
                                update_tag_cloud_keywords_for_page($page_id, $_POST['edit_site_search'], $original_meta_keywords, 0, '');
                                
                            // else if the site search was on and it is now being turned off, then call the function that updates the tag cloud table to reduce or remove the keywords
                            } elseif (($original_page_search == 1) && ($_POST['edit_site_search'] != 1)) {
                                update_tag_cloud_keywords_for_page($page_id, 0, '', $original_page_search, $original_meta_keywords);
                            }
                        }
                        
                        // if sitemap was selected to be edited and page has a valid page type for the sitemap, then update sitemap value
                        if (
                            ($_POST['edit_sitemap'] != '')
                            &&
                            (
                                ($page_type == 'standard')
                                || ($page_type == 'folder view')
                                || ($page_type == 'photo gallery')
                                || ($page_type == 'custom form')
                                || ($page_type == 'form list view')
                                || ($page_type == 'form item view')
                                || ($page_type == 'form view directory')
                                || ($page_type == 'calendar view')
                                || ($page_type == 'calendar event view')
                                || ($page_type == 'catalog')
                                || ($page_type == 'catalog detail')
                                || ($page_type == 'express order')
                                || ($page_type == 'order form')
                                || ($page_type == 'shopping cart')
                                || ($page_type == 'search results')
                            )
                        ) {
                            $query =
                                "UPDATE page
                                SET
                                    sitemap = '" . escape($_POST['edit_sitemap']) . "',
                                    page_timestamp = UNIX_TIMESTAMP(),
                                    page_user = '" . $user['id'] . "'
                                WHERE page_id = '" . escape($page_id) . "'";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        }
                        
                        $number_of_pages++;
                    
                    // else output error
                    } else {
                        output_error('You do not have access to edit the pages that you selected. <a href="javascript:history.go(-1);">Go back</a>.');
                    }
                }
                
                // if more than 0 pages were modified, then log activity
                if ($number_of_pages > 0) {
                    $log_message = '';
                    
                    // if a folder was selected to move the page(s) to, then output message for log
                    if ($_POST['move_to_folder']) {
                        // get folder name for log
                        $query = "SELECT folder_name FROM folder WHERE folder_id = '" . escape($_POST['move_to_folder']) . "'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        $row = mysqli_fetch_assoc($result);
                        
                        // output message for log
                        $log_message = "were moved to " . $row['folder_name'];
                    }
                    
                    // if a page style was selected to be applied to the page(s), then set message for log
                    if ($_POST['edit_page_style'] != '') {
                        // if the log message is not blank, then add separator
                        if ($log_message != '') {
                            $log_message .= ', and ';
                        }
                        
                        // If page style is equal to 0 then output default
                        if ($_POST['edit_page_style'] == '0') {
                            // If the user is currently previewing a theme that is not the activated theme,
                            // then set default label in a certain way.
                            if (
                                ($_SESSION['software']['preview_theme_id'])
                                && ($_SESSION['software']['preview_theme_id'] != $activated_desktop_theme_id)
                                && ($_SESSION['software']['preview_theme_id'] != $activated_mobile_theme_id)
                            ) {
                                $style = '[Activated]';

                            } else {
                                $style = 'Default (inherit)';
                            }
                        
                        // else get style name for log
                        } else {
                            $query = "SELECT style_name FROM style WHERE style_id = '" . escape($_POST['edit_page_style']) . "'";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                            $row = mysqli_fetch_assoc($result);
                            $style = $row['style_name'];
                        }

                        $preview = '';

                        // If the user is currently previewing a theme that is not the activated theme,
                        // then add preview label to log message.
                        if (
                            ($_SESSION['software']['preview_theme_id'])
                            && ($_SESSION['software']['preview_theme_id'] != $activated_desktop_theme_id)
                            && ($_SESSION['software']['preview_theme_id'] != $activated_mobile_theme_id)
                        ) {
                            $preview = 'preview ';

                        }
                        
                        // output message for log
                        $log_message .= 'had ' . $preview . 'page style changed to ' . $style;
                    }

                    // if a mobile page style was selected to be applied to the page(s), then set message for log
                    if ($_POST['edit_mobile_style_id'] != '') {
                        // if the log message is not blank, then add separator
                        if ($log_message != '') {
                            $log_message .= ', and ';
                        }
                        
                        // if mobile page style is equal to 0 then output default
                        if ($_POST['edit_mobile_style_id'] == '0') {
                            // If the user is currently previewing a theme that is not the activated theme,
                            // then set default label in a certain way.
                            if (
                                ($_SESSION['software']['preview_theme_id'])
                                && ($_SESSION['software']['preview_theme_id'] != $activated_desktop_theme_id)
                                && ($_SESSION['software']['preview_theme_id'] != $activated_mobile_theme_id)
                            ) {
                                $mobile_style = '[Activated]';

                            } else {
                                $mobile_style = 'Default (inherit)';
                            }
                        
                        // else get mobile style name for log
                        } else {
                            $query = "SELECT style_name FROM style WHERE style_id = '" . escape($_POST['edit_mobile_style_id']) . "'";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                            $row = mysqli_fetch_assoc($result);
                            $mobile_style = $row['style_name'];
                        }

                        $preview = '';

                        // If the user is currently previewing a theme that is not the activated theme,
                        // then add preview label to log message.
                        if (
                            ($_SESSION['software']['preview_theme_id'])
                            && ($_SESSION['software']['preview_theme_id'] != $activated_desktop_theme_id)
                            && ($_SESSION['software']['preview_theme_id'] != $activated_mobile_theme_id)
                        ) {
                            $preview = 'preview ';

                        }
                        
                        // output message for log
                        $log_message .= 'had mobile ' . $preview . 'page style changed to ' . $mobile_style;
                    }
                    
                    // if the site search was selected to be edited, then set message for log
                    if ($_POST['edit_site_search'] != '') {
                        // if the log message is not blank, then add separator
                        if ($log_message != '') {
                            $log_message .= ', and ';
                        }
                        
                        // prepare site search value for log
                        if ($_POST['edit_site_search'] == '1') {
                            $on_off = 'on';
                        } else {
                            $on_off = 'off';
                        }
                        
                        // output message for log
                        $log_message .= 'had site search turned ' . $on_off;
                    }
                    
                    // if sitemap was selected to be edited, then set message for log
                    if ($_POST['edit_sitemap'] != '') {
                        // if the log message is not blank, then add separator
                        if ($log_message != '') {
                            $log_message .= ', and ';
                        }
                        
                        // prepare sitemap value for log
                        if ($_POST['edit_sitemap'] == '1') {
                            $added_to_or_removed_from = 'added to';
                        } else {
                            $added_to_or_removed_from = 'removed from';
                        }
                        
                        // output message for log
                        $log_message .= 'were ' . $added_to_or_removed_from . ' sitemap.xml';
                    }
                    
                    // if there is a log message, then log it and add notice
                    if ($log_message != '') {
                        log_activity($number_of_pages . ' page(s) ' . $log_message, $_SESSION['sessionusername']);
                        $liveform->add_notice($number_of_pages . ' page(s) ' . h($log_message) . '.');
                    }
                }
                
                break;

            // if pages are being deleted
            case 'delete':
                // if the user has a user role and the user does not have access to delete pages, then output error
                if (($user['role'] == '3') && ($user['delete_pages'] == FALSE)) {
                    log_activity("access denied because user does not have access to delete pages", $_SESSION['sessionusername']);
                    output_error('Access denied. <a href="javascript:history.go(-1)">Go back</a>.');
                }
                
                $access_error_exists = false;
                $submitted_forms_error_exists = false;
                
                foreach ($_POST['pages'] as $page_id) {
                    // get page properties
                    $query =
                        "SELECT
                            page_id,
                            page_folder,
                            page_type,
                            page_name,
                            page_search,
                            page_meta_keywords
                        FROM page
                        WHERE page_id = '" . escape($page_id) . "'";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    $row = mysqli_fetch_assoc($result);

                    $page_id = $row['page_id'];
                    $page_folder = $row['page_folder'];
                    $page_type = $row['page_type'];
                    $page_name = $row['page_name'];
                    $original_page_search = $row['page_search'];
                    $original_meta_keywords = $row['page_meta_keywords'];

                    // if user has access to page, remember that
                    if (check_edit_access($page_folder) == true) {
                        $access_granted = true;
                    } else {
                        $access_granted = false;
                        $access_error_exists = true;
                    }

                    // if this page is a custom form, we need to check if there are submitted forms for this page,
                    // because software will not allow this page to be deleted if there are submitted forms for this page
                    if ($page_type == 'custom form') {
                        $query = "SELECT id FROM forms WHERE page_id = '" . escape($page_id) . "'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                        // if there are submitted forms for this page, remember that
                        if (mysqli_num_rows($result) > 0) {
                            $submitted_forms = true;
                            $submitted_forms_error_exists = true;
                        } else {
                            $submitted_forms = false;
                        }
                    } else {
                        $submitted_forms = false;
                    }
                    
                    // if user has access to this page and there are no submitted forms, then delete page
                    if (($access_granted == true) && ($submitted_forms == false)) {
                        $query = "DELETE FROM page WHERE page_id = '" . escape($page_id) . "'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        
                        // call the function that updates the tag cloud table
                        update_tag_cloud_keywords_for_page($page_id, 0, '', $original_page_search, $original_meta_keywords);
                        
                        // if this is a search results page, then call the function responsible for removing the keywords for this page from the tag cloud
                        if ($page_type == 'search results') {
                            delete_tag_cloud_keywords_for_search_results_page($page_id);
                        }
                        
                        // delete regions
                        $query = "DELETE FROM pregion WHERE pregion_page = '" . escape($page_id) . "'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        
                        // delete form fields for page
                        $query = "DELETE FROM form_fields WHERE page_id = '" . escape($page_id) . "'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        
                        // delete form field options for page
                        $query = "DELETE FROM form_field_options WHERE page_id = '" . escape($page_id) . "'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                        // Delete target options for page.
                        db("DELETE FROM target_options WHERE page_id = '" . escape($page_id) . "'");
                        
                        // if this page is a form list view, delete filters and form_view_directories_form_list_views_xref records
                        if ($page_type == 'form list view') {
                            $query = "DELETE FROM form_list_view_filters WHERE page_id = '" . escape($page_id) . "'";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                            $query = "DELETE FROM form_list_view_browse_fields WHERE page_id = '" . escape($page_id) . "'";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                            
                            $query = "DELETE FROM form_view_directories_form_list_views_xref WHERE form_list_view_page_id = '" . escape($page_id) . "'";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        }
                        
                        // if this page is a form view directory, delete form_view_directories_form_list_views_xref records
                        if ($current_page_type == 'form view directory') {
                            $query = "DELETE FROM form_view_directories_form_list_views_xref WHERE form_view_directory_page_id = '" . escape($page_id) . "'";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        }
                        
                        // delete views for this page that the form view directory feature uses
                        $query = "DELETE FROM submitted_form_views WHERE page_id = '" . escape($page_id) . "'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        
                        // delete calendar_views_calendars_xref records
                        $query = "DELETE FROM calendar_views_calendars_xref WHERE page_id = '" . escape($page_id) . "'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        
                        // delete calendar_event_views_calendars_xref records
                        $query = "DELETE FROM calendar_event_views_calendars_xref WHERE page_id = '" . escape($page_id) . "'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        
                        // if page type has a table for properties, delete page type record of properties
                        if (check_for_page_type_properties($page_type) == true) {
                            $page_type_table_name = str_replace(' ', '_', $page_type) . '_pages';
                            
                            $query = "DELETE FROM $page_type_table_name WHERE page_id = '" . escape($page_id) . "'";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        }
                        
                        // get comment attachments for this page, so they can be deleted
                        $query = 
                            "SELECT
                                comments.id as comment_id,
                                files.id,
                                files.name
                            FROM comments
                            LEFT JOIN files ON comments.file_id = files.id
                            WHERE
                                (comments.page_id = '" . escape($page_id) . "')
                                AND (files.id IS NOT NULL)";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        
                        $attachments = array();
                        
                        // loop through the attachments in order to add them to array
                        while ($row = mysqli_fetch_assoc($result)) {
                            $attachments[] = $row;
                        }
                        
                        // loop through the attachments so they can be deleted
                        foreach ($attachments as $attachment) {
                            // check if the file attachment is used by another comment (multiple comments can share the same file attachment when pages are duplicated)
                            $query = "SELECT id FROM comments WHERE (file_id = '" . $attachment['id'] . "') AND (id != '" . $attachment['comment_id'] . "')";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                            
                            // if the file attachment is not used by another comment, then delete the file
                            if (mysqli_num_rows($result) == 0) {
                                // delete file from database
                                $query = "DELETE FROM files WHERE id = '" . $attachment['id'] . "'";
                                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                                
                                // delete file on file system
                                @unlink(FILE_DIRECTORY_PATH . '/' . $attachment['name']);
                                
                                // log that the file was deleted
                                log_activity('file attachment (' . $attachment['name'] . ') for a comment was deleted because the page (' . $page_name . ') was deleted', $_SESSION['sessionusername']);
                            }
                        }
                        
                        // delete comments for page
                        $query = "DELETE FROM comments WHERE page_id = '" . escape($page_id) . "'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        
                        // delete submitted_form_info for page
                        $query = "DELETE FROM submitted_form_info WHERE page_id = '" . escape($page_id) . "'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                        // delete allow new comments data for page
                        $query = "DELETE FROM allow_new_comments_for_items WHERE page_id = '" . escape($page_id) . "'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        
                        // delete watchers for this page
                        $query = "DELETE FROM watchers WHERE page_id = '" . escape($page_id) . "'";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                        // Check if this page has short links, in order to determine if we need to delete them and update rewrite file.
                        $query =
                            "SELECT COUNT(*)
                            FROM short_links
                            WHERE
                                (destination_type = 'page')
                                AND (page_id = '" . escape($page_id) . "')";
                        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        $row = mysqli_fetch_row($result);

                        // If a short link exists, then delete short links for this page
                        // and remember that we need to update short links in rewrite file later.
                        if ($row[0] != 0) {
                            $query =
                                "DELETE FROM short_links
                                WHERE
                                    (destination_type = 'page')
                                    AND (page_id = '" . escape($page_id) . "')";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                        }

                        db("DELETE FROM preview_styles WHERE page_id = '" . escape($page_id) . "'");
                        
                        // If a layout file exists, then delete it.
                        if (file_exists(LAYOUT_DIRECTORY_PATH . '/' . $page_id . '.php')) {
                            unlink(LAYOUT_DIRECTORY_PATH . '/' . $page_id . '.php');
                        }

                        $number_of_pages++;
                    }
                }

                // if more than 0 pages were deleted, then log activity
                if ($number_of_pages > 0) {
                    log_activity("$number_of_pages page(s) were deleted", $_SESSION['sessionusername']);
                    $liveform->add_notice("$number_of_pages page(s) were deleted.");
                }
                
                if ($access_error_exists == true) {
                    $liveform->mark_error('access_error', 'At least one page could not be deleted, because you do not have access to the page(s).');
                }
                
                if ($submitted_forms_error_exists == true) {
                    $liveform->mark_error('submitted_forms_error', 'At least one page could not be deleted, because there are submitted forms for the page(s).  All submitted forms for the page(s) must be deleted before the page(s) are allowed to be deleted.');
                }
                
                break;
        }
    }
    
    // If there is a send to value then send user back to that screen
    if ((isset($_POST['send_to']) == TRUE) && ($_POST['send_to'] != '')) {
        header('Location: ' . URL_SCHEME . HOSTNAME . $_POST['send_to']);
        
    // else send user to the default view
    } else {
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_pages.php');
    }
}
?>
