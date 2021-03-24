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

// if there has not been a post, then continue to output the page
if (!$_POST) {
    // get file data from databse
    $query = "SELECT name FROM files WHERE id = '" . escape($_REQUEST['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    $file_name = $row['name'];
    
    $output_body_onload_event = '';
    
    $output_header = output_header();
    
    // get the file contents from the CSS file
    $code = file_get_contents(FILE_DIRECTORY_PATH . '/' . $file_name);
    
    // output the page
    print
        $output_header . '
        <div id="subnav">
            <h1 style="margin-bottom: .25em">' . h($file_name) . '</h1>
        </div>
        <div id="content">
            
            <a href="#" id="help_link">Help</a>
            <h1>Edit CSS</h1>
            <div class="subheading" style="margin-bottom: 1.5em">You are editing this CSS file.</div>
            <form action="edit_theme_css.php" method="post">
                ' . get_token_field() . '
                <input type="hidden" name="id" value="' . h($_REQUEST['id']) . '">
                <input type="hidden" name="name" value="' . h($file_name) . '">
                <input type="hidden" name="send_to" value="' . h($_REQUEST['send_to']) . '">
                <input type="hidden" name="from" value="' . h($_REQUEST['from']) . '">
                <div id="edit_custom" style="margin-bottom: 1.5em">
                    <textarea name="code" id="code" rows="25" cols="60" wrap="off">' . h($code) . '</textarea>
                    ' . get_codemirror_includes() . '
                    ' . get_codemirror_javascript(array('id' => 'code', 'code_type' => 'css')) . '
                </div>
                <div class="buttons">
                    <input type="submit" name="submit_save_and_return" value="Save & Return" class="submit-primary" />&nbsp;&nbsp;&nbsp;<input type="submit" name="submit_save" value="Save" class="submit-secondary">&nbsp;&nbsp;&nbsp;<input type="submit" name="submit_duplicate" value="Duplicate" class="submit-secondary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="window.location=\'' . h(escape_javascript(URL_SCHEME . HOSTNAME . $_REQUEST['send_to'])) . '\'" class="submit-secondary">
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
                description,
                theme
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
                theme,
                user,
                timestamp)
            VALUES (
                '" . escape($new_file_name) . "',
                '" . escape($row['folder']) . "',
                '" . escape($row['description']) . "',
                '" . escape($file_extension) . "',
                '" . escape(filesize(FILE_DIRECTORY_PATH . '/' . $new_file_name)) . "',
                '1',
                '" . $row['theme'] . "',
                '$user[id]',
                UNIX_TIMESTAMP())";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $new_theme_id = mysqli_insert_id(db::$con);

        // Duplicate preview style records.

        $preview_styles = db_items(
            "SELECT
                page_id,
                style_id,
                device_type
            FROM preview_styles
            WHERE theme_id = '" . escape($_POST['id']) . "'");
        
        foreach ($preview_styles as $preview_style) {
            db(
                "INSERT INTO preview_styles (
                    page_id,
                    theme_id,
                    style_id,
                    device_type)
                VALUES (
                    '" . $preview_style['page_id'] . "',
                    '" . $new_theme_id . "',
                    '" . $preview_style['style_id'] . "',
                    '" . $preview_style['device_type'] . "')");
        }
        
        log_activity("a new copy of the CSS file (" . $_POST['name'] . ") was created", $_SESSION['sessionusername']);

        // if the user came from a front-end page, then set from to edit_theme_file
        if ($_POST['from'] == '') {
            $_POST['from'] = 'edit_theme_file';
        }
        
        include_once('liveform.class.php');
        $liveform = new liveform($_POST['from']);
        $liveform->add_notice('A new copy of the CSS file has been created.');

        // Send the user to a screen that will reload the theme so that it will clear the user's cache
        // so that the user does not view an old version of the theme. Even though we are saving a new theme
        // with a new name, we still do this in case the new name was used in the past and the user has a cache of it.
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/reload_theme.php?name=' . urlencode($new_file_name) . '&send_to=' . urlencode(PATH . SOFTWARE_DIRECTORY . '/' . $_POST['from'] . '.php?id=' . $new_theme_id));
        exit();
    
    // else, save the file
    } else {
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
        
        // log the change and add a notice that the file was modified
        log_activity("the css for theme file (" . $_POST['name'] . ") was modified", $_SESSION['sessionusername']);

        // if the user came from the edit theme file or edit design file screens, then add notice
        if (
            ($_POST['from'] == 'edit_theme_file')
            || ($_POST['from'] == 'edit_design_file')
        ) {
            include_once('liveform.class.php');
            $liveform = new liveform($_POST['from']);
            $liveform->add_notice('The CSS file was edited successfully.');
        }

        if ($_POST['submit_save'] == 'Save') {
            // send the user back to reload this screen again
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/edit_theme_css.php?id=' . urlencode($_POST['id']) . '&send_to=' . urlencode($_POST['send_to']));
            exit();
        } else {
            // send the user to a screen that will reload the theme so that it will clear the user's cache
            // so that the user does not view an old version of the theme
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/reload_theme.php?name=' . urlencode($_POST['name']) . '&send_to=' . urlencode($_POST['send_to']));
            exit();
        }
    }
}
?>
