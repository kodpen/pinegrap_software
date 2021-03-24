<?php
include ('../../../init.php');
$user = validate_user();

validate_token_field();

$file_name = prepare_file_name($_FILES['file']['name']);

// if user did not select a file or file was empty, then output error
if ($_FILES['file']['error'] > 0 || $_FILES['file']['size'] == 0){
    output_error('Please select a file. <a href="javascript:history.go(-1);">Go back</a>.');
}

// if file name is invalid, output error
if ($file_name == '.htaccess') {
    output_error('File name is invalid. <a href="javascript:history.go(-1)">Go back</a>.');
}

// check that user has access to place file in selected folder
if (check_edit_access($_POST['folder_id']) == false) {
    log_activity("access denied to upload file into folder", $_SESSION['sessionusername']);
    output_error('Access denied. <a href="javascript:history.go(-1)">Go back</a>.');
}

// find out if file name is already in use.
$query = "SELECT id, folder, design
         FROM files
         WHERE name = '" . escape($file_name) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

// if file name is already in use
if (mysqli_num_rows($result) > 0) {
    // if user requested to overwrite file
    if ($_POST['overwrite'] == 'true') {
        // get existing file id and folder id
        $row = mysqli_fetch_assoc($result);
        $existing_file_id = $row['id'];
        $existing_folder_id = $row['folder'];
        
        // If user is admin or below and the file is a design file
        if (($user['role'] >= 2) && ($row['design'] == 1)) {
            output_error('You do not have access to overwrite the file. <a href="javascript:history.go(-1)">Go back</a>.');
        }
        // if user has access to overwrite existing file, delete existing record for file in database and delete existing file
        if (check_edit_access($existing_folder_id) == true) {
            $query = "DELETE FROM files WHERE id = '$existing_file_id'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

            // delete file's system css properties in case any exist
            $query = "DELETE FROM system_theme_css_rules WHERE file_id = '" . $existing_file_id . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

            db("DELETE FROM preview_styles WHERE theme_id = '" . escape($existing_file_id) . "'");
            
            unlink(FILE_DIRECTORY_PATH . '/' . $file_name) or output_error('The existing file could not be deleted.');
            
            $overwrite = true;
        }
        
    // else user did not request to overwrite file, so output error
    } else {
        output_error(h($file_name) . ' already exists. <a href="javascript:history.go(-1)">Go back</a>.');
    }
}

$file_type = $_FILES['file']['type'];
$file_size = $_FILES['file']['size'];
$file_temp_name = $_FILES['file']['tmp_name'];

// get file extension
$array_file_extension = explode('.', $file_name);
$size_of_array = count($array_file_extension);
$file_extension = $array_file_extension[$size_of_array - 1];

// create file
copy($file_temp_name, FILE_DIRECTORY_PATH . '/' . $file_name) or output_error('Upload failed. <a href="javascript:history.go(-1);">Go back</a>.');

// insert file data into file table
$query =    "INSERT INTO files ".
            "(name, folder, type, size, user, timestamp, design) ".
            "VALUES ('" . escape($file_name) . "','" . escape($_POST['folder_id']) . "','" . escape($file_extension) . "','" . escape($file_size) . "','" . $user['id'] . "',UNIX_TIMESTAMP(), '0')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$file_id = mysqli_insert_id(db::$con);

if ($overwrite == true) {
    log_activity("file ($file_name) was overwritten", $_SESSION['sessionusername']);
} else {
    log_activity("file ($file_name) was created", $_SESSION['sessionusername']);
}

// send user back to insert link page
header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/tiny_mce_3_5_10/plugins/advimage/image.htm?page_id=' . $_POST['page_id'] . '&file_id=' . $file_id . '&file_url=' . PATH . $file_name);
?>