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
$liveform = new liveform('add_style');

// if the form has not been submitted, then prepare to output form
if (!$_POST) {
    // assume that we will not show the layout until we find out otherwise
    $output_layout_style = '; display: none';
    
    // if the system type has been selected already, then prepare to show layout
    if ($liveform->get_field_value('type') == 'system') {
        $output_layout_style = '';
    }
    
    print
        output_header() . '
        <div id="subnav">
            <h1>[new page style]</h1>
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Create Page Style</h1>
            <div class="subheading" style="margin-bottom: 1.5em">Create a new HTML template that can be associated with one or many Pages.</div>
            <form name="form" action="add_style.php" method="post" id="question_text">
                ' . get_token_field() . '
                <div style="margin-bottom: 2em">What type of Page Style do you want to create?</div>
                <div style="margin-bottom: 1.5em; margin-left: 1em">' . $liveform->output_field(array('type'=>'radio', 'name'=>'type', 'id'=>'custom', 'value'=>'custom', 'class'=>'radio', 'checked' => 'checked', 'onclick'=>'show_or_hide_style_type(\'custom\')')) . '<label for="custom"> Custom (enter your own HTML; good for responsive page design)</label></div>
                <div style="margin-bottom: 2em; margin-left: 1em">' . $liveform->output_field(array('type'=>'radio', 'name'=>'type', 'id'=>'system', 'value'=>'system', 'class'=>'radio', 'onclick'=>'show_or_hide_style_type(\'system\')')) . '<label for="system"> System (use Style Designer; good for adaptive page design)</label></div>
                <div id="layout" style="margin-left: 3em; margin-bottom: 2em' . $output_layout_style . '">
                    <div style="margin-bottom: .7em">Which Page Layout would you like to use?</div>
                    <table>
                        <tr>
                            <td style="padding-right: 2em">' . $liveform->output_field(array('type'=>'radio', 'name'=>'layout', 'id'=>'one_column', 'value'=>'one_column', 'class'=>'radio')) . '<label for="one_column"> 1 Column</label></td>
                            <td style="padding-right: 2em">' . $liveform->output_field(array('type'=>'radio', 'name'=>'layout', 'id'=>'one_column_email', 'value'=>'one_column_email', 'class'=>'radio')) . '<label for="one_column_email"> 1 Column, E-mail</label></td>
                            <td style="padding-right: 2em">' . $liveform->output_field(array('type'=>'radio', 'name'=>'layout', 'id'=>'one_column_mobile', 'value'=>'one_column_mobile', 'class'=>'radio')) . '<label for="one_column_mobile"> 1 Column, Mobile</label></td>
                            <td style="padding-right: 2em">' . $liveform->output_field(array('type'=>'radio', 'name'=>'layout', 'id'=>'two_column_sidebar_left', 'value'=>'two_column_sidebar_left', 'class'=>'radio')) . '<label for="two_column_sidebar_left"> 2 Column, Sidebar Left</label></td>
                            <td style="padding-right: 2em">' . $liveform->output_field(array('type'=>'radio', 'name'=>'layout', 'id'=>'two_column_sidebar_right', 'value'=>'two_column_sidebar_right', 'class'=>'radio')) . '<label for="two_column_sidebar_right"> 2 Column, Sidebar Right</label></td>
                            <td>' . $liveform->output_field(array('type'=>'radio', 'name'=>'layout', 'id'=>'three_column_sidebar_left', 'value'=>'three_column_sidebar_left', 'class'=>'radio')) . '<label for="three_column_sidebar_left"> 3 Column, Sidebar Left</label></td>
                        </tr>
                        <tr>
                            <td style="text-align: center"><label for="one_column"><img src="images/layout_one_column.png" width="88" height="92" alt="1 Column" title="" /></label></td>
                            <td style="text-align: center"><label for="one_column_email"><img src="images/layout_one_column_email.png" width="64" height="89" alt="1 Column, E-mail" title="" /></label></td>
                            <td style="text-align: center"><label for="one_column_mobile"><img src="images/layout_one_column_mobile.png" width="32" height="60" alt="1 Column, Mobile" title="" /></label></td>
                            <td style="text-align: center"><label for="two_column_sidebar_left"><img src="images/layout_two_column_sidebar_left.png" width="88" height="92" alt="2 Column, Sidebar Left" title="" /></label></td>
                            <td style="text-align: center"><label for="two_column_sidebar_right"><img src="images/layout_two_column_sidebar_right.png" width="88" height="92" alt="2 Column, Sidebar Right" title="" /></label></td>
                            <td style="text-align: center"><label for="three_column_sidebar_left"><img src="images/layout_three_column_sidebar_left.png" width="88" height="92" alt="3 Column, Sidebar Left" title="" /></label></td>
                        </tr>
                    </table>
                </div>
                <div class="buttons">
                    <input type="submit" name="submit_continue" value="Continue" class="submit-primary" />&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="javascript:history.go(-1);" class="submit-secondary" />
                </div>
            </form>
        </div>' .
        output_footer();
    
    $liveform->remove_form();

// else the form has been submitted, so process the form
} else {
    validate_token_field();
    
    $liveform->add_fields_to_session();
    
    $liveform->validate_required_field('type', 'Please select a type.');
    
    // if the system type was selected, then require layout
    if ($liveform->get_field_value('type') == 'system') {
        $liveform->validate_required_field('layout', 'Please select a Page Layout.');
    }
    
    // if there is an error, forward user back to the previous screen
    if ($liveform->check_form_errors() == true) {
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/add_style.php');
        exit();
    }
    
    // if the user selected a system type, then forward user to create system style
    if ($liveform->get_field_value('type') == 'system') {
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/add_system_style.php?layout=' . $liveform->get_field_value('layout'));
        
    // else the user select a custom type, so forward user to create custom style
    } else {
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/add_custom_style.php');
    }
    
    $liveform->remove_form();
}
?>