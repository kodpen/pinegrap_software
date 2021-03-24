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
$liveform = new liveform('add_login_region');

// if add login region screen has not been submitted already
if (!$_POST) {
    print
        output_header() . '
        <div id="subnav">
            <h1>[new login region]</h1>
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Create Login Region</h1>
            <div class="subheading">Create the messages displayed in the login region before and after users log in.</div>
            <form name="form" action="add_login_region.php" method="post">
                ' . get_codemirror_includes() . '
                ' . get_token_field() . '
                <table class="field">
                    <tr>
                        <th colspan="2"><h2>New Login Region Name</h2></th>
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
                            ' . $liveform->output_field(array('type'=>'textarea', 'name'=>'not_logged_in_header', 'id'=>'not_logged_in_header', 'style'=>'width: 600px; height: 200px')) . '
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
                            ' . $liveform->output_field(array('type'=>'textarea', 'name'=>'not_logged_in_footer', 'id'=>'not_logged_in_footer', 'style'=>'width: 600px; height: 200px')) . '
                            ' . get_codemirror_javascript(array('id' => 'not_logged_in_footer', 'code_type' => 'mixed', 'height' => '200px')) . '
                        </td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Header content to display when User is logged in</h2></th>
                    </tr>
                    <tr>
                        <td style="vertical-align: top">Logged In Header:</td>
                        <td>
                            ' . $liveform->output_field(array('type'=>'textarea', 'name'=>'logged_in_header', 'id'=>'logged_in_header', 'style'=>'width: 600px; height: 200px')) . '
                            ' . get_codemirror_javascript(array('id' => 'logged_in_header', 'code_type' => 'mixed', 'height' => '200px')) . '
                        </td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Footer content to display when User is logged in</h2></th>
                    </tr>
                    <tr>
                        <td style="vertical-align: top">Logged In Footer:</td>
                        <td>
                            ' . $liveform->output_field(array('type'=>'textarea', 'name'=>'logged_in_footer', 'id'=>'logged_in_footer', 'style'=>'width: 600px; height: 200px')) . '
                            ' . get_codemirror_javascript(array('id' => 'logged_in_footer', 'code_type' => 'mixed', 'height' => '200px')) . '
                        </td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_save" value="Save" class="submit-primary" />&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="javascript:history.go(-1);" class="submit-secondary" />
                </div>
            </form>
        </div>' .
        output_footer();
    
    $liveform->remove_form();

// else, the form was submitted so create the login region
} else {
    validate_token_field();
    
    $liveform->add_fields_to_session();
    
    // validate the required fields
    $liveform->validate_required_field('name', 'Name is required.');
    
    // if there is an error, forward user back to form
    if ($liveform->check_form_errors() == true) {
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/add_login_region.php');
        exit();
    }
    
    // check to see if name is already in use
    $query =
        "SELECT id
        FROM login_regions
        WHERE (name = '" . escape($liveform->get_field_value('name')) . "')";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    // if name is already in use, prepare error and forward user back to form
    if (mysqli_num_rows($result) > 0) {
        $liveform->mark_error('name', 'The name that you entered is already in use, so please enter a different name.');
        
        // forward user back to form
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/add_login_region.php');
        exit();
    }
    
    // insert row into login_regions table
    $query = 
        "INSERT INTO login_regions
            (name, 
            not_logged_in_header,
            login_form,
            not_logged_in_footer,
            logged_in_header,
            logged_in_footer,
            created_user_id,
            created_timestamp,
            last_modified_user_id,
            last_modified_timestamp)
        VALUES
            ('" . escape($liveform->get_field_value('name')) . "',
            '" . escape($liveform->get_field_value('not_logged_in_header')) . "',
            '" . escape($liveform->get_field_value('login_form')) . "',
            '" . escape($liveform->get_field_value('not_logged_in_footer')) . "',
            '" . escape($liveform->get_field_value('logged_in_header')) . "',
            '" . escape($liveform->get_field_value('logged_in_footer')) . "',
            '" . $user['id'] . "',
            UNIX_TIMESTAMP(),
            '" . $user['id'] . "',
            UNIX_TIMESTAMP())";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed');
    
    log_activity('login region (' . $liveform->get_field_value('name') . ') was created', $_SESSION['sessionusername']);
    
    $liveform_view_regions = new liveform('view_regions');
    $liveform_view_regions->add_notice('The login region was created successfully.');
    
    header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_regions.php?filter=all_login_regions');
    
    $liveform->remove_form();
}
?>