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
    // Get style information.
    $query = 
        "SELECT
           style.style_id,
           style.style_name,
           style.style_code,
           style.social_networking_position,
           style.collection,
           style.layout_type,
           style.style_timestamp,
           user.user_username
       FROM style
       LEFT JOIN user ON style.style_user = user.user_id
       WHERE style_id = '" . escape($_REQUEST['id']) . "'";
    $result = mysqli_query(db::$con, $query);
    $row = mysqli_fetch_array($result);
    $style_id = $row['style_id'];
    $style_name = $row['style_name'];
    $style_timestamp = $row['style_timestamp'];
    $style_code = $row['style_code'];
    $social_networking_position = $row['social_networking_position'];
    $collection = $row['collection'];
    $layout_type = $row['layout_type'];
    
    $output_header = output_header();

    $output_social_networking_position = '';

    // If social networking is enabled, then output position pick list.
    if (SOCIAL_NETWORKING == TRUE) {
        $output_top_left_selected = '';
        $output_top_right_selected = '';
        $output_bottom_left_selected = '';
        $output_bottom_right_selected = '';
        $output_disabled_selected = '';

        switch ($social_networking_position) {
            case 'top_left':
                $output_top_left_selected = ' selected="selected"';
                break;

            case 'top_right':
                $output_top_right_selected = ' selected="selected"';
                break;

            case 'bottom_left':
                $output_bottom_left_selected = ' selected="selected"';
                break;

            case 'bottom_right':
                $output_bottom_right_selected = ' selected="selected"';
                break;

            case 'disabled':
                $output_disabled_selected = ' selected="selected"';
                break;
        }

        $output_social_networking_position =
            '<table class="field">
                <tr>
                    <td>Social Networking Position:</td>
                    <td>
                        <select name="social_networking_position" style="vertical-align: middle">
                            <option value="top_left"' . $output_top_left_selected . '>Top Left</option>
                            <option value="top_right"' . $output_top_right_selected . '>Top Right</option>
                            <option value="bottom_left"' . $output_bottom_left_selected . '>Bottom Left</option>
                            <option value="bottom_right"' . $output_bottom_right_selected . '>Bottom Right</option>
                            <option value="disabled"' . $output_disabled_selected . '>Disabled</option>
                        </select>
                    </td>
                </tr>
            </table>';
    }

    if ($collection == 'a') {
        $output_collection_a_checked = ' checked="checked"';
        $output_collection_b_checked = '';
    } else {
        $output_collection_a_checked = '';
        $output_collection_b_checked = ' checked="checked"';
    }

    $output_layout_type_system_selected = '';
    $output_layout_type_custom_selected = '';

    if ($layout_type == 'system') {
        $output_layout_type_system_selected = ' selected="selected"';
    } else if ($layout_type == 'custom') {
        $output_layout_type_custom_selected = ' selected="selected"';
    }
    
    if (isset($row['user_username']) == TRUE) {
        $user_username = $row['user_username'];
    } else {
        $user_username = '[Unknown]';
    }

    // If there is a send to, then set the cancel button URL to the send to.
    if ($_GET['send_to'] != '') {
        $output_cancel_button_url = h(escape_javascript($_GET['send_to']));
        
    // Otherwise there is not a send to, so set the cancel button URL to the view styles screen.
    } else {
        $output_cancel_button_url = OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/view_styles.php';
    }
    
    // Find if style is being used by a folder
    $query =
        "SELECT
            COUNT(folder_id)
        FROM folder
        WHERE
            (folder_style = '" . $style_id . "')
            OR (mobile_style_id = '" . $style_id . "')";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_row($result);
    $folders_using_style = $row[0];
    
    // Find if style is being used by a page
    $query =
        "SELECT
            COUNT(page_id)
        FROM page
        WHERE
            (page_style = '" . $style_id . "')
            OR (mobile_style_id = '" . $style_id . "')";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_row($result);
    $pages_using_style = $row[0];

    // if style is being used by either a folder or a page
    if (($folders_using_style > 0) || ($pages_using_style > 0)) {
        // output delete button with alert
        $output_delete_button = '<input type="button" value="Delete" class="delete" onclick="alert(\'You may not delete this page style because it is being used by at least one folder or page.\')" />';
    } else {
        // output regular delete button
        $output_delete_button = '<input type="submit" name="submit_delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This page style will be permanently deleted.\')">';
    }

    print 
        $output_header . '
        <div id="subnav">
            <h1>' . h($style_name) . '</h1>
            <div class="subheading">Last Modified ' . get_relative_time(array('timestamp' => $style_timestamp)) . ' by ' . h($user_username) . '</div>
        </div>
        <div id="content">
            
            <a href="#" id="help_link">Help</a>
            <h1>Edit Page Style</h1>
            <div class="subheading">Update this HTML template that is applied to all associated pages.</div>
            <div style="width: 100%; padding-top: .5em;">
                <form action="edit_custom_style.php" method="post">
                    ' . get_token_field() . '
                    <input type="hidden" name="id" value="'. h($_REQUEST['id']) .'">
                    <input type="hidden" name="send_to" value="' . h($_REQUEST['send_to']) . '" />
                    <h2 style="margin-bottom: 1em">Page Style Name</h2>
                    Name:&nbsp;<input name="name" type="text" size="60" maxlength="100" value = "' . h($style_name) . '" />
                    <h2 style="margin-bottom: 1em">HTML page template with embedded Tags</h2>                
                    <div id="edit_custom" style="margin-bottom: 1.5em">
                        <textarea name="code" id="code" rows="25" cols="60" wrap="off">' . h($style_code) . '</textarea>
                        ' . get_codemirror_includes() . '
                        ' . get_codemirror_javascript(array('id' => 'code', 'code_type' => 'mixed')) . '
                    </div>

                    <h2 style="margin-bottom: 1em">Additional Options</h2>

                    <div>

                        <table class="field">

                            <tr>
                                <td>Collection:</td>
                                <td>

                                    <input type="radio" name="collection" id="collection_a" value="a"' . $output_collection_a_checked . ' class="radio" /><label for="collection_a">Collection A</label>&nbsp;

                                    <input type="radio" name="collection" id="collection_b" value="b"' . $output_collection_b_checked . ' class="radio" /><label for="collection_b">Collection B</label>
                                </td>
                            </tr>

                            <tr>
                                <td><label for="layout_type">Override Layout Type:</label></td>
                                <td>
                                    <select id="layout_type" name="layout_type">
                                        <option value=""></option>
                                        <option value="system"' . $output_layout_type_system_selected . '>System</option>
                                        <option value="custom"' . $output_layout_type_custom_selected . '>Custom</option>
                                    </select>
                                </td>
                            </tr>

                        </table>

                    </div>

                    <div >' . $output_social_networking_position . '</div>
                    <div style="clear: both"></div>
                    <div class="buttons">
                        <input type="submit" name="submit_save_and_return" value="Save & Return" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="submit" name="submit_save" value="Save" class="submit-secondary" />&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="window.location.href=\'' . $output_cancel_button_url . '\'" class="submit-secondary">&nbsp;&nbsp;&nbsp;<input type="button" name="duplicate" value="Duplicate" onclick="javascript:window.location.href=\'' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/duplicate_style.php?id=' . h(escape_javascript(urlencode($_REQUEST['id']))) . get_token_query_string_field() . '\';" class="submit">&nbsp;&nbsp;&nbsp;' . $output_delete_button . '
                    </div>
                </form>
            </div>
            <div style="clear: both"></div>
        </div>' .
        output_footer();
}
else
{
    validate_token_field();
    
    // Start liveform
    include_once('liveform.class.php');
    
    // if style was selected for delete
    if ($_POST['submit_delete'] == 'Delete') {
        
        // Find if style is being used by a folder
        $query =
            "SELECT
                COUNT(folder_id)
            FROM folder
            WHERE
                (folder_style = '" . escape($_POST['id']) . "')
                OR (mobile_style_id = '" . escape($_POST['id']) . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_row($result);
        $folders_using_style = $row[0];
        
        // Find if style is being used by a page
        $query =
            "SELECT
                COUNT(page_id)
            FROM page
            WHERE
                (page_style = '" . escape($_POST['id']) . "')
                OR (mobile_style_id = '" . escape($_POST['id']) . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_row($result);
        $pages_using_style = $row[0];

        // if style is being used by either a folder or a page
        if (($folders_using_style > 0) || ($pages_using_style > 0)) {
            
            // output error
            output_error('You may not delete this page style because it is being used by at least one folder or page.');
        
        // else delete the style
        } else {
            
            $result=mysqli_query(db::$con, "DELETE FROM style WHERE style_id = '" . escape($_POST['id']) . "'") or output_error('Query failed');

            db("DELETE FROM preview_styles WHERE style_id = '" . escape($_POST['id']) . "'");
            
            log_activity("style ($_POST[name]) was deleted", $_SESSION['sessionusername']);
            $notice = 'The style was deleted successfully.';
        }
    
    // Else update the style.
    } else {
        $name = trim($_POST['name']);

        $sql_social_networking_position = "";

        // If social networking is enabled, then update position value.
        if (SOCIAL_NETWORKING == TRUE) {
            $sql_social_networking_position = "social_networking_position = '" . escape($_POST['social_networking_position']) . "',";
        }

        // update style
        $result = mysqli_query(db::$con, "UPDATE style SET style_name = '" . escape($name) . "', style_code = '" . escape($_POST['code']) . "', " . $sql_social_networking_position . "collection = '" . escape($_POST['collection']) . "', layout_type = '" . e($_POST['layout_type']) . "', style_timestamp = UNIX_TIMESTAMP(), style_user = '" . $user['id'] . "' WHERE style_id = '" . escape($_POST['id']) . "'") or output_error('Query failed');
        log_activity("style ($name) was modified", $_SESSION['sessionusername']);
        $notice = 'The style was edited successfully.';
    }

    // If user clicked save button, then forward user back to this screen again.
    if ($_POST['submit_save'] == 'Save') {
        $send_to = '' ;

        if ($_POST['send_to'] != '') {
            $send_to = '&send_to=' . urlencode($_POST['send_to']);
        }

        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/edit_custom_style.php?id=' . urlencode($_POST['id']) . $send_to);
        exit();

    // Otherwise the user clicked one of the other buttons (e.g. Save & Return or Delete),
    // so determine where the user should be sent.
    } else {
        // If there is a send to set, then forward user to send to.
        if ($_POST['send_to'] != '') {
            header('Location: ' . URL_SCHEME . HOSTNAME . $_POST['send_to']);
            exit();
            
        // Otherwise there is not a send to set, so send user to view styles screen.
        } else {
            $liveform_view_styles = new liveform('view_styles');
            $liveform_view_styles->add_notice($notice);
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_styles.php');
            exit();
        }
    }
}
?>