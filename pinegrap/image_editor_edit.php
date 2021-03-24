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

// init varibles to be used in the output
$file_name = $_GET['file_name'];
$send_to = $_GET['send_to'];
$object_type = $_GET['object_type'];
$object_id = $_GET['object_id'];


// if the form has not been submitted
if (!$_POST) {

    // get file info
    $query =
    "SELECT
        name,
        folder,
        id,
        design,
        type
    FROM files
    WHERE name = '" . escape($file_name) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    $file_name_extension = $row['type'];
    $original_file_name = $row['name'];
    $file_id = $row['id'];
    $folder_id = $row['folder'];
    $design = $row['design'];
    $type = $row['type'];


    // if the file does not exist, then output error
    if (mysqli_num_rows($result) == 0) {
        output_error('The image cannot be updated because it no longer exists. <a href="javascript:history.go(-1)">Go back</a>.');
    }

    // set the output format to the same format as the current image (use the extension to determine this)
    $output_format = '';
    switch($file_name_extension) {
       case 'jpg':
       case 'jpeg':
           $output_format = 'jpg';
           break;
       case 'png':
           $output_format = 'png';
           break;
       case 'gif':
           $output_format = 'png';
           break;
       default:
           $output_format = 'jpg';
           break;
    }
    // if user does not have access to edit this file, or if it is a design file, then output error
    if (($user['role'] == 3) && ((check_edit_access($folder_id) == false) || ($file_design == 1))) {
        log_activity("access denied to edit image with Image Editor because user does not have access to edit image", $_SESSION['sessionusername']);
        output_error('Access denied. <a href="javascript:history.go(-1)">Go back</a>.');
    }

    $column_to_update = '';

    // if there is a column specified to update, then prepare it for the link
    if ($_GET['column_to_update'] != '') {
        $column_to_update = '&column_to_update=' . h(urlencode($_GET['column_to_update']));
    }

    $software_title = '';

    if (PRIVATE_LABEL == FALSE) {
        $software_title = 'Pinegrap - ';
    }

    // if CDN is enabled, then use Google CDN for Fabric/darkroom files for performance reasons
    if (
        (defined('CDN') == FALSE)
        || (CDN == TRUE)
    ) {
        $output_fabrics =
            '<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/1.5.0/fabric.min.js"></script>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/darkroomjs/2.0.1/darkroom.js"></script>';

    // else CDN is disabled, so use local Fabric/darkroom files
    } else {
        $output_fabrics =
            '<script src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/backend/core/fabric.min.js"></script>
                <script src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/backend/core/darkroom.min.js"></script>';
    }
    $output_editing_photo_name ='';
    if(language_ruler()==='en'){
     $output_editing_photo_name = '<div>You\'re editing ' . h($file_name) . '...</div>';
    }else if(language_ruler()==='tr'){
     $output_editing_photo_name = '<div>' . h($file_name) . ' isimli dosyay&#305; d&uuml;zenliyorsunuz...</div>';
    }

    $image_location =  PATH . $file_name;




        echo
            output_header() . '
                <style>
                    #content {display: flex;justify-content: center;max-width:100%;overflow:auto;}
                    @media only screen and (max-width: 600px) {#content {display: inline-block;}}
                    .darkroom-container{position:relative}.darkroom-image-container{top:0;left:0}.darkroom-toolbar{display:block;position:absolute;top:-45px;left:0;background:#444;height:40px;min-width:40px;z-index:99;border-radius:2px;white-space:nowrap;padding:0 5px}.darkroom-toolbar:before{content:"";position:absolute;bottom:-7px;left:20px;width:0;height:0;border-left:7px solid transparent;border-right:7px solid transparent;border-top:7px solid #444}.darkroom-button-group{display:inline-block;margin:0;padding:0}.darkroom-button-group:last-child{border-right:none}.darkroom-button{box-sizing:border-box;background:transparent;border:none;outline:none;padding:2px 0 0 0;width:40px;height:40px}.darkroom-button:hover{cursor:pointer;background:#555}.darkroom-button:active{cursor:pointer;background:#333}.darkroom-button:disabled .darkroom-icon{fill:#666}.darkroom-button:disabled:hover{cursor:default;background:transparent}.darkroom-button.darkroom-button-active .darkroom-icon{fill:#33b5e5}.darkroom-button.darkroom-button-hidden{display:none}.darkroom-button.darkroom-button-success .darkroom-icon{fill:#99cc00}.darkroom-button.darkroom-button-warning .darkroom-icon{fill:#FFBB33}.darkroom-button.darkroom-button-danger .darkroom-icon{fill:#FF4444}.darkroom-icon{width:24px;height:24px;fill:#fff}
                </style>
                ' . $output_fabrics . '

                <div id="subnav">

                    '.$output_editing_photo_name.'
                </div>
                <div id="content" style="padding-top: 5rem;">
                    <form name="form" action="image_editor_edit.php" method="post">
                        ' . get_token_field() . '

                        <div id="dialog-confirm" style="display:none" title="Image saving options">
                            <p><span class="ui-icon ui-icon-alert" ></span>Keep Original Image or overwrite it.</p>
                        </div>

                        <input type="hidden" name="file_id" value="'.$file_id.'" />
                        <input type="hidden" name="send_to" value="'.$send_to.'" />
                        <input type="hidden" name="object_type" value="'.$object_type.'" />
                        <input type="hidden" name="object_id" value="'.$object_id.'" />
                        <input type="hidden" name="image_file" value="" />
                        <input type="hidden" name="save_option" value="" />
                        <img src="' . $image_location . '" id="image" name="image" />

                        <script>

                            var dkrm = new Darkroom("#image", {
                                // Size options
                                minWidth: 100,
                                minHeight: 100,
                                maxWidth: 600,
                                maxHeight: 500,
                            
                                backgroundColor: "#00000000",
                                // Plugins options
                                plugins: {
                                    save: {

                                        callback: function() {
                                            this.darkroom.selfDestroy();
                                            var newImage = dkrm.canvas.toDataURL();
                                            // Set your data that will be sent to reflect this change
                                            someVariable = newImage;
                                            var base64image = $("#image").attr("src");
                                            $("input[name=image_file]").val(someVariable);

                                            $( "#dialog-confirm" ).dialog({
                                                resizable: false,
                                                height: "auto",
                                                closeOnEscape: true,
                                                width: 400,
                                                modal: false,
                                                buttons: {
                                                  "Replace": function() {
                                                    $("input[name=save_option]").val("Replace");
                                                    $( this ).dialog( "close" );
                                                    $("form").submit();

                                                  },
                                                  "Keep Original And Save": function() {
                                                    $("input[name=save_option]").val("Keep and Save");
                                                    $( this ).dialog( "close" );
                                                    $("form").submit();
                                                
                                                  }
                                                }
                                            });
                                        
                                        }
                                    },
                                    crop: {
                                        quickCropKey: 67, //key "c"
                                    }
                                },
                                // Post initialize script
                                initialize: function() {
                                    $(".current-image").fadeTo( "fast" , 1);
                                    $("#instruction").css("opacity","0"); 
                                    var cropPlugin = this.plugins["crop"];
                                    //cropPlugin.requireFocus();
                                }
                            });
                        </script>
                        
                    </form>
                </div>
               ' . output_footer();

}else{

    // get parameters from image editor
    $object_type = $_POST['object_type'];
    $object_id = $_POST['object_id'];
    $file_id = $_POST['file_id'];
    $send_to = $_POST['send_to'];
    $image_data = $_POST['image_file'];
    $error = FALSE;

    // get file data from database
    $query = 
        "SELECT
            name,
            folder,
            design,
            type
        FROM files 
        WHERE id = '" . escape($file_id) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    $file_name = $row['name'];
    $original_file_name = $row['name'];
    $folder_id = $row['folder'];
    $design = $row['design'];
    $type = $row['type'];

    


    /** Start Access Control Checks **/

    // if the user has a user role, then check access
    if ($user['role'] == 3) {
        // if user does not have access to edit this file or if it is a design file, then output error
        if ((check_edit_access($folder_id) == false) || ($design == 1)) {
            log_activity("access denied to save image from ImageEditor because user does not have access to edit image", $_SESSION['sessionusername']);
            $error = TRUE;
        }

        // do access control for various object types
        switch ($object_type) {
            case 'ad':
                // get this ad's name and ad region id
                $query = "SELECT name, ad_region_id FROM ads WHERE id = '" . escape($object_id) . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                $row = mysqli_fetch_assoc($result);
                $name = $row['name'];
                $ad_region_id = $row['ad_region_id'];

                // if the user does not have access to the ad region that this ad is in, then log activity and output error
                if (in_array($ad_region_id, get_items_user_can_edit('ad_regions', $user['id'])) == FALSE) {
                    log_activity("access denied to update ad content with image from ImageEditor because user does not have access to edit ad (" . $name . ")", $_SESSION['sessionusername']);
                    $error = TRUE;
                }

                break;

            case 'pregion':
                // A user might be editing images in an inline page region that does not exist yet,
                // because region has not been saved/created yet, so that is why we add this check.
                if ($object_id) {
                    // get the folder id from the page that this pregion is on
                    $query =
                        "SELECT page.page_folder as pregion_folder_id
                        FROM pregion
                        LEFT JOIN page ON pregion.pregion_page = page.page_id
                        WHERE pregion.pregion_id = '" . escape($object_id) . "'";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    $row = mysqli_fetch_assoc($result);
                    $pregion_folder_id = $row['pregion_folder_id'];

                    // if the user does not have edit access to the pregion's folder, then log activity and output error
                    if (check_edit_access($pregion_folder_id) == false) {
                        log_activity("access denied to update page region content with image from ImageEditor because user does not have access to edit folder that page region is in", $_SESSION['sessionusername']);
                        $error = TRUE;
                    }
                }

                break;

            case 'system_region_header':
                // get the folder id from the page that this system region header is on
                $query =
                    "SELECT page_folder as system_region_header_folder_id
                    FROM page
                    WHERE page_id = '" . escape($object_id) . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                $row = mysqli_fetch_assoc($result);
                $system_region_header_folder_id = $row['system_region_header_folder_id'];

                // if the user does not have edit access to the page's folder, then log activity and output error
                if (check_edit_access($system_region_header_folder_id) == false) {
                    log_activity('access denied to update system region header content with image from ImageEditor because user does not have access to edit folder that the page is in', $_SESSION['sessionusername']);
                    $error = TRUE;
                }

                break;

            case 'system_region_footer':
                // get the folder id from the page that this system region footer is on
                $query =
                    "SELECT page_folder as system_region_footer_folder_id
                    FROM page
                    WHERE page_id = '" . escape($object_id) . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                $row = mysqli_fetch_assoc($result);
                $system_region_footer_folder_id = $row['system_region_footer_folder_id'];

                // if the user does not have edit access to the page's folder, then log activity and output error
                if (check_edit_access($system_region_footer_folder_id) == false) {
                    log_activity('access denied to update system region footer content with image from ImageEditor because user does not have access to edit folder that the page is in', $_SESSION['sessionusername']);
                    $error = TRUE;
                }

                break;
            
            case 'cregion':
                // if user does not have access to this common region, then user does not have access to edit region, so log activity and output error
                if (in_array($object_id, get_items_user_can_edit('common_regions', $user['id'])) == FALSE) {
                    log_activity("access denied to update common region content with image from ImageEditor because user does not have access to edit common region", $_SESSION['sessionusername']);
                    $error = TRUE;
                }

                break;
            
            case 'calendar_event':
                // if user does not have access to manage calendars or if they do not have access to edit this calendar event then log activity and output error
                if (($user['manage_calendars'] == FALSE) || (validate_calendar_event_access($object_id) == FALSE)) {
                    log_activity("access denied to update calendar event content with image from ImageEditor because user does not have access to edit calendar that the calendar event is in", $_SESSION['sessionusername']);
                    $error = TRUE;
                }

                break;
            
            case 'product_group':
            case 'product':
                // if user does not have access to manage ecommerce then log activity and output error
                if ($user['manage_ecommerce'] == FALSE) {
                    log_activity("access denied to update product or product group with image from ImageEditor because user does not have access to commerce", $_SESSION['sessionusername']);
                    $error = TRUE;
                }

                break;

            case 'form_field':
                // get the folder id from the page that the custom form is in
                $query =
                    "SELECT page.page_folder as form_field_folder_id
                    FROM form_fields
                    LEFT JOIN page ON form_fields.page_id = page.page_id
                    WHERE form_fields.id = '" . escape($object_id) . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                $row = mysqli_fetch_assoc($result);
                $form_field_folder_id = $row['form_field_folder_id'];

                // if the user does not have edit access to the form field's folder, then log activity and output error
                if (check_edit_access($form_field_folder_id) == false) {
                    log_activity("access denied to update form field content with image from ImageEditor because user does not have access to edit folder that custom form is in", $_SESSION['sessionusername']);
                    $error = TRUE;
                }

                break;
        }

        // if there was an error then output error
        if ($error == TRUE) {
            output_error('Access denied. <a href="javascript:history.go(-1)">Go back</a>.');
        }
    }

    // if there was an error accessing the file, then output error
    if ($image_data === FALSE) {
        log_activity('image (' . $file_name . ') could not be retrieved from Editor', $_SESSION['sessionusername']);
        output_error('We\'re sorry, we encountered a problem while retrieving your edited image from Editor. Your image was not updated on the website. Please try again later.');
    }


    

    // if the file was not a GIF, then the image needs to be replaced, so save the image over the original
    // we don't want to replace GIF's because we always save a new copy as a PNG,
    // because the image editor does not support exporting as GIF.
    if ( ($_POST['save_option'] == 'Replace') && (mb_strtolower($type) != 'gif') ) {
        
        $image_data = str_replace('data:image/png;base64,','',$image_data);
        $image_data = base64_decode($image_data);

        // delete the existing file. we have to do this in order to avoid permission errors in certain cirumstances
        unlink(FILE_DIRECTORY_PATH . '/' . $file_name);

        // save the file
        $handle = fopen(FILE_DIRECTORY_PATH . '/' . $file_name, 'w');
        fwrite($handle, $image_data);
        fclose($handle);

        // update image in database
        $query =
            "UPDATE files 
            SET 
                size = '" . escape(filesize(FILE_DIRECTORY_PATH . '/' . $file_name)) . "',
                optimized = '0',
                timestamp = UNIX_TIMESTAMP(), 
                user = '" . $user['id'] . "' 
            WHERE id = '" . escape($file_id) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        log_activity("file ($file_name) was modified via Image Editor", $_SESSION['sessionusername']);
        

    }else if( ($_POST['save_option'] == 'Keep and Save') || (mb_strtolower($type) == 'gif') ){

        $image_data = str_replace('data:image/png;base64,','',$image_data);
        $image_data = base64_decode($image_data);
        // get file name with and without file extension
        $file_name_without_extension = mb_substr($file_name, 0, mb_strrpos($file_name, '.'));
        $file_extension = mb_substr($file_name, mb_strrpos($file_name, '.') + 1);

        // If the file was a GIF, then change it to PNG.
        if (mb_strtolower($type) == 'gif') {
            $file_name = $file_name_without_extension . '.png';
            $file_extension = 'png';
        }

        // Check if file name is already in use and change it if necessary.
        $file_name = get_unique_name(array(
            'name' => $file_name,
            'type' => 'file'));

        // save the file
        $handle = fopen(FILE_DIRECTORY_PATH . '/' . $file_name, 'w');
        fwrite($handle, $image_data);
        fclose($handle);
        
        // insert file data into files table
        $query =
            "INSERT INTO files (
                name,
                folder,
                type,
                size,
                user,
                design,
                optimized,
                timestamp) 
            VALUES (
                '" . escape($file_name) . "',
                '" . escape($folder_id) . "',
                '" . escape($file_extension) . "',
                '" . escape(filesize(FILE_DIRECTORY_PATH . '/' . $file_name)) . "',
                '" . $user['id'] . "',
                '0',
                '0',
                UNIX_TIMESTAMP())";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
        log_activity("file ($file_name) was created via Image Editor", $_SESSION['sessionusername']);

    }
   
    /** replace image in content with new dimensions and src value if necessary **/

    $column_to_update = '';

    // if there is a column to update, then set it so that it can be used later on in various places
    if ($_GET['column_to_update']) {
        $column_to_update = $_GET['column_to_update'];
    }

    switch ($object_type) {
        case 'ad':
            // get content
            $query = "SELECT content FROM ads WHERE id = '" . escape($object_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $content = $row['content'];

            $content = update_image_in_content($content, $original_file_name, $file_name);

            // update content in database
            $query = "UPDATE ads SET content = '" . escape($content) . "' WHERE id = '" . escape($object_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            break;
        
        case 'calendar_event':
            // get content
            $query = "SELECT full_description FROM calendar_events WHERE id = '" . escape($object_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $content = $row['full_description'];

            $content = update_image_in_content($content, $original_file_name, $file_name);

            // update content in database
            $query = "UPDATE calendar_events SET full_description = '" . escape($content) . "' WHERE id = '" . escape($object_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            break;
        
        case 'cregion':
            // get content
            $query = "SELECT cregion_content as content FROM cregion WHERE cregion_id = '" . escape($object_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $content = $row['content'];

            $content = update_image_in_content($content, $original_file_name, $file_name);

            // update content in database
            $query = "UPDATE cregion SET cregion_content = '" . escape($content) . "' WHERE cregion_id = '" . escape($object_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            break;
        
        case 'pregion':
            // A user might be editing images in an inline page region that does not exist yet,
            // because region has not been saved/created yet, so that is why we add this check.
            if ($object_id) {
                // get content
                $query = "SELECT pregion_content as content FROM pregion WHERE pregion_id = '" . escape($object_id) . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                $row = mysqli_fetch_assoc($result);
                $content = $row['content'];

                $content = update_image_in_content($content, $original_file_name, $file_name);

                // update content in database
                $query = "UPDATE pregion SET pregion_content = '" . escape($content) . "' WHERE pregion_id = '" . escape($object_id) . "'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            }

            break;

        case 'system_region_header':
            // get content
            $query = "SELECT system_region_header as content FROM page WHERE page_id = '" . escape($object_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $content = $row['content'];

            $content = update_image_in_content($content, $original_file_name, $file_name);

            // update content in database
            $query = "UPDATE page SET system_region_header = '" . escape($content) . "' WHERE page_id = '" . escape($object_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            break;

        case 'system_region_footer':
            // get content
            $query = "SELECT system_region_footer as content FROM page WHERE page_id = '" . escape($object_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $content = $row['content'];

            $content = update_image_in_content($content, $original_file_name, $file_name);

            // update content in database
            $query = "UPDATE page SET system_region_footer = '" . escape($content) . "' WHERE page_id = '" . escape($object_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            break;
        
        case 'product':
        case 'product_group':
            // set the sql table based on the object type
            if ($object_type == 'product') {
                $sql_table = 'products';
            } else {
                $sql_table = 'product_groups';
            }

            $sql_column = '';
            $content = '';

            // set column to update and get content
            switch ($column_to_update) {
                case 'image_name':
                    $sql_column = 'image_name';
                    $content = $file_name;
                    break;

                case 'details':
                    $sql_column = 'details';

                    $query = "SELECT details FROM $sql_table WHERE id = '" . escape($object_id) . "'";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    $row = mysqli_fetch_assoc($result);
                    $content = update_image_in_content($row['details'], $original_file_name, $file_name);
                    break;

                default:
                    $sql_column = 'full_description';

                    $query = "SELECT full_description FROM $sql_table WHERE id = '" . escape($object_id) . "'";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                    $row = mysqli_fetch_assoc($result);
                    $content = update_image_in_content($row['full_description'], $original_file_name, $file_name);
                    break;
            }

            // update content in database
            $query = "UPDATE $sql_table SET $sql_column = '" . escape($content) . "' WHERE id = '" . escape($object_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            break;

        case 'form_field':
            // get content
            $query = "SELECT information FROM form_fields WHERE id = '" . escape($object_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $content = $row['information'];

            $content = update_image_in_content($content, $original_file_name, $file_name);

            // update content in database
            $query = "UPDATE form_fields SET information = '" . escape($content) . "' WHERE id = '" . escape($object_id) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            break;
    }



    // if the image was replaced and it was not a GIF, then it has the same file name, so output code that will update the user's cache,
    // so that the user will see the new image and forward the user to the original page that they were at
    if($_POST['save_option'] == 'Replace' ){
        $output_rawurlencode_iframe = '';

        // if the file name is different when it is rawurlencoded, then we need to clear the cache for both the plain file name and the rawurlencoded file name
        // IE will show the old image if we do not do this, because sometimes a file is embedded with its plain name and sometimes with its rawurlencoded name
        if ($file_name != encode_url_path($file_name)) {
            $output_rawurlencode_iframe = '<iframe id="image_rawurlencode" src="' . OUTPUT_PATH . h(encode_url_path($file_name)) . '" style="display: none"></iframe>';
        }

        print
            '<!DOCTYPE html>
            <html lang="en">
                <head>
                    <meta charset="utf-8">
                    ' . get_generator_meta_tag() . '
                    <script type="text/javascript">
                        function init()
                        {
                            // reload the iframe with the image
                            document.getElementById("image").contentWindow.location.reload(true);

                            // if the rawurlencode iframe exists, then reload it
                            if (document.getElementById("image_rawurlencode")) {
                                document.getElementById("image_rawurlencode").contentWindow.location.reload(true);
                            }

                            // wait a little bit to make sure that the iframe(s) have reloaded and then send the user to the original page that they came from
                            setTimeout("window.parent.location = \'' . URL_SCHEME . HOSTNAME . escape_javascript($send_to) . '\';", 1000);
                        }

                        window.onload = init; 
                    </script>
                </head>
                <body>
                    <iframe id="image" src="' . OUTPUT_PATH . h($file_name) . '" style="display: none"></iframe>
                    ' . $output_rawurlencode_iframe . '
                </body>
            </html>';
                    
    // else the user chose to save a new copy or the image was a GIF, so the image has a new name,
    // so we don't need to update the cache, so forward user to the page that they came from
    } else {
        print
            '<!DOCTYPE html>
            <html lang="en">
                <head>
                    <meta charset="utf-8">
                    ' . get_generator_meta_tag() . '
                    <script type="text/javascript">
                        function init()
                        {
                            window.parent.location = "' . URL_SCHEME . HOSTNAME . escape_javascript($send_to) . '";
                        }

                        window.onload = init; 
                    </script>
                </head>
                <body>
                </body>
            </html>';
    }


}