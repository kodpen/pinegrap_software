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

function get_membership_entrance($properties = array()) {

    $page_id = $properties['page_id'];
    $device_type = $properties['device_type'];

    $layout_type = get_layout_type($page_id);

    $form = new liveform('membership_entrance');

    // if software is in secure mode, then make sure that form is submitted to a secure URL
    if (URL_SCHEME == 'https://') {
        $action_url = 'https://' . HOSTNAME . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/membership_entrance.php';
        
    // else software is not in secure mode, so just use relative URL
    } else {
        $action_url = OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/membership_entrance.php';
    }

    // If the form has not been submitted yet, then prefill values.
    if (!$form->field_in_session('token')) {

        // If the remember me feature is enabled, then deal with it.
        if (REMEMBER_ME) {
            // If the visitor checked remember me during the last login,
            // then check the remember me check box by default
            if ($_COOKIE['software']['remember_me'] == 'true') {
                $form->assign_field_value('login_remember_me', '1');
                $form->assign_field_value('register_remember_me', '1');
            }
        }

        $form->set('opt_in', '1');
    }

    // If the layout type is system or if there is no membership entrance page,
    // and therefore no layout setting, then use system layout.
    if ($layout_type == 'system' || !$layout_type) {
        
        $output_forgot_password_link = '';
        $output_password_hint_field = '';

        $output_login_remember_me_checkbox = '';
        $output_register_remember_me_checkbox = '';

        // if the remember me feature is enabled, then deal with it
        if (REMEMBER_ME == TRUE) {

            $output_login_remember_me_checkbox = 
                '<tr>
                    <td class="mobile_hide">&nbsp;</td>
                    <td>' . $form->output_field(array('type'=>'checkbox', 'name'=>'login_remember_me', 'id'=>'login_remember_me', 'value'=>'1', 'class'=>'software_input_checkbox')) . '<label for="login_remember_me"> Remember Me</label></td>
                </tr>';

            $output_register_remember_me_checkbox = 
                '<tr>
                    <td class="mobile_hide">&nbsp;</td>
                    <td>' . $form->output_field(array('type'=>'checkbox', 'name'=>'register_remember_me', 'id'=>'register_remember_me', 'value'=>'1', 'class'=>'software_input_checkbox')) . '<label for="register_remember_me"> Remember Me</label></td>
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
                    <td>Password Hint:</td>
                    <td>' . $form->output_field(array('type'=>'text', 'name'=>'password_hint', 'maxlength'=>'100', 'class'=>'software_input_text')) . '</td>
                </tr>';
        }

        if ($device_type == 'mobile') {
            // output tableless version for wrapping
            $output .=
                $form->output_errors() . '
                <div class="login" style="vertical-align: top; padding-top: 1em; padding-right: 2em">
                    <div class="heading" style="border: none">Login</div>
                    <form class="data" action="' . $action_url . '" method="post" class="software" style="margin: 0em">
                        ' . get_token_field() . '
                        <input type="hidden" name="login" value="true">
                        <input type="hidden" name="send_to" value="' . h($_GET['send_to']) . '" />
                        <input type="hidden" name="require_cookies" value="true" />
                        <table style="margin-bottom: 1em">
                            <tr>
                                <td>Email:</td>
                                <td>' . $form->output_field(array('type'=>'text', 'name'=>'u', 'class'=>'software_input_text', 'required' => 'true')) . '</td>
                            </tr>
                            <tr>
                                <td>Password:</td>
                                <td>' . $form->output_field(array('type'=>'password', 'name'=>'p', 'class'=>'software_input_password', 'required' => 'true')) . '</td>
                            </tr>
                            ' . $output_login_remember_me_checkbox . '
                        </table>
                        <input type="submit" name="submit_login" value="Login" class="software_input_submit_primary login_button" /><br />
                        <br />
                        ' . $output_forgot_password_link . '
                    </form>
                </div>

                <div class="register" style="vertical-align: top; padding-top: 2em">
                    <div class="heading" style="border:none;">Register</div>
                    <form class="data" action="' . $action_url . '" method="post" class="software" style="margin: 0em">
                        ' . get_token_field() . '
                        <input type="hidden" name="register" value="true">
                        <input type="hidden" name="send_to" value="' . h($_GET['send_to']) . '" />
                        <input type="hidden" name="require_cookies" value="true" />
                        <table style="margin-bottom: 1em">
                            <tr>
                                <td>' . h(MEMBER_ID_LABEL) . '*:</td>
                                <td>' . $form->output_field(array('type'=>'text', 'name'=>'member_id', 'class'=>'software_input_text', 'required' => 'true')) . '</td>
                            </tr>
                            <tr>
                                <td>First Name*:</td>
                                <td>' . $form->output_field(array('type'=>'text', 'name'=>'first_name', 'class'=>'software_input_text', 'required' => 'true')) . '</td>
                            </tr>
                            <tr>
                                <td>Last Name*:</td>
                                <td>' . $form->output_field(array('type'=>'text', 'name'=>'last_name', 'class'=>'software_input_text', 'required' => 'true')) . '</td>
                            </tr>
                            <tr>
                                <td>Username*:</td>
                                <td>' . $form->output_field(array('type'=>'text', 'name'=>'username', 'class'=>'software_input_text', 'required' => 'true')) . '</td>
                            </tr>
                            <tr>
                                <td>Email*:</td>
                                <td>' . $form->output_field(array('type'=>'email', 'name'=>'email_address', 'class'=>'software_input_text', 'required' => 'true')) . '</td>
                            </tr>
                            <tr>
                                <td>Confirm Email*:</td>
                                <td>' . $form->output_field(array('type'=>'email', 'name'=>'email_address_verify', 'class'=>'software_input_text', 'required' => 'true')) . '</td>
                            </tr>
                            ' . $output_strong_password_requirement_row . '
                            <tr>
                                <td>Password*:</td>
                                <td>' . $form->output_field(array('type'=>'password', 'name'=>'password', 'class'=>'software_input_password', 'required' => 'true')) . '</td>
                            </tr>
                            <tr>
                                <td>Confirm Password*:</td>
                                <td>' . $form->output_field(array('type'=>'password', 'name'=>'password_verify', 'class'=>'software_input_password', 'required' => 'true')) . '</td>
                            </tr>
                            ' . $output_password_hint_field . '
                            ' . $output_register_remember_me_checkbox . '

                            <tr>
                                <td class="mobile_hide">&nbsp;</td>
                                <td>' .
                                    $form->output_field(array(
                                        'type' => 'checkbox',
                                        'id' => 'opt_in',
                                        'name' => 'opt_in',
                                        'value' => '1',
                                        'class' => 'software_input_checkbox')) .
                                    '<label for="opt_in"> ' . h(OPT_IN_LABEL) . '</label>
                                </td>
                            </tr>

                        </table>
                        <input type="submit" name="submit_register" value="Register" class="software_input_submit_primary register_button" /><br />
                    </form>
                </div>
                <div style="clear: both"></div>';
        } else {  
        //output desktop version with tables for better spacing
        $output .=
            $form->output_errors() . '
            <table width="100%" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td class="login desktop_left" style="vertical-align: top; padding-right: 1em">
                        <div class="heading" style="border: none">Login</div>
                        <form class="data" action="' . $action_url . '" method="post" class="software" style="margin: 0em">
                            ' . get_token_field() . '
                            <input type="hidden" name="login" value="true">
                            <input type="hidden" name="send_to" value="' . h($_GET['send_to']) . '" />
                            <input type="hidden" name="require_cookies" value="true" />
                            <table style="margin-bottom: 1em">
                                <tr>
                                    <td>Email:</td>
                                    <td>' . $form->output_field(array('type'=>'text', 'name'=>'u', 'class'=>'software_input_text', 'required' => 'true')) . '</td>
                                </tr>
                                <tr>
                                    <td>Password:</td>
                                    <td>' . $form->output_field(array('type'=>'password', 'name'=>'p', 'class'=>'software_input_password', 'required' => 'true')) . '</td>
                                </tr>
                                ' . $output_login_remember_me_checkbox . '
                            </table>
                            <input type="submit" name="submit_login" value="Login" class="software_input_submit_primary login_button" /><br />
                            <br />
                            ' . $output_forgot_password_link . '
                        </form>
                    </td>
                    <td class="register desktop_right" style="vertical-align: top">
                        <div class="heading" style="border: none">Register</div>
                        <form class="data" action="' . $action_url . '" method="post" class="software" style="margin: 0em">
                            ' . get_token_field() . '
                            <input type="hidden" name="register" value="true">
                            <input type="hidden" name="send_to" value="' . h($_GET['send_to']) . '" />
                            <input type="hidden" name="require_cookies" value="true" />
                            <table style="margin-bottom: 1em">
                                <tr>
                                    <td>' . h(MEMBER_ID_LABEL) . '*:</td>
                                    <td>' . $form->output_field(array('type'=>'text', 'name'=>'member_id', 'class'=>'software_input_text', 'required' => 'true')) . '</td>
                                </tr>
                                <tr>
                                    <td>First Name*:</td>
                                    <td>' . $form->output_field(array('type'=>'text', 'name'=>'first_name', 'class'=>'software_input_text', 'required' => 'true')) . '</td>
                                </tr>
                                <tr>
                                    <td>Last Name*:</td>
                                    <td>' . $form->output_field(array('type'=>'text', 'name'=>'last_name', 'class'=>'software_input_text', 'required' => 'true')) . '</td>
                                </tr>
                                <tr>
                                    <td>Username*:</td>
                                    <td>' . $form->output_field(array('type'=>'text', 'name'=>'username', 'class'=>'software_input_text', 'required' => 'true')) . '</td>
                                </tr>
                                <tr>
                                    <td>Email*:</td>
                                    <td>' . $form->output_field(array('type'=>'email', 'name'=>'email_address', 'class'=>'software_input_text', 'required' => 'true')) . '</td>
                                </tr>
                                <tr>
                                    <td>Confirm Email*:</td>
                                    <td>' . $form->output_field(array('type'=>'email', 'name'=>'email_address_verify', 'class'=>'software_input_text', 'required' => 'true')) . '</td>
                                </tr>
                                ' . $output_strong_password_requirement_row . '
                                <tr>
                                    <td>Password*:</td>
                                    <td>' . $form->output_field(array('type'=>'password', 'name'=>'password', 'class'=>'software_input_password', 'required' => 'true')) . '</td>
                                </tr>
                                <tr>
                                    <td>Confirm Password*:</td>
                                    <td>' . $form->output_field(array('type'=>'password', 'name'=>'password_verify', 'class'=>'software_input_password', 'required' => 'true')) . '</td>
                                </tr>
                                ' . $output_password_hint_field . '
                                ' . $output_register_remember_me_checkbox . '

                                <tr>
                                    <td class="mobile_hide">&nbsp;</td>
                                    <td>' .
                                        $form->output_field(array(
                                            'type' => 'checkbox',
                                            'id' => 'opt_in',
                                            'name' => 'opt_in',
                                            'value' => '1',
                                            'class' => 'software_input_checkbox')) .
                                        '<label for="opt_in"> ' . h(OPT_IN_LABEL) . '</label>
                                    </td>
                                </tr>

                            </table>
                            <input type="submit" name="submit_register" value="Register" class="software_input_submit_primary register_button" /><br />
                        </form>
                    </td>
                </tr>
            </table>';

        }

    // Otherwise the layout is custom.
    } else {

        $attributes =
            'action="' . $action_url . '" ' .
            'method="post"';

        $form->set('u', 'required', true);
        $form->set('p', 'required', true);

        $login_system =
            get_token_field() . '
            <input type="hidden" name="login" value="true">
            <input type="hidden" name="send_to" value="' . h($_GET['send_to']) . '">
            <input type="hidden" name="require_cookies" value="true">';

        if (FORGOT_PASSWORD_LINK) {
            $forgot_password_url = PATH . SOFTWARE_DIRECTORY . '/forgot_password.php';

            if ($_GET['send_to'] != '') {
                $forgot_password_url .= '?send_to=' . urlencode($_GET['send_to']);
            }

        } else {
            $forgot_password_url = '';
        }

        $form->set('first_name', 'required', true);
        $form->set('last_name', 'required', true);
        $form->set('member_id', 'required', true);
        $form->set('username', 'required', true);
        $form->set('email_address', 'required', true);
        $form->set('email_address_verify', 'required', true);
        $form->set('password', 'required', true);
        $form->set('password_verify', 'required', true);
        
        // If strong password is enabled, then display password requirements.
        if (STRONG_PASSWORD) {
            $strong_password_help = get_strong_password_requirements();
        } else {
            $strong_password_help = '';
        }

        if (PASSWORD_HINT) {
            $form->set('password_hint', 'maxlength', 100);
        }

        $register_system =
            get_token_field() . '
            <input type="hidden" name="register" value="true">
            <input type="hidden" name="send_to" value="' . h($_GET['send_to']) . '">
            <input type="hidden" name="require_cookies" value="true">';

        $output = render_layout(array(
            'page_id' => $page_id,
            'messages' => $form->get_messages(),
            'form' => $form,
            'attributes' => $attributes,
            'login_system' => $login_system,
            'forgot_password_url' => $forgot_password_url,
            'strong_password_help' => $strong_password_help,
            'opt_in_label' => OPT_IN_LABEL,
            'register_system' => $register_system));

        $output = $form->prepare($output);
        
    }

    $form->remove_form();

    return $output;
}