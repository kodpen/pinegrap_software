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

class liveform {

    var $form;
    var $index;

    function liveform($form, $index = 0) {
        $this->form = $form;
        $this->index = $index;
    }

    function output_field($attributes) {

        $type =         $attributes['type'];
        $field =        $attributes['name'];
        $id =           $attributes['id'];
        $value =        $attributes['value'];
        $size =         $attributes['size'];
        $maxlength =    $attributes['maxlength'];
        $checked =      $attributes['checked'];
        $rows =         $attributes['rows'];
        $cols =         $attributes['cols'];
        $multiple =     $attributes['multiple'];
        $options =      $attributes['options'];
        $class =        $attributes['class'];
        $style =        $attributes['style'];
        $readonly =     $attributes['readonly'];
        $onchange =     $attributes['onchange'];
        $onclick =      $attributes['onclick'];
        $onfocus =      $attributes['onfocus'];
        $onblur =       $attributes['onblur'];
        $min =          $attributes['min'];
        $max =          $attributes['max'];
        $title =        $attributes['title'];
        $placeholder =  $attributes['placeholder'];
        $required =     $attributes['required'];

        // If this is not a hidden or submit field, then prepare required value.
        if (($type != 'hidden') && ($type != 'submit')) {
            $output_required = '';

            if ($required != '') {
                $output_required = ' required="' . h($required) . '"';
            }
        }

        $autofocus = '';

        if ($attributes['autofocus']) {
            $autofocus = ' autofocus';
        }

        $autocomplete = '';

        if ($attributes['autocomplete']) {
            $autocomplete = ' autocomplete="' . h($attributes['autocomplete']) . '"';
        }

        $spellcheck = '';

        if ($attributes['spellcheck']) {
            $spellcheck = ' spellcheck="' . h($attributes['spellcheck']) . '"';
        }

        switch ($type) {
            case 'text' :
            case 'email' :
            case 'url':
            case 'tel':
            case 'number':
                if ($this->field_in_session($field) == true) {
                    $value = $this->get_field_value($field);
                    
                    // if value is an array, then this field was previously a multi-selection field, so we will use the first value in the array
                    if (is_array($value) == true) {
                        $value = $value[0];
                    }
                }
                $style = $this->create_error_style($field, $style);

                $output_readonly = ($readonly) ? ' readonly="' . $readonly . '"' : '';
                $output_onchange = ($onchange) ? ' onchange="' . $onchange . '"' : '';
                $output_onclick = ($onclick) ? ' onclick="' . $onclick . '"' : '';
                $output_onfocus = ($onfocus) ? ' onfocus="' . $onfocus . '"' : '';
                $output_onblur = ($onblur) ? ' onblur="' . $onblur . '"' : '';

                $output_min = '';

                // If there is a min, then output it.
                if ($min != '') {
                    $output_min = ' min="' . $min . '"';
                }

                $output_max = '';

                // If there is a max, then output it.
                if ($max != '') {
                    $output_max = ' max="' . $max . '"';
                }

                $output_placeholder = '';

                if ($placeholder != '') {
                    $output_placeholder = ' placeholder="' . h($placeholder) . '"';
                }

                $output_maxlength = '';

                if ($maxlength != '') {
                    $output_maxlength = ' maxlength="' . h($maxlength) . '"';
                }

                $inputmode = '';

                if ($attributes['inputmode']) {
                    $inputmode = ' inputmode="' . h($attributes['inputmode']) . '"';
                }
                
                return "<input type=\"$type\" name=\"" . h($field) . "\" id=\"" . h($id) . "\" value=\"" . h($value) . "\" size=\"$size\" class=\"$class\" style=\"$style\"$output_readonly$output_onchange$output_onclick$output_onfocus$output_onblur$output_min$output_max" . $output_placeholder . $output_required . $output_maxlength . $autocomplete . $autofocus . $spellcheck . $inputmode . ">";
                break;

            case 'checkbox':
                $field_name = $field;
                
                // if the field has brackets in the name, then remove them
                if (mb_substr($field, -2) == '[]') {
                    $field = mb_substr($field, 0, -2);
                }
                
                if ($value != '') {
                    $output_value = ' value="' . h($value) . '"';
                } else {
                    $output_value = '';
                }
                
                // if check box was checked
                if (
                    ($this->get_field_value($field) == 'on')
                    || ($value && ($this->get_field_value($field) == $value))
                    || ((is_array($this->get_field_value($field)) == true) && (in_array($value, $this->get_field_value($field)) == true))
                    ) {
                        $checked = ' checked="checked"';
                } elseif ($this->field_in_session($field) == true) {
                    $checked = '';
                } elseif ($checked == 'checked') {
                    $checked = ' checked="checked"';
                } else {
                    $checked = '';
                }
                $style = $this->create_error_style($field, $style);
                
                $output_onclick = ($onclick) ? ' onclick="' . $onclick . '"' : '';
                $output_onfocus = ($onfocus) ? ' onfocus="' . $onfocus . '"' : '';
                
                return "<input type=\"checkbox\" name=\"" . h($field_name) . "\" id=\"" . h($id) . "\"$output_value$checked class=\"$class\" style=\"$style\"$output_onclick$output_onfocus" . $output_required . $autofocus . ">";
                break;

            case 'radio':
                if ($this->get_field_value($field) == $value) {
                    $checked = ' checked="checked"';
                } elseif ($this->field_in_session($field) == TRUE) {
                    $checked = '';
                } elseif ($checked == 'checked') {
                    $checked = ' checked="checked"';
                } else {
                    $checked = '';
                }
                $style = $this->create_error_style($field, $type, $style);
                
                $output_onclick = ($onclick) ? ' onclick="' . $onclick . '"' : '';
                $output_onfocus = ($onfocus) ? ' onfocus="' . $onfocus . '"' : '';
                
                return "<input type=\"radio\" name=\"" . h($field) . "\" id=\"" . h($id) . "\" value=\"" . h($value) . "\"$checked class=\"$class\" style=\"$style\"$output_onclick$output_onfocus" . $output_required . $autofocus . ">";
                break;

            case 'select':
                $field_name = $field;
                
                // if the field has brackets in the name, then remove them
                if (mb_substr($field, -2) == '[]') {
                    $field = mb_substr($field, 0, -2);
                }
                
                $style = $this->create_error_style($field, $style);
                
                if ($multiple == 'multiple') {
                    $multiple = 'multiple="multiple"';
                } else {
                    $multiple = '';
                }
                
                $output_onclick = ($onclick) ? ' onclick="' . $onclick . '"' : '';
                $output_onfocus = ($onfocus) ? ' onfocus="' . $onfocus . '"' : '';
                
                $output = "<select name=\"" . h($field_name) . "\" id=\"" . h($id) . "\" size=\"$size\"$multiple class=\"$class\" style=\"$style\" onchange=\"$onchange\"$output_onclick$output_onfocus" . $output_required . $autocomplete . $autofocus . ">";

                // If options were not directly passed into this function,
                // then check for options in session.
                if (!$options) {
                    $options = $this->get($field, 'options');
                }

                if (is_array($options) == true) {
                    foreach ($options as $option_name => $option_value) {
                        // if the option is a starting optgroup, then prepare starting optgroup
                        if ($option_value == '<optgroup>') {
                            $output .= '<optgroup label="' . $option_name . '">';
                            
                        // else if option is an ending optgroup, then prepare ending optgroup
                        } elseif ($option_value == '</optgroup>') {
                            $output .= '</optgroup>';
                            
                        // else option is a standard option, so prepare standard option
                        } else {
                            // If option value is an array, then attributes were passed in an array, so deal with them.
                            if (is_array($option_value) == true) {
                                // If there is a label in the array, then use that for the option name.
                                // We sometimes pass the label in the array instead of the key in cases where there may be multiple options
                                // with the same label/name. This can happen if there are multiple product groups with the same name.
                                if (isset($option_value['label']) == TRUE) {
                                    $option_name = $option_value['label'];
                                }

                                $default_selected = $option_value['default_selected'];
                                $option_value = $option_value['value'];
                                
                            // else option value is not an array, so default selected values were not passed
                            } else {
                                $default_selected = 0;
                            }
                            
                            if (
                                ($this->get_field_value($field) == $option_value)
                                || ((is_array($this->get_field_value($field)) == true) && (in_array($option_value, $this->get_field_value($field)) == true))
                                ) {
                                $selected = ' selected="selected"';
                            } elseif ($this->field_in_session($field) == true) {
                                $selected = '';
                            } elseif (($default_selected == 1) || ($value == $option_value)) {
                                $selected = ' selected="selected"';
                            } else {
                                $selected = '';
                            }
                            
                            $output .= "<option value=\"$option_value\"$selected>$option_name</option>";
                        }
                    }
                }

                $output .= "</select>";
                return $output;
                break;

            case 'textarea':
                if ($this->field_in_session($field) == true) {
                    $value = $this->get_field_value($field);
                    
                    // if value is an array, then this field was previously a multi-selection field, so we will use the first value in the array
                    if (is_array($value) == true) {
                        $value = $value[0];
                    }
                }
                $style = $this->create_error_style($field, $style);
                
                $output_onclick = ($onclick) ? ' onclick="' . $onclick . '"' : '';
                $output_onfocus = ($onfocus) ? ' onfocus="' . $onfocus . '"' : '';

                $output_readonly = '';

                // If there is a readonly value, then output it.
                if ($readonly != '') {
                    $output_readonly = ' readonly="' . $readonly . '"';
                }

                $output_placeholder = '';

                if ($placeholder != '') {
                    $output_placeholder = ' placeholder="' . h($placeholder) . '"';
                }

                $output_maxlength = '';

                if ($maxlength != '') {
                    $output_maxlength = ' maxlength="' . h($maxlength) . '"';
                }
                
                return "<textarea name=\"" . h($field) . "\" id=\"" . h($id) . "\" rows=\"$rows\" cols=\"$cols\" class=\"$class\" style=\"$style\"$output_onclick$output_onfocus$output_readonly" . $output_placeholder . $output_required . $output_maxlength . $autocomplete . $autofocus . $spellcheck . ">" . h($value) . "</textarea>";
                break;

            case 'password':
                if ($this->field_in_session($field) == true) {
                    $value = $this->get_field_value($field);
                    
                    // if value is an array, then this field was previously a multi-selection field, so we will use the first value in the array
                    if (is_array($value) == true) {
                        $value = $value[0];
                    }
                }
                $style = $this->create_error_style($field, $style);
                
                $output_onclick = ($onclick) ? ' onclick="' . $onclick . '"' : '';
                $output_onfocus = ($onfocus) ? ' onfocus="' . $onfocus . '"' : '';

                $output_maxlength = '';

                if ($maxlength != '') {
                    $output_maxlength = ' maxlength="' . h($maxlength) . '"';
                }
                
                return "<input type=\"password\" name=\"" . h($field) . "\" id=\"" . h($id) . "\" value=\"" . h($value) . "\" size=\"$size\" class=\"$class\" style=\"$style\"$output_onclick$output_onfocus" . $output_required . $output_maxlength . $autocomplete . $autofocus . $spellcheck . ">";
                break;
                
            case 'hidden':
                if ($this->field_in_session($field) == true) {
                    $value = $this->get_field_value($field);
                    
                    // if value is an array, then this field was previously a multi-selection field, so we will use the first value in the array
                    if (is_array($value) == true) {
                        $value = $value[0];
                    }
                }

                $output_class = '';

                // If the class is not blank, then output class.
                if ($class != '') {
                    $output_class = ' class="' . h($class) . '"';
                }

                return "<input type=\"hidden\" name=\"" . h($field) . "\" id=\"" . h($id) . "\" value=\"" . h($value) . "\"$output_class>";
                break;
                
            case 'file':
                $style = $this->create_error_style($field, $style);
                
                $output_onclick = ($onclick) ? ' onclick="' . $onclick . '"' : '';
                $output_onfocus = ($onfocus) ? ' onfocus="' . $onfocus . '"' : '';
                
                return '<input type="file" name="' . h($field) . '" size="' . $size . '" class="' . $class . '" style="' . $style . '"' . $output_onclick . $output_onfocus . $output_required . $autofocus . '>';
                break;

            case 'submit':
                $output_name = '';

                // If the name is not blank, then output name.
                if ($field != '') {
                    $output_name = ' name="' . h($field) . '"';
                }

                $output_id = '';

                // If the id is not blank, then output id.
                if ($id != '') {
                    $output_id = ' id="' . h($id) . '"';
                }

                $output_value = '';

                // If a value was passed, then output value.
                // We treat this property differently from the others because we might want to pass a blank value.
                if (isset($attributes['value']) == true) {
                    $output_value = ' value="' . h($attributes['value']) . '"';
                }

                $output_class = '';

                // If the class is not blank, then output class.
                if ($class != '') {
                    $output_class = ' class="' . h($class) . '"';
                }

                $style = $this->create_error_style($field, $style);

                $output_style = '';

                // If the style is not blank, then output style.
                if ($style != '') {
                    $output_style = ' style="' . h($style) . '"';
                }

                $output_title = '';

                // If the title is not blank, then output title.
                if ($title != '') {
                    $output_title = ' title="' . h($title) . '"';
                }

                return '<input type="submit"' . $output_name . $output_id . $output_value . $output_class . $output_style . $output_title . $autofocus . '>';

                break;
        }
    }

    // Shorter alias function for output_field().
    function field($attributes) {
        return $this->output_field($attributes);
    }

    function field_in_session($field)
    {
        if ($_SESSION['software']['liveforms'][$this->form][$this->index][$field]) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function add_fields_to_session($properties = array()) {

        if (isset($properties['trim'])) {
            $trim = $properties['trim'];
        } else {
            $trim = true;
        }

        // if there are fields in this session, loop through all fields in session, in order to clear values
        // we must do this in order to clear values for check boxes and pick lists because they do not pass POST values when nothing is selected
        if ($_SESSION['software']['liveforms'][$this->form][$this->index]) {
            foreach ($_SESSION['software']['liveforms'][$this->form][$this->index] as $field => $value) {
                unset($_SESSION['software']['liveforms'][$this->form][$this->index][$field]['value']);
            }
        }
        
        // loop through all data that was submitted in order to add it to the session
        foreach ($_REQUEST as $field => $value) {
            // if value for field is an array (i.e. check boxes or pick list with multiple answers)
            if (is_array($value) == true) {
                foreach ($value as $key => $value_2) {
                    // If the value is an array (unusual), or trim is disabled,
                    // then just set it and do not trim it.
                    // we added the array check because, for some reason, array data could appear at this level
                    // which would cause the following error:
                    // Warning: trim() expects parameter 1 to be a string, array given...
                    if (is_array($value_2) or !$trim) {
                        $_SESSION['software']['liveforms'][$this->form][$this->index][$field]['value'][$key] = $value_2;

                    // else the value is not an array (usual), so trim and set it
                    } else {
                        $_SESSION['software']['liveforms'][$this->form][$this->index][$field]['value'][$key] = trim($value_2);
                    }
                }
            
            // else value for field is not an array
            } else {
                // If trim is enabled, then remove white-space from beginning and end.
                if ($trim) {
                    $value = trim($value);
                }
                
                // if this field is a credit card number field and encryption is enabled, then encrypt value before it is added to session,
                // so that credit card number does not appear in session file unencrypted
                if (
                    ($field == 'card_number')
                    && (defined('ENCRYPTION_KEY') == TRUE)
                    && (extension_loaded('mcrypt') == TRUE)
                    && (in_array('rijndael-256', mcrypt_list_algorithms()) == TRUE)
                ) {
                    $value = encrypt_credit_card_number($value, ENCRYPTION_KEY);
                }
                
                $_SESSION['software']['liveforms'][$this->form][$this->index][$field]['value'] = $value;
            }
        }

        // Remember that the form should not be prefilled because it has now been submitted
        $_SESSION['software']['liveforms'][$this->form][$this->index]['__prefill'] = false;
    }

    function assign_field_value($field, $value)
    {
        // if this field is a credit card number field and encryption is enabled, then encrypt value before it is added to session,
        // so that credit card number does not appear in session file unencrypted
        if (
            ($field == 'card_number')
            && (defined('ENCRYPTION_KEY') == TRUE)
            && (extension_loaded('mcrypt') == TRUE)
            && (in_array('rijndael-256', mcrypt_list_algorithms()) == TRUE)
        ) {
            $value = encrypt_credit_card_number($value, ENCRYPTION_KEY);
        }
        
        $_SESSION['software']['liveforms'][$this->form][$this->index][$field]['value'] = $value;
    }

    // Used for setting the value of a field, or the value of a different attribute.
    // Examples:
    // set('first_name', 'John')
    // set('first_name', 'required', true)

    function set($field, $argument_2, $argument_3 = null) {

        // If the 3rd argument was not passed, then assume that we should set
        // the value attribute to the value that was passed in the 2nd argument.
        if ($argument_3 === null) {
            
            $value = $argument_2;

            // if this field is a credit card number field and encryption is enabled, then encrypt value before it is added to session,
            // so that credit card number does not appear in session file unencrypted
            if (
                ($field == 'card_number')
                && (defined('ENCRYPTION_KEY') == TRUE)
                && (extension_loaded('mcrypt') == TRUE)
                && (in_array('rijndael-256', mcrypt_list_algorithms()) == TRUE)
            ) {
                $value = encrypt_credit_card_number($value, ENCRYPTION_KEY);
            }

            $_SESSION['software']['liveforms'][$this->form][$this->index][$field]['value'] = $value;

        // Otherwise the 3rd argument was passed, so assume that we should set
        // the attribute that was passed in the 2nd argument to the value that
        // that was passed in the 3rd argument.
        } else {
            $_SESSION['software']['liveforms'][$this->form][$this->index][$field][$argument_2] = $argument_3;
        }

    }

    // Used for setting/replacing all the attributes for a field by passing an entire array.

    function set_field($field, $attributes) {
        $_SESSION['software']['liveforms'][$this->form][$this->index][$field] = $attributes;
    }

    function get($field, $attribute = '') {

        // If an attribute was not passed, then return the field value.
        if ($attribute === '') {
            $value = $_SESSION['software']['liveforms'][$this->form][$this->index][$field]['value'];

            // if this field is a credit card number field and encryption is enabled, then decrypt value
            if (
                ($field == 'card_number')
                && (defined('ENCRYPTION_KEY') == TRUE)
                && (extension_loaded('mcrypt') == TRUE)
                && (in_array('rijndael-256', mcrypt_list_algorithms()) == TRUE)
            ) {
                $value = decrypt_credit_card_number($value, ENCRYPTION_KEY);
            }

            return $value;

        // Otherwise an attribute was passed, so return that attribute's value.
        } else {
            return $_SESSION['software']['liveforms'][$this->form][$this->index][$field][$attribute];
        }

    }

    function get_field($field) {
        return $_SESSION['software']['liveforms'][$this->form][$this->index][$field];
    }

    function get_field_value($field)
    {
        $value = $_SESSION['software']['liveforms'][$this->form][$this->index][$field]['value'];
        
        // if this field is a credit card number field and encryption is enabled, then decrypt value
        if (
            ($field == 'card_number')
            && (defined('ENCRYPTION_KEY') == TRUE)
            && (extension_loaded('mcrypt') == TRUE)
            && (in_array('rijndael-256', mcrypt_list_algorithms()) == TRUE)
        ) {
            $value = decrypt_credit_card_number($value, ENCRYPTION_KEY);
        }
        
        return $value;
    }

    function validate_required_field($field, $error_message = '')
    {
        // assume that a value does not exist until we find out otherwise
        $value_exists = false;
        
        // if value for field is an array (i.e. check boxes or pick list with multiple answers)
        if (is_array($this->get_field_value($field)) == true) {
            
            foreach ($this->get_field_value($field) as $value) {
                if ($value != '') {
                    $value_exists = true;
                    break;
                }
            }
        
        // else value for field is not an array
        } else {
            // if a value was entered for the field
            if ($this->get_field_value($field) != '') {
                $value_exists = true;
            }
        }
        
        // if a value was entered for the field
        if ($value_exists == true) {
            // do not mark the field with an error
            $_SESSION['software']['liveforms'][$this->form][$this->index][$field]['error'] = false;
            $_SESSION['software']['liveforms'][$this->form][$this->index][$field]['error_message'] = '';
        // else a value was not entered for the field
        } else {
            // mark the field with an error
            $_SESSION['software']['liveforms'][$this->form][$this->index][$field]['error'] = true;
            $_SESSION['software']['liveforms'][$this->form][$this->index][$field]['error_message'] = $error_message;
        }
    }

    function check_form_errors()
    {
        if ($_SESSION['software']['liveforms'][$this->form][$this->index]) {
            foreach ($_SESSION['software']['liveforms'][$this->form][$this->index] as $field) {
                if ($field['error'] == TRUE) {
                    return TRUE;
                }
            }
        }
    }

    function check_field_error($field)
    {
        if ($_SESSION['software']['liveforms'][$this->form][$this->index][$field]['error'] == TRUE) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function create_error_style($field, $style)
    {
        // if field has error
        if ($this->check_field_error($field)) {
            if ($style == '') {
                return $style . 'border: red 2px solid';
            } else {
                return $style . '; border: red 2px solid';
            }
        // else field does not have error
        } else {
            return $style;
        }
    }

    function add_error_style_to_tag($field, $tag) {
        if ($this->check_field_error($field)) {
            $error_style = 'border: red 2px solid';

            // If the style attribute exists in the tag already, then replace it.
            if (preg_match('/\sstyle="(.*?)"/is', $tag, $style_match)) {
                $old_style_html = trim($style_match[0]);
                $style = trim($style_match[1]);

                if ($style != '') {
                    if (mb_substr($style, -1) != ';') {
                        $style .= ';';
                    }

                    $style .= ' ';
                }

                $style .= $error_style;

                return str_replace($old_style_html, ' style="' . $style . '"', $tag);
             
            // Otherwise the style attribute does not exist, so add it to the end of the tag.
            } else {
                return str_replace('>', ' style="' . $error_style . '">', $tag);
            }

        } else {
            return $tag;
        }
    }

    function output_errors()
    {
        if ($_SESSION['software']['liveforms'][$this->form][$this->index]) {
            $error_exists = FALSE;
            $error_messages = array();

            foreach ($_SESSION['software']['liveforms'][$this->form][$this->index] as $field) {
                // if there is an error then remember that and determine if there is an error message to store
                if ($field['error'] == TRUE) {
                    $error_exists = TRUE;
                    
                    // if there is an error message, then add it to array
                    if ($field['error_message'] != '') {
                        $error_messages[] = $field['error_message'];
                    }
                }
            }
            
            $output = '';
            
            // if there is at least one error, then output message
            if ($error_exists == TRUE) {
                $output_error_messages = '';
                
                // if there is not an error message, then just prepare description
                if (count($error_messages) == 0) {
                    $output_error_messages = '<div class="description" style="display: inline;">An error occurred.</div>';
                    
                // else if there is 1 error message, then output description and error message
                } else if (count($error_messages) == 1) {
                    $output_error_messages = '<div style="display: inline; vertical-align: top;"><span class="description">An error occurred:</span> <span class="error">' . $error_messages[0] . '</span></div>';
                    
                // else there is more than 1 error message, so output description and list of error messages
                } else {
                    $output_error_messages =
                        '<div class="description" style="display: inline; vertical-align: top;">An error occurred:</div>
                        <ul>';
                    
                    // loop through the error messages in order to output them
                    foreach ($error_messages as $error_message) {
                        $output_error_messages .= '<li class="error">' . $error_message . '</li>';
                    }
                    
                    $output_error_messages .=
                        '</ul>';
                }
                
                $output =
                    '<div class="software_error">
                        <img src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/images/icon_error.png" alt="Error" title="" class="icon" style="float: none; display: inline-block; height: 1em; width: 1em; min-height: 16px; min-width: 16px;">
                        ' . $output_error_messages . '
                    </div>';
            }

            return $output;
        }
    }

    function mark_error($field, $error_message = '') {
        $_SESSION['software']['liveforms'][$this->form][$this->index][$field]['error'] = TRUE;
        $_SESSION['software']['liveforms'][$this->form][$this->index][$field]['error_message'] = $error_message;
    }

    function unmark_error($field) {
        $_SESSION['software']['liveforms'][$this->form][$this->index][$field]['error'] = FALSE;
        $_SESSION['software']['liveforms'][$this->form][$this->index][$field]['error_message'] = '';
    }

    function unmark_errors() {
        if ($_SESSION['software']['liveforms'][$this->form][$this->index]) {
            foreach ($_SESSION['software']['liveforms'][$this->form][$this->index] as $field => $value) {
                $_SESSION['software']['liveforms'][$this->form][$this->index][$field]['error'] = FALSE;
                $_SESSION['software']['liveforms'][$this->form][$this->index][$field]['error_message'] = '';
            }
        }
    }
    
    function add_notice($notice) {
        $_SESSION['software']['liveforms'][$this->form][$this->index]['notices'][] = $notice;
    }

    function add_warning($warning) {
        $_SESSION['software']['liveforms'][$this->form][$this->index]['warnings'][] = $warning;
    }

    function output_notices()
    {
        $output = '';
        
        // if there is at least one notice, then output message
        if ($_SESSION['software']['liveforms'][$this->form][$this->index]['notices']) {
            $output_notices = '';
                
            // if there is 1 notice, then output description and notice
            if (count($_SESSION['software']['liveforms'][$this->form][$this->index]['notices']) == 1) {
                $output_notices = '<div style="display: inline; vertical-align: top;"><span class="description">Notice:</span> <span class="notice">' . $_SESSION['software']['liveforms'][$this->form][$this->index]['notices'][0] . '</span></div>';
                
            // else there is more than 1 notice, so output description and list of notices
            } else {
                $output_notices =
                    '<div class="description" style="display: inline; vertical-align: top;">Notices:</div>
                    <ul>';
                
                // loop through the notices in order to output them
                foreach ($_SESSION['software']['liveforms'][$this->form][$this->index]['notices'] as $notice) {
                    $output_notices .= '<li class="notice">' . $notice . '</li>';
                }
                
                $output_notices .=
                    '</ul>';
            }
            
            $output =
                '<div class="software_notice">
                    <img src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/images/icon_notice.png" alt="Notice" title="" class="icon" style="float: none; display: inline-block; height: 1em; width: 1em; min-height: 16px; min-width: 16px;">
                    ' . $output_notices . '
                </div>';
        }

        return $output;
    }

    function get_warnings()
    {
        $output = '';
        
        // If there is at least one warning, then output message.
        if ($_SESSION['software']['liveforms'][$this->form][$this->index]['warnings']) {
            $output_warnings = '';
                
            // if there is 1 warning, then output description and warning
            if (count($_SESSION['software']['liveforms'][$this->form][$this->index]['warnings']) == 1) {
                $output_warnings = '<div style="display: inline; vertical-align: top;"><span class="description">Warning:</span> <span class="warning">' . $_SESSION['software']['liveforms'][$this->form][$this->index]['warnings'][0] . '</span></div>';
                
            // else there is more than 1 warning, so output description and list of warnings
            } else {
                $output_warnings =
                    '<div class="description" style="display: inline; vertical-align: top;">Warnings:</div>
                    <ul>';
                
                // loop through the warnings in order to output them
                foreach ($_SESSION['software']['liveforms'][$this->form][$this->index]['warnings'] as $warning) {
                    $output_warnings .= '<li class="warning">' . $warning . '</li>';
                }
                
                $output_warnings .=
                    '</ul>';
            }
            
            $output =
                '<div class="software_warning">
                    <img src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/images/icon_warning.png" alt="Warning" title="" class="icon" style="float: none; display: inline-block; height: 1em; width: 1em; min-height: 16px; min-width: 16px;">
                    ' . $output_warnings . '
                </div>';
        }

        return $output;
    }

    function check_form_notices()
    {
        if ($_SESSION['software']['liveforms'][$this->form][$this->index]['notices']) {
            return true;
        } else {
            return false;
        }
    }
    
    function clear_notices() {
        unset($_SESSION['software']['liveforms'][$this->form][$this->index]['notices']);
    }
    
    function remove_form() {
        unset($_SESSION['software']['liveforms'][$this->form][$this->index]);
    }

    // Same function as above but shorter name.
    function remove() {
        unset($_SESSION['software']['liveforms'][$this->form][$this->index]);
    }

    function remove_forms() {
        unset($_SESSION['software']['liveforms']);
    }

    // This function is a short-hand to get all errors, notices, and warnings.
    function get_messages() {
        return
            $this->output_errors() .
            $this->get_warnings() .
            $this->output_notices();
    }

    // This function will scan a chunk of content for form fields,
    // and update them so they contain the correct value, selected option, and etc.
    // $form_id allows you to only update fields within one form that has a certain id.
    // This is useful for screens with multiple forms.
    function prepare($content, $form_id = '') {

        // If a form id was passed, then try to find a form in the content with the id.
        if ($form_id) {

            preg_match('/<form\s+[^>]*id="' . preg_quote($form_id, '/'). '".*?<\/form>/is',
                $content, $match);

            // If no form was found with the id, then something is wrong, so just return the content.
            if (!$match) {
                return $content;
            }

            // Remember the original full content, so we have it later.
            $full_content = $content;

            // Remeber the old form content, before we update the form's content, so later we can
            // use that content to search and replace.
            $old_form_content = $match[0];

            // Set content to the old form content, so the code below will just update fields
            // in that content.
            $content = $old_form_content;

        }

        preg_match_all('/<input\s.*?>/is', $content, $input_matches, PREG_SET_ORDER);

        foreach ($input_matches as $input_match) {
            $old_tag = $input_match[0];

            $new_tag = $old_tag;

            preg_match('/\sname="(.*?)"/is', $new_tag, $name_match);
            $name = trim($name_match[1]);

            // If the field has brackets on the end of the name, then remove them.
            // Fields like checkboxes, that allow for multiple values, have brackets.
            if (mb_substr($name, -2) == '[]') {
                $name = mb_substr($name, 0, -2);
            }

            $field = $this->get_field($name);

            // If we don't have any info for this field,
            // then just ignore this field and continue to the next one.
            if (!$field) {
                continue;
            }

            preg_match('/\stype="(.*?)"/is', $new_tag, $type_match);
            $type = trim($type_match[1]);

            // If the type is radio or checkbox and there is a value set in the session,
            // then prepare to check the field if it matches that value.
            if (
                (($type == 'radio') || ($type == 'checkbox'))
                &&  (isset($field['value']))
            ) {
                preg_match('/\svalue="(.*?)"/is', $new_tag, $value_match);
                $value = trim($value_match[1]);

                if (
                    ($value == $field['value'])
                    ||
                    (
                        ($type == 'checkbox')
                        && is_array($field['value'])
                        && in_array($value, $field['value'])
                    )
                ) {
                    $field['checked'] = true;

                } else {
                    $field['checked'] = false;
                }

                // Remove the value attribute because we don't want
                // to replace the radio button or checkbox value in the code below,
                // like we do for text fields.
                unset($field['value']);
            }

            // Remove options attribute, because sometimes options are set for a field that might
            // end of being a hidden input or select (e.g. the field that stores a selected option
            // for an attribute for product attributes).  This prevents a PHP error where it is not
            // valid to output the options array as if it was a scalar value.  Because this is for
            // an input field, we know we don't need to deal with options.
            unset($field['options']);

            foreach ($field as $attribute => $value) {
                if ($attribute == 'error' || $attribute == 'error_message') {
                    continue;
                }

                // If this is a boolean attribute then prepare attribute html
                // without the equal sign and quote value (e.g. <input required>).
                if (is_bool($value)) {
                    if ($value) {
                        $new_attribute_html = ' ' . h($attribute);
                    } else {
                        $new_attribute_html = '';
                    }

                // Otherwise, this is not a boolean attribute, so prepare attribute html
                // with the equal sign and quote value (e.g. <input class="example">).
                } else {

                    // if this field is a credit card number field and encryption is enabled, then decrypt value
                    if (
                        ($attribute == 'value')
                        && ($name == 'card_number')
                        && (defined('ENCRYPTION_KEY') == TRUE)
                        && (extension_loaded('mcrypt') == TRUE)
                        && (in_array('rijndael-256', mcrypt_list_algorithms()) == TRUE)
                    ) {
                        $value = decrypt_credit_card_number($value, ENCRYPTION_KEY);
                    }

                    $new_attribute_html = ' ' . h($attribute) . '="' . h($value) . '"';
                }

                // If the attribute exists in the tag already, then replace it.
                if (preg_match('/\s' . preg_quote($attribute) . '(=".*?")?/is', $new_tag, $attribute_match)) {
                    $old_attribute_html = $attribute_match[0];
                    $new_tag = str_replace($old_attribute_html, $new_attribute_html, $new_tag);
                 
                // Otherwise the attribute does not exist, so add it to the end of the tag.
                } else {
                    $new_tag = str_replace('>', $new_attribute_html . '>', $new_tag);
                }
            }

            $new_tag = $this->add_error_style_to_tag($name, $new_tag);

            $content = str_replace($old_tag, $new_tag, $content);
        }

        preg_match_all('/(<select\s.*?>).*?<\/select>/is', $content, $select_matches, PREG_SET_ORDER);

        foreach ($select_matches as $select_match) {
            $old_tag = $select_match[0];
            $old_start_tag = $select_match[1];

            $new_tag = $old_start_tag;

            preg_match('/\sname="(.*?)"/is', $new_tag, $name_match);
            $name = trim($name_match[1]);

            // If the field has brackets on the end of the name, then remove them.
            // If a pick list supports multi-selection, then it has brackets in the name.
            if (mb_substr($name, -2) == '[]') {
                $name = mb_substr($name, 0, -2);
            }

            $field = $this->get_field($name);

            // If we don't have any info for this field,
            // then just ignore this field and continue to the next one.
            if (!$field) {
                continue;
            }

            // Remember the selected value and then remove the value attribute
            // because we don't want to add a value attribute to the select tag
            // like we do for text fields.

            if (isset($field['value'])) {
                $set_value = $field['value'];
                unset($field['value']);

            } else {
                unset($set_value);
            }

            // Remember the options and then remove the options attribute
            // because we don't want to add an options attribute to the select tag.
            $options = $field['options'];
            unset($field['options']);

            foreach ($field as $attribute => $value) {
                if ($attribute == 'error' || $attribute == 'error_message') {
                    continue;
                }

                // If this is a boolean attribute then prepare attribute html
                // without the equal sign and quote value (e.g. <select required>).
                if (is_bool($value)) {
                    if ($value) {
                        $new_attribute_html = ' ' . h($attribute);
                    } else {
                        $new_attribute_html = '';
                    }

                // Otherwise, this is not a boolean attribute, so prepare attribute html
                // with the equal sign and quote value (e.g. <select class="example">).
                } else {
                    $new_attribute_html = ' ' . h($attribute) . '="' . h($value) . '"';
                }

                // If the attribute exists in the tag already, then replace it.
                if (preg_match('/\s' . preg_quote($attribute) . '(=".*?")?/is', $new_tag, $attribute_match)) {
                    $old_attribute_html = $attribute_match[0];
                    $new_tag = str_replace($old_attribute_html, $new_attribute_html, $new_tag);
                 
                // Otherwise the attribute does not exist, so add it to the end of the tag.
                } else {
                    $new_tag = str_replace('>', $new_attribute_html . '>', $new_tag);
                }
            }

            $options_html = '';

            if (is_array($options)) {
                foreach ($options as $option_name => $option_value) {
                    // if the option is a starting optgroup, then prepare starting optgroup
                    if ($option_value == '<optgroup>') {
                        $options_html .= '<optgroup label="' . $option_name . '">';
                        
                    // else if option is an ending optgroup, then prepare ending optgroup
                    } elseif ($option_value == '</optgroup>') {
                        $options_html .= '</optgroup>';
                        
                    // else option is a standard option, so prepare standard option
                    } else {
                        // If option value is an array, then attributes were passed in an array, so deal with them.
                        if (is_array($option_value)) {
                            // If there is a label in the array, then use that for the option name.
                            // We sometimes pass the label in the array instead of the key in cases where there may be multiple options
                            // with the same label/name. This can happen if there are multiple product groups with the same name.
                            if (isset($option_value['label'])) {
                                $option_name = $option_value['label'];
                            }

                            $default_selected = $option_value['default_selected'];
                            $option_value = $option_value['value'];
                            
                        // else option value is not an array, so default selected values were not passed
                        } else {
                            $default_selected = 0;
                        }
                        
                        if (
                            ($set_value == $option_value)
                            ||
                            (
                                is_array($set_value)
                                && in_array($option_value, $set_value)
                            )
                        ) {
                            $selected = ' selected';

                        } else {
                            $selected = '';
                        }
                        
                        $options_html .= '<option value="' . $option_value . '"' . $selected . '>' . $option_name . '</option>';
                    }
                }
            }

            $new_tag = $this->add_error_style_to_tag($name, $new_tag);

            $content = str_replace($old_tag, $new_tag . $options_html . '</select>', $content);
        }

        preg_match_all('/(<textarea\s.*?>)(.*?)<\/textarea>/is', $content, $textarea_matches, PREG_SET_ORDER);

        foreach ($textarea_matches as $textarea_match) {
            $old_tag = $textarea_match[0];
            $old_start_tag = $textarea_match[1];
            $old_content = $textarea_match[2];

            $new_tag = $old_start_tag;

            preg_match('/\sname="(.*?)"/is', $new_tag, $name_match);
            $name = trim($name_match[1]);

            $field = $this->get_field($name);

            // If we don't have any info for this field,
            // then just ignore this field and continue to the next one.
            if (!$field) {
                continue;
            }

            // Remember the stored value and then remove the value attribute
            // because we don't want to add a value attribute to the textarea tag
            // like we do for text fields.

            if (isset($field['value'])) {
                $set_value = $field['value'];
                unset($field['value']);

            } else {
                unset($set_value);
            }

            foreach ($field as $attribute => $value) {
                if ($attribute == 'error' || $attribute == 'error_message') {
                    continue;
                }
                
                // If this is a boolean attribute then prepare attribute html
                // without the equal sign and quote value (e.g. <textarea required>).
                if (is_bool($value)) {
                    if ($value) {
                        $new_attribute_html = ' ' . h($attribute);
                    } else {
                        $new_attribute_html = '';
                    }

                // Otherwise, this is not a boolean attribute, so prepare attribute html
                // with the equal sign and quote value (e.g. <textarea class="example">).
                } else {
                    $new_attribute_html = ' ' . h($attribute) . '="' . h($value) . '"';
                }

                // If the attribute exists in the tag already, then replace it.
                if (preg_match('/\s' . preg_quote($attribute) . '(=".*?")?/is', $new_tag, $attribute_match)) {
                    $old_attribute_html = $attribute_match[0];
                    $new_tag = str_replace($old_attribute_html, $new_attribute_html, $new_tag);
                 
                // Otherwise the attribute does not exist, so add it to the end of the tag.
                } else {
                    $new_tag = str_replace('>', $new_attribute_html . '>', $new_tag);
                }
            }

            $new_tag = $this->add_error_style_to_tag($name, $new_tag);

            // If there was a value set for this field in the session, then use for the content.
            if (isset($set_value)) {
                $new_content = h($set_value);
            } else {
                $new_content = $old_content;
            }

            $content = str_replace($old_tag, $new_tag . $new_content . '</textarea>', $content);
        }

        // If a form id was passed, then inject the update content for just that one form
        // into the original full content.
        if ($form_id) {
            $content = str_replace($old_form_content, $content, $full_content);
        }

        return $content;
    }

    // Used in order to determine if values should be prefilled in a form
    // that has not been submitted by the visitor yet.
    
    function is_empty() {
        if (isset($_SESSION['software']['liveforms'][$this->form][$this->index])) {
            return false;
        } else {
            return true;
        }
    }

    // Check if we should prefill form or not.  We consider that the form should be prefilled, if 
    // add_fields_to_session has not not been called yet. We initially added is_empty function above
    // to accomplish this but that function has issues where if a notice is added then the form is
    // considered to be not empty, however this creates issues, because we still want to prefill
    // fields. We should eventually remove is_empty function above.

    function prefill() {

        // If the prefill value has not been set or it is true, then return true.
        if (
            !isset($_SESSION['software']['liveforms'][$this->form][$this->index]['__prefill'])
            or $_SESSION['software']['liveforms'][$this->form][$this->index]['__prefill']
        ) {
            return true;

        // Otherwise add_fields_to_session has set it to false, so return false.
        } else {
            return false;
        }
    }
}