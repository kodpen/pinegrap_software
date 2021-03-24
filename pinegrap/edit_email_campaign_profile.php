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
validate_email_access($user);

include_once('liveform.class.php');
$liveform = new liveform('edit_email_campaign_profile');

// Get profile data that we will use later.
$email_campaign_profile = db_item(
    "SELECT
        id,
        name,
        enabled,
        action,
        action_item_id,
        subject,
        format,
        body,
        page_id,
        from_name,
        from_email_address,
        reply_email_address,
        bcc_email_address,
        schedule_time,
        schedule_length,
        schedule_unit,
        schedule_period,
        schedule_base,
        purpose,
        created_user_id
    FROM email_campaign_profiles
    WHERE id = '" . escape($_REQUEST['id']) . "'");

// If the user does not have access to this profile, then output error.
if (
    (USER_ROLE == 3)
    && (USER_ID != $email_campaign_profile['created_user_id'])
) {
    log_activity('access denied to edit campaign profile because user does not have access to it', $_SESSION['sessionusername']);
    output_error('Access denied. <a href="javascript:history.go(-1)">Go back</a>.');
}

// If the form was not just submitted, then show form.
if (!$_POST) {
    // If the form has not been submitted at all yet, pre-populate fields with data.
    if ($liveform->field_in_session('id') == false) {
        $liveform->assign_field_value('name', $email_campaign_profile['name']);
        $liveform->assign_field_value('enabled', $email_campaign_profile['enabled']);
        $liveform->assign_field_value('action', $email_campaign_profile['action']);

        // Pre-populate fields differently based on the action.
        switch ($liveform->get_field_value('action')) {
            case 'calendar_event_reserved':
                $liveform->assign_field_value('calendar_event_id', $email_campaign_profile['action_item_id']);
                break;

            case 'custom_form_submitted':
                $liveform->assign_field_value('custom_form_page_id', $email_campaign_profile['action_item_id']);
                break;

            case 'email_campaign_sent':
                $liveform->assign_field_value('email_campaign_profile_id', $email_campaign_profile['action_item_id']);
                break;

            case 'product_ordered':
                $liveform->assign_field_value('product_id', $email_campaign_profile['action_item_id']);
                break;
        }

        $liveform->assign_field_value('subject', $email_campaign_profile['subject']);
        $liveform->assign_field_value('format', $email_campaign_profile['format']);
        $liveform->assign_field_value('body', $email_campaign_profile['body']);
        $liveform->assign_field_value('page_id', $email_campaign_profile['page_id']);
        $liveform->assign_field_value('bcc_email_address', $email_campaign_profile['bcc_email_address']);
        $liveform->assign_field_value('from_name', $email_campaign_profile['from_name']);
        $liveform->assign_field_value('from_email_address', $email_campaign_profile['from_email_address']);
        $liveform->assign_field_value('reply_email_address', $email_campaign_profile['reply_email_address']);

        // If the schedule time is not "00:00:00" then populate field.
        if ($email_campaign_profile['schedule_time'] != '00:00:00') {
            $liveform->assign_field_value('schedule_time', prepare_form_data_for_output($email_campaign_profile['schedule_time'], 'time'));
        }

        $liveform->assign_field_value('schedule_length', $email_campaign_profile['schedule_length']);
        $liveform->assign_field_value('schedule_unit', $email_campaign_profile['schedule_unit']);
        $liveform->assign_field_value('schedule_period', $email_campaign_profile['schedule_period']);
        $liveform->assign_field_value('schedule_base', $email_campaign_profile['schedule_base']);

        $liveform->set('purpose', $email_campaign_profile['purpose']);
    }

    // Prepare display property for various items.
    $output_calendar_event_id_row_style = ' style="display: none"';
    $output_custom_form_page_id_row_style = ' style="display: none"';
    $output_email_campaign_profile_id_row_style = ' style="display: none"';
    $output_product_id_row_style = ' style="display: none"';
    $output_body_row_style = ' style="display: none"';
    $output_page_id_row_style = ' style="display: none"';
    $output_body_preview_row_style = ' style="display: none"';
    $output_body_preview_iframe_source = '';
    $calendar_event_reserved_schedule_period_and_base_style = ' style="display: none"';
    $standard_schedule_period_and_base_style = ' style="display: none"';

    switch ($liveform->get_field_value('action')) {
        case 'calendar_event_reserved':
            $output_calendar_event_id_row_style = '';
            $calendar_event_reserved_schedule_period_and_base_style = '';
            break;

        case 'custom_form_submitted':
            $output_custom_form_page_id_row_style = '';
            $standard_schedule_period_and_base_style = '';
            break;

        case 'email_campaign_sent':
            $output_email_campaign_profile_id_row_style = '';
            $standard_schedule_period_and_base_style = '';
            break;

        case 'order_abandoned':
        case 'order_completed':
        case 'order_shipped':
            $standard_schedule_period_and_base_style = '';
            break;

        case 'product_ordered':
            $output_product_id_row_style = '';
            $standard_schedule_period_and_base_style = '';
            break;

        default:
            $standard_schedule_period_and_base_style = '';
            break;
    }

    switch ($liveform->get_field_value('format')) {
        case 'plain_text':
        default:
            $output_body_row_style = '';
            break;

        case 'html':
            $output_page_id_row_style = '';

            // If a body page is selected, then get page name.
            if ($liveform->get_field_value('page_id') != '') {
                $page_name = get_page_name($liveform->get_field_value('page_id'));

                // If a page name was found, then update body preview iframe with page.
                if ($page_name != '') {
                    $output_body_preview_row_style = '';
                    $output_body_preview_iframe_source = OUTPUT_PATH . h(encode_url_path($page_name)) . '?edit=no&amp;email=true';
                }
            }

            break;
    }

    $action_options = array(
        '' => '',
        'Auto Campaign Sent' => 'email_campaign_sent',
        'Calendar Event Reserved' => 'calendar_event_reserved',
        'Custom Form Submitted' => 'custom_form_submitted');

    $output_product_id_row = '';

    // If commerce is enabled and the user has access to commerce,
    // then add commerce actions and product id row.
    if ((ECOMMERCE == true) && (USER_MANAGE_ECOMMERCE == true)) {
        $action_options['Order Abandoned'] = 'order_abandoned';
        $action_options['Order Completed'] = 'order_completed';
        $action_options['Order Shipped'] = 'order_shipped';
        $action_options['Product Ordered'] = 'product_ordered';

        $output_product_id_row =
            '<tr id="product_id_row"' . $output_product_id_row_style . '>
                <td style="padding-left: 2em">Product:</td>
                <td>' . $liveform->output_field(array('type' => 'select', 'name' => 'product_id', 'options' => get_product_options())) . '</td>
            </tr>';
    }

    echo
        output_header() . '
        <div id="subnav">
            <h1>' . h($email_campaign_profile['name']) . '</h1>
        </div>
        <div id="button_bar">
            <a href="duplicate_email_campaign_profile.php?id=' . h($_GET['id']) . get_token_query_string_field() . '">Duplicate</a>
        </div>
        <div id="content">
            
            ' . $liveform->output_errors() . '
            ' . $liveform->output_notices() . '
            <a href="#" id="help_link">Help</a>
            <h1>Edit Campaign Profile</h1>
            <div class="subheading" style="margin-bottom: 1.5em">Modify the Campaign that is created automatically when a certain action is completed (e.g. Visitor reserves Calendar Event).</div>
            <form action="edit_email_campaign_profile.php" method="post">
                ' . get_token_field() . '
                ' . $liveform->output_field(array('type'=>'hidden', 'name'=>'id', 'value'=>$_GET['id'])) . '
                <table class="field" style="width: 100%">
                    <tr>
                        <td style="width: 20%">Name:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'name', 'size'=>'60', 'maxlength'=>'100')) . '</td>
                    </tr>
                    <tr>
                        <td><label for="enabled">Enable:</label></td>
                        <td>' . $liveform->output_field(array('type'=>'checkbox', 'id'=>'enabled', 'name'=>'enabled', 'value'=>'1', 'checked'=>'checked', 'class'=>'checkbox')) . '</td>
                    </tr>
                    <tr>
                        <td>Action:</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'id'=>'action', 'name'=>'action', 'options'=>$action_options, 'onchange'=>'change_email_campaign_profile_action()')) . '</td>
                    </tr>
                    <tr id="calendar_event_id_row"' . $output_calendar_event_id_row_style . '>
                        <td style="padding-left: 2em">Calendar Event:</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'name'=>'calendar_event_id', 'options'=>get_calendar_event_options(array('reservations' => true)))) . '</td>
                    </tr>
                    <tr id="custom_form_page_id_row"' . $output_custom_form_page_id_row_style . '>
                        <td style="padding-left: 2em">Custom Form:</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'name'=>'custom_form_page_id', 'options'=>get_page_options('', 'custom form'))) . '</td>
                    </tr>
                    <tr id="email_campaign_profile_id_row"' . $output_email_campaign_profile_id_row_style . '>
                        <td style="padding-left: 2em">Campaign Profile for<br />Campaign that was sent:</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'name'=>'email_campaign_profile_id', 'options'=>get_email_campaign_profile_options())) . '</td>
                    </tr>
                    ' . $output_product_id_row . '
                    <tr>
                        <th colspan="2"><h2>E-Mail Message</h2></th>
                    </tr>
                    <tr>
                        <td>Subject:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'subject', 'size'=>'80', 'maxlength'=>'255')) . '</td>
                    </tr>
                    <tr>
                        <td>Format:</td>
                        <td>' . $liveform->output_field(array('type'=>'radio', 'id'=>'format_plain_text', 'name'=>'format', 'value'=>'plain_text', 'checked'=>'checked', 'class'=>'radio', 'onclick' => 'show_or_hide_email_campaign_profile_format()')) . '<label for="format_plain_text">Plain Text</label> &nbsp;' . $liveform->output_field(array('type'=>'radio', 'id'=>'format_html', 'name'=>'format', 'value'=>'html', 'class'=>'radio', 'onclick' => 'show_or_hide_email_campaign_profile_format()')) . '<label for="format_html">HTML</label></td>
                    </tr>
                    <tr id="body_row"' . $output_body_row_style . '>
                        <td colspan="2">
                            <div style="margin-bottom: 1em">Body:</div>
                            ' . $liveform->output_field(array('type'=>'textarea', 'name'=>'body', 'style'=>'width: 99%; height: 300px')) . '
                        </td>
                    </tr>
                    <tr id="page_id_row"' . $output_page_id_row_style . '>
                        <td>My Page to Send:</td>
                        <td>' . $liveform->output_field(array('type'=>'select', 'id'=>'page_id', 'name'=>'page_id', 'options'=>get_page_options('', '', 'view'), 'onchange'=>'change_email_campaign_profile_page_id()')) . '
                    </tr>
                    <tr id="body_preview_row"' . $output_body_preview_row_style . '>
                        <td colspan="2">
                            <div style="margin-bottom: 1em">Body Preview:</div>
                            <iframe id="body_preview_iframe" src="' . $output_body_preview_iframe_source . '" style="width: 99%; height: 300px"></iframe>
                        </td>
                    </tr>
                    <tr>
                        <td>To:</td>
                        <td>Contact that triggers action.</td>
                    </tr>
                    <tr>
                        <td>BCC E-mail Address:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'bcc_email_address', 'size'=>'40', 'maxlength'=>'100')) . '</td>
                    </tr>
                    <tr>
                        <td>From Name:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'from_name', 'size'=>'40', 'maxlength'=>'100')) . '</td>
                    </tr>
                    <tr>
                        <td>From E-mail Address:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'from_email_address', 'size'=>'40', 'maxlength'=>'100')) . '</td>
                    </tr>
                    <tr>
                        <td>Reply to E-mail Address:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'reply_email_address', 'size'=>'40', 'maxlength'=>'100')) . '</td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>E-mail Schedule</h2></th>
                    </tr>
                    <tr>
                        <td>Send e-mail:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'schedule_length', 'value'=>'1', 'size'=>'3', 'maxlength'=>'9')) . ' ' . $liveform->output_field(array('type'=>'select', 'name'=>'schedule_unit', 'options'=>array('day(s)' => 'days', 'hour(s)' => 'hours'))) . ' <span id="calendar_event_reserved_schedule_period_and_base"' . $calendar_event_reserved_schedule_period_and_base_style . '>' . $liveform->output_field(array('type'=>'select', 'name'=>'schedule_period', 'options'=>array('' => '', 'before' => 'before', 'after' => 'after'))) . ' ' . $liveform->output_field(array('type'=>'select', 'name'=>'schedule_base', 'options'=>array('' => '', 'action' => 'action', 'calendar event start time' => 'calendar_event_start_time'))) . '</span><span id="standard_schedule_period_and_base"' . $standard_schedule_period_and_base_style . '>&nbsp;after action</span></td>
                    </tr>
                    <tr>
                        <td>Send at a specific time:</td>
                        <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'schedule_time', 'size'=>'8', 'maxlength'=>'8')) . ' (h:mm AM/PM, leave blank to send at any time)</td>
                    </tr>
                    <tr>
                        <th colspan="2"><h2>Purpose (as defined by the CAN-SPAM Act)</h2></th>
                    </tr>
                    <tr>
                        <td style="vertical-align: top">Purpose:</td>
                        <td>
                            <div>' .
                                $liveform->output_field(array(
                                    'type' => 'radio',
                                    'id' => 'purpose_commercial',
                                    'name' => 'purpose',
                                    'value' => 'commercial',
                                    'checked' => 'checked',
                                    'class' => 'radio')) .

                                '<label for="purpose_commercial">
                                    Commercial &nbsp;(send email to opted-in contacts only. Example: "We have an offer for you")
                                </label>
                            </div>

                            <div>' .
                                $liveform->output_field(array(
                                    'type' => 'radio',
                                    'id' => 'purpose_transactional',
                                    'name' => 'purpose',
                                    'value' => 'transactional',
                                    'class' => 'radio')) .

                                '<label for="purpose_transactional">
                                    Transactional &nbsp;(send email, regardless of opt-in. Example: "Your order has been shipped")
                                </label>
                            </div>
                        </td>
                    </tr>
                </table>
                <div class="buttons">
                    <input type="submit" name="submit_save" value="Save" class="submit-primary">&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="javascript:history.go(-1);" class="submit-secondary">&nbsp;&nbsp;&nbsp<input type="submit" name="submit_delete" value="Delete" class="delete" onclick="return confirm(\'WARNING: This campaign profile will be permanently deleted.\')" />
                </div>
            </form>
        </div>' .
        output_footer();
    
    $liveform->remove_form();

// Otherwise the form was just submitted, so process form.
} else {
    validate_token_field();
    
    $liveform->add_fields_to_session();

    // If the user selected to delete this profile, then delete it.
    if ($liveform->get_field_value('submit_delete') == 'Delete') {
        db("DELETE FROM email_campaign_profiles WHERE id = '" . escape($liveform->get_field_value('id')) . "'");
        
        log_activity('campaign profile (' . $email_campaign_profile['name'] . ') was deleted', $_SESSION['sessionusername']);
        
        $liveform_view_email_campaign_profiles = new liveform('view_email_campaign_profiles');
        $liveform_view_email_campaign_profiles->add_notice('The campaign profile has been deleted.');
        
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_email_campaign_profiles.php');

    // Otherwise the user selected to save the profile, so save it.
    } else {
        $liveform->validate_required_field('name', 'Name is required.');

        // If there is not already an error for the name field, and that name is already in use, then output error.
        if (
            ($liveform->check_field_error('name') == false)
            && (db_value("SELECT COUNT(*) FROM email_campaign_profiles WHERE (name = '" . escape($liveform->get_field_value('name')) . "') AND (id != '" . escape($liveform->get_field_value('id')) . "')") != 0)
        ) {
            $liveform->mark_error('name', 'The name that you entered is already in use, so please enter a different name.');
        }

        // If a commerce action was selected, and the user is not allowed to select a commerce action,
        // then clear value in order to generate an error.
        if (
            (
                $liveform->get('action') == 'order_abandoned'
                or $liveform->get('action') == 'order_completed'
                or $liveform->get('action') == 'order_shipped'
                or $liveform->get('action') == 'product_ordered'
            )
            and (!ECOMMERCE or !USER_MANAGE_ECOMMERCE)
        ) {
            $liveform->set('action', '');
        }

        $liveform->validate_required_field('action', 'Action is required.');

        // If there is not already an error for the action field, then validate sub-fields for it.
        if ($liveform->check_field_error('action') == false) {
            switch ($liveform->get_field_value('action')) {
                case 'calendar_event_reserved':
                    $liveform->validate_required_field('calendar_event_id', 'Calendar Event is required.');

                    // If there is not already an error for the calendar event field and the user does not have access to selected calendar event, then add error.
                    if (
                        ($liveform->check_field_error('calendar_event_id') == false)
                        && (validate_calendar_event_access($liveform->get_field_value('calendar_event_id')) == false)
                    ) {
                        log_activity('access denied for user to set calendar event for a campaign profile, because user does not have access to calendar event', $_SESSION['sessionusername']);
                        $liveform->mark_error('calendar_event_id', 'Sorry, you do not have access to that calendar event.');
                    }

                    break;

                case 'custom_form_submitted':
                    $liveform->validate_required_field('custom_form_page_id', 'Custom Form is required.');

                    // If there is not already an error for the custom form field and the user does not have edit access to the custom form, then add error.
                    if (
                        ($liveform->check_field_error('custom_form_page_id') == false)
                        && (check_edit_access(db_value("SELECT page_folder FROM page WHERE page_id = '" . escape($liveform->get_field_value('custom_form_page_id')) . "'")) == false)
                    ) {
                        log_activity('access denied for user to set custom form for a campaign profile, because user does not have edit access to custom form page', $_SESSION['sessionusername']);
                        $liveform->mark_error('custom_form_page_id', 'Sorry, you do not have access to that custom form.');
                    }

                    break;

                case 'email_campaign_sent':
                    $liveform->validate_required_field('email_campaign_profile_id', 'Campaign Profile is required.');

                    // If there is not already an error for the campaign profile field and the user does not have edit access to the campaign profile, then add error.
                    if (
                        ($liveform->check_field_error('email_campaign_profile_id') == false)
                        && (USER_ROLE == 3)
                        && (USER_ID != db_value("SELECT created_user_id FROM email_campaign_profiles WHERE id = '" . escape($liveform->get_field_value('email_campaign_profile_id')) . "'"))
                    ) {
                        log_activity('access denied for user to set campaign profile action for a campaign profile, because user does not have access to campaign profile', $_SESSION['sessionusername']);
                        $liveform->mark_error('email_campaign_profile_id', 'Sorry, you do not have access to that campaign profile.');
                    }
                
                    break;

                case 'product_ordered':
                    $liveform->validate_required_field('product_id', 'Product is required.');

                    // We don't have to check commerce access to product
                    // because we already did that above for the action.

                    break;
            }
        }

        $liveform->validate_required_field('subject', 'Subject is required.');
        $liveform->validate_required_field('format', 'Format is required.');

        // If there is not already an error for the format field and HTML format was selected, then validate page id field.
        if (
            ($liveform->check_field_error('format') == false)
            && ($liveform->get_field_value('format') == 'html')
        ) {
            $liveform->validate_required_field('page_id', 'My Page to Send is required.');

            // If there is not already an error for the page id field
            // and the user does not have view access to the selected page,
            // then log activity and add error.
            if (
                ($liveform->check_field_error('page_id') == false)
                && (check_view_access(db_value("SELECT page_folder FROM page WHERE page_id = '" . escape($liveform->get_field_value('page_id')) . "'")) == false)
            ) {
                log_activity('access denied for user to set page for a campaign profile, because user does not have view access to the page', $_SESSION['sessionusername']);
                $liveform->mark_error('page_id', 'You are not authorized to select that page.  Please select a different page.');
            }
        }

        // If a bcc e-mail address was entered and it is not valid, then add error.
        if (
            ($liveform->get_field_value('bcc_email_address') != '')
            && (validate_email_address($liveform->get_field_value('bcc_email_address')) == false)
        ) {
            $liveform->mark_error('bcc_email_address', 'Please enter a valid bcc e-mail address.');
        }

        $liveform->validate_required_field('from_name', 'From Name is required.');
        $liveform->validate_required_field('from_email_address', 'From E-mail Address is required.');

        // If there is not already an error for the from email address field and the from e-mail address is not valid, then add error.
        if (
            ($liveform->check_field_error('from_email_address') == false)
            && (validate_email_address($liveform->get_field_value('from_email_address')) == false)
        ) {
            $liveform->mark_error('from_email_address', 'Please enter a valid from e-mail address.');
        }

        // If a reply e-mail address was entered and it is not valid, then add error.
        if (
            ($liveform->get_field_value('reply_email_address') != '')
            && (validate_email_address($liveform->get_field_value('reply_email_address')) == false)
        ) {
            $liveform->mark_error('reply_email_address', 'Please enter a valid reply to e-mail address.');
        }

        // If a schedule time was entered and it is not valid, then add error.
        if (
            ($liveform->get_field_value('schedule_time') != '')
            && (validate_time($liveform->get_field_value('schedule_time')) == false)
        ) {
            $liveform->mark_error('schedule_time', 'Please enter a valid time.');
        }

        $liveform->validate_required_field('schedule_unit', 'Day(s)/Hour(s) is required.');

        // If calendar event reserved action was selected, then validate fields for that.
        if ($liveform->get_field_value('action') == 'calendar_event_reserved') {
            $liveform->validate_required_field('schedule_period', 'Before/After is required.');
            $liveform->validate_required_field('schedule_base', 'Action/Calendar event start time is required.');

            // If there is not already an error for the period and base fields, and the user selected "before" and "action",
            // then add error because that is not valid.  Eventually we should spend the time to solve this on the front-end
            // instead of letting this error occur.
            if (
                ($liveform->check_field_error('schedule_period') == false)
                && ($liveform->check_field_error('schedule_base') == false)
                && ($liveform->get_field_value('schedule_period') == 'before')
                && ($liveform->get_field_value('schedule_base') == 'action')
            ) {
                $liveform->mark_error('schedule_period', 'Sorry, you may not select "before action".  Try setting "after action".');
                $liveform->mark_error('schedule_base', '');
            }
        }

        $liveform->validate_required_field('purpose', 'Purpose is required.');

        // If there is an error, forward user back to previous screen.
        if ($liveform->check_form_errors() == true) {
            header('Location: ' . URL_SCHEME . HOSTNAME . $_SERVER['PHP_SELF'] . '?id=' . $liveform->get_field_value('id'));
            exit();
        }

        $action_item_id = '';
        $schedule_period = '';
        $schedule_base = '';

        // Set properties differently based on the selected action.
        switch ($liveform->get_field_value('action')) {
            case 'calendar_event_reserved':
                $action_item_id = $liveform->get_field_value('calendar_event_id');
                $schedule_period = $liveform->get_field_value('schedule_period');
                $schedule_base = $liveform->get_field_value('schedule_base');
                break;

            case 'custom_form_submitted':
                $action_item_id = $liveform->get_field_value('custom_form_page_id');
                $schedule_period = 'after';
                $schedule_base = 'action';
                break;

            case 'email_campaign_sent':
                $action_item_id = $liveform->get_field_value('email_campaign_profile_id');
                $schedule_period = 'after';
                $schedule_base = 'action';
                break;

            case 'order_abandoned':
            case 'order_completed':
            case 'order_shipped':
                $action_item_id = '0';
                $schedule_period = 'after';
                $schedule_base = 'action';
                break;

            case 'product_ordered':
                $action_item_id = $liveform->get_field_value('product_id');
                $schedule_period = 'after';
                $schedule_base = 'action';
                break;
        }

        // If the user set the schedule time to "12:00 AM", then force the time to 12:01 AM,
        // because when 12:00 AM is stored in the database, then we assume that means they don't want a schedule time.
        if (mb_strtolower($liveform->get_field_value('schedule_time')) == '12:00 am') {
            $schedule_time = '00:01:00';

        // Otherwise the user did not set the schedule to "12:00 AM", so prepare value like normal.
        } else {
            $schedule_time = prepare_form_data_for_input($liveform->get_field_value('schedule_time'), 'time');
        }
        
        // Update profile.
        db(
            "UPDATE email_campaign_profiles
            SET
                name = '" . escape($liveform->get_field_value('name')) . "',
                enabled = '" . escape($liveform->get_field_value('enabled')) . "',
                action = '" . escape($liveform->get_field_value('action')) . "',
                action_item_id = '" . escape($action_item_id) . "',
                subject = '" . escape($liveform->get_field_value('subject')) . "',
                format = '" . escape($liveform->get_field_value('format')) . "',
                body = '" . escape($liveform->get_field_value('body')) . "',
                page_id = '" . escape($liveform->get_field_value('page_id')) . "',
                from_name = '" . escape($liveform->get_field_value('from_name')) . "',
                from_email_address = '" . escape($liveform->get_field_value('from_email_address')) . "',
                reply_email_address = '" . escape($liveform->get_field_value('reply_email_address')) . "',
                bcc_email_address = '" . escape($liveform->get_field_value('bcc_email_address')) . "',
                schedule_time = '" . escape($schedule_time) . "',
                schedule_length = '" . escape($liveform->get_field_value('schedule_length')) . "',
                schedule_unit = '" . escape($liveform->get_field_value('schedule_unit')) . "',
                schedule_period = '" . escape($schedule_period) . "',
                schedule_base = '" . escape($schedule_base) . "',
                purpose = '" . e($liveform->get('purpose')) . "',
                last_modified_user_id = '" . USER_ID . "',
                last_modified_timestamp = UNIX_TIMESTAMP()
            WHERE id = '" . escape($liveform->get_field_value('id')) . "'");

        log_activity('campaign profile (' . $liveform->get_field_value('name') . ') was modified', $_SESSION['sessionusername']);
        
        $liveform_view_email_campaign_profiles = new liveform('view_email_campaign_profiles');
        $liveform_view_email_campaign_profiles->add_notice('The campaign profile has been saved.');

        // Forward user to view email campaign profiles screen.
        header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/view_email_campaign_profiles.php');
    }

    $liveform->remove_form();
    exit();
}