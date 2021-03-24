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
$user = validate_user();
validate_area_access($user, 'designer');

$liveform = new liveform('edit_theme_file');

if (!$_POST) {
    // get the file data
    $query =
        "SELECT
            name,
            folder,
            description,
            activated_desktop_theme,
            activated_mobile_theme
        FROM files
        WHERE id = '" . escape($_GET['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_array($result);

    $file_name = $row['name'];
    $file_folder = $row['folder'];
    $description = $row['description'];
    $activated_desktop_theme = $row['activated_desktop_theme'];
    $activated_mobile_theme = $row['activated_mobile_theme'];
    
    // if user requested to export the theme, then export the theme
    if ($_GET['export'] == TRUE) {

        // remove the file extension from the filename
        $file_name_for_csv = mb_substr($file_name, 0, -4);
        
        // force download dialog
        header("Content-type: text/csv");
        header("Content-disposition: attachment; filename=" . $file_name_for_csv . ".csv");

        print 'area,row,col,module,region_type,region_name,property,value' . "\n";
        
        // get the properties from the database, then loop through them to add them to the csv file
        $query = 
            "SELECT
                area,
                `row`, # Backticks for reserved word.
                col,
                module,
                property,
                value,
                region_type,
                region_name
            FROM system_theme_css_rules 
            WHERE file_id = '" . escape($_GET['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        while($row = mysqli_fetch_array($result)) {
            // for each value in the row
            foreach ($row as $key => $value) {
               // replace quotation mark with two quotation marks
               $value = str_replace('"', '""', $value);
               // add quotation marks around value
               $value = '"' . $value . '"';
               // set new value
               $row[$key] = $value;
            }
            
            // print the row to the csv file
            print $row['area'] . ',' . $row['row'] . ',' . $row['col'] . ',' . $row['module'] . ',' . $row['region_type'] . ',' . $row['region_name'] . ',' . $row['property'] . ',' . $row['value'] . "\n";
        }
        
        // log the activity
        log_activity("theme file ($file_name) was exported", $_SESSION['sessionusername']);
    
    // else output the page
    } else {
        // if the form has not been submitted yet, pre-populate fields with data
        if ($liveform->field_in_session('id') == FALSE) {
            $liveform->assign_field_value('activated_desktop_theme', $activated_desktop_theme);
            $liveform->assign_field_value('activated_mobile_theme', $activated_mobile_theme);
        }

        // check to see if this is a system theme
        $query = "SELECT COUNT(id) FROM system_theme_css_rules WHERE file_id = '" . escape($_GET['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_row($result);
        
        // if this is a system theme then output the edit theme button
        if ($row[0] > 0) {
            $output_edit_theme_button = '<input type="button" value="Edit Theme" OnClick="window.location=\'' . h(escape_javascript(PATH . SOFTWARE_DIRECTORY . '/theme_designer.php?id=' . urlencode($_GET['id']) . '&clear_theme_designer_session=true')) . '\'" class="submit-secondary">&nbsp;&nbsp;&nbsp;';

            $output_export_button = '<input type="button" name="export" value="Export"' . ' onclick="window.location=\'' . h(escape_javascript(PATH . SOFTWARE_DIRECTORY . '/edit_theme_file.php?id=' . urlencode($_GET['id']) . '&export=true')) . '\'" class="submit-secondary" />&nbsp;&nbsp;&nbsp;';
            $output_download_file_button = '';
            
        // else output the edit theme css button
        } else {
            $output_edit_theme_button = '<input type="button" value="Edit CSS" OnClick="window.location=\'' . h(escape_javascript(PATH . SOFTWARE_DIRECTORY . '/edit_theme_css.php?id=' . urlencode($_GET['id']) . '&send_to=' . urlencode(get_request_uri()) . '&from=edit_theme_file')) . '\'" class="submit-secondary">&nbsp;&nbsp;&nbsp;';
            $output_export_button = '';
            $output_download_file_button = '<input type="button" name="download" value="Download File" onclick="window.open(\'' . h(escape_javascript(PATH . encode_url_path($file_name))) . '\')" class="submit-secondary">&nbsp;&nbsp;&nbsp;';
        }
        
        print
            output_header() . '
            <div id="subnav">
                <h1 style="margin-bottom: .25em"><a href="' . OUTPUT_PATH . h($file_name) . '" target="_blank">' . h($file_name) . '</a></h1>
                <div class="subheading">Page Style Head Tag: ' . h('<stylesheet></stylesheet>') . '</div>
            </div>
            <div id="content">
                
                ' . $liveform->output_errors() . '
                ' . $liveform->output_notices() . '
                <a href="#" id="help_link">Help</a>
                <h1>Edit Theme</h1>
                <div class="subheading">View or update this theme file.<br />WARNING: Your website may become unreadable if you delete or move this theme file to a non-public folder when it is activate.</div>
                <form action="edit_theme_file.php" method="post">
                    ' . get_token_field() . '
                    ' . $liveform->output_field(array('type'=>'hidden', 'name'=>'id', 'value'=>$_GET['id'])) . '
                    ' . $liveform->output_field(array('type'=>'hidden', 'name'=>'from', 'value'=>$from)) . '
                    <table class="field">
                        <tr>
                            <th colspan="2"><h2>Theme File Name</h2></th>
                        </tr>
                        <tr>
                            <td>Name:</td>
                            <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'name', 'value'=>$file_name, 'size'=>'60', 'maxlength'=>'100')) . '</td>
                        </tr>
                        <tr>
                            <th colspan="2"><h2>Theme File Access Control</h2></th>
                        </tr>
                        <tr>
                            <td>Folder:</td>
                            <td><select name="folder">' . select_folder($file_folder) . '</select></td>
                        </tr>
                        <tr>
                            <td colspan="2"><h2>Theme File Description</h2></td>
                        </tr>
                        <tr>
                            <td style="vertical-align: top">Description:</td>
                            <td>' . $liveform->output_field(array('type'=>'textarea', 'name'=>'description', 'value'=>$description, 'rows'=>'3', 'cols'=>'50')) . '</td>
                        </tr>
                        <tr>
                            <td colspan="2"><h2>Activate Theme to Make it Live</h2></td>
                        </tr>
                        <tr>
                            <td><label for="activated_desktop_theme">Activate for Desktop:</label></td>
                            <td>' . $liveform->output_field(array('type'=>'checkbox', 'name'=>'activated_desktop_theme', 'id'=>'activated_desktop_theme', 'value'=>'1', 'class'=>'checkbox')) . '</td>
                        </tr>
                        <tr>
                            <td><label for="activated_mobile_theme">Activate for Mobile:</label></td>
                            <td>' . $liveform->output_field(array('type'=>'checkbox', 'name'=>'activated_mobile_theme', 'id'=>'activated_mobile_theme', 'value'=>'1', 'class'=>'checkbox')) . '</td>
                        </tr>
                    </table>
                    <div class="buttons">
                        <input type="submit" name="submit_save" value="Save" class="submit-primary" />&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="window.location=\'' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/view_themes.php\'" class="submit-secondary">&nbsp;&nbsp;&nbsp;' . $output_edit_theme_button . $output_export_button . $output_download_file_button . '<input type="submit" name="delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This theme will be permanently deleted.\')" />
                    </div>
                </form>
            </div>
            ' . output_footer();
        
        $liveform->remove_form('edit_theme_file');
    }

} else {
    validate_token_field();
    
    $liveform->add_fields_to_session();
    
    // Create new liveform
    $liveform_view_themes = new liveform('view_themes');
    
    // get original values because we might need them below
    $query =
        "SELECT
            name,
            activated_desktop_theme,
            activated_mobile_theme
        FROM files
        WHERE id = '" . escape($liveform->get_field_value('id')) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);

    $original_name = $row['name'];
    $original_activated_desktop_theme = $row['activated_desktop_theme'];
    $original_activated_mobile_theme = $row['activated_mobile_theme'];

    $name = prepare_file_name($liveform->get_field_value('name'));
    
    // if file was selected for delete
    if ($_POST['delete']) {
        // delete file row
        $query = "DELETE FROM files WHERE id = '" . escape($liveform->get_field_value('id')) . "'";
        $result=mysqli_query(db::$con, $query) or output_error('Query failed');
        
        // delete file's system css properties
        $query = "DELETE FROM system_theme_css_rules WHERE file_id = '" . escape($liveform->get_field_value('id')) . "'";
        $result=mysqli_query(db::$con, $query) or output_error('Query failed.');

        db("DELETE FROM preview_styles WHERE theme_id = '" . escape($liveform->get_field_value('id')) . "'");
        
        // Delete file on file system.
        @unlink(FILE_DIRECTORY_PATH . '/' . $name);
        
        // log activity, add a notice to the liveform, and then send the user back to the view themes screen
        log_activity("theme file ($name) was deleted", $_SESSION['sessionusername']);
        $liveform_view_themes->add_notice('The theme file was deleted successfully.');
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_themes.php');
    
    // else then the file was selected to be saved, so save the file
    } else {
        // validate the name field
        $liveform->validate_required_field('name', 'Name is required.');
        
        // if there are not any errors, and if the name does not contain ".css", then add an error to the liveform
        if (($liveform->check_form_errors() == false) && (mb_strtolower(mb_substr($liveform->get_field_value('name'), mb_strrpos($liveform->get_field_value('name'), '.'))) != '.css')) {
            $liveform->mark_error('name', 'The name must end with ".css".');
        }
        
        // if there is an error, forward user back to edit theme file screen
        if ($liveform->check_form_errors() == true) {
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/edit_theme_file.php?id=' . $liveform->get_field_value('id'));
            exit();
        }
        
        // if file name is invalid, output error
        if ($name == '.htaccess') {
            $liveform->mark_error('name', 'File name is invalid.');
        }

        if (check_name_availability(array('name' => $name, 'ignore_item_id' => $liveform->get_field_value('id'), 'ignore_item_type' => 'file')) == false) {
            $liveform->mark_error('name', $name . ' already exists.  Please choose a different file name.');
        }
        
        // if there is an error, forward user back to add theme file screen
        if ($liveform->check_form_errors() == true) {
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/edit_theme_file.php?id=' . $liveform->get_field_value('id'));
            exit();
        }
        
        // get file extension
        $array_file_extension = explode('.', $name);
        $size_of_array = count($array_file_extension);
        $file_extension = $array_file_extension[$size_of_array - 1];
        
        // if this theme was activated as the desktop theme, then deactivate all other themes
        if ($liveform->get_field_value('activated_desktop_theme') == 1) {
            // Check if there was a theme that was activated before that is not this theme.
            $old_activated_theme_id = db_value(
                "SELECT id
                FROM files
                WHERE
                    (activated_desktop_theme = '1')
                    AND (id != '" . escape($liveform->get_field_value('id')) . "')");

            // If there is an old activated theme, then contiue with deactivation process.
            if ($old_activated_theme_id) {
                // Delete all existing preview styles for the old theme,
                // because we are going to set new preview styles based on the activated styles,
                // so if this old theme is re-activated in the future, the proper styles will be set.
                db(
                    "DELETE FROM preview_styles
                    WHERE
                        (theme_id = '" . $old_activated_theme_id . "')
                        AND (device_type = 'desktop')");

                // Get all activated styles for pages in order to create preview style records for them.
                $styles = db_items(
                    "SELECT
                        page_style AS id,
                        page_id
                    FROM page
                    WHERE
                        (page_style != '0')
                        AND (page_style IS NOT NULL)");

                // Loop through the activated styles in order to create preview style records.
                foreach ($styles as $style) {
                    db(
                        "INSERT INTO preview_styles (
                            page_id,
                            theme_id,
                            style_id,
                            device_type)
                        VALUES (
                            '" . $style['page_id'] . "',
                            '" . $old_activated_theme_id . "',
                            '" . $style['id'] . "',
                            'desktop')");
                }

                // Deactivate old theme.
                $query =
                    "UPDATE files
                    SET activated_desktop_theme = '0'
                    WHERE
                        (activated_desktop_theme = '1')
                        AND (id != '" . escape($liveform->get_field_value('id')) . "')";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            }
        }

        // if this theme was activated as the mobile theme, then deactivate all other themes
        if ($liveform->get_field_value('activated_mobile_theme') == 1) {
            // Check if there was a theme that was activated before that is not this theme.
            $old_activated_theme_id = db_value(
                "SELECT id
                FROM files
                WHERE
                    (activated_mobile_theme = '1')
                    AND (id != '" . escape($liveform->get_field_value('id')) . "')");

            // If there is an old activated theme, then contiue with deactivation process.
            if ($old_activated_theme_id) {
                // Delete all existing preview styles for the old theme,
                // because we are going to set new preview styles based on the activated styles,
                // so if this old theme is re-activated in the future, the proper styles will be set.
                db(
                    "DELETE FROM preview_styles
                    WHERE
                        (theme_id = '" . $old_activated_theme_id . "')
                        AND (device_type = 'mobile')");

                // Get all activated styles for pages in order to create preview style records for them.
                $styles = db_items(
                    "SELECT
                        mobile_style_id AS id,
                        page_id
                    FROM page
                    WHERE mobile_style_id != '0'");

                // Loop through the activated styles in order to create preview style records.
                foreach ($styles as $style) {
                    db(
                        "INSERT INTO preview_styles (
                            page_id,
                            theme_id,
                            style_id,
                            device_type)
                        VALUES (
                            '" . $style['page_id'] . "',
                            '" . $old_activated_theme_id . "',
                            '" . $style['id'] . "',
                            'mobile')");
                }

                // Deactivate old theme.
                $query =
                    "UPDATE files
                    SET activated_mobile_theme = '0'
                    WHERE
                        (activated_mobile_theme = '1')
                        AND (id != '" . escape($liveform->get_field_value('id')) . "')";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            }
        }

        // update theme
        $query =
            "UPDATE files 
            SET 
                name = '" . escape($name) . "',
                folder = '" . escape($liveform->get_field_value('folder')) . "', 
                description = '" . escape($liveform->get_field_value('description')) . "',
                type = '" . escape($file_extension) . "',
                activated_desktop_theme = '" . escape($liveform->get_field_value('activated_desktop_theme')) . "',
                activated_mobile_theme = '" . escape($liveform->get_field_value('activated_mobile_theme')) . "',
                timestamp = UNIX_TIMESTAMP(),
                user = '" . $user['id'] . "'
            WHERE id = '" . escape($liveform->get_field_value('id')) . "'";
        
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // rename file's name
        rename(FILE_DIRECTORY_PATH . '/' . $original_name, FILE_DIRECTORY_PATH . '/' . $name);
        log_activity("theme file ($name) was modified", $_SESSION['sessionusername']);

        // If this theme was activated as a desktop or mobile theme,
        // then update styles for pages.
        if (
            (
                ($original_activated_desktop_theme == 0)
                && ($liveform->get_field_value('activated_desktop_theme') == 1)
            )
            ||
            (
                ($original_activated_mobile_theme == 0)
                && ($liveform->get_field_value('activated_mobile_theme') == 1)
            )
        ) {
            $sql_device_type_filter = "";

            // If this theme was only activated as a desktop theme,
            // then only get preview styles for that device type.
            if (
                ($original_activated_desktop_theme == 0)
                && ($liveform->get_field_value('activated_desktop_theme') == 1)
                &&
                (
                    ($liveform->get_field_value('activated_mobile_theme') == 0)
                    || ($original_activated_mobile_theme == 1)
                )
            ) {
                $sql_device_type_filter = "AND (device_type = 'desktop')";

            // Otherwise, if this theme was only activated as a mobile theme,
            // then only get preview styles for that device type.
            } else if (
                ($original_activated_mobile_theme == 0)
                && ($liveform->get_field_value('activated_mobile_theme') == 1)
                &&
                (
                    ($liveform->get_field_value('activated_desktop_theme') == 0)
                    || ($original_activated_desktop_theme == 1)
                )
            ) {
                $sql_device_type_filter = "AND (device_type = 'mobile')";
            }

            // Get all preview styles for this theme.
            $preview_styles = db_items(
                "SELECT
                    page_id,
                    style_id,
                    device_type
                FROM preview_styles
                WHERE
                    (theme_id = '" . escape($liveform->get_field_value('id')) . "')
                    " . $sql_device_type_filter);

            // Loop through all preview styles in order to update styles for pages.
            foreach ($preview_styles as $preview_style) {
                if ($preview_style['device_type'] == 'desktop') {
                    $sql_style_id_column = "page_style";
                } else {
                    $sql_style_id_column = "mobile_style_id";
                }

                db(
                    "UPDATE page
                    SET
                        " . $sql_style_id_column . " = '" . $preview_style['style_id'] . "',
                        page_user = '" . USER_ID . "',
                        page_timestamp = UNIX_TIMESTAMP()
                    WHERE page_id = '" . $preview_style['page_id'] . "'");
            }
        }

        // if this theme file was not activated for desktop before and it is now,
        // then check if we should update preview theme and log activity.
        if (
            ($original_activated_desktop_theme == 0)
            && ($liveform->get_field_value('activated_desktop_theme') == 1)
        ) {
            // If the user's device type is desktop and the user is previewing a theme,
            // then update the theme that the user is previewing to be this theme.
            if (
                ($_SESSION['software']['device_type'] == 'desktop')
                && (isset($_SESSION['software']['preview_theme_id']))
            ) {
                $_SESSION['software']['preview_theme_id'] = $liveform->get_field_value('id');
            }

            log_activity('theme file (' . $name . ') was activated for desktop', $_SESSION['sessionusername']);
        }

        // if this theme file was activated for desktop before and it is not now, then log activity
        if (
            ($original_activated_desktop_theme == 1)
            && ($liveform->get_field_value('activated_desktop_theme') == 0)
        ) {
            log_activity('theme file (' . $name . ') was deactivated for desktop', $_SESSION['sessionusername']);
        }

        // if this theme file was not activated for mobile before and it is now,
        // then check if we should update preview theme and log activity.
        if (
            ($original_activated_mobile_theme == 0)
            && ($liveform->get_field_value('activated_mobile_theme') == 1)
        ) {
            // If the user's device type is mobile and the user is previewing a theme,
            // then update the theme that the user is previewing to be this theme.
            if (
                ($_SESSION['software']['device_type'] == 'mobile')
                && (isset($_SESSION['software']['preview_theme_id']))
            ) {
                $_SESSION['software']['preview_theme_id'] = $liveform->get_field_value('id');
            }

            log_activity('theme file (' . $name . ') was activated for mobile', $_SESSION['sessionusername']);
        }

        // if this theme file was activated for mobile before and it is not now, then log activity
        if (
            ($original_activated_mobile_theme == 1)
            && ($liveform->get_field_value('activated_mobile_theme') == 0)
        ) {
            log_activity('theme file (' . $name . ') was deactivated for mobile', $_SESSION['sessionusername']);
        }
        
        $notice = 'The theme file was edited successfully.';
        
        $liveform_view_themes->add_notice($notice);
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_themes.php');
    }
    
    // clear the liveform
    $liveform->remove_form();
}