<?php
include('../init.php');

if ($_SESSION['sessionusername']) {
    $user = validate_user();
    
    $folders_that_user_has_access_to = array();

    // if user is a basic user, then get folders that user has access to
    if ($user['role'] == 3) {
        $folders_that_user_has_access_to = get_folders_that_user_has_access_to($user['id']);
    }
    
    $output_js = "var tinyMCELinkList = new Array(
        // Name, URL\r\n";

    $output_js .= '["---", ""]';
    
    // get page list
    $query =
        "SELECT
            page.page_name,
            page.page_folder
        FROM page
        LEFT JOIN folder ON page.page_folder = folder.folder_id
        WHERE folder.folder_archived = '0'
        ORDER BY page.page_name";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    while ($row = mysqli_fetch_assoc($result)) {
        if (check_folder_access_in_array($row['page_folder'], $folders_that_user_has_access_to) == true) {
            $output_js .= ', ' . "\r\n" . '["' . escape($row['page_name']) . '", "' . escape_javascript(PATH) . escape($row['page_name']) . '"]';
        }
    }

    $output_js .= ');';

    print $output_js;
}
?>