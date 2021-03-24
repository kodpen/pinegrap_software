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

function get_my_account_profile($properties = array()) {

    $page_id = $properties['page_id'];

    $layout_type = get_layout_type($page_id);

    // get user information to find contact
    $query = "SELECT user_contact, timezone FROM user WHERE user_username = '" . escape($_SESSION['sessionusername']) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed');
    $row = mysqli_fetch_assoc($result);
    $contact_id = $row['user_contact'];
    $timezone = $row['timezone'];

    // get contact information
    $query = "SELECT
                salutation,
                first_name,
                last_name,
                suffix,
                title,
                company,
                business_address_1,
                business_address_2,
                business_city,
                business_state,
                business_zip_code,
                business_phone,
                home_phone,
                mobile_phone,
                business_fax,
                business_country
             FROM contacts
             WHERE id = '" . $contact_id . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed');
    $row = mysqli_fetch_assoc($result);

    $salutation = $row['salutation'];
    $first_name = $row['first_name'];
    $last_name = $row['last_name'];
    $suffix = $row['suffix'];
    $company = $row['company'];
    $title = $row['title'];
    $business_address_1 = $row['business_address_1'];
    $business_address_2 = $row['business_address_2'];
    $business_city = $row['business_city'];
    $business_state = $row['business_state'];
    $business_zip_code = $row['business_zip_code'];
    $business_phone = $row['business_phone'];
    $business_fax = $row['business_fax'];
    $business_country = $row['business_country'];
    $home_phone = $row['home_phone'];
    $mobile_phone = $row['mobile_phone'];
    
    $form = new liveform('my_account_profile');

    $show_timezone = false;
    $timezone_options = array();

    // If this PHP version supports user timezones then output timezone row.
    if (version_compare(PHP_VERSION, '5.2.0', '>=')) {

        $show_timezone = true;

        $timezones = get_timezones();

        $timezone_options = array();

        // Get the site's time zone.
        if (TIMEZONE != '') {
            $site_timezone = TIMEZONE;
        } else {
            $site_timezone = SERVER_TIMEZONE;
        }

        // Check to see if the site's timezone is one of the timezones in our pick list
        // and get the label if it exists.
        $site_timezone_label = array_search($site_timezone, $timezones);

        // If a label could not be found then just use the actual site's timezone for the label.
        if (!$site_timezone_label) {
            $site_timezone_label = $site_timezone;
        }

        $timezone_options['Default: ' . h($site_timezone_label)] = '';

        $timezone_options = array_merge($timezone_options, $timezones);
    }

    // if form has not been submitted yet, then populate fields with data
    if ($form->field_in_session('salutation') == false) {
        $form->assign_field_value('salutation', $salutation);
        $form->assign_field_value('first_name', $first_name);
        $form->assign_field_value('last_name', $last_name);
        $form->assign_field_value('suffix', $suffix);
        $form->assign_field_value('company', $company);
        $form->assign_field_value('business_address_1', $business_address_1);
        $form->assign_field_value('business_address_2', $business_address_2);
        $form->assign_field_value('business_city', $business_city);
        $form->assign_field_value('business_state', $business_state);
        $form->assign_field_value('business_zip_code', $business_zip_code);
        $form->assign_field_value('business_phone', $business_phone);
        $form->assign_field_value('business_fax', $business_fax);
        $form->assign_field_value('business_country', $business_country);
        $form->assign_field_value('title', $title);
        $form->assign_field_value('home_phone', $home_phone);
        $form->assign_field_value('mobile_phone', $mobile_phone);

        // if country is blank, set default country
        if (!$form->get_field_value('business_country')) {
            $query = "SELECT code FROM countries WHERE default_selected = 1";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            // if a default country was found, set default country
            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $form->assign_field_value('business_country', $row['code']);
            }
        }

        // If we are going to show timezone field then populate timezone field.
        if ($show_timezone) {
            $form->assign_field_value('timezone', $timezone);
        }
    }

    $system = '';

    // set blank to be the first option of the county selection drop-down
    $business_country_options[''] = '';

    // get countries for country selection
    $countries = db_items("SELECT name, code, zip_code_required AS zip FROM countries ORDER BY name", 'code');

    foreach ($countries as $country) {
        $business_country_options[$country['name']] = $country['code'];
    }

    $states = db_items(
        "SELECT
            states.name,
            states.code,
            countries.code AS country_code
        FROM states
        LEFT JOIN countries ON countries.id = states.country_id
        ORDER BY states.name");

    // Loop through the states in order to add them to countries,
    // so that we can pass them to the init_country() JS function.
    foreach ($states as $state) {
        $countries[$state['country_code']]['states'][] = array(
            'name' => $state['name'],
            'code' => $state['code']);
    }

    $system .=
        '<script>
            software.countries = ' . encode_json($countries) . ';
            software.init_country({
                country_id: "business_country",
                state_text_box_id: "business_state_text_box",
                state_pick_list_id: "business_state_pick_list",
                zip_code_id: "business_zip_code"});
        </script>';

    // If the layout type is system or if there is no my account profile page,
    // and therefore no layout setting, then use system layout.
    if ($layout_type == 'system' || !$layout_type) {

        $output_timezone_row = '';

        // If we are going to show timezone field, then show it.
        if ($show_timezone) {
            $output_timezone_row =
                '<tr>
                    <td>Timezone</td>
                    <td>' . $form->output_field(array('type' => 'select', 'name' => 'timezone', 'options' => $timezone_options, 'class' => 'software_select')) . '</td>
                </tr>';
        }

        $output .=
            $form->output_errors() . '
            <form action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/my_account_profile.php" method="post" class="software">
                ' . get_token_field() . '
                <table style="margin-bottom: 15px">
                    <tr>
                        <td style="width: 50%; vertical-align: top; padding-right: 20px">       
                            <div class="contact" style="margin-bottom:2em;">
                                <div class="heading" style="margin-bottom: 10px">Contact Information</div>
                                <table class="data">
                                    <tr>
                                        <td>Salutation</td>
                                        <td>' . $form->output_field(array('type'=>'select', 'name'=>'salutation', 'options'=>get_salutation_options(), 'class'=>'software_select')) . '</td>
                                    </tr>
                                    <tr>
                                        <td>First Name*</td>
                                        <td>' . $form->output_field(array('type'=>'text', 'name'=>'first_name', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                                    </tr>
                                    <tr>
                                        <td>Last Name*</td>
                                        <td>' . $form->output_field(array('type'=>'text', 'name'=>'last_name', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                                    </tr>
                                    <tr>
                                        <td>Suffix</td>
                                        <td>' . $form->output_field(array('type'=>'select', 'name'=>'suffix', 'options'=>get_suffix_options(), 'class'=>'software_select')) . '</td>
                                    </tr>
                                    <tr>
                                        <td>Title</td>
                                        <td>' . $form->output_field(array('type'=>'text', 'name'=>'title', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                                    </tr>
                                    <tr>
                                        <td>Main Phone</td>
                                        <td>' . $form->output_field(array('type'=>'tel', 'name'=>'business_phone', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                                    </tr>
                                    <tr>
                                        <td>Mobile Phone&nbsp;</td>
                                        <td>' . $form->output_field(array('type'=>'tel', 'name'=>'mobile_phone', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                                    </tr>
                                    <tr>
                                        <td>Home Phone</td>
                                        <td>' . $form->output_field(array('type'=>'tel', 'name'=>'home_phone', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                                    </tr>                                <tr>
                                        <td>Fax</td>
                                        <td>' . $form->output_field(array('type'=>'tel', 'name'=>'business_fax', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="billing" style="margin-bottom: 1em">
                                <div class="heading" style="margin-bottom: 10px">Billing / Mailing Address</div>
                                <table class="data">
                                    <tr>
                                        <td>Organization</td>
                                        <td>' . $form->output_field(array('type'=>'text', 'name'=>'company', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                                    </tr>
                                    <tr>
                                        <td>Address</td>
                                        <td>' . $form->output_field(array('type'=>'text', 'name'=>'business_address_1', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>' . $form->output_field(array('type'=>'text', 'name'=>'business_address_2', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                                    </tr>
                                    <tr>
                                        <td>City</td>
                                        <td>' . $form->output_field(array('type'=>'text', 'name'=>'business_city', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                                    </tr>
                                    <tr>
                                        <td>Country</td>
                                        <td>' . $form->output_field(array('type'=>'select', 'name'=>'business_country', 'id'=>'business_country', 'options'=>$business_country_options, 'class'=>'software_select')) . '</td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <label for="business_state_text_box">
                                                State / Province
                                            </label>

                                            <label for="business_state_pick_list" style="display: none">
                                                State / Province
                                            </label>
                                        </td>
                                        <td>' .
                                            $form->output_field(array('type'=>'text', 'id'=> 'business_state_text_box', 'name'=> 'business_state', 'maxlength'=>'50', 'class'=>'software_input_text')) .

                                            $form->output_field(array('type'=>'select', 'id'=> 'business_state_pick_list', 'name'=> 'business_state', 'class'=>'software_select')) . '
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Zip / Postal Code&nbsp;</td>
                                        <td>' . $form->output_field(array('type'=>'text', 'id' => 'business_zip_code', 'name'=>'business_zip_code', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                                    </tr>
                                    ' . $output_timezone_row . '
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>
                <input type="submit" name="submit" value="Update" class="software_input_submit_primary update_button" />&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="javascript:window.location.href=\'' . h(escape_javascript(get_page_type_url('my account'))) . '\'" class="software_input_submit_secondary cancel_button">
                ' . $system . '
            </form>';

    // Otherwise the layout is custom.
    } else {

        $attributes =
            'action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/my_account_profile.php" ' .
            'method="post"';

        $form->set('salutation', 'options', get_salutation_options());

        $form->set('first_name', 'maxlength', 50);
        $form->set('first_name', 'required', true);

        $form->set('last_name', 'maxlength', 50);
        $form->set('last_name', 'required', true);

        $form->set('suffix', 'options', get_suffix_options());

        $form->set('title', 'maxlength', 50);

        $form->set('business_phone', 'maxlength', 50);
        $form->set('mobile_phone', 'maxlength', 50);
        $form->set('home_phone', 'maxlength', 50);
        $form->set('business_fax', 'maxlength', 50);

        $form->set('company', 'maxlength', 50);

        $form->set('business_address_1', 'maxlength', 50);
        $form->set('business_address_2', 'maxlength', 50);

        $form->set('business_city', 'maxlength', 50);

        $form->set('business_state', 'maxlength', 50);

        $form->set('business_zip_code', 'maxlength', 50);

        $form->set('business_country', 'options', $business_country_options);

        $form->set('timezone', 'options', $timezone_options);

        $system .= get_token_field();

        $output = render_layout(array(
            'page_id' => $page_id,
            'messages' => $form->get_messages(),
            'form' => $form,
            'attributes' => $attributes,
            'timezone' => $show_timezone,
            'system' => $system,
            'my_account_url' => get_page_type_url('my account')));

        $output = $form->prepare($output);

    }    
        
    $form->remove_form();

    return $output;
}