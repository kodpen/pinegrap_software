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

function get_affiliate_sign_up_form_screen_content($properties)
{
    $current_page_id = $properties['current_page_id'];
    $terms_page_id = $properties['terms_page_id'];
    $terms_page_name = get_page_name($terms_page_id);
    $submit_button_label = $properties['submit_button_label'];
    $next_page_id = $properties['next_page_id'];

    include_once('liveform.class.php');
    $liveform = new liveform('affiliate_sign_up_form');
    
    // get user information to find contact
    $query = "SELECT user_id, user_email, user_contact FROM user WHERE user_username = '" . escape($_SESSION['sessionusername']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    $user_id = $row['user_id'];
    $contact_id = $row['user_contact'];

    // if form has not been submitted yet, then populate fields with data
    if ($liveform->field_in_session('submit') == false) {
        // get contact information
        $query = "SELECT
                    first_name,
                    last_name,
                    business_address_1,
                    business_address_2,
                    business_city,
                    business_state,
                    business_zip_code,
                    business_country,
                    business_phone,
                    business_fax,
                    email_address,
                    company,
                    website
                 FROM contacts
                 WHERE id = '" . $contact_id . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        
        // assign form values
        $liveform->assign_field_value('first_name', $row['first_name']);
        $liveform->assign_field_value('last_name', $row['last_name']);
        $liveform->assign_field_value('address_1', $row['business_address_1']);
        $liveform->assign_field_value('address_2', $row['business_address_2']);
        $liveform->assign_field_value('city', $row['business_city']);
        $liveform->assign_field_value('state', $row['business_state']);
        $liveform->assign_field_value('zip_code', $row['business_zip_code']);
        $liveform->assign_field_value('country', $row['business_country']);
        $liveform->assign_field_value('phone_number', $row['business_phone']);
        $liveform->assign_field_value('fax_number', $row['business_fax']);
        $liveform->assign_field_value('email_address', $row['email_address']);
        $liveform->assign_field_value('affiliate_name', $row['company']);
        
        if ($row['website']) {
            $liveform->assign_field_value('affiliate_website', $row['website']);
        } else {
            $liveform->assign_field_value('affiliate_website', 'http://');
        }
    }

    // if country is blank, set default country
    if (!$liveform->get_field_value('country')) {
        $query = "SELECT code FROM countries WHERE default_selected = 1";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        // if a default country was found, set default country
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $liveform->assign_field_value('country', $row['code']);
        }
    }

    $system = '';
    
    // set blank to be the first option of the country selection drop-down
    $country_options[''] = '';

    // get countries for country selection
    $countries =
        db_items("SELECT name, code, zip_code_required AS zip FROM countries ORDER BY name",
            'code');

    foreach ($countries as $country) {
        $country_options[$country['name']] = $country['code'];
    }

    $states = db_items(
        "SELECT
            states.name,
            states.code,
            countries.code AS country_code
        FROM states
        LEFT JOIN countries ON countries.id = states.country_id
        ORDER BY states.name");

    // Loop through the states in order to add them to countries.
    foreach ($states as $state) {
        $countries[$state['country_code']]['states'][] = array(
            'name' => $state['name'],
            'code' => $state['code']);
    }

    $system .=
        '<script>
            software.countries = ' . encode_json($countries) . ';
            software.init_country({
                country_id: "country",
                state_text_box_id: "state_text_box",
                state_pick_list_id: "state_pick_list",
                zip_code_id: "zip_code"});
        </script>';

    // if a terms page is selected for this page then prepare output for terms checkbox
    if ($terms_page_name) {
        $output_terms = '<div style="margin-bottom: 15px">' . $liveform->output_field(array('type'=>'checkbox', 'id'=>'terms_and_conditions', 'name'=>'terms_and_conditions', 'class'=>'software_input_checkbox')) . '<label for="terms_and_conditions"> I agree to the </label><a href="' . OUTPUT_PATH . $terms_page_name . '" target="_blank"> terms and conditions</a>.</div>';
    }
    
    // if a submit button label was entered for the page, then use that
    if ($submit_button_label) {
        $output_submit_button_label = h($submit_button_label);
    
    // else a submit button label could not be found, so use a default label
    } else {
        $output_submit_button_label = 'Sign Up';
    }
    
    // we are limiting the affiliate code to 50 characters because the key code database field only supports 50 characters (group offer feature will put affiliate code in key code)

    $output .=
        $liveform->output_errors() . '
        <form action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/affiliate_sign_up_form.php" method="post">
            ' . get_token_field() . '
            <input type="hidden" name="current_page_id" value="' . $current_page_id . '" />
            <input type="hidden" name="next_page_id" value="' . $next_page_id . '" />
            <table style="margin-bottom: 15px">
                <tr>
                    <td>First Name*</td>
                    <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'first_name', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                </tr>
                <tr>
                    <td>Last Name*</td>
                    <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'last_name', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                </tr>
                <tr>
                    <td>Address 1*</td>
                    <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'address_1', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                </tr>
                <tr>
                    <td>Address 2</td>
                    <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'address_2', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                </tr>
                <tr>
                    <td>City*</td>
                    <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'city', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                </tr>
                <tr>
                    <td>Country*</td>
                    <td>' . $liveform->output_field(array('type'=>'select', 'name'=>'country', 'id'=>'country', 'options'=>$country_options, 'class'=>'software_select')) . '</td>
                </tr>
                <tr>
                    <td>
                        <label for="state_text_box">
                            State / Province
                        </label>

                        <label for="state_pick_list" style="display: none">
                            State / Province*
                        </label>
                    </td>
                    <td>' .
                        $liveform->output_field(array('type'=>'text', 'id'=> 'state_text_box', 'name'=> 'state', 'maxlength'=>'50', 'class'=>'software_input_text')) .

                        $liveform->output_field(array('type'=>'select', 'id'=> 'state_pick_list', 'name'=> 'state', 'class'=>'software_select')) . '
                    </td>
                </tr>
                <tr>
                    <td>Zip / Postal Code<span id="zip_code_required" style="display: none">*</span></td>
                    <td>' . $liveform->output_field(array('type'=>'text', 'id' => 'zip_code', 'name'=>'zip_code', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                </tr>
                <tr>
                    <td>Phone*</td>
                    <td>' . $liveform->output_field(array('type'=>'tel', 'name'=>'phone_number', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                </tr>
                <tr>
                    <td>Fax</td>
                    <td>' . $liveform->output_field(array('type'=>'tel', 'name'=>'fax_number', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                </tr>
                <tr>
                    <td>Email*</td>
                    <td>' . $liveform->output_field(array('type'=>'email', 'name'=>'email_address', 'size'=>'30', 'maxlength'=>'100', 'class'=>'software_input_text')) . '</td>
                </tr>
                <tr>
                    <td>Affiliate / Company Name*</td>
                    <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'affiliate_name', 'size'=>'30', 'maxlength'=>'100', 'class'=>'software_input_text')) . '</td>
                </tr>
                <tr>
                    <td>Affiliate Website</td>
                    <td>' . $liveform->output_field(array('type'=>'url', 'name'=>'affiliate_website', 'value'=>'http://', 'size'=>'40', 'maxlength'=>'255', 'class'=>'software_input_text')) . '</td>
                </tr>
                <tr>
                    <td colspan="2">Create your own Affiliate Code to share with others.</td>
                </tr>
                <tr>
                    <td>Affiliate Code*</td>
                    <td>' . $liveform->output_field(array('type'=>'text', 'name'=>'affiliate_code', 'size'=>'30', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                </tr>
            </table>
            ' . $output_terms . '
            <div style="text-align: left">
                <input type="submit" name="submit" value="' . $output_submit_button_label . '" class="software_input_submit_primary submit_button" />
            </div>
            ' . $system . '
        </form>';

    return $output;
}