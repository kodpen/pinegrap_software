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

$custom_form_fields = array();

// Get folder id for custom form page in order to verify that user has edit access to custom form.
$folder_id = db_value("SELECT page_folder FROM page WHERE page_id = '" . escape($_GET['page_id']) . "'");

// If custom form page exists and user has edit access to custom form, then continue to get fields.
if (($folder_id) && (check_edit_access($folder_id) == true)) {
    $custom_form_fields = db_items(
        "SELECT
            id,
            name,
            type
        FROM form_fields
        WHERE
            (page_id = '" . escape($_GET['page_id']) . "')
            AND (type != 'information')
            AND (type != 'file upload')
            AND (name != '')
        ORDER BY sort_order ASC");
}

echo encode_json($custom_form_fields);
?>