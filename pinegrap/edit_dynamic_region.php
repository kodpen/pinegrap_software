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
validate_area_access($user, 'administrator');

if (!isset($_POST['name'])) {
    $query = "SELECT dregion_name, dregion_code "
            ."FROM dregion "
            ."WHERE dregion_id = '" . escape($_GET['id']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed');
    $row = mysqli_fetch_array($result);
    $dregion_name = $row['dregion_name'];
    $dregion_code = $row['dregion_code'];

    print 
        output_header() . '
        <div id="subnav">
            <h1>' . h($dregion_name) . '</h1>
            <div class="subheading">Page Style Body Tag: ' . h('<dregion>' . $dregion_name . '</dregion>') . '</div>
        </div>
        <div id="content">
            
            <a href="#" id="help_link">Help</a>
            <h1>Edit Dynamic Region</h1>
            <div class="subheading"></div>
            <form action="edit_dynamic_region.php" method="post">
                ' . get_token_field() . '
                <input type="hidden" name="id" value="' . h($_GET['id']) . '" />
                <h2 style="margin-bottom: 1em">Dynamic Region Name</h2>
                Dynamic Region Name:&nbsp;&nbsp;<input name="name" type="text" value="' .h($dregion_name) . '" size="60" maxlength="100">
                <h2 style="margin-bottom: 1em">PHP Code to appear on associated Pages</h2>
                <div style="margin-bottom: 1.5em">
                    <div style="margin-bottom: 1em">PHP Code Snippet:</div>
                    <textarea name="code" id="code" rows="30" cols="60" wrap="off">' . h($dregion_code) . '</textarea>
                    ' . get_codemirror_includes() . '
                    ' . get_codemirror_javascript(array('id' => 'code', 'code_type' => 'php')) . '
                </div>
                <div class="buttons">
                    <input type="submit" name="submit_save" value="Save" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary">&nbsp;&nbsp;&nbsp;<input type="submit" name="submit_delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This dynamic region will be permanently deleted.\')">
                </div>
            </form>
        </div>' .
        output_footer();
} else {
    validate_token_field();
    
    include_once('liveform.class.php');
    
    // if region was selected for delete
    if ($_POST['submit_delete'] == 'Delete') {
        $query = "DELETE FROM dregion "
                ."WHERE dregion_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed');
        log_activity("dynamic region ($_POST[name]) was deleted", $_SESSION['sessionusername']);
        $notice = 'Dynamic region was deleted successfully.';
    } else {
        $_POST['name'] = trim($_POST['name']);
        // update region
        $query = "UPDATE dregion "
                ."SET dregion_name = '" . escape($_POST['name']) . "', "
                    ."dregion_code = '" . escape($_POST['code']) . "', "
                    ."dregion_user = {$user['id']}, "
                    ."dregion_timestamp = UNIX_TIMESTAMP() "
                ."WHERE dregion_id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed');
        log_activity("dynamic region ($_POST[name]) was modified", $_SESSION['sessionusername']);
        $notice = 'Dynamic region was edited successfully.';
    }
    
    $liveform_view_styles = new liveform('view_regions');
    $liveform_view_styles->add_notice($notice);
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_regions.php?filter=all_dynamic_regions');
}
?>