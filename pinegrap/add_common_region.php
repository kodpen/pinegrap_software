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
    $output .= output_header() . '
        <div id="subnav">
            <h1>[new common region]</h1>
        </div>
        <div id="content">
            
            <a href="#" id="help_link">Help</a>
            <h1>Create Common Region</h1>
            <div class="subheading">Create a common region of shared content that can be added to any page style and updated during page editing by any site manager.</div>
            <form action="add_common_region.php" method="post">
                ' . get_token_field() . '
                <h2 style="margin-bottom: 1em">New Common Region Name</h2>
                Common Region Name: <input name="name" type="text" size="60" maxlength="100">
                <h2 style="margin-bottom: 1em">Shared Content to appear on associated Pages</h2>
                <div style="margin-bottom: 1.5em">
                    <div style="margin-bottom: 1em">Content:</div>
                    <textarea id="content_textarea" name="content" style="width: 100%" rows="30" wrap="off"></textarea>
                </div>
                <div class="buttons">
                    <input type="submit" name="submit_create" value="Create" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="javascript:history.go(-1);" class="submit-secondary">
                </div>
            </form>
        </div>' .
        output_footer();
    
    // Get wysiwyg editor code
    $output_wysiwyg_editor_code = get_wysiwyg_editor_code(array('content_textarea'), $activate_editors = true);
    
    // Puts the wysiwyg code in the header.
    $output = preg_replace('/(<\/head>)/i', $output_wysiwyg_editor_code . '$1', $output);
    
    print $output;
    
} else {
    validate_token_field();
    
    include_once('liveform.class.php');
    
    $_POST['name'] = trim($_POST['name']);

    $query = "INSERT INTO cregion (cregion_name, cregion_content, cregion_designer_type, cregion_user, cregion_timestamp) "
            ."VALUES ('" . escape($_POST['name']) . "', '" . escape(prepare_rich_text_editor_content_for_input($_POST['content'])) . "', 'no', {$user['id']}, UNIX_TIMESTAMP())";
    // insert row into region table
    $result = mysqli_query(db::$con, $query) or output_error('Query failed');
    log_activity("common region ($_POST[name]) was created", $_SESSION['sessionusername']);
    
    $notice = 'Common region was created successfully';
    
    $liveform_view_styles = new liveform('view_regions');
    $liveform_view_styles->add_notice($notice);
    
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_regions.php?filter=all_common_regions');
}
?>