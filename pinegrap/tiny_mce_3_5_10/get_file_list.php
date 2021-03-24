<?php
include('../init.php');

if ($_SESSION['sessionusername']) {
    $user = validate_user();
    
    $folders_that_user_has_access_to = array();

    // if user is a basic user, then get folders that user has access to
    if ($user['role'] == 3) {
        $folders_that_user_has_access_to = get_folders_that_user_has_access_to($user['id']);
    }
    
    $output_js = "var tinyMCEFilesList = new Array(
        // Name, URL\r\n";

    $output_js .= '["---", ""]';
    
    // get file list
    $query =
        "SELECT
            files.name,
            files.folder
        FROM files
        LEFT JOIN folder ON files.folder = folder.folder_id
        WHERE
            (files.design = 0)
            AND (files.attachment = 0)
            AND (folder.folder_archived = '0')
        ORDER BY files.name";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    while ($row = mysqli_fetch_assoc($result)) {
        if (check_folder_access_in_array($row['folder'], $folders_that_user_has_access_to) == true) {
            $output_js .= ', ' . "\r\n" . '["' . escape($row['name']) . '", "' . escape_javascript(PATH) . escape($row['name']) . '"]';
        }
    }

    $output_js .= "\r\n );";

    print $output_js;
}
?>