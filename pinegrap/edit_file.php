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
validate_area_access($user, 'user');
$liveform_view_files = new liveform('view_files');
// get file's folder in order to validate folder access
$result = mysqli_query(db::$con, "SELECT design, folder FROM files WHERE id = '" . escape($_REQUEST['id']) . "'") or output_error('Query failed');
$row = mysqli_fetch_assoc($result);

// if the user does not have edit rights to this file's folder,
// or this file is a design file and the user is not a designer or administrator,
// then log activity and output error
if (
    (check_edit_access($row['folder']) == false)
    ||
    (
        ($row['design'] == 1)
        && ($user['role'] > 1)
    )
) {
    log_activity("access denied because user does not have access to file", $_SESSION['sessionusername']);
    output_error('Access denied. <a href="javascript:history.go(-1)">Go back</a>.');
}

if (!$_POST['name']) {
    $query = 
        "SELECT 
            files.name,
            files.folder,
            files.description,
            files.type,
            files.size,
            files.design,
            files.optimized,
            folder.folder_archived
        FROM files 
        LEFT JOIN folder ON files.folder = folder.folder_id
        WHERE files.id = '" . escape($_GET['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_array($result);
    
    $file_folder = $row['folder'];
    $file_name = $row['name'];
    $design = $row['design'];
    $optimized = $row['optimized'];
    $folder_archived = $row['folder_archived'];
    
    $output_file_name = '';
    
    // if this files folder is archived, then output a notice next to the file name
    if ($folder_archived == '1') {
        $output_file_name = h($file_name . ' [ARCHIVED]');
    
    // else output the file name as normal
    } else {
        $output_file_name = h($file_name);
    }
    
    // If file type is an image.
    if ((mb_strtolower($row['type']) == 'bmp') || (mb_strtolower($row['type']) == 'gif') || (mb_strtolower($row['type']) == 'jpg') || (mb_strtolower($row['type']) == 'jpeg') || (mb_strtolower($row['type']) == 'png') || (mb_strtolower($row['type']) == 'tif') || (mb_strtolower($row['type']) == 'tiff')) {
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
    $file['type'] = mb_strtolower($row['type']);
    if (    ($file['type'] == 'jpg')
            or ($file['type'] == 'jpeg')
            or ($file['type'] == 'png')
            or ($file['type'] == 'gif')
            or ($file['type'] == 'bmp')
            or ($file['type'] == 'tiff')
        ){
            if (!$optimized){
                $optimize_button =
                    '<a href="optimize.php?id=' . h($_GET['id']) . get_token_query_string_field() . '">
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
    $output_file_size = convert_bytes_to_string ($row['size']);

    $output_design_rows = '';

    // if the user is a designer or administrator, then output design rows
    if ($user['role'] <= 1) {
        $output_design_checked = '';

        // if this file is a design file, then check the design check box
        if ($design == 1) {
            $output_design_checked = ' checked="checked"';
        }

        $output_design_rows =
            '<tr>
                <th colspan="2"><h2>Check if File is a Design File that is Managed by Site Designers</h2></th>
            </tr>
            <tr>
                <td><label for="design">Design:</label></td>
                <td><input type="checkbox" id="design" name="design" value="1"' . $output_design_checked . ' class="checkbox" /></td>
            </tr>';
    }
    
    echo
        output_header() . '
        <div id="subnav">
            ' . $output_thumbnail . '
            <div style="padding: .25em 0em .25em 0em">
                <h1 style="padding: 0em; margin-bottom: .25em"><a href="' . OUTPUT_PATH . $file_name . '" target="_blank">' . $output_file_name . '</a></h1>
                <div class="subheading">File Size: '. $output_file_size .' | Access: ' . h(get_access_control_type_name(get_access_control_type($file_folder))) . '</div>
            </div>
            <div style="clear: both"></div>
        </div>
       
        <div id="button_bar">
            ' . $image_buttons . '
            <a download="image" href="' . OUTPUT_PATH . $file_name . '" >Download</a>
        </div>
        <div id="content">
            
            <a href="#" id="help_link">Help</a>
            <h1>Edit File</h1>
            <p class="subheading">Rename file, move it to another folder, or change its description.</p>
            <form action="edit_file.php" method="post">
                ' . get_token_field() . '
                <input type="hidden" name="id" value="' . h($_GET['id']) . '"><input type="hidden" name="from" value="' . h($from) . '">
                <input type="hidden" name="send_to" value="' . h($_REQUEST['send_to']) . '" />
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>File Name</h2></th>
                    </tr>
                    <tr>
                        <td>Name:</td>
                        <td><input name="name" type="text" value="' . h($file_name) . '" size="40" maxlength="100"></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>File Access Control</h2></th>
                    </tr>
                    <tr>
                        <td>Folder:</td>
                        <td><select name="folder">' . select_folder($row['folder']) . '</select></td>
                    </tr>
                    ' . $output_design_rows . '
                    <tr>
                        <th colspan="2"><h2>File Description / Photo Gallery Caption</h2></th>
                    </tr>
                    <tr>
                        <td style="vertical-align: top">File Description:</td>
                        <td><textarea rows="3" cols="50" name="description">' . h($row['description']) . '</textarea></td>
                    </tr>
                </table>
               
                <div class="buttons">
                    <input type="submit" name="submit_save" value="Save" class="submit-primary" />&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary">&nbsp;&nbsp;&nbsp;<input type="submit" name="delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This file will be permanently deleted.\')" />
                </div>
            </form>
        </div>
        ' . output_footer();

print $output;

} else {
    validate_token_field();
    
    $result=mysqli_query(db::$con, "SELECT name FROM files WHERE id = '" . escape($_POST['id']) . "'") or output_error('Query failed');
    $row=mysqli_fetch_array($result);

    $name = prepare_file_name($_POST['name']);
    
    // if file was selected for delete
    if ($_POST['delete'])
    {    // delete file row
        $result=mysqli_query(db::$con, "DELETE FROM files WHERE id = '" . escape($_POST['id']) . "'") or output_error('Query failed');

        // delete file's system css properties in case any exist
        $query = "DELETE FROM system_theme_css_rules WHERE file_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        db("DELETE FROM preview_styles WHERE theme_id = '" . escape($_POST['id']) . "'");
        
        // Delete file on file system.
        @unlink(FILE_DIRECTORY_PATH . '/' . $name);

        log_activity("file ($name) was deleted", $_SESSION['sessionusername']);
        
        $notice = 'The file was deleted successfully.';
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

        $sql_design = "";

        // if the user is a designer or administrator, then save design property
        if ($user['role'] <= 1) {
            $sql_design = "design = '" . escape($_POST['design']) . "',";
        }

        // update file
        $query =
            "UPDATE files 
            SET 
                name = '" . escape($name) . "',
                folder = '" . escape($_POST['folder']) . "', 
                description = '" . escape($_POST['description']) . "',
                type = '" . escape($file_extension) . "', 
                " . $sql_design . "
                timestamp = UNIX_TIMESTAMP(), 
                user = '" . $user['id'] . "' 
            WHERE id = '" . escape($_POST['id']) . "'";
        
        $result=mysqli_query(db::$con, $query) or output_error('Query failed');
        // rename file's name
        rename(FILE_DIRECTORY_PATH . '/' . $row['name'], FILE_DIRECTORY_PATH . '/' . $name);
        log_activity("file ($name) was modified", $_SESSION['sessionusername']);
        $notice = 'The file was edited successfully.';
    }
    
    $liveform_view_files->add_notice($notice);
    
    // If there is a send to value then send user back to that screen
    if ((isset($_REQUEST['send_to']) == TRUE) && ($_REQUEST['send_to'] != '')) {
        header('Location: ' . URL_SCHEME . HOSTNAME . $_REQUEST['send_to']);
        
    // else send user to the default view
    } else {
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_files.php');
    }
}