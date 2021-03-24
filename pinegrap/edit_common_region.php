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

include_once('liveform.class.php');
$liveform = new liveform('edit_common_region');

if (!isset($_POST['name'])) {
    $query = "SELECT cregion_name, cregion_content, cregion_designer_type "
            ."FROM cregion "
            ."WHERE cregion_id = '" . escape($_GET['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed');
    $row = mysqli_fetch_array($result);
    $cregion_name = $row['cregion_name'];
    $cregion_content = $row['cregion_content'];
    $cregion_designer_type = $row['cregion_designer_type'];
    
    $output .= 
        output_header() . '
            <div id="subnav">
                <h1>' . h($cregion_name) . '</h1>
                <div class="subheading">Page Style Body Tag: ' . h('<cregion>' . $cregion_name . '</cregion>') . '</div>
            </div>
            <div id="content">
                
                ' . $liveform->output_errors() . '
                ' . $liveform->output_notices() . '
                <a href="#" id="help_link">Help</a>
                <h1>Edit Common Region</h1>
                <div class="subheading">Update this common region of shared content. (A rename will require its tag to be updated in any page styles.)</div>
                <form action="edit_common_region.php" method="post">
                    ' . get_token_field() . '
                    <input type="hidden" name="id" value="' . h($_GET['id']) . '">
                    <table class="field" style="margin-bottom: 2em; width: 100%">
                        <tr>
                            <th colspan="2"><h2>Common Region Name</h2></th>
                        </tr>
                        <tr>
                            <td style="white-space: nowrap">Common Region Name:</td>
                            <td style="width: 100%"><input name="name" type="text" value="' . h($cregion_name) . '" size="60" maxlength="100"></td>
                        </tr>
                        <tr>
                            <th colspan="2"><h2>Shared Content to appear on associated Pages</h2></th>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <div style="margin-bottom: 1em">Content:</div>
                                <textarea id="content_textarea" name="content" style="width: 100%" rows="30" wrap="off">' . h(prepare_rich_text_editor_content_for_output($cregion_content)) . '</textarea>
                            </td>
                        </tr>
                        <tr>
                            <th colspan="2"><h2>Change Region Type</h2></th>
                        </tr>
                        <tr>
                            <td style="white-space: nowrap">Region Type:</td>
                            <td><select name="type"><option value="common" selected="selected">Common Region</option><option value="designer">Designer Region</option></select></td>
                        </tr>
                    </table>
                    <div class="buttons">
                        <input type="submit" name="submit_save" value="Save" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary">&nbsp;&nbsp;&nbsp;<input type="button" name="duplicate" value="Duplicate" onclick="javascript:window.location.href=\'' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/duplicate_common_region.php?id=' . h(escape_javascript(urlencode($_REQUEST['id']))) . get_token_query_string_field() . '\';" class="submit">&nbsp;&nbsp;&nbsp;<input type="submit" name="submit_delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This common region will be permanently deleted.\')">
                    </div>
                </form>
            </div>' .
            output_footer();
    
    // Get wysiwyg editor code
    $output_wysiwyg_editor_code = get_wysiwyg_editor_code(array('content_textarea'), $activate_editors = true);
    
    // Puts the wysiwyg code in the header.
    $output = preg_replace('/(<\/head>)/i', $output_wysiwyg_editor_code . '$1', $output);
    
    print $output;

    $liveform->remove_form();

} else {
    validate_token_field();
    
    // if region was selected for delete
    if ($_POST['submit_delete'] == 'Delete') {
        $query = "DELETE FROM cregion "
                ."WHERE cregion_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed');
        
        // delete users_common_regions_xref records
        $query = "DELETE FROM users_common_regions_xref WHERE common_region_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        log_activity("common region ($_POST[name]) was deleted", $_SESSION['sessionusername']);
        $notice = 'Common region was deleted successfully';
    } else {
        $_POST['name'] = trim($_POST['name']);

        if ($_POST['type'] == 'common') {
            $cregion_designer_type = 'no';
        } else {
            $cregion_designer_type = 'yes';
        }

        $query = "UPDATE cregion "
                ."SET cregion_name = '" . escape($_POST['name']) . "', "
                    ."cregion_content = '" . escape(prepare_rich_text_editor_content_for_input($_POST['content'])) . "', "
                    ."cregion_designer_type = '$cregion_designer_type', "
                    ."cregion_user = '" . $user['id'] . "', "
                    ."cregion_timestamp = UNIX_TIMESTAMP() "
                ."WHERE cregion_id = '" . escape($_POST['id']) . "'";
        // update region
        $result = mysqli_query(db::$con, $query) or output_error('Query failed');
        log_activity("common region ($_POST[name]) was modified", $_SESSION['sessionusername']);
        $notice = 'Common region was edited successfully';
    }
    
    $liveform_view_styles = new liveform('view_regions');
    $liveform_view_styles->add_notice($notice);
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_regions.php?filter=all_' . $_POST['type'] . '_regions');
}