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
include_once('liveform.class.php');
$liveform_view_design_files = new liveform('view_design_files');
$liveform = new liveform('edit_design_file');
validate_area_access($user, 'designer');

if (!$_POST['name']) {
    $query = 
        "SELECT 
            files.name, 
            files.folder, 
            files.description, 
            files.type, 
            files.size,
            files.optimized,
            files.theme,
            folder.folder_archived
        FROM files 
        LEFT JOIN folder ON files.folder = folder.folder_id
        WHERE files.id = '" . escape($_GET['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_array($result);
    
    $file_name = $row['name'];
    $file_folder = $row['folder'];
    $file_description = $row['description'];
    $file_type = $row['type'];
    $file_size = $row['size'];
    $optimized = $row['optimized'];
    $theme = $row['theme'];
    $folder_archived = $row['folder_archived'];
    
    $output_file_name = '';
    
    // if this files folder is archived, then output a notice next to the file name
    if ($folder_archived == '1') {
        $output_file_name = h($file_name . ' [ARCHIVED]');
    
    // else output the file name as normal
    } else {
        $output_file_name = h($file_name);
    }
    
    if ((mb_strtolower($file_type) == 'bmp') || (mb_strtolower($file_type) == 'gif') || (mb_strtolower($file_type) == 'jpg') || (mb_strtolower($file_type) == 'jpeg') || (mb_strtolower($file_type) == 'png') || (mb_strtolower($file_type) == 'tif') || (mb_strtolower($file_type) == 'tiff')) {
        // Get the dimensions of the image.
        $image_size = @getimagesize(FILE_DIRECTORY_PATH . '/' . $file_name);
        $image_width = $image_size[0];
        $image_height = $image_size[1];
        
        // Output the image dimensions to the table.
        $output_image_dimensions = 'width: ' . $image_width . ' px height: ' . $image_height . ' px';
        
        // Set the maximum dimension size for the image.
        $max_dimension = 75;
        
        // Call function to resize image.
        $thumbnail_dimensions = get_thumbnail_dimensions($image_width, $image_height, $max_dimension);
        
        // Output thumnail.
        $output_thumbnail = '<div style="float: left; padding: .5em 1em .5em 0em"><a href="' . OUTPUT_PATH . $file_name . '" target="_blank"><img src="' . OUTPUT_PATH . $file_name . '" width="' . $thumbnail_dimensions['width'] . '" height="' . $thumbnail_dimensions['height'] . '" alt="" title="Image Size:&nbsp;' . $output_image_dimensions . '" /></a></div>';
    }

     // If this file has not been optimized yet, and it is an image type that we support, then
    // show optimize button in button bar and edit image link


    $image_buttons = '';
    $output_image_edit_link = '' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/image_editor_edit.php?file_name=' . $output_file_name . '&send_to=' . h(escape_javascript(REQUEST_URL)) . '';
    $file['type'] =  mb_strtolower($file_type);
    if (    ($file['type'] == 'jpg')
            or ($file['type'] == 'jpeg')
            or ($file['type'] == 'png')
            or ($file['type'] == 'gif')
            or ($file['type'] == 'bmp')
            or ($file['type'] == 'tiff')
        ){
            if (!$optimized){
                $optimize_button =
                    '<a href="optimize.php?id=' . h($_GET['id']) . '&amp;send_to=' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/view_design_files.php' . get_token_query_string_field() . '">
                        Optimize
                    </a>';
            }
            $image_buttons =
            '
                '.$optimize_button.'
                <a href="' . $output_image_edit_link . '">Edit Image</a>
            ';
    }


    // Convert file size to a user friendly output.
    $output_file_size = convert_bytes_to_string($file_size);
    
    // get file extension
    $file_extension = mb_strtolower(mb_substr($file_name, mb_strrpos($file_name, '.') + 1));
    
    // if the file is either a CSS or JS file, then output the edit file button
    if (($file_extension == 'css') || ($file_extension == 'js')) {
        $edit_file_button_label = '';
        $edit_file_button_location = '';
        
        // if this is a CSS file, then output the CSS or Theme label and location
        if ($file_extension == 'css') {
            // check to see if this is a system theme
            $query = "SELECT COUNT(id) FROM system_theme_css_rules WHERE file_id = '" . escape($_GET['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_row($result);
        
            // if this is a system theme then output the edit theme button
            if ($row[0] > 0) {
                $edit_file_button_label = 'Theme';
                $edit_file_button_location = 'theme_designer.php?id=' . urlencode($_GET['id']) . '&send_to=' . urlencode(get_request_uri());

                // Remember that this is a system theme so we know later to disable theme check box.
                $system_theme = true;
            
            // else output the edit css button
            } else {
                $edit_file_button_label = 'CSS';
                $edit_file_button_location = 'edit_theme_css.php?id=' . urlencode($_GET['id']) . '&send_to=' . urlencode(get_request_uri()) . '&from=edit_design_file';

                // Remember that this is not a system theme so we know later to enable theme check box.
                $system_theme = false;
            }
            
        // else this is a JS file so output the JS label and location
        } else {
            $edit_file_button_label = 'JavaScript';
            $edit_file_button_location = 'edit_javascript.php?id=' . urlencode($_GET['id']);
        }
        
        $output_edit_file_button = '&nbsp;&nbsp;&nbsp;<input type="button" value="Edit ' . $edit_file_button_label . '" OnClick="window.location=\'' . h(escape_javascript(PATH . SOFTWARE_DIRECTORY . '/' . $edit_file_button_location)) . '\'" class="submit-secondary">';
    }

    $output_theme_rows = '';

    // If this is a CSS file, then output theme property.
    if (mb_strtolower($file_type) == 'css') {
        $output_theme_checked = '';

        if ($theme == 1) {
            $output_theme_checked = ' checked="checked"';
        }

        $output_theme_disabled = '';

        if ($system_theme == true) {
            $output_theme_disabled = ' disabled="disabled"';
        }

        $output_theme_rows =
            '<tr>
                <th colspan="2"><h2>Check if Design File should be included on All Themes screen and in Theme preview</h2></th>
            </tr>
            <tr>
                <td><label for="theme">Theme:</label></td>
                <td><input type="checkbox" id="theme" name="theme" value="1"' . $output_theme_checked . ' class="checkbox"' . $output_theme_disabled . ' /></td>
            </tr>';
    }
    
    echo
        output_header() . '
        <div id="subnav">
            ' . $output_thumbnail . '
            <div style="padding: .25em 0em .25em 0em">
                <h1 style="padding: 0em; margin-bottom: .25em"><a href="' . OUTPUT_PATH . $file_name . '" target="_blank">' . $output_file_name . '</a></h1>
                <div class="subheading">File Size: '. $output_file_size .' | Access:&nbsp;' . h(get_access_control_type_name(get_access_control_type($file_folder))) . '</div>
            </div>
            <div style="clear: both"></div>
        </div>
        <div id="button_bar">
            ' . $image_buttons . '
            <a download="image" href="' . OUTPUT_PATH . $file_name . '" >Download</a>
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Edit Design File</h1>
            <div class="subheading">Rename, move, delete, or download this design file. (A rename/delete will require any links to this file to be updated.  This file will not be viewable to site visitors if placed in a folder that is not public.)</div>
            <form action="edit_design_file.php" method="post">
                ' . get_token_field() . '
                <input type="hidden" name="id" value="' . h($_GET['id']) . '"><input type="hidden" name="from" value="' . h($from) . '">
                <table class="field">
                    <tr>
                        <td colspan="2"><h2>Design File Name</h2></td>
                    </tr>
                    <tr>
                        <td>File Name:</td>
                        <td><input name="name" type="text" value="' . $file_name . '" size="40" maxlength="100"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><h2>Design File Access Control</h2></td>
                    </tr>
                    <tr>
                        <td>Folder:</td>
                        <td><select name="folder">' . select_folder($file_folder) . '</select></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Check if File is a Design File that is Managed by Site Designers</h2></th>
                    </tr>
                    <tr>
                        <td><label for="design">Design:</label></td>
                        <td><input type="checkbox" id="design" name="design" value="1" checked="checked" class="checkbox" /></td>
                    </tr>
                    ' . $output_theme_rows . '
                    <tr>
                        <td colspan="2"><h2>Design File Description</h2></td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top">File Description:</td>
                        <td><textarea rows="3" cols="50" name="description">' . h($file_description) . '</textarea></td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_save" value="Save" class="submit-primary" />&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="window.location=\'' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/view_design_files.php\'" class="submit-secondary">' . $output_edit_file_button . '&nbsp;&nbsp;&nbsp;<input type="submit" name="delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This design file will be permanently deleted.\')" />
                </div>
            </form>
        </div>
        ' . output_footer();
    
    $liveform->remove_form('edit_design_file');

print $output;

} else {
    validate_token_field();
    
    $result=mysqli_query(db::$con, "SELECT name FROM files WHERE id = '" . escape($_POST['id']) . "'") or output_error('Query failed');
    $row=mysqli_fetch_array($result);

    $name = prepare_file_name($_POST['name']);
    
    // if file was selected for delete
    if ($_POST['delete'])
    {
        // delete file row
        $query = "DELETE FROM files WHERE id = '" . escape($_POST['id']) . "'";
        $result=mysqli_query(db::$con, $query) or output_error('Query failed');
        
        // check to see if this is a system theme
        $query = "SELECT COUNT(id) FROM system_theme_css_rules WHERE file_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_row($result);
        
        // if this is a system theme then delete it's css theme properties from the database
        if ($row[0] > 0) {
            // delete file's system css properties
            $query = "DELETE FROM system_theme_css_rules WHERE file_id = '" . escape($_POST['id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        }

        db("DELETE FROM preview_styles WHERE theme_id = '" . escape($_POST['id']) . "'");

        // Delete file on file system.
        @unlink(FILE_DIRECTORY_PATH . '/' . $name);
        
        log_activity("design file ($name) was deleted", $_SESSION['sessionusername']);
        $notice = 'The design file was deleted successfully.';
    }
    else
    {
        // if file name is invalid, output error
        if ($name == '.htaccess') {
            output_error('File name is invalid. <a href="javascript:history.go(-1)">Go back</a>.');
        }

        if (check_name_availability(array('name' => $name, 'ignore_item_id' => $_POST['id'], 'ignore_item_type' => 'file')) == false) {
            output_error(h($name) . ' already exists.  Please choose a different file name.  <a href="javascript:history.go(-1)">Go back</a>.');
        }

        // get file extension
        $array_file_extension = explode('.', $name);
        $size_of_array = count($array_file_extension);
        $file_extension = $array_file_extension[$size_of_array - 1];

        $sql_theme = "";

        // If this is a CSS file and this is not a system theme,
        // then update theme property.
        if (
            (mb_strtolower($file_extension) == 'css')
            && (db_value("SELECT COUNT(*) FROM system_theme_css_rules WHERE file_id = '" . escape($_POST['id']) . "'") == 0)
        ) {
            $sql_theme = "theme = '" . escape($_POST['theme']) . "',";
        }
        
        // update file
        $query =
            "UPDATE files 
            SET 
                name = '" . escape($name) . "',
                folder = '" . escape($_POST['folder']) . "', 
                description = '" . escape($_POST['description']) . "',
                type = '" . escape($file_extension) . "',
                design = '" . escape($_POST['design']) . "',
                $sql_theme
                timestamp = UNIX_TIMESTAMP(), 
                user = '" . $user['id'] . "' 
            WHERE id = '" . escape($_POST['id']) . "'";
        
        $result=mysqli_query(db::$con, $query) or output_error('Query failed');
        
        // rename file's name
        rename(FILE_DIRECTORY_PATH . '/' . $row['name'], FILE_DIRECTORY_PATH . '/' . $name);
        log_activity("design file ($name) was modified", $_SESSION['sessionusername']);
        
        $notice = 'The design file was edited successfully.';
    }
    
    $liveform_view_design_files->add_notice($notice);
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_design_files.php');
}
?>
