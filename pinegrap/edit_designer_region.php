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
$liveform = new liveform('edit_designer_region');

if (!isset($_POST['name'])) {
    // Get designer region information.
    $query = "SELECT cregion_name, cregion_content, cregion_designer_type "
            ."FROM cregion "
            ."WHERE cregion_id = '" . escape($_GET['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed');
    $row = mysqli_fetch_array($result);
    $cregion_name = $row['cregion_name'];
    $cregion_content = $row['cregion_content'];
    
    print output_header() . '
<div id="subnav">
    <h1>' . h($cregion_name) . '</h1>
    <div class="subheading">Page Style Body Tag: ' . h('<cregion>' . $cregion_name . '</cregion>') . '</div>
</div>
<div id="content">
    
    ' . $liveform->output_errors() . '
    ' . $liveform->output_notices() . '
    <a href="#" id="help_link">Help</a>
    <h1>Edit Designer Region</h1>
    <div class="subheading">Update this designer region of shared content.</div>
    <form action="edit_designer_region.php" method="post">
        ' . get_token_field() . '
        <input type="hidden" name="id" value="' . h($_GET['id']) . '">
        <input type="hidden" name="send_to" value="' . h($_GET['send_to']) . '" />
        <table class="field" style="margin-bottom: 2em; width: 100%">
            <tr>
                <th colspan="2"><h2>Designer Region Name</h2></th>
            </tr>
            <tr>
                <td style="white-space: nowrap">Designer Region Name:</td>
                <td style="width: 100%"><input name="name" type="text" value="' . h($cregion_name) . '" size="60" maxlength="100"></td>
            </tr>
            <tr>
                <th colspan="2"><h2>Shared HTML Content to appear on associated Pages</h2></th>
            </tr>
            <tr>
                <td colspan="2">
                    <div style="margin-bottom: 1em">HTML Code Snippet:</div>
                    <textarea name="content" id="code" rows="30" cols="60" wrap="off">' . h($cregion_content) . '</textarea>
                    ' . get_codemirror_includes() . '
                    ' . get_codemirror_javascript(array('id' => 'code', 'code_type' => 'mixed')) . '
                </td>
            </tr>
            <tr>
                <th colspan="2"><h2>Change Region Type</h2></th>
            </tr>
            <tr>
                <td style="white-space: nowrap">Region Type:</td>
                <td><select name="type"><option value="common">Common Region</option><option value="designer" selected="selected">Designer Region</option></select></td>
            </tr>
        </table>
        <div class="buttons">
            <input type="submit" name="submit_save" value="Save" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary">&nbsp;&nbsp;&nbsp;<input type="button" name="duplicate" value="Duplicate" onclick="javascript:window.location.href=\'' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/duplicate_designer_region.php?id=' . h(escape_javascript(urlencode($_REQUEST['id']))) . get_token_query_string_field() . '\';" class="submit">&nbsp;&nbsp;&nbsp;<input type="submit" name="submit_delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This designer region will be permanently deleted.\')">
        </div>
    </form>
</div>' .
output_footer();

$liveform->remove_form();
    
} else {
    validate_token_field();
    
    // if region was selected for delete
    if ($_POST['submit_delete'] == 'Delete') {
        $query = "DELETE FROM cregion "
                ."WHERE cregion_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed');
        log_activity("designer region ($_POST[name]) was deleted", $_SESSION['sessionusername']);
        
        // if there is not a send to, then prepare notice
        if ($_POST['send_to'] == '') {
            $notice = 'Designer region was deleted successfully.';
        }
        
    } else {
        $_POST['name'] = trim($_POST['name']);

        if ($_POST['type'] == 'common') {
            $cregion_designer_type = 'no';
        } else {
            $cregion_designer_type = 'yes';
        }
        
        $query = "UPDATE cregion "
                ."SET cregion_name = '" . escape($_POST['name']) . "', "
                    ."cregion_content = '" . escape($_POST['content']) . "', "
                    ."cregion_designer_type = '$cregion_designer_type', "
                    ."cregion_user = '" . $user['id'] . "', "
                    ."cregion_timestamp = UNIX_TIMESTAMP() "
                ."WHERE cregion_id = '" . escape($_POST['id']) . "'";
        // update region
        $result = mysqli_query(db::$con, $query) or output_error('Query failed');
        log_activity("designer region ($_POST[name]) was modified", $_SESSION['sessionusername']);
        
        // if there is not a send to, then prepare notice
        if ($_POST['send_to'] == '') {
            $notice = 'Designer region was edited successfully.';
        }
    }
    
    // if there is a send to, then forward user to send to
    if ($_POST['send_to'] != '') {
        header('Location: ' . URL_SCHEME . HOSTNAME . $_POST['send_to']);
        
    // else there is not a send to, so prepare notice and send user to view ads screen
    } else {
        $liveform_view_styles = new liveform('view_regions');
        $liveform_view_styles->add_notice($notice);
        
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_regions.php?filter=all_' . $_POST['type'] . '_regions');
    }
}
?>