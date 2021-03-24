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

// Increase limits so image optimization can run for a while.
ini_set('max_execution_time', '9999');
ini_set('memory_limit', '-1');

include('init.php');

include_once('liveform.class.php');
$liveform = new liveform($_POST['from']);
$user = validate_user();
validate_area_access($user, 'user');

// if the form has not been submitted yet, then output form
if (!$_POST) {
    $output_design_row = '';

    // if this user is a designer or administrator, then allow the user to update the design property
    if ($user['role'] <= 1) {
        // set options for design pick list
        $design_options = array(
            '' => '',
            'Yes' => '1',
            'No' => '0'
        );

        $output_design_row =
            '<tr>
                <td>Design:</td>
                <td>
                    ' . $liveform->output_field(array('type'=>'select', 'id'=>'design', 'options'=>$design_options)) . '
                </td>
            </tr>';
    }

    echo
        '<!DOCTYPE html>
        <html lang="en">
            <head>
                <meta charset="utf-8">
                <title>Modify Files</title>
                ' . get_generator_meta_tag() . '
                ' . output_control_panel_header_includes() . '
                <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
                <script type="text/javascript">
                    function edit_files()
                    {
                        // if there is a value then update field in the form
                        if (document.getElementById("folder").value != "") {
                            opener.document.form.move_to_folder.value = document.getElementById("folder").value;
                        }
                        
                        // if the design field exists and there is a value then update field in the form
                        if (
                            (document.getElementById("design"))
                            && (document.getElementById("design").value != "")
                        ) {
                            opener.document.form.edit_design.value = document.getElementById("design").value;
                        }

                        if (document.getElementById("optimize").checked) {

                            var number_of_files = opener.$(\'input[name="files[]"]:checked\').length;

                            if (number_of_files > 20) {
                                alert("Sorry, we were not able to modify the files. You may optimize a maximum of 20 files at a time. " + number_of_files + " files are selected. You may deselect some files and try again, or you may start over and select a maximum of 20 files.");
                                return false;
                            }

                            opener.document.form.optimize.value = 1;

                            opener.scrollTo(0, 0);

                            opener.$("#content").prepend(\'\
                                <div class="software_notice">\
                                    <h1>Optimizing images...</h1>\
                                    <div><img style="margin-top: .5em;" src="images/icon_processing.gif"></div>\
                                    <h3>Please stay on this page until this process is complete.</h3>\
                                    <div>This process might take several minutes if many images are being optimized.</div>\
                                </div>\');

                            // Add delay before submitting form in parent window so loading image
                            // has time to load.
                            setTimeout (function () {
                                opener.document.form.submit();
                                window.close();
                            }, 500);

                        } else {
                            opener.document.form.submit();
                            window.close();
                        }

                    }
                </script>
            </head>
            <body class="files" style="overflow:auto;">
                <div class="navigation fixed">
                    <div class="title" title="You may update the selected Files via the form below. You may leave an option unselected if you do not want to modify a property.">Modify Files
                    </div>

                    <ul class="right">
                        <li><a href="#!" class="red-text" onclick="window.close()" ><i class="material-icons">close</i></a></li>
                    </ul>
                </div>
                <div id="content">
                    <table class="field">
                        <tr>
                            <td>Folder:</td>
                            <td><select id="folder"><option value=""></option>' . select_folder() . '</select></td>
                        </tr>
                        ' . $output_design_row . '
                        <tr>
                            <td><label for="optimize">Optimize Images:</label></td>
                            <td><input type="checkbox" id="optimize" value="1" class="checkbox"></td>
                        </tr>
                    </table>
                    <div class="buttons">
                        <input type="button" value="Modify Files" class="submit-primary" onclick="edit_files()" />
                    </div>
                </div>
            </body>
        </html>';


// else the form has been submitted, so process it
} else {
    validate_token_field();
    
    // if at least one file was selected then continue
    if ($_POST['files']) {

        $number_of_files = 0;
        $number_of_images = 0;
        
        switch ($_POST['action']) {
            // if files are being edited, proceed
            case 'edit':
                // if at least one action was selected, then continue
                if (
                    ($_POST['move_to_folder'])
                    ||
                    (
                        ($_POST['edit_design'] != '')
                        && ($user['role'] <= 1)
                    )
                    or $_POST['optimize']
                ) {
                    // if a folder was selected to move the file(s) to
                    // and if user does not have access to the folder that he/she is trying to move files to, output error
                    if (($_POST['move_to_folder']) && (check_edit_access($_POST['move_to_folder']) == false)) {
                        output_error('You do not have access to move files to the folder that you selected. <a href="javascript:history.go(-1);">Go back</a>.');
                    }

                    if ($_POST['optimize']) {
                        require(dirname(__FILE__) . '/optimize_image.php');
                    }
                    
                    // loop through each file and process actions
                    foreach ($_POST['files'] as $file_id) {

                        $file = db_item(
                            "SELECT
                                id,
                                design,
                                folder AS folder_id,
                                type,
                                optimized
                            FROM files
                            WHERE id = '" . e($file_id) . "'");

                        $design = $file['design'];
                        $folder_id = $file['folder_id'];

                        // if the user does not have edit rights to this file's folder,
                        // or this file is a design file and the user is not a designer or administrator,
                        // then log activity and output error
                        if (
                            (check_edit_access($folder_id) == false)
                            ||
                            (
                                ($design == 1)
                                && ($user['role'] > 1)
                            )
                        ) {
                            log_activity('access denied to modify files because user does not have access to file', $_SESSION['sessionusername']);
                            output_error('You do not have access to modify a file that you selected. <a href="javascript:history.go(-1);">Go back</a>.');
                        }

                        $sql_folder = "";

                        // if a folder was selected to move the file(s) to, then move file
                        if ($_POST['move_to_folder']) {
                            $sql_folder = "folder = '" . escape($_POST['move_to_folder']) . "',";
                        }

                        $sql_design = "";

                        // if design was selected to be edited and the user is a designer or administrator,
                        // then update design property
                        if (
                            ($_POST['edit_design'] != '')
                            && ($user['role'] <= 1)
                        ) {
                            $sql_design = "design = '" . escape($_POST['edit_design']) . "',";
                        }

                        if ($sql_folder or $sql_design) {

                            db(
                                "UPDATE files
                                SET
                                    " . $sql_folder . "
                                    " . $sql_design . "
                                    timestamp = UNIX_TIMESTAMP(),
                                    user = '" . $user['id'] . "'
                                WHERE id = '" . e($file_id) . "'");

                            $number_of_files++;

                        }

                        $file['type'] = mb_strtolower($file['type']);

                        // If this file is an image that we should optimize, then do that.
                        if (
                            $_POST['optimize']
                            and !$file['optimized']
                            and (
                                ($file['type'] == 'jpg')
                                or ($file['type'] == 'jpeg')
                                or ($file['type'] == 'png')
                                or ($file['type'] == 'gif')
                                or ($file['type'] == 'bmp')
                                or ($file['type'] == 'tiff')
                            )
                        ) {

                            $response = optimize_image($file['id']);

                            if ($response['status'] == 'success') {
                                $liveform->add_notice(h($response['message']));
                                $number_of_images++;
                            } else  {
                                $liveform->mark_error($file['id'], h($response['message']));
                            }

                        }

                    }
                    
                    // if more than 0 files were modified, then log activity
                    if ($number_of_files) {
                        $log_message = '';
                        
                        // if a folder was selected to move the files(s) to, then output message for log
                        if ($_POST['move_to_folder']) {
                            // get folder name for log
                            $query = "SELECT folder_name FROM folder WHERE folder_id = '" . escape($_POST['move_to_folder']) . "'";
                            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                            $row = mysqli_fetch_assoc($result);
                            
                            // output message for log
                            $log_message = "were moved to " . $row['folder_name'];
                        }
                        
                        // if the design property was selected to be edited and the user is a design or administrator,
                        // then set message for log
                        if (
                            ($_POST['edit_design'] != '')
                            && ($user['role'] <= 1)
                        ) {
                            // if the log message is not blank, then add separator
                            if ($log_message != '') {
                                $log_message .= ', and ';
                            }
                            
                            // prepare design value for log
                            if ($_POST['edit_design'] == '1') {
                                $on_off = 'on';
                            } else {
                                $on_off = 'off';
                            }
                            
                            // output message for log
                            $log_message .= 'had design turned ' . $on_off;
                        }
                        
                        // if there is a log message, then log it and add notice
                        if ($log_message != '') {
                            log_activity($number_of_files . ' file(s) ' . $log_message, $_SESSION['sessionusername']);
                            $liveform->add_notice($number_of_files . ' file(s) ' . h($log_message) . '.');
                        }
                    }

                    if ($number_of_images) {

                        if ($number_of_images > 1) {
                            $message = $number_of_images . ' images were optimized';
                        } else {
                            $message = '1 image was optimized';
                        }

                        log_activity($message);

                        $liveform->add_notice($message . '.');

                    }

                }
                
                break;

            // if files are being deleted
            case 'delete':
                foreach ($_POST['files'] as $file_id) {
                    // get properties for file, in order to validate access
                    $query =
                        "SELECT
                            name,
                            design,
                            folder AS folder_id
                        FROM files
                        WHERE id = '" . escape($file_id) . "'";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    $row = mysqli_fetch_assoc($result);

                    $name = $row['name'];
                    $design = $row['design'];
                    $folder_id = $row['folder_id'];

                    // if the user does not have edit rights to this file's folder,
                    // or this file is a design file and the user is not a designer or administrator,
                    // then log activity and output error
                    if (
                        (check_edit_access($folder_id) == false)
                        ||
                        (
                            ($design == 1)
                            && ($user['role'] > 1)
                        )
                    ) {
                        log_activity('access denied to delete files because user does not have access to file', $_SESSION['sessionusername']);
                        output_error('You do not have access to delete the files you selected. <a href="javascript:history.go(-1);">Go back</a>.');
                    }

                    $query = "DELETE FROM files WHERE id = '" . escape($file_id) . "'";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                    // delete file's system css properties in case any exist
                    $query = "DELETE FROM system_theme_css_rules WHERE file_id = '" . escape($file_id) . "'";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

                    db("DELETE FROM preview_styles WHERE theme_id = '" . escape($file_id) . "'");
                    
                    // delete file on filesystem
                    @unlink(FILE_DIRECTORY_PATH . '/' . $name);

                    $number_of_files++;
                }

                // if more than 0 files were deleted, then log activity
                if ($number_of_files > 0) {
                    log_activity($number_of_files . ' file(s) were deleted', $_SESSION['sessionusername']);
                    $liveform->add_notice($number_of_files . ' file(s) were deleted.');
                }
                
                break;
        }
    }

    // If there is a send to value then send user back to that screen
    if (isset($_POST['send_to']) == TRUE) {
        header('Location: ' . URL_SCHEME . HOSTNAME . $_POST['send_to']);
        
    // else send user to the default view
    } else {
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/' . $_POST['from'] . '.php');
    }
}