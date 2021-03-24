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
validate_area_access($user, 'user');

$form = new liveform('edit_folder');

if (check_edit_access($_REQUEST['id']) == false) {
    log_activity("access denied because user does not have access to modify folder", $_SESSION['sessionusername']);
    output_error('Access denied. <a href="javascript:history.go(-1)">Go back</a>.');
}

if (!$_POST['name']) {
    // get folder data
    $query =
        "SELECT
            folder_id,
            folder_name,
            folder_parent,
            folder_order,
            folder_access_control_type,
            folder_style,
            mobile_style_id,
            folder_archived
        FROM folder
        WHERE folder_id = '" . escape($_GET['id']) . "'";
    $result=mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_array($result);
    
    $folder_id = $row['folder_id'];
    $folder_name = $row['folder_name'];
    $folder_parent = $row['folder_parent'];
    $folder_order = $row['folder_order'];
    $folder_access_control_type = $row['folder_access_control_type'];
    $folder_style = $row['folder_style'];
    $mobile_style_id = $row['mobile_style_id'];
    $folder_archived = $row['folder_archived'];

    $duplicate = '';

    // If this is not root folder and user has edit access to parent folder and has access to
    // create/duplicate pages, then show duplicate button.
    if (
        $folder_parent and check_edit_access($folder_parent)
        and (USER_ROLE < 3 or $user['create_pages'])
    ) {
        $duplicate = '<div id="button_bar"><a href="duplicate_folder.php?id=' . h($_GET['id']) . '">Duplicate</a></div>';
    }
    
    // only display parent folder selection if folder is not root
    if ($folder_parent != 0) {
        $output_folder_row =
            '<tr>
                <td>Parent folder:</td>
                <td><select name="folder">' . select_folder($folder_parent, 0, $excluded_folder_id = $folder_id) . '</select></td>
            </tr>';
    } else {
        $output_folder_row = '';
    }
    
    // if user role is Administrator, Designer, or Manager, then allow user to select style and mobile style for folder
    if ($user['role'] < 3) {
        $output_style = '<select name="style">' . select_style($folder_style) . '</select>';
        $output_mobile_style = '<select name="mobile_style_id">' . get_mobile_style_options($mobile_style_id) . '</select>';
        
    // else user has a user role
    } else {
        // if there is a style set for this folder
        if ($folder_style) {
            // get style name
            $query = "SELECT style_name FROM style WHERE style_id = '$folder_style'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            
            $output_style = h($row['style_name']);
            
        // else there is not a style set for this folder, so get inherited style
        } else {
            // get inherited style
            $style_id = get_style($folder_id, 'desktop');
            
            // get inherited style name
            $query = "SELECT style_name FROM style WHERE style_id = '$style_id'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            
            $output_style = 'Default (inherit): ' . h($row['style_name']);
        }

        // if there is a mobile style set for this folder, then output style name
        if ($mobile_style_id != 0) {
            // get style name
            $query = "SELECT style_name FROM style WHERE style_id = '$mobile_style_id'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            
            $output_mobile_style = h($row['style_name']);
            
        // else there is not a mobile style set for this folder, so output inherited mobile style name
        } else {
            // get inherited style
            $mobile_style_id = get_style($folder_id, 'mobile');
            
            // get inherited style name
            $query = "SELECT style_name FROM style WHERE style_id = '$mobile_style_id'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            
            $output_mobile_style = 'Default (inherit): ' . h($row['style_name']);
        }
    }
    
    // is there a child folder in this folder?
    $query = "SELECT folder_id FROM folder WHERE folder_parent = '" . escape($_GET['id']) . "' LIMIT 1";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed');
    if (mysqli_num_rows($result)) {
        $child_exists = true;
    }

    // is there a child page in this folder?
    $query = "SELECT page_id FROM page WHERE page_folder = '" . escape($_GET['id']) . "' LIMIT 1";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed');
    if (mysqli_num_rows($result)) {
        $child_exists = true;
    }

    // is there a child file in this folder?
    $query = "SELECT id FROM files WHERE folder = '" . escape($_GET['id']) . "' LIMIT 1";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed');
    if (mysqli_num_rows($result)) {
        $child_exists = true;
    }

    // if there is a child object in this folder, then disable delete button
    if ($child_exists == true) {
        // check if there is a design file in this folder
        $query = "SELECT id FROM files WHERE (folder = '" . escape($_GET['id']) . "') AND (design = '1') LIMIT 1";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed');
        
        $output_design_file_notice = '';
        
        // if a design file exists then add extra message about that
        if (mysqli_num_rows($result) > 0) {
            $output_design_file_notice = ' Also, at least one design file exists in this folder. Design files can be deleted by a user that has access to the design tab.';
        }
        
        $output_delete_button = '<input type="button" value="Delete" class="delete" onclick="alert(\'Please delete all folders, pages, and files in this folder before deleting this folder.' . $output_design_file_notice . '\')" />';
    // else there is not a child object in this folder, so allow delete
    } else {
        $output_delete_button = '<input type="submit" name="submit_delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This folder will be permanently deleted.\')" />';
    }
    
    $folder_archived_checked = '';
    
    // if folder archived is 1, then check the checkbox
    if ($folder_archived == '1') {
        $folder_archived_checked = ' checked="checked"';
    }
    
    echo
        output_header() . '
        <div id="subnav">
            <h1>' . h($folder_name) . '</h1>        
        </div>
        ' . $duplicate . '
        <div id="content"> 
            ' . $form->get_messages() . '
            <a href="#" id="help_link">Help</a>
            <h1>Edit Folder</h1>
            <div class="subheading">View and update this folder.</div>
            <form action="edit_folder.php" method="post">
                ' . get_token_field() . '
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>Folder Name</h2></th>
                    </tr>
                    <tr>
                        <td>Folder Name:</td>
                        <td><input name="name" type="text" value="' . h($folder_name) . '" size="60" maxlength="100"></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Parent Folder</h2></th>
                    </tr>
                    ' . $output_folder_row . '
                    <tr>
                        <th colspan="2"><h2>Access Control for Pages and Files within this Folder</h2></th>
                    </tr>
                    <tr>
                        <td>Folder Access Control Type:</td>
                        <td><select name="access_control_type">' . select_access_control_type($folder_access_control_type) . '</select></td>
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
                        <td><input name="order" type="text" value="' . $folder_order . '" size="5"></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Archive Folder for Pages and Files that are no longer being used</h2></th>
                    </tr>
                    <tr>
                        <td><label for="folder_archived">Archive:</label></td>
                        <td><input type="checkbox" id="folder_archived" name="folder_archived" value="1"' . $folder_archived_checked . ' class="checkbox" /></td>
                    </tr>
                </table>
                <input type="hidden" name="id" value="' . h($_GET['id']) . '">
                <div class="buttons">
                    <input type="submit" name="submit_save" value="Save" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary">&nbsp;&nbsp;&nbsp;' . $output_delete_button . '
                </div>
            </form>
        </div>' .
        output_footer();

        $form->remove();
    
} else {
    validate_token_field();
    
    // Add liveform notices
    include_once('liveform.class.php');
    $liveform_view_folders = new liveform('view_folders');
    
    // if folder was selected for deletion
    if ($_POST['submit_delete'] == 'Delete') {
        // assume that a child item does not exist until we find out otherwise
        $child_exists = false;
        
        // is there a child folder in this folder?
        $query = "SELECT folder_id FROM folder WHERE folder_parent = '" . escape($_POST['id']) . "' LIMIT 1";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed');
        if (mysqli_num_rows($result)) {
            $child_exists = true;
        }

        // is there a child page in this folder?
        $query = "SELECT page_id FROM page WHERE page_folder = '" . escape($_POST['id']) . "' LIMIT 1";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed');
        if (mysqli_num_rows($result)) {
            $child_exists = true;
        }

        // is there a child file in this folder?
        $query = "SELECT id FROM files WHERE folder = '" . escape($_POST['id']) . "' LIMIT 1";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed');
        if (mysqli_num_rows($result)) {
            $child_exists = true;
        }

        // if there is a child object in this folder, then output error
        if ($child_exists == true) {
            output_error('Please delete all folders, pages, and files in this folder before deleting this folder. <a href="javascript:history.go(-1)">Go back</a>.');
        }
        
        // delete entries in acl for this folder
        $result=mysqli_query(db::$con, "DELETE FROM aclfolder WHERE aclfolder_folder = '" . escape($_POST['id']) . "'") or output_error('Query failed');
        // delete folder
        $result=mysqli_query(db::$con, "DELETE FROM folder WHERE folder_id = '" . escape($_POST['id']) . "'") or output_error('Query failed');
        log_activity("folder ($_POST[name]) was deleted", $_SESSION['sessionusername']);
        $notice = 'The folder was deleted successfully.';
        
    } else {
        // get parent
        $result=mysqli_query(db::$con, "SELECT folder_parent FROM folder WHERE folder_id = '" . escape($_POST['id']) . "'") or output_error('Query failed');
        $row=mysqli_fetch_array($result);

        // if select form was blank or not displayed
        if (!$_POST['folder']) {
            // if folder is root
            if ($row['folder_parent'] == 0) {
                $folder = 0;
            // else select form was blank
            } else {
                $folder = $row['folder_parent'];
            }
        // else select form was displayed
        } else {
            $folder = $_POST['folder'];
        }

        // if parent has changed then execute
        if ($folder != $row['folder_parent'])
        {
            // find level of folder being moved
            $result=mysqli_query(db::$con, "SELECT folder_level FROM folder WHERE folder_id = '" . escape($_POST['id']) . "'") or output_error('Query failed');
            $row=mysqli_fetch_array($result);
            $level_folder = $row[folder_level];
            // get level of parent
            $result=mysqli_query(db::$con, "SELECT folder_level FROM folder WHERE folder_id = '" . escape($folder) . "'") or output_error('Query failed');
            $row=mysqli_fetch_array($result);
            $level_parent = $row[folder_level];
            // how much each level will change
            $change_level = $level_parent + 1;
            // update the parent and level attributes of the folder being moved
            $result=mysqli_query(db::$con, "UPDATE folder SET folder_parent = '" . escape($folder) . "', folder_level = '$change_level' WHERE folder_id = '" . escape($_POST['id']) . "'") or output_error('Query failed');

            // if the level of the subfolders do not need to be changed then don't execute code
            if (($level_folder - $level_parent) != 1)
            {
                // change level of sub-folders
                change_level($_POST['id'], $change_level);
            }
        }

        $name = trim($_POST['name']);

        $sql_style_fields = "";
        
        // if user role is Administrator, Designer, or Manager, then allow user to change style and mobile style for folder
        if ($user['role'] < 3) {
            $sql_style_fields =
                "folder_style = '" . escape($_POST['style']) . "',
                mobile_style_id = '" . escape($_POST['mobile_style_id']) . "',";
        }
        
        $query =
            "UPDATE folder
            SET
                folder_name = '" . escape($name) . "',
                folder_order = '" . escape($_POST['order']) . "',
                folder_access_control_type = '" . escape($_POST['access_control_type']) . "',
                folder_archived = '" . escape($_POST['folder_archived']) . "',
                $sql_style_fields
                folder_timestamp = UNIX_TIMESTAMP(),
                folder_user = '" . $user['id'] . "'
            WHERE folder_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        log_activity("folder ($name) was modified", $_SESSION['sessionusername']);
        $notice = 'The folder was edited successfully.';
    }
    // Add notice to liveform.
    $liveform_view_folders->add_notice($notice);
    // Redirect user back to view folders.
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_folders.php');
}

// functions

// change level of sub-folders using recursion
function change_level($parent_id, $parent_level)
{
    // find sub-folders that need to be moved so that we can change their level
    $result=mysqli_query(db::$con, "SELECT folder_id, folder_level FROM folder WHERE folder_parent = '" . escape($parent_id) . "'") or output_error('Query failed');
    while($row=mysqli_fetch_array($result))
    {
        // update the level of the sub-folder
        $result2=mysqli_query(db::$con, "UPDATE folder SET folder_level = '" . escape($parent_level) . "' + 1 WHERE folder_id = '" . $row['folder_id'] . "'") or output_error('Query failed');
        // check for subfolders
        $result_check=mysqli_query(db::$con, "SELECT folder_id FROM folder WHERE folder_parent = '" . $row['folder_id'] . "'") or output_error('Query failed');
        if(mysqli_num_rows($result_check) > 0)
        {
            // recursion
            change_level($row['folder_id'], $parent_level + 1);
        }
    }
}