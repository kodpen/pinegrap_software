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

function get_update_address_book($properties = array()) {

    $page_id = $properties['page_id'];

    $properties = get_page_type_properties($page_id, 'update address book');

    $address_type = $properties['address_type'];
    $address_type_page_id = $properties['address_type_page_id'];

    $layout_type = get_layout_type($page_id);
    
    $form = new liveform('update_address_book');

    // if an id was passed and form has not been filled out yet, get recipient data in order to populate fields
    if ($_GET['id'] && ($form->field_in_session('ship_to_name') == false)) {
        // get user id because we want to make sure that this user has access to the recipient that they are requesting to update
        $query = "SELECT user_id FROM user WHERE user_username = '" . escape($_SESSION['sessionusername']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        $user_id = $row['user_id'];

        // get recipient data
        $query = "SELECT * FROM address_book WHERE id = '" . escape($_GET['id']) . "' AND user = '$user_id'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);

        // set field values
        $form->assign_field_value('ship_to_name', $row['ship_to_name']);
        $form->assign_field_value('salutation', $row['salutation']);
        $form->assign_field_value('first_name', $row['first_name']);
        $form->assign_field_value('last_name', $row['last_name']);
        $form->assign_field_value('company', $row['company']);
        $form->assign_field_value('address_1', $row['address_1']);
        $form->assign_field_value('address_2', $row['address_2']);
        $form->assign_field_value('city', $row['city']);
        $form->assign_field_value('state', $row['state']);
        $form->assign_field_value('zip_code', $row['zip_code']);
        $form->assign_field_value('country', $row['country']);
        
        // if address type is enabled then prefill value
        if ($address_type == 1) {
            $form->assign_field_value('address_type', $row['address_type']);
        }
        
        $form->assign_field_value('phone_number', $row['phone_number']);
    }

    // if country is blank, set default country
    if (!$form->get_field_value('country')) {
        $query = "SELECT code FROM countries WHERE default_selected = 1";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        // if a default country was found, set default country
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $form->assign_field_value('country', $row['code']);
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

    // If the layout type is system or if there is no update address book page,
    // and therefore no layout setting, then use system layout.
    if ($layout_type == 'system' || !$layout_type) {
        
        $output_address_type_row = '';
        
        // if address type field is enabled, then output it
        if ($address_type == 1) {
            $output_address_type_what_is_this_link = '';
            
            // if there is an address type instruction page set, then check if page still exists
            if ($address_type_page_id != 0) {
                // get page name in order to check if page still exists
                $address_type_page_name = get_page_name($address_type_page_id);
                
                // if there is a page name, then page still exists, so output what is this link
                if ($address_type_page_name != '') {
                    $output_address_type_what_is_this_link = '&nbsp;&nbsp;&nbsp;&nbsp;<a href="' . OUTPUT_PATH . h(encode_url_path($address_type_page_name)) . '" target="_blank">What is this?</a>';
                }
            }
            
            $output_address_type_row =
                '<tr>
                    <td>Address Type*</td>
                    <td style="padding-top: 0.5em; padding-bottom: 0.5em">' . $form->output_field(array('type'=>'radio', 'name'=>'address_type', 'id'=>'address_type_residential', 'value'=>'residential', 'class'=>'software_input_radio')) . '<label for="address_type_residential"> Residential</label>&nbsp;&nbsp;&nbsp;&nbsp;' . $form->output_field(array('type'=>'radio', 'name'=>'address_type', 'id'=>'address_type_business', 'value'=>'business', 'class'=>'software_input_radio')) . '<label for="address_type_business"> Business</label>' . $output_address_type_what_is_this_link . '</td>
                </tr>';
        }

        $output .=
            $form->output_errors() . '
            <form action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/update_address_book.php" method="post" class="software">
                ' . get_token_field() . '
                <input type="hidden" name="page_id" value="' . $page_id . '" />
                <input type="hidden" name="id" value="' . h($_GET['id']) . '" />
                <table style="margin-bottom: 15px">
                    <tr>
                        <td>Ship to Name*</td>
                        <td>' . $form->output_field(array('type'=>'text', 'name'=>'ship_to_name', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                    </tr>
                    <tr>
                        <td colspan="2">&nbsp;</td>
                    </tr>
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
                        <td>Organization</td>
                        <td>' . $form->output_field(array('type'=>'text', 'name'=>'company', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                    </tr>
                    <tr>
                        <td>Address*</td>
                        <td>' . $form->output_field(array('type'=>'text', 'name'=>'address_1', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>' . $form->output_field(array('type'=>'text', 'name'=>'address_2', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                    </tr>
                    <tr>
                        <td>City*</td>
                        <td>' . $form->output_field(array('type'=>'text', 'name'=>'city', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                    </tr>
                    <tr>
                        <td>Country*</td>
                        <td>' . $form->output_field(array('type'=>'select', 'name'=>'country', 'id'=>'country', 'options'=>$country_options, 'class'=>'software_select')) . '</td>
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
                            $form->output_field(array('type'=>'text', 'id'=> 'state_text_box', 'name'=> 'state', 'maxlength'=>'50', 'class'=>'software_input_text')) .

                            $form->output_field(array('type'=>'select', 'id'=> 'state_pick_list', 'name'=> 'state', 'class'=>'software_select')) . '
                        </td>
                    </tr>
                    <tr>
                        <td>Zip / Postal Code<span id="zip_code_required" style="display: none">*</span></td>
                        <td>' . $form->output_field(array('type'=>'text', 'id' => 'zip_code', 'name'=>'zip_code', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                    </tr>
                    ' . $output_address_type_row . '
                    <tr>
                        <td>Phone</td>
                        <td>' . $form->output_field(array('type'=>'tel', 'name'=>'phone_number', 'maxlength'=>'50', 'class'=>'software_input_text')) . '</td>
                    </tr>
                </table>
                <input type="submit" name="submit" value="Submit" class="software_input_submit_primary submit_button" />&nbsp;&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" onclick="javascript:window.location.href=\'' . h(escape_javascript(get_page_type_url('my account'))) . '\'" class="software_input_submit_secondary cancel_button">
                ' . $system . '
            </form>';

    // Otherwise the layout is custom.
    } else {

        $attributes =
            'action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/update_address_book.php" ' .
            'method="post"';

        $form->set('ship_to_name', 'maxlength', 50);
        $form->set('ship_to_name', 'required', true);

        $form->set('salutation', 'options', get_salutation_options());

        $form->set('first_name', 'maxlength', 50);
        $form->set('first_name', 'required', true);

        $form->set('last_name', 'maxlength', 50);
        $form->set('last_name', 'required', true);

        $form->set('company', 'maxlength', 50);

        $form->set('address_1', 'maxlength', 50);
        $form->set('address_1', 'required', true);
        $form->set('address_2', 'maxlength', 50);

        $form->set('city', 'maxlength', 50);
        $form->set('city', 'required', true);

        $form->set('state', 'maxlength', 50);

        $form->set('zip_code', 'maxlength', 50);

        $form->set('country', 'required', true);

        $form->set('country', 'options', $country_options);

        settype($address_type, 'bool');

        $address_type_url = '';

        // If address type is enabled, then deal with it.
        if ($address_type) {
            $form->set('address_type', 'required', true);

            // If there is an address type page selected,
            // and that page still exists, then prepare address type URL.
            if (
                $address_type_page_id
                && ($address_type_page_name = get_page_name($address_type_page_id))
            ) {
                $address_type_url = PATH . encode_url_path($address_type_page_name);
            }
        }

        $form->set('phone_number', 'maxlength', 50);

        $system .=
            get_token_field() . '

            <input type="hidden" name="page_id" value="' . h($page_id) . '">
            <input type="hidden" name="id" value="' . h($_GET['id']) . '">';

        $output = render_layout(array(
            'page_id' => $page_id,
            'messages' => $form->get_messages(),
            'form' => $form,
            'attributes' => $attributes,
            'address_type' => $address_type,
            'address_type_url' => $address_type_url,
            'system' => $system,
            'my_account_url' => get_page_type_url('my account')));

        $output = $form->prepare($output);

    }

    $form->remove_form();

    return $output;
}