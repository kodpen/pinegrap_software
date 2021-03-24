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

// If the form was not just submitted, then continue to output the page.
if (!$_POST) {
    
    $output_header = output_header();
    
    $name = '';
        
    $code =
'<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title></title>
        <meta_tags></meta_tags>
        <stylesheet></stylesheet>
    </head>
    <body>
        <pregion></pregion>
        <system></system>
        <pregion></pregion>
    </body>
</html>';
    

    $output_social_networking_position = '';

    // If social networking is enabled, then output position pick list.
    if (SOCIAL_NETWORKING == TRUE) {
        $output_social_networking_position =
            '<table class="field">
                <tr>
                    <td>Social Networking Position:</td>
                    <td>
                        <select name="social_networking_position" style="vertical-align: middle">
                            <option value="top_left">Top Left</option>
                            <option value="top_right">Top Right</option>
                            <option value="bottom_left" selected="selected">Bottom Left</option>
                            <option value="bottom_right">Bottom Right</option>
                            <option value="disabled">Disabled</option>
                        </select>
                    </td>
                </tr>
            </table>';
    }
   
    print
        $output_header . '
        <div id="subnav">
            <h1>[new page style]</h1>
        </div>
        <div id="content">
            
            <a href="#" id="help_link">Help</a>
            <h1>Create Custom Page Style</h1>
            <div class="subheading">Create a new HTML template that can be associated with one or many pages.</div>
            <div style="padding-top: .5em;" class="custom_page_style_name">
                <form action="add_custom_style.php" method="post">
                    ' . get_token_field() . '
                    <h2 style="margin-bottom: 1em">New Page Style Name</h2>
                    <span>Name:</span> <input name="name" type="text" size="60" maxlength="100" value="' . h($name) . '" />
                    <h2 style="margin-bottom: 1em">HTML Page with embedded Tags</h2>
                    <div id="edit_custom" style="margin-bottom: 1.5em">

                        <textarea name="code" id="code" rows="25" cols="60" wrap="off">' . h($code) . '</textarea>
                        ' . get_codemirror_includes() . '
                        ' . get_codemirror_javascript(array('id' => 'code', 'code_type' => 'mixed')) . '
                    </div>

                    <h2 style="margin-bottom: 1em">Additional Options</h2>

                    <div >

                        <table class="field">

                            <tr>
                                <td>Collection:</td>
                                <td>

                                    <input type="radio" name="collection" id="collection_a" value="a" checked="checked" class="radio" /><label for="collection_a">Collection A</label>&nbsp;

                                    <input type="radio" name="collection" id="collection_b" value="b" class="radio" /><label for="collection_b">Collection B</label>
                                </td>
                            </tr>

                            <tr>
                                <td><label for="layout_type">Override Layout Type:</label></td>
                                <td>
                                    <select id="layout_type" name="layout_type">
                                        <option value=""></option>
                                        <option value="system">System</option>
                                        <option value="custom">Custom</option>
                                    </select>
                                </td>
                            </tr>

                        </table>

                    </div>

                    <div >' . $output_social_networking_position . '</div>
                    <div style="clear: both"></div>
                    <div class="buttons">
                        <input type="submit" name="submit_create" value="Create" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" OnClick="window.location=\'' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/view_styles.php\'" class="submit-secondary">
                    </div>
                </form>
            </div>
        </div>' .
    output_footer();

// else save the page style
} else {
    validate_token_field();
    
    $name = trim($_POST['name']);

    $sql_field_social_networking_position = "";
    $sql_value_social_networking_position = "";

    // If social networking is enabled, then update position value.
    if (SOCIAL_NETWORKING == TRUE) {
        $sql_field_social_networking_position = "social_networking_position,";
        $sql_value_social_networking_position = "'" . escape($_POST['social_networking_position']) . "',";
    }
    
    // insert row into style table
    $result=mysqli_query(db::$con, "INSERT INTO style (style_name, style_code, " . $sql_field_social_networking_position . "collection, layout_type, style_timestamp, style_user) VALUES ('" . escape($name) . "', '" . escape($_POST['code']) . "', " . $sql_value_social_networking_position . "'" . escape($_POST['collection']) . "', '" . e($_POST['layout_type']) . "', UNIX_TIMESTAMP(), '$user[id]')") or output_error('Query failed');

    log_activity("style ($name) was created", $_SESSION['sessionusername']);
    include_once('liveform.class.php');
    $notice = 'The style was created successfully.';
    $liveform_view_styles = new liveform('view_styles');
    $liveform_view_styles->add_notice($notice);
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/view_styles.php');
}
?>