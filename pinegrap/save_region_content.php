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

validate_token_field();

include_once('liveform.class.php');
$liveform = new liveform('region_content');

$liveform->add_fields_to_session();

$page_id = $liveform->get_field_value('page_id');

// get page properties from the database
$query =
    "SELECT
        page_name,
        page_folder,
        seo_analysis_current
    FROM page
    WHERE page_id = '" . escape($page_id) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$page_name = $row['page_name'];
$folder_id = $row['page_folder'];
$seo_analysis_current = $row['seo_analysis_current'];

$sql_seo_analysis_current = "";

// replace path references with a placeholder, so the path is not stored in the database
$liveform->assign_field_value('region_content', prepare_rich_text_editor_content_for_input($liveform->get_field_value('region_content')));

switch ($liveform->get_field_value('region_type')) {
    // if type is pregion
    case 'pregion':
        // if region already exists then update the region
        if ($liveform->get_field_value('region_id')) {
            // get region information
            // we get page information again that we have already gotten above, in case the user is trying to hack and send a region id that is not part of the page id that they passed
            $query =
                "SELECT
                    pregion.pregion_content,
                    pregion.pregion_page as page_id,
                    page.page_name as page_name,
                    page.page_folder as folder_id,
                    page.seo_analysis_current
                FROM pregion
                LEFT JOIN page ON pregion.pregion_page = page.page_id
                WHERE pregion.pregion_id = '" . escape($liveform->get_field_value('region_id')) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            
            $page_region_content = $row['pregion_content'];
            $page_id = $row['page_id'];
            $page_name = $row['page_name'];
            $folder_id = $row['folder_id'];
            $seo_analysis_current = $row['seo_analysis_current'];
            
            // validate user's access to page region
            if (check_edit_access($folder_id) == false) {
                log_activity("access denied to edit region because user does not have access to modify folder that page is in", $_SESSION['sessionusername']);
                output_error('Access denied.');
            }

            // update region content
            $query =
                "UPDATE pregion
                SET 
                    pregion_content = '" . escape($liveform->get_field_value('region_content')) . "', 
                    pregion_user = '" . $user['id'] . "', 
                    pregion_timestamp = UNIX_TIMESTAMP()
                WHERE pregion_id = '" . escape($liveform->get_field_value('region_id')) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // if the seo analysis is current and the page region content has changed, then prepare to remove current status
            if (($seo_analysis_current == 1) && ($page_region_content != $liveform->get_field_value('region_content'))) {
                $sql_seo_analysis_current = "seo_analysis_current = '0',";
            }
            
        // else region does not exist so create a new one
        } else {
            // validate that user has access to create page region for the page
            if (check_edit_access($folder_id) == false) {
                log_activity("access denied to edit region because user does not have access to modify folder that page is in", $_SESSION['sessionusername']);
                output_error('Access denied.');
            }

            $order = $liveform->get_field_value('region_order');
            $collection = $liveform->get_field_value('collection');

            // If this region's order is greater than 1, then check if we need to create
            // placeholder regions for earlier page regions.  This is necessary,
            // so that the content will appear in the correct page region location,
            // when the page is refreshed (i.e. not moved up to the top region).
            if ($order > 1) {
                // Loop through each earlier page region.
                for ($region_order = 1; $region_order < $order; $region_order++) {
                    // If a page region does not yet exist for this earlier region,
                    // then create a blank page region for it.
                    if (!db_value(
                        "SELECT pregion_id
                        FROM pregion
                        WHERE
                            (pregion_page = '" . e($page_id) . "')
                            AND (pregion_order = '$region_order')
                            AND (collection = '" . e($collection) . "')")
                    ) {
                        db(
                            "INSERT INTO pregion (
                                pregion_name,
                                pregion_page,
                                pregion_order,
                                collection,
                                pregion_user,
                                pregion_timestamp)
                            VALUES (
                                '" . e(time() . '_' . $region_order) . "',
                                '" . e($page_id) . "',
                                '" . e($region_order) . "',
                                '" . e($collection) . "',
                                '" . $user['id'] . "',
                                UNIX_TIMESTAMP())");
                    }
                }
            }
            
            // create region name
            $region_name = time() . '_' . $order;
            
            $query =
                "INSERT INTO pregion (
                    pregion_name,
                    pregion_content,
                    pregion_page,
                    pregion_order,
                    collection,
                    pregion_user,
                    pregion_timestamp)
                VALUES (
                    '" . e($region_name) . "',
                    '" . e($liveform->get_field_value('region_content')) . "',
                    '" . e($page_id) . "',
                    '" . e($order) . "',
                    '" . e($collection) . "',
                    '" . $user['id'] . "',
                    UNIX_TIMESTAMP())";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // if the seo analysis is current and the new page region has content, then prepare to remove current status
            if (($seo_analysis_current == 1) && ($liveform->get_field_value('region_content') != '')) {
                $sql_seo_analysis_current = "seo_analysis_current = '0',";
            }
        }
        break;
    
    // if type is cregion
    case 'cregion':
        // if user has a user role and if they do not have access to this common region, then user does not have access to edit region, so output error
        if (($user['role'] == 3) && (in_array($liveform->get_field_value('region_id'), get_items_user_can_edit('common_regions', $user['id'])) == FALSE)) {
            $query = "SELECT cregion_name FROM cregion WHERE cregion_id = '" . escape($liveform->get_field_value('region_id')) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            
            log_activity("access denied because user does not have access to edit common region (" . $row['cregion_name'] . ")", $_SESSION['sessionusername']);
            output_error('Access denied.');
        }
        
        // update region content
        $query =
            "UPDATE cregion
            SET 
                cregion_content = '" . escape($liveform->get_field_value('region_content')) . "', 
                cregion_user = '" . $user['id'] . "', 
                cregion_timestamp = UNIX_TIMESTAMP()
            WHERE cregion_id = '" . escape($liveform->get_field_value('region_id')) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        break;

    // if type is system_region_header then update it
    case 'system_region_header':
        // get system region header information
        $query =
            "SELECT
                page_id,            
                page_name,
                page_folder as folder_id
            FROM page
            WHERE page_id = '" . escape($liveform->get_field_value('region_id')) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        
        $page_id = $row['page_id'];
        $page_name = $row['page_name'];
        $folder_id = $row['folder_id'];
        
        // validate user's access to system region header
        if (check_edit_access($folder_id) == false) {
            log_activity('access denied to edit system region header (' . $page_name . ') because user does not have access to modify folder that page is in', $_SESSION['sessionusername']);
            output_error('Access denied.');
        }

        // update the info for the system region header and its page
        $query =
            "UPDATE page
            SET 
                system_region_header = '" . escape($liveform->get_field_value('region_content')) . "',
                page_user = '" . $user['id'] . "',
                page_timestamp = UNIX_TIMESTAMP()
            WHERE page_id = '" . escape($liveform->get_field_value('region_id')) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        break;

    // if type is system_region_footer then update it
    case 'system_region_footer':
        // get system region footer information
        $query =
            "SELECT
                page_id,            
                page_name,
                page_folder as folder_id
            FROM page
            WHERE page_id = '" . escape($liveform->get_field_value('region_id')) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        
        $page_id = $row['page_id'];
        $page_name = $row['page_name'];
        $folder_id = $row['folder_id'];
        
        // validate user's access to system region footer
        if (check_edit_access($folder_id) == false) {
            log_activity('access denied to edit system region footer (' . $page_name . ') because user does not have access to modify folder that page is in', $_SESSION['sessionusername']);
            output_error('Access denied.');
        }

        // update the info for the system region footer and its page
        $query =
            "UPDATE page
            SET 
                system_region_footer = '" . escape($liveform->get_field_value('region_content')) . "',
                page_user = '" . $user['id'] . "',
                page_timestamp = UNIX_TIMESTAMP()
            WHERE page_id = '" . escape($liveform->get_field_value('region_id')) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        break;
}

// if this region is not a system region header or system region footer,
// then update the page properties.  We have already updated the page for the
// system region header and footer.
if (
    ($liveform->get_field_value('region_type') != 'system_region_header')
    && ($liveform->get_field_value('region_type') != 'system_region_footer')
) {
    $query =
        "UPDATE page
        SET
            $sql_seo_analysis_current
            page_user = '" . $user['id'] . "',
            page_timestamp = UNIX_TIMESTAMP()
        WHERE page_id = '" . escape($page_id) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
}

// log activity
log_activity("page ($page_name) was modified", $_SESSION['sessionusername']);

if ($liveform->get_field_value('inline') != 'true') {
    // forward user to the last page they were on
    header('Location: ' . URL_SCHEME . HOSTNAME . $liveform->get_field_value('send_to'));
}

$liveform->remove_form();
?>