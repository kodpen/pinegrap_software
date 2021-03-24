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
$liveform = new liveform('edit_login_region', $_REQUEST['id']);

if (!$_POST) {
    // if edit login region screen has not been submitted already, pre-populate fields with data
    if (isset($_SESSION['software']['liveforms']['edit_login_region'][$_GET['id']]) == false) {
        // get login name, header and footer information to use for later
        $query =
            "SELECT 
                name,
                not_logged_in_header,
                login_form,
                not_logged_in_footer,
                logged_in_header,
                logged_in_footer
            FROM login_regions
            WHERE id = '" . escape($_REQUEST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        
        // Assign the values to the fields.
        $liveform->assign_field_value('name', $row['name']);
        $liveform->assign_field_value('not_logged_in_header', $row['not_logged_in_header']);
        $liveform->assign_field_value('login_form', $row['login_form']);
        $liveform->assign_field_value('not_logged_in_footer', $row['not_logged_in_footer']);
        $liveform->assign_field_value('logged_in_header', $row['logged_in_header']);
        $liveform->assign_field_value('logged_in_footer', $row['logged_in_footer']);
    }
    
    print 
        output_header() . '
        <div id="subnav">
            <h1>' . h($row['name']) . '</h1>
            <div class="subheading">Page Style Body Tag: ' . h('<login>' . $row['name'] . '</login>') . '</div>
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Edit Login Region</h1>
            <div class="subheading">Update the messages displayed in the login region before and after users log in.</div>
            <form name="form" action="edit_login_region.php" method="post">
                ' . get_codemirror_includes() . '
                ' . get_token_field() . '
                <input type="hidden" name="id" value="' . h($_GET['id']) . '" />
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>Login Region Name</h2></th>
                    </tr>
                    <tr>
                        <td>Login Region Name:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'name', 'size'=>'60', 'maxlength'=>'100')) . '</td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Header content to display when User is not logged in</h2></th>
                    </tr>
                    <tr>
                        <td style="vertical-align: top">Not Logged In Header:</td>
                        <td>
                            ' . $liveform->output_field(array('type'=>'textarea', 'name'=>'not_logged_in_header', 'id'=>'not_logged_in_header', 'style'=>'width: 600px; height: 200px', 'class'=>'text-area')) . '
                            ' . get_codemirror_javascript(array('id' => 'not_logged_in_header', 'code_type' => 'mixed', 'height' => '200px')) . '
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top"><label for="login_form">Show Login Form:</label></td>
                        <td>' . $liveform->output_field(array('type'=>'checkbox', 'name'=>'login_form', 'id'=>'login_form', 'value'=>'1', 'class'=>'checkbox', 'checked'=>'checked')) . '</td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Footer content to display when User is not logged in</h2></th>
                    </tr>
                    <tr>
                        <td style="vertical-align: top">Not Logged In Footer:</td>
                        <td>
                            ' . $liveform->output_field(array('type'=>'textarea', 'name'=>'not_logged_in_footer', 'id'=>'not_logged_in_footer', 'style'=>'width: 600px; height: 200px', 'class'=>'text-area')) . '
                            ' . get_codemirror_javascript(array('id' => 'not_logged_in_footer', 'code_type' => 'mixed', 'height' => '200px')) . '
                        </td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Header content to display when User is logged in</h2></th>
                    </tr>
                    <tr>
                        <td style="vertical-align: top">Logged In Header:</td>
                        <td>
                            ' . $liveform->output_field(array('type'=>'textarea', 'name'=>'logged_in_header', 'id'=>'logged_in_header', 'style'=>'width: 600px; height: 200px', 'class'=>'text-area')) . '
                            ' . get_codemirror_javascript(array('id' => 'logged_in_header', 'code_type' => 'mixed', 'height' => '200px')) . '
                        </td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Footer content to display when User is logged in</h2></th>
                    </tr>
                    <tr>
                        <td style="vertical-align: top">Logged In Footer:</td>
                        <td>
                            ' . $liveform->output_field(array('type'=>'textarea', 'name'=>'logged_in_footer', 'id'=>'logged_in_footer', 'style'=>'width: 600px; height: 200px', 'class'=>'text-area')) . '
                            ' . get_codemirror_javascript(array('id' => 'logged_in_footer', 'code_type' => 'mixed', 'height' => '200px')) . '
                        </td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_save" value="Save" class="submit-primary" />&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="javascript:history.go(-1);" class="submit-secondary" />&nbsp;&nbsp;&nbsp;<input type="submit" name="submit_delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This login region will be permanently deleted.\')" />
                </div>
            </form>
        </div>' .
        output_footer();
    
    $liveform->remove_form();

} else {
    validate_token_field();
    
    // if login region was selected for deletion
    if ($_POST['submit_delete'] == 'Delete') {
        // delete login region
        $query = "DELETE FROM login_regions WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        log_activity('login region (' . $_POST['name'] . ') was deleted', $_SESSION['sessionusername']);
        
        $liveform_view_regions = new liveform('view_regions');
        $liveform_view_regions->add_notice('The login region was deleted successfully.');
        
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_regions.php?filter=all_login_regions');
        
        $liveform->remove_form();
        
        exit();
        
    // else login region was not selected for deletion
    } else {
        $liveform->add_fields_to_session();
        
        // validate the required fields
        $liveform->validate_required_field('name', 'Name is required.');
        
        // if there is an error, forward user back to edit login region screen
        if ($liveform->check_form_errors() == true) {
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/edit_login_region.php?id=' . $_POST['id']);
            exit();
        }
        
        // check to see if name is already in use
        $query =
            "SELECT id
            FROM login_regions
            WHERE (name = '" . escape($liveform->get_field_value('name')) . "')
            AND (id != '" . escape($_POST['id']) . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // if name is already in use, prepare error and forward user back to login region screen
        if (mysqli_num_rows($result) > 0) {
            $liveform->mark_error('name', 'The name that you entered is already in use, so please enter a different name.');
            
            // forward user to edit login region screen
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/edit_login_region.php?id=' . $_POST['id']);
            exit();
        }
        
        // update region
        $query = 
            "UPDATE login_regions SET
                name = '" . escape($liveform->get_field_value('name')) . "',
                not_logged_in_header = '" . escape($liveform->get_field_value('not_logged_in_header')) . "',
                login_form = '" . escape($liveform->get_field_value('login_form')) . "',
                not_logged_in_footer = '" . escape($liveform->get_field_value('not_logged_in_footer')) . "',
                logged_in_header = '" . escape($liveform->get_field_value('logged_in_header')) . "',
                logged_in_footer = '" . escape($liveform->get_field_value('logged_in_footer')) . "',
                last_modified_user_id = '" . $user['id'] . "',
                last_modified_timestamp = UNIX_TIMESTAMP()
            WHERE id = '" . escape($_POST['id']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed');
        
        log_activity('login region (' . $_POST['name'] . ') was modified', $_SESSION['sessionusername']);
        
        $liveform_view_regions = new liveform('view_regions');
        $liveform_view_regions->add_notice('The login region was edited successfully.');
        
        $liveform->remove_form();
        
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_regions.php?filter=all_login_regions');
    }
}
?>