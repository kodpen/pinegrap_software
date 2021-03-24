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
validate_area_access($user, 'designer');

// if there has not been a post then continue to output the page
if (!$_POST) {
    // get file data from databse
    $query = "SELECT name FROM files WHERE id = '" . escape($_REQUEST['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    $file_name = $row['name'];
    
    // get the file contents from the script file
    $code = file_get_contents(FILE_DIRECTORY_PATH . '/' . $file_name);
    
    // output the page
    print
        output_header() . '
        <div id="subnav">
            <h1 style="margin-bottom: .25em">' . h($file_name) . '</h1>
        </div>
        <div id="content">
            
            <a href="#" id="help_link">Help</a>
            <h1>Edit JavaScript</h1>
            <div class="subheading" style="margin-bottom: 1.5em">You are editing the JavaScript for this File.</div>
            <form action="edit_javascript.php" method="post">
                ' . get_token_field() . '
                <input type="hidden" name="id" value="' . h($_REQUEST['id']) . '">
                <input type="hidden" name="name" value="' . h($file_name) . '">
                <div id="edit_custom" style="margin-bottom: 1.5em">
                    <textarea name="code" id="code" rows="25" cols="60" wrap="off">' . h($code) . '</textarea>
                    ' . get_codemirror_includes() . '
                    ' . get_codemirror_javascript(array('id' => 'code', 'code_type' => 'javascript')) . '
                </div>
                <div class="buttons">
                    <input type="submit" name="submit_save_and_return" value="Save & Return" class="submit-primary" />&nbsp;&nbsp;&nbsp;<input type="submit" name="submit_save" value="Save" class="submit-secondary" />&nbsp;&nbsp;&nbsp;<input type="submit" name="submit_duplicate" value="Duplicate" class="submit-secondary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary">
                </div>
            </form>
        </div>
        ' . output_footer();

// else process the file
} else {
    validate_token_field();
    
    // if save new copy was selected, then save a new copy of the file
    if ($_POST['submit_duplicate'] == 'Duplicate') {
        $new_file_name = prepare_file_name($_POST['name']);

        $new_file_name = get_unique_name(array(
            'name' => $new_file_name,
            'type' => 'file'));

        // Get the position of the last period in order to get the extension.
        $position_of_last_period = mb_strrpos($new_file_name, '.');

        $file_extension = '';
        
        // If there is an extension then remember it.
        if ($position_of_last_period !== false) {
            $file_extension = mb_substr($new_file_name, $position_of_last_period + 1);
        }
        
        // save the file
        $handle = fopen(FILE_DIRECTORY_PATH . '/' . $new_file_name, 'w');
        fwrite($handle, $_POST['code']);
        fclose($handle);
        
        // get data from the file we are duplicating
        $query =
            "SELECT
                folder,
                description
            FROM files
            WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        
        // insert duplicated file's data into files table
        $query =
            "INSERT INTO files (
                name,
                folder,
                description,
                type,
                size,
                design,
                user,
                timestamp)
            VALUES (
                '" . escape($new_file_name) . "',
                '" . escape($row['folder']) . "',
                '" . escape($row['description']) . "',
                '" . escape($file_extension) . "',
                '" . escape(filesize(FILE_DIRECTORY_PATH . '/' . $new_file_name)) . "',
                '1',
                '$user[id]',
                UNIX_TIMESTAMP())";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $new_file_id = mysqli_insert_id(db::$con);
        
        log_activity("a new copy of the file (" . $_POST['name'] . ") was created", $_SESSION['sessionusername']);
        
        // send user to edit the newly duplicated javascript file
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/edit_design_file.php?id=' . $new_file_id);
    
    // else, save the file
    } else {
        include_once('liveform.class.php');
        
        // delete the existing file. we have to do this in order to avoid permission errors in certain cirumstances
        unlink(FILE_DIRECTORY_PATH . '/' . $_POST['name']);
        
        // update the content in the file
        $handle = fopen(FILE_DIRECTORY_PATH . '/' . $_POST['name'], 'w');
        fwrite($handle, $_POST['code']);
        fclose($handle);
        
        // update file in database
        $query =
            "UPDATE files 
            SET 
                size = '" . escape(filesize(FILE_DIRECTORY_PATH . '/' . $_POST['name'])) . "',
                timestamp = UNIX_TIMESTAMP(), 
                user = '" . $user['id'] . "' 
            WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // log the change that the file was modified
        log_activity("the JavaScript for file (" . $_POST['name'] . ") was modified", $_SESSION['sessionusername']);
        
        if ($_POST['submit_save'] == 'Save') {
            // send the user back to reload this screen again
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/edit_javascript.php?id=' . urlencode($_POST['id']));
            exit();
        } else {
            // send user back to edit design file screen
            $liveform = new liveform('edit_design_file');
            $liveform->add_notice('The JavaScript file was edited successfully.');
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/edit_design_file.php?id=' . urlencode($_POST['id']));
            exit();
        }
    }
}
?>
