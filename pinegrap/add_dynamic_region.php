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
    $output =
output_header() . '
<div id="subnav">
    <h1>[new dynamic region]</h1>
</div>
<div id="content">
    
    <a href="#" id="help_link">Help</a>
    <h1>Create Dynamic Region</h1>
    <div class="subheading">Create a new dynamic region that contains PHP code and can be added to any page style.</div>
    <form action="add_dynamic_region.php" method="post">
        ' . get_token_field() . '
        <h2 style="margin-bottom: 1em">New Dynamic Region Name</h2>
        Dynamic Region Name:&nbsp;&nbsp;<input name="name" type="text" size="60" maxlength="100">
        <h2 style="margin-bottom: 1em">PHP Code to appear on associated Pages</h2>
        <div style="margin-bottom: 1.5em">
            <div style="margin-bottom: 1em">PHP Code Snippet:</div>
            <textarea name="code" id="code" rows="30" cols="60" wrap="off"></textarea>
            ' . get_codemirror_includes() . '
            ' . get_codemirror_javascript(array('id' => 'code', 'code_type' => 'php')) . '
        </div>
        <div class="buttons">
            <input type="submit" name="submit_create" value="Create" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary">
        </div>
    </form>
</div>' .
output_footer();

    print $output;
} else {
    validate_token_field();
    
    include_once('liveform.class.php');
    
    $_POST['name'] = trim($_POST['name']);
    // insert row into region table
    $query = "INSERT INTO dregion (dregion_name, dregion_code, dregion_user, dregion_timestamp) "
            ."VALUES ('" . escape($_POST['name']) . "', '" . escape($_POST['code']) . "', {$user['id']}, UNIX_TIMESTAMP())";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed');
    log_activity("dynamic region ($_POST[name]) was created", $_SESSION['sessionusername']);
    $notice = 'Dynamic region was created successfully';
    
    $liveform_view_styles = new liveform('view_regions');
    $liveform_view_styles->add_notice($notice);
    
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_regions.php?filter=all_dynamic_regions');
}
?>