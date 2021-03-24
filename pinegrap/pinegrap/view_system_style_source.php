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
$liveform = new liveform('view_system_style_source', $_REQUEST['id']);
$liveform->assign_field_value('id', $_REQUEST['id']);
$liveform->assign_field_value('send_to', $_REQUEST['send_to']);

// get style information
$query = 
    "SELECT
       style.style_name as name,
       style.style_head as head,
       style.style_code as code,
       style.style_timestamp as last_modified_timestamp,
       user.user_username as last_modified_username
   FROM style
   LEFT JOIN user ON style.style_user = user.user_id
   WHERE style.style_id = '" . escape($liveform->get_field_value('id')) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);

$name = $row['name'];
$head = $row['head'];
$code = $row['code'];
$last_modified_timestamp = $row['last_modified_timestamp'];
$last_modified_username = $row['last_modified_username'];

// if the form was not just submitted, then prepare to output form
if (!$_POST) {
    // if there is not any head content, then add placeholder to head
    if ($head == '') {
        $code = str_replace('</head>', '    <!-- Additional Head Content Will Go Here -->' . "\n" . '            </head>', $code);
        
    // else there is head content, so add start and stop indicators
    } else {
        $code = str_replace('</head>', '    <!-- Additional Head Content Starts Here -->' . "\n" . $head . "\n" . '                <!-- Additional Head Content Ends Here -->' . "\n" . '            </head>', $code);
    }
    
    // store field values
    $liveform->assign_field_value('head', $head);
    $liveform->assign_field_value('code', $code);
    
    print
        output_header() . '
        <div id="subnav">
            <h1>' . h($name) . '</h1>
            <div class="subheading">Last Modified ' . get_relative_time(array('timestamp' => $last_modified_timestamp)) . ' by ' . h($last_modified_username) . '</div>
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>View Source</h1>
            <div class="subheading" style="margin-bottom: 1.5em">View the HTML Source for this System Page Style and optionally insert additional head content.</div>
            <form action="view_system_style_source.php" method="post" style="margin-bottom: 2em">
                ' . get_token_field() . '
                ' . $liveform->output_field(array('type'=>'hidden', 'name'=>'id')) . '
                ' . $liveform->output_field(array('type'=>'hidden', 'name'=>'send_to')) . '
                <h2 style="margin-bottom: 1em">Additional Head Content</h2>
                <div style="margin-bottom: 1em">' . $liveform->output_field(array('type'=>'textarea', 'name'=>'head', 'id'=>'head')) . '</div>
                ' . get_codemirror_includes() . '
                ' . get_codemirror_javascript(array('id' => 'head', 'code_type' => 'mixed', 'height' => '150px', 'readonly' => false)) . '
                <div class="buttons">
                    <input type="submit" name="submit_save_and_return" value="Save &amp; Return" class="submit-primary" />&nbsp;&nbsp;&nbsp;<input type="submit" name="submit_save" value="Save" class="submit-secondary" />&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="window.location.href=\'' . h(escape_javascript($liveform->get_field_value('send_to'))) . '\'" class="submit-secondary" />
                </div>
            </form>
            <h2 style="margin-bottom: 1em">HTML Source (read-only)</h2>
            <div style="margin-bottom: 1em">' . $liveform->output_field(array('type'=>'textarea', 'name'=>'code', 'id'=>'code')) . '</div>
            ' . get_codemirror_javascript(array('id' => 'code', 'code_type' => 'mixed', 'height' => '400px', 'readonly' => true)) . '
        </div>' .
        output_footer();
    
    $liveform->remove_form();

// else the form has been submitted, so process the form
} else {

    validate_token_field();
    
    $liveform->add_fields_to_session();
    
    // update page style in database
    $query =
        "UPDATE style
        SET
            style_head = '" . escape($liveform->get_field_value('head')) . "',
            style_user = '" . $user['id'] . "',
            style_timestamp = UNIX_TIMESTAMP()
        WHERE style_id = '" . $liveform->get_field_value('id') . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    log_activity('additional head content for style (' . $name . ') was modified', $_SESSION['sessionusername']);
    
    $notice = 'The additional head content has been saved.';
    
    // if the user clicked the Save & Preview button, then add notice and forward user to this same screen again
    if ($liveform->get_field_value('submit_save') == 'Save') {
        $liveform->add_notice($notice);
        
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_system_style_source.php?id=' . $liveform->get_field_value('id') . '&send_to=' . urlencode($liveform->get_field_value('send_to')));
        
    // else the user clicked the Save & Return button, so add notice and forward user back to edit system style screen
    } else {
        $liveform_edit_system_style = new liveform('edit_system_style', $liveform->get_field_value('id'));
        $liveform_edit_system_style->add_notice($notice);
        
        header('Location: ' . URL_SCHEME . HOSTNAME . $liveform->get_field_value('send_to'));
        
        $liveform->remove_form();
    }
}
?>