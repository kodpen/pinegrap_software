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
$liveform_add_folder = new liveform('add_folder');
$user = validate_user();
validate_area_access($user, 'user');

if (!$_POST) {
    // if user role is Administrator, Designer, or Manager, then allow user to select style and mobile style for folder
    if ($user['role'] < 3) {
        $output_style = '<select name="style">' . select_style() . '</select>';
        $output_mobile_style = '<select name="mobile_style_id">' . get_mobile_style_options() . '</select>';
        
    // else user has a user role so don't allow user to select style and mobile style for folder
    } else {
        $output_style = 'Default (inherit)';
        $output_mobile_style = 'Default (inherit)';
    }
    
    print
        output_header() . '
        <div id="subnav">
            <h1>[new folder]</h1>
        </div>
        <div id="content">
            
            ' . $liveform_add_folder->output_errors() . '
            ' . $liveform_add_folder->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Create Folder</h1>
            <div class="subheading">Create a new folder to secure pages & files.</div>
            <form action="add_folder.php" method="post">
                ' . get_token_field() . '
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>New Folder Name</h2></th>
                    </tr>
                    <tr>
                        <td>Folder Name:</td>
                        <td><input name="name" type="text" size="60" maxlength="100"></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Parent Folder</h2></th>
                    </tr>
                    <tr>
                        <td>Parent Folder:</td>
                        <td><select name="folder">' . select_folder() . '</select></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Access Control for all Pages and Files within this Folder</h2></th>
                    </tr>
                    <tr>
                        <td>Folder Access Control Type:</td>
                        <td><select name="access_control_type">' . select_access_control_type() . '</select></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Default Page Styles for Pages within this Folder</h2></th>
                    </tr>
                    <tr>
                        <td>Desktop Page Style:</td>
                        <td>' . $output_style . '</td>
                    </tr>
                    <tr>
                        <td>Mobile Page Style:</td>
                        <td>' . $output_mobile_style . '</td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Display order of this Folder to other Folders</h2></th>
                    </tr>
                    <tr>
                        <td>Sort Order:</td>
                        <td><input name="order" type="text" size="5"></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Archive Folder for Pages and Files that are no longer being used</h2></th>
                    </tr>
                    <tr>
                        <td><label for="folder_archived">Archive:</label></td>
                        <td><input type="checkbox" id="folder_archived" name="folder_archived" value="1" class="checkbox" /></td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_create" value="Create" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary">
                </div>
            </form>
        </div>' .
        output_footer();
    
    $liveform_add_folder->unmark_errors('add_page');
    $liveform_add_folder->clear_notices('add_page');
    
} else {
    validate_token_field();
    
    // validate access to create folder in parent folder
    if (check_edit_access($_POST['folder']) == false) {
        log_activity("access denied because user does not have access to create folder in parent folder", $_SESSION['sessionusername']);
        output_error('Access denied. <a href="javascript:history.go(-1)">Go back</a>.');
    }
    
    // find level of parent
    $result=mysqli_query(db::$con, "SELECT folder_level FROM folder WHERE folder_id = '" . escape($_POST['folder']) . "'") or output_error('Query failed');
    $row=mysqli_fetch_array($result);
    $level = ++$row[folder_level];

    $name = trim($_POST['name']);
    
    // If the folder name field is blank.
    if ($name == '') {
        // Create notice.
		$liveform_add_folder->mark_error('name', 'The folder must have a name. Please type in a name for the folder.');
        
        // Refresh page.
        header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/add_folder.php');
        exit();
    }

    $sql_style_fields = "";
    $sql_style_values = "";
    
    // if user role is Administrator, Designer, or Manager, then allow user to set style and mobile style for folder
    if ($user['role'] < 3) {
        $sql_style_fields =
            "folder_style,
            mobile_style_id,";
        
        $sql_style_values =
            "'" . escape($_POST['style']) . "',
            '" . escape($_POST['mobile_style_id']) . "',";
    }
    
    // insert row into folder table
    $query =
        "INSERT INTO folder (
            folder_name,
            folder_parent,
            folder_level,
            folder_order,
            folder_access_control_type,
            folder_archived,
            $sql_style_fields
            folder_timestamp,
            folder_user)
        VALUES (
            '" . escape($name) . "',
            '" . escape($_POST['folder']) . "',
            '" . escape($level) . "',
            '" . escape($_POST['order']) . "',
            '" . escape($_POST['access_control_type']) . "',
            '" . escape($_POST['folder_archived']) . "',
            $sql_style_values
            UNIX_TIMESTAMP(),
            '$user[id]')";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed');
    
    log_activity("folder ($name) was created", $_SESSION['sessionusername']);
    
    $notice = 'The folder was created successfully.';
    $liveform_view_folders = new liveform('view_folders');
    $liveform_view_folders->add_notice($notice);
    
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_folders.php');
}
?>
