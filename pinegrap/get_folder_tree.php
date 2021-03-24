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

// prepare expanded folders array from cookie
$expanded_folders = explode(',', $_COOKIE['software']['view_folders']['expanded_folders']);

$folders_that_user_has_access_to = array();

// if user is a basic user, then get folders that user has access to
if ($user['role'] == 3) {
    $folders_that_user_has_access_to = get_folders_that_user_has_access_to($user['id']);
}

header("Content-type: text/xml");
print
'<?xml version="1.0" encoding="utf-8" ?>
<root>' . get_folder_tree($_GET['folder_id']) . '</root>';

function get_folder_tree($parent_folder_id)
{
    global $user;
    global $expanded_folders;
    global $folders_that_user_has_access_to;
    
    // get styles
    $query = "SELECT style_id, style_name FROM style";
    $style_result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    // get folders
    $query = "SELECT
                folder.folder_id,
                folder.folder_name,
                folder.folder_level,
                folder.folder_style,
                folder.folder_archived,
                style.style_id,
                style.style_name
             FROM folder
             LEFT JOIN style ON folder.folder_style = style.style_id
             WHERE folder.folder_parent = '" . escape($parent_folder_id) . "'
             ORDER BY folder.folder_order, folder.folder_name";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    while ($row = mysqli_fetch_assoc($result)) {
        // if user has access to folder
        if (check_folder_access_in_array($row['folder_id'], $folders_that_user_has_access_to) == true) {
            $folder_access = true;
        } else {
            $folder_access = false;
        }
        
        // if user has access to folder
        if ($folder_access == true) {
            $access_control_type = get_access_control_type($row['folder_id']);
            
            // If the folder style is not set to zero then set the page style name.
            if ($row['folder_style'] != '0') {
                $style = $row['style_name'];
                
            // Else the page style is inherited.
            } else {
                $style = 'Inherited';
            }
            
            $folder_archived = 'false';
            
            // if the folder is archived, then set archived to true
            if ($row['folder_archived'] == '1') {
                $folder_archived = 'true';
            }
            
            // output folder
            $output .=
                '<folder>' .
                    '<id>' . $row['folder_id'] . '</id>' .
                    '<name>' . h($row['folder_name']) . '</name>' .
                    '<style>' . h($style) . '</style>' .
                    '<access_control_type>' . $access_control_type . '</access_control_type>' .
                    '<archived>' . $folder_archived . '</archived>' . "\n";
        }
        
        // if this folder is expanded or if all folders are being expanded,
        // or if this is the root folder,
        // or if this is a basic user and user does not have access to this folder then we need to continue to see if user has access to a further folder
        // use recursion to get other objects in this folder
        if (
            (in_array($row['folder_id'], $expanded_folders))
            || ($_GET['expand_all'] == 'true')
            || ($row['folder_id'] == 1)
            || (($user['role'] == 3) && ($folder_access == false))
            ) {
            $output .= get_folder_tree($row['folder_id']);
        }
        
        // if user has access to folder
        if ($folder_access == true) {
            $output .= '</folder>';
        }
    }
    
    // if user has access to folder
    if (check_folder_access_in_array($parent_folder_id, $folders_that_user_has_access_to) == true) {
        // get pages
        $query = "SELECT
                    page.page_id,
                    page.page_name,
                    page.page_folder,
                    page.page_style,
                    page.page_home,
                    page.page_type,
                    style.style_id,
                    style.style_name,
                    folder.folder_archived
                 FROM page
                 LEFT JOIN style ON page.page_style = style.style_id
                 LEFT JOIN folder ON page.page_folder = folder.folder_id
                 WHERE page.page_folder = '" . escape($parent_folder_id) . "'
                 ORDER BY page.page_name";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        while ($row = mysqli_fetch_assoc($result)) {
            
            // If the folder style is not set to zero then set the page style name.
            if ($row['page_style'] != '0') {
                $style = $row['style_name'];
                
            // Else the page style is inherited.
            } else {
                $style = '&nbsp;';
            }
            
            if ($row['page_home'] == 'yes') {
                $home = 'true';
            } else {
                $home = 'false';
            }
            
            $folder_archived = 'false';
            
            // if the folder is archived, then set archived to true
            if ($row['folder_archived'] == '1') {
                $folder_archived = 'true';
            }
            
            // output page
            $output .=
                '<page>' .
                    '<id>' . $row['page_id'] . '</id>' .
                    '<name>' . h($row['page_name']) . '</name>' .
                    '<style>' . h($style) . '</style>' .
                    '<home>' . $home . '</home>' .
                    '<type>' . h($row['page_type']) . '</type>' .
                    '<archived>' . $folder_archived . '</archived>' .
                '</page>' . "\n";
        }

        // get files
        $query = "SELECT
                    files.id,
                    files.name,
                    files.design,
                    folder.folder_archived
                 FROM files
                 LEFT JOIN folder ON files.folder = folder.folder_id
                 WHERE files.folder = '" . escape($parent_folder_id) . "'
                 ORDER BY files.name";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        while ($row = mysqli_fetch_assoc($result)) {
            $design = 'false';
            
            // if the file is a design file, then set design to true
            if ($row['design'] == '1') {
                $design = 'true';
            }

            $access = 'false';

            // if this is not a design file or if the user has access to design files,
            // then the user has access so send that
            if (
                ($row['design'] == 0)
                || ($user['role'] <= 1)
            ) {
                $access = 'true';
            }

            $folder_archived = 'false';
            
            // if the folder is archived, then set archived to true
            if ($row['folder_archived'] == '1') {
                $folder_archived = 'true';
            }
            
            // output file
            $output .=
                '<file>' .
                    '<id>' . $row['id'] . '</id>' .
                    '<name>' . h($row['name']) . '</name>' .
                    '<design>' . $design . '</design>' .
                    '<access>' . $access . '</access>' .
                    '<archived>' . $folder_archived . '</archived>' .
                '</file>' . "\n";
        }
    }
    
    return $output;
}
?>