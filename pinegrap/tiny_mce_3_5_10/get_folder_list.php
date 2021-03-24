<?php
include('../init.php');

if ($_SESSION['sessionusername']) {
    $user = validate_user();
    
    $output_js = "var tinyMCEFolderList = new Array(
        // Name, URL\r\n";
    
    $output_js .= mb_substr(select_folder_tiny_mce($folder_id, 0),2);

    $output_js .= ');';

    print $output_js;
}

function select_folder_tiny_mce($folder_id = 0, $parent_folder_id = 0, $excluded_folder_id = 0, $level = 0, $folders = array(), $folders_that_user_has_access_to = array())
{
    global $user;
    
    $output = '';
    
    // if this is the first time this function has run, then get folders and folders that user has has access to
    if ($parent_folder_id == 0) {
        // get all folders
        $query =
            "SELECT
                folder_id as id,
                folder_name as name,
                folder_parent as parent_folder_id,
                folder_archived as archived
            FROM folder
            ORDER BY folder_level, folder_order, folder_name";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        while ($row = mysqli_fetch_assoc($result)) {
            $folders[] = $row;
        }
        
        // if user is a basic user, then get folders that user has access to
        if ($user['role'] == 3) {
            $folders_that_user_has_access_to = get_folders_that_user_has_access_to($user['id']);
        }
    }
    
    $child_folders = array();
    
    // loop through folders array in order to get all folders that are in parent folder
    foreach ($folders as $folder) {
        // if the parent folder id for this folder is equal to the parent folder id, then this is a child folder, so add to array
        if ($folder['parent_folder_id'] == $parent_folder_id) {
            $child_folders[] = $folder;
        }
    }
    
    // loop through child folders
    foreach ($child_folders as $folder) {
        // if folder id is not equal to excluded folder id, then continue to prepare option and get child folders
        if ($folder['id'] != $excluded_folder_id) {
            // if user has access to folder, or if the folder is the selected folder, then output option for folder
            if ((check_folder_access_in_array($folder['id'], $folders_that_user_has_access_to) == true) || ($folder['id'] == $folder_id)) {
                // prepare indentation
                $indentation = '';
                
                for ($i = 1; $i <= $level; $i++)
                {
                    $indentation .= '&nbsp;&nbsp;&nbsp;&nbsp;';
                }
                
                $next_level = $level + 1;

                // if this folder is the selected folder, then this option should be selected
                if ($folder['id'] == $folder_id) {
                    $selected = ' selected';
                } else {
                    $selected = '';
                }
                
                $archived_label = '';
                
                // if this folder is archived, then output archived beside the folder name
                if ($folder['archived'] == '1') {
                    $archived_label = ' [ARCHIVED]';
                }
                
                // output folder
                $output .= ", \r\n" . '["' . $indentation . h($folder['name'] . $archived_label) . '", "' . $folder['id'] . '"]';
            }
            
            // get options for child folders
            $output .= select_folder_tiny_mce($folder_id, $folder['id'], $excluded_folder_id, $next_level, $folders, $folders_that_user_has_access_to);
        }
    }
    
    return $output;
}
?>