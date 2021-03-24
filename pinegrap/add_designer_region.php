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

if (!isset($_POST['name'])) {
    print output_header() . '
        <div id="subnav">
            <h1>[new designer region]</h1>
        </div>
        <div id="content">
            
            <a href="#" id="help_link">Help</a>
            <h1>Create Designer Region</h1>
            <div class="subheading">Create a designer region of shared content that can be added to any page style and updated during page editing by any Site designer.</div>
            <form action="add_designer_region.php" method="post">
                ' . get_token_field() . '
                <h2 style="margin-bottom: 1em">New Designer Region Name</h2>
                Designer Region Name:&nbsp;&nbsp;<input name="name" type="text" size="60" maxlength="100">
                <h2 style="margin-bottom: 1em">Shared HTML Content to appear on associated Pages</h2>
                <div style="margin-bottom: 1.5em">
                    <div style="margin-bottom: 1em">HTML Code Snippet:</div>
                    <textarea name="content" id="code" rows="30" cols="60" wrap="off"></textarea>
                    ' . get_codemirror_includes() . '
                    ' . get_codemirror_javascript(array('id' => 'code', 'code_type' => 'mixed')) . '
                </div>
                <div class="buttons">
                    <input type="submit" name="submit_create" value="Create" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary">
                </div>
            </form>
        </div>' .
        output_footer();
    
} else {
    validate_token_field();
    
    include_once('liveform.class.php');
    
    $_POST['name'] = trim($_POST['name']);

    $query = "INSERT INTO cregion (cregion_name, cregion_content, cregion_designer_type, cregion_user, cregion_timestamp) "
            ."VALUES ('" . escape($_POST['name']) . "', '" . escape($_POST['content']) . "', 'yes', {$user['id']}, UNIX_TIMESTAMP())";
    // insert row into region table
    $result = mysqli_query(db::$con, $query) or output_error('Query failed');
    log_activity("common region ($_POST[name]) was created", $_SESSION['sessionusername']);
    $notice = 'Designer region was created successfully';
    
    $liveform_view_styles = new liveform('view_regions');
    $liveform_view_styles->add_notice($notice);
    
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_regions.php?filter=all_designer_regions');
}
?>