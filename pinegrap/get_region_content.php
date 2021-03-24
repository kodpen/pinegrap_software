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

$output = '';

switch ($_GET['region_type']) {
    // if type is pregion
    case 'pregion':
        // get region information
        $query =
            "SELECT
                pregion.pregion_content,
                pregion.pregion_page as page_id,
                page.page_folder as folder_id
            FROM pregion
            LEFT JOIN page ON pregion.pregion_page = page.page_id
            WHERE pregion.pregion_id = '" . escape($_GET['region_id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        
        $pregion_content = $row['pregion_content'];
        $page_id = $row['page_id'];
        $folder_id = $row['folder_id'];
        
        // if the user does not have access to this page region, then output a notice to give the user
        if (check_edit_access($folder_id) == false) {
            log_activity("access denied to edit region because user does not have access to modify folder that page is in", $_SESSION['sessionusername']);
            output_error('Access denied.');
            
        // else the user has access so set output
        } else {
            $output = $pregion_content;
        }
        break;
    
    // if type is cregion
    case 'cregion':
        // if user has a user role and if they do not have access to this common region, then user does not have access to edit region, so output error
        if (($user['role'] == 3) && (in_array($_GET['region_id'], get_items_user_can_edit('common_regions', $user['id'])) == FALSE)) {
            $query = "SELECT cregion_name FROM cregion WHERE cregion_id = '" . escape($_GET['region_id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            
            log_activity("access denied because user does not have access to edit common region (" . $row['cregion_name'] . ")", $_SESSION['sessionusername']);
            output_error('Access denied.');
        
        // else the user has access to output the content
        } else {
            // get common region content
            $query = "SELECT cregion_content FROM cregion WHERE cregion_id = '" . escape($_GET['region_id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $output = $row['cregion_content'];
        }
        break;

    // if type is system_region_header
    case 'system_region_header':
        // get system region header content
        $query =
            "SELECT
                system_region_header,
                page_folder as folder_id
            FROM page
            WHERE page_id = '" . escape($_GET['region_id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        
        $system_region_header = $row['system_region_header'];
        $folder_id = $row['folder_id'];
        
        // if the user does not have access to the page, then output a notice to the user
        if (check_edit_access($folder_id) == false) {
            log_activity('access denied to edit system region header because user does not have access to modify folder that page is in', $_SESSION['sessionusername']);
            output_error('Access denied.');
            
        // else the user has access so set output
        } else {
            $output = $system_region_header;
        }

        break;

    // if type is system_region_footer
    case 'system_region_footer':
        // get system region footer content
        $query =
            "SELECT
                system_region_footer,
                page_folder as folder_id
            FROM page
            WHERE page_id = '" . escape($_GET['region_id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        
        $system_region_footer = $row['system_region_footer'];
        $folder_id = $row['folder_id'];
        
        // if the user does not have access to the page, then output a notice to the user
        if (check_edit_access($folder_id) == false) {
            log_activity('access denied to edit system region footer because user does not have access to modify folder that page is in', $_SESSION['sessionusername']);
            output_error('Access denied.');
            
        // else the user has access so set output
        } else {
            $output = $system_region_footer;
        }
        
        break;
}

// replace {path} placeholders with actual path so content works correctly in rich-text editor
// and then print the output
print prepare_rich_text_editor_content_for_output($output);
?>