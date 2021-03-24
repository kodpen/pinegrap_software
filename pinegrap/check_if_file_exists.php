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

// this script is called by Javascript (via AJAX) to determine if the file name for a file, is already in use
// it returns a response in XML format
include('init.php');
$user = validate_user();
validate_area_access($user, 'user');

$file_name = prepare_file_name($_GET['file_name']);

// check if there is already a file with the file name
$query = "SELECT id, design, folder
         FROM files
         WHERE name = '" . escape($file_name) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

// if file name is not in use
if (mysqli_num_rows($result) == 0) {
    $response = 'upload';
    
// else file name is in use
} else {
    $row = mysqli_fetch_assoc($result);
    
    // If user is designer or greater
    if ($user['role'] < 2) {
        
        // Allow overwrite
        $response = 'overwrite';
        
    // Else if the user is a manager or below.
    } else {
        
        // if user has access to existing file, then user has permission to overwrite file
        if ((check_edit_access($row['folder']) == true) && ($row['design'] == 0)) {
            $response = 'overwrite';
            
        // else user does not have access to existing file
        } else {
            $response = 'access denied';
        }
    }
}

header("Content-type: text/xml");
print
'<?xml version="1.0" encoding="utf-8" ?>
<response>' . $response . '</response>';
?>