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

function get_login($properties = array()) {

    $page_id = $properties['page_id'];

    $layout_type = get_layout_type($page_id);

    $form = new liveform('login');
    
    // if software is in secure mode, then make sure that form is submitted to a secure URL
    if (URL_SCHEME == 'https://') {
        $action_url = 'https://' . HOSTNAME . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/index.php';
        
    // else software is not in secure mode, so just use relative URL
    } else {
        $action_url = OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/index.php';
    }

    // If the form has not been submitted yet, then prefill values.
    if (!$form->field_in_session('token')) {
        // If the remember me feature is enabled, then deal with it.
        if (REMEMBER_ME) {
            // If the visitor checked remember me during the last login,
            // then check the remember me check box by default
            if ($_COOKIE['software']['remember_me'] == 'true') {
                $form->assign_field_value('remember_me', '1');
            }
        }
    }

    // If the layout type is system or if there is no login page,
    // and therefore no layout setting, then use system layout.
    if ($layout_type == 'system' || !$layout_type) {

        $output_remember_me_checkbox = '';
        $output_forgot_password_link = '';
    
        // if the remember me feature is enabled, then output remember me row
        if (REMEMBER_ME == TRUE) {
            $output_remember_me_checkbox = 
                    '<tr>
                        <td class="mobile_hide">&nbsp;</td>
                        <td>' . $form->output_field(array('type'=>'checkbox', 'name'=>'remember_me', 'id'=>'remember_me', 'value'=>'1', 'class'=>'software_input_checkbox')) . '<label for="remember_me"> Remember Me</label></td>
                    </tr>';
        }
        
        if (FORGOT_PASSWORD_LINK == true) {
            $output_send_to = '';

            // If there is a send to, then prepare it.
            if ((isset($_GET['send_to']) == TRUE) && ($_GET['send_to'] != '')) {
                $output_send_to = h(urlencode($_GET['send_to']));

            // Otherwise there is not a send to, so create send to based on the screen that the visitor is currently on.
            } else {
                $output_send_to = h(urlencode(get_request_uri()));
            }

            $output_forgot_password_link = '<a class="forgot_button" href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/forgot_password.php?send_to=' . $output_send_to . '">Forgot password?</a><br />';
        }
        
        // if email field has an error or if password field does not have an error, email field gets focus
        if (($form->check_field_error('email') == true) || ($form->check_field_error('password') == false)) {
            $email_autofocus = true;
            $password_autofocus = false;
            
        // else password field gets focus
        } else {
            $email_autofocus = false;
            $password_autofocus = true;
        }
        
        $output =
            $form->output_errors() . '
            <form name="login" action="' . $action_url . '" method="post">
                ' . get_token_field() . '
                <input type="hidden" name="send_to" value="' . h($_GET['send_to']) . '" />
                <input type="hidden" name="require_cookies" value="true" />
                <table style="margin-bottom: 1em">
                    <tr>
                        <td><label for="email">Email:</label></td>
                        <td>' .
                            $form->field(array(
                                'type' => 'email',
                                'id' => 'email',
                                'name' => 'email',
                                'size' => '30',
                                'class' => 'software_input_text',
                                'required' => 'true',
                                'autocomplete' => 'email',
                                'autofocus' => $email_autofocus,
                                'spellcheck' => 'false')) . '
                        </td>
                    </tr>
                    <tr>
                        <td><label for="password">Password:</label></td>
                        <td>' .
                            $form->field(array(
                                'type' => 'password',
                                'id' => 'password',
                                'name' => 'password',
                                'size' => '30',
                                'class' => 'software_input_password',
                                'required' => 'true',
                                'autocomplete' => 'current-password',
                                'autofocus' => $password_autofocus,
                                'spellcheck' => 'false')) . '
                        </td>
                    </tr>
                    ' . $output_remember_me_checkbox . '
                </table>
                <input type="submit" name="submit_login" value="Login" class="software_input_submit_primary login_button" /><br />
                <br />
                ' . $output_forgot_password_link . '
            </form>';

    // Otherwise the layout is custom.
    } else {

        $attributes =
            'action="' . $action_url . '" ' .
            'method="post"';

        $form->set('email', 'required', true);
        $form->set('password', 'required', true);

        // If email field has an error or if password field does not have an error,
        // then focus username field.
        if (($form->check_field_error('email')) || (!$form->check_field_error('password'))) {
            $form->set('email', 'autofocus', true);

        // Otherwise focus password field.
        } else {
            $form->set('password', 'autofocus', true);
        }

        $system =
            get_token_field() . '
            <input type="hidden" name="send_to" value="' . h($_GET['send_to']) . '">
            <input type="hidden" name="require_cookies" value="true">';

        if (FORGOT_PASSWORD_LINK) {
            $forgot_password_url = PATH . SOFTWARE_DIRECTORY . '/forgot_password.php?send_to=';

            if ($_GET['send_to'] != '') {
                $forgot_password_url .= urlencode($_GET['send_to']);
            } else {
                $forgot_password_url .= urlencode(REQUEST_URL);
            }

        } else {
            $forgot_password_url = '';
        }

        $output = render_layout(array(
            'page_id' => $page_id,
            'messages' => $form->get_messages(),
            'form' => $form,
            'attributes' => $attributes,
            'system' => $system,
            'forgot_password_url' => $forgot_password_url));

        $output = $form->prepare($output);
        
    }

    $form->remove_form();

    return $output;
}