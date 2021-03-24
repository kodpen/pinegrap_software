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

// If the token does not exist in the session,
// or the passed token does not match the token from the session,
// then this might be a CSRF attack so output error.
// We don't log activity anymore for this because the log was getting filled up with these messages
// when we tried to log it in the past.  Innocent activities (e.g. session expired, web crawlers, spammers)
// and not CSRF attacks generate most of these errors, so it is not important to log them.
// We are not using the validate_token_field function here because it does not
// support returning an error like we need for this situation.
if (
    ($_SESSION['software']['token'] == '')
    ||
    (
        ($_POST['token'] != $_SESSION['software']['token'])
        && ($_GET['token'] != $_SESSION['software']['token'])
    )
) {
    echo '<script>window.parent.CKEDITOR.tools.callFunction("' . escape_javascript($_GET['CKEditorFuncNum']) . '", "", "Sorry, the file could not be uploaded, because it appears that your session expired. We recommend that you refresh the page and try again.")</script>';
    exit();
}

// If the user is not logged in, then log activity and output error.
if (USER_LOGGED_IN == false) {
    log_activity('access denied to upload file in editor because user was not logged in', $_SESSION['sessionusername']);
    echo '<script>window.parent.CKEDITOR.tools.callFunction("' . escape_javascript($_GET['CKEditorFuncNum']) . '", "", "Sorry, the file could not be uploaded, because you are not logged in.")</script>';
    exit();
}

// If the user has a user role and they do not have edit access to any folders,
// then log activity and output error.
if ((USER_ROLE == 3) && (no_acl_check(USER_ID) == false)) {
    log_activity('access denied to upload file in editor because user did not have edit access to any folders', $_SESSION['sessionusername']);
    echo '<script>window.parent.CKEDITOR.tools.callFunction("' . escape_javascript($_GET['CKEditorFuncNum']) . '", "", "Sorry, the file could not be uploaded, because you do not have edit access to any folders.")</script>';
    exit();
}

$folder_id = 0;

// If a folder id was passed then that means that user is using editor
// on a page, so if user has edit access to that page's folder,
// then allow file to be uploaded into that folder.
if (
    ($_GET['folder_id'])
    && (check_edit_access($_GET['folder_id']) == true)
) {
    $folder_id = $_GET['folder_id'];

// Otherwise a folder id was not passed or the user does not have edit access to the folder that was passed,
// so find the first folder in the folder tree that the user has edit access to.
// We will just use that folder as a default.  First we will look for a public folder,
// and if we can't find one, then we will look for the first folder of any access control.
} else {
    // First look for the first public folder in the folder tree that the user has edit access to.
    $folder_id = get_first_folder_id(0, array(), array(), 'public');

    // If we could not find a public folder that the user has edit access to,
    // then try to find first folder of any access type.
    if (!$folder_id) {
        $folder_id = get_first_folder_id(0, array(), array(), '');

        // If for some reason we still could not find a folder
        // (should never happen because we checked that user at least had some edit access above),
        // then log activity and output error.
        if (!$folder_id) {
            log_activity('access denied to upload file in editor because user did not have edit access to any folders', $_SESSION['sessionusername']);
            echo '<script>window.parent.CKEDITOR.tools.callFunction("' . escape_javascript($_GET['CKEditorFuncNum']) . '", "", "Sorry, the file could not be uploaded, because you do not have edit access to any folders.")</script>';
            exit();
        }
    }
}

$file_name = prepare_file_name($_FILES['upload']['name']);

$file_name = get_unique_name(array(
    'name' => $file_name,
    'type' => 'file'));

$file_type = $_FILES['upload']['type'];
$file_size = $_FILES['upload']['size'];
$file_temp_name = $_FILES['upload']['tmp_name'];

// get file extension
$array_file_extension = explode('.', $file_name);
$size_of_array = count($array_file_extension);
$file_extension = $array_file_extension[$size_of_array - 1];

// create file
copy($file_temp_name, FILE_DIRECTORY_PATH . '/' . $file_name);

// insert file data into file table
$query =    "INSERT INTO files ".
            "(name, folder, type, size, user, timestamp, design) ".
            "VALUES ('" . escape($file_name) . "','" . escape($folder_id) . "','" . escape($file_extension) . "','" . escape($file_size) . "','" . USER_ID . "', UNIX_TIMESTAMP(), '0')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$file_id = mysqli_insert_id(db::$con);

log_activity("file ($file_name) was created", $_SESSION['sessionusername']);

echo '<script>window.parent.CKEDITOR.tools.callFunction("' . escape_javascript($_GET['CKEditorFuncNum']) . '", "/' . escape_javascript($file_name) . '", "")</script>';
exit();

// Create a function that will go through the whole folder tree in correct order
// and find the first folder that the user has edit access to and that matches
// the access control type requirements.
function get_first_folder_id($parent_folder_id = 0, $folders = array(), $folders_that_user_has_access_to = array(), $access_control_type = '')
{
    // If this is the first time this function has run, then get all folders
    // and folders that user has has access to.
    if ($parent_folder_id == 0) {
        // Get all folders.
        $folders = db_items(
            "SELECT
                folder_id as id,
                folder_parent as parent_folder_id,
                folder_archived as archived
            FROM folder
            ORDER BY folder_level, folder_order, folder_name");
        
        // If user has a user role, then get folders that user has access to.
        if (USER_ROLE == 3) {
            $folders_that_user_has_access_to = get_folders_that_user_has_access_to(USER_ID);
        }
    }

    $child_folders = array();
    
    // Loop through folders array in order to get all folders that are in parent folder.
    foreach ($folders as $folder) {
        // If the parent folder id for this folder is equal to the parent folder id,
        // then this is a child folder, so add to array.
        if ($folder['parent_folder_id'] == $parent_folder_id) {
            $child_folders[] = $folder;
        }
    }

    // Loop through child folders.
    foreach ($child_folders as $folder) {
        // If this folder is not archived and user has edit access to folder,
        // and the access control type matches, then we have found what we want,
        // so return the folder id.
        if (
            ($folder['archived'] == 0)
            &&
            (
                (USER_ROLE < 3)
                || (in_array($folder['id'], $folders_that_user_has_access_to) == true)
            )
            &&
            (
                ($access_control_type == '')
                || ($access_control_type == get_access_control_type($folder['id']))
            )
        ) {
            return $folder['id'];

        // Otherwise the folder is archived, or the user does not have edit access to this folder,
        // or the access control type does not match what we are looking for,
        // so look at child folders of this child folder.
        } else {
            $first_folder_id = get_first_folder_id($folder['id'], $folders, $folders_that_user_has_access_to, $access_control_type);

            if ($first_folder_id != '') {
                return $first_folder_id;
            }
        }
    }
    
    // If we have gotten here then that means that we did not a folder
    // that we were looking for, so just empty string.
    return '';
}
?>