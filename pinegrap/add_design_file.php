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

if (!$_POST) {
    $max_file_uploads = ini_get('max_file_uploads');

    $max_number_of_files = 0;

    if (is_numeric($max_file_uploads) == true) {
        $max_number_of_files = $max_file_uploads;
    }

    $output_maxfiles = '';

    if ($max_number_of_files > 0) {
        $output_maxfiles = 'maxFiles: ' . $max_number_of_files . ',';
    }

    echo
        output_header() .'
        <div id="subnav">
            <h1>[new design files]</h1>
        </div>
        <div id="content">
            
            <a href="#" id="help_link">Help</a>
            <h1>Upload Design Files</h1>
            <div class="subheading" style="margin-bottom: 2em">Drop design files below, update the settings (if necessary), and then click Upload.</div>
            <form enctype="multipart/form-data" action="add_design_file.php" method="post" id="dropzone" class="dropzone">
                ' . get_token_field() . '
                <div class="dropzone_max_files_warning">
                    Warning: You have reached the limit of the number of files your server configuration allows you to upload at the same time (' . h(number_format($max_number_of_files)) . ').  Your server administrator can increase the max_file_uploads setting in PHP in order to increase the limit.  You may click Upload now in order to upload the files you added before you reached the limit.
                </div>
                <div class="dropzone_previews">
                    <div class="dropzone_help">Drop files here or click to browse</div>
                </div>
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>Design File Access Control</h2></th>
                    </tr>
                    <tr>
                        <td>Folder:</td>
                        <td><select name="folder">' . select_folder($_GET['id'], 0) . '</select></td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Design File Description</h2></th>
                    </tr>
                    <tr>
                        <td style="vertical-align: top">File Description:</td>
                        <td><textarea rows="3" cols="50" name="description"></textarea></td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" id="submit" value="Upload" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary">
                </div>
                <div class="fallback" style="margin-top: 2em; margin-bottom: 2em">
                    <div style="font-weight: bold; font-size: 120%; margin-bottom: 1em">It appears that you are using an older browser that does not support dragging &amp; dropping files to upload them.  Please use the field below to select one or more files and then click the upload button above.</div>
                    <input type="hidden" name="fallback" value="true" />
                    Select File: <input name="file[]" type="file" size="60" multiple="multiple">
                </div>
            </form>
            <link rel="stylesheet" type="text/css" href="dropzone/dropzone.' . ENVIRONMENT_SUFFIX . '.css" />
            <script src="dropzone/dropzone.' . ENVIRONMENT_SUFFIX . '.js"></script>
            <script>
                Dropzone.options.dropzone = {
                    autoProcessQueue: false,
                    uploadMultiple: true,
                    // Had to set high value for parallelUploads in order for
                    // large number of uploads to work (overrides default which is 2).
                    parallelUploads: 9999,
                    // Had to set high value for maxFilesize in order to override
                    // the default of 256 MB (value is in MB).
                    maxFilesize: 9999,
                    previewsContainer: ".dropzone_previews",
                    clickable: ".dropzone_previews",
                    ' . $output_maxfiles . '

                    init: function() {
                        var myDropzone = this;

                        // First change the button to actually tell Dropzone to process the queue.
                        this.element.querySelector("input[type=submit]").addEventListener("click", function(e) {
                          // Make sure that the form isnt actually being sent.
                          e.preventDefault();
                          e.stopPropagation();
                          myDropzone.processQueue();
                        });

                        this.on("sendingmultiple", function() {
                            $("#submit").prop("disabled", true);
                            $("#submit").addClass("disabled");
                            $("#submit").val("Uploading...");
                        });

                        this.on("successmultiple", function(files, response) {
                            var send_to = $("#send_to").val();

                            if (send_to) {
                                window.location = send_to;
                            } else {
                                window.location = "view_design_files.php";
                            }
                            
                        });
                        this.on("errormultiple", function(files, response) {
                            alert("Sorry, the files could not be uploaded.  Please refresh and try again.");
                        });
                    }
                }
            </script>
        </div>' .
        output_footer();

} else {
    validate_token_field();

    // If the user didn't select a file then output error.
    if ($_FILES['file']['name'][0] == '') {
        output_error('Please select a file. <a href="javascript:history.go(-1)">Go back</a>.');
    }

    foreach ($_FILES['file']['name'] as $index => $file_name) {
        $file_name = prepare_file_name($file_name);

        $file_name = get_unique_name(array(
            'name' => $file_name,
            'type' => 'file'));

        $array_file_extension = explode('.', $file_name);
        $size_of_array = count($array_file_extension);
        $file_extension = $array_file_extension[$size_of_array - 1];

        copy($_FILES['file']['tmp_name'][$index], FILE_DIRECTORY_PATH . '/' . $file_name);

        db(
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
                '" . escape($file_name) . "',
                '" . escape($_POST['folder']) . "',
                '" . escape($_POST['description']) . "',
                '" . escape($file_extension) . "',
                '" . escape($_FILES['file']['size'][$index]) . "',
                '1',
                '" . USER_ID . "',
                UNIX_TIMESTAMP())");

        log_activity("design file ($file_name) was created", $_SESSION['sessionusername']);
    }

    include_once('liveform.class.php');

    $liveform_view_design_files = new liveform('view_design_files');

    $number_of_uploaded_files = count($_FILES['file']['name']);

    // If one file was uploaded, then prepare notice for that.
    if ($number_of_uploaded_files == 1) {
        $liveform_view_design_files->add_notice('The design file, <a href="' . OUTPUT_PATH . h($file_name) . '" target="_blank">' . h($file_name) . '</a>, has been uploaded.');

    // Otherwise more than one file was uploaded, so prepare different notice for that.
    } else {
        $liveform_view_design_files->add_notice(number_format($number_of_uploaded_files) . ' design files have been uploaded.');
    }

    // If visitor has an old browser and did not use drag-and-drop,
    // then forward visitor to next scren.  Drag-and-drop uses AJAX post
    // to this script, so we don't need to forward visitor anywhere in that case.
    if ($_POST['fallback'] == 'true') {
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_design_files.php');
    }
}
?>
