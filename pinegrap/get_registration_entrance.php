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

function get_registration_entrance($properties = array()) {

    $page_id = $properties['page_id'];
    $device_type = $properties['device_type'];

    $layout_type = get_layout_type($page_id);
    
    $login_form = new liveform('login');
    $register_form = new liveform('register');

    // if software is in secure mode, then make sure that form is submitted to a secure URL
    if (URL_SCHEME == 'https://') {
        $action_url = 'https://' . HOSTNAME . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/registration_entrance.php';
        
    // else software is not in secure mode, so just use relative URL
    } else {
        $action_url = OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/registration_entrance.php';
    }

    // If the form has not been submitted yet, then prefill values.
    if (!$login_form->field_in_session('token')) {
        // If the remember me feature is enabled, then deal with it.
        if (REMEMBER_ME) {
            // If the visitor checked remember me during the last login,
            // then check the remember me check box by default
            if ($_COOKIE['software']['remember_me'] == 'true') {
                $login_form->assign_field_value('login_remember_me', '1');
            }
        }
    }

    // If the form has not been submitted yet, then prefill values.
    if (!$register_form->field_in_session('token')) {

        // If the remember me feature is enabled, then deal with it.
        if (REMEMBER_ME) {
            // If the visitor checked remember me during the last login,
            // then check the remember me check box by default
            if ($_COOKIE['software']['remember_me'] == 'true') {
                $register_form->assign_field_value('register_remember_me', '1');
            }
        }

        $register_form->set('opt_in', '1');
    }

    $allow_guest_hidden_field = '';

    // If allow_guest is in the query string, add it to each form so that it will become a post. (In case the form returns an error)
    if (($_POST['allow_guest'] == "true") || ($_GET['allow_guest'] == "true")) {
        $allow_guest_hidden_field = '<input type="hidden" name="allow_guest" value="true">';
    }

    // If the layout type is system or if there is no registration entrance page,
    // and therefore no layout setting, then use system layout.
    if ($layout_type == 'system' || !$layout_type) {

        $output_forgot_password_link = '';
        $output_continue_as_a_guest_link = '';
        $output_password_hint_field = '';
        
        $output_login_remember_me_checkbox = '';
        $output_register_remember_me_checkbox = '';

        // if the remember me feature is enabled, then deal with it
        if (REMEMBER_ME == TRUE) {

            $output_login_remember_me_checkbox = 
                '<tr>
                    <td class="mobile_hide">&nbsp;</td>
                    <td>' . $login_form->output_field(array('type'=>'checkbox', 'name'=>'login_remember_me', 'id'=>'login_remember_me', 'value'=>'1', 'class'=>'software_input_checkbox')) . '<label for="login_remember_me"> Remember Me</label></td>
                </tr>';

            $output_register_remember_me_checkbox = 
                '<tr>
                    <td class="mobile_hide">&nbsp;</td>
                    <td>' . $register_form->output_field(array('type'=>'checkbox', 'name'=>'register_remember_me', 'id'=>'register_remember_me', 'value'=>'1', 'class'=>'software_input_checkbox')) . '<label for="register_remember_me"> Remember Me</label></td>
                </tr>';

        }
        
        if (FORGOT_PASSWORD_LINK == true) {
            $output_send_to_query_string = '';
            
            // if there is a send to, then add send to to query string
            if ((isset($_GET['send_to']) == true) && ($_GET['send_to'] != '')) {
                $output_send_to_query_string = h('?send_to=' . urlencode($_GET['send_to']));
            }
            
            $output_forgot_password_link = '<a class="forgot_button" href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/forgot_password.php' . $output_send_to_query_string . '">Forgot password?</a><br />' . "\n";
        }
        
        // If allow_guest is true, display the continue as guest button
        if ($_GET['allow_guest'] == "true") {
            $output_continue_as_a_guest_link = 
                '<div class="heading" style="padding-bottom: 20px;">
                    Continue as a Guest
                    <form class="data" action="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/registration_entrance.php" method="post" class="software" style="margin: 0; margin-top: 10px" id="guest-form">
                        ' . get_token_field() . '
                        <input type="hidden" name="continue" value="true">
                        <input type="hidden" name="send_to" value="' . h($_GET['send_to']) . '" />
                        ' . $allow_guest_hidden_field . '
                        <input type="submit" name="submit_continue" value="Continue" class="software_input_submit_primary guest_button" /><br />
                    </form>
                </div>';
        }
        
        $output_strong_password_requirement_row = '';
        
        // if strong password is enabled, then display password requirements
        if (STRONG_PASSWORD == true) {
            $output_strong_password_requirement_row =
                '<tr>
                    <td colspan="2">
                        ' . get_strong_password_requirements() . '
                    </td>
                </tr>';
        }
        
        // If Password_hint is enabled in the settings, then display the password hint field
        if (PASSWORD_HINT == true) {
            $output_password_hint_field =
                '<tr>
                    <td><label for="password_hint">Password Hint:</label></td>
                    <td>' .
                        $register_form->field(array(
                            'type' => 'text',
                            'id' => 'password_hint',
                            'name' => 'password_hint',
                            'maxlength' => '100',
                            'class' => 'software_input_text')) . '
                    </td>
                </tr>';
        }
        
        $output_captcha_fields = '';

        // if CAPTCHA is enabled then prepare to output CAPTCHA fields
        if (CAPTCHA == TRUE) {
            // get captcha fields if there are any
            $output_captcha_fields = get_captcha_fields($register_form);
            
            // if there are captcha fields to be displayed, then output them in a container
            if ($output_captcha_fields != '') {
                $output_captcha_fields = '<div style="margin-bottom: 1em">' . $output_captcha_fields . '</div>';
            }
        }

        if ($device_type == 'mobile') {
            // output tableless version for wrapping
            $output .=
                $login_form->get_messages() .
                $register_form->get_messages() . '
                <div class="login" style="vertical-align: top; padding-top: 1em; padding-right: 2em">
                    <div class="heading" style="border: none">Login</div>
                    <form class="data" action="' . $action_url . '" method="post" class="software" style="margin: 0em" id="login-form">
                        ' . get_token_field() . '
                        <input type="hidden" name="login" value="true">
                        <input type="hidden" name="send_to" value="' . h($_GET['send_to']) . '" />
                        <input type="hidden" name="require_cookies" value="true" />
                        <table style="margin-bottom: 1em">
                            <tr>
                                <td><label for="login_email">Email:</label></td>
                                <td>' .
                                    $login_form->field(array(
                                        'type' => 'email',
                                        'id' => 'login_email',
                                        'name' => 'email',
                                        'class' => 'software_input_text',
                                        'required' => 'true',
                                        'autocomplete' => 'email',
                                        'spellcheck' => 'false')) . '
                                </td>
                            </tr>
                            <tr>
                                <td><label for="login_password">Password:</label></td>
                                <td>' .
                                    $login_form->field(array(
                                        'type' => 'password',
                                        'id' => 'login_password',
                                        'name' => 'password',
                                        'class' => 'software_input_password',
                                        'required' => 'true',
                                        'autocomplete' => 'current-password',
                                        'spellcheck' => 'false')) . '
                                </td>
                            </tr>
                            ' . $output_login_remember_me_checkbox . '
                        </table>
                        ' . $allow_guest_hidden_field . '
                        <input type="submit" name="submit_login" value="Login" class="software_input_submit_primary login_button"><br>
                        <br />
                        ' . $output_forgot_password_link . '
                    </form>
                </div>

                <div class="register" style="vertical-align: top; padding-top: 2em">
                    ' . $output_continue_as_a_guest_link . '
                    <div class="heading" style="border:none;">Register</div>
                    <form class="data" action="' . $action_url . '" method="post" class="software" style="margin: 0em" id="register-form">
                        ' . get_token_field() . '
                        <input type="hidden" name="register" value="true">
                        <input type="hidden" name="send_to" value="' . h($_GET['send_to']) . '">
                        <input type="hidden" name="require_cookies" value="true">
                        <table style="margin-bottom: 1em">
                            <tr>
                                <td><label for="first_name">First Name*:</label></td>
                                <td>' .
                                    $register_form->field(array(
                                        'type' => 'text',
                                        'id' => 'first_name',
                                        'name' => 'first_name',
                                        'class' => 'software_input_text',
                                        'required' => 'true',
                                        'autocomplete' => 'given-name',
                                        'spellcheck' => 'false')) . '
                                </td>
                            </tr>
                            <tr>
                                <td><label for="last_name">Last Name*:</label></td>
                                <td>' .
                                    $register_form->field(array(
                                        'type' => 'text',
                                        'id' => 'last_name',
                                        'name' => 'last_name',
                                        'class' => 'software_input_text',
                                        'required' => 'true',
                                        'autocomplete' => 'family-name',
                                        'spellcheck' => 'false')) . '</td>
                            </tr>
                            <tr>
                                <td><label for="username">Username*:</label></td>
                                <td>' .
                                    $register_form->field(array(
                                        'type' => 'text',
                                        'id' => 'username',
                                        'name' => 'username',
                                        'class' => 'software_input_text',
                                        'required' => 'true',
                                        'autocomplete' => 'username',
                                        'spellcheck' => 'false')) . '
                                </td>
                            </tr>
                            <tr>
                                <td><label for="register_email">Email*:</label></td>
                                <td>' .
                                    $register_form->field(array(
                                        'type' => 'email',
                                        'id' => 'register_email',
                                        'name' => 'email',
                                        'class' => 'software_input_text',
                                        'required' => 'true',
                                        'autocomplete' => 'email',
                                        'spellcheck' => 'false')) . '
                                </td>
                            </tr>
                            <tr>
                                <td><label for="email_verify">Confirm Email*:</label></td>
                                <td>' .
                                    $register_form->field(array(
                                        'type' => 'email',
                                        'id' => 'email_verify',
                                        'name' => 'email_verify',
                                        'class' => 'software_input_text',
                                        'required' => 'true',
                                        'autocomplete' => 'email',
                                        'spellcheck' => 'false')) . '
                                </td>
                            </tr>
                            ' . $output_strong_password_requirement_row . '
                            <tr>
                                <td><label for="register_password">Password*:</label></td>
                                <td>' .
                                    $register_form->field(array(
                                        'type' => 'password',
                                        'id' => 'register_password',
                                        'name' => 'password',
                                        'class' => 'software_input_password',
                                        'required' => 'true',
                                        'autocomplete' => 'new-password',
                                        'spellcheck' => 'false')) . '
                                </td>
                            </tr>
                            <tr>
                                <td><label for="password_verify">Confirm Password*:</label></td>
                                <td>' .
                                    $register_form->field(array(
                                        'type' => 'password',
                                        'id' => 'password_verify',
                                        'name' => 'password_verify',
                                        'class' => 'software_input_password',
                                        'required' => 'true',
                                        'autocomplete' => 'new-password',
                                        'spellcheck' => 'false')) . '
                                </td>
                            </tr>
                            ' . $output_password_hint_field . '
                            ' . $output_register_remember_me_checkbox . '

                            <tr>
                                <td class="mobile_hide">&nbsp;</td>
                                <td>' .
                                    $register_form->field(array(
                                        'type' => 'checkbox',
                                        'id' => 'opt_in',
                                        'name' => 'opt_in',
                                        'value' => '1',
                                        'class' => 'software_input_checkbox')) .
                                    '<label for="opt_in"> ' . h(OPT_IN_LABEL) . '</label>
                                </td>
                            </tr>

                            <tr>
                                <td colspan="2">
                                ' . $output_captcha_fields . '
                                ' . $allow_guest_hidden_field . '
                                <input type="submit" name="submit_register" value="Register" class="software_input_submit_primary register_button"><br>
                                </td>
                            </tr>
                        </table>
                    </form>
                </div>
                <div style="clear: both"></div>';
        } else {  
            //output desktop version with tables for better spacing
            $output .=
                $login_form->get_messages() .
                $register_form->get_messages() . '
                <table width="100%" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td class="login desktop_left" style="vertical-align: top; padding-right: 1em">
                            <div class="heading" style="border:none;">Login</div>
                            <form class="data" action="' . $action_url . '" method="post" class="software" style="margin: 0em" id="login-form">
                                ' . get_token_field() . '
                                <input type="hidden" name="login" value="true">
                                <input type="hidden" name="send_to" value="' . h($_GET['send_to']) . '" />
                                <input type="hidden" name="require_cookies" value="true" />
                                <table style="margin-bottom: 1em">
                                    <tr>
                                        <td><label for="login_email">Email:</label></td>
                                        <td>' .
                                            $login_form->field(array(
                                                'type' => 'email',
                                                'id' => 'login_email',
                                                'name' => 'email',
                                                'class' => 'software_input_text',
                                                'required' => 'true',
                                                'autocomplete' => 'email',
                                                'spellcheck' => 'false')) . '
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><label for="login_password">Password:</label></td>
                                        <td>' .
                                            $login_form->field(array(
                                                'type' => 'password',
                                                'id' => 'login_password',
                                                'name' => 'password',
                                                'class' => 'software_input_password',
                                                'required' => 'true',
                                                'autocomplete' => 'current-password',
                                                'spellcheck' => 'false')) . '
                                        </td>
                                    </tr>
                                    ' . $output_login_remember_me_checkbox . '
                                </table>
                                ' . $allow_guest_hidden_field . '
                                <input type="submit" name="submit_login" value="Login" class="software_input_submit_primary login_button" /><br />
                                <br />
                                ' . $output_forgot_password_link . '
                            </form>
                        </td>
                        <td class="register desktop_right" style="vertical-align: top">
                            ' . $output_continue_as_a_guest_link . '
                            <div>
                                <div class="heading" style="border:none;">Register</div>
                                <form class="data" action="' . $action_url . '" method="post" class="software" style="margin: 0em" id="register-form">
                                    ' . get_token_field() . '
                                    <input type="hidden" name="register" value="true">
                                    <input type="hidden" name="send_to" value="' . h($_GET['send_to']) . '" />
                                    <input type="hidden" name="require_cookies" value="true" />
                                    <table style="margin-bottom: 1em">
                                        <tr>
                                            <td><label for="first_name">First Name*:</label></td>
                                            <td>' .
                                                $register_form->field(array(
                                                    'type' => 'text',
                                                    'id' => 'first_name',
                                                    'name' => 'first_name',
                                                    'class' => 'software_input_text',
                                                    'required' => 'true',
                                                    'autocomplete' => 'given-name',
                                                    'spellcheck' => 'false')) . '
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><label for="last_name">Last Name*:</label></td>
                                            <td>' .
                                                $register_form->field(array(
                                                    'type' => 'text',
                                                    'id' => 'last_name',
                                                    'name' => 'last_name',
                                                    'class' => 'software_input_text',
                                                    'required' => 'true',
                                                    'autocomplete' => 'family-name',
                                                    'spellcheck' => 'false')) . '</td>
                                        </tr>
                                        <tr>
                                            <td><label for="username">Username*:</label></td>
                                            <td>' .
                                                $register_form->field(array(
                                                    'type' => 'text',
                                                    'id' => 'username',
                                                    'name' => 'username',
                                                    'class' => 'software_input_text',
                                                    'required' => 'true',
                                                    'autocomplete' => 'username',
                                                    'spellcheck' => 'false')) . '
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><label for="register_email">Email*:</label></td>
                                            <td>' .
                                                $register_form->field(array(
                                                    'type' => 'email',
                                                    'id' => 'register_email',
                                                    'name' => 'email',
                                                    'class' => 'software_input_text',
                                                    'required' => 'true',
                                                    'autocomplete' => 'email',
                                                    'spellcheck' => 'false')) . '
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><label for="email_verify">Confirm Email*:</label></td>
                                            <td>' .
                                                $register_form->field(array(
                                                    'type' => 'email',
                                                    'id' => 'email_verify',
                                                    'name' => 'email_verify',
                                                    'class' => 'software_input_text',
                                                    'required' => 'true',
                                                    'autocomplete' => 'email',
                                                    'spellcheck' => 'false')) . '
                                            </td>
                                        </tr>
                                        ' . $output_strong_password_requirement_row . '
                                        <tr>
                                            <td><label for="register_password">Password*:</label></td>
                                            <td>' .
                                                $register_form->field(array(
                                                    'type' => 'password',
                                                    'id' => 'register_password',
                                                    'name' => 'password',
                                                    'class' => 'software_input_password',
                                                    'required' => 'true',
                                                    'autocomplete' => 'new-password',
                                                    'spellcheck' => 'false')) . '
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><label for="password_verify">Confirm Password*:</label></td>
                                            <td>' .
                                                $register_form->field(array(
                                                    'type' => 'password',
                                                    'id' => 'password_verify',
                                                    'name' => 'password_verify',
                                                    'class' => 'software_input_password',
                                                    'required' => 'true',
                                                    'autocomplete' => 'new-password',
                                                    'spellcheck' => 'false')) . '
                                            </td>
                                        </tr>
                                        ' . $output_password_hint_field . '
                                        ' . $output_register_remember_me_checkbox . '

                                        <tr>
                                            <td class="mobile_hide">&nbsp;</td>
                                            <td>' .
                                                $register_form->field(array(
                                                    'type' => 'checkbox',
                                                    'id' => 'opt_in',
                                                    'name' => 'opt_in',
                                                    'value' => '1',
                                                    'class' => 'software_input_checkbox')) .
                                                '<label for="opt_in"> ' . h(OPT_IN_LABEL) . '</label>
                                            </td>
                                        </tr>
                                    </table>
                                    ' . $output_captcha_fields . '
                                    ' . $allow_guest_hidden_field . '
                                    <input type="submit" name="submit_register" value="Register" class="software_input_submit_primary register_button"><br>
                                </form>
                            </div>
                        </td>
                    </tr>
                </table>';
        }

    // Otherwise the layout is custom.
    } else {

        $login_attributes = 'action="' . $action_url . '" method="post" id="login-form"';

        $login_form->set('email', 'required', true);
        $login_form->set('password', 'required', true);

        $login_system =
            get_token_field() . '
            <input type="hidden" name="login" value="true">
            <input type="hidden" name="send_to" value="' . h($_GET['send_to']) . '">
            <input type="hidden" name="require_cookies" value="true">
            ' . $allow_guest_hidden_field;

        if (FORGOT_PASSWORD_LINK) {
            $forgot_password_url = PATH . SOFTWARE_DIRECTORY . '/forgot_password.php';

            if ($_GET['send_to'] != '') {
                $forgot_password_url .= '?send_to=' . urlencode($_GET['send_to']);
            }

        } else {
            $forgot_password_url = '';
        }

        // If allow_guest is true, display the continue as guest button.
        if ($_GET['allow_guest'] == 'true') {

            $guest = true;

            $guest_attributes = 'action="' . $action_url . '" method="post" id="guest-form"';

            $guest_system =
                get_token_field() . '
                <input type="hidden" name="continue" value="true">
                <input type="hidden" name="send_to" value="' . h($_GET['send_to']) . '">
                ' . $allow_guest_hidden_field;

        } else {
            $guest = false;
            $guest_attributes = '';
            $guest_system = '';
        }

        $register_attributes = 'action="' . $action_url . '" method="post" id="register-form"';

        $register_form->set('first_name', 'required', true);
        $register_form->set('last_name', 'required', true);
        $register_form->set('username', 'required', true);
        $register_form->set('email', 'required', true);
        $register_form->set('email_verify', 'required', true);
        $register_form->set('password', 'required', true);
        $register_form->set('password_verify', 'required', true);
        
        // If strong password is enabled, then display password requirements.
        if (STRONG_PASSWORD) {
            $strong_password_help = get_strong_password_requirements();
        } else {
            $strong_password_help = '';
        }

        if (PASSWORD_HINT) {
            $register_form->set('password_hint', 'maxlength', 100);
        }

        $captcha_info = get_captcha_info($register_form);

        $register_system =
            get_token_field() . '
            <input type="hidden" name="register" value="true">
            <input type="hidden" name="send_to" value="' . h($_GET['send_to']) . '">
            <input type="hidden" name="require_cookies" value="true">
            ' . $allow_guest_hidden_field .
            $captcha_info['system'];

        $output = render_layout(array(
            'page_id' => $page_id,
            'messages' =>
                $login_form->get_messages() .
                $register_form->get_messages(),
            'login_form' => $login_form,
            'register_form' => $register_form,
            'login_attributes' => $login_attributes,
            'login_system' => $login_system,
            'forgot_password_url' => $forgot_password_url,
            'guest' => $guest,
            'guest_attributes' => $guest_attributes,
            'guest_system' => $guest_system,
            'register_attributes' => $register_attributes,
            'strong_password_help' => $strong_password_help,
            'opt_in_label' => OPT_IN_LABEL,
            'captcha_question' => $captcha_info['question'],
            'register_system' => $register_system));

        $output = $login_form->prepare($output, 'login-form');
        $output = $register_form->prepare($output, 'register-form');
        
    }

    $login_form->remove();
    $register_form->remove();

    return $output;
}