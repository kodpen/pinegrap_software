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

function generate_layout_content($page_id) {

    $page_type = db_value("SELECT page_type FROM page WHERE page_id = '" . e($page_id) . "'");

    $template_name = str_replace(' ', '_', $page_type) . '.php';

    $content = file_get_contents(dirname(__FILE__) . '/templates/' . $template_name);

    switch ($page_type) {

        case 'custom form':

            $search = '<?=eval(\'?>\' . generate_form_layout_content(array(\'page_id\' => $page_id)))?>';

            $replace = generate_form_layout_content(array('page_id' => $page_id));

            $content = str_replace($search, $replace, $content);

            break;


        case 'billing information':

            $search = '<?=eval(\'?>\' . generate_form_layout_content(array(\'page_id\' => $page_id, \'indent\' => \'        \')))?>';

            $replace = generate_form_layout_content(array(
                'page_id' => $page_id,
                'indent' => '        '));

            $content = str_replace($search, $replace, $content);

            break;

        case 'express order':

            // Add custom shipping form

            $search = '<?=eval(\'?>\' . generate_form_layout_content(array(\'page_id\' => $page_id, \'form_type\' => \'shipping\', \'indent\' => \'                                                \')))?>';

            $replace = generate_form_layout_content(array(
                'page_id' => $page_id,
                'form_type' => 'shipping',
                'indent' => '                                                '));

            $content = str_replace($search, $replace, $content);

            // Add custom billing form

            $search = '<?=eval(\'?>\' . generate_form_layout_content(array(\'page_id\' => $page_id, \'form_type\' => \'billing\', \'indent\' => \'            \')))?>';

            $replace = generate_form_layout_content(array(
                'page_id' => $page_id,
                'form_type' => 'billing',
                'indent' => '            '));

            $content = str_replace($search, $replace, $content);

            break;

        case 'shipping address and arrival':

            $search = '<?=eval(\'?>\' . generate_form_layout_content(array(\'page_id\' => $page_id, \'indent\' => \'        \')))?>';

            $replace = generate_form_layout_content(array(
                'page_id' => $page_id,
                'indent' => '        '));

            $content = str_replace($search, $replace, $content);

            break;

    }

    // Embed template content for every render call.
    $content = replace_render($content);

    return $content;

}

// Replaces every render call in the content with the template content.

function replace_render($content) {

    // We get the spaces before the render call so that we can indent
    // the embedded template content by that amount, so indentation is preserved.
    preg_match_all('/(\h*)<\?=render\(.*?\'template\'\s*=>\s*\'(.*?)\'.*?\?>/ms', $content, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {

        $indent = $match[1];
        $template_name = $match[2];

        // If a template name was not found in the render call,
        // then skip to the next render call.
        if (!$template_name) {
            continue;
        }

        $render_content = @file_get_contents(dirname(__FILE__) . '/templates/' . $template_name);

        // If a template file could not be found for that template name,
        // then skip to the next render call.
        if (!$render_content) {
            continue;
        }

        // If indent content was found before the render call, then add
        // all of that white-space to the beginning of every line.
        if ($indent) {
            $render_content = preg_replace('/^/m', $indent, $render_content);
        }

        // Use recursion to also replace render calls in this template.
        $render_content = replace_render($render_content);

        // Replace render call with template content.
        $content = str_replace($match[0], $render_content, $content);

    }

    return $content;
}

// Generates layout code for custom form, custom billing form, and etc.
function generate_form_layout_content($properties) {

    $page_id = $properties['page_id'];
    $form_type = $properties['form_type'];

    // If an indent was passed, then use that.
    if (isset($properties['indent'])) {
        $indent = $properties['indent'];

    // Otherwise an indent was not passed, so set to default (4 spaces).
    } else {
        $indent = '    ';
    }

    // Store the base indent because we might change it below,
    // but we will need to revert back to it after we are done with each field.
    $base_indent = $indent;

    $page_type = db_value("SELECT page_type FROM page WHERE page_id = '" . e($page_id) . "'");

    $form_type_filter = "";

    // If this is an express order page then add filter to get just shipping or billing fields
    if ($page_type == 'express order') {
        $form_type_filter = "AND (form_type = '" . e($form_type) . "')";
    }

    $fields = db_items(
        "SELECT
            id,
            name,
            type,
            contact_field,
            default_value,
            use_folder_name_for_default_value,
            multiple,
            office_use_only
        FROM form_fields
        WHERE
            (page_id = '" . e($page_id) . "')
            $form_type_filter
        ORDER BY sort_order",
        'name');

    $content = '';

    if ($page_type == 'express order' and $form_type == 'shipping') {
        $array_name = '$shipping_field';
    } else {
        $array_name = '$field';
    }

    foreach ($fields as $field) {

        if ($field['office_use_only']) {
            $content .= $indent . '<?php if ($office_use_only): ?>' . "\n";

            // Prepare to indent this field more than normal so it is indented inside
            // office use only check.
            $indent .= '    ';
        }

        // If this is a radio button or check box field, then get options,
        // so we can output them below.
        if (($field['type'] == 'radio button') || ($field['type'] == 'check box')) {
            $options = db_items(
                "SELECT
                    label,
                    value
                FROM form_field_options
                WHERE form_field_id = '" . $field['id'] . "'
                ORDER BY sort_order");
        }

        $content .= $indent . '<!-- ' . $field['name'] . ' -->' . "\n";

        if ($page_type == 'custom form') {
            $name = $field['id'];

        // Otherwise if this is a custom shipping form on an express order page, then prepare name
        // that is unique because there might be a shipping form for each recipient.
        } else if ($page_type == 'express order' and $form_type == 'shipping') {
            $name = 'shipping_<?=$recipient[\'id\']?>_field_' . $field['id'];

        } else {
            $name = 'field_' . $field['id'];
        }

        switch ($field['type']) {
            case 'text box':
            case 'email address':
            case 'date':
            case 'date and time':
            case 'time':
                if ($field['type'] == 'email address') {
                    $type = 'email';
                } else {
                    $type = 'text';
                }

                if ($field['type'] == 'time') {
                    $help = ' (Format: h:mm AM/PM)';
                } else {
                    $help = '';
                }

                $content .=
                    $indent . '<div class="form-group">' . "\n" .
                    $indent . '    <label for="' . $name . '"><?=' . $array_name . '[\'' . $field['id'] . '\'][\'label\']?></label>' . "\n" .
                    $indent . '    <input type="' . $type . '" name="' . $name . '" id="' . $name . '" class="form-control">' . $help . "\n" .
                    $indent . '</div>' . "\n";

                break;

            case 'text area':
                $content .=
                    $indent . '<div class="form-group">' . "\n" .
                    $indent . '    <label for="' . $name . '"><?=' . $array_name . '[\'' . $field['id'] . '\'][\'label\']?></label>' . "\n" .
                    $indent . '    <textarea name="' . $name . '" id="' . $name . '" class="form-control"></textarea>' . "\n" .
                    $indent . '</div>' . "\n";

                break;

            case 'pick list':
                $html_field_name = $name;

                // If this pick list allows multi-selection,
                // then update html field name to support that.
                if ($field['multiple']) {
                    $html_field_name .= '[]';
                }

                $content .=
                    $indent . '<div class="form-group">' . "\n" .
                    $indent . '    <label for="' . $name . '"><?=' . $array_name . '[\'' . $field['id'] . '\'][\'label\']?></label>' . "\n" .
                    $indent . '    <select name="' . $html_field_name . '" id="' . $name . '" class="form-control"></select>' . "\n" .
                    $indent . '</div>' . "\n";

                break;

            case 'radio button':
                $content .=
                    $indent . '<div><?=' . $array_name . '[\'' . $field['id'] . '\'][\'label\']?></div>' . "\n" .
                    $indent . "\n";

                foreach ($options as $index => $option) {
                    // If this is not the first option, then add new line for separation.
                    if ($index != 0) {
                        $content .= $indent . "\n";
                    }

                    $content .=
                        $indent . '<div class="radio">' . "\n" .
                        $indent . '    <label>' . "\n" .
                        $indent . '        <input type="radio" name="' . $name . '" value="' . h($option['value']) . '">' . "\n" .
                        $indent . '        ' . h($option['label']) . "\n" .
                        $indent . '    </label>' . "\n" .
                        $indent . '</div>' . "\n";
                }

                break;

            case 'check box':
                $html_field_name = $name;

                // If this check box field has multiple check boxes,
                // then update html field name to support that.
                if (count($options) > 1) {
                    $html_field_name .= '[]';
                }

                $content .=
                    $indent . '<div><?=' . $array_name . '[\'' . $field['id'] . '\'][\'label\']?></div>' . "\n" .
                    $indent . "\n";

                foreach ($options as $index => $option) {
                    // If this is not the first option, then add new line for separation.
                    if ($index != 0) {
                        $content .= $indent . "\n";
                    }

                    $content .=
                        $indent . '<div class="checkbox">' . "\n" .
                        $indent . '    <label>' . "\n" .
                        $indent . '        <input type="checkbox" name="' . $html_field_name . '" value="' . h($option['value']) . '">' . "\n" .
                        $indent . '        ' . h($option['label']) . "\n" .
                        $indent . '    </label>' . "\n" .
                        $indent . '</div>' . "\n";
                }

                break;

            case 'file upload':
                $content .=
                    $indent . '<div class="form-group">' . "\n" .
                    $indent . '    <label for="' . $name . '"><?=' . $array_name . '[\'' . $field['id'] . '\'][\'label\']?></label>' . "\n" .
                    $indent . '    <input type="file" name="' . $name . '" id="' . $name . '" class="form-control">' . "\n" .
                    $indent . '</div>' . "\n";

                break;

            case 'information':
                $content .=
                    $indent . '<?=' . $array_name . '[\'' . $field['id'] . '\'][\'information\']?>' . "\n";

                break;
        }

        // Now that we are done with outputting the field, change indent back to base.
        $indent = $base_indent;

        if ($field['office_use_only']) {
            $content .= $indent . '<?php endif ?>' . "\n";
        }

        $content .= $indent . "\n";
    }

    return $content;

}