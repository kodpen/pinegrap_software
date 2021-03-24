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

// this function generates CSS based on the rules that are passed into it
function generate_system_theme_css($css_properties)
{
    $output = '';
 
    // Build a css include statement to retrieve all google fonts necessary for this theme like:
    // @import url(https://fonts.googleapis.com/css?family=Tangerine|Droid+Sans);

    // first, find all the google font families in the theme
    $google_font_families = get_google_font_families($css_properties);
    
    // then, build the @import url statement
    $google_fonts_url = '';
    $google_font_found = FALSE;
    $google_length = 0;
    $chars = array ("'","-");

    foreach ($google_font_families as $key => $value) {
        $google_font_found = TRUE;
        $google_font = mb_substr($value,0,mb_strpos($value,'-',2)+2);   // get the first google font family in the font stack ('-Tangerine-' or '-Droid Sans-')
        $google_font = str_replace($chars,'',$google_font);       //  remove the single quotes and dashes
        $google_font = str_replace(' ','+',$google_font);         // replace any spaces with + sign (Tangerine and Droid+Sans)
                            
        // build @import url statement with all google font families found
        // if this is the first font family found, create the @import statement
        if ($google_fonts_url == '') {
            // We can't use the // trick for the scheme for automatic scheme detection
            // because IE 7 & 8 has a bug where it will download the resource twice for link and import features.
            // This is probaably fixed in IE 9. We are going to always use https for now until IE 7 & 8
            // are no longer being used.
            $google_fonts_url = '@import url(https://fonts.googleapis.com/css?family=' . $google_font;

        } else { // append the font family to the @import statement
            $google_fonts_url .= '|' . $google_font;
        }
    }
    
    // lastly, if an @import url statement was created, then add it
    if ($google_font_found == TRUE) {
        $google_fonts_url .= ');';
        // Browser rules dictate that the @import statement MUST be the first thing in the css file or it won't work.
        $output .= $google_fonts_url . ' /* browsers require @import before any other css */' . "\r\n";
    }

    // organize the areas in the array so that the css file is written in the correct order
    $css_properties = 
        array(
            'site_wide' => $css_properties['site_wide'],
            'body' => $css_properties['body'],
            'site_border' => $css_properties['site_border'],
            'email_border' => $css_properties['email_border'],
            'mobile_border' => $css_properties['mobile_border'],
            'site_top' => $css_properties['site_top'],
            'site_header' => $css_properties['site_header'],
            'area_border' => $css_properties['area_border'],
            'area_header' => $css_properties['area_header'],
            'page_wrapper' => $css_properties['page_wrapper'],
            'page_border' => $css_properties['page_border'],
            'page_header' => $css_properties['page_header'],
            'page_content' => $css_properties['page_content'],
            'page_content_left' => $css_properties['page_content_left'],
            'page_content_right' => $css_properties['page_content_right'],
            'sidebar' => $css_properties['sidebar'],
            'page_footer' => $css_properties['page_footer'],
            'area_footer' => $css_properties['area_footer'],
            'site_footer_border' => $css_properties['site_footer_border'],
            'site_footer' => $css_properties['site_footer'],
            'ad_region' => $css_properties['ad_region'],
            'menu_region' => $css_properties['menu_region']
        );

    $site_wide_properties = array();
        
    // loop through the areas to put the site wide properties into it's own array and then remove it from the css properties
    foreach($css_properties as $area => $objects) {
        if ($area == 'site_wide') {
            $site_wide_properties = $objects;
            unset($css_properties[$area]);
            break;
        }
    }
    
    // output pre styling
    $output .= $site_wide_properties['base_object']['base_module']['pre_styling'];
    
    // add a the body css rules to the output
    $output .= 'body' . "\r\n" . 
        '{' . "\r\n" . 
        'padding: 0.01em;' . "\r\n" . 
        'margin: 0em 0em 0em 0em;' . "\r\n";
        'font-size: 100%;' . "\r\n";  //don't inherit OS browser style defaults
        
    $global_font_family = 'sans-serif';  // set default if none
         
    // if there is a font family property, then add it and set the global font family to be used later
    if ($site_wide_properties['base_object']['text']['font_family'] != '') {
        // check for google font (and remove leading font used only to identify a google font)
        $allfonts = $site_wide_properties['base_object']['text']['font_family'];
        if (mb_substr($allfonts, 0, 2) == '\'-') {
            // strip off leading font
            $global_font_family = mb_substr($allfonts,mb_strpos($allfonts,',',2)+1);
        } else {
            // don't strip off any fonts
            $global_font_family = $allfonts;
        }
        $output .= 'font-family: ' . $global_font_family . ';' . "\r\n";
        
    // else set a default font of arial and set the global font family to be used later
    } else {
        $output .= 'font-family: sans-serif;' . "\r\n";
    }
    
    // if there is a font color property, then add it
    if ($site_wide_properties['base_object']['text']['font_color'] != '') {
        $output .= 'color: #' . $site_wide_properties['base_object']['text']['font_color'] . ';' . "\r\n";
    
    // else set a default font color of blank
    } else {
        $output .= 'color: #000000;' . "\r\n";
        $site_wide_properties['base_object']['text']['font_color'] = '000000';
    }

    $font_color = $site_wide_properties['base_object']['text']['font_color'];
    
    // if there is a font size property, then add it
    if ($site_wide_properties['base_object']['text']['font_size'] != '') {
        $output .= 'font-size: ' . $site_wide_properties['base_object']['text']['font_size'] . ';' . "\r\n";
        '}' . "\r\n";
    // else set a default font size of .75em which is about 12px
    } else {
        $output .= 'font-size: .75em;' . "\r\n";
        '}' . "\r\n";
        $site_wide_properties['base_object']['text']['font_size'] = '.75em';
    }
    
    // if there is a font style property, then add it
    if ($site_wide_properties['base_object']['text']['font_style'] != '') {
        $output .= 'font-style: ' . $site_wide_properties['base_object']['text']['font_style'] . ';' . "\r\n";
    }
    
    // if there is a font weight property, then add it
    if ($site_wide_properties['base_object']['text']['font_weight'] != '') {
        $output .= 'font-weight: ' . $site_wide_properties['base_object']['text']['font_weight'] . ';' . "\r\n";
    }
    
    // if there is a line height property, then add it
    if ($site_wide_properties['base_object']['text']['line_height'] != '') {
        $output .= 'line-height: ' . $site_wide_properties['base_object']['text']['line_height'] . ';' . "\r\n";
    
    // else set a default font size of 1.25em
    } else {
        $output .= 'line-height: 1.25em;' . "\r\n";
    }
    
    // if there is a background property in the body area of the css properties array, then output it
    if ($css_properties['body']['base_object']['base_module']['background_type'] != '') {
        $output .= output_css_properties($css_properties['body']['base_object']['base_module']);
        
    // else default the background to white
    } else {
        $output .= 'background: #FFFFFF;' . "\r\n";
    }
    
    // remove the body properties from the array, this is so that we do not to loop through it again later
    unset($css_properties['body']);
    
    // close the body tag
    $output .= '}' . "\r\n";
    
    // if there is no font size property, then set inputs so they don't inherit OS browser style defaults
    if ($site_wide_properties['base_object']['text']['font_size'] == '') {
        $output .= '.software_input_radio, .software_input_checkbox' . "\r\n" .   
        '{' . "\r\n" . 
        'height: 0.75em;' . "\r\n" . 
        'width: 0.75em;' . "\r\n" . 
        '}' . "\r\n";
    }
    
    // if there is general header properties in the site wide properties, then output them
    if (empty($site_wide_properties['base_object']['headings_general']) == FALSE) {
        $output .= 
            'h1, h2, h3, h4, h5, h6' . "\r\n" .
            '{' . "\r\n";
        
        // build and output the properties
        $output .= output_css_properties($site_wide_properties['base_object']['headings_general']);
        
        $output .= '}' . "\r\n";
    }
    
    // loop through each site wide heading and output it
    for ($i = 1; $i <= 6; $i++) {
        // if there is heading 1 properties in the site wide properties, then output them
        if (empty($site_wide_properties['base_object']['heading_' . $i]) == FALSE) {
            $output .= 
                'h' . $i . "\r\n" .
                '{' . "\r\n";
            
            // build and output the properties
            $output .= output_css_properties($site_wide_properties['base_object']['heading_' . $i]);
            
            $output .= '}' . "\r\n";
        }
    }
    
    // if there are link properties in the site wide properties, then output them
    if (empty($site_wide_properties['base_object']['links']) == FALSE) {
        $output .= 
            'a:link, a:active, a:visited' . "\r\n" .
            '{' . "\r\n";
        
        // build and output the properties
        $output .= output_css_properties($site_wide_properties['base_object']['links']);
        
        $output .= '}' . "\r\n";
    }
    
    // if there are link hover properties in the site wide properties, then output them
    if (empty($site_wide_properties['base_object']['links_hover']) == FALSE) {
        $output .= 
            'a:hover, a:focus' . "\r\n" .
            '{' . "\r\n";
        
        // build and output the properties
        $output .= output_css_properties($site_wide_properties['base_object']['links_hover']);
        
        $output .= '}' . "\r\n";
    }
    
    // if there is paragraph properties in the site wide properties, then output them
    if (empty($site_wide_properties['base_object']['paragraph']) == FALSE) {
        $output .= 
            'p' . "\r\n" .
            '{' . "\r\n";
        
        // build and output the properties
        $output .= output_css_properties($site_wide_properties['base_object']['paragraph']);
        
        $output .= '}' . "\r\n";
    }
    
    $output_input_properties = '';
    
    // if there are input properties, then output them
    if (empty($site_wide_properties['base_object']['input']) == FALSE) {
        $output_input_properties = output_css_properties($site_wide_properties['base_object']['input']);
    }

    // output input styling
    $output .=
        'input, select' . "\r\n" .
        '{' . "\r\n" .
            'font-family: ' . $global_font_family . ';' . "\r\n" .  // add body font in case form field font != text font in theme designer
        '}' . "\r\n" .
        'input, select,' . "\r\n" .
        '.software_input_text,' . "\r\n" .
        '.software_input_password,' . "\r\n" . 
        '.software_select,' . "\r\n" . 
        '.software_textarea' . "\r\n" . 
        '{' . "\r\n" .
            'margin: 0em;' . "\r\n" . 
            'vertical-align: middle;' . "\r\n" .
            'font-size: 100%;' . "\r\n" .   //don't inherit OS browser style defaults
            $output_input_properties .
        '}' . "\r\n" .
        'input[type=submit]' . "\r\n" . // override for chrome and safari defaults for input buttons (necessary if hover properties are used in Theme Designer)
        //'select.software_select' . "\r\n" .  // don't override pick lists in v8.6 since chrome and safari lose pick list drop down button
        '{' . "\r\n" .
            '-webkit-appearance: none;' . "\r\n" .  
        '}' . "\r\n" .
        '.software_comments .software_textarea' . "\r\n" .  // extend software_textarea when inside software_comments to be about 100% of width
        '{' . "\r\n" .
            'width: 98%;' . "\r\n" . 
        '}' . "\r\n";

    // get the input field colors to share with pick list options
    $input_field_color = $site_wide_properties['base_object']['input']['font_color'];
    $input_field_background_color = $site_wide_properties['base_object']['input']['background_color'];
    $input_field_padding_left = $site_wide_properties['base_object']['input']['padding_left'];
    $input_field_padding_right = $site_wide_properties['base_object']['input']['padding_right'];
    if ($input_field_color == '') {
        $input_field_color = "000000";
    }
    if ($input_field_background_color == '') {
        $input_field_background_color = "FFFFFF";
    }
    if ($input_field_padding_left == '') {
        $input_field_padding_left = "0";
    }
    if ($input_field_padding_right == '') {
        $input_field_padding_right = "0";
    }
    // set picklist dropdown styling but then override/remove others that don't work well in pick lists
    $output .= 'select.software_select option' . "\r\n" . 
        '{' . "\r\n" .
            'color: #' . $input_field_color . ' !important;' . "\r\n" .
            'background: #' . $input_field_background_color . ' !important;' . "\r\n" .
            'border: none !important;' . "\r\n" .
            'margin: 0 !important;' . "\r\n" .
            'padding: 0 ' . $input_field_padding_right . ' 0 ' . $input_field_padding_left . ' !important;' . "\r\n" .
            '-moz-border-radius: 0 !important;' . "\r\n" .
            '-webkit-border-radius: 0 !important;' . "\r\n" .
            'border-radius: 0 !important;' . "\r\n" .
            '-moz-box-shadow: 0 !important;' . "\r\n" .
            '-webkit-box-shadow: 0 !important;' . "\r\n" .
            'box-shadow: 0 !important;' . "\r\n" .
        '}' . "\r\n";
    //set radio button and checkbox to grow in size based on base font (override OS browser style defaults. 1em works for both percent and fixed base font-size.)
    $output .= '.software_input_radio, .software_input_checkbox' . "\r\n" .   
    '{' . "\r\n" . 
    'height: 1em;' . "\r\n" . 
    'width: 1em;' . "\r\n" .
    'line-height: 1em;' . "\r\n" .
    '}' . "\r\n";
    
    $primary_color = '';
    $primary_color_tint = '';
        
    // if there is a primary color, then set that to the border color
    if ($site_wide_properties['base_object']['base_module']['primary_color'] != '') {
        $primary_color = $site_wide_properties['base_object']['base_module']['primary_color'];
        
    // else set the primary color to black
    } else {
        $primary_color = '000000';
    }

    $primary_color_tint = color_tint($primary_color, 50);

    // set primary button colors for classes
    if ($site_wide_properties['base_object']['primary_buttons']['background_color'] != '') {
        $primary_button_background = $site_wide_properties['base_object']['primary_buttons']['background_color'];
    }  else {
        $primary_button_background = $primary_color;
    }
    if ($site_wide_properties['base_object']['primary_buttons']['font_color'] != '') {
        $primary_button_color = $site_wide_properties['base_object']['primary_buttons']['font_color'];
    }  else {
        $primary_button_color = $primary_color;
    }
    
    $secondary_color = '';
        
    // if there is a secondary color, then set that to the border color
    if ($site_wide_properties['base_object']['base_module']['secondary_color'] != '') {
        $secondary_color = $site_wide_properties['base_object']['base_module']['secondary_color'];
        
    // else set the secondary color to white
    } else {
        $secondary_color = 'FFFFFF';
    }

    // set secondary button colors for classes
    if ($site_wide_properties['base_object']['secondary_buttons']['background_color'] != '') {
        $secondary_button_background = $site_wide_properties['base_object']['secondary_buttons']['background_color'];
    }  else {
        $secondary_button_background = $secondary_color;
    }
    if ($site_wide_properties['base_object']['secondary_buttons']['font_color'] != '') {
        $secondary_button_color = $site_wide_properties['base_object']['secondary_buttons']['font_color'];
    }  else {
        $secondary_button_color = $secondary_color;
    }
    
    // output global styles
    $output .=
        'b' . "\r\n" . 
        '{' . "\r\n" .
            'padding: 0;' . "\r\n" . 
            'margin: 0;' . "\r\n" . 
        '}' . "\r\n" . 
        'blockquote' . "\r\n" . 
        '{' . "\r\n" .
            'font-style: italic;' . "\r\n" . 
            'font-size: 110%;' . "\r\n" . 
        '}' . "\r\n" . 
        'blockquote p' . "\r\n" . 
        '{' . "\r\n" .
            'padding: .5em .75em;' . "\r\n" . 
            'margin: 0em;' . "\r\n" .
        '}' . "\r\n" . 
        'blockquote,ul,ol' . "\r\n" . 
        '{' . "\r\n" .
            'margin-top: 0em;' . "\r\n" . 
            'margin-bottom: 1em;' . "\r\n" . 
        '}' . "\r\n" . 
        'hr' . "\r\n" . 
        '{' . "\r\n" .
            'background: #' . $primary_color . ';' . "\r\n" . 
            'color: #' . $primary_color . ';' . "\r\n" . 
            'border: 1px;' . "\r\n" . 
            'height: 1px;' . "\r\n" . 
        '}' . "\r\n" . 
        'img, a img' . "\r\n" . 
        '{' . "\r\n" .
            'border: none;' . "\r\n" . 
            'text-decoration: none;' . "\r\n" .
        '}' . "\r\n" . 
        'pre' . "\r\n" . 
        '{' . "\r\n" .
            'font-size: larger;' . "\r\n" .
        '}' . "\r\n" . 
        'ol' . "\r\n" . 
        '{' . "\r\n" .
            'list-style-type: decimal;' . "\r\n" .
        '}' . "\r\n" . 
        'ul' . "\r\n" . 
        '{' . "\r\n" .
            'list-style-type: disc;' . "\r\n" .
        '}' . "\r\n";
    
    // loop through the css properties to combine the layout, background borders and spacing, and the text modules
    foreach($css_properties as $area => $objects) {
        if (is_array($objects) == TRUE) {
            foreach($objects as $object => $modules) {
                // loop through each module to combine the general modules
                foreach($modules as $module => $properties) {
                    $combined_modules = array();
                    
                    // if this is one of the general modules, then add it to the combined modules array and remove it from the css properties so that it is not addes twice
                    if (
                        ($module == 'layout')
                        || ($module == 'background_borders_and_spacing')
                        || ($module == 'text')
                    ) {
                        // if this module has properties, then merge it to the combined modules array and remove it from the css properties array
                        if ((is_array($css_properties[$area][$object]['layout']) == TRUE) && (empty($css_properties[$area][$object]['layout']) == FALSE)) {
                            $combined_modules = array_merge($combined_modules, $css_properties[$area][$object]['layout']);
                            unset($css_properties[$area][$object]['layout']);
                        }
                        
                        // if this module has properties, then merge it to the combined modules array and remove it from the css properties array
                        if ((is_array($css_properties[$area][$object]['background_borders_and_spacing']) == TRUE) && (empty($css_properties[$area][$object]['background_borders_and_spacing']) == FALSE)) {
                            $combined_modules = array_merge($combined_modules, $css_properties[$area][$object]['background_borders_and_spacing']);
                            unset($css_properties[$area][$object]['background_borders_and_spacing']);
                        }
                        
                        // if this module has properties, then merge it to the combined modules array and remove it from the css properties array
                        if ((is_array($css_properties[$area][$object]['text']) == TRUE) && (empty($css_properties[$area][$object]['text']) == FALSE)) {
                            $combined_modules = array_merge($combined_modules, $css_properties[$area][$object]['text']);
                            unset($css_properties[$area][$object]['text']);
                        }
                        
                        // if there are combined modules, then add them to the base module
                        if (isset($combined_modules) == TRUE) {
                            $css_properties[$area][$object]['base_module'] = $combined_modules;
                            break;
                        }
                    }
                }
            }
        }
    }
    
    // loop through each level of the array to build the rest of the css output
    foreach($css_properties as $area => $objects) {
        if (($area != 'ad_region') && ($area != 'menu_region')) {
            /* build the area selector */
            $area_selector = '';
            
            // add the area to the are selector
            $area_selector .= '#' . $area;
            
            // if the object is an array, then loop through the objects in order to continue preparing the selector and outputting the css properties
            if (is_array($objects) == TRUE) {
                foreach($objects as $object => $modules) {
                    $object_selector = '';
                    
                    // if this is not the base object, then add the object to the object selector
                    if ($object != 'base_object') {
                        $object_selector .= ' .' . $object;
                    }
                    
                    // loop through each module to output it's properties
                    foreach($modules as $module => $properties) {
                        // prepare and set the module selector
                        $selector = prepare_module_selector($module, $area_selector . $object_selector);
                        
                        // add the selector to the output and open a code block for the css properties
                        $output .= $selector . "\r\n" . '{' . "\r\n";
                        
                        // call the function responsible for preparing and outputting the properties from the database
                        $output .= output_css_properties($properties);
                        
                        // close the code block
                        $output .= '}' . "\r\n";
                    }
                }
            }
        }
    }
    
    // if there is ad region properties, then output them
    if (isset($css_properties['ad_region']) == TRUE) {
        foreach($css_properties['ad_region'] as $object => $modules) {
            // get the properties for just the ad region base module and save them in an array
            $ad_region_properties = $css_properties['ad_region'][$object]['base_module'];
            
            // if there is a width property, then set it and remove it from the array
            if ($ad_region_properties['width'] != '') {
                $output_ad_region_width = $ad_region_properties['width'];
                $output_ad_width = $ad_region_properties['width'];
                
                unset($ad_region_properties['width']);
            
            // else set the defaults
            } else {
                $output_ad_region_width = 200 . 'px';
                $output_ad_width = 199 . 'px';
            }
            
            // if there is a height property, then set it and remove it from the array
            if ($ad_region_properties['height'] != '') {
                $output_ad_region_height = $ad_region_properties['height'];
                
                unset($ad_region_properties['height']);
            
            // else set the defaults
            } else {
                $output_ad_region_height = 200 . 'px';
            }
            
            // get the rest of the ad region properties
            $output_region_ad_properties = output_css_properties($ad_region_properties);
            
            // get the menu properties properties and put them into an array
            $menu_properties = $css_properties['ad_region'][$object]['menu'];
            
            $output_menu_position = '';
            
            // if there is a position value, then prepare the left and right values based on what position was selected
            if ($menu_properties['position'] != '') {
                switch($menu_properties['position']) {
                    case 'top_left':
                        $top = '0em';
                        
                        // if there is padding for the ad, then use the padding for the position of the menu
                        if ($ad_region_properties['padding_top'] != '') {
                            $top = $ad_region_properties['padding_top'];
                        }
                        
                        $left = '0em';
                        
                        // if there is padding for the ad, then use the padding for the position of the menu
                        if ($ad_region_properties['padding_left'] != '') {
                            $left = $ad_region_properties['padding_left'];
                        }
                        
                        $output_menu_position = 
                            'top: ' . $top . ';' . "\r\n" . 
                            'left: ' . $left . ';' . "\r\n";
                        break;
                        
                    case 'top_right':
                        $top = '0em';
                        
                        // if there is padding for the ad, then use the padding for the position of the menu
                        if ($ad_region_properties['padding_top'] != '') {
                            $top = $ad_region_properties['padding_top'];
                        }
                        
                        $right = '0em';
                        
                        // if there is padding for the ad, then use the padding for the position of the menu
                        if ($ad_region_properties['padding_right'] != '') {
                            $right = $ad_region_properties['padding_right'];
                        }
                        
                        $output_menu_position = 
                            'top: ' . $top . ';' . "\r\n" . 
                            'right: ' . $right . ';' . "\r\n";
                        break;
                        
                    case 'bottom_right':
                        $bottom = '0em';
                        
                        // if there is padding for the ad, then use the padding for the position of the menu
                        if ($ad_region_properties['padding_bottom'] != '') {
                            $bottom = $ad_region_properties['padding_bottom'];
                        }
                        
                        $right = '0em';
                        
                        // if there is padding for the ad, then use the padding for the position of the menu
                        if ($ad_region_properties['padding_right'] != '') {
                            $right = $ad_region_properties['padding_right'];
                        }
                        
                        $output_menu_position = 
                            'bottom: ' . $bottom . ';' . "\r\n" . 
                            'right: ' . $right . ';' . "\r\n";
                        break;
                        
                    case 'bottom_left':
                        $bottom = '0em';
                        
                        // if there is padding for the ad, then use the padding for the position of the menu
                        if ($ad_region_properties['padding_bottom'] != '') {
                            $bottom = $ad_region_properties['padding_bottom'];
                        }
                        
                        $left = '0em';
                        
                        // if there is padding for the ad, then use the padding for the position of the menu
                        if ($ad_region_properties['padding_left'] != '') {
                            $left = $ad_region_properties['padding_left'];
                        }
                        
                        $output_menu_position = 
                            'bottom: ' . $bottom . ';' . "\r\n" . 
                            'left: ' . $left . ';' . "\r\n";
                        break;
                }
                
                // remove the position from the menu properties array so that it isn't outputted again below
                unset($menu_properties['position']);
            }
            
            // get the rest of the menu properties for output
            $output_menu_properties = output_css_properties($menu_properties);
            
            // get the menu item properties properties and put them into an array
            $menu_item_properties = $css_properties['ad_region'][$object]['menu_item'];
            
            $output_menu_item_list_properties = '';
            
            // if there is a margin property in for the menu item, then add the margin properties to the menu item list element, and remove them from the ad region menu properties array
            if (
                ($menu_item_properties['margin_top'] != '')
                || ($menu_item_properties['margin_right'] != '')
                || ($menu_item_properties['margin_bottom'] != '')
                || ($menu_item_properties['margin_left'] != '')
            ) {
                // prepare the properties for output
                $output_menu_item_list_properties = output_css_properties(array('margin_top' => $menu_item_properties['margin_top'], 'margin_right' => $menu_item_properties['margin_right'], 'margin_bottom' => $menu_item_properties['margin_bottom'], 'margin_left' => $menu_item_properties['margin_left']));
                
                // remove the margins from the array
                unset($menu_item_properties['margin_top']);
                unset($menu_item_properties['margin_right']);
                unset($menu_item_properties['margin_bottom']);
                unset($menu_item_properties['margin_left']);
            
            // else default the margin to 0em
            } else {
                $output_menu_item_list_properties = 'margin: 0em;';
            }
            
            // if there is properties, then output them
            if (isset($menu_item_properties) == TRUE) {
                $output_menu_item_link_properties = output_css_properties($menu_item_properties);
            }
            
            // if there is properties, then output them
            if (isset($css_properties['ad_region'][$object]['menu_item_hover']) == TRUE) {
                $output_menu_item_link_hover_properties = output_css_properties($css_properties['ad_region'][$object]['menu_item_hover']);
            }

            // Get the previous & next button properties and put them into an array.
            $previous_and_next_buttons_properties = $css_properties['ad_region'][$object]['previous_and_next_buttons'];

            $output_previous_and_next_buttons_general = '';
            $output_previous_and_next_buttons_horizontal_offset = '';

            // If previous & next buttons are enabled, then show the buttons and set their position.
            if ($previous_and_next_buttons_properties['previous_and_next_buttons_toggle'] == 1) {
                // If the vertical offset is not blank, then set it.
                if ($previous_and_next_buttons_properties['previous_and_next_buttons_vertical_offset'] != '') {
                    $output_previous_and_next_buttons_general =
                        '#software_ad_region_' . $object . '.software_ad_region_dynamic .previous,' . "\n" .
                        '#software_ad_region_' . $object . '.software_ad_region_dynamic .next' . "\n" .
                        '{' . "\n" .
                            'top: ' . $previous_and_next_buttons_properties['previous_and_next_buttons_vertical_offset'] . 'px;' . "\n" .
                        '}' . "\n";
                }

                // If the horizontal offset is not blank, then set it.
                if ($previous_and_next_buttons_properties['previous_and_next_buttons_horizontal_offset'] != '') {
                    $output_previous_and_next_buttons_horizontal_offset =
                        '#software_ad_region_' . $object . '.software_ad_region_dynamic .previous' . "\n" .
                        '{' . "\n" .
                            'left: ' . $previous_and_next_buttons_properties['previous_and_next_buttons_horizontal_offset'] . 'px;' . "\n" .
                        '}' . "\n" .
                        '#software_ad_region_' . $object . '.software_ad_region_dynamic .next' . "\n" .
                        '{' . "\n" .
                            'right: ' . $previous_and_next_buttons_properties['previous_and_next_buttons_horizontal_offset'] . 'px;' . "\n" .
                        '}' . "\n";
                }

            // Otherwise the previous & next buttons are disabled, so hide the buttons.
            } else {
                $output_previous_and_next_buttons_general =
                    '#software_ad_region_' . $object . '.software_ad_region_dynamic .previous,' . "\n" .
                    '#software_ad_region_' . $object . '.software_ad_region_dynamic .next' . "\n" .
                    '{' . "\n" .
                        'display: none;' . "\n" .
                    '}' . "\n";
            }
            
            // output code for the ad region
            $output .= 
                '#software_ad_region_' . $object . '.software_ad_region_dynamic' . "\r\n" . 
                '{' . "\r\n" .
                    'position: relative;' . "\r\n" . 
                    'width: ' . $output_ad_region_width . ';' . "\r\n" .
                    'height: ' . $output_ad_region_height . ';' . "\r\n" .
                    $output_region_ad_properties . 
                '}' . "\r\n" .
                '#software_ad_region_' . $object . '.software_ad_region_dynamic .items_container' . "\r\n" . 
                '{' . "\r\n" .
                    'overflow: auto;' . "\r\n" . 
                    'overflow-x: hidden;' . "\r\n" . 
                    'position: relative;' . "\r\n" . 
                    'clear: left;' . "\r\n" . 
                    'width: ' . $output_ad_region_width . ';' . "\r\n" .
                    'height: ' . $output_ad_region_height . ';' . "\r\n" .
                '}' . "\r\n" .
                '#software_ad_region_' . $object . '.software_ad_region_dynamic .item' . "\r\n" . 
                '{' . "\r\n" .
                    'width: ' . $output_ad_width . ';' . "\r\n" .
                    'height: ' . $output_ad_region_height . ';' . "\r\n" .
                '}' . "\r\n" .
                '#software_ad_region_' . $object . '.software_ad_region_dynamic ul.menu' . "\r\n" . 
                '{' . "\r\n" .
                    'list-style: none;' . "\r\n" . 
                    'position: absolute;' . "\r\n" . 
                    'z-index: 1;' . "\r\n" . 
                    'margin: 0em;' . "\r\n" . 
                    'padding: 0em;' . "\r\n" . 
                    $output_menu_position . 
                    $output_menu_properties . 
                '}' . "\r\n" .
                '#software_ad_region_' . $object . '.software_ad_region_dynamic ul.menu li' . "\r\n" . 
                '{' . "\r\n" .
                    'list-style: none;' . "\r\n" . 
                    'display: inline;' . "\r\n" . 
                    $output_menu_item_list_properties . 
                '}' . "\r\n" .
                '#software_ad_region_' . $object . '.software_ad_region_dynamic ul.menu li a' . "\r\n" . 
                '{' . "\r\n" .
                    $output_menu_item_link_properties . 
                '}' . "\r\n" .
                '#software_ad_region_' . $object . '.software_ad_region_dynamic ul.menu li a:hover, ' . '#software_ad_region_' . $object . ' .software_ad_region_dynamic ul.menu li a:focus' . "\r\n" . 
                '{' . "\r\n" .
                    $output_menu_item_link_hover_properties . 
                '}' . "\r\n" . 
                '#software_ad_region_' . $object . '.software_ad_region_dynamic ul.menu li a.current' . "\r\n" . 
                '{' . "\r\n" .
                    $output_menu_item_link_hover_properties . 
                '}' . "\r\n" .
                $output_previous_and_next_buttons_general .
                $output_previous_and_next_buttons_horizontal_offset;
            
            // loop through the rest of the modules so that we can output their properties
            foreach($modules as $module => $properties) {
                if (
                    ($module != 'base_module')
                    && ($module != 'menu')
                    && ($module != 'menu_item')
                    && ($module != 'menu_item_hover')
                    && ($module != 'previous_and_next_buttons')
                ) {
                    // prepare and set the module selector
                    $selector = prepare_module_selector($module, '#software_ad_region_' . $object . '.software_ad_region_dynamic .item');
                    
                    // add the selector to the output and open a code block for the css properties
                    $output .= $selector . "\r\n" . '{' . "\r\n";
                    
                    // call the function responsible for preparing and outputting the properties from the database
                    $output .= output_css_properties($properties);
                    
                    // close the code block
                    $output .= '}' . "\r\n";
                }
            }
        }
    }
    
    // if there is menu region properties, then output them
    if (isset($css_properties['menu_region']) == TRUE) {
        foreach($css_properties['menu_region'] as $object => $modules) {
            // get the properties for just the menu region base module and save them in an array
            $menu_region_properties = $css_properties['menu_region'][$object]['base_module'];

            $menu_wrap = '';
                        
            // if the menu width is set, then override the menu's container width, and then remove it from the array
            if ($menu_region_properties['width'] != '') {
                $menu_wrap .=
                    '.menu_' . $object . "\r\n" .
                    '{' . "\r\n" .
                        'width: ' . $menu_region_properties['width'] . ' !important;' . "\r\n" .
                    '}' . "\r\n";
                unset($menu_region_properties['width']);
            }
            
            $menu_item_float_property = '';
            $menu_display_property = '';
            
            // if the menu orientation is horizonatal, then set the menu properties for a horizontal menu, and remove the value and remove it from the array
            if ($menu_region_properties['menu_orientation'] == 'horizontal') {
                $menu_item_float_property = 'float: none;' . "\r\n";
                //$menu_item_float_property = 'float: left;' . "\r\n";
                $menu_display_property = 'display: inline-block;' . "\r\n" . 
                                         // include hacks for IE7 since it doesn't support display block correctly
                                         '*display: inline;' . "\r\n" .
                                         '*float: left;' . "\r\n";
                unset($menu_region_properties['menu_orientation']);
            
            // else if the menu orientation is vertical, then set the menu properties for a vertical menu, and then remove it from the array
            } elseif ($menu_region_properties['menu_orientation'] == 'vertical') {
                $menu_item_float_property = 'float: none;' . "\r\n";
                unset($menu_region_properties['menu_orientation']);
            }

            // setup menu positioning within it's container
            $menu_items_position = '';

            // default menu items to the left to be backward compatible with v7.
            if ($menu_region_properties['position'] == '') {
                $menu_items_position = 'left';
            } else {
                $menu_items_position = $menu_region_properties['position'];
            }
            unset($menu_region_properties['position']);

            // get the css properties for the menu region
            $output_menu_region_properties = output_css_properties($menu_region_properties);
            
            $margin_properties = '';

            // need to reset these arrays each time through this loop or menu regions falsely "inherit" properties from other menu regions.
            // added in v8.7
            $menu_item_properties = '';
            $menu_item_hover_properties = '';
            $sub_menu_properties = '';
            $sub_menu_item_properties = '';
            $sub_menu_item_hover_properties = '';

            
            // if there is a margin on the menu item, then add it to the margin properties and remove it
            if (isset($css_properties['menu_region'][$object]['menu_item']['margin_left']) == TRUE) {
                $margin_properties .= 'margin-left: ' . $css_properties['menu_region'][$object]['menu_item']['margin_left'] . ';' . "\r\n";
                unset($css_properties['menu_region'][$object]['menu_item']['margin_left']);
            }
            
            if (isset($css_properties['menu_region'][$object]['menu_item']['margin_right']) == TRUE) {
                $margin_properties .= 'margin-right: ' . $css_properties['menu_region'][$object]['menu_item']['margin_right'] . ';' . "\r\n";
                unset($css_properties['menu_region'][$object]['menu_item']['margin_right']);
            }
            
            if (isset($css_properties['menu_region'][$object]['menu_item']['margin_top']) == TRUE) {
                $margin_properties .= 'margin-top: ' . $css_properties['menu_region'][$object]['menu_item']['margin_top'] . ';' . "\r\n";
                unset($css_properties['menu_region'][$object]['menu_item']['margin_top']);
            }
            
            if (isset($css_properties['menu_region'][$object]['menu_item']['margin_bottom']) == TRUE) {
                $margin_properties .= 'margin-bottom: ' . $css_properties['menu_region'][$object]['menu_item']['margin_bottom'] . ';' . "\r\n";
                unset($css_properties['menu_region'][$object]['menu_item']['margin_bottom']);
            }
            
            // if the margin properties are blank, then set them to 0
            if ($margin_properties == '') {
                $margin_properties = 'margin: 0em;' . "\r\n";
            }
            
            // if there is menu item properties, then get the css properties for the menu item
            if (isset($css_properties['menu_region'][$object]['menu_item']) == TRUE) {
                $menu_item_properties = output_css_properties($css_properties['menu_region'][$object]['menu_item']);
                unset($css_properties['menu_region'][$object]['menu_item']); // v8.7 unset values (remove) from array
            }
            
            // if there is menu item hover effect properties, then get the css properties for the menu item hover effect
            if (isset($css_properties['menu_region'][$object]['menu_item_hover']) == TRUE) {
                $menu_item_hover_properties = output_css_properties($css_properties['menu_region'][$object]['menu_item_hover']);
                unset($css_properties['menu_region'][$object]['menu_item_hover']);  // v8.7 unset values (remove) from array
            }

            // if there are properties for this module then output them
            if (isset($css_properties['menu_region'][$object]['submenu_background_borders_and_spacing']) == TRUE) {
                $sub_menu_properties = output_css_properties($css_properties['menu_region'][$object]['submenu_background_borders_and_spacing']);
                unset($css_properties['menu_region'][$object]['submenu_background_borders_and_spacing']); // v8.7 unset values (remove) from array
            }
            
            // if there are properties for this module then output them
            if (isset($css_properties['menu_region'][$object]['submenu_menu_item']) == TRUE) {
                $sub_menu_item_properties = output_css_properties($css_properties['menu_region'][$object]['submenu_menu_item']);
                unset($css_properties['menu_region'][$object]['submenu_menu_item']); // v8.7 unset values (remove) from array
            }
            
            // if there are properties for this module then output them
            if (isset($css_properties['menu_region'][$object]['submenu_menu_item_hover']) == TRUE) {
                $sub_menu_item_hover_properties = output_css_properties($css_properties['menu_region'][$object]['submenu_menu_item_hover']);
                unset($css_properties['menu_region'][$object]['submenu_menu_item_hover']); // v8.7 unset values (remove) from array
            }
            
            // get the effect type from the database
            $query = "SELECT effect FROM menus WHERE name = '" . escape($object) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $menu_effect_type = $row['effect'];
            
            $submenu_effect_specific_properties = '';
            $submenu_item_effect_specific_properties = '';
            
            // If the menu effect type is pop up, then output the properties needed for a pop-up menu.
            // We are no longer outputting z-index here because the JavaScript takes care of this now and places
            // the z-index on the parent li instead for IE 7 compatibility.
            if ($menu_effect_type == 'Pop-up') {
                $submenu_effect_specific_properties = 
                    'position: absolute;' . "\r\n" . 
                    'display: none;' . "\r\n" . 
                    'top: 50px;' . "\r\n" . 
                    'left: 0;' . "\r\n";
                
            // else if the menu effect type is accordion, then output the properties needed for an accordion menu
            } elseif ($menu_effect_type == 'Accordion') {
                $submenu_effect_specific_properties = 
                    'position: static;' . "\r\n" . 
                    'display: none;' . "\r\n" . 
                    'top: 0;' . "\r\n" . 
                    'left: 0;' . "\r\n";
                
                $submenu_item_effect_specific_properties = 
                    'margin-left: 1em;' . "\r\n";
            }

            // output code for the menu region
            $output .=
                $menu_wrap .
                '.menu_' . $object . "\r\n" .
                '{' . "\r\n" .
                    'margin: 0 auto;' . "\r\n" .
                    $output_menu_region_properties .
                '}' . "\r\n" . 
                '#software_menu_' . $object . '.software_menu,' . "\r\n" . 
                '#software_menu_' . $object . '.software_menu ul' . "\r\n" . 
                '{' . "\r\n" .
                    'padding: 0;' . "\r\n" . 
                    'margin: 0;' . "\r\n" . 
                    'list-style-type: none;' . "\r\n" . 
                    'text-align: ' . $menu_items_position . ';' . "\r\n" .
                    // Add IE hack because it only supports left position
                    '*text-align: left;' . "\r\n" .
                '}' . "\r\n" . 
                '#software_menu_' . $object . '.software_menu li' . "\r\n" . 
                '{' . "\r\n" .
                    'position: relative;' . "\r\n" . 
                    // the following padding remains fixed because there is no setting in Theme Designer as of v8.0
                    // which means the size of top level menu items will be forced to the same size as submenu items (not ideal for designs)
                    // set in advanced styling for default 8.0 theme
                    'padding: 0;' . "\r\n" . 
                    $menu_item_float_property . 
                    $margin_properties .
                    'text-align: left;' . "\r\n" . 
                    $menu_display_property . 
                '}' . "\r\n" .
                '#software_menu_' . $object . '.software_menu li a' . "\r\n" . 
                '{' . "\r\n" .
                    'display: block;' . "\r\n" . 
                    'outline: none;' . "\r\n" . 
                    $menu_item_properties .
                '}' . "\r\n" . 
                '#software_menu_' . $object . '.software_menu a.on,' . "\r\n" .
                '#software_menu_' . $object . '.software_menu a.current,' . "\r\n" .
                '#software_menu_' . $object . '.software_menu a:hover,' . "\r\n" .
                '#software_menu_' . $object . '.software_menu a:focus' . "\r\n" .
                '{' . "\r\n" .
                    $menu_item_hover_properties .
                '}' . "\r\n" .
                '#software_menu_' . $object . '.software_menu li ul' . "\r\n" . 
                '{' . "\r\n" .
                    'width: auto;' . "\r\n" . 
                    $submenu_effect_specific_properties .
                    $sub_menu_properties .
                '}' . "\r\n" . 
                '#software_menu_' . $object . '.software_menu li li' . "\r\n" . 
                '{' . "\r\n" .
                    'padding: 0em;' . "\r\n" . 
                    'margin: 0;' . "\r\n" . 
                    'width: auto;' . "\r\n" . 
                    $submenu_item_effect_specific_properties .
                '}' . "\r\n" . 
                '#software_menu_' . $object . '.software_menu li li a' . "\r\n" . 
                '{' . "\r\n" .
                    'outline: none;' . "\r\n" . 
                    $sub_menu_item_properties .
                '}' . "\r\n" . 
                '#software_menu_' . $object . '.software_menu li li a.on,' . "\r\n" .
                '#software_menu_' . $object . '.software_menu li li a.current,' . "\r\n" .
                '#software_menu_' . $object . '.software_menu li li a:hover,' . "\r\n" .
                '#software_menu_' . $object . '.software_menu li li a:focus' . "\r\n" .
                '{' . "\r\n" .
                    $sub_menu_item_hover_properties .
                '}' . "\r\n";
        }
    }
    
    // output the custom format selectors (in the order they will be displayed in editor's Custom Format pick list).
    $output .= 
        '/* <custom_formats> */' . "\r\n" .
        '.background-primary{}' . "\r\n" . 
        '.background-secondary{}' . "\r\n" . 
        '.color-primary{}' . "\r\n" . 
        '.color-secondary{}' . "\r\n" .
        '.heading-primary{}' . "\r\n" .
        '.heading-secondary{}' . "\r\n" .
        '.image-primary{}' . "\r\n" .
        '.image-secondary{}' . "\r\n" .
        '.image-left-primary{}' . "\r\n" .
        '.image-left-secondary{}' . "\r\n" .
        '.image-right-primary{}' . "\r\n" .
        '.image-right-secondary{}' . "\r\n" .
        '.image-desktop-hide{}' . "\r\n" .
        '.image-mobile-hide{}' . "\r\n" .
        '.link-button-primary-large{}' . "\r\n" .
        '.link-button-primary-small{}' . "\r\n" .
        '.link-button-secondary-large{}' . "\r\n" .
        '.link-button-secondary-small{}' . "\r\n" .
        '.link-menu-item{}' . "\r\n" .
        '.link-content-more{}' . "\r\n" .
        '.link-desktop-hide{}' . "\r\n" .
        '.link-mobile-hide{}' . "\r\n" .
        '.list-accordion{}' . "\r\n" .
        '.list-accordion-expanded{}' . "\r\n" .
        '.list-tabs{}' . "\r\n" .
        '.paragraph-box-primary{}' . "\r\n" .
        '.paragraph-box-secondary{}' . "\r\n" .
        '.paragraph-box-example{}' . "\r\n" .
        '.paragraph-box-notice{}' . "\r\n" .
        '.paragraph-box-warning{}' . "\r\n" .
        '.paragraph-no-margin{}' . "\r\n" .
        '.paragraph-no-margin-top{}' . "\r\n" .
        '.paragraph-no-margin-bottom{}' . "\r\n" .
        '.paragraph-indent{}' . "\r\n" .
        '.paragraph-desktop-hide{}' . "\r\n" .
        '.paragraph-mobile-hide{}' . "\r\n" .
        '.table-primary{}' . "\r\n" .
        '.table-secondary{}' . "\r\n" .
        '.table-left{}' . "\r\n" .
        '.table-right{}' . "\r\n" . 
        '.table-center{}' . "\r\n" .
        '.table-desktop-hide{}' . "\r\n" .
        '.table-mobile-hide{}' . "\r\n" .
        '.table-row-header{}' . "\r\n" .
        '.table-row-body{}' . "\r\n" .
        '.table-row-footer{}' . "\r\n" .
        '.table-cell-header{}' . "\r\n" .
        '.table-cell-data{}' . "\r\n" .
        '.table-cell-mobile-fill{}' . "\r\n" .
        '.table-cell-mobile-wrap{}' . "\r\n" .
        '.table-cell-mobile-hide{}' . "\r\n" .
        '.table-cell-desktop-hide{}' . "\r\n" .
        '.text-box-primary{}' . "\r\n" .
        '.text-box-secondary{}' . "\r\n" .
        '.text-box-example{}' . "\r\n" .
        '.text-box-notice{}' . "\r\n" .
        '.text-box-warning{}' . "\r\n" .
        '.text-desktop-hide{}' . "\r\n" .
        '.text-mobile-hide{}' . "\r\n" .
        '.text-highlighter{}' . "\r\n" .
        '.text-fine-print{}' . "\r\n" .
        '.text-annotate{}' . "\r\n" .
        '.text-quote{}' . "\r\n" .
        '.video-primary{}' . "\r\n" .
        '.video-secondary{}' . "\r\n" .
        '.video-left-primary{}' . "\r\n" .
        '.video-left-secondary{}' . "\r\n" .
        '.video-right-primary{}' . "\r\n" .
        '.video-right-secondary{}' . "\r\n" .
        '.video-desktop-hide{}' . "\r\n" .
        '.video-mobile-hide{}' . "\r\n" .
        '/* </custom_formats> */' . "\r\n";
    
    // output the video objects (center) primary properties
    $output .= 
        '.video-primary object,' . "\r\n" .
        '.video-primary iframe,' . "\r\n" .
        '.video-primary video' . "\r\n" .
        '{' . "\r\n" .
            'display: block;' . "\r\n" . // for objects not floated
        '}' . "\r\n" .
        'img.image-primary' . "\r\n" .
        '{' . "\r\n" .
            'margin-right: auto;' . "\r\n" .
            'margin-left: auto;' . "\r\n" .
        '}' . "\r\n";
        
    // output the image/video objects left primary properties
    $output .= 
        'img.image-left-primary,' . "\r\n" . 
        '.video-left-primary object,' . "\r\n" .
        '.video-left-primary iframe,' . "\r\n" .
        '.video-left-primary video' . "\r\n" .
        '{' . "\r\n" .
            'float: left;' . "\r\n" . 
            'margin-left: 0em;' . "\r\n" .
            'margin-top: 0em;' . "\r\n" .
            'margin-right: 1em;' . "\r\n" .
        '}' . "\r\n";
        
    // output the image/objects right primary properties
    $output .= 
        'img.image-right-primary,' . "\r\n" . 
        '.video-right-primary object,' . "\r\n" .
        '.video-right-primary iframe,' . "\r\n" .
        '.video-right-primary video' . "\r\n" .
        '{' . "\r\n" .
            'float: right;' . "\r\n" . 
            'margin-right: 0em;' . "\r\n" .
            'margin-top: 0em;' . "\r\n" .
            'margin-left: 1em;' . "\r\n" .
        '}' . "\r\n";
    
    // get the properties for the image primary custom formats
    $output_image_primary_properties = output_css_properties($site_wide_properties['base_object']['image_primary']);
    
    // output the image/video objects primary properties
    $output .= 
        'img.image-primary,' . "\r\n" .
        'img.image-left-primary,' . "\r\n" .
        'img.image-right-primary,' . "\r\n" .
        '.video-primary object,' . "\r\n" .
        '.video-primary iframe,' . "\r\n" .
        '.video-primary video,' . "\r\n" .
        '.video-left-primary object,' . "\r\n" .
        '.video-left-primary iframe,' . "\r\n" .
        '.video-left-primary video,' . "\r\n" .
        '.video-right-primary object,' . "\r\n" .
        '.video-right-primary iframe,' . "\r\n" .
        '.video-right-primary video' . "\r\n" .
        '{' . "\r\n" .
            $output_image_primary_properties . 
        '}' . "\r\n";
    
    // output the image/video objects (center) secondary properties
    $output .= 
        '.video-secondary object,' . "\r\n" .
        '.video-secondary iframe,' . "\r\n" .
        '.video-secondary video' . "\r\n" .
        '{' . "\r\n" .
            'display: block;' . "\r\n" . // for objects not floated
        '}' . "\r\n" .
        'img.image-secondary' . "\r\n" .
        '{' . "\r\n" .
            'margin-right: auto;' . "\r\n" .
            'margin-left: auto;' . "\r\n" .
        '}' . "\r\n";
        
    // output the image/video objects left secondary properties
    $output .= 
        'img.image-left-secondary,' . "\r\n" .
        '.video-left-secondary object,' . "\r\n" .
        '.video-left-secondary iframe,' . "\r\n" .
        '.video-left-secondary video' . "\r\n" .
        '{' . "\r\n" .
            'float: left;' . "\r\n" . 
            'margin-top: 0em;' . "\r\n" .
            'margin-left: 0em;' . "\r\n" .
            'margin-right: 1em;' . "\r\n" .
        '}' . "\r\n";
    
    // output the image/video objects right secondary properties
    $output .= 
        'img.image-right-secondary,' . "\r\n" .
        '.video-right-secondary object,' . "\r\n" .
        '.video-right-secondary iframe,' . "\r\n" .
        '.video-right-secondary video' . "\r\n" .
        '{' . "\r\n" .
            'float: right;' . "\r\n" . 
            'margin-top: 0em;' . "\r\n" .
            'margin-right: 0em;' . "\r\n" .
            'margin-left: 1em;' . "\r\n" .
        '}' . "\r\n";
    
    // get the properties for the image secondary custom formats
    $output_image_secondary_properties = output_css_properties($site_wide_properties['base_object']['image_secondary']);
    
    // output the image/video objects secondary properties
    $output .= 
        'img.image-secondary,' . "\r\n" .
        'img.image-left-secondary,' . "\r\n" .
        'img.image-right-secondary,' . "\r\n" .
        '.video-secondary object,' . "\r\n" .
        '.video-secondary iframe,' . "\r\n" .
        '.video-secondary video,' . "\r\n" .
        '.video-left-secondary object,' . "\r\n" .
        '.video-left-secondary iframe,' . "\r\n" .
        '.video-left-secondary video,' . "\r\n" .
        '.video-right-secondary object,' . "\r\n" .
        '.video-right-secondary iframe,' . "\r\n" .
        '.video-right-secondary video' . "\r\n" .
        '{' . "\r\n" .
            $output_image_secondary_properties . 
        '}' . "\r\n";
    
    // output the primary button properties
    $output .= 
        '.software_input_submit,' . "\r\n" . 
        'a.link-button-primary-large,' . "\r\n" . 
        'a.link-button-primary-large:link,' . "\r\n" . 
        'a.link-button-primary-large:visited,' . "\r\n" . 
        'a.link-button-primary-large:active, ' . "\r\n" . 
        'a.link-button-primary-small,' . "\r\n" . 
        'a.link-button-primary-small:link,' . "\r\n" . 
        'a.link-button-primary-small:visited,' . "\r\n" . 
        'a.link-button-primary-small:active,' . "\r\n" . 
        '.software_input_submit_primary,' . "\r\n" . 
        'a.software_input_submit_primary:link,' . "\r\n" . 
        'a.software_input_submit_primary:visited,' . "\r\n" . 
        'a.software_input_submit_primary:active,' . "\r\n" . 
        '.software_input_submit_small_primary,' . "\r\n" . 
        'a.software_input_submit_small_primary:link,' . "\r\n" . 
        'a.software_input_submit_small_primary:visited,' . "\r\n" . 
        'a.software_input_submit_small_primary:active,' . "\r\n" . 
        '.software_button_primary,' . "\r\n" . 
        'a.software_button_primary:link,' . "\r\n" . 
        'a.software_button_primary:visited,' . "\r\n" . 
        'a.software_button_primary:active,' . "\r\n" . 
        '.software_button_small_primary,' . "\r\n" . 
        'a.software_button_small_primary:link,' . "\r\n" . 
        'a.software_button_small_primary:visited,' . "\r\n" . 
        'a.software_button_small_primary:active,' . "\r\n" . 
        '.more_detail a' . "\r\n" . 
        '{' . "\r\n" .
            'padding: .5em .75em !important;' . "\r\n" .              //new modern button default styling for v8.5
            'text-decoration: none !important;' . "\r\n" .      //new modern button default styling for v8.5
            'display: inline-block;' . "\r\n" .
            'line-height: normal !important;' . "\r\n" .
            'cursor: pointer !important;' . "\r\n" .
            'text-align: center !important;' . "\r\n" .         //needed to center button text on mobile browsers
            'vertical-align: middle !important;' . "\r\n" .     //needed to center button text on mobile browsers
            'border: 1px solid #' . $primary_color . ' !important;' . "\r\n" . //new modern button default styling for v8.5

            output_css_properties($site_wide_properties['base_object']['primary_buttons'], 'primary_buttons');
            
            // if there was no background set then output default gradient background based on the primary color
            if ($site_wide_properties['base_object']['primary_buttons']['background_type'] == '') {
                $tint_color = color_tint($primary_color, 30);
                $IE_tint_color = color_tint($primary_color, 80);

                // If rounded corners are enabled, then disable filter gradient for IE 8+,
                // because IE 9 shows the gradient outside of the curved corners.
                // Technically, we really only want to disable the gradient for IE 9,
                // because the gradient works fine in IE 8 (because it does not support rounded corners),
                // however we don't have a way of targeting just IE 9 so we have to disable it for IE 8 also.
                // We will still output the old "filter" property further below so that the gradient still works in IE 7.
                // IE 7 does not process -ms-filter properties, so this will not disable the filter gradient for it.
                // We have also added a -ms-linear-gradient property further below which should allow IE 10 in the future
                // to support both rounded corners and gradients.
                if ($site_wide_properties['base_object']['primary_buttons']['rounded_corners_toggle'] == 1) {
                    $ie_8_and_9_gradient = '-ms-filter: "none" !important;' . "\r\n";

                // else rounded corners are disabled, so we can enable filter gradient for IE 8+
                } else {
                    $ie_8_and_9_gradient = '-ms-filter: "progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr=\'#' . $IE_tint_color . '\', endColorstr=\'#' . $primary_color . '\')" !important;' . "\r\n";
                }

                // output a gradient background for each browser
                $output .=
                'background-color: #' . $primary_color . ' !important;' . "\r\n" .
                'background: linear-gradient(bottom,#' . $primary_color .' 50%,#' . $tint_color . ' 100%) !important;' . "\r\n" .
                'background: -o-linear-gradient(bottom,#' . $primary_color . ' 50%,#' . $tint_color . ' 100%) !important;' . "\r\n" .
                'background: -moz-linear-gradient(bottom,#' . $primary_color . ' 50%,#' . $tint_color . ' 100%) !important;' . "\r\n" .
                'background: -webkit-linear-gradient(bottom,#' . $primary_color . ' 50%,#' . $tint_color . ' 100%) !important;' . "\r\n" . 
                'background: -ms-linear-gradient(bottom,#' . $primary_color . ' 50%,#' . $tint_color . ' 100%) !important;' . "\r\n" . // IE 10
                'background: -webkit-gradient(linear,left bottom,left top,color-stop(0.50,#' . $primary_color . '),color-stop(1.0,#' . $tint_color . ')) !important;' . "\r\n" .
                'filter:  progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr=\'#' . $IE_tint_color . '\', endColorstr=\'#' . $primary_color . '\') !important;' . "\r\n" . // IE6 & IE7 
                $ie_8_and_9_gradient;
            }
    $output .= '}' . "\r\n";
    
    // set the font size for the small buttons
    $output .= 
        'a.link-button-primary-small,' . "\r\n" . 
        'a.link-button-primary-small:link,' . "\r\n" . 
        'a.link-button-primary-small:visited,' . "\r\n" . 
        'a.link-button-primary-small:active,' . "\r\n" . 
        '.software_input_submit_small_primary,' . "\r\n" . 
        'a.software_input_submit_small_primary:link,' . "\r\n" . 
        'a.software_input_submit_small_primary:visited,' . "\r\n" . 
        'a.software_input_submit_small_primary:active,' . "\r\n" . 
        '.software_button_small_primary,' . "\r\n" . 
        'a.software_button_small_primary:link,' . "\r\n" . 
        'a.software_button_small_primary:visited,' . "\r\n" . 
        'a.software_button_small_primary:active' . "\r\n" . 
        '{' . "\r\n" .
            'font-size: 75% !important;' . "\r\n" .
        '}' . "\r\n";
    
        // get any primary button hover properties
        $output_button_primary_hover_properties = output_css_properties($site_wide_properties['base_object']['primary_buttons_hover'], 'primary_buttons_hover');
        
        // output the primary button hover properties
        $output .=
        '.software_input_submit:hover,' . "\r\n" . 
        '.software_input_submit:focus,' . "\r\n" . 
        'a.link-button-primary-large:hover,' . "\r\n" .  
        'a.link-button-primary-large:focus,' . "\r\n" . 
        'a.link-button-primary-small:hover,' . "\r\n" . 
        'a.link-button-primary-small:focus,' . "\r\n" . 
        '.software_input_submit_primary:hover,' . "\r\n" . 
        '.software_input_submit_primary:focus,' . "\r\n" . 
        'a.software_input_submit_primary:hover,' . "\r\n" .  
        'a.software_input_submit_primary:focus,' . "\r\n" . 
        '.software_input_submit_small_primary:hover,' . "\r\n" . 
        '.software_input_submit_small_primary:focus,' . "\r\n" . 
        '.software_button_primary:hover,' . "\r\n" . 
        '.software_button_primary:focus,' . "\r\n" . 
        'a.software_button_primary:hover,' . "\r\n" . 
        'a.software_button_primary:focus,' . "\r\n" . 
        '.software_button_small_primary:focus,' . "\r\n" . 
        '.software_button_small_primary:focus,' . "\r\n" . 
        'a.software_button_small_primary:hover,' . "\r\n" . 
        'a.software_button_small_primary:focus,' . "\r\n" . 
        '.more_detail a:hover,' . "\r\n" . 
        '.more_detail a:focus' . "\r\n" . 
            '{' . "\r\n" .
                $output_button_primary_hover_properties;

        // if there was no background set then output default gradient background based on the primary color
        if ($site_wide_properties['base_object']['primary_buttons_hover']['background_type'] == '') {
            $tint_color = color_tint($primary_color, 90);
            $hover_tint_color = color_tint($tint_color, 30);
            $hover_IE_tint_color = color_tint($tint_color, 80);

            if ($site_wide_properties['base_object']['primary_buttons_hover']['rounded_corners_toggle'] == 1) {
                $ie_8_and_9_gradient = '-ms-filter: "none" !important;' . "\r\n";

            // else rounded corners are disabled, so we can enable filter gradient for IE 8+
            } else {
                $ie_8_and_9_gradient = '-ms-filter: "progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr=\'#' . $hover_IE_tint_color . '\', endColorstr=\'#' . $tint_color . '\')" !important;' . "\r\n";
            }

            // output a gradient background for each browser
            $output .=
            'background-color: #' . $tint_color . ' !important;' . "\r\n" .
            'background: linear-gradient(bottom,#' . $tint_color .' 50%,#' . $hover_tint_color . ' 100%) !important;' . "\r\n" .
            'background: -o-linear-gradient(bottom,#' . $tint_color . ' 50%,#' . $hover_tint_color . ' 100%) !important;' . "\r\n" .
            'background: -moz-linear-gradient(bottom,#' . $tint_color . ' 50%,#' . $hover_tint_color . ' 100%) !important;' . "\r\n" .
            'background: -webkit-linear-gradient(bottom,#' . $tint_color . ' 50%,#' . $hover_tint_color . ' 100%) !important;' . "\r\n" . 
            'background: -ms-linear-gradient(bottom,#' . $tint_color . ' 50%,#' . $hover_tint_color . ' 100%) !important;' . "\r\n" . // IE 10
            'background: -webkit-gradient(linear,left bottom,left top,color-stop(0.50,#' . $tint_color  . '),color-stop(1.0,#' . $hover_tint_color . ')) !important;' . "\r\n" .
            'filter:  progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr=\'#' . $hover_IE_tint_color . '\', endColorstr=\'#' . $tint_color . '\') !important;' . "\r\n" . // IE6 & IE7 
            $ie_8_and_9_gradient;
        }
    $output .= '}' . "\r\n";
    
    // output the secondary button properties
    $output .= 
        'a.link-button-secondary-large,' . "\r\n" . 
        'a.link-button-secondary-large:link,' . "\r\n" . 
        'a.link-button-secondary-large:visited,' . "\r\n" . 
        'a.link-button-secondary-large:active, ' . "\r\n" . 
        'a.link-button-secondary-small,' . "\r\n" . 
        'a.link-button-secondary-small:link,' . "\r\n" . 
        'a.link-button-secondary-small:visited,' . "\r\n" . 
        'a.link-button-secondary-small:active,' . "\r\n" . 
        '.software_input_submit_secondary,' . "\r\n" . 
        'a.software_input_submit_secondary:link,' . "\r\n" . 
        'a.software_input_submit_secondary:visited,' . "\r\n" . 
        'a.software_input_submit_secondary:active,' . "\r\n" . 
        '.software_input_submit_small_secondary,' . "\r\n" . 
        'a.software_input_submit_small_secondary:link,' . "\r\n" . 
        'a.software_input_submit_small_secondary:visited,' . "\r\n" . 
        'a.software_input_submit_small_secondary:active,' . "\r\n" . 
        '.software_button_secondary,' . "\r\n" . 
        'a.software_button_secondary:link,' . "\r\n" . 
        'a.software_button_secondary:visited,' . "\r\n" . 
        'a.software_button_secondary:active,' . "\r\n" . 
        '.software_button_small_secondary,' . "\r\n" . 
        'a.software_button_small_secondary:link,' . "\r\n" . 
        'a.software_button_small_secondary:visited,' . "\r\n" . 
        'a.software_button_small_secondary:active,' . "\r\n" . 
        '.software_button_tiny_secondary,' . "\r\n" . 
        'a.software_button_tiny_secondary:link,' . "\r\n" . 
        'a.software_button_tiny_secondary:visited,' . "\r\n" . 
        'a.software_button_tiny_secondary:active,' . "\r\n" . 
        '.software_input_submit_tiny_secondary,' . "\r\n" .
        'a.software_input_submit_tiny_secondary:link,' . "\r\n" .
        'a.software_input_submit_tiny_secondary:visited,' . "\r\n" .
        'a.software_input_submit_tiny_secondary:active,' . "\r\n" .
        '.software_menu_sequence a' . "\r\n" . 
        '{' . "\r\n" .
            'padding: .5em .75em !important;' . "\r\n" .                      //new modern button default styling for v8.5
            'text-decoration: none !important;' . "\r\n" .              //new modern button default styling for v8.5
            'display: inline-block;' . "\r\n" .
            'line-height: normal !important;' . "\r\n" .
            'cursor: pointer !important;' . "\r\n" .
            'text-align: center !important;' . "\r\n" .                //needed to center button text on mobile browsers
            'vertical-align: middle !important;' . "\r\n" .            //needed to center button text on mobile browsers
            'border: 1px solid #' . $secondary_color . ' !important;' . "\r\n" .  //new modern button default styling for v8.5

            output_css_properties($site_wide_properties['base_object']['secondary_buttons'], 'secondary_buttons');
            
            // if there was no background set then output default gradient background based on the secondary color
            if ($site_wide_properties['base_object']['secondary_buttons']['background_type'] == '') {    
                $tint_color = color_tint($secondary_color, 30);
                $IE_tint_color = color_tint($secondary_color, 80);

                // If rounded corners are enabled, then disable filter gradient for IE 8+,
                // because IE 9 shows the gradient outside of the curved corners.
                // Technically, we really only want to disable the gradient for IE 9,
                // because the gradient works fine in IE 8 (because it does not support rounded corners),
                // however we don't have a way of targeting just IE 9 so we have to disable it for IE 8 also.
                // We will still output the old "filter" property further below so that the gradient still works in IE 7.
                // IE 7 does not process -ms-filter properties, so this will not disable the filter gradient for it.
                // We have also added a -ms-linear-gradient property further below which should allow IE 10 in the future
                // to support both rounded corners and gradients.
                if ($site_wide_properties['base_object']['secondary_buttons']['rounded_corners_toggle'] == 1) {
                    $ie_8_and_9_gradient = '-ms-filter: "none" !important;' . "\r\n";

                // else rounded corners are disabled, so we can enable filter gradient for IE 8+
                } else {
                    $ie_8_and_9_gradient = '-ms-filter: "progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr=\'#' . $IE_tint_color . '\', endColorstr=\'#' . $secondary_color . '\')" !important;' . "\r\n";
                }

                // output a gradient background for each browser
                $output .=
                'background-color: #' . $secondary_color . ' !important;' . "\r\n" .
                'background: linear-gradient(bottom,#' . $secondary_color .' 50%,#' . $tint_color . ' 100%) !important;' . "\r\n" .
                'background: -o-linear-gradient(bottom,#' . $secondary_color . ' 50%,#' . $tint_color . ' 100%) !important;' . "\r\n" .
                'background: -moz-linear-gradient(bottom,#' . $secondary_color . ' 50%,#' . $tint_color . ' 100%) !important;' . "\r\n" .
                'background: -webkit-linear-gradient(bottom,#' . $secondary_color . ' 50%,#' . $tint_color . ' 100%) !important;' . "\r\n" . 
                'background: -ms-linear-gradient(bottom,#' . $secondary_color . ' 50%,#' . $tint_color . ' 100%) !important;' . "\r\n" . // IE 10
                'background: -webkit-gradient(linear,left bottom,left top,color-stop(0.50,#' . $secondary_color . '),color-stop(1.0,#' . $tint_color . ')) !important;' . "\r\n" .
                'filter:  progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr=\'#' . $IE_tint_color . '\', endColorstr=\'#' . $secondary_color . '\') !important;' . "\r\n" . // IE6 & IE7 
                $ie_8_and_9_gradient;
            }
    $output .= '}' . "\r\n";
    
    // output the button secondary properties
    $output .= 
        'a.link-button-secondary-small,' . "\r\n" . 
        'a.link-button-secondary-small:link,' . "\r\n" . 
        'a.link-button-secondary-small:visited,' . "\r\n" . 
        'a.link-button-secondary-small:active,' . "\r\n" . 
        '.software_input_submit_small_secondary,' . "\r\n" . 
        'a.software_input_submit_small_secondary:link,' . "\r\n" . 
        'a.software_input_submit_small_secondary:visited,' . "\r\n" . 
        'a.software_input_submit_small_secondary:active,' . "\r\n" . 
        '.software_button_small_secondary,' . "\r\n" . 
        'a.software_button_small_secondary:link,' . "\r\n" . 
        'a.software_button_small_secondary:visited,' . "\r\n" . 
        'a.software_button_small_secondary:active' . "\r\n" . 
        '{' . "\r\n" .
            'font-size: 75% !important;' . "\r\n" .
        '}' . "\r\n";
    
    // output tiny secondary button properties
    $output .= 
        '.software_button_tiny_secondary,' . "\r\n" . 
        'a.software_button_tiny_secondary:link,' . "\r\n" . 
        'a.software_button_tiny_secondary:visited,' . "\r\n" . 
        'a.software_button_tiny_secondary:active,' . "\r\n" .
        '.software_input_submit_tiny_secondary,' . "\r\n" . 
        'a.software_input_submit_tiny_secondary:link,' . "\r\n" . 
        'a.software_input_submit_tiny_secondary:visited,' . "\r\n" . 
        'a.software_input_submit_tiny_secondary:active' . "\r\n" .
        '{' . "\r\n" .
            'font-size: 75% !important;' . "\r\n" .
            'font-weight: normal !important;' . "\r\n" .
            'padding: 2px 6px !important;' . "\r\n" .
        '}' . "\r\n";

        // get any secondary button hover properties
        $output_button_secondary_hover_properties = output_css_properties($site_wide_properties['base_object']['secondary_buttons_hover'], 'secondary_buttons_hover');
        
        // output the hover secondary properties
        $output .= 
        'a.link-button-secondary-large:hover,' . "\r\n" .  
        'a.link-button-secondary-large:focus,' . "\r\n" . 
        'a.link-button-secondary-small:hover,' . "\r\n" . 
        'a.link-button-secondary-small:focus,' . "\r\n" . 
        '.software_input_submit_secondary:hover,' . "\r\n" . 
        '.software_input_submit_secondary:focus,' . "\r\n" . 
        'a.software_input_submit_secondary:hover,' . "\r\n" .  
        'a.software_input_submit_secondary:focus,' . "\r\n" . 
        '.software_input_submit_small_secondary:hover,' . "\r\n" . 
        '.software_input_submit_small_secondary:focus,' . "\r\n" . 
        '.software_button_secondary:hover,' . "\r\n" . 
        '.software_button_secondary:focus,' . "\r\n" . 
        'a.software_button_secondary:hover,' . "\r\n" . 
        'a.software_button_secondary:focus,' . "\r\n" . 
        '.software_button_small_secondary:focus,' . "\r\n" . 
        '.software_button_small_secondary:focus,' . "\r\n" . 
        'a.software_button_small_secondary:hover,' . "\r\n" . 
        'a.software_button_small_secondary:focus,' . "\r\n" . 
        'a.software_button_tiny_secondary:hover,' . "\r\n" . 
        'a.software_button_tiny_secondary:focus,' . "\r\n" .
        'a.software_input_submit_tiny_secondary:hover,' . "\r\n" . 
        'a.software_input_submit_tiny_secondary:focus' . "\r\n" .
        '{' . "\r\n" .
            $output_button_secondary_hover_properties;

        // if there was no background set then output default gradient background based on the secondary color
        if ($site_wide_properties['base_object']['secondary_buttons_hover']['background_type'] == '') {
            $tint_color = color_tint($secondary_color, 90);
            $hover_tint_color = color_tint($tint_color, 30);
            $hover_IE_tint_color = color_tint($tint_color, 80);

            if ($site_wide_properties['base_object']['secondary_buttons_hover']['rounded_corners_toggle'] == 1) {
                $ie_8_and_9_gradient = '-ms-filter: "none" !important;' . "\r\n";

            // else rounded corners are disabled, so we can enable filter gradient for IE 8+
            } else {
                $ie_8_and_9_gradient = '-ms-filter: "progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr=\'#' . $hover_IE_tint_color . '\', endColorstr=\'#' . $tint_color . '\')" !important;' . "\r\n";
            }

            // output a gradient background for each browser
            $output .=
            'background-color: #' . $tint_color . ' !important;' . "\r\n" .
            'background: linear-gradient(bottom,#' . $tint_color .' 50%,#' . $hover_tint_color . ' 100%) !important;' . "\r\n" .
            'background: -o-linear-gradient(bottom,#' . $tint_color . ' 50%,#' . $hover_tint_color . ' 100%) !important;' . "\r\n" .
            'background: -moz-linear-gradient(bottom,#' . $tint_color . ' 50%,#' . $hover_tint_color . ' 100%) !important;' . "\r\n" .
            'background: -webkit-linear-gradient(bottom,#' . $tint_color . ' 50%,#' . $hover_tint_color . ' 100%) !important;' . "\r\n" . 
            'background: -ms-linear-gradient(bottom,#' . $tint_color . ' 50%,#' . $hover_tint_color . ' 100%) !important;' . "\r\n" . // IE 10
            'background: -webkit-gradient(linear,left bottom,left top,color-stop(0.50,#' . $tint_color  . '),color-stop(1.0,#' . $hover_tint_color . ')) !important;' . "\r\n" .
            'filter:  progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr=\'#' . $hover_IE_tint_color . '\', endColorstr=\'#' . $tint_color . '\') !important;' . "\r\n" . // IE6 & IE7 
            $ie_8_and_9_gradient;
        }
    $output .= '}' . "\r\n";
    
    // output custom formats that do not have user options
    $output .=
        'h1.heading-primary, h2.heading-primary, h3.heading-primary, h4.heading-primary, h5.heading-primary, h6.heading-primary ' . "\r\n" . 
        '{' . "\r\n" .
            'border-bottom: 1px solid;' . "\r\n" .
        '}' . "\r\n" .
        'h1.heading-secondary, h2.heading-secondary, h3.heading-secondary, h4.heading-secondary, h5.heading-secondary, h6.heading-secondary ' . "\r\n" .
        '{' . "\r\n" .
            'border-bottom: 1px dotted;' . "\r\n" .
        '}' . "\r\n" .
        'td.text-annotate, p.text-annotate, span.text-annotate' . "\r\n" . 
        '{' . "\r\n" .
            'font-size: 8pt;' . "\r\n" . 
            'text-decoration: none;' . "\r\n" .
            'padding: 2px 5px;' . "\r\n" . 
            'border: 1px solid;' . "\r\n" . 
            'line-height: 1.4em;' . "\r\n" . 
        '}' . "\r\n" . 
        'td.text-fine-print, p.text-fine-print, span.text-fine-print' . "\r\n" . 
        '{' . "\r\n" .
            'font-size: 75%;' . "\r\n" . 
            'text-decoration: none;' . "\r\n" . 
            'line-height: 1.5em;' . "\r\n" . 
        '}' . "\r\n" . 
        'td.text-box-primary, p.text-box-primary, span.text-box-primary, p.paragraph-box-primary' . "\r\n" . 
        '{' . "\r\n" .
            'margin: .5em 0em;' . "\r\n" . 
            'padding: .5em;' . "\r\n" . 
            'border: 1px solid #' . $primary_color . ' !important;' . "\r\n" . 
            'line-height: 1.5em;' . "\r\n" . 
        '}' . "\r\n" .
        'td.text-box-secondary, p.text-box-secondary, span.text-box-secondary, p.paragraph-box-secondary' . "\r\n" . 
        '{' . "\r\n" .
            'margin: .5em 0em;' . "\r\n" . 
            'padding: .5em;' . "\r\n" . 
            'border: 1px solid #' . $secondary_color . ' !important;' . "\r\n" . 
            'line-height: 1.5em;' . "\r\n" . 
        '}' . "\r\n" . 
        'td.text-box-warning, p.text-box-warning, span.text-box-warning, p.paragraph-box-warning' . "\r\n" . 
        '{' . "\r\n" .
            'color: red;' . "\r\n" . 
            'line-height: 1.4em;' . "\r\n" . 
            'text-decoration: none;' . "\r\n" . 
            'padding: 10px;' . "\r\n" . 
            'border: 1px solid red !important;' . "\r\n" . 
        '}' . "\r\n" . 
        'td.paragraph-no-margin, p.paragraph-no-margin, span.paragraph-no-margin, p.paragraph-no-margin' . "\r\n" . 
        '{' . "\r\n" .
            'margin-top: 0px;' . "\r\n" . 
            'margin-bottom: 0px;' . "\r\n" . 
        '}' . "\r\n" .
        'td.paragraph-no-margin-top, p.paragraph-no-margin-top, span.paragraph-no-margin-top, p.paragraph-no-margin-top' . "\r\n" . 
        '{' . "\r\n" .
            'margin-top: 0px;' . "\r\n" .
        '}' . "\r\n" .
        'td.paragraph-no-margin-bottom, p.paragraph-no-margin-bottom, span.paragraph-no-margin-bottom, p.paragraph-no-margin-bottom' . "\r\n" . 
        '{' . "\r\n" .
            'margin-bottom: 0px;' . "\r\n" . 
        '}' . "\r\n" .
        '.text-box-notice, p.text-box-notice, span.text-box-notice, p.paragraph-box-notice' . "\r\n" . 
        '{' . "\r\n" .
            'line-height: 1.4em;' . "\r\n" . 
            'text-decoration: none;' . "\r\n" . 
            'padding: 10px;' . "\r\n" . 
            'border: 1px solid;' . "\r\n" . 
            'margin: .5em 0em;' . "\r\n" . 
        '}' . "\r\n" . 
        'td.text-box-example, p.text-box-example, span.text-box-example, p.paragraph-box-example' . "\r\n" . 
        '{' . "\r\n" .
            'font-family: courier;' . "\r\n" . 
            'line-height: 1.4em;' . "\r\n" . 
            'word-spacing: normal;' . "\r\n" . 
            'text-decoration: none;' . "\r\n" . 
            'border-top: 1px dashed #' . $primary_color . ' !important;' . "\r\n" . 
            'border-bottom: 1px dashed #' . $primary_color . ' !important;' . "\r\n" . 
            'margin: 10px 0px;' . "\r\n" . 
            'padding: .5em;' . "\r\n" .
        '}' . "\r\n" .
        'td.text-highlighter, p.text-highlighter, span.text-highlighter' . "\r\n" . 
        '{' . "\r\n" .
            'color: #000000 !important;' . "\r\n" . 
            'background-color: yellow !important;' . "\r\n" . 
            'text-decoration: none;' . "\r\n" . 
            'padding: 2px;' . "\r\n" . 
        '}' . "\r\n" . 
        'td.text-highlighter a, p.text-highlighter a, span.text-highlighter a' . "\r\n" .
        '{' . "\r\n" .
            'color: #000000 !important;' . "\r\n" .
        '}' . "\r\n" . 
        'td.text-quote, p.text-quote, span.text-quote' . "\r\n" . 
        '{' . "\r\n" .
            'font-size: 150%;' . "\r\n" .
            'line-height: 150%;' . "\r\n" . 
            'font-style: oblique;' . "\r\n" . 
            'margin: 0px;' . "\r\n" . 
            'padding: 0px;' . "\r\n" . 
            'border: none;' . "\r\n" .            
        '}' . "\r\n" . 
        'td.paragraph-indent, p.paragraph-indent' . "\r\n" . 
        '{' . "\r\n" .
            'text-indent: 5%;' . "\r\n" . 
        '}' . "\r\n" . 
        'li.link-menu-item, p.link-menu-item, a.link-menu-item' . "\r\n" . 
        '{' . "\r\n" .
            'display: block;' . "\r\n" . 
            'padding: 0.5em 1em;' . "\r\n" . 
            'margin: 0em 0em .5em 0em;' . "\r\n" . 
            'font-size: 100%;' . "\r\n" . 
            'font-weight: normal;' . "\r\n" . 
            'font-style: normal;' . "\r\n" .
            'text-decoration: none;' . "\r\n" .
            'color: #' . $primary_color . ' !important;' . "\r\n" .
            'background: #' . $secondary_color . ' !important;' . "\r\n" .
        '}' . "\r\n" .
        'a.link-menu-item:hover, a.link-menu-item:focus' . "\r\n" . 
        '{' . "\r\n" .
            'color: #' . $secondary_color . ' !important;' . "\r\n" . 
            'background: #' . $primary_color . ' !important;' . "\r\n" . 
        '}' . "\r\n" .
        'a.link-content-more,' . "\r\n" .
        'a.link-content-more:link,' . "\r\n" .
        'a.link-content-more:active,' . "\r\n" .
        'a.link-content-more:visited' . "\r\n" . 
        '{' . "\r\n" .
            'font-size: 75%;' . "\r\n" .
            'border: 1px solid #' . $primary_color . ' !important;' . "\r\n" .
            'text-decoration: none;' . "\r\n" .
            'padding: .5em;' . "\r\n" .  
        '}' . "\r\n" .
        'a.link-content-more:hover, a.link-content-more:focus' . "\r\n" . 
        '{' . "\r\n" .
            'color: #'. $primary_color . ' !important;' . "\r\n" .
        '}' . "\r\n" .
        'table.table-primary' . "\r\n" .
        '{' . "\r\n" .
            'border: 5px solid #' . $primary_color . ' !important;' . "\r\n" .
            'border-width: 5px;' . "\r\n" .
            'vertical-align: top;' . "\r\n" .
            'border-collapse: separate;' . "\r\n" .
        '}' . "\r\n" .
        'table.table-primary th' . "\r\n" .
        '{' . "\r\n" .
            'border-bottom: 5px solid #' . $primary_color . ' !important;' . "\r\n" .
            'border-width: 5px;' . "\r\n" .
            'vertical-align: top;' . "\r\n" .
        '}' . "\r\n" .    
        'table.table-secondary ' . "\r\n" .
        '{' . "\r\n" .
            'border: 1px solid #' . $secondary_color . ' !important;' . "\r\n" .
            'border-width: 1px;' . "\r\n" .
            'vertical-align: top;' . "\r\n" .
            'border-collapse: separate;' . "\r\n" .
        '}' . "\r\n" .
        'table.table-secondary th' . "\r\n" .
        '{' . "\r\n" .
            'border-bottom: 1px solid #' . $secondary_color . ' !important;' . "\r\n" .
            'border-width: 1px;' . "\r\n" .
            'vertical-align: top;' . "\r\n" .
        '}' . "\r\n" . 
        'table.table-left' . "\r\n" .
        '{' . "\r\n" .
            'float: left;' . "\r\n" .
            'width: auto !important;' . "\r\n" .
            'margin-right: 1em !important;' . "\r\n" .
            'margin-bottom: .2em !important;' . "\r\n" .
        '}' . "\r\n" .
        'table.table-right' . "\r\n" .
        '{' . "\r\n" .
            'float: right;' . "\r\n" .
            'width: auto !important;' . "\r\n" .
            'margin-left: 1em !important;' . "\r\n" .
            'margin-bottom: .2em !important;' . "\r\n" .
        '}' . "\r\n" .
        'table.table-center' . "\r\n" .
        '{' . "\r\n" .
            'width: auto !important;' . "\r\n" .
            'margin-right: auto !important;' . "\r\n" .
            'margin-left: auto !important;' . "\r\n" .
        '}' . "\r\n" .
        'thead.table-row-header' . "\r\n" .
        '{' . "\r\n" .
            'background: #' . $primary_color . ' !important;' . "\r\n" .
            'color: #' . $secondary_color . ' !important;' . "\r\n" .
        '}' . "\r\n" .
        'tbody.table-row-body' . "\r\n" .
        '{' . "\r\n" .
            // intentionally left blank
        '}' . "\r\n" .
        'tfoot.table-row-footer' . "\r\n" .
        '{' . "\r\n" .
            'background: #' . $primary_color . ' !important;' . "\r\n" .
            'color: #' . $secondary_color . ' !important;' . "\r\n" .
        '}' . "\r\n" .
        'th.table-cell-header' . "\r\n" .
        '{' . "\r\n" .
            'background: #' . $primary_color . ' !important;' . "\r\n" .
            'color: #' . $secondary_color . ' !important;' . "\r\n" .
        '}' . "\r\n" .   
        'td.table-cell-data' . "\r\n" .
        '{' . "\r\n" .
            // intentionally left blank
        '}' . "\r\n" .
        '.one_column_mobile td.table-cell-mobile-fill' . "\r\n" .
        '{' . "\r\n" .
            'float: left !important;' . "\r\n" .
            'width: 100% !important;' . "\r\n" .
            'white-space: normal !important;' . "\r\n" .
        '}' . "\r\n" .
        '.one_column_mobile td.table-cell-mobile-wrap' . "\r\n" .
        '{' . "\r\n" .
            'float: left !important;' . "\r\n" .
            'width: auto !important;' . "\r\n" .
        '}' . "\r\n" .
        // add mobile hide for only mobile page styles
        '.one_column_mobile table.table-mobile-hide,' . "\r\n" .
        '.one_column_mobile td.table-cell-mobile-hide,' . "\r\n" .
        '.one_column_mobile p.paragraph-mobile-hide,' . "\r\n" .
        '.one_column_mobile img.image-mobile-hide,' . "\r\n" .
        '.one_column_mobile .video-mobile-hide,' . "\r\n" .
        '.one_column_mobile a.link-mobile-hide,' . "\r\n" .
        '.one_column_mobile div.mobile-hide,' . "\r\n" .
        '.one_column_mobile span.text-mobile-hide' . "\r\n" .
        '{' . "\r\n" .
            'display: none;' . "\r\n" .
        '}' . "\r\n" .
        // add desktop hide for all but mobile pages styles
        '.one_column table.table-desktop-hide,' . "\r\n" .
        '.one_column td.table-cell-desktop-hide,' . "\r\n" .
        '.one_column p.paragraph-desktop-hide,' . "\r\n" .
        '.one_column img.image-desktop-hide,' . "\r\n" .
        '.one_column .video-desktop-hide,' . "\r\n" .
        '.one_column a.link-desktop-hide,' . "\r\n" .
        '.one_column div.desktop-hide,' . "\r\n" .
        '.one_column span.text-desktop-hide,' . "\r\n" .
        '.one_column_email table.table-desktop-hide,' . "\r\n" .
        '.one_column_email td.table-cell-desktop-hide,' . "\r\n" .
        '.one_column_email p.paragraph-desktop-hide,' . "\r\n" .
        '.one_column_email img.image-desktop-hide,' . "\r\n" .
        '.one_column_email .video-desktop-hide,' . "\r\n" .
        '.one_column_email a.link-desktop-hide,' . "\r\n" .
        '.one_column_email div.desktop-hide,' . "\r\n" .
        '.one_column_email span.text-desktop-hide,' . "\r\n" .
        '.two_column_sidebar_left table.table-desktop-hide,' . "\r\n" .
        '.two_column_sidebar_left td.table-cell-desktop-hide,' . "\r\n" .
        '.two_column_sidebar_left p.paragraph-desktop-hide,' . "\r\n" .
        '.two_column_sidebar_left img.image-desktop-hide,' . "\r\n" .
        '.two_column_sidebar_left .video-desktop-hide,' . "\r\n" .
        '.two_column_sidebar_left a.link-desktop-hide,' . "\r\n" .
        '.two_column_sidebar_left div.desktop-hide,' . "\r\n" .
        '.two_column_sidebar_left span.text-desktop-hide,' . "\r\n" .
        '.two_column_sidebar_right table.table-desktop-hide,' . "\r\n" .
        '.two_column_sidebar_right td.table-cell-desktop-hide,' . "\r\n" .
        '.two_column_sidebar_right p.paragraph-desktop-hide,' . "\r\n" .
        '.two_column_sidebar_right img.image-desktop-hide,' . "\r\n" .
        '.two_column_sidebar_right .video-desktop-hide,' . "\r\n" .
        '.two_column_sidebar_right a.link-desktop-hide,' . "\r\n" .
        '.two_column_sidebar_right div.desktop-hide,' . "\r\n" .
        '.two_column_sidebar_right span.text-desktop-hide,' . "\r\n" .
        '.three_column_sidebar_left table.table-desktop-hide,' . "\r\n" .
        '.three_column_sidebar_left td.table-cell-desktop-hide,' . "\r\n" .
        '.three_column_sidebar_left p.paragraph-desktop-hide,' . "\r\n" .
        '.three_column_sidebar_left img.image-desktop-hide,' . "\r\n" .
        '.three_column_sidebar_left .video-desktop-hide,' . "\r\n" .
        '.three_column_sidebar_left a.link-desktop-hide,' . "\r\n" .
        '.three_column_sidebar_left div.desktop-hide,' . "\r\n" .
        '.three_column_sidebar_left span.text-desktop-hide' . "\r\n" .
        '{' . "\r\n" .
            'display: none;' . "\r\n" .
        '}' . "\r\n" .
        // override all hides when user is in edit mode
        '.edit_mode table.table-mobile-hide,' . "\r\n" .
        '.edit_mode td.table-cell-mobile-hide,' . "\r\n" .
        '.edit_mode p.paragraph-mobile-hide,' . "\r\n" .
        '.edit_mode img.image-mobile-hide,' . "\r\n" .
        '.edit_mode .video-mobile-hide,' . "\r\n" .
        '.edit_mode a.link-mobile-hide,' . "\r\n" .
        '.edit_mode div.mobile-hide,' . "\r\n" .
        '.edit_mode span.text-mobile-hide,' . "\r\n" .
        '.edit_mode table.table-desktop-hide,' . "\r\n" .
        '.edit_mode td.table-cell-desktop-hide,' . "\r\n" .
        '.edit_mode p.paragraph-desktop-hide,' . "\r\n" .
        '.edit_mode img.image-desktop-hide,' . "\r\n" .
        '.edit_mode .video-desktop-hide,' . "\r\n" .
        '.edit_mode a.link-desktop-hide,' . "\r\n" .
        '.edit_mode div.desktop-hide,' . "\r\n" .
        '.edit_mode span.text-desktop-hide' . "\r\n" .
        '{' . "\r\n" .
            'display: block !important;' . "\r\n" .
        '}' . "\r\n" .
        '.background-primary' . "\r\n" .
        '{' . "\r\n" .
            'background-color: #' . $primary_color . ' !important;' . "\r\n" .
        '}' . "\r\n" .
        '.background-secondary' . "\r\n" .
        '{' . "\r\n" .
            'background-color: #' . $secondary_color . ' !important;' . "\r\n" .
        '}' . "\r\n" .
        '.color-primary' . "\r\n" .
        '{' . "\r\n" .
            'color: #' . $primary_color . ' !important;' . "\r\n" .
        '}' . "\r\n" .
        '.color-secondary' . "\r\n" .
        '{' . "\r\n" .
            'color: #' . $secondary_color . ' !important;' . "\r\n" .
        '}' . "\r\n" .
        // output software classes
        '.software_highlight' . "\r\n" . 
        '{' . "\r\n" .
            'font-weight: bold;' . "\r\n" .
            'color: #' . $primary_color . ' !important;' . "\r\n" .
        '}' . "\r\n" . 
        '.software_hr' . "\r\n" . 
        '{' . "\r\n" .
            'border-top-width: 0px;' . "\r\n" . 
            'border-right-width: 0px;' . "\r\n" . 
            'border-bottom-width: 0px;' . "\r\n" . 
            'border-left-width: 0px;' . "\r\n" . 
            'color: #' . $site_wide_properties['base_object']['text']['font_color'] . ' !important;' . "\r\n" . 
            'height: 1px;' . "\r\n" . 
            'background-color: #' . $site_wide_properties['base_object']['text']['font_color'] . ' !important;' . "\r\n" .
        '}' . "\r\n" .
        '.software_input_radio,' . "\r\n" . 
        '.software_input_checkbox' . "\r\n" . 
        '{' . "\r\n" .
            'border-top-width: 0px;' . "\r\n" . 
            'border-right-width: 0px;' . "\r\n" . 
            'border-bottom-width: 0px;' . "\r\n" . 
            'border-left-width: 0px;' . "\r\n" . 
        '}' . "\r\n" . 
        'input.software_input_submit_small_secondary' . "\r\n" . 
        '{' . "\r\n" .
            'display: inline-block;' . "\r\n" . 
            'line-height: normal;' . "\r\n" .
        '}' . "\r\n" . 
        '.software_legend' . "\r\n" . 
        '{' . "\r\n" .
            'color: #' . $primary_color . ' !important;' . "\r\n" . 
            'font-weight: bold;' . "\r\n" .
        '}' . "\r\n" . 
        '.software_fieldset' . "\r\n" . 
        '{' . "\r\n" .
            'border: 1px solid #' . $primary_color . ' !important;' . "\r\n" .
            'margin: 0 0 1em 0;' . "\r\n" .
        '}' . "\r\n" . 
        '.software_office_use_only' . "\r\n" . 
        '{' . "\r\n" .
            'font-weight: bold;' . "\r\n" .
        '}' . "\r\n" . 
        '.software_monthly_calendar' . "\r\n" . 
        '{' . "\r\n" .
            'width: 100%;' . "\r\n" . 
            'border-collapse: collapse;' . "\r\n" . 
            'border: 1px solid #' . $primary_color . ' !important;' . "\r\n" .
        '}' . "\r\n" .
        '.software_calendar form input,' . "\r\n" . 
        '.software_calendar form .software_select,' . "\r\n" .
        '.software_calendar form .software_input_submit_small_secondary' . "\r\n" .
        '{' . "\r\n" . 
            'vertical-align: middle !important;' . "\r\n" . 
        '}' . "\r\n" . 
        '.software_monthly_calendar a:link,' . "\r\n" . 
        '.software_monthly_calendar a:visited' . "\r\n" . 
        '{' . "\r\n" .
            'text-decoration: none;' . "\r\n" . 
            'border: none;' . "\r\n" . 
            'line-height: 1.2em;' . "\r\n" .
        '}' . "\r\n" . 
        '.software_monthly_calendar td, .software_monthly_calendar th' . "\r\n" . 
        '{' . "\r\n" .
            'line-height: 1em;' . "\r\n" .
            'padding: 1em;' . "\r\n" . 
            'vertical-align: top;' . "\r\n" .
            'border: 1px solid #' . $primary_color . ' !important;' . "\r\n" .
        '}' . "\r\n" . 
        '.software_monthly_calendar th' . "\r\n" . 
        '{' . "\r\n" .
            'background: #' . $primary_color . ' !important;' . "\r\n" .
            'color: #' . $primary_button_color . ' !important;' . "\r\n" .
        '}' . "\r\n" . 
        '.software_monthly_calendar td.inactive' . "\r\n" . 
        '{' . "\r\n" .
            'background-image: url({path}{software_directory}/images/translucent_20.png);' . "\n" .
        '}' . "\r\n" .
        '.software_pagination' . "\r\n" . 
        '{' . "\r\n" .
            'margin-top: 1em;' . "\r\n" .
            'margin-bottom: 1em;' . "\r\n" .
            'text-decoration: none;' . "\r\n" .
            'font-size: 80%;' . "\r\n" .
            'font-weight: bold;' . "\r\n" .
        '}' . "\r\n" . 
        '.software_pagination a,' . "\r\n" . 
        '.software_pagination span' . "\r\n" . 
        '{' . "\r\n" .
            'padding: 0.2em 0.4em !important;' . "\r\n" .
            'margin-left: 0.1em;' . "\r\n" .
            'margin-right: 0.1em;' . "\r\n" .
            'text-decoration: none;' . "\r\n" .
            'font-style: normal;' . "\r\n" .
            'border: 1px solid;' . "\r\n" .
        '}' . "\r\n" . 
        '.software_pagination a' . "\r\n" . 
        '{' . "\r\n" .
            'color: #' . $primary_button_background . ' !important;' . "\r\n" .
        '}' . "\r\n" . 
        '.software_pagination a:hover,' . "\r\n" .
        '.software_pagination a.previous:hover,' . "\r\n" . 
        '.software_pagination a.next:hover' . "\r\n" . 
        '{' . "\r\n" .
            'border: 1px solid #' . $primary_button_background . ' !important;' . "\r\n" .
            'color: #' . $primary_button_color . ' !important;' . "\r\n" .
            'background: #' . $primary_button_background . ' !important;' . "\r\n" .
        '}' . "\r\n" . 
        '.software_pagination .current' . "\r\n" . 
        '{' . "\r\n" .
            'border: 1px solid;' . "\r\n" .
        '}' . "\r\n" . 
        '.software_pagination a.previous,' . "\r\n" . 
        '.software_pagination a.next' . "\r\n" . 
        '{' . "\r\n" .
            'border: 1px solid;' . "\r\n" .
        '}' . "\r\n" . 
        '.software_pagination span.previous,' . "\r\n" . 
        '.software_pagination span.next' . "\r\n" . 
        '{' . "\r\n" .
            'display: none;' . "\r\n" .
        '}' . "\r\n" . 
        '.software_catalog,' . "\r\n" . 
        '.software_catalog .featured_and_new_item_table,' . "\r\n" . 
        '.software_catalog .item_table' . "\r\n" . 
        '{' . "\r\n" .
            'border-collapse: collapse;' . "\r\n" . 
        '}' . "\r\n" . 
        '.software_catalog .featured_and_new_item_table' . "\r\n" . 
        '{' . "\r\n" .
            'width: 100%;' . "\r\n" . 
        '}' . "\r\n" . 
        '.software_catalog table td' . "\r\n" . 
        '{' . "\r\n" .
            'vertical-align: top;' . "\r\n" . 
        '}' . "\r\n" . 
        '.software_catalog .heading' . "\r\n" . 
        '{' . "\r\n" .
            'margin-bottom: .5em;' . "\r\n" . 
        '}' . "\r\n" . 
        '.software_catalog .item_table' . "\r\n" . 
        '{' . "\r\n" .
            'border-collapse: collapse;' . "\r\n" .
            'width: 100%;' . "\r\n" .
        '}' . "\r\n" . 
        '.software_catalog .item' . "\r\n" . 
        '{' . "\r\n" .
            'margin: 0em 0em 2em 0em;' . "\r\n" .
        '}' . "\r\n" .               
        '.software_catalog .item .short_description' . "\r\n" . 
        '{' . "\r\n" .
            'text-align: center;' . "\r\n" .
        '}' . "\r\n" . 
        '.software_catalog .featured_and_new_item_table' . "\r\n" . 
        '{' . "\r\n" .
            'width: 100%;' . "\r\n" . 
            'margin: 0em 0em 0em 0em;' . "\r\n" . 
        '}' . "\r\n" . 
        '.software_catalog .featured_and_new_item_table .top_item' . "\r\n" . 
        '{' . "\r\n" .
              'margin-right: 10%;' . "\r\n" .
        '}' . "\r\n" .
        '.more_detail' . "\r\n" . 
        '{' . "\r\n" .
            'margin: 1em 0em;' . "\r\n" . 
        '}' . "\r\n" . 
        '.software_catalog .featured_and_new_item_table .top_item .more_detail a' . "\r\n" . 
        '{' . "\r\n" .
            'font-weight: normal;' . "\r\n" . 
        '}' . "\r\n" .
        '.software_catalog_search_results' . "\r\n" . 
        '{' . "\r\n" .
            'margin-top: 1em;' . "\r\n" . 
            'margin-bottom: 1em;' . "\r\n" . 
        '}' . "\r\n" . 
        '.software_catalog_search_results .item' . "\r\n" . 
        '{' . "\r\n" .
            'margin-bottom: 1em;' . "\r\n" . 
        '}' . "\r\n" . 
        '.software_catalog_search_results .item .image' . "\r\n" . 
        '{' . "\r\n" .
            'margin-bottom: .25em;' . "\r\n" . 
        '}' . "\r\n" . 
        '.software_catalog_search_results .item .short_description' . "\r\n" . 
        '{' . "\r\n" .
            'margin-bottom: .25em;' . "\r\n" . 
        '}' . "\r\n" . 
        '.software_catalog_detail .keywords,' . "\r\n" . 
        '.software_catalog_detail .price' . "\r\n" . 
        '{' . "\r\n" .
            'padding-bottom: 1em;' . "\r\n" .
        '}' . "\r\n" .
        '.software_tag_cloud' . "\r\n" . 
        '{' . "\r\n" .
            'text-align: left;' . "\r\n" .
        '}' . "\r\n" .
        '.comments_heading' . "\r\n" .
        '{' . "\r\n" .
            'margin: 1em 0em .5em 0em;' . "\r\n" .
        '}' . "\r\n" .
        '.comments_heading .title' . "\r\n" .
        '{' . "\r\n" .
            'font-size: 120%;' . "\r\n" .
            'font-weight: bold;' . "\r\n" .
        '}' . "\r\n" .
        '.comments_heading .links,' . "\r\n" .
        '.comments_heading .links a' . "\r\n" .
        '{' . "\r\n" .
            'font-size: 90%;' . "\r\n" .
        '}' . "\r\n" .
        '.add_comment_heading' . "\r\n" . 
        '{' . "\r\n" .
            'font-weight: bold;' . "\r\n" . 
            'margin: 1em 0em .5em 0em;' . "\r\n" . 
        '}' . "\r\n" .
        '.comment' . "\r\n" . 
        '{' . "\r\n" .
            'margin: 0em 0em 1em 0em;' . "\r\n" . 
            'padding: 1em;' . "\r\n" . 
            'border-top: 1px solid;' . "\r\n" . 
        '}' . "\r\n" . 
        '.comment .name' . "\r\n" . 
        '{' . "\r\n" .
            'font-weight: bold;' . "\r\n" . 
        '}' . "\r\n" . 
        '.comment .date_and_time' . "\r\n" . 
        '{' . "\r\n" .
            'font-size: 75%;' . "\r\n" .
            'font-style: italic;' . "\r\n" . 
        '}' . "\r\n" . 
        '.comment .notice' . "\r\n" . 
        '{' . "\r\n" .
            'color: red;' . "\r\n" . 
        '}' . "\r\n" . 
        '.software_cart_region' . "\r\n" . 
        '{' . "\r\n" .
            'padding: 0em;' . "\r\n" . 
            'text-align: left;' . "\r\n" . 
            'display: inline;' . "\r\n" .
            'text-decoration:none;' . "\r\n" .
        '}' . "\r\n" .
        '.software_cart_region .items' . "\r\n" . 
        '{' . "\r\n" .
            'display: inline;' . "\r\n" . 
            'padding: 0em;' . "\r\n" .
        '}' . "\r\n" . 
        '.software_icalendar_link' . "\r\n" . 
        '{' . "\r\n" .
            'margin-top: 1em;' . "\r\n" . 
            'margin-bottom: 1em;' . "\r\n" . 
        '}' . "\r\n" . 
        '.software_discounted_price' . "\r\n" . 
        '{' . "\r\n" .
            'color: #FF0000;' . "\r\n" .
        '}' . "\r\n" . 
        '.software_login_region form' . "\r\n" . 
        '{' . "\r\n" .
            'margin: 0em;' . "\r\n" . 
            'padding: 0em;' . "\r\n" . 
        '}' . "\r\n" . 
        '.software_login_region .software_input_checkbox' . "\r\n" . 
        '{' . "\r\n" .
            'margin: .25em .3em .65em 0em;' . "\r\n" . 
        '}' . "\r\n" . 
        '.software_login_region input' . "\r\n" . 
        '{' . "\r\n" .
            'margin: 0em .25em .5em 0em;' . "\r\n" . 
        '}' . "\r\n" . 
        '.software_comments .watcher_container' . "\r\n" . 
        '{' . "\r\n" .
            'margin-top: 1em;' . "\r\n" . 
        '}' . "\r\n" . 
        '.watcher_container' . "\r\n" . 
        '{' . "\r\n" .
            'margin: 2em 0em .5em 0em;' . "\r\n" . 
        '}' . "\r\n" . 
        '.watcher_count' . "\r\n" . 
        '{' . "\r\n" .
            'font-weight: bold;' . "\r\n" . 
            'margin: 0em 0em .5em 0em;' . "\r\n" . 
        '}' . "\r\n" . 
        '.watcher_question' . "\r\n" . 
        '{' . "\r\n" .
            'margin: 0em 0em .5em 0em;' . "\r\n" . 
        '}' . "\r\n" .
        '.software_photo_gallery_album .heading' . "\r\n" . 
        '{' . "\r\n" .
            'margin-bottom: .5em;' . "\r\n" . 
        '}' . "\r\n" .
        '.software_photo_gallery_album table' . "\r\n" .  
        '{' . "\r\n" .
            'border-collapse: collapse;' . "\r\n" . 
            'margin-bottom: 1em;' . "\r\n" . 
        '}' . "\r\n" .
        '.software_photo_gallery_album table td' . "\r\n" . 
        '{' . "\r\n" .
            'width: 100px;' . "\r\n" . 
            'text-align: center;' . "\r\n" . 
        '}' . "\r\n" .
        '.software_photo_gallery_album table td.album' . "\r\n" . 
        '{' . "\r\n" .
            'padding: 1em;' . "\r\n" . 
        '}' . "\r\n" .
        '.software_photo_gallery_album table td.photo' . "\r\n" . 
        '{' . "\r\n" .
            'padding: .5em;' . "\r\n" . 
        '}' . "\r\n" .
        '.software_photo_gallery_album .image' . "\r\n" .
        '{' . "\r\n" .
            'cursor: pointer;' . "\r\n" . 
        '}' . "\r\n" .
        '.software_photo_gallery_album .album .image' . "\r\n" . 
        '{' . "\r\n" .
            'display: block;' . "\r\n" . 
            'border: 1px solid #' . $primary_color . ' !important;' . "\r\n" . 
            'background: #' . $secondary_color . ' !important;' . "\r\n" . 
            'padding: 5px;' . "\r\n" . 
            'position: relative;' . "\r\n" . 
            'z-index: 3;' . "\r\n" . 
        '}' . "\r\n" .
        '.software_photo_gallery_album .album .image_hover' . "\r\n" . 
        '{' . "\r\n" .
            'background: #' . $primary_color . ' !important;' . "\r\n" . 
            'border: 1px solid #' . $secondary_color . ' !important;' . "\r\n" . 
        '}' . "\r\n" .
        '.software_photo_gallery_album .album .thumbnail' . "\r\n" . 
        '{' . "\r\n" .
            'margin-bottom: 1em;' . "\r\n" . 
        '}' . "\r\n" .
        '.software_photo_gallery_album .album_frame' . "\r\n" . 
        '{' . "\r\n" .
            'position: absolute;' . "\r\n" . 
            'border: 1px solid #' . $primary_color . ' !important;' . "\r\n" . 
            'background: #' . $secondary_color . ' !important;' . "\r\n" . 
        '}' . "\r\n" .
        '.software_photo_gallery_album #album_frame_1' . "\r\n" . 
        '{' . "\r\n" .
            'top: 1px;' . "\r\n" . 
            'left: 1px;' . "\r\n" . 
            'z-index: 2;' . "\r\n" . 
        '}' . "\r\n" .
        '.software_photo_gallery_album #album_frame_2' . "\r\n" . 
        '{' . "\r\n" .
            'top: 4px;' . "\r\n" . 
            'left: 4px;' . "\r\n" . 
            'z-index: 1;' . "\r\n" . 
        '}' . "\r\n" .
        '.software_photo_gallery_album .album .name' . "\r\n" . 
        '{' . "\r\n" .
            'font-weight: bold;' . "\r\n" . 
        '}' . "\r\n" .
        '.software_photo_gallery_album .photo .image' . "\r\n" . 
        '{' . "\r\n" .
            'border: 1px solid #' . $secondary_color . ' !important;' . "\r\n" . 
            'padding: 5px;' . "\r\n" . 
        '}' . "\r\n" .
        '.software_photo_gallery_album .photo .image_hover' . "\r\n" . 
        '{' . "\r\n" .
            'border: 1px solid #' . $primary_color . ' !important;' . "\r\n" . 
        '}' . "\r\n" .
        '.heading' . "\r\n" .
        '{' . "\r\n" .
            'font-weight: bold;' . "\r\n" .
            'border-bottom: 1px solid #' . $primary_color_tint . ';' . "\r\n" .
            'padding-bottom: .5em;' . "\r\n" .
            'margin-bottom: .5em;' . "\r\n" .
        '}' . "\r\n" .
        '.data' . "\r\n" .
        '{' . "\r\n" .
            // intentionally left blank
        '}' . "\r\n" .
        '.software_calendar .today' . "\r\n" .
        '{' . "\r\n" .
            'font-weight: bold;' . "\r\n" .
        '}' . "\r\n" .
        '.mceContentBody a,.mceContentBody a:hover,.mceContentBody a:focus' . "\r\n" . 
        '{' . "\r\n" .
            'text-decoration: underline;' . "\r\n" . 
        '}' . "\r\n" .
        '.mceContentBody hr' . "\r\n" . 
        '{' . "\r\n" .
            'background: black !important;' . "\r\n" . 
        '}' . "\r\n" .
        '.mceContentBody span.text-highlighter' . "\r\n" . 
        '{' . "\r\n" .
            'color: black !important;' . "\r\n" .
            'background-color: #dedede !important;' . "\r\n" . 
        '}' . "\r\n" . 
       '.mceContentBody .mceItemTable td'. "\r\n" . 
        '{' . "\r\n" .
            'font-family: ' . $global_font_family . ';' . "\r\n" .
            'font-size: ' . $site_wide_properties['base_object']['text']['font_size'] . ';' . "\r\n" .
        '}' . "\r\n" .
        '.mceContentBody span.mceItemHiddenSpellWord'. "\r\n" . 
        '{' . "\r\n" .
            'color: white !important;' . "\r\n" .
            'background: red !important;' . "\r\n" .
            'padding: 2px !important;' . "\r\n" .
            'font-weight: bold !important;' . "\r\n" .
        '}' . "\r\n" .
        '.mceContentBody ul.list-accordion'. "\r\n" . 
        '{' . "\r\n" .
            'list-style-type: disc !important;' . "\r\n" .
            'padding: 0 0 0 40px !important;' . "\r\n" .
        '}' . "\r\n" .
        '.mceContentBody table'. "\r\n" .
        '{' . "\r\n" .
            'margin: 0px !important;' . "\r\n" .
        '}' . "\r\n" .
        '.software_ad_region_dynamic' . "\r\n" . 
        '{' . "\r\n" .
            'position: relative;' . "\r\n" . 
            'width: 895px;' . "\r\n" .
            'height: 200px;' . "\r\n" .
            'margin: 0 auto;' . "\r\n" .  // v8.5
        '}' . "\r\n" .
        '.software_ad_region_dynamic .items_container' . "\r\n" . 
        '{' . "\r\n" .
            'overflow: auto;' . "\r\n" . 
            'overflow-x: hidden;' . "\r\n" . 
            'position: relative;' . "\r\n" . 
            'clear: left;' . "\r\n" . 
            'width: 895px;' . "\r\n" .
            'height: 200px;' . "\r\n" .
        '}' . "\r\n" .
        '.software_ad_region_dynamic .item' . "\r\n" . 
        '{' . "\r\n" .
            'width: 899px;' . "\r\n" . 
            'height: 200px;' . "\r\n" . 
        '}' . "\r\n" .
        '.software_ad_region_dynamic ul.menu' . "\r\n" . 
        '{' . "\r\n" .
            'list-style: none;' . "\r\n" . 
            'position: absolute;' . "\r\n" . 
            'z-index: 1;' . "\r\n" . 
            'margin: 0em;' . "\r\n" . 
            'padding: 0em;' . "\r\n" .
            'bottom: 0em;' . "\r\n" .
            'right: 0em;' . "\r\n" .
        '}' . "\r\n" .
        '.software_ad_region_dynamic ul.menu li' . "\r\n" . 
        '{' . "\r\n" .
            'list-style: none;' . "\r\n" . 
            'display: inline;' . "\r\n" . 
            'margin-right: .5em;' . "\r\n" . 
        '}' . "\r\n" . 
        '.software_ad_region_dynamic .previous,' . "\n" .
        '.software_ad_region_dynamic .next' . "\n" .
        '{' . "\n" .
            'background-position: center;' . "\n" .
            'background-repeat: no-repeat;' . "\n" .
            'cursor: pointer;' . "\n" .
            'height: 60px;' . "\n" .
            'position: absolute;' . "\n" .
            'top: 3.5em;' . "\n" .
            'width: 47px;' . "\n" .
            'z-index: 2;' . "\n" .
        '}' . "\n" .
        '.software_ad_region_dynamic .previous' . "\n" .
        '{' . "\n" .
            'background-image: url({path}{software_directory}/images/previous.png);' . "\n" .
            'left: .5em;' . "\n" .
        '}' . "\n" .
        '.software_ad_region_dynamic .next' . "\n" .
        '{' . "\n" .
            'background-image: url({path}{software_directory}/images/next.png);' . "\n" .
            'right: .5em;' . "\n" .
        '}' . "\n" .
        '.software_ad_region_dynamic .caption' . "\n" .
        '{' . "\n" .
            'background-image: url({path}{software_directory}/images/translucent_black_60.png);' . "\n" .
            'bottom: 0;' . "\n" .
            'color: white;' . "\n" .
            'display: none;' . "\n" .
            'left: 0;' . "\n" .
            'position: absolute;' . "\n" .
        '}' . "\n" .
        '.software_ad_region_dynamic .caption a,' . "\n" .
        '.software_ad_region_dynamic .caption h1,' . "\n" .
        '.software_ad_region_dynamic .caption h2,' . "\n" .
        '.software_ad_region_dynamic .caption h3,' . "\n" .
        '.software_ad_region_dynamic .caption h4,' . "\n" .
        '.software_ad_region_dynamic .caption h5,' . "\n" .
        '.software_ad_region_dynamic .caption h6' . "\n" .
        '{' . "\n" .
            'color: white;' . "\n" .
        '}' . "\n" .
        '.software_ad_region_dynamic .caption p' . "\n" .
        '{' . "\n" .
            'margin: 0;' . "\n" .
        '}' . "\n" .
        '.software_ad_region_dynamic .caption_content' . "\n" .
        '{' . "\n" .
            'padding: 1em 2em;' . "\n" .
        '}' . "\n" .
        '.one_column_mobile .software_ad_region_dynamic .caption_content' . "\n" .
        '{' . "\n" .
            'padding: .5em;' . "\n" .
        '}' . "\n" .
        '.software_menu,' . "\r\n" . 
        '.software_menu ul' . "\r\n" . 
        '{' . "\r\n" .
            'padding: 0em;' . "\r\n" . 
            'margin: 0em;' . "\r\n" . 
            'list-style-type: none;' . "\r\n" . 
        '}' . "\r\n" . 
        '.software_menu li' . "\r\n" . 
        '{' . "\r\n" .
            'position: relative;' . "\r\n" . 
            'padding: 0;' . "\r\n" . 
            'margin: 0em 1em 0em 0em;' . "\r\n" . 
            'float: left;' . "\r\n" . 
        '}' . "\r\n" . 
        '.software_menu li a' . "\r\n" . 
        '{' . "\r\n" .
            'display: block;' . "\r\n" . 
        '}' . "\r\n" . 
        '.software_menu li ul' . "\r\n" . 
        '{' . "\r\n" .
            'position: absolute;' . "\r\n" . 
            'display: none;' . "\r\n" . 
            'top: 50px;' . "\r\n" . 
            'left: 0;' . "\r\n" . 
            'width: auto;' . "\r\n" . 
            'padding: .5em;' . "\r\n" . 
        '}' . "\r\n" . 
        '.software_menu li ul li' . "\r\n" . 
        '{' . "\r\n" .
            'padding: 0em;' . "\r\n" . 
            'margin: 0;' . "\r\n" . 
            'width: auto;' . "\r\n" . 
        '}' . "\r\n" . 
        '.software_menu_sequence' . "\r\n" . 
        '{' . "\r\n" .
            'padding: 0em;' . "\r\n" . 
            'margin: 0em 0em 1em 0em;' . "\r\n" . 
            'text-align: right;' . "\r\n" . 
        '}' . "\r\n" . 
        '.software_menu_sequence .previous,' . "\r\n" . 
        '.software_menu_sequence .next' . "\r\n" . 
        '{' . "\r\n" .
            'padding: .5em;' . "\r\n" . 
            'margin: 0em;' . "\r\n" . 
        '}' . "\r\n" . 
        '.software_menu_sequence a.previous:hover,' . "\r\n" . 
        '.software_menu_sequence a.previous:focus,' . "\r\n" . 
        '.software_menu_sequence a.next:hover,' . "\r\n" . 
        '.software_menu_sequence a.next:focus' . "\r\n" . 
        '{' . "\r\n" .
            'text-decoration: none;' . "\r\n" . 
        '}' . "\r\n" . 
        '.software_error,' . "\r\n" .
        '.software_notice' . "\r\n" .
        '{' . "\r\n" .
        '    margin-bottom: 1.5em;' . "\r\n" .
        '    padding: 1em;' . "\r\n" .
        '    -moz-border-radius: 7px 7px 7px 7px;' . "\r\n" .
        '    -webkit-border-radius: 7px 7px 7px 7px;' . "\r\n" .
        '    border-radius: 7px 7px 7px 7px;' . "\r\n" .
        '}' . "\r\n" .
        "\r\n" .
        '.software_error' . "\r\n" .
        '{' . "\r\n" .
        '    background-color: #fdd5ce;' . "\r\n" .
        '    border: 2px solid red;' . "\r\n" .
        '    color: red;' . "\r\n" .
        '}' . "\r\n" .
        "\r\n" .
        '.software_notice' . "\r\n" .
        '{' . "\r\n" .
        '    background-color: #edfced;' . "\r\n" .
        '    border: 1px solid #428221;' . "\r\n" .
        '    color: #428221;' . "\r\n" .
        '}' . "\r\n" .
        "\r\n" .
        '.software_error .description,' . "\r\n" .
        '.software_notice .description' . "\r\n" .
        '{' . "\r\n" .
        '    font-size: 110%;' . "\r\n" .
        '    font-weight: bold;' . "\r\n" .
        '}' . "\r\n" .
        "\r\n" .
        '.software_error .icon,' . "\r\n" .
        '.software_notice .icon' . "\r\n" .
        '{' . "\r\n" .
        '    float: left;' . "\r\n" .
        '    margin-right: .75em;' . "\r\n" .
        '}' . "\r\n" .
        "\r\n" .
        '.software_error ul,' . "\r\n" .
        '.software_notice ul' . "\r\n" .
        '{' . "\r\n" .
        '    margin-top: 1em !important;' . "\r\n" .
        '    margin-bottom: 0em !important;' . "\r\n" .
        '}' . "\r\n" .
        '.software_badge' . "\r\n" .
        '{' . "\r\n" .
        'padding: 0.1em 0.2em 0;' . "\r\n" .
        'vertical-align: middle;' . "\r\n" .
        'border: 1px solid;' . "\r\n" .
        'font-size: 70%;' . "\r\n" .
        'font-weight: bold;' . "\r\n" .
        'font-style: normal;' . "\r\n" .
        '-moz-border-radius: 3px 3px 3px 3px;' . "\r\n" .
        '-webkit-border-radius: 3px 3px 3px 3px;' . "\r\n" .
        'border-radius: 3px 3px 3px 3px;' . "\r\n" .
        '}' . "\r\n" .
        "\r\n" .
        // additional styling for mobile and v8.0
        '.software_mobile_switch' . "\r\n" .
        '{' . "\r\n" .
        '    text-align: center;' . "\r\n" .
        '    padding: 1em;' . "\r\n" .
        '}' . "\r\n" .
        // v8.0 align radio buttons for desktop and mobile
        'label' . "\r\n" .
        '{' . "\r\n" .
        '    vertical-align: middle;' . "\r\n" .
        '}' . "\r\n" .
        // mobile image overrides
        '.one_column_mobile .image-right-primary,' . "\r\n" .
        '.one_column_mobile .image-left-primary,' . "\r\n" .
        '.one_column_mobile .image-right-secondary,' . "\r\n" .
        '.one_column_mobile .image-left-secondary' . "\r\n" .
        '{' . "\r\n" .
        '   float: left;' . "\r\n" .
        '   margin-right: 1em;' . "\r\n" .
        '   margin-left: 0;' . "\r\n" .
        '}' . "\r\n" .
        '.one_column_mobile .video-primary object,' . "\r\n" .
        '.one_column_mobile .video-primary iframe,' . "\r\n" .
        '.one_column_mobile .video-primary video,' . "\r\n" .
        '.one_column_mobile .video-secondary object,' . "\r\n" .
        '.one_column_mobile .video-secondary iframe,' . "\r\n" .
        '.one_column_mobile .video-secondary video,' . "\r\n" .
        '.one_column_mobile .video-right-primary object,' . "\r\n" .
        '.one_column_mobile .video-right-primary iframe,' . "\r\n" .
        '.one_column_mobile .video-right-primary video,' . "\r\n" .
        '.one_column_mobile .video-left-primary object,' . "\r\n" .
        '.one_column_mobile .video-left-primary iframe,' . "\r\n" .
        '.one_column_mobile .video-left-primary video,' . "\r\n" .
        '.one_column_mobile .video-right-secondary object,' . "\r\n" .
        '.one_column_mobile .video-right-secondary iframe,' . "\r\n" .
        '.one_column_mobile .video-right-secondary video,' . "\r\n" .
        '.one_column_mobile .video-left-secondary object,' . "\r\n" .
        '.one_column_mobile .video-left-secondary iframe,' . "\r\n" .
        '.one_column_mobile .video-left-secondary video' . "\r\n" .
        '{' . "\r\n" .
        '   float: left;' . "\r\n" .
        '   margin-right: 1em;' . "\r\n" .
        '   margin-left: 0;' . "\r\n" .
        '   border-width: 1px;' . "\r\n" .
        '   border-radius: 0;' . "\r\n" .
        '   -moz-border-radius: 0;' . "\r\n" .
        '   -webkit-border-radius: 0;' . "\r\n" .
        '}' . "\r\n" .
        '.one_column_mobile img,' . "\r\n" .
        '.one_column_mobile object,' . "\r\n" .
        '.one_column_mobile iframe,' . "\r\n" .
        '.one_column_mobile video' . "\r\n" .
        '{' . "\r\n" .
        '   max-width: 100%;' . "\r\n" .
        '   width: auto\9;' . "\r\n" .  //ie8
        '}' . "\r\n" .
        '.one_column_mobile embed,' . "\r\n" .
        '.one_column_mobile object,' . "\r\n" .
        '.one_column_mobile iframe,' . "\r\n" .
        '.one_column_mobile video' . "\r\n" .
        '{' . "\r\n" .
        '    width: 100%;' . "\r\n" .
        '}' . "\r\n" .
        // v8.0 this is required to wrap custom form labels and fields for mobile viewing
        '.one_column_mobile .software_input_text,' . "\r\n" .
        '.one_column_mobile .software_textarea,' . "\r\n" .
        '.one_column_mobile .software_input_password' . "\r\n" .
        '{' . "\r\n" .
        '    width: 98%;' . "\r\n" .
        '}' . "\r\n" .
        '.one_column_mobile .mobile_width' . "\r\n" .
        '{' . "\r\n" .
        '    width: 100% !important;' . "\r\n" .
        '    white-space: normal !important;' . "\r\n" .
        '}' . "\r\n" .
        '.one_column_mobile .mobile_fixed_width' . "\r\n" .
        '{' . "\r\n" .
        '    width: 100px;' . "\r\n" .
        '}' . "\r\n" .
        '.one_column_mobile .mobile_left' . "\r\n" .
        '{' . "\r\n" .
        '    float: left !important;' . "\r\n" .
        '}' . "\r\n" .
        '.one_column_mobile .mobile_right' . "\r\n" .
        '{' . "\r\n" .
        '    float: right !important;' . "\r\n" .
        '}' . "\r\n" .
        '.one_column_mobile .mobile_hide' . "\r\n" .
        '{' . "\r\n" .
        '    display: none;' . "\r\n" .
        '}' . "\r\n" .
        '.one_column_mobile .mobile_text_width' . "\r\n" .
        '{' . "\r\n" .
        '    width: 50% !important' . "\r\n" .
        '}' . "\r\n" .
        '.one_column_mobile .mobile_align_left' . "\r\n" .
        '{' . "\r\n" .
        '    text-align: left !important;' . "\r\n" .
        '    white-space: normal !important;' . "\r\n" .
        '    margin-bottom: .5em !important;' . "\r\n" .
        '}' . "\r\n" .
        '.one_column_mobile .mobile_align_left input' . "\r\n" .
        '{' . "\r\n" .
        '    margin-bottom: .5em !important;' . "\r\n" .
        '}' . "\r\n" .
        '.one_column_mobile .mobile_margin_top' . "\r\n" .
        '{' . "\r\n" .
        '    margin-top: 1em !important;' . "\r\n" .
        '}' . "\r\n" .
        '.one_column_mobile .mobile_margin_bottom' . "\r\n" .
        '{' . "\r\n" .
        '    margin-bottom: 1em !important;' . "\r\n" .
        '}' . "\r\n" .
        // my account page adjustments for mobile
        '.one_column_mobile .complete_orders .data,' . "\r\n" .
        '.one_column_mobile .incomplete_orders .data' . "\r\n" .
        '{' . "\r\n" .
        '    padding-left: 0 !important;' . "\r\n" .
        '}' . "\r\n" .
        // credit card fields padding on mobile express order
        '.one_column_mobile #credit_debit_card_fields' . "\r\n" .
        '{' . "\r\n" .
        '    padding-left: 0 !important;' . "\r\n" .
        '}' . "\r\n" .
        //checkbox and radio buttons larger for mobile
        '.one_column_mobile .software_input_radio,' . "\r\n" . 
        '.one_column_mobile .software_input_checkbox' . "\r\n" . 
        '{' . "\r\n" .
            'font-size: 150%;' . "\r\n" . 
        '}' . "\r\n" .
        // force captcha input field smaller for ui clarity 
        '.one_column_mobile .software_captcha_answer' . "\r\n" . 
        '{' . "\r\n" .
            'width: 2em;' . "\r\n" . 
        '}' . "\r\n" .
        // force card verification number field smaller for ui clarity 
        '.one_column_mobile card_verification_number input' . "\r\n" . 
        '{' . "\r\n" .
            'width: 5em;' . "\r\n" . 
        '}' . "\r\n" .
        // spacing for thumbnails in catalog/photo gallery pages 
        '.one_column_mobile div.item.mobile_left,' . "\r\n" .  
        '.one_column_mobile td.mobile_spacer' . "\r\n" .
        '{' . "\r\n" .
            'margin-right: .5em;' . "\r\n" . 
        '}' . "\r\n" .
'
/*  Tabbed List CSS support
    Caution! Ensure accessibility in print and other media types. */
@media projection, screen { /* Use class for showing/hiding tab content, so that visibility can be better controlled in different media types... */
    .ui-tabs-hide {
        display: none !important;
    }
}
/* Hide useless elements in print layouts... */
@media print {
    .ui-tabs-nav {
        display: none;
    }
}
.ui-tabs-nav
{
    list-style: none;
    margin-bottom: 0px;
    padding: 0;
}
.ui-tabs-nav:after 
{ /* clearing without presentational markup, IE gets extra treatment */
    display: block;
    clear: both;
    content: " ";
}
.ui-tabs-nav li 
{
    float: left;
}
.ui-tabs-nav a,
.ui-tabs-nav a span 
{
    float: left; /* fixes dir=ltr problem and other quirks IE */
}
.ui-tabs-nav a
{
    background-position: 100% 0;
    text-decoration: none;
    white-space: nowrap; /* @ IE 6 */
    outline: 0; /* @ Firefox, prevent dotted border after click */
    position: relative;
    top: 0px;
    z-index: 2;
    padding-right: .5em;
}
.ui-tabs-nav .ui-tabs-selected a:link,
.ui-tabs-nav .ui-tabs-selected a:visited,
.ui-tabs-nav .ui-tabs-disabled a:link,
.ui-tabs-nav .ui-tabs-disabled a:visited
{ /* @ Opera, use pseudo classes otherwise it confuses cursor... */
    cursor: text;
}
.ui-tabs-nav a:hover,
.ui-tabs-nav a:focus,
.ui-tabs-nav a:active,
.ui-tabs-nav .ui-tabs-unselect a:hover,
.ui-tabs-nav .ui-tabs-unselect a:focus,
.ui-tabs-nav .ui-tabs-unselect a:active
{ /* @ Opera, we need to be explicit again here now... */
    cursor: pointer;
}
/* Additional IE specific bug fixes... */
* html .ui-tabs-nav
{ /* auto clear @ IE 6 & IE 7 Quirks Mode */
    display: inline-block;
}
*:first-child+html .ui-tabs-nav
{ /* auto clear @ IE 7 Standards Mode - do not group selectors, otherwise IE 6 will ignore complete rule (because of the unknown + combinator)... */
    display: inline-block;
}
.ui-tabs-panel
{
    padding: 1em;
}
.ui-tabs-nav a
{
    background-image: url({path}{software_directory}/images/translucent_10.png);
    padding: .2em .5em;
    -moz-border-radius: 4px 4px 0px 0px;
    -webkit-border-radius: 4px 4px 0px 0px;
    border-radius: 4px 4px 0px 0px;
    border: none;
}
.ui-tabs-selected a,
.ui-tabs-panel
{
    background-image: url({path}{software_directory}/images/translucent_20.png);
    border: none;
}
.ui-tabs-selected a {
    -moz-border-radius: 4px 4px 0px 0px;
    -webkit-border-radius: 4px 4px 0px 0px;
    border-radius: 4px 4px 0px 0px;
    border: none;
}
.ui-tabs-panel
{
    -moz-border-radius: 0px 4px 4px 4px;
    -webkit-border-radius: 0px 4px 4px 4px;
    border-radius: 0px 4px 4px 4px;
}
ul.list-accordion a.item_heading
{
    display: block;
    padding: 0.5em 1em;
    margin: 0em 0em .5em 0em;
    font-size: 100%;
    font-weight: normal;
    font-style: normal;
    text-decoration: none;
    background-image: url({path}{software_directory}/images/translucent_20.png);
    -moz-border-radius: 4px 4px 4px 4px;
    -webkit-border-radius: 4px 4px 4px 4px;
    border-radius: 4px 4px 4px 4px;
    border: none;
}
ul.list-accordion a.item_heading:hover,
ul.list-accordion a.item_heading:focus,
ul.list-accordion a.item_heading:active
{
    background-image: url({path}{software_directory}/images/translucent_20.png);
    outline: 0 none;
    border: none;
}
ul.list-accordion
{
    list-style-type: none;
    padding: 0;
}
ul div.ui-accordion-content-active {
    padding: 0 1em;
}
ul a.item_heading.ui-state-default:before {
    content: \'\\25BA\';
    margin-right: .5em;
    font-size: 80%;
}
ul a.item_heading.ui-state-active:before {
    content: \'\\25BC\';
    margin-right: .5em;
    font-size: 80%;
}
ol.list-accordion
{
    background-image: url({path}{software_directory}/images/translucent_20.png);
    padding-top: 1em;
    padding-bottom: 1em;
    -moz-border-radius: 4px 4px 4px 4px;
    -webkit-border-radius: 4px 4px 4px 4px;
    border-radius: 4px 4px 4px 4px;
}
ol div.ui-accordion-content-active {
    padding: 0 2em 0 0;
}
ol.list-accordion a.item_heading
{
    display: block;
    margin: 0em 0em .5em 0em;
    padding: .5em 0;
    font-size: 100%;
    font-weight: normal;
    font-style: normal;
    text-decoration: none;
    border: none;
}
ol.list-accordion a.item_heading:hover,
ol.list-accordion a.item_heading:focus,
ol.list-accordion a.item_heading:active
{
    outline: 0 none;
    border: none;
}
/* Dialog styling */

.software iframe.ui-dialog-content {
    width: 100% !important; /* for jquery UI v1.8 */
}

div.software.ui-dialog {
    border-top: 2px solid #' . $primary_button_background . ';
    border-right: 5px solid #' . $primary_button_background . ';
    border-bottom: 5px solid #' . $primary_button_background . ';
    border-left: 5px solid #' . $primary_button_background . ';
}

.software .ui-dialog .ui-dialog-titlebar,
.software.ui-dialog .ui-dialog-titlebar {
    line-height: 100%;
    font-size: 12px;
    font-weight: bold;
    padding-top: 5px;
    margin: 0px;
    height: 20px;
    background: #' . $primary_button_background . ';
}

.software .ui-draggable .ui-dialog-titlebar,
.software.ui-draggable .ui-dialog-titlebar {
    cursor: move;
}

.software .ui-draggable-disabled .ui-dialog-titlebar,
.software.ui-draggable-disabled .ui-dialog-titlebar {
    cursor: standard;
}

.software .ui-dialog .ui-dialog-titlebar-close,
.software.ui-dialog .ui-dialog-titlebar-close {
    width: 16px;
    height: 16px;
    background: #000 url({path}{software_directory}/jquery/theme/images/dialog-titlebar-close.gif) no-repeat;
    position: absolute;
    right: 0px;
    top: 3px;
    cursor: standard;
    -moz-border-radius: 20px 20px 20px 20px;
    -webkit-border-radius: 20px 20px 20px 20px;
    border-radius: 20px 20px 20px 20px;
    padding: 0;
}

.software .ui-dialog .ui-dialog-titlebar-close span,
.software.ui-dialog .ui-dialog-titlebar-close span {
    display: none;
}

.software .ui-dialog .ui-dialog-title,
.software.ui-dialog .ui-dialog-title {
    color: #' . $primary_button_color . ';
    padding: 0;
    margin: 0;
}

.software .ui-dialog .ui-dialog-title .title_bar_table,
.software.ui-dialog .ui-dialog-title .title_bar_table {
    border-collapse: collapse; 
    width: 100%; 
    margin: 0;
    padding: 0;
}

.software.ui-dialog .ui-dialog-content {
    margin: 0;
    background: #' . $primary_color . ';
}

.software .ui-dialog .ui-resizable-n,
.software.ui-dialog .ui-resizable-n { 
    cursor: n-resize; 
    height: 0px;
    width: 100%; 
    top: 0px;
    left: 0px;
}

.software .ui-dialog .ui-resizable-s,
.software.ui-dialog .ui-resizable-s { 
    cursor: s-resize; 
    height: 5px; 
    width: 100%; 
    bottom: 0px; 
    left: 0px;
}

.software .ui-dialog .ui-resizable-e,
.software.ui-dialog .ui-resizable-e { 
    cursor: e-resize; 
    width: 5px;
    right: 0px;
    top: 22px;
    height: 100%;
}

.software .ui-dialog .ui-resizable-w,
.software.ui-dialog .ui-resizable-w { 
    cursor: w-resize; 
    width: 5px;
    right: 0px;
    top: 22px;
    height: 100%;
}

.software .ui-dialog .ui-resizable-se,
.software.ui-dialog .ui-resizable-se {
    cursor: se-resize;
    width: 5px;
    height: 5px;
    right: 0px;
    bottom: 0px;
}

.software .ui-dialog .ui-resizable-sw,
.software.ui-dialog .ui-resizable-sw { 
    cursor: sw-resize; 
    width: 5px;
    height: 5px;
    left: 0px;
    bottom: 0px;
}

.software .ui-dialog .ui-resizable-nw,
.software.ui-dialog .ui-resizable-nw { 
    cursor: nw-resize; 
    width: 5px;
    height: 5px;
    left: 0px;
    top: 0px;
}

.software .ui-dialog .ui-resizable-ne,
.software.ui-dialog .ui-resizable-ne { 
    cursor: ne-resize;
    width: 0px;
    height: 0px;
    right: 0px;
    top: 0px;
}

.ui-widget-overlay
{
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: #000;
    filter:alpha(opacity=50);
    -moz-opacity:0.5;
    -khtml-opacity: 0.5;
    opacity: 0.5;
}

.software.ui-resizable { position: relative; }
.software .ui-resizable-handle { position: absolute; display: none; font-size: 0.1px; }
.software.ui-resizable .ui-resizable-handle { display: block; }
body .software.ui-resizable-disabled .ui-resizable-handle { display: none; } /* use body to make it more specific (css order) */
body .software.ui-resizable-autohide .ui-resizable-handle { display: none; } /* use body to make it more specific (css order) */
.software .ui-resizable-n { cursor: n-resize; height: 6px; width: 100%; top: 0px; left: 0px;  }
.software .ui-resizable-s { cursor: s-resize; height: 6px; width: 100%; bottom: 0px; left: 0px; }
.software .ui-resizable-e { cursor: e-resize; width: 6px; right: 0px; top: 0px; height: 100%; }
.software .ui-resizable-w { cursor: w-resize; width: 6px; left: 0px; top: 0px; height: 100%; }
.software .ui-resizable-se { cursor: se-resize; width: 9px; height: 9px; right: 0px; bottom: 0px;}
.software .ui-resizable-sw { cursor: sw-resize; width: 9px; height: 9px; left: 0px; bottom: 0px; }
.software .ui-resizable-nw { cursor: nw-resize; width: 9px; height: 9px; left: 0px; top: 0px; }
.software .ui-resizable-ne { cursor: ne-resize; width: 9px; height: 9px; right: 0px; top: 0px; }

.ui-datepicker {border: 1px solid #aaaaaa; background: #ffffff; color: #222222; width: 17em; padding: .2em .2em 0; display: none;}
.ui-datepicker-header {border: 1px solid #aaaaaa; background: #cccccc; color: #222222; font-weight: bold;}
.ui-datepicker .ui-state-default {border: 1px solid #d3d3d3; background: #e6e6e6; font-weight: normal; color: #555555;}
.ui-datepicker .ui-state-hover {border: 1px solid #999999; background: #dadada; font-weight: normal; color: #212121;}
.ui-datepicker .ui-state-active {border: 1px solid #aaaaaa; background: #ffffff; font-weight: normal; color: #212121;}
.ui-datepicker .ui-state-highlight {border: 1px solid #fcefa1; background: #fbf9ee; color: #363636;}
.ui-datepicker .ui-icon {display: block; text-indent: -99999px; overflow: hidden; background-repeat: no-repeat;}
.ui-datepicker .ui-icon {width: 16px; height: 16px; background-image: url({path}{software_directory}/jquery/theme/images/ui-icons_222222_256x240.png);}
.ui-datepicker .ui-icon-circle-triangle-w {background-position: -80px -192px;}
.ui-datepicker .ui-icon-circle-triangle-e {background-position: -48px -192px;}
.ui-datepicker-header { position:relative; padding:.2em 0; }
.ui-datepicker-prev, .ui-datepicker-next {position:absolute; top: 2px; width: 1.8em; height: 1.8em;}
.ui-datepicker-prev-hover, .ui-datepicker-next-hover {top: 1px;}
.ui-datepicker-prev {left:2px;}
.ui-datepicker-next {right:2px;}
.ui-datepicker-prev-hover {left:1px;}
.ui-datepicker-next-hover {right:1px;}
.ui-datepicker-prev span, .ui-datepicker-next span {display: block; position: absolute; left: 50%; margin-left: -8px; top: 50%; margin-top: -8px;}
a.ui-datepicker-prev, a.ui-datepicker-next {transition: none !important}
.ui-datepicker-title {margin: 0 2.3em; line-height: 1.8em; text-align: center;}
.ui-datepicker-title select {font-size:1em; margin:1px 0;}
.ui-datepicker table {width: 100%; font-size: .9em; border-collapse: collapse; margin:0 0 .4em;}
.ui-datepicker th {padding: .7em .3em; text-align: center; font-weight: bold; border: 0;}
.ui-datepicker td {border: 0; padding: 1px;}
.ui-datepicker td span, .ui-datepicker td a {display: block; padding: .2em; text-align: right; text-decoration: none;}
.ui-datepicker-buttonpane { background-image: none; margin: .7em 0 0 0; padding:0 .2em; border-left: 0; border-right: 0; border-bottom: 0; }
.ui-datepicker-buttonpane button { float: right; margin: .5em .2em .4em; cursor: pointer; padding: .2em .6em .3em .6em; width:auto; overflow:visible; }
.ui-datepicker-buttonpane button.ui-datepicker-current { float:left; }
.ui-slider {position: relative; text-align: left; border: 1px solid #aaaaaa;}
.ui-slider-handle {position: absolute; z-index: 2; width: 1.2em; height: 1.2em; cursor: default;}
.ui-slider-range {position: absolute; z-index: 1; font-size: .7em; display: block; border: 0; background-position: 0 0;}
.ui-slider-horizontal {height: .8em;}
.ui-slider-horizontal .ui-slider-handle {top: -.3em; margin-left: -.6em;}
.ui-slider-horizontal .ui-slider-range {top: 0; height: 100%;}
.ui-slider-horizontal .ui-slider-range-min {left: 0;}
.ui-slider-horizontal .ui-slider-range-max {right: 0;}
a.ui-slider-handle {transition: none !important}
.ui-timepicker-div .ui-widget-header {margin-bottom: 8px;}
.ui-timepicker-div dl {text-align: left;}
.ui-timepicker-div dl dt {height: 25px; margin-bottom: -25px;}
.ui-timepicker-div dl dd {margin: 0 10px 10px 65px;}
.ui-timepicker-div td {font-size: 90%;}
.ui-tpicker-grid-label {background: none; border: none; margin: 0; padding: 0;}

.software_form_list_view .browse_and_search_table
{
    border-collapse: collapse;
    width: 100%;
}

.software_form_list_view .browse_and_search_table td
{
    padding: 0;
    vertical-align: bottom;
}

.software_form_list_view .search_cell
{
    text-align: right;
}

.software_form_list_view .browse,
.software_form_list_view .search
{
    display: inline-block;
    white-space: nowrap;
}

.software_form_list_view .browse_enabled .browse,
.software_form_list_view .advanced_enabled .search
{
    background-image: url({path}{software_directory}/images/translucent_20.png);
    border-radius: 4px 4px 4px 4px;
    -moz-border-radius: 4px 4px 4px 4px;
    -webkit-border-radius: 4px 4px 4px 4px;
    padding: .5em;
}
.software_form_list_view .browse_expanded .browse,
.software_form_list_view .advanced_expanded .search
{
    border-radius: 4px 4px 0px 0px;
    -moz-border-radius: 4px 4px 0px 0px;
    -webkit-border-radius: 4px 4px 0px 0px;
}
.software_form_list_view .advanced_expanded .browse,
.software_form_list_view .browse_expanded .search
{
    background: none !important;
    padding: .5em;
}
.software_form_list_view .browse_expanded .search 
{
    padding-right: 0
}
.software_form_list_view .browse_enabled .search 
{
    padding-bottom: .5em;
}

.browse select.software_select {
    vertical-align: middle;
}
.search a.advanced_toggle {
    vertical-align: inherit !important;
}

.software_form_list_view .browse_toggle,
.software_form_list_view .advanced_toggle
{
    ' . $output_input_properties . '
    text-align: center;
    text-decoration: none;
    outline: 0;
    color: inherit;
}

.software_form_list_view .simple,
.search_results_search .simple,
.catalog_search .simple
{
    position: relative;
}

.software_form_list_view .query,
.search_results_search .query,
.catalog_search .query
{
    padding-left: 1.75em !important;
    padding-right: .5em !important;
    vertical-align: inherit;
}

.software_form_list_view .simple .submit,
.search_results_search .simple .submit,
.catalog_search .simple .submit {
    background: url(\'{path}{software_directory}/images/search.png\') no-repeat scroll center center transparent;
    border: medium none;
    height: 100%;
    width: 2em;
    position: absolute;
    top: 0;
    left: 0;
    display: block;
    -moz-box-shadow: none;
    -webkit-box-shadow: none;
    box-shadow: none;
    border-radius: 0 0 0 0;
    -moz-border-radius: 0 0 0 0;
    -webkit-border-radius: 0 0 0 0;
}
.software_form_list_view .simple .clear,
.software_catalog .simple .clear {
    background: url(\'{path}{software_directory}/images/clear.png\') no-repeat scroll center center transparent;
    border: medium none;
    height: 100%;
    width: 2em;
    position: absolute;
    top: 0;
    right: .1em;
    display: block;
    -moz-box-shadow: none;
    -webkit-box-shadow: none;
    box-shadow: none;
    border-radius: 0 0 0 0;
    -moz-border-radius: 0 0 0 0;
    -webkit-border-radius: 0 0 0 0;
}

.software_form_list_view .simple .submit:hover,
.search_results_search .simple .submit:hover,
.catalog_search .simple .submit:hover,
.software_form_list_view .simple .clear:hover,
.software_catalog .simple .clear:hover
{
    cursor: pointer;
}

.software_form_list_view .browse_filter_container,
.software_form_list_view .advanced
{
    background-image: url({path}{software_directory}/images/translucent_20.png);
}

.software_form_list_view .browse_filter_container
{
    border-radius: 0px 4px 4px 4px;
    -moz-border-radius: 0px 4px 4px 4px;
    -webkit-border-radius: 0px 4px 4px 4px;
    padding: .75em;
}

.software_form_list_view .browse_filter_container table
{
    border-collapse: collapse;
    width: 100%;
}

.software_form_list_view .browse_filter_container td
{
    padding: .5em;
    vertical-align: top;
}

.one_column_mobile .software_form_list_view .browse_filter_container td
{
    width: auto !important;
}
 
.software_form_list_view .browse_filter_container .current
{
    font-weight: bold;
}

.software_form_list_view .advanced
{
    border-radius: 4px 0px 4px 4px;
    -moz-border-radius: 4px 0px 4px 4px;
    -webkit-border-radius: 4px 0px 4px 4px;
    padding: 1em;
}

.folder_view_tree ul {
    list-style-type: none;
    padding-left: 0px;
    margin-bottom: 0.25em;
}

.folder_view_tree ul li ul {
    padding-left: 15px;
}

.folder_view_tree li.folder {
    font-weight: bold;
}

.folder_view_tree li.folder li.page {
    font-weight: normal;
}

.folder_view_tree li.folder li.file {
    font-weight: normal;
}

.search-title {
    font-size: 105%;
}

.search-link {
    font-size: 95%;
    font-style: italic;
}
' .
        $site_wide_properties['base_object']['base_module']['advanced_styling'];
    
    return $output;
}
?>