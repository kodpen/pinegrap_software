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

function get_custom_form_confirmation_screen_content($properties)
{
    $current_page_id = $properties['current_page_id'];
    $continue_button_label = $properties['continue_button_label'];
    $next_page_id = $properties['next_page_id'];
    $next_page_name = get_page_name($next_page_id);
    $form_id = $properties['form_id'];
    
    // if the user came from the control panel, then return placeholder content
    if ((isset($_GET['from']) == true) && ($_GET['from'] == 'control_panel')) {
        return '<p class="software_notice">The submitted data for the Custom Form will be displayed here when this page is linked from a Custom Form Page Type.</p>';
    }

    $where = "";

    // If a form id has been passed like an API (e.g. for getting the page content
    // for an email where there won't be a reference code in the query string)
    // then use that to get the submitted form.
    if ($form_id != '') {
        $where = "forms.id = '" . escape($form_id) . "'";
    
    // Otherwise, a reference code should have been passed in the query string
    // so deal with that.
    } else {
        $_GET['r'] = trim($_GET['r']);

        // If there is no reference code, then output error.
        if ($_GET['r'] == '') {
            output_error('Sorry, it appears that a reference code was not included in the website address, so we don\'t know which confirmation to show you.');
        }

        // If the visitor has not submitted the form they requested in the current session,
        // then output error.
        if (
            (is_array($_SESSION['software']['submitted_form_reference_codes']) == false)
            || (in_array($_GET['r'], $_SESSION['software']['submitted_form_reference_codes']) == false)
        ) {
            output_error('Sorry, we can\'t show you the confirmation for that form, because it does not appear that you submitted the form, or your session might have expired.');
        }

        $where = "forms.reference_code = '" . escape($_GET['r']) . "'";
    }
    
    // get form information
    $query = "SELECT
                custom_form_pages.form_name,
                forms.id,
                forms.reference_code,
                forms.submitted_timestamp
             FROM forms
             LEFT JOIN custom_form_pages on forms.page_id = custom_form_pages.page_id
             WHERE $where";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // if a submitted form could not be found for this confirmation, output error
    if (mysqli_num_rows($result) == 0) {
        output_error('Sorry, we can\'t show you the confirmation because we can\'t find the form. The form might have recently been deleted.');
    }

    $row = mysqli_fetch_assoc($result);

    $form_name = $row['form_name'];
    $form_id = $row['id'];
    $reference_code = $row['reference_code'];
    $submitted_timestamp = $row['submitted_timestamp'];
    
    // get form data
    $query = "SELECT
                form_data.form_field_id,
                form_fields.label,
                form_fields.type,
                form_fields.wysiwyg,
                form_data.file_id,
                form_data.data,
                count(*) as number_of_values
             FROM form_data
             LEFT JOIN form_fields on form_data.form_field_id = form_fields.id
             WHERE (form_data.form_id = '$form_id') AND (form_fields.office_use_only = 0)
             GROUP BY form_data.form_field_id
             ORDER BY form_fields.sort_order";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    $fields = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $fields[] = $row;
    }

    foreach ($fields as $field) {
        // if field has file upload type
        if ($field['type'] == 'file upload') {
            // get file info
            $query = "SELECT name
                     FROM files
                     WHERE id = '" . escape($field['file_id']) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            $row = mysqli_fetch_assoc($result);
            
            $data = '<a href="' . OUTPUT_PATH . h($row['name']) . '" target="_blank">' . h($row['name']) . '</a>';
            
        // else field does not have file upload type
        } else {
            // if there is more than one value, get all values
            if ($field['number_of_values'] > 1) {
                $query = "SELECT data
                         FROM form_data
                         WHERE (form_id = '$form_id') AND (form_field_id = '" . $field['form_field_id'] . "')
                         ORDER BY id";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                $field['data'] = '';
                
                while ($row = mysqli_fetch_assoc($result)) {
                    $field['data'] .= $row['data'] . ', ';
                }
                
                // remove last comma and space
                $field['data'] = mb_substr($field['data'], 0, -2);
            }
            
            if ($field['wysiwyg'] == 1) {
                $data = prepare_form_data_for_output($field['data'], $field['type'], $prepare_for_html = false);
            } else {
                $data = prepare_form_data_for_output($field['data'], $field['type'], $prepare_for_html = true);
            }
        }
        
        $output_data .=
            '<tr>
                <td style="vertical-align: top">' . $field['label'] . '</td>
                <td style="vertical-align: top">' . $data . '</td>
             </tr>';
    }

    $output_auto_registration = '';

    // If a user account was created via the auto-registration feature,
    // then show user account info.
    if ($_SESSION['software']['custom_form_auto_registration'][$form_id]['email_address'] != '') {
        $output_auto_registration =
            '<div class="account heading" style="margin-top: 1em">New Account</div>
            <div class="account data">
                <p>We have created a new account for you on our site. You can find your login info below.</p>
                <p>
                    Email: ' . h($_SESSION['software']['custom_form_auto_registration'][$form_id]['email_address']) . '<br>
                    Password: ' . h($_SESSION['software']['custom_form_auto_registration'][$form_id]['password']) . '
                </p>
            </div>';
    }
    
    // if a next page was found prepare continue button
    if ($next_page_name) {
        // if a continue button label was entered for the page, then use that
        if ($continue_button_label) {
            $output_continue_button_label = h($continue_button_label);
        
        // else a continue button label could not be found, so use a default label
        } else {
            $output_continue_button_label = 'Continue';
        }
        
        $output_continue_button = '<div style="text-align: right; margin-bottom: 5px"><a href="' . OUTPUT_PATH . h($next_page_name) . '" class="software_button_primary">' . h($output_continue_button_label) . '</a></div>';

    // else a next page was not found
    } else {
        $output_continue_button = '';
    }
    
    $output =
        '<table style="margin-bottom: 15px">
            <tr>
                <td>Form:</td>
                <td>' . h($form_name) . '</td>
            </tr>
            <tr>
                <td>Reference Code:</td>
                <td>' . $reference_code . '</td>
            </tr>
            <tr>
                <td>Date Submitted:</td>
                <td>' . get_absolute_time(array('timestamp' => $submitted_timestamp, 'size' => 'long')) . '</td>
            </tr>
        </table>
        <table style="margin-bottom: 15px">
            ' . $output_data . '
        </table>
        ' . $output_auto_registration . '
        ' . $output_continue_button;
    
    return $output;
}
?>