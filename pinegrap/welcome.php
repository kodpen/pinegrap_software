<?php
/**
 *
 * Pinegrap CMS - Website Platform
 *
 * @author      Kodpen
 * @link        https://kodpen.com
 * @copyright   2016-2021 kodpen Yazılım ve Tasarım
 * @license     https://opensource.org/licenses/mit-license.html MIT License
 *
 */

include ('init.php');
$user = validate_user();
// Add pinegrap notices
include_once ('liveform.class.php');
$liveform = new liveform('welcome');
//   $liveform->mark_error("","error title");
//   $liveform->add_warning("warning title");
//   $liveform->add_notice("notice title");
$query = "SELECT * FROM dashboard";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$dashboard = mysqli_fetch_assoc($result);
$main_weather_location = $dashboard['main_weather_location'];
$weather_app_id = $dashboard['weather_app_id'];
$weather_key = $dashboard['weather_key'];
$weather_secret = $dashboard['weather_secret'];
$notes_widget_data = $dashboard['notes_widget_data'];
$notes_widget_data = quoted_printable_decode(base64_decode($notes_widget_data));
$bg_image = $dashboard['bg_image'];

if ($bg_image == 'bg_purple_and_blue')
{
    $selected_bg_1 = 'selected="selected"';
}
if ($bg_image == 'bg_dark')
{
    $selected_bg_2 = 'selected="selected"';
}
if ($bg_image == 'bg_colorfull')
{
    $selected_bg_3 = 'selected="selected"';
}
if ($bg_image == 'bg_red')
{
    $selected_bg_4 = 'selected="selected"';
}
if ($bg_image == 'bg_green')
{
    $selected_bg_5 = 'selected="selected"';
}
if ($bg_image == 'bg_purple')
{
    $selected_bg_6 = 'selected="selected"';
}
if ($bg_image == 'bg_megatron')
{
    $selected_bg_7 = 'selected="selected"';
}
if ($bg_image == 'bg_moonlit_asteroid')
{
    $selected_bg_8 = 'selected="selected"';
}
if ($bg_image == 'bg_evening_sunshine')
{
    $selected_bg_9 = 'selected="selected"';
}
if ($bg_image == 'bg_witching_house')
{
    $selected_bg_10 = 'selected="selected"';
}
if ($bg_image == 'bg_metapolis')
{
    $selected_bg_11 = 'selected="selected"';
}
if ($bg_image == 'bg_by_design')
{
    $selected_bg_12 = 'selected="selected"';
}
if ($bg_image == 'bg_wiretap')
{
    $selected_bg_13 = 'selected="selected"';
}
if ($bg_image == 'bg_blue_raspberry')
{
    $selected_bg_14 = 'selected="selected"';
}
if ($bg_image == 'bg_frost')
{
    $selected_bg_15 = 'selected="selected"';
}
if ($bg_image == 'image1')
{
    $selected_bg_16 = 'selected="selected"';
}
if ($bg_image == 'image2')
{
    $selected_bg_17 = 'selected="selected"';
}
if ($bg_image == 'image3')
{
    $selected_bg_18 = 'selected="selected"';
}
if ($bg_image == 'image4')
{
    $selected_bg_19 = 'selected="selected"';
}
if ($bg_image == 'image5')
{
    $selected_bg_20 = 'selected="selected"';
}
$widget_themes = $dashboard['widget_themes'];
if ($widget_themes == 'blur_one')
{
    $selected_theme1 = 'selected="selected"';
}
if ($widget_themes == 'blur_two')
{
    $selected_theme2 = 'selected="selected"';
}
if ($widget_themes == 'blur_three')
{
    $selected_theme3 = 'selected="selected"';
}
if ($widget_themes == 'classic')
{
    $selected_theme4 = 'selected="selected"';
}
$order_widgets = $dashboard['order_widgets'];

$widget_color_main_output = '';
if ($widget_themes === 'classic')
{
    $widget_color_main_output = 'black';
}
else
{
    $widget_color_main_output = 'white';
}

if (((defined('SOFTWARE_UPDATE_CHECK') == false) || (SOFTWARE_UPDATE_CHECK == true)) && (SOFTWARE_UPDATE_AVAILABLE == true))
{
    $liveform->add_notice('Software Update Available. <a  class="" href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/software_update.php">Update</a>');
}

// if the user has a user role and the user does not have edit access to any folders and the user does not have access to control panels, then deny access to software welcome screen
if (($user['role'] == 3) && (no_acl_check($user['id']) == false) && ($user['manage_calendars'] == false) && ($user['manage_forms'] == false) && ($user['manage_visitors'] == false) && ($user['manage_contacts'] == false) && ($user['manage_emails'] == false) && ($user['manage_ecommerce'] == false) && ($user['manage_ecommerce_reports'] == false) && (count(get_items_user_can_edit('ad_regions', $user['id'])) == 0))
{
    log_activity("access denied to welcome screen", $_SESSION['sessionusername']);
    output_error('Access denied. <a href="javascript:history.go(-1)">Go back</a>.');
}

switch ($user['role'])
{
    case '0':
        $role = 'administrator';
    break;
    case '1':
        $role = 'designer';
    break;
    case '2':
        $role = 'manager';
    break;
    case '3':
        $role = 'user';
    break;
}

$W_response = '';
if ($weather_app_id != '' || $weather_app_id != ' ')
{

    // Copyright 2019 Oath Inc. Licensed under the terms of the zLib license see https://opensource.org/licenses/Zlib for terms.
    function buildBaseString($baseURI, $method, $params)
    {
        $r = array();
        ksort($params);
        foreach ($params as $key => $value)
        {
            $r[] = "$key=" . rawurlencode($value);
        }
        return $method . "&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $r));
    }

    function buildAuthorizationHeader($oauth)
    {
        $r = 'Authorization: OAuth ';
        $values = array();
        foreach ($oauth as $key => $value)
        {
            $values[] = "$key=\"" . rawurlencode($value) . "\"";
        }
        $r .= implode(', ', $values);
        return $r;
    }

    $W_url = 'https://weather-ydn-yql.media.yahoo.com/forecastrss';
    $app_id = $weather_app_id;
    $consumer_key = $weather_key;
    $consumer_secret = $weather_secret;
    if (language_ruler() === 'en')
    {
        $language_code = 'en-gb';
    }
    elseif (language_ruler() === 'tr')
    {
        $language_code = 'tr-tr';
    }
    $query = array(
        'location' => $main_weather_location,
        'lang' => $language_code,
        'u' => 'c',
        'format' => 'json',
    );

    $oauth = array(
        'oauth_consumer_key' => $consumer_key,
        'oauth_nonce' => uniqid(mt_rand(1, 1000)) ,
        'oauth_signature_method' => 'HMAC-SHA1',
        'oauth_timestamp' => time() ,
        'oauth_version' => '1.0'
    );

    $base_info = buildBaseString($W_url, 'GET', array_merge($query, $oauth));
    $composite_key = rawurlencode($consumer_secret) . '&';
    $oauth_signature = base64_encode(hash_hmac('sha1', $base_info, $composite_key, true));
    $oauth['oauth_signature'] = $oauth_signature;

    $W_header = array(
        buildAuthorizationHeader($oauth) ,
        'X-Yahoo-App-Id: ' . $app_id
    );
    $options = array(
        CURLOPT_HTTPHEADER => $W_header,
        CURLOPT_HEADER => false,
        CURLOPT_URL => $W_url . '?' . http_build_query($query) ,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false
    );

    $ch = curl_init();
    curl_setopt_array($ch, $options);
    $W_response = curl_exec($ch);
    curl_close($ch);

    $Weather_Response = json_decode($W_response);

    $WeatherCity = $Weather_Response
        ->location->city;
    $WeatherRegion = $Weather_Response
        ->location->region;
    $WeatherCountry = $Weather_Response
        ->location->country;
    $WeatherConditionText = $Weather_Response
        ->current_observation
        ->condition->text;
    $WeatherConditionCode = $Weather_Response
        ->current_observation
        ->condition->code;
    $WeatherConditionTemperature = $Weather_Response
        ->current_observation
        ->condition->temperature;
    $weather_image = '<img style="width: 70px;height: auto;" src="backend/core/images/weather_icons/color/' . $WeatherConditionCode . '.png" />';

}
$output_widget_control_button_remove = '';
$output_widget_control_buttons = '';
$output_widget_controls = '';
$output_widget_control_button_add ='';
// if the user is a Manager or above then output widget controls

if ($user['role'] < 2)
{
    $output_widget_controls = '
        $("#widgets").sortable({
            connectWith: "#widgets",
            handle: ".widget-header span,.widget_time_00001 h1",
            stop: function() {
                var widgets = [];
                $.each($("#widgets .widget"), function() {
                    widgets.push($(this).attr("id"));
                });
    
                if (widgets.length === 0) {
                    alert("Please select at lease 1 widget");
                    return false;
                }
                widgets.join(",");
    
                // Use AJAX to get various card info.
                $.ajax({
                    contentType: "application/json",
                    url: "api.php",
                    data: JSON.stringify({
                        action: "update_dashboard_widgets",
                        token: software_token,
                        message_text : "repositioning",
                        widgets: widgets
                    }),
                    type: "POST",
                    success: function(response) {
                        // Check the values in console
                        $status = response.status;
                        if ($status == "success") {
                            toast(response.message);
                        }
                    }
                });
            }
        });
        $( "#widgets" ).draggable({
            connectToSortable: "#widgets",
            helper: "clone",      
            handle: ".widget-header span,.widget_time_00001 h1",
            revert: "invalid",
            zIndex: 100,
            opacity: 0.35,
        });
        
        ';
    $output_widget_control_button_remove = '<a class="widget-deactivate-button material-icons" title="deactive this widget" onclick="deactive_this_widget(this)">close</a>';
    $output_widget_control_buttons = '
        <a onclick="reset_widgets()" class="button"><i class="material-icons">refresh</i>Reset Widgets</a>
        <a class="show_disabled_widgets_button material-icons" title="show/hide deactive widgets" onclick="show_disabled_widgets();">keyboard_arrow_down</a>
        
        <div class="dropdown-wrapper">
            <div class="dropdown">
                <label for="widget_settings_background">Background</label>
                <select id="widget_settings_background" name="widget_settings_background" >
                    <option value="bg_purple_and_blue"      ' . $selected_bg_1 .    '>Purple and Blue (Color)</option>
                    <option value="bg_dark"                 ' . $selected_bg_2 .    '>Dark (Color)</option>
                    <option value="bg_colorfull"            ' . $selected_bg_3 .    '>Colorfull (Color)</option>
                    <option value="bg_red"                  ' . $selected_bg_4 .    '>Red (Color)</option>
                    <option value="bg_green"                ' . $selected_bg_5 .    '>Green (Color)</option>
                    <option value="bg_purple"               ' . $selected_bg_6 .    '>Purple (Color)</option>
                    <option value="bg_megatron"             ' . $selected_bg_7 .    '>Megatron (Color)</option>
                    <option value="bg_moonlit_asteroid"     ' . $selected_bg_8 .    '>Monlit Asteroid (Color)</option>
                    <option value="bg_evening_sunshine"     ' . $selected_bg_9 .    '>Evening Sunshine (Color)</option>
                    <option value="bg_witching_house"       ' . $selected_bg_10 .   '>Witching House (Color)</option>
                    <option value="bg_metapolis"            ' . $selected_bg_11 .   '>Metapolis (Color)</option>
                    <option value="bg_by_design"            ' . $selected_bg_12 .   '>By Design (Color)</option>
                    <option value="bg_wiretap"              ' . $selected_bg_13 .   '>Wiretap (Color)</option>
                    <option value="bg_blue_raspberry"       ' . $selected_bg_14 .   '>Blue Raspberry (Color)</option>
                    <option value="bg_frost"                ' . $selected_bg_15 .   '>Frost (Color)</option>
                    <option value="bg_image1"               ' . $selected_bg_16 .   '>Mountain and sea sunset</option>
                    <option value="bg_image2"               ' . $selected_bg_17 .   '>Space rocket launch</option>
                    <option value="bg_image3"               ' . $selected_bg_18 .   '>Dark streets of europe</option>
                    <option value="bg_image4"               ' . $selected_bg_19 .   '>Palm sunset</option>
                    <option value="bg_image5"               ' . $selected_bg_20 .   '>Jungle bridge</option>
                </select>


                <label for="widget_settings_themes">Widget Theme</label>

                <select id="widget_settings_themes" name="widget_settings_themes" />
                    <option value="blur_one" ' . $selected_theme1 . '>Blur </option>
                    <option value="blur_two" ' . $selected_theme2 . '>Blur 2</option>
                    <option value="blur_three" ' . $selected_theme3 . '>Blur 3</option>
                    <option value="classic" ' . $selected_theme4 . '>Classic</option>
                    
                </select>
            </div>
            <a class="button dropdown-trigger" style="margin-left:.5rem;"><i class="material-icons">widgets</i>Theme Settings</a>
        </div>
        <style>
        body.welcome .dropdown-wrapper{
            display: inline-block;
            position: relative;
        }
        body.welcome .dropdown{background: white;
            display: none;
            flex-direction: column;
            width: 170px;
            padding: 1rem;
            border-top-right-radius: 3px;
            border-top-left-radius: 3px;
            position: absolute;
            z-index: 99;
            bottom: 50%;
            left: 50%;
            transform: translate(-50%,-13%);
            overflow: hidden;
            -webkit-box-shadow: 0px 0px 3px black;
            box-shadow: 0px 0px 3px black;
        }
        body.welcome .dropdown.show{
        display:flex;
        }
        </style>    
        <script>
            $(".dropdown-trigger").click(function(e){
                $(".dropdown").toggleClass("show");
                e.stopPropagation();
            });
            
            $(".dropdown").click(function(e){
                e.stopPropagation();
            });
            
            $(document).click(function(){
                $(".dropdown").removeClass("show");
            });
        </script>
    ';
    $output_widget_control_button_add ='
        <a class="widget-activate-button material-icons" title="active this widget" onclick="active_this_widget(this)">add</a>
    ';
}

$output_last_site_logs_widget_x1 = '';
// if the user is a Manager or above then output site logs
if ($user['role'] < 3)
{

    $query = "SELECT 
                log_id, 
                log_description, 
                log_ip, 
                log_user, 
                log_timestamp 
              FROM log 
              ORDER BY log_timestamp DESC LIMIT 25";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed');
    while ($row = mysqli_fetch_assoc($result))
    {
        $site_logs[] = $row;
    }

    // if there is at least one result to display
    if (!empty($site_logs))
    {
        foreach ($site_logs as $site_log)
        {
            $log_id = $site_log['log_id'];
            $log_timestamp = $site_log['log_timestamp'];
            $log_description = $site_log['log_description'];
            $log_ip = $site_log['log_ip'];
            $log_user = $site_log['log_user'];
            // if the username is blank, then set to UNKNOWN
            if ($log_user == '')
            {
                $log_user = 'UNKNOWN';
            }
            // output style row
            $output_site_logs .= '
            <div class="row">
                <div class="col-1"><i class="material-icons icon" style="color:var(--coloron-settings-color);">text_snippet</i></div>
                <div class="col-3">
                    <div class="header">' . h($log_user) . '</div>
                    <div class="body textarea unlimited">' . convert_text_to_html($log_description) . '</div>
                    <div class="extend left"> IP: ' . h($log_ip) . '</div>
                    <div class="extend right">' . get_relative_time(array('timestamp' => $log_timestamp)) . '</div>

                   
                </div>
            </div>';

        }
    }else{
        $output_site_logs = '<p class="empty-text">There is no Site Log right now.</p>';
    }
    $output_last_site_logs_widget_x1 = '  
    <div id="17" class="widget" title="Last Site Logs Widget | 1x size">
        <div class="widget-wrapper ' . $widget_themes . '">
      
            <div class="widget-header"><span>Site Logs</span>' . $output_widget_control_button_remove . $output_widget_control_button_add . '</div>
            <div class="widget-content">
                ' . $output_site_logs . '
            </div> 
        </div>
    </div> ';
    
}

if ($user['role'] < 1)
{
    $output_notes_widget_x2 = '
<div id="18" class="widget flex-col-2" title="Notes Widget | 2x size">
    <div class="widget_notes_wrapper ' . $widget_themes . '">
    <div class="widget-header"><span>Admin Notes</span>' . $output_widget_control_button_remove . $output_widget_control_button_add . '</div>
    <div class="widget-content">
        <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
        <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

        <div id="editor" contenteditable="true" class="ql-container ql-snow"><span class="ql-editor">' . $notes_widget_data . '</span></div>
        <div class="toolbar">
        <button class="deactive" id="submit_note">Save</button>
        <button class="deactive" id="clear_note">clear</button>
        
        </div>
        <script>

        var once = false;
        var editor = $("#editor");
        var save = $("#submit_note");
        var clear = $("#clear_note");
        editor.click(function(e){
            
            if ( ! once) {
                once = true;
                var toolbarOptions = [
                    
                    [{ "header": [1, 2, 3, 4, 5, 6, false] }],
                    ["bold", "italic", "underline", "strike",{ "color": [] }],        // toggled buttons
                    [{ "align": [] }],
                    ["blockquote", "code-block"],
                    [{ "list": "ordered"}, { "list": "bullet" }],
                    ["clean"]
                  ];
                var quill = new Quill("#editor", {
                modules: {
                    toolbar: toolbarOptions
                },
                theme: "snow"
              });
              
            $(document).keydown(function(event) {

            //19 for Mac Command+S
            if (!( String.fromCharCode(event.which).toLowerCase() == "s" && event.ctrlKey) && !(event.which == 19)) return true;
        
            save.click();
        
            event.preventDefault();
            return false;
            });
            }
        });
        function isEmpty( el ){
            return !$.trim(el.html())
        }
        if (!isEmpty(editor)) {
            clear.removeClass("deactive");
        }
        editor.bind("DOMSubtreeModified",function(){
            save.removeClass("deactive");
            clear.removeClass("deactive");
        });
        clear.click(function(){
            
            if (confirm("Do you really want to clear current note?")) {
                editor.find(".ql-editor").empty();
                clear.addClass("deactive");
                save.click();
            } else {
                return false;
            }
        });
        save.click(function(){
            save.html("saving.. <i id=\u0027save_load\u0027 class=\u0027material-icons\u0027>loop</i>");
        
            // Encode the String
            
            var notes = editor.find(".ql-editor").html();
            
            // Use AJAX to get various card info.
            $.ajax({
            contentType: "application/json",
            url: "api.php",
            data: JSON.stringify({
                action: "update_dashboard_note",
                token: software_token,
                notes: notes
            }),
            type: "POST",
            success: function(response) {
                // Check the values in console
                $status = response.status;
                if ($status == "success") {
                    
                    save.html("save");
                    save.addClass("deactive");
                }
            }
            });
        });
        </script>
    </div></div>
</div>';

}




// get the date today in order to get the timestamp for the beginning of today
$date_today = date('Y-m-d');

// get the timestamp for the beginning of today
$timestamp_today = strtotime($date_today);
// get the timestamp for the beginning of yesterday
$timestamp_yesterday = $timestamp_today - 86400;
// get the timestamp for current time yesterday
$timestamp_current_time_yesterday = time() - 86400;
$timestamp_2_days_ago = $timestamp_yesterday - 86400;
$timestamp_3_days_ago = $timestamp_2_days_ago - 86400;
$timestamp_4_days_ago = $timestamp_3_days_ago - 86400;
$timestamp_5_days_ago = $timestamp_4_days_ago - 86400;
$timestamp_6_days_ago = $timestamp_5_days_ago - 86400;

$timestamp_7_days_ago = $timestamp_today - 604800; // Today to 7 days
$timestamp_14_days_ago = $timestamp_7_days_ago - 604800;
$timestamp_21_days_ago = $timestamp_14_days_ago - 604800;
$timestamp_28_days_ago = $timestamp_21_days_ago - 604800;

$timestamp_30_days_ago = $timestamp_today - 2592000; // Today to 30 days
$timestamp_60_days_ago = $timestamp_30_days_ago - 2592000;
$timestamp_90_days_ago = $timestamp_60_days_ago - 2592000;
$timestamp_120_days_ago = $timestamp_90_days_ago - 2592000;
$timestamp_150_days_ago = $timestamp_120_days_ago - 2592000;
$timestamp_180_days_ago = $timestamp_150_days_ago - 2592000;

//VISITOR//
// get the number of visitors for today
$query = "SELECT COUNT(*) as number_of_visitors_for_today FROM visitors WHERE start_timestamp >= '$timestamp_today'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_visitors_for_today = $row['number_of_visitors_for_today'];

// get the number of visitors for yesterday through the current time
$query = "SELECT COUNT(*) as number_of_visitors_for_yesterday_comparison FROM visitors WHERE (start_timestamp >= '$timestamp_yesterday') AND (start_timestamp <= '$timestamp_current_time_yesterday')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_visitors_for_yesterday_comparison = $row['number_of_visitors_for_yesterday_comparison'];

// get the number of visitors for yesterday
$query = "SELECT COUNT(*) as number_of_visitors_for_yesterday FROM visitors WHERE (start_timestamp >= '$timestamp_yesterday') AND (start_timestamp < '$timestamp_today')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_visitors_for_yesterday = $row['number_of_visitors_for_yesterday'];

// get the number of visitors 2 days ago
$query = "SELECT COUNT(*) as number_of_visitors_for_2_days_ago FROM visitors WHERE (start_timestamp >= '$timestamp_2_days_ago') AND (start_timestamp < '$timestamp_yesterday')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_visitors_for_2_days_ago = $row['number_of_visitors_for_2_days_ago'];

// get the number of visitors 3 days ago
$query = "SELECT COUNT(*) as number_of_visitors_for_3_days_ago FROM visitors WHERE (start_timestamp >= '$timestamp_3_days_ago') AND (start_timestamp < '$timestamp_2_days_ago')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_visitors_for_3_days_ago = $row['number_of_visitors_for_3_days_ago'];

// get the number of visitors 4 days ago
$query = "SELECT COUNT(*) as number_of_visitors_for_4_days_ago FROM visitors WHERE (start_timestamp >= '$timestamp_4_days_ago') AND (start_timestamp < '$timestamp_3_days_ago')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_visitors_for_4_days_ago = $row['number_of_visitors_for_4_days_ago'];

// get the number of visitors for 5 days ago
$query = "SELECT COUNT(*) as number_of_visitors_for_5_days_ago FROM visitors WHERE (start_timestamp >= '$timestamp_5_days_ago') AND (start_timestamp < '$timestamp_4_days_ago')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_visitors_for_5_days_ago = $row['number_of_visitors_for_5_days_ago'];

// get the number of visitors for 6 days ago
$query = "SELECT COUNT(*) as number_of_visitors_for_6_days_ago FROM visitors WHERE (start_timestamp >= '$timestamp_6_days_ago') AND (start_timestamp < '$timestamp_5_days_ago')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_visitors_for_6_days_ago = $row['number_of_visitors_for_6_days_ago'];

// get the number of visitors for 7 days ago
$query = "SELECT COUNT(*) as number_of_visitors_for_7_days_ago FROM visitors WHERE (start_timestamp >= '$timestamp_7_days_ago') AND (start_timestamp < '$timestamp_6_days_ago')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_visitors_for_7_days_ago = $row['number_of_visitors_for_7_days_ago'];

// get the number of visitors for the past 7 days
$query = "SELECT COUNT(*) as number_of_visitors_for_past_7_days FROM visitors WHERE (start_timestamp >= '$timestamp_7_days_ago') AND (start_timestamp < '$timestamp_today')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_visitors_for_past_7_days = $row['number_of_visitors_for_past_7_days'];

// get the number of visitors for the week before the past week
$query = "SELECT COUNT(*) as number_of_visitors_for_week_before_last FROM visitors WHERE (start_timestamp >= '$timestamp_14_days_ago') AND (start_timestamp < '$timestamp_7_days_ago')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_visitors_for_week_before_last = $row['number_of_visitors_for_week_before_last'];

// get the number of visitors for the 3 weeks before
$query = "SELECT COUNT(*) as number_of_visitors_for_3_weeks_before FROM visitors WHERE (start_timestamp >= '$timestamp_21_days_ago') AND (start_timestamp < '$timestamp_14_days_ago')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_visitors_for_3_weeks_before = $row['number_of_visitors_for_3_weeks_before'];

// get the number of visitors for the 4 weeks before
$query = "SELECT COUNT(*) as number_of_visitors_for_4_weeks_before FROM visitors WHERE (start_timestamp >= '$timestamp_28_days_ago') AND (start_timestamp < '$timestamp_21_days_ago')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_visitors_for_4_weeks_before = $row['number_of_visitors_for_4_weeks_before'];

// get the number of visitors for the past 30 days
$query = "SELECT COUNT(*) as number_of_visitors_for_past_30_days FROM visitors WHERE (start_timestamp >= '$timestamp_30_days_ago') AND (start_timestamp < '$timestamp_today')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_visitors_for_past_30_days = $row['number_of_visitors_for_past_30_days'];

// get the number of visitors for the month before the past month
$query = "SELECT COUNT(*) as number_of_visitors_for_month_before_last FROM visitors WHERE (start_timestamp >= '$timestamp_60_days_ago') AND (start_timestamp < '$timestamp_30_days_ago')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_visitors_for_month_before_last = $row['number_of_visitors_for_month_before_last'];

// get the number of visitors for the month before the past 2 month ago
$query = "SELECT COUNT(*) as number_of_visitors_for_2_month_before FROM visitors WHERE (start_timestamp >= '$timestamp_90_days_ago') AND (start_timestamp < '$timestamp_60_days_ago')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_visitors_for_2_month_before = $row['number_of_visitors_for_2_month_before'];

// get the number of visitors for the month before the past 3 month ago
$query = "SELECT COUNT(*) as number_of_visitors_for_3_month_before FROM visitors WHERE (start_timestamp >= '$timestamp_120_days_ago') AND (start_timestamp < '$timestamp_90_days_ago')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_visitors_for_3_month_before = $row['number_of_visitors_for_3_month_before'];

// get the number of visitors for the month before the past 4 month ago
$query = "SELECT COUNT(*) as number_of_visitors_for_4_month_before FROM visitors WHERE (start_timestamp >= '$timestamp_150_days_ago') AND (start_timestamp < '$timestamp_120_days_ago')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_visitors_for_4_month_before = $row['number_of_visitors_for_4_month_before'];

// get the number of visitors for the month before the past 5 month ago
$query = "SELECT COUNT(*) as number_of_visitors_for_5_month_before FROM visitors WHERE (start_timestamp >= '$timestamp_180_days_ago') AND (start_timestamp < '$timestamp_150_days_ago')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_visitors_for_5_month_before = $row['number_of_visitors_for_5_month_before'];

//ORDER//
// get the number of orders for today
$query = "SELECT COUNT(*) as number_of_orders_for_today FROM orders WHERE (orders.order_date >= '$timestamp_today') AND (orders.status = 'complete')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_orders_for_today = $row['number_of_orders_for_today'];

// get the number of orders for yesterday
$query = "SELECT COUNT(*) as number_of_orders_for_yesterday FROM orders WHERE ((orders.order_date >= '$timestamp_yesterday') AND (orders.order_date < '$timestamp_today') AND (orders.status = 'complete'))";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_orders_for_yesterday = $row['number_of_orders_for_yesterday'];

// get the number of orders for 2 days ago
$query = "SELECT COUNT(*) as number_of_orders_for_2_days_ago FROM orders WHERE ((orders.order_date >= '$timestamp_2_days_ago') AND (orders.order_date < '$timestamp_yesterday') AND (orders.status = 'complete'))";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_orders_for_2_days_ago = $row['number_of_orders_for_2_days_ago'];

// get the number of orders for 3 days Ago
$query = "SELECT COUNT(*) as number_of_orders_for_3_days_ago FROM orders WHERE ((orders.order_date >= '$timestamp_3_days_ago') AND (orders.order_date < '$timestamp_2_days_ago') AND (orders.status = 'complete'))";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_orders_for_3_days_ago = $row['number_of_orders_for_3_days_ago'];

// get the number of orders for 4 days Ago
$query = "SELECT COUNT(*) as number_of_orders_for_4_days_ago FROM orders WHERE ((orders.order_date >= '$timestamp_4_days_ago') AND (orders.order_date < '$timestamp_3_days_ago') AND (orders.status = 'complete'))";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_orders_for_4_days_ago = $row['number_of_orders_for_4_days_ago'];

// get the number of orders for 5 days Ago
$query = "SELECT COUNT(*) as number_of_orders_for_5_days_ago FROM orders WHERE ((orders.order_date >= '$timestamp_5_days_ago') AND (orders.order_date < '$timestamp_4_days_ago') AND (orders.status = 'complete'))";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_orders_for_5_days_ago = $row['number_of_orders_for_5_days_ago'];

// get the number of orders for 6 days Ago
$query = "SELECT COUNT(*) as number_of_orders_for_6_days_ago FROM orders WHERE ((orders.order_date >= '$timestamp_6_days_ago') AND (orders.order_date < '$timestamp_5_days_ago') AND (orders.status = 'complete'))";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_orders_for_6_days_ago = $row['number_of_orders_for_6_days_ago'];

// get the number of orders for 7 days Ago
$query = "SELECT COUNT(*) as number_of_orders_for_7_days_ago FROM orders WHERE ((orders.order_date >= '$timestamp_7_days_ago') AND (orders.order_date < '$timestamp_6_days_ago') AND (orders.status = 'complete'))";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_orders_for_7_days_ago = $row['number_of_orders_for_7_days_ago'];

//FORMS//
// get the number of forms for today
$query = "SELECT COUNT(*) as number_of_forms_for_today FROM forms WHERE forms.last_modified_timestamp >= '$timestamp_today'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_forms_for_today = $row['number_of_forms_for_today'];

// get the number of forms for yesterday
$query = "SELECT COUNT(*) as number_of_forms_for_yesterday FROM forms WHERE (forms.last_modified_timestamp >= '$timestamp_yesterday') AND (forms.last_modified_timestamp < '$timestamp_today')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_forms_for_yesterday = $row['number_of_forms_for_yesterday'];

// get the number of forms 2 days ago
$query = "SELECT COUNT(*) as number_of_forms_for_2_days_ago FROM forms WHERE (forms.last_modified_timestamp >= '$timestamp_2_days_ago') AND (forms.last_modified_timestamp < '$timestamp_yesterday')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_forms_for_2_days_ago = $row['number_of_forms_for_2_days_ago'];

// get the number of forms 3 days ago
$query = "SELECT COUNT(*) as number_of_forms_for_3_days_ago FROM forms WHERE (forms.last_modified_timestamp >= '$timestamp_3_days_ago') AND (forms.last_modified_timestamp < '$timestamp_2_days_ago')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_forms_for_3_days_ago = $row['number_of_forms_for_3_days_ago'];

// get the number of forms 4 days ago
$query = "SELECT COUNT(*) as number_of_forms_for_4_days_ago FROM forms WHERE (forms.last_modified_timestamp >= '$timestamp_4_days_ago') AND (forms.last_modified_timestamp < '$timestamp_3_days_ago')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_forms_for_4_days_ago = $row['number_of_forms_for_4_days_ago'];

// get the number of forms for 5 days ago
$query = "SELECT COUNT(*) as number_of_forms_for_5_days_ago FROM forms WHERE (forms.last_modified_timestamp >= '$timestamp_5_days_ago') AND (forms.last_modified_timestamp < '$timestamp_4_days_ago')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_forms_for_5_days_ago = $row['number_of_forms_for_5_days_ago'];

// get the number of forms for 6 days ago
$query = "SELECT COUNT(*) as number_of_forms_for_6_days_ago FROM forms WHERE (forms.last_modified_timestamp >= '$timestamp_6_days_ago') AND (forms.last_modified_timestamp < '$timestamp_5_days_ago')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_forms_for_6_days_ago = $row['number_of_forms_for_6_days_ago'];

// get the number of forms for 7 days ago
$query = "SELECT COUNT(*) as number_of_forms_for_7_days_ago FROM forms WHERE (forms.last_modified_timestamp >= '$timestamp_7_days_ago') AND (forms.last_modified_timestamp < '$timestamp_6_days_ago')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_forms_for_7_days_ago = $row['number_of_forms_for_7_days_ago'];

//CONTACTS//
// get the number of contacts for today
$query = "SELECT COUNT(*) as number_of_contacts_for_today FROM contacts WHERE contacts.timestamp >= '$timestamp_today'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_contacts_for_today = $row['number_of_contacts_for_today'];

// get the number of contacts for yesterday
$query = "SELECT COUNT(*) as number_of_contacts_for_yesterday FROM contacts WHERE (contacts.timestamp >= '$timestamp_yesterday') AND (contacts.timestamp < '$timestamp_today')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_contacts_for_yesterday = $row['number_of_contacts_for_yesterday'];

// get the number of contacts 2 days ago
$query = "SELECT COUNT(*) as number_of_contacts_for_2_days_ago FROM contacts WHERE (contacts.timestamp >= '$timestamp_2_days_ago') AND (contacts.timestamp < '$timestamp_yesterday')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_contacts_for_2_days_ago = $row['number_of_contacts_for_2_days_ago'];

// get the number of contacts 3 days ago
$query = "SELECT COUNT(*) as number_of_contacts_for_3_days_ago FROM contacts WHERE (contacts.timestamp >= '$timestamp_3_days_ago') AND (contacts.timestamp < '$timestamp_2_days_ago')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_contacts_for_3_days_ago = $row['number_of_contacts_for_3_days_ago'];

// get the number of contacts 4 days ago
$query = "SELECT COUNT(*) as number_of_contacts_for_4_days_ago FROM contacts WHERE (contacts.timestamp >= '$timestamp_4_days_ago') AND (contacts.timestamp < '$timestamp_3_days_ago')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_contacts_for_4_days_ago = $row['number_of_contacts_for_4_days_ago'];

// get the number of contacts for 5 days ago
$query = "SELECT COUNT(*) as number_of_contacts_for_5_days_ago FROM contacts WHERE (contacts.timestamp >= '$timestamp_5_days_ago') AND (contacts.timestamp < '$timestamp_4_days_ago')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_contacts_for_5_days_ago = $row['number_of_contacts_for_5_days_ago'];

// get the number of contacts for 6 days ago
$query = "SELECT COUNT(*) as number_of_contacts_for_6_days_ago FROM contacts WHERE (contacts.timestamp >= '$timestamp_6_days_ago') AND (contacts.timestamp < '$timestamp_5_days_ago')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_contacts_for_6_days_ago = $row['number_of_contacts_for_6_days_ago'];

// get the number of contacts for 7 days ago
$query = "SELECT COUNT(*) as number_of_contacts_for_7_days_ago FROM contacts WHERE (contacts.timestamp >= '$timestamp_7_days_ago') AND (contacts.timestamp < '$timestamp_6_days_ago')";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$row = mysqli_fetch_assoc($result);
$number_of_contacts_for_7_days_ago = $row['number_of_contacts_for_7_days_ago'];



if ((ECOMMERCE === true) and (($user['role'] < 3) or USER_MANAGE_ECOMMERCE or USER_MANAGE_ECOMMERCE_REPORTS))
{

    $output_current_site_exchange_rates_widget_x1 = '';
    // if the user has access to the visitors tab, then output visitor information
    if (($user['role'] < 3) || ($user['manage_visitors'] == true))
    {

        // get all of the currency information. Join user_id with username
        $query = "SELECT
        currencies.id,
        currencies.name,
        currencies.base,
        currencies.code,
        currencies.symbol,
        currencies.exchange_rate,
        currencies.created_user_id,
        currencies.created_timestamp,
        currencies.last_modified_user_id,
        currencies.last_modified_timestamp,
        last_modified_user.user_username as last_modified_username
    FROM currencies
    LEFT JOIN user as last_modified_user ON currencies.last_modified_user_id = last_modified_user.user_id
    ORDER BY base DESC,name DESC LIMIT 25";

        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        while ($row = mysqli_fetch_assoc($result))
        {
            $currencies[] = $row;
        }
        if(!empty($currencies)){
            // if there is at least one result to display  
            foreach ($currencies as $currency)
            {
                
                $output_link_url = 'edit_currency.php?id=' . $currency['id'] . '&amp;send_to=' . h(escape_javascript(urlencode(REQUEST_URL)));
                if ($currency['base'] == 1)
                {
                    $output_currencies .= '  
                <div class="row pointer" title="Site Base Currency" onclick="window.location.href=\'' . $output_link_url . '\'">
                    <div class="col-1"><span class="icon">' . $currency['symbol'] . '</span></div>
                    <div class="col-3">
                        <div class="header">' . h($currency['code']) . ' - ' . h($currency['name']) . '</div>
                        <div class="body textarea">' . h($currency['name']) . ' is site base currency</div>
                        
                    </div>
                </div>';
                }
                else
                {
                    $output_currencies .= ' 
                <div class="row pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
                    <div class="col-1"><span class="icon">' . $currency['symbol'] . '</span></div>
                    <div class="col-3">
                        <div class="header">' . h($currency['code']) . ' - ' . h($currency['name']) . '</div>
                        <div class="body ">1.00 ' . $currency['symbol'] . ' = ' . h(number_format((1 / $currency['exchange_rate']) , 5)) . ' ' . BASE_CURRENCY_SYMBOL . '</div>
                        <div class="extend left">' . h($currency['exchange_rate']) . ' ' . $currency['symbol'] . '</div>
                    </div>
                </div>';
                }

            }
        }else{
            $output_currencies = '<p class="empty-text">There is no Site Exchange Rate right now.</p>';
        }

        $output_current_site_exchange_rates_widget_x1 = '
            <div id="16" class="widget" title="Current Site Exchange Rates Widget | 1x size">
                <div class="widget-wrapper  ' . $widget_themes . '">
                    <div class="widget-header"><span>Current Site Exchange Rates</span>' . $output_widget_control_button_remove . $output_widget_control_button_add . '</div>
                    <div class="widget-content">
                            ' . $output_currencies . '
                    </div> 
            </div>
            </div> ';
    }

    $output_ecommerce_summary = '';
    if ((ECOMMERCE === true) and (($user['role'] < 3) or USER_MANAGE_ECOMMERCE or USER_MANAGE_ECOMMERCE_REPORTS))
    {

        $orders = array();
        $query = "SELECT
                orders.id,
                orders.order_number,
                orders.total as total,
                orders.order_date as timestamp
            FROM orders
            WHERE status != 'incomplete'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        // loop through the result in order to prepare array of items
        while ($row = mysqli_fetch_assoc($result))
        {
            $orders[] = $row;
        }

        $order_totals = 0;
        $this_year_totals = 0;
        $this_month_totals = 0;
        $this_week_totals = 0;
        $today_totals = 0;

        $number_of_order = 0;
        $this_year_number_of_order = 0;
        $this_month_number_of_order = 0;
        $this_week_number_of_order = 0;
        $today_number_of_order = 0;

        // loop through the orders, in order to output rows
        foreach ($orders as $order)
        {

            $order_totals = $order_totals + $order['total'];
            $number_of_order++;

            if ((time() - $order['timestamp']) < 31556926)
            { //365days
                $this_year_totals = $this_year_totals + $order['total'];
                $this_year_number_of_order++;
            }
            if ((time() - $order['timestamp']) < 2629743)
            { //30days
                $this_month_totals = $this_month_totals + $order['total'];
                $this_month_number_of_order++;
            }
            if ((time() - $order['timestamp']) < 604800)
            { //7days
                $this_week_totals = $this_week_totals + $order['total'];
                $this_week_number_of_order++;
            }
            if ((time() - $order['timestamp']) < 86400)
            { //24 hours
                $today_totals = $today_totals + $order['total'];
                $today_number_of_order++;
            }

        }

        if ($number_of_order < 2)
        {
            $number_of_order = $number_of_order . ' Order';
        }
        else
        {
            $number_of_order = $number_of_order . ' Orders';
        }
        if ($this_year_number_of_order < 2)
        {
            $this_year_number_of_order = $this_year_number_of_order . ' Order';
        }
        else
        {
            $this_year_number_of_order = $this_year_number_of_order . ' Orders';
        }
        if ($this_month_number_of_order < 2)
        {
            $this_month_number_of_order = $this_month_number_of_order . ' Order';
        }
        else
        {
            $this_month_number_of_order = $this_month_number_of_order . ' Orders';
        }
        if ($this_week_number_of_order < 2)
        {
            $this_week_number_of_order = $this_week_number_of_order . ' Order';
        }
        else
        {
            $this_week_number_of_order = $this_week_number_of_order . ' Orders';
        }
        if ($today_number_of_order < 2)
        {
            $today_number_of_order = $today_number_of_order . ' Order';
        }
        else
        {
            $today_number_of_order = $today_number_of_order . ' Orders';
        }

        $order_total = sprintf("%01.2lf", $order_totals / 100);
        $this_year_total = sprintf("%01.2lf", $this_year_totals / 100);
        $this_month_total = sprintf("%01.2lf", $this_month_totals / 100);
        $this_week_total = sprintf("%01.2lf", $this_week_totals / 100);
        $today_total = sprintf("%01.2lf", $today_totals / 100);

        $order_total = number_format($order_total, 2, ',', '.');
        $this_year_total = number_format($this_year_total, 2, ',', '.');
        $this_month_total = number_format($this_month_total, 2, ',', '.');
        $this_week_total = number_format($this_week_total, 2, ',', '.');
        $today_total = number_format($today_total, 2, ',', '.');

        $output_ecommerce_summary_x1 = '
            <div id="3" class="widget" title="Ecommerce Summary Widget | 1x size">
                <div class="widget-wrapper ' . $widget_themes . '">
                    <div class="widget-header"><span>Ecommerce Summary</span>' . $output_widget_control_button_remove . $output_widget_control_button_add . '</div>
                    <div class="widget-content">
                        <div class="row" style="min-height: 70px;">
                            <div class="col-1"><i class="material-icons icon" style="color:var(--coloron-ecommerce-color);">point_of_sale</i></div>
                            <div class="col-3">
                                <div class="header">' . BASE_CURRENCY_SYMBOL . $today_total . '</div>
                                <div class="body" style="background:transparent;"></div>
                                <div class="extend left">' . $today_number_of_order . '</div>
                                <div class="extend right">Last 24 Hours</div>
                            </div>
                        </div> 
                        <div class="row" style="min-height: 70px;">
                            <div class="col-1"><i class="material-icons icon" style="color:var(--coloron-ecommerce-color);">point_of_sale</i></div>
                            <div class="col-3">
                                <div class="header">' . BASE_CURRENCY_SYMBOL . $this_week_total . '</div>
                                <div class="body" style="background:transparent;"></div>
                                <div class="extend left">' . $this_week_number_of_order . '</div>
                                <div class="extend right">Last 7 Days</div>
                            </div>
                        </div>
                        <div class="row" style="min-height: 70px;">
                            <div class="col-1"><i class="material-icons icon" style="color:var(--coloron-ecommerce-color);">point_of_sale</i></div>
                            <div class="col-3">
                                <div class="header">' . BASE_CURRENCY_SYMBOL . $this_month_total . '</div>
                                <div class="body" style="background:transparent;"></div>
                                <div class="extend left">' . $this_month_number_of_order . '</div>
                                <div class="extend right">Last 30 Days</div>
                            </div>
                        </div>
                        <div class="row" style="min-height: 70px;">
                            <div class="col-1"><i class="material-icons icon" style="color:var(--coloron-ecommerce-color);">point_of_sale</i></div>
                            <div class="col-3">
                                <div class="header">' . BASE_CURRENCY_SYMBOL . $this_year_total . '</div>
                                <div class="body" style="background:transparent;"></div>
                                <div class="extend left">' . $this_year_number_of_order . '</div>
                                <div class="extend right">Last 365 Days</div>
                            </div>
                        </div>
                        <div class="row" style="min-height: 70px;">
                            <div class="col-1"><i class="material-icons icon" style="color:var(--coloron-ecommerce-color);">point_of_sale</i></div>
                            <div class="col-3">
                                <div class="header">' . BASE_CURRENCY_SYMBOL . $order_total . '</div>
                                <div class="body" style="background:transparent;"></div>
                                <div class="extend left">' . $number_of_order . '</div>
                                <div class="extend right">Until Now</div>
                            </div>
                        </div>
                    </div> 
                </div>
            </div>';
    }
}


$output_activity_summary_widget_x2 = '
<div id="2" class="widget flex-col-2" title="Activity Summary Widget | 2x size">
    <div class="widget-wrapper ' . $widget_themes . '">
    <div class="widget-header"><span>Activity Summary</span>' . $output_widget_control_button_remove . $output_widget_control_button_add . '</div>
    <div class="widget-content"><div id="widget_activity_summary_2x"></div>
    </div></div>
</div>';

$output_visitor_summary_x2 = '

<div id="5" class="widget flex-col-2" title="Visitor Summary Widget | 2x size">
    <div class="widget-wrapper ' . $widget_themes . '">
    <div class="widget-header"><span>Visitor Summary</span>' . $output_widget_control_button_remove . $output_widget_control_button_add . '</div>
    <div class="widget-content"><div id="visitor_summary_widget_x2"></div>
    </div></div>
</div>';

$output_visitor_summary_pies_widget_x1 = '

<div id="6" class="widget" title="Visitor Summary Pies Widget | 1x size">
    <div class="widget-wrapper ' . $widget_themes . '">
        <div class="widget-header"><span>Visitor Summary Pies</span>' . $output_widget_control_button_remove . $output_widget_control_button_add . '</div>
        <div class="widget-content"><div id="chart_div_for_month"></div>
        <div id="chart_div_for_week"></div></div>
    </div>
</div>';



$output_greeting_message_text = '';
/* This sets the $time variable to the current hour in the 24 hour clock format */
$time = date("H");
/* Set the $timezone variable to become the current timezone */
$timezone = date("T");
/* If the time is less than 1200 hours, show good morning */
if ($time < "12")
{
    $output_greeting_message_text = "Good morning, ";
}
else
/* If the time is grater than or equal to 1200 hours, but less than 1700 hours, so good afternoon */
if ($time >= "12" && $time < "17")
{
    $output_greeting_message_text = "Good afternoon, ";
}
else
/* Should the time be between or equal to 1700 and 1900 hours, show good evening */
if ($time >= "17" && $time < "19")
{
    $output_greeting_message_text = "Good evening, ";
}
else
/* Finally, show good night if the time is greater than or equal to 1900 hours */
if ($time >= "19")
{
    $output_greeting_message_text = "Good night, ";
}
$output_greeting_message_text = $output_greeting_message_text . $_SESSION['sessionusername'];

$output_welcome_widget_weather = '';
if (isset($WeatherConditionCode))
{
    $output_welcome_widget_weather = '
            <div class="weather">
                <div class="city" title="' . $WeatherCity . ', ' . $WeatherRegion . ', ' . $WeatherCountry . '">
                 ' . $WeatherCity . '
                </div>
                <div class="status">
                    <div class="status_image">' . $weather_image . '</div>
                    <div class="status_text">' . $WeatherConditionText . ' ' . $WeatherConditionTemperature . 'C°</div>
                </div>

            </div>
';
}

$output_welcome_greetings_widget_x4 = '
    <div id="1" class="widget flex-col-4 widget_time_00001" title="Welcome Greetings Widget | 4x size" >
        <h1 title="You are logged in with ' . h($role) . ' role access.">
            ' . $output_greeting_message_text . '
        </h1>
        <div class="wrapper  ' . $widget_themes . '" style="min-height:auto!important;padding-top: 1rem !important;">
            ' . $output_welcome_widget_weather . '
            <div class="time ">
                ' . get_absolute_time(array(
    'timestamp' => time() ,
    'type' => 'date',
    'year' => false,
    'size' => 'long',
    'timezone_type' => 'site'
)) . '<br/>
                <span class="digital-7" style="font-size: xx-large;">' . get_absolute_time(array(
    'timestamp' => time() ,
    'type' => 'time',
    'timezone_type' => 'site'
)) . '</span>
            </div>
        </div>      
    </div>
    ';

// initialize variable for storing the maximum number of items that should appear in the recent update area
$maximum_number_of_items = 1000;
// initialize variable for storing the maximum number of items for special items (e.g. files, designer files, and products)
$special_maximum_number_of_items = 50;
// initialize array for storing items that might appear in the recent updates area
$recent_update_items = array();

// initialize array that will be used for sorting the items for the recent updates area
$recent_update_item_timestamps = array();

$folders_that_user_has_access_to = array();

// if user is a basic user, then get folders that user has access to
if ($user['role'] == 3)
{
    $folders_that_user_has_access_to = get_folders_that_user_has_access_to($user['id']);
}

$pages = array();

// get all pages sorted by last modified descending
$query = "SELECT
        page.page_name as name,
        page.page_timestamp as timestamp,
        user.user_username as username,
        page.page_folder as folder_id,
        page.page_type
    FROM page
    LEFT JOIN user ON page.page_user = user.user_id
    ORDER BY page.page_timestamp DESC";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

// loop through the result in order to prepare array of items
while ($row = mysqli_fetch_assoc($result))
{
    $pages[] = $row;
}

// initialize variable to keep track of how many items have been added
$count = 0;

// loop through the items in order to determine which the user has access to
foreach ($pages as $page)
{
    // if user has access to item then add it to arrays
    if (check_folder_access_in_array($page['folder_id'], $folders_that_user_has_access_to) == true)
    {
        $page['type'] = 'page';
        $recent_update_items[] = $page;
        $recent_update_item_timestamps[] = $page['timestamp'];

        $count++;

        // if the maximum number of items has been added, then we are done, so break out of the loop
        if ($count == $maximum_number_of_items)
        {
            break;
        }
    }
}

$short_links = array();

// Get all short links sorted by last modified descending
$query = "SELECT
        short_links.id,
        short_links.name,
        short_links.destination_type,
        short_links.created_user_id,
        short_links.last_modified_timestamp AS timestamp,
        user.user_username AS username,
        page.page_folder AS folder_id
    FROM short_links
    LEFT JOIN user ON short_links.last_modified_user_id = user.user_id
    LEFT JOIN page ON short_links.page_id = page.page_id
    ORDER BY short_links.last_modified_timestamp DESC";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');
$short_links = mysqli_fetch_items($result);

// initialize variable to keep track of how many items have been added
$count = 0;

// loop through the items in order to determine which the user has access to
foreach ($short_links as $short_link)
{
    // if user has access to item then add it to arrays
    if ((USER_ROLE < 3) || ((($short_link['destination_type'] == 'page') || ($short_link['destination_type'] == 'product_group') || ($short_link['destination_type'] == 'product')) && (check_folder_access_in_array($short_link['folder_id'], $folders_that_user_has_access_to) == true)) || (($short_link['destination_type'] == 'url') && (USER_ID == $short_link['created_user_id'])))
    {
        $short_link['type'] = 'short_link';
        $recent_update_items[] = $short_link;
        $recent_update_item_timestamps[] = $short_link['timestamp'];

        $count++;

        // if the maximum number of items has been added, then we are done, so break out of the loop
        if ($count == $maximum_number_of_items)
        {
            break;
        }
    }
}

$files = array();

// get all files sorted by last modified descending
$query = "SELECT
        files.id,
        files.name,
        files.timestamp,
        user.user_username as username,
        files.folder as folder_id
    FROM files
    LEFT JOIN user ON files.user = user.user_id
    WHERE files.design = '0'
    ORDER BY files.timestamp DESC";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

// loop through the result in order to prepare array of items
while ($row = mysqli_fetch_assoc($result))
{
    $files[] = $row;
}

// initialize variable to keep track of how many items have been added
$count = 0;

// loop through the items in order to determine which the user has access to
foreach ($files as $file)
{
    // if user has access to item then add it to arrays
    if (check_folder_access_in_array($file['folder_id'], $folders_that_user_has_access_to) == true)
    {
        $file['type'] = 'file';
        $recent_update_items[] = $file;
        $recent_update_item_timestamps[] = $file['timestamp'];

        $count++;

        // if the maximum number of items has been added, then we are done, so break out of the loop
        if ($count == $special_maximum_number_of_items)
        {
            break;
        }
    }
}

$folders = array();

// get all folders sorted by last modified descending
$query = "SELECT
        folder.folder_id as id,
        folder.folder_name as name,
        folder.folder_timestamp as timestamp,
        user.user_username as username
    FROM folder
    LEFT JOIN user ON folder.folder_user = user.user_id
    ORDER BY folder.folder_timestamp DESC";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

// loop through the result in order to prepare array of items
while ($row = mysqli_fetch_assoc($result))
{
    $folders[] = $row;
}

// initialize variable to keep track of how many items have been added
$count = 0;

// loop through the items in order to determine which the user has access to
foreach ($folders as $folder)
{
    // if user has access to item then add it to arrays
    if (check_folder_access_in_array($folder['id'], $folders_that_user_has_access_to) == true)
    {
        $folder['type'] = 'folder';
        $recent_update_items[] = $folder;
        $recent_update_item_timestamps[] = $folder['timestamp'];

        $count++;

        // if the maximum number of items has been added, then we are done, so break out of the loop
        if ($count == $maximum_number_of_items)
        {
            break;
        }
    }
}

// if calendars is enabled and the user has access to manage calendars, then get calendars and events
if ((CALENDARS == true) && (($user['role'] < 3) || ($user['manage_calendars'] == true)))
{
    $calendars = array();

    // get all calendars sorted by last modified descending
    $query = "SELECT
            calendars.id,
            calendars.name,
            calendars.last_modified_timestamp as timestamp,
            user.user_username as username
        FROM calendars
        LEFT JOIN user ON calendars.last_modified_user_id = user.user_id
        ORDER BY calendars.last_modified_timestamp DESC";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // loop through the result in order to prepare array of items
    while ($row = mysqli_fetch_assoc($result))
    {
        $calendars[] = $row;
    }

    // initialize variable to keep track of how many items have been added
    $count = 0;

    // loop through the items in order to determine which the user has access to
    foreach ($calendars as $calendar)
    {
        // if user has access to item then add it to arrays
        if (validate_calendar_access($calendar['id']) == true)
        {
            $calendar['type'] = 'calendar';
            $recent_update_items[] = $calendar;
            $recent_update_item_timestamps[] = $calendar['timestamp'];

            $count++;

            // if the maximum number of items has been added, then we are done, so break out of the loop
            if ($count == $maximum_number_of_items)
            {
                break;
            }
        }
    }

    $calendar_events = array();

    // get all calendar events sorted by last modified descending
    $query = "SELECT
            calendar_events.id,
            calendar_events.name,
            calendar_events.last_modified_timestamp as timestamp,
            user.user_username as username
        FROM calendar_events
        LEFT JOIN user ON calendar_events.last_modified_user_id = user.user_id
        ORDER BY calendar_events.last_modified_timestamp DESC";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // loop through the result in order to prepare array of items
    while ($row = mysqli_fetch_assoc($result))
    {
        $calendar_events[] = $row;
    }

    // initialize variable to keep track of how many items have been added
    $count = 0;

    // loop through the items in order to determine which the user has access to
    foreach ($calendar_events as $calendar_event)
    {
        // if user has access to item then add it to arrays
        if (validate_calendar_event_access($calendar_event['id']) == true)
        {
            $calendar_event['type'] = 'calendar_event';
            $recent_update_items[] = $calendar_event;
            $recent_update_item_timestamps[] = $calendar_event['timestamp'];

            $count++;

            // if the maximum number of items has been added, then we are done, so break out of the loop
            if ($count == $maximum_number_of_items)
            {
                break;
            }
        }
    }
}

// if e-commerce is enabled and the user has access to manage e-commerce, then get e-commerce items
if ((ECOMMERCE == true) && (($user['role'] < 3) || ($user['manage_ecommerce'] == true)))
{
    $products = array();

    // get all products sorted by last modified descending
    $query = "SELECT
            products.id,
            products.short_description as name,
            products.timestamp,
            user.user_username as username
        FROM products
        LEFT JOIN user ON products.user = user.user_id
        ORDER BY products.timestamp DESC
        LIMIT $special_maximum_number_of_items";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // loop through the result in order to prepare array of items
    while ($row = mysqli_fetch_assoc($result))
    {
        $products[] = $row;
    }

    // loop through the items in order to add them to arrays
    foreach ($products as $product)
    {
        $product['type'] = 'product';
        $recent_update_items[] = $product;
        $recent_update_item_timestamps[] = $product['timestamp'];
    }

    $product_groups = array();

    // get all product groups sorted by last modified descending
    $query = "SELECT
            product_groups.id,
            product_groups.name,
            product_groups.timestamp,
            user.user_username as username
        FROM product_groups
        LEFT JOIN user ON product_groups.user = user.user_id
        ORDER BY product_groups.timestamp DESC
        LIMIT $maximum_number_of_items";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // loop through the result in order to prepare array of items
    while ($row = mysqli_fetch_assoc($result))
    {
        $product_groups[] = $row;
    }

    // loop through the items in order to add them to arrays
    foreach ($product_groups as $product_group)
    {
        $product_group['type'] = 'product_group';
        $recent_update_items[] = $product_group;
        $recent_update_item_timestamps[] = $product_group['timestamp'];
    }

    $offers = array();

    // get all offers sorted by last modified descending
    $query = "SELECT
            offers.id,
            offers.code as name,
            offers.timestamp,
            user.user_username as username
        FROM offers
        LEFT JOIN user ON offers.user = user.user_id
        ORDER BY offers.timestamp DESC
        LIMIT $maximum_number_of_items";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // loop through the result in order to prepare array of items
    while ($row = mysqli_fetch_assoc($result))
    {
        $offers[] = $row;
    }

    // loop through the items in order to add them to arrays
    foreach ($offers as $offer)
    {
        $offer['type'] = 'offer';
        $recent_update_items[] = $offer;
        $recent_update_item_timestamps[] = $offer['timestamp'];
    }
}

// If ads are enabled, then get them.
if (ADS === true)
{
    $ads = array();

    // get all ads sorted by last modified descending
    $query = "SELECT
            ads.id,
            ads.name,
            ads.last_modified_timestamp as timestamp,
            user.user_username as username,
            ads.ad_region_id
        FROM ads
        LEFT JOIN user ON ads.last_modified_user_id = user.user_id
        ORDER BY ads.last_modified_timestamp DESC";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // loop through the result in order to prepare array of items
    while ($row = mysqli_fetch_assoc($result))
    {
        $ads[] = $row;
    }

    // initialize variable to keep track of how many items have been added
    $count = 0;

    // loop through the items in order to determine which the user has access to
    foreach ($ads as $ad)
    {
        // if user has access to item then add it to arrays
        if (($user['role'] < 3) || (in_array($ad['ad_region_id'], get_items_user_can_edit('ad_regions', $user['id'])) == true))
        {
            $ad['type'] = 'ad';
            $recent_update_items[] = $ad;
            $recent_update_item_timestamps[] = $ad['timestamp'];

            $count++;

            // if the maximum number of items has been added, then we are done, so break out of the loop
            if ($count == $maximum_number_of_items)
            {
                break;
            }
        }
    }
}

$menus = array();

// get all menus sorted by last modified descending
$query = "SELECT
        menus.id,
        menus.name,
        menus.last_modified_timestamp as timestamp,
        user.user_username as username
    FROM menus
    LEFT JOIN user ON menus.last_modified_user_id = user.user_id
    ORDER BY menus.last_modified_timestamp DESC";
$result = mysqli_query(db::$con, $query) or output_error('Query failed.');

// loop through the result in order to prepare array of items
while ($row = mysqli_fetch_assoc($result))
{
    $menus[] = $row;
}

// initialize variable to keep track of how many items have been added
$count = 0;

// loop through the items in order to determine which the user has access to
foreach ($menus as $menu)
{
    // if user has access to item then add it to arrays
    if (($user['role'] < 3) || (in_array($menu['id'], get_items_user_can_edit('menus', $user['id'])) == true))
    {
        $menu['type'] = 'menu';
        $recent_update_items[] = $menu;
        $recent_update_item_timestamps[] = $menu['timestamp'];

        $count++;

        // if the maximum number of items has been added, then we are done, so break out of the loop
        if ($count == $maximum_number_of_items)
        {
            break;
        }
    }
}

// if the user has access to the design tab, then get design items
if ($user['role'] < 2)
{
    $styles = array();

    // get all styles sorted by last modified descending
    $query = "SELECT
            style.style_id as id,
            style.style_name as name,
            style.style_timestamp as timestamp,
            user.user_username as username,
            style.style_type
        FROM style
        LEFT JOIN user ON style.style_user = user.user_id
        ORDER BY style.style_timestamp DESC
        LIMIT $maximum_number_of_items";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // loop through the result in order to prepare array of items
    while ($row = mysqli_fetch_assoc($result))
    {
        $styles[] = $row;
    }

    // loop through the items in order to add them to arrays
    foreach ($styles as $style)
    {
        $style['type'] = 'style';
        $recent_update_items[] = $style;
        $recent_update_item_timestamps[] = $style['timestamp'];
    }

    $common_regions = array();

    // get all common regions sorted by last modified descending
    $query = "SELECT
            cregion.cregion_id as id,
            cregion.cregion_name as name,
            cregion.cregion_timestamp as timestamp,
            user.user_username as username
        FROM cregion
        LEFT JOIN user ON cregion.cregion_user = user.user_id
        WHERE cregion.cregion_designer_type = 'no'
        ORDER BY cregion.cregion_timestamp DESC
        LIMIT $maximum_number_of_items";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // loop through the result in order to prepare array of items
    while ($row = mysqli_fetch_assoc($result))
    {
        $common_regions[] = $row;
    }

    // loop through the items in order to add them to arrays
    foreach ($common_regions as $common_region)
    {
        $common_region['type'] = 'common_region';
        $recent_update_items[] = $common_region;
        $recent_update_item_timestamps[] = $common_region['timestamp'];
    }

    $designer_regions = array();

    // get all designer regions sorted by last modified descending
    $query = "SELECT
            cregion.cregion_id as id,
            cregion.cregion_name as name,
            cregion.cregion_timestamp as timestamp,
            user.user_username as username
        FROM cregion
        LEFT JOIN user ON cregion.cregion_user = user.user_id
        WHERE cregion.cregion_designer_type = 'yes'
        ORDER BY cregion.cregion_timestamp DESC
        LIMIT $maximum_number_of_items";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // loop through the result in order to prepare array of items
    while ($row = mysqli_fetch_assoc($result))
    {
        $designer_regions[] = $row;
    }

    // loop through the items in order to add them to arrays
    foreach ($designer_regions as $designer_region)
    {
        $designer_region['type'] = 'designer_region';
        $recent_update_items[] = $designer_region;
        $recent_update_item_timestamps[] = $designer_region['timestamp'];
    }

    // If ads are enabled, then get ad regions.
    if (ADS === true)
    {
        $ad_regions = array();

        // get all ad regions sorted by last modified descending
        $query = "SELECT
                ad_regions.id,
                ad_regions.name,
                ad_regions.last_modified_timestamp as timestamp,
                user.user_username as username
            FROM ad_regions
            LEFT JOIN user ON ad_regions.last_modified_user_id = user.user_id
            ORDER BY ad_regions.last_modified_timestamp DESC
            LIMIT $maximum_number_of_items";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        // loop through the result in order to prepare array of items
        while ($row = mysqli_fetch_assoc($result))
        {
            $ad_regions[] = $row;
        }

        // loop through the items in order to add them to arrays
        foreach ($ad_regions as $ad_region)
        {
            $ad_region['type'] = 'ad_region';
            $recent_update_items[] = $ad_region;
            $recent_update_item_timestamps[] = $ad_region['timestamp'];
        }
    }

    // if the user is an administrator and dynamic regions are enabled, then get dynamic regions
    if (($user['role'] == 0) && (defined('DYNAMIC_REGIONS') == true) && (DYNAMIC_REGIONS == true))
    {
        $dynamic_regions = array();

        // get all dynamic regions sorted by last modified descending
        $query = "SELECT
                dregion.dregion_id as id,
                dregion.dregion_name as name,
                dregion.dregion_timestamp as timestamp,
                user.user_username as username
            FROM dregion
            LEFT JOIN user ON dregion.dregion_user = user.user_id
            ORDER BY dregion.dregion_timestamp DESC
            LIMIT $maximum_number_of_items";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        // loop through the result in order to prepare array of items
        while ($row = mysqli_fetch_assoc($result))
        {
            $dynamic_regions[] = $row;
        }

        // loop through the items in order to add them to arrays
        foreach ($dynamic_regions as $dynamic_region)
        {
            $dynamic_region['type'] = 'dynamic_region';
            $recent_update_items[] = $dynamic_region;
            $recent_update_item_timestamps[] = $dynamic_region['timestamp'];
        }
    }

    $login_regions = array();

    // get all login regions sorted by last modified descending
    $query = "SELECT
            login_regions.id,
            login_regions.name,
            login_regions.last_modified_timestamp as timestamp,
            user.user_username as username
        FROM login_regions
        LEFT JOIN user ON login_regions.last_modified_user_id = user.user_id
        ORDER BY login_regions.last_modified_timestamp DESC
        LIMIT $maximum_number_of_items";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // loop through the result in order to prepare array of items
    while ($row = mysqli_fetch_assoc($result))
    {
        $login_regions[] = $row;
    }

    // loop through the items in order to add them to arrays
    foreach ($login_regions as $login_region)
    {
        $login_region['type'] = 'login_region';
        $recent_update_items[] = $login_region;
        $recent_update_item_timestamps[] = $login_region['timestamp'];
    }

    $themes = array();

    // get all themes sorted by last modified descending
    $query = "SELECT
            files.id,
            files.name,
            files.timestamp,
            user.user_username as username
        FROM files
        LEFT JOIN user ON files.user = user.user_id
        WHERE (files.type = 'css') AND (files.design = '1')
        ORDER BY files.timestamp DESC
        LIMIT $maximum_number_of_items";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // loop through the result in order to prepare array of items
    while ($row = mysqli_fetch_assoc($result))
    {
        $themes[] = $row;
    }

    // loop through the items in order to add them to arrays
    foreach ($themes as $theme)
    {
        $theme['type'] = 'theme';
        $recent_update_items[] = $theme;
        $recent_update_item_timestamps[] = $theme['timestamp'];
    }

    $design_files = array();

    // get all design files sorted by last modified descending
    // even though themes are considered design files, we are going to exclude this from this query because we don't want them appear twice (as both a theme and a design file)
    $query = "SELECT
            files.id,
            files.name,
            files.timestamp,
            user.user_username as username,
            files.folder as folder_id
        FROM files
        LEFT JOIN user ON files.user = user.user_id
        WHERE (files.design = '1') AND (files.type != 'css')
        ORDER BY files.timestamp DESC
        LIMIT $special_maximum_number_of_items";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // loop through the result in order to prepare array of items
    while ($row = mysqli_fetch_assoc($result))
    {
        $design_files[] = $row;
    }

    // loop through the items in order to add them to arrays
    foreach ($design_files as $design_file)
    {
        $design_file['type'] = 'design_file';
        $recent_update_items[] = $design_file;
        $recent_update_item_timestamps[] = $design_file['timestamp'];
    }
}

// sort the recent update items by the timestamp descending
array_multisort($recent_update_item_timestamps, SORT_DESC, $recent_update_items);

// update array to only contain the maximum number of items
$recent_update_items = array_slice($recent_update_items, 0, $maximum_number_of_items);

$output_recent_update_rows = '';
if (!empty($recent_update_items)) {
    // loop through the recent update items, in order to output rows
    foreach ($recent_update_items as $recent_update_item)
    {
        $type_name = '';
        $output_link_url = '';
    
        // get type name
        switch ($recent_update_item['type'])
        {
            case 'page':
                $type_name = 'Page';
    
                $query_string_from = '';
    
                // if page type is a certain page type, then prepare from
                switch ($recent_update_item['page_type'])
                {
                    case 'view order':
                    case 'custom form':
                    case 'custom form confirmation':
                    case 'calendar event view':
                    case 'catalog detail':
                    case 'shipping address and arrival':
                    case 'shipping method':
                    case 'logout':
                        $query_string_from = '?from=control_panel';
                    break;
                }
    
                $output_link_url = h(escape_javascript(PATH)) . h(escape_javascript(encode_url_path($recent_update_item['name']))) . $query_string_from;
                $type_md_icon = 'desktop_windows';
                $output_recent_update_icon_color_styles = 'color:var(--coloron-pages-color);';
            break;
    
            case 'short_link':
                $type_name = 'Short Link';
                $output_link_url = 'edit_short_link.php?id=' . $recent_update_item['id'];
                $type_md_icon = 'link';
                $output_recent_update_icon_color_styles = 'color:var(--coloron-pages-color);';
            break;
    
            case 'file':
                $type_name = 'File';
                $output_link_url = 'edit_file.php?id=' . $recent_update_item['id'];
                $type_md_icon = 'insert_drive_file';
                $output_recent_update_icon_color_styles = 'color:var(--coloron-files-color);';
            break;
    
            case 'folder':
                $type_name = 'Folder';
                $output_link_url = 'edit_folder.php?id=' . $recent_update_item['id'];
                $type_md_icon = 'folder';
                $output_recent_update_icon_color_styles = 'color:var(--coloron-folders-color);';
            break;
    
            case 'calendar':
                $type_name = 'Calendar';
                $output_link_url = 'calendars.php?id=' . $recent_update_item['id'];
                $type_md_icon = 'insert_invitation';
                $output_recent_update_icon_color_styles = 'color:var(--coloron-calendars-color);';
            break;
    
            case 'calendar_event':
                $type_name = 'Event';
                $output_link_url = 'edit_calendar_event.php?id=' . $recent_update_item['id'];
                $type_md_icon = 'event_available';
                $output_recent_update_icon_color_styles = 'color:var(--coloron-calendars-color);';
            break;
    
            case 'product':
                $type_name = 'Product';
                $output_link_url = 'edit_product.php?id=' . $recent_update_item['id'];
                $type_md_icon = 'watch';
                $output_recent_update_icon_color_styles = 'color:var(--coloron-ecommerce-color);';
            break;
    
            case 'product_group':
                $type_name = 'Product Group';
                $output_link_url = 'edit_product_group.php?id=' . $recent_update_item['id'];
                $type_md_icon = 'view_comfy';
                $output_recent_update_icon_color_styles = 'color:var(--coloron-ecommerce-color);';
            break;
    
            case 'offer':
                $type_name = 'Offer';
                $output_link_url = 'edit_offer.php?id=' . $recent_update_item['id'];
                $type_md_icon = 'local_offer';
                $output_recent_update_icon_color_styles = 'color:var(--coloron-ecommerce-color);';
            break;
    
            case 'ad':
                $type_name = 'Ad';
                $output_link_url = 'edit_ad.php?id=' . $recent_update_item['id'];
                $type_md_icon = 'wb_incandescent';
                $output_recent_update_icon_color_styles = 'color:var(--coloron-ad-color);';
            break;
    
            case 'menu':
                $type_name = 'Menu';
                $output_link_url = 'view_menu_items.php?id=' . $recent_update_item['id'] . '&from=welcome&send_to=' . h(escape_javascript(urlencode(get_request_uri())));
                $type_md_icon = 'filter_list';
                $output_recent_update_icon_color_styles = 'color:var(--coloron-design-color);';
            break;
    
            case 'style':
                $type_name = 'Page Style';
                $output_link_url = 'edit_' . $recent_update_item['style_type'] . '_style.php?id=' . $recent_update_item['id'];
                $type_md_icon = 'dvr';
                $output_recent_update_icon_color_styles = 'color:var(--coloron-design-color);';
            break;
    
            case 'common_region':
                $type_name = 'Common Region';
                $output_link_url = 'edit_common_region.php?id=' . $recent_update_item['id'];
                $type_md_icon = 'wysiwyg';
                $output_recent_update_icon_color_styles = 'color:var(--coloron-design-color);';
            break;
    
            case 'designer_region':
                $type_name = 'Designer Region';
                $output_link_url = 'edit_designer_region.php?id=' . $recent_update_item['id'];
                $type_md_icon = 'view_stream';
                $output_recent_update_icon_color_styles = 'color:var(--coloron-design-color);';
            break;
    
            case 'ad_region':
                $type_name = 'Ad Region';
                $output_link_url = 'edit_ad_region.php?id=' . $recent_update_item['id'];
                $type_md_icon = 'branding_watermark';
                $output_recent_update_icon_color_styles = 'color:var(--coloron-design-color);';
            break;
    
            case 'dynamic_region':
                $type_name = 'Dynamic Region';
                $output_link_url = 'edit_dynamic_region.php?id=' . $recent_update_item['id'];
                $type_md_icon = 'dynamic_form';
                $output_recent_update_icon_color_styles = 'color:var(--coloron-design-color);';
    
            break;
    
            case 'login_region':
                $type_name = 'Login Region';
                $output_link_url = 'edit_login_region.php?id=' . $recent_update_item['id'];
                $type_md_icon = 'login';
                $output_recent_update_icon_color_styles = 'color:var(--coloron-design-color);';
            break;
    
            case 'theme':
                $type_name = 'Theme';
                $output_link_url = 'edit_theme_file.php?id=' . $recent_update_item['id'];
                $type_md_icon = 'style';
                $output_recent_update_icon_color_styles = 'color:var(--coloron-design-color);';
            break;
    
            case 'design_file':
                $type_name = 'Design File';
                $output_link_url = 'edit_design_file.php?id=' . $recent_update_item['id'];
                $type_md_icon = 'description';
                $output_recent_update_icon_color_styles = 'color:var(--coloron-design-color);';
            break;
        }
    
        // if the username is known, then prepend ' by '
        if ($recent_update_item['username'] != '')
        {
            $recent_update_item['username'] = ' - ' . $recent_update_item['username'];
        }
    
        $output_recent_update_rows .= '
            <div class="row pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
                <div class="col-1">
                    <i class="material-icons icon" style="' . $output_recent_update_icon_color_styles . '">' . $type_md_icon . '</i>
                </div>
                <div class="col-3">
                    <div class="header translateable">' . h($type_name) . '</div>
                    <div class="body">' . h($recent_update_item['name']) . '</div>
                    <div class="extend right" title=" Create time - user">' . get_relative_time(array('timestamp' => $recent_update_item['timestamp'])) . h($recent_update_item['username']) . '</div>
                </div>
            </div>';
    }

}else{
    $output_recent_update_rows = '<p class="empty-text">There is no Recent update right now.</p>';
} 
$output_recent_updates_widget_x1 = '
    <div id="7" class="widget " title="Recent Updates Widget | 1x size">
        <div class="widget_wrapper  ' . $widget_themes . '">
        <div class="widget-header"><span>Recent Updates</span>' . $output_widget_control_button_remove . $output_widget_control_button_add . '</div>
        <div class="widget-content">
             ' . $output_recent_update_rows . '
            </div>
        </div> 
    </div>';










$output_contacts_widget_x1 = '';

// if the user has access to contacts, then get contacts
if (($user['role'] < 3) || ($user['manage_contacts'] == true))
{
    $contacts = array();

    // if the user is above a user role, then get the contacts in a certain way (for performance reasons)
    if ($user['role'] < 3)
    {
        $query = "SELECT
                contacts.id,
                contacts.first_name,
                contacts.last_name,
                contacts.email_address,
                contacts.timestamp,
                user.user_username as username
            FROM contacts
            LEFT JOIN user ON contacts.user = user.user_id
            ORDER BY contacts.timestamp DESC
            LIMIT 25";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        // loop through the result in order to prepare array of items
        while ($row = mysqli_fetch_assoc($result))
        {
            $contacts[] = $row;
        }

        // else the user has a user role, so get the contacts in a different way
        
    }
    else
    {
        $contact_groups = get_items_user_can_edit('contact_groups', $user['id']);

        // if the user has access to at least one contact group, then get contacts
        if (count($contact_groups) > 0)
        {
            $sql_where = '';

            // loop through the contact groups in order to prepare where SQL statement
            foreach ($contact_groups as $contact_group)
            {
                // if there is already where content then add an or
                if ($sql_where != '')
                {
                    $sql_where .= ' OR ';
                }

                // add condition for this contact group
                $sql_where .= '(contacts_contact_groups_xref.contact_group_id = ' . $contact_group . ')';
            }

            $query = "SELECT
                    contacts.id,
                    contacts.first_name,
                    contacts.last_name,
                    contacts.email_address,
                    contacts.timestamp,
                    user.user_username as username
                FROM contacts
                LEFT JOIN user ON contacts.user = user.user_id
                LEFT JOIN contacts_contact_groups_xref ON contacts.id = contacts_contact_groups_xref.contact_id
                WHERE $sql_where
                GROUP BY contacts.id
                ORDER BY contacts.timestamp DESC
                LIMIT 25";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

            // loop through the result in order to prepare array of items
            while ($row = mysqli_fetch_assoc($result))
            {
                $contacts[] = $row;
            }
        }
    }

    $output_contact_rows = '';
    if(!empty($contacts)){
        // loop through the contacts, in order to output rows
        foreach ($contacts as $contact)
        {
            $output_link_url = 'edit_contact.php?id=' . $contact['id'];

            $name = '';

            // if there is a first name, then add it to the name
            if ($contact['first_name'] != '')
            {
                $name .= $contact['first_name'];
            }

            // if there is a last name, then add it to the name
            if ($contact['last_name'] != '')
            {
                // if the name is not blank, then add space
                if ($name != '')
                {
                    $name .= ' ';
                }

                $name .= $contact['last_name'];
            }

            // if the username is known, then prepend ' by '
            if ($contact['username'] != '')
            {
                $contact['username'] = ' - ' . $contact['username'];
            }

            $output_contact_rows .= '<div onclick="window.location.href=\'' . $output_link_url . '\'" class="row pointer">
                    <div class="col-1"><i class="material-icons icon" style="color:var(--coloron-contacts-color);">contacts</i></div>
                    <div class="col-3">
                        <div class="header">' . h($name) . '</div>
                        <div class="body">' . h($contact['email_address']) . '</div>
                        <div class="extend right">' . get_relative_time(array('timestamp' => $contact['timestamp'])) . h($contact['username']) . '</div>
                    </div>
                </div>';
        }
    }else{
        $output_contact_rows = '<p class="empty-text">There is no Contacts right now.</p>';
    }
    $output_contacts_widget_x1 = '
        <div id="10" class="widget" title="Contacts Widget | 1x size">
            <div class="widget-wrapper ' . $widget_themes . '">
                <div class="widget-header"><span>Contacts</span>' . $output_widget_control_button_remove . $output_widget_control_button_add . '</div>
                <div class="widget-content">' . $output_contact_rows . '</div>
            </div>
        </div>';
}

$output_users_widget_x1 = '';
$output_who_is_online_widget_x1 = '';
// if the user is a manager or above, then get users
if ($user['role'] < 3)
{

    $online_users = array();

    $query = "SELECT
        user.user_id as id,
        user.user_role as user_role,
        user.user_username as username,
        user.user_email as email_address,
        user.user_online_timestamp as user_online_timestamp,
        last_modified_user.user_username as last_modified_username
    FROM user
    LEFT JOIN user as last_modified_user ON user.user_user = last_modified_user.user_id
    ORDER BY user.user_online_timestamp DESC";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // loop through the result in order to prepare array of items
    while ($row = mysqli_fetch_assoc($result))
    {
        $online_users[] = $row;
    }

    $output_who_is_online_rows = '';

    if(!empty($online_users)){
        // loop through the users, in order to output rows
        // we are using the variable name $recent_user instead of $user, because $user is a reserved variable for storing user information
        // there was a bug where the start page link in the header would not appear because were using the $user variable
        foreach ($online_users as $online_user)
        {

            switch ($online_user['user_role'])
            {
                case '0':
                    
                    $user_role = 'Administrator';
                break;
                case '1':
                  
                    $user_role = 'Designer';
                break;
                case '2':
                   
                    $user_role = 'Manager';
                break;
                case '3':
                    
                    $user_role = 'User';
                break;
            }

            //now timestamp - user_online_timestamp give use how second ago user online who is online function in function.php and check once at 50 sec.
            $user_last_online_date = time() - $online_user['user_online_timestamp'];

            //output last online date.
            if ((time() - $online_user['user_online_timestamp']) < 50)
            {
                $output_user_last_online_date = 'Online';
            }
            else
            {
                $output_user_last_online_date = (get_relative_time(array(
                    'timestamp' => $online_user['user_online_timestamp']
                )));
            }

            //if user last online timestamp is 0 this mean user didnt online before so set unknown
            if ($online_user['user_online_timestamp'] == 0)
            {
                $output_user_last_online_date = '[Unknown]';
            }

            $online_status = '';
            //if the user has been online for the last 5 minutes may still online who knows :D
            // so we output green online status
            if ($user_last_online_date < 299)
            {
                
                $online_status = '<i title="Online" style="color: #188c18" class="material-icons icon">wifi_tethering</i>';
            }
            else if (($user_last_online_date > 300) && ($user_last_online_date < 1200))
            {
                //if the user has not been online for the last 5 minutes and If not more than 20 minutes than is status away
                
                $online_status = '<i title="Away" style="color:#f59300;" class="material-icons icon">wifi_tethering_error_rounded</i>';
            }
            else
            {
                //else user is offline
                
                $online_status = '<i title="Offline" style="" class="material-icons  icon">wifi_tethering_off</i>';
            }

            $output_link_url = 'edit_user.php?id=' . $online_user['id'];

            //if user last online timestamp is equal or biggert than 1 this mean we know last online status so output username
            if ($online_user['user_online_timestamp'] >= 1)
            {
                $output_who_is_online_rows .= '
                <div onclick="window.location.href=\'' . $output_link_url . '\'" class="row pointer">
                    <div class="col-1">
                        ' . $online_status . '
                    </div>    
                    <div class="col-3">
                        <div class="header translateable"> ' . $user_role . '</div>
                        <div class="body">' . h($online_user['username']) . '</div>
                        <div class="extend right translateable-2">' . $output_user_last_online_date . '</div>
                    </div>
                </div>';
            }
        }
    }else{
        $output_who_is_online_rows = '<p class="empty-text">There is no Online User right now.</p>';
    }
    $output_who_is_online_widget_x1 = '
        <div id="4" class="widget" title="Who Is Online Widget | 1x size">
        <div class="widget-wrapper ' . $widget_themes . '">
        <div class="widget-header"><span>Who Is Online</span>' . $output_widget_control_button_remove . $output_widget_control_button_add . '</div>
        <div class="widget-content">' . $output_who_is_online_rows . '</div>
        
        </div>
        </div>';






    $sql_where = "";
    // if the user is not an administrator, then prepare where condition for role
    if ($user['role'] > 0)
    {
        $sql_where = "WHERE user.user_role > '" . $user['role'] . "'";
    }
    $users = array();
    $query = "SELECT
            user.user_id as id,
            user.user_username as username,
            user.user_email as email_address,
            user.user_timestamp as timestamp,
            last_modified_user.user_username as last_modified_username
        FROM user
        LEFT JOIN user as last_modified_user ON user.user_user = last_modified_user.user_id
        $sql_where
        ORDER BY user.user_timestamp DESC
        LIMIT 25";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // loop through the result in order to prepare array of items
    while ($row = mysqli_fetch_assoc($result))
    {
        $users[] = $row;
    }

    $output_user_rows = '';
    if(!empty($users)){
        // loop through the users, in order to output rows
        // we are using the variable name $recent_user instead of $user, because $user is a reserved variable for storing user information
        // there was a bug where the start page link in the header would not appear because were using the $user variable
        foreach ($users as $recent_user)
        {
            $output_link_url = 'edit_user.php?id=' . $recent_user['id'];

            // if the last modified username is known, then prepend ' by '
            if ($recent_user['last_modified_username'] != '')
            {
                $recent_user['last_modified_username'] = ' - ' . $recent_user['last_modified_username'];
            }

            $output_user_rows .= '
            <div onclick="window.location.href=\'' . $output_link_url . '\'" class="row pointer">
                <div class="col-1"><i class="material-icons icon" style="color:var(--coloron-users-color);">account_circle</i></div>
                <div class="col-3">
                    <div class="header">' . h($recent_user['username']) . '</div>
                    <div class="body">' . h($recent_user['email_address']) . '</div>
                    <div class="extend right">' . get_relative_time(array('timestamp' => $recent_user['timestamp'])) . h($recent_user['last_modified_username']) . '</div>
                </div>       
            </div>';
        }
    }else{
        $output_user_rows = '<p class="empty-text">There is no User right now.</p>';
    }
    
    $output_users_widget_x1 = '
        <div id="11" class="widget" title="Users Widget | 1x size">
        <div class="widget-wrapper ' . $widget_themes . '">
        <div class="widget-header"><span>Users</span>' . $output_widget_control_button_remove . $output_widget_control_button_add . '</div>
        <div class="widget-content">' . $output_user_rows . '</div>
        
        </div>
        </div>';
}

$output_submitted_forms_widget_x1 = '';

// if the user has access to manage forms, then get submitted forms
if (($user['role'] < 3) || ($user['manage_forms'] == true))
{
    $submitted_forms = array();

    // if the user is above a user role, then get the submitted forms in a certain way
    if ($user['role'] < 3)
    {
        $query = "SELECT
                forms.id,
                custom_form_pages.form_name,
                user.user_username as username,
                contacts.first_name,
                contacts.last_name,
                forms.last_modified_timestamp as timestamp,
                last_modified_user.user_username as last_modified_username
            FROM forms
            LEFT JOIN custom_form_pages ON forms.page_id = custom_form_pages.page_id
            LEFT JOIN user ON forms.user_id = user.user_id
            LEFT JOIN contacts ON forms.contact_id = contacts.id
            LEFT JOIN user as last_modified_user ON forms.last_modified_user_id = last_modified_user.user_id
            ORDER BY forms.last_modified_timestamp DESC
            LIMIT 25";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        // loop through the result in order to prepare array of items
        while ($row = mysqli_fetch_assoc($result))
        {
            $submitted_forms[] = $row;
        }

        // else the user has a user role, so get the submitted forms in a different way
        
    }
    else
    {
        $custom_forms = array();

        // get all custom forms in order to determine which the user has access to
        $query = "SELECT
                page_id,
                page_folder as folder_id
            FROM page
            WHERE page_type = 'custom form'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        // loop through the result in order to prepare array of items
        while ($row = mysqli_fetch_assoc($result))
        {
            // if user has access to the custom form then add it to array
            if (check_folder_access_in_array($row['folder_id'], $folders_that_user_has_access_to) == true)
            {
                $custom_forms[] = $row;
            }
        }

        // if the user has access to at least one custom form, then get submitted forms
        if (count($custom_forms) > 0)
        {
            $sql_where = "";

            // loop through the custom forms in order to prepare where SQL conditions
            foreach ($custom_forms as $custom_form)
            {
                // if there is already where content then add an or
                if ($sql_where != "")
                {
                    $sql_where .= " OR ";
                }

                // add condition for this custom form
                $sql_where .= "(forms.page_id = '" . $custom_form['page_id'] . "')";
            }

            $query = "SELECT
                    forms.id,
                    custom_form_pages.form_name,
                    user.user_username as username,
                    forms.last_modified_timestamp as timestamp,
                    last_modified_user.user_username as last_modified_username
                FROM forms
                LEFT JOIN custom_form_pages ON forms.page_id = custom_form_pages.page_id
                LEFT JOIN user ON forms.user_id = user.user_id
                LEFT JOIN user as last_modified_user ON forms.last_modified_user_id = last_modified_user.user_id
                WHERE $sql_where
                ORDER BY forms.last_modified_timestamp DESC
                LIMIT 25";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

            // loop through the result in order to prepare array of items
            while ($row = mysqli_fetch_assoc($result))
            {
                $submitted_forms[] = $row;
            }
        }
    }

    $output_submitted_form_rows = '';


    if(!empty($submitted_forms)){
        // loop through the submitted forms, in order to output rows
        foreach ($submitted_forms as $submitted_form)
        {
            $output_link_url = 'edit_submitted_form.php?id=' . $submitted_form['id'];

            $name = '';

            // if there is a username then use that for the name
            if ($submitted_form['username'] != '')
            {
                $name = $submitted_form['username'];
            }

            // if the name is blank, then set it to placeholder
            if ($name == '')
            {
                $name = '[Unknown]';
            }

            // if the last modified username is not known, then set to [Unknown]
            if ($submitted_form['last_modified_username'] != '')
            {
                $submitted_form['last_modified_username'] = ' - ' . $submitted_form['last_modified_username'];
            }

            $output_submitted_form_rows .= '
                <div onclick="window.location.href=\'' . $output_link_url . '\'" class="row pointer">
                <div class="col-1"><i class="material-icons icon" style="color:var(--coloron-forms-color);">list_alt</i></div>
                <div class="col-3">
                    <div class="header">' . h($name) . '</div>
                    <div class="body">' . h($submitted_form['form_name']) . '</div>
                    <div class="extend right">' . get_relative_time(array('timestamp' => $submitted_form['timestamp'])) . h($submitted_form['last_modified_username']) . '</div>
                </div>
            </div>';
        }
    }else{
        $output_submitted_form_rows = '<p class="empty-text">There is no Form right now.</p>';
    }
    // if there is at least one submitted form, then output submitted form area
    if (count($submitted_forms) > 0)
    {
        $output_submitted_forms_widget_x1 = '
        <div id="13" class="widget" title="Forms Widget | 1x size">
            <div class="widget-wrapper ' . $widget_themes . '">
                <div class="widget-header"><span>Forms</span>' . $output_widget_control_button_remove . $output_widget_control_button_add . '</div>
                <div class="widget-content">' . $output_submitted_form_rows . '</div>
            </div>
        </div>';

    }
}

$output_carts_widget_x1 = '';
$output_orders_widget_x1 = '';

// if e-commerce is enabled and the user has access to manage e-commerce, then get carts and orders
if ((ECOMMERCE == true) && (($user['role'] < 3) || ($user['manage_ecommerce'] == true)))
{
    $carts = array();

    $query = "SELECT
            orders.id,
            user.user_username as username,
            contacts.first_name,
            contacts.last_name,
            orders.reference_code,
            orders.order_date as timestamp
        FROM orders
        LEFT JOIN user ON orders.user_id = user.user_id
        LEFT JOIN contacts ON orders.contact_id = contacts.id
        WHERE status = 'incomplete'
        ORDER BY orders.order_date DESC
        LIMIT 25";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // loop through the result in order to prepare array of items
    while ($row = mysqli_fetch_assoc($result))
    {
        $carts[] = $row;
    }

    $output_cart_rows = '';
    if (!empty($orders)) {
        // loop through the carts, in order to output rows
        foreach ($carts as $cart)
        {
            $output_link_url = 'view_order.php?id=' . $cart['id'];

            $name = '';

            // if there is a username then use that for the name
            if ($cart['username'] != '')
            {
                $name = $cart['username'];

                // else there is not a username, so use contact name
                
            }
            else
            {
                // if there is a first name, then add it to the name
                if ($cart['first_name'] != '')
                {
                    $name .= $cart['first_name'];
                }

                // if there is a last name, then add it to the name
                if ($cart['last_name'] != '')
                {
                    // if the name is not blank, then add space
                    if ($name != '')
                    {
                        $name .= ' ';
                    }

                    $name .= $cart['last_name'];
                }
            }

            // if the name is blank, then set it to placeholder
            if ($name == '')
            {
                $name = '[Visitor]';
            }

            // get total for order
            $query = "SELECT
                    SUM(
                        (order_items.price * CAST(order_items.quantity AS signed))
                        + (order_items.tax * CAST(order_items.quantity AS signed))
                        + CAST((order_items.shipping * order_items.quantity) AS signed)
                    ) as total
                FROM order_items
                WHERE order_items.order_id = '" . $cart['id'] . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_assoc($result);
            $total = $row['total'];

            $output_cart_rows .= '
                <div class="row pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
                    <div class="col-1">
                        <i class="material-icons icon" style="color:#ff7800;">shopping_cart</i>
                    </div>
                    <div class="col-3">
                        <div class="header ">' . prepare_amount($total / 100) . '</div>
                        <div class="body">' . h($name) . '</div>
                        <div class="extend right" title=" Create time - user">' . get_relative_time(array('timestamp' => $cart['timestamp'])) . '</div>
                        <div class="extend left">' . $cart['reference_code'] . '</div>
                    </div>
                </div>';
        }
    }else{
        $output_cart_rows = '<p class="empty-text">There is no Shopping Card right now.</p>';
    }

  


    $output_carts_widget_x1 = '
        <div id="9" class="widget" title="Shopping Carts Widget | 1x size">
        <div class="widget-wrapper ' . $widget_themes . '">
        <div class="widget-header"><span>Shopping Carts</span>' . $output_widget_control_button_remove . $output_widget_control_button_add . '</div>
        <div class="widget-content">' . $output_cart_rows . '</div>
        
        </div>
        </div>';
    

    $orders = array();

    $query = "SELECT
            orders.id ,
            orders.order_number,
            user.user_username as username,
            contacts.first_name,
            contacts.last_name,
            orders.tracking_code,
            orders.total,
            orders.order_date as timestamp
        FROM orders
        LEFT JOIN user ON orders.user_id = user.user_id
        LEFT JOIN contacts ON orders.contact_id = contacts.id
        LEFT JOIN ship_tos ON orders.id = ship_tos.order_id
        WHERE status != 'incomplete'
        ORDER BY orders.order_date DESC
        LIMIT 25";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // loop through the result in order to prepare array of items
    while ($row = mysqli_fetch_assoc($result))
    {
        $orders[] = $row;
    }

    $output_order_rows = '';


    if (!empty($orders)) {

        // loop through the orders, in order to output rows
        foreach ($orders as $order)
        {
            $output_link_url = 'view_order.php?id=' . $order['id'];

            $name = '';

            // if there is a username then use that for the name
            if ($order['username'] != '')
            {
                $name = $order['username'];

                // else there is not a username, so use contact name
                
            }
            else
            {
                // if there is a first name, then add it to the name
                if ($order['first_name'] != '')
                {
                    $name .= $order['first_name'];
                }

                // if there is a last name, then add it to the name
                if ($order['last_name'] != '')
                {
                    // if the name is not blank, then add space
                    if ($name != '')
                    {
                        $name .= ' ';
                    }

                    $name .= $order['last_name'];
                }
            }

            // if the name is blank, then set it to placeholder
            if ($name == '')
            {
                $name = '[Visitor]';
            }

            $id = $order['id'];
            $shipping_tracking_icon = '';
            if (ECOMMERCE_SHIPPING == true)
            {
                $shipping_query = "SELECT 
                number,
                id
                    FROM shipping_tracking_numbers
                    WHERE order_id = '" . $id . "'";
                $ship_result = mysqli_query(db::$con, $shipping_query) or output_error('Query failed.');
                $row_ship = mysqli_fetch_assoc($ship_result);

                $shipping_tracking_numbers = $row_ship['number'];
                $shipping_tracking_id = $row_ship['id'];



                    if ($shipping_tracking_numbers)
                    {
                        $shipping_tracking_icon = '<div class="icon " title="Shipped" style="color:#468c00;"><i class="material-icons">local_shipping</i></div>';
                    }
                    else
                    {
                        $shipping_tracking_icon = '<div class="icon " title="Not shipped yet" style="color: #7d7d7da3;" ><i class="material-icons">local_shipping</i></div>';
                    }
            }
            $output_order_rows .= '
            <div class="row pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
                <div class="col-1">
                    <i class="material-icons icon">' . $shipping_tracking_icon . '</i>
                </div>
                <div class="col-3">
                    <div class="header ">' . prepare_amount($order['total'] / 100) . '</div>
                    <div class="body">' . h($name) . '</div>
                    <div class="extend right" title=" Create time - user">' . get_relative_time(array('timestamp' => $order['timestamp'])) . '</div>
                    <div class="extend left">' . $order['order_number'] . '</div>
                </div>
            </div>';
        }
    }else{
        $output_order_rows = '<p class="empty-text">There is no order right now.</p>';
    }

    $output_orders_widget_x1 = '
            <div id="8" class="widget" title="Orders Widget | 1x size">
                <div class="widget-wrapper  ' . $widget_themes . '">
                    <div class="widget-header"><span>Orders</span>' . $output_widget_control_button_remove . $output_widget_control_button_add . '</div>
                    <div class="widget-content">' . $output_order_rows . '</div>
                </div>
            </div>';
   
    $out_of_stock_products = array();

    $query = "SELECT
                products.id as id,
                products.name as name,
                products.enabled,
				products.image_name  as image_name,
                products.inventory as inventory,
                products.inventory_quantity as inventory_quantity,
                products.short_description as short_description,
                products.price as price,
                products.taxable as taxable,
                products.form_name as form_name,
                products.seo_score as seo_score,
                user.user_username as user,
                products.out_of_stock_timestamp as timestamp
            FROM products
            LEFT JOIN user ON products.user = user.user_id
            WHERE out_of_stock = '1'
            ORDER BY timestamp DESC
            LIMIT 25";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // loop through the result in order to prepare array of items
    while ($row = mysqli_fetch_assoc($result))
    {
        $out_of_stock_products[] = $row;
    }

    $output_out_of_stock_product_rows = '';
    if (!empty($out_of_stock_products)) {
        // loop through the orders, in order to output rows
        foreach ($out_of_stock_products as $out_of_stock_product)
        {
            $output_link_url = 'edit_product.php?id=' . $out_of_stock_product['id'];

            $output_out_of_stock_product_rows .= '
                <div class="row pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
                    <div class="col-1">
                        <img class="lazy image src="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/images/loading.gif"   data-src="' . PATH . $out_of_stock_product['image_name'] . '" />
                    </div>
                    <div class="col-3">
                        <div class="header">' . $out_of_stock_product['name'] . '</div>
                        <div class="body">' . $out_of_stock_product['short_description'] . '</div>
                       
                        <div class="extend right" title=" Create time - user">' . get_relative_time(array('timestamp' => $out_of_stock_product['timestamp'])) . '</div>
                        <div class="extend left">' . prepare_amount($out_of_stock_product['price'] / 100) . '</div>
                        
                    </div>
                </div>
                ';
        }


    }else{
        $output_out_of_stock_product_rows = '<p class="empty-text">There is no out of stock product right now.</p>';
    }  

    $output_out_of_stock_x1 = '
    <div id="12" class="widget " title="Out of Stock Products Widget | 1x size">
        <div class="widget_wrapper  ' . $widget_themes . '">
        <div class="widget-header"><span>Out of Stock Products</span>' . $output_widget_control_button_remove . $output_widget_control_button_add . '</div>
        <div class="widget-content">
             ' . $output_out_of_stock_product_rows . ' 
            </div>
        </div> 
    </div>
    <script>
        $(function () {
            $("img.lazy").Lazy();
        });
    </script>';
}

$output_weather_forecast_widget_x1 = '';

$Forecast_Counter = 0;
if(!empty($Weather_Response->forecasts)){
    foreach ($Weather_Response->forecasts as $forecast)
    {
        if (language_ruler() === 'tr')
        {
            switch ($forecast->day)
            {
                case 'Mon':
                    $output_day = 'Pazartesi';
                break;
                case 'Tue':
                    $output_day = 'Salı';
                break;
                case 'Wed':
                    $output_day = 'Çarşamba';
                break;
                case 'Thu':
                    $output_day = 'Perşembe';
                break;
                case 'Fri':
                    $output_day = 'Cuma';
                break;
                case 'Sat':
                    $output_day = 'Cumartesi';
                break;
                case 'Sun':
                    $output_day = 'Pazar';
                break;
            }

            setlocale(LC_ALL, 'tr_TR.UTF-8');
        }

        $ForecastConditionCode = $forecast->code;
        $ForecastDate = $forecast->date;
        $ForecastLow = $forecast->low;
        $ForecastHigh = $forecast->high;
        $forecast_image = '<img class="image" src="backend/core/images/weather_icons/color/' . $ForecastConditionCode . '.png" />';
        $output_weather_forecast .= '
        <div class="row">
            <div class="col-1">' . $forecast_image . '</div>
            <div class="col-3">
                <div class="header">' . $ForecastHigh . 'C° / ' . $ForecastLow . 'C°</div>
                <div class="body textarea">' . $forecast->text . ' </div>
                <div class="extend right">' . strftime('%B %e', $ForecastDate) . ', ' . $output_day . '</div>
                
            
            </div>
        </div>';

        $Forecast_Counter++;

        if($Forecast_Counter > 0){

            $output_weather_forecast_counter = '/ ' . $Forecast_Counter . ' Day(s) -';
        }
    }
}else{
    $output_weather_forecast = '<p class="empty-text">There is no Weather Forecast right now. Maybe an site admin not setup weather options from site settings yet.</p>';
}
$output_weather_forecast_widget_x1 = '
<div id="15" class="widget" title="Weather Forecast Widget | 1x size">
    <div class="widget-wrapper  ' . $widget_themes . '">
        <div class="widget-header"><span>Weather Forecast ' . $output_weather_forecast_counter . ' <i title="' . $WeatherCity . ',' . $WeatherRegion . ' ' . $WeatherCountry . '">' . $WeatherCity . '</i></span>' . $output_widget_control_button_remove . $output_widget_control_button_add . '</div>
        <div class="widget-content">
                ' . $output_weather_forecast . '
        </div> 
    </div>
</div> ';


$output_subscription_widget_x1 = '';
if ($user['role'] < 1)
{
    if ((SUBSCRIPTION_KEY != '') && (SUBSCRIPTION_KEY != ' ') && (SUBSCRIPTION_KEY != NULL))
    {
        $API = '59593DS72233483322T669223344';
        if ($API != NULL and $API != '')
        {
            $request = array();
            $request['hostname'] = HOSTNAME_SETTING;
            $request['url'] = URL_SCHEME . HOSTNAME_SETTING . PATH;
            $request['version'] = VERSION;
            $request['edition'] = EDITION;
            $request['uname'] = php_uname();
            $request['os'] = PHP_OS;
            $request['web_server'] = $_SERVER['SERVER_SOFTWARE'];
            $request['php_version'] = phpversion();
            $request['mysql_version'] = db("SELECT VERSION()");
            $request['installer'] = INSTALLER;
            $request['private_label'] = PRIVATE_LABEL;
            $data = encode_json($request);
            $REQUEST = 'get';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://www.kodpen.com/api2?API=' . $API . '&REQUEST=' . $REQUEST . '&SECRET=' . SUBSCRIPTION_KEY);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data)
            ));
            // if there is a proxy address, then send cURL request through proxy
            if (PROXY_ADDRESS != '')
            {
                curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                curl_setopt($ch, CURLOPT_PROXY, PROXY_ADDRESS);
            }
            $response = curl_exec($ch);
            $curl_errno = curl_errno($ch);
            $curl_error = curl_error($ch);
            curl_close($ch);
            $data = decode_json($response);

            $D_name = $data['D_name'];
            $D_Host = $data['D_Host'];
            $D_start_d = $data['D_start_d'];
            $D_end_d = $data['D_end_d'];
            $Hosting = $data['Hosting'];
            $H_Host = $data['H_Host'];
            $H_Domain = $data['H_Domain'];
            $H_start_d = $data['H_start_d'];
            $H_end_d = $data['H_end_d'];
            $SSL_author = $data['SSL_author'];
            $SSL_Domain = $data['SSL_Domain'];
            $SSL_start_d = $data['SSL_start_d'];
            $SSL_end_d = $data['SSL_end_d'];
            $P_KEY = $data['P_KEY'];
            $P_start_d = $data['P_start_d'];
            $P_end_d = $data['P_end_d'];
            $S_limit = $data['S_limit'];
            $S_ID = $data['S_ID'];
            $S_password = $data['S_password'];

            $Mi = 0;
            foreach ($data['E_MAILS'] as $key => $val)
            {
                $E_ADDRESS = $key;
                $E_HOST = $val['E_HOST'];
                $E_PASSWORD = $val['E_PASSWORD'];
                //Output One Time Because kodpen not support multiple mail server for one mail account.
                $E_HOST_Output = '<p>Mail Sunucusu: ' . $E_HOST . '</p>';
                if ($Mi >= 1)
                {
                    $E_HOST_Output = '';
                }
                $E_WEB_URL = $val['E_WEB_URL'];
                $output_encoded_email = urlencode($E_ADDRESS);
                $output_encoded_password = urlencode($E_PASSWORD);

                $EMAILS .= '
                     ' . $E_HOST_Output . '
                    <div class="list-of-mail" >
                        <p>' . $E_ADDRESS . '</p>
                        <input type="password" style="max-width: 200px;" readonly value="' . $E_PASSWORD . '" />
                    </div>
                ';
                $Mi++;
            };
            $OUTPUT_EMAIL_ROW = '';
            if ($E_HOST and $EMAILS and $Mi > 0)
            {
                $OUTPUT_EMAIL_ROW = '
                <div class="column">
                    <p class="title" ><i class="material-icons">mail</i> Eposta Hesapları</p>
                    ' . $EMAILS . '
                   
                  
                </div>';

            }

            if (language_ruler() === 'en')
            {
            }
            elseif (language_ruler() === 'tr')
            {
                setlocale(LC_ALL, 'tr_TR.UTF-8');
            }
            $today = date_create(date("d-m-Y"));
            $D_start_d_formatted = date_create(date("d-m-Y", strtotime($D_start_d)));
            $D_end_d_formatted = date_create(date("d-m-Y", strtotime($D_end_d)));
            $H_start_d_formatted = date_create(date("d-m-Y", strtotime($H_start_d)));
            $H_end_d_formatted = date_create(date("d-m-Y", strtotime($H_end_d)));
            $SSL_start_d_formatted = date_create(date("d-m-Y", strtotime($SSL_start_d)));
            $SSL_end_d_formatted = date_create(date("d-m-Y", strtotime($SSL_end_d)));
            $P_start_d_formatted = date_create(date("d-m-Y", strtotime($P_start_d)));
            $P_end_d_formatted = date_create(date("d-m-Y", strtotime($P_end_d)));
            $D_start_d_localized = strftime("%e %B %Y", strtotime($D_start_d));
            $D_end_d_localized = strftime("%e %B %Y", strtotime($D_end_d));
            $H_start_d_localized = strftime("%e %B %Y", strtotime($H_start_d));
            $H_end_d_localized = strftime("%e %B %Y", strtotime($H_end_d));
            $SSL_start_d_localized = strftime("%e %B %Y", strtotime($SSL_start_d));
            $SSL_end_d_localized = strftime("%e %B %Y", strtotime($SSL_end_d));
            $P_start_d_localized = strftime("%e %B %Y", strtotime($P_start_d));
            $P_end_d_localized = strftime("%e %B %Y", strtotime($P_end_d));

            $D_interval = date_diff($D_end_d_formatted, $today);
            $D_interval_dif = date_diff($D_end_d_formatted, $D_start_d_formatted);
            $D_countdown = $D_interval->format('%a');
            $D_day_dif = $D_interval_dif->format('%a');
            $output_D_countdown = '';
            if (($D_countdown > 0) && ($today < $D_end_d_formatted))
            {
                if ($D_countdown > 10000)
                {
                    $output_D_countdown = '<span class="day_countdown_wrapper"><span class="day_countdown">Sınırsız</span></span>';
                }
                else
                {
                    $output_D_countdown = '<span class="day_countdown_wrapper"><span class="day_countdown">' . $D_countdown . '</span> gün kaldı</span>';
                }
            }
            else
            {
                $output_D_countdown = '<span class="day_countdown_wrapper"><span class="day_countdown">Süresi Dolmuş</span></span>';
            }
            $output_D_determine = '';
            if (($D_countdown > 0) && ($today < $D_end_d_formatted))
            {
                if ($D_countdown > 10000)
                {
                    $output_D_determine = '<div class="life-progress-determinate" style="width: 100%"></div>';
                }
                elseif ($D_countdown < 60)
                {
                    $output_D_determine = '<div class="life-progress-determinate" style="width: calc(100% / ' . $D_day_dif . '  * ' . $D_countdown . ' )"></div>';
                }
                else
                {
                    $output_D_determine = '<div class="life-progress-determinate" style="width: calc(100% / ' . $D_day_dif . '  * ' . $D_countdown . ' )"></div>';
                }
            }
            elseif (($D_Host == '') || ($D_Host == NULL))
            {
                // no output
                
            }
            else
            {
                $output_D_determine = '<div class="life-progress-determinate" style="width: 100%;background: #ff0000a3;"></div>';

            }

            if ($D_countdown < 60 and $D_countdown > 0 and $today < $D_end_d_formatted)
            {
                $liveform->add_warning("Domain süresi dolmak üzere, son <strong>$D_countdown</strong> gün kaldı.");

            }
            $OUTPUT_DOMAIN_ROWS = '';
            if ($D_Host && $D_name)
            {
                $OUTPUT_DOMAIN_ROWS = '
                    <div class="column">
                        <p class="title" title="SKT: ' . $D_end_d_localized . '"><i class="material-icons">language</i> Alan Adı ' . $output_D_countdown . '</p>
                        
                        <p>Alan Adı: ' . $D_name . '</p>
                        <div class="life-progress">' . $output_D_determine . '</div>
                    </div>';
            }

            $H_interval = date_diff($H_end_d_formatted, $today);
            $H_interval_dif = date_diff($H_end_d_formatted, $H_start_d_formatted);
            $H_countdown = $H_interval->format('%a');
            $H_day_dif = $H_interval_dif->format('%a');
            $output_H_countdown = '';
            if (($H_countdown > 0) && ($today < $H_end_d_formatted))
            {
                if ($H_countdown > 10000)
                {
                    $output_H_countdown = '<span class="day_countdown_wrapper"><span class="day_countdown">Sınırsız</span></span>';
                }
                else
                {
                    $output_H_countdown = '<span class="day_countdown_wrapper"><span class="day_countdown">' . $H_countdown . '</span> gün kaldı</span>';
                }
            }
            else
            {
                $output_H_countdown = '<span class="day_countdown_wrapper"><span class="day_countdown">Süresi Dolmuş</span></span>';
            }
            $output_H_determine = '';
            if (($H_countdown > 0) && ($today < $H_end_d_formatted))
            {
                if ($H_countdown > 10000)
                {
                    $output_H_determine = '<div class="life-progress-determinate" style="width: 100%"></div>';
                }
                elseif ($H_countdown < 60)
                {
                    $output_H_determine = '<div class="life-progress-determinate" style="width: calc(100% / ' . $H_day_dif . '  * ' . $H_countdown . ' )"></div>';
                }
                else
                {
                    $output_H_determine = '<div class="life-progress-determinate" style="width: calc(100% / ' . $H_day_dif . '  * ' . $H_countdown . ' )"></div>';
                }
            }
            elseif (($Hosting == '') || ($Hosting == NULL))
            {
                // no output
                
            }
            else
            {
                $output_H_determine = '<div class="life-progress-determinate" style="width: 100%;background: #ff0000a3;"></div>';
                $liveform->add_warning("Hosting süresi dolmuş.");
            }

            if ($H_countdown < 60 and $H_countdown > 0 and $today < $H_end_d_formatted)
            {
                $liveform->add_warning("Hosting süresi dolmak üzere, son <strong>$H_countdown</strong> gün kaldı.");
            }
            switch ($Hosting)
            {
                case '1':
                    $Hosting = 'Ekonomik Hosting';
                break;
                case '2':
                    $Hosting = 'Sınırsız Hosting';
                break;
                case '3':
                    $Hosting = 'Sınırsız Pro Hosting';
                break;
                case '5':
                    $Hosting = 'Xmail Hosting';
                break;
                default:
                    $Hosting = 'Bilinmiyor';
            }
            $OUTPUT_HOSTING_ROWS = '';
            if ($H_Host && $Hosting)
            {
                $OUTPUT_HOSTING_ROWS = '
                    <div class="column">
                        <p class="title" title="SKT: ' . $H_end_d_localized . '"><i class="material-icons">dns</i> Hosting ' . $output_H_countdown . '</p>
                        
                        <p>Hosting Tipi: ' . $Hosting . '</p>
                        <div class="life-progress">' . $output_H_determine . '</div>
                    </div>';
            }

            $SSL_interval = date_diff($SSL_end_d_formatted, $today);
            $SSL_interval_dif = date_diff($SSL_end_d_formatted, $SSL_start_d_formatted);
            $SSL_countdown = $SSL_interval->format('%a');
            $SSL_day_dif = $SSL_interval_dif->format('%a');
            $output_SSL_countdown = '';
            if (($SSL_countdown > 0) && ($today < $SSL_end_d_formatted))
            {
                if ($SSL_countdown > 10000)
                {
                    $output_SSL_countdown = '<span class="day_countdown_wrapper"><span class="day_countdown">Sınırsız</span></span>';
                }
                else
                {
                    $output_SSL_countdown = '<span class="day_countdown_wrapper"><span class="day_countdown">' . $SSL_countdown . '</span> gün kaldı</span>';
                }
            }
            else
            {
                $output_SSL_countdown = '<span class="day_countdown_wrapper"><span class="day_countdown">Süresi Dolmuş</span></span>';
            }
            $output_SSL_determine = '';
            if (($SSL_countdown > 0) && ($today < $SSL_end_d_formatted))
            {
                if ($SSL_countdown > 10000)
                {
                    $output_SSL_determine = '<div class="life-progress-determinate" style="width: 100%"></div>';
                }
                elseif ($SSL_countdown < 60)
                {
                    $output_SSL_determine = '<div class="life-progress-determinate" style="width: calc(100% / ' . $SSL_day_dif . '  * ' . $SSL_countdown . ' )"></div>';
                }
                else
                {
                    $output_SSL_determine = '<div class="life-progress-determinate" style="width: calc(100% / ' . $SSL_day_dif . '  * ' . $SSL_countdown . ' )"></div>';
                }
            }
            elseif (($SSL_author == '') || ($SSL_author == NULL))
            {
                // no output
                
            }
            else
            {
                $output_SSL_determine = '<div class="life-progress-determinate" style="width: 100%;background: #ff0000a3;"></div>';
                $liveform->add_warning("SSL süresi dolmuş.");
            }

            if ($SSL_countdown < 60 and $SSL_countdown > 0 and $today < $SSL_end_d_formatted)
            {
                $liveform->add_warning("SSL süresi dolmak üzere, son <strong>$SSL_countdown</strong> gün kaldı.");

            }
            $OUTPUT_SSL_ROWS = '';
            if ($SSL_author && $SSL_Domain)
            {
                $OUTPUT_SSL_ROWS = '
                    <div class="column">
                        <p class="title" title="SKT: ' . $SSL_end_d_localized . '"><i class="material-icons">lock</i> SSL Sertifikası ' . $output_SSL_countdown . '</p>
                        
                        <p>Veren: ' . $SSL_author . '</p>
                        <div class="life-progress">' . $output_SSL_determine . '</div>
                    </div>';
            }

            $P_interval = date_diff($P_end_d_formatted, $today);
            $P_interval_dif = date_diff($P_end_d_formatted, $P_start_d_formatted);
            $P_countdown = $P_interval->format('%a');
            $P_day_dif = $P_interval_dif->format('%a');
            $output_P_countdown = '';
            if (($P_countdown > 0) && ($today < $P_end_d_formatted))
            {
                if ($P_countdown > 10000)
                {
                    $output_P_countdown = '<span class="day_countdown_wrapper"><span class="day_countdown">Sınırsız</span></span>';
                }
                else
                {
                    $output_P_countdown = '<span class="day_countdown_wrapper"><span class="day_countdown">' . $P_countdown . '</span> gün kaldı</span>';
                }
            }
            else
            {
                $output_P_countdown = '<span class="day_countdown_wrapper"><span class="day_countdown">Süresi Dolmuş</span></span>';
            }
            $output_P_determine = '';
            if (($P_countdown > 0) && ($today < $P_end_d_formatted))
            {
                if ($P_countdown > 10000)
                {
                    $output_P_determine = '<div class="life-progress-determinate" style="width: 100%"></div>';
                }
                elseif ($P_countdown < 60)
                {
                    $output_P_determine = '<div class="life-progress-determinate" style="width: calc(100% / ' . $P_day_dif . '  * ' . $P_countdown . ' )"></div>';
                }
                else
                {
                    $output_P_determine = '<div class="life-progress-determinate" style="width: calc(100% / ' . $P_day_dif . '  * ' . $P_countdown . ' )"></div>';
                }
            }
            elseif (($P_KEY == '') || ($P_KEY == NULL))
            {
                // no output
                
            }
            else
            {
                $output_P_determine = '<div class="life-progress-determinate" style="width: 100%;background: #ff0000a3;"></div>';
                $liveform->add_warning("Yazılım Lisans süresi dolmuş.");
            }
            if ($P_countdown < 60 and $P_countdown > 0 and $today < $P_end_d_formatted)
            {
                $liveform->add_warning("Yazılım Lisans süresi dolmak üzere, son <strong>$P_countdown</strong> gün kaldı.");
            }
            $OUTPUT_P_ROWS = '';
            if ($P_KEY)
            {
                $OUTPUT_P_ROWS = '
                    <div class="column">
                        <p class="title" title="SKT: ' . $P_end_d_localized . '"><i class="material-icons">eco</i>Yazılım Lisansı ' . $output_P_countdown . '</p>
                        
                        <p>Lisans Anahtarı: ' . $P_KEY . '</p>
                        <div class="life-progress">' . $output_P_determine . '</div>
                    </div>';
            }

            $output_subscription_widget_x1 = '
            <div id="14" class="widget " title="Subscriptions Widget | 1x size">
                <div class="widget_subscription_wrapper  ' . $widget_themes . '">
                <div class="widget-header"><span>Subscriptions</span>' . $output_widget_control_button_remove . $output_widget_control_button_add . '</div>
                <div class="widget-content">' . $Username . '
                    <div class="flexbar">
                        ' . $OUTPUT_DOMAIN_ROWS . $OUTPUT_HOSTING_ROWS . $OUTPUT_SSL_ROWS . $OUTPUT_P_ROWS . $OUTPUT_EMAIL_ROW . '
                    </div>
                    </div>
                </div> 
            </div>';

        }
    }
}



$output_campaigns_widget_x1 = '';
if($user['role'] < 3){
    $output_campaigns = array();
    $query = "SELECT
                email_campaigns.id as id,
                email_campaigns.type,
                email_campaigns.subject,
                email_campaigns.status,
                email_campaigns.purpose,
                email_campaigns.start_time,
                email_campaigns.created_user_id,
                email_campaigns.created_timestamp,
                email_campaigns.last_modified_timestamp,
                created_user.user_username as created_username
            FROM email_campaigns
            LEFT JOIN user as created_user ON email_campaigns.created_user_id = created_user.user_id
            LEFT JOIN user as last_modified_user ON email_campaigns.last_modified_user_id = last_modified_user.user_id
            ORDER BY email_campaigns.last_modified_timestamp DESC
            LIMIT 20";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // loop through the result in order to prepare array of items
    while ($row = mysqli_fetch_assoc($result))
    {
        $output_campaigns[] = $row;
    }

    $output_campaign_rows = '';

    if (!empty($output_campaigns)) {

        foreach ($output_campaigns as $output_campaign)
        {
            // if the e-mail campaign job is enabled, then prepare to show start time cell
            if (defined('EMAIL_CAMPAIGN_JOB') and EMAIL_CAMPAIGN_JOB) {
                // if start time was not set, then clear start time
                if ($email_campaigns[$key]['start_time'] == '0000-00-00 00:00:00') {
                    $start_time = '';
                
                // else start time was set, so prepare format for start time
                } else {
                    $start_time = get_relative_time(array('timestamp' => strtotime($email_campaigns[$key]['start_time'])));
                }
                
            }
            // get total number of recipients
            $query = "SELECT COUNT(*) FROM email_recipients WHERE email_campaign_id = '" . $output_campaign['id'] . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_row($result);
            $number_of_email_recipients = $row[0];
            // Set the to value differently based on the campaign type.
            switch ($output_campaign['type']) {
                case 'manual':
                    $plural_suffix = '';

                    if (($number_of_email_recipients == 0) or ($number_of_email_recipients > 1)) {
                        $plural_suffix = 's';
                    }

                    $output_to = number_format($number_of_email_recipients) . ' Contact' . $plural_suffix;

                    break;
                
                case 'automatic':
                    // Set to value to the single recipient's email address for this automatic campaign.
                    $output_to = h(db_value("SELECT email_address FROM email_recipients WHERE email_campaign_id = '" . $email_campaigns[$key]['id'] . "'"));
                    break;
            }
            // get total number of complete recipients
            $query = "SELECT COUNT(*) FROM email_recipients WHERE (email_campaign_id = '" . $output_campaign['id'] . "') AND (complete = '1')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            $row = mysqli_fetch_row($result);
            $number_of_completed_email_recipients = $row[0];

            if ($number_of_email_recipients > 0) {
                $progress_percentage = number_format($number_of_completed_email_recipients / $number_of_email_recipients * 100);
            } else {
                $progress_percentage = '100';
            }
            $output_link_url = 'edit_email_campaign.php?id=' . $output_campaign['id'] . '&amp;send_to=' . h(escape_javascript(urlencode(REQUEST_URL)));

            if ($output_campaign['created_username']) {
                $campaigns_created_username = $output_campaign['created_username'];
            } else {
                $campaigns_created_username = '[Unknown]';
            }

            if ($output_campaign['last_modified_username']) {
                $campaigns_last_modified_username = $output_campaign['last_modified_username'];
            } else {
                $campaigns_last_modified_username = '[Unknown]';
            }

            $output_campaign_rows .= '
                
                <div class="row pointer" onclick="window.location.href=\'' . $output_link_url . '\'">
                    <div class="col-1">
                        <i class="material-icons icon" style="color:var(--coloron-campaigns-color);">campaign</i>
                    </div>
                    <div class="col-3">
                        <div class="header translateable">' . h(ucwords($output_campaign['purpose'])) . ' - ' . get_email_campaign_status_name($output_campaign['status']) . '</div>
                        
                        <div class="body textarea">' . $output_campaign['subject'] . '</div>
                       
                        <div class="extend right" title=" Create time - user">' . get_relative_time(array('timestamp' => $output_campaign['created_timestamp'])) . ' - ' . h($campaigns_created_username) . '</div>
                        <div class="extend left">' . $progress_percentage . '% (' . number_format($number_of_completed_email_recipients) . ' / ' . number_format($number_of_email_recipients) . ')</div>
                        
                    </div>
                </div>';
        }
    }else{
        $output_campaign_rows = '<p class="empty-text">There is no email compaign right now.</p>';
    }   

    $output_campaigns_widget_x1 = '
        <div id="19" class="widget " title="Email Campaigns Widget | 1x size">
            <div class="widget_wrapper  ' . $widget_themes . '">
            <div class="widget-header"><span>Email Campaigns</span>' . $output_widget_control_button_remove . $output_widget_control_button_add . '</div>
            <div class="widget-content">
                 ' . $output_campaign_rows . ' 
                </div>
            </div> 
        </div>';

}

    



$widgets = array(
    1 => $output_welcome_greetings_widget_x4,
    2 => $output_activity_summary_widget_x2,
    3 => $output_ecommerce_summary_x1,
    4 => $output_who_is_online_widget_x1,
    5 => $output_visitor_summary_x2,
    6 => $output_visitor_summary_pies_widget_x1,
    7 => $output_recent_updates_widget_x1,
    8 => $output_orders_widget_x1,
    9 => $output_carts_widget_x1,
    10 => $output_contacts_widget_x1,
    11 => $output_users_widget_x1,
    12 => $output_out_of_stock_x1,
    13 => $output_submitted_forms_widget_x1,
    14 => $output_subscription_widget_x1,
    15 => $output_weather_forecast_widget_x1,
    16 => $output_current_site_exchange_rates_widget_x1,
    17 => $output_last_site_logs_widget_x1,
    18 => $output_notes_widget_x2,
    19 => $output_campaigns_widget_x1,
);

$order_widgets_array = explode(',', $order_widgets);
$selected_widgets = $order_widgets_array;
uksort($widgets, function ($key1, $key2) use ($selected_widgets)
{
    return (array_search($key1, $selected_widgets) > array_search($key2, $selected_widgets));
});
foreach ($widgets as $key => $value)
{
    if (in_array($key, $selected_widgets))
    {
        $output_widgets .= $value;
    }elseif (!in_array($key, $selected_widgets)){
        $output_disabled_widgets .= $value;
    }

}

$_7_days_ago_text = '';
$_6_days_ago_text = '';
$_5_days_ago_text = '';
$_4_days_ago_text = '';
$_3_days_ago_text = '';
$_2_days_ago_text = '';
$_yesterday_text = '';
$_today_text = '';
$_days_text = '';
$_visitors_text = '';
$_months_text = '';
$_5_months_ago_text = '';
$_4_months_ago_text = '';
$_3_months_ago_text = '';
$_2_months_ago_text = '';
$_last_month_text = '';
$_this_month_text = '';
$_weeks_text = '';
$_3_weeks_before_text = '';
$_2_weeks_before_text = '';
$_last_week_text = '';
$_this_week_text = '';
$_orders_text = '';
$_forms_text = '';
$_new_users_text = '';

if (language_ruler() === 'en')
{
    $_7_days_ago_text = '7 days ago';
    $_6_days_ago_text = '6 days ago';
    $_5_days_ago_text = '5 days ago';
    $_4_days_ago_text = '4 days ago';
    $_3_days_ago_text = '3 days ago';
    $_2_days_ago_text = '2 days ago';
    $_yesterday_text = 'Yesterday';
    $_today_text = 'Today';
    $_days_text = 'Days';
    $_visitors_text = 'Visitors';
    $_months_text = 'Months';
    $_5_months_ago_text = '5 months ago';
    $_4_months_ago_text = '4 months ago';
    $_3_months_ago_text = '3 months ago';
    $_2_months_ago_text = '2 months ago';
    $_last_month_text = 'Last month';
    $_this_month_text = 'This month';
    $_weeks_text = 'Weeks';
    $_3_weeks_before_text = '3 weeks ago';
    $_2_weeks_before_text = '2 weeks ago';
    $_last_week_text = 'Last Week';
    $_this_week_text = 'This week';
    $_orders_text = 'Orders';
    $_forms_text = 'Forms';
    $_new_users_text = 'New users';
}
elseif (language_ruler() === 'tr')
{
    $_7_days_ago_text = '7 gün önce';
    $_6_days_ago_text = '6 gün önce';
    $_5_days_ago_text = '5 gün önce';
    $_4_days_ago_text = '4 gün önce';
    $_3_days_ago_text = '3 gün önce';
    $_2_days_ago_text = '2 gün önce';
    $_yesterday_text = 'Dün';
    $_today_text = 'Bugün';
    $_days_text = 'Günler';
    $_visitors_text = 'Ziyaretçiler';
    $_months_text = 'Aylar';
    $_5_months_ago_text = '5 ay önce';
    $_4_months_ago_text = '4 ay önce';
    $_3_months_ago_text = '3 ay önce';
    $_2_months_ago_text = '2 ay önce';
    $_last_month_text = 'Geçtiğimiz ay';
    $_this_month_text = 'Bu ay';
    $_weeks_text = 'Haftalar';
    $_3_weeks_before_text = '3 hafta önce';
    $_2_weeks_before_text = '2 hafta önce';
    $_last_week_text = 'Geçtiğimiz hafta';
    $_this_week_text = 'Bu hafta';
    $_orders_text = 'Siparişler';
    $_forms_text = 'Formlar';
    $_new_users_text = 'Yeni Kullanıcılar';
}

    $output_order_chart_header = '';
    $output_order_chart_7_days_ago_data = '';
    $output_order_chart_6_days_ago_data = '';
    $output_order_chart_5_days_ago_data = '';
    $output_order_chart_4_days_ago_data = '';
    $output_order_chart_3_days_ago_data = '';
    $output_order_chart_2_days_ago_data = '';
    $output_order_chart_yesterday_data = '';
    $output_order_chart_today_data = '';

if ((ECOMMERCE === true) and (($user['role'] < 3) or USER_MANAGE_ECOMMERCE or USER_MANAGE_ECOMMERCE_REPORTS))
{
    $output_order_chart_header = ', "' . $_orders_text . '"';
    $output_order_chart_7_days_ago_data = ' ' . str_replace(', ', '
                ', $number_of_orders_for_7_days_ago) . ', ';
    $output_order_chart_6_days_ago_data = '' . str_replace(', ', '
                ', $number_of_orders_for_6_days_ago) . ',';
    $output_order_chart_5_days_ago_data = '' . str_replace(', ', '
                ', $number_of_orders_for_5_days_ago) . ',';
    $output_order_chart_4_days_ago_data = '' . str_replace(', ', '
                ', $number_of_orders_for_4_days_ago) . ',';
    $output_order_chart_3_days_ago_data = '' . str_replace(', ', '
                ', $number_of_orders_for_3_days_ago) . ',';
    $output_order_chart_2_days_ago_data = '' . str_replace(', ', '
                ', $number_of_orders_for_2_days_ago) . ',';
    $output_order_chart_yesterday_data = '' . str_replace(', ', '
                ', $number_of_orders_for_yesterday) . ',';
    $output_order_chart_today_data = '' . str_replace(', ', '
                ', $number_of_orders_for_today) . ',';
}


// Output page.
print output_header() . '
   
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

    <a href="#" id="help_link" style="position:absolute;right:0;color:#fff" class="white_help_button">Help</a>
      
    ' . $liveform->output_errors() . '
    ' . $liveform->get_warnings() . '
    ' . $liveform->output_notices() . '

    <div id="widgets" class="flex-4 ">
        ' . $output_widgets . '
    </div>
    <div style="text-align:center;">
        ' . $output_widget_control_buttons . '
    </div>
    
    <div id="disabled_widgets"  class="flex-4">
        ' . $output_disabled_widgets . '
    </div>
    </div>
    <script>
    //toast message toast();
    var $message_container=$("<div></div>");$("body").append($message_container);var $options={"bgColor":"#282828","duration":4000,"ftColor":"white","vPosition":"top","hPosition":"right","fadeIn":400,"fadeOut":400,"clickable":!0,"autohide":!0};function toast(message,options=null){var type=typeof options;if(options!==null&&type==="object"){$.extend($options,options)}
    msg_container_css={"position":"fixed","margin-left":"7px","z-index":"50",};msg_container_css[$options.vPosition]="50px";msg_container_css[$options.hPosition]="5px";$message_container.css(msg_container_css);var $message=$("<div></div>");msg_css={"text-align":"right","margin-top":"10px","padding":"15px","border":"1px solid #dcdcdc","border-radius":"5px","float":"right","clear":"right","background-color":$options.bgColor,"color":$options.ftColor,"font-family":"Arial, Helvetica, sans-serif"};$message.css(msg_css);$message.text(message);$message_container.append($message).children(":last").hide().fadeIn($options.fadeIn);if($options.clickable){$message.on("click",function(){$(this).fadeOut($options.fadeOut)})}
    if($options.autohide){setTimeout(function(){$message.fadeOut($options.fadeOut),$message.remove()},$options.duration)}}
    
    function scroll_remove_nav_shadow() {
        document.body.scrollTop > 10 || document.documentElement.scrollTop > 10 ? document.getElementById("header").className = "" : document.getElementById("header").className = "no-shadow "
    }
    window.onscroll = function() {
        scroll_remove_nav_shadow()
    };
    
    function show_disabled_widgets(){
        $(".show_disabled_widgets_button").toggleClass( "expanded" );
        $("#disabled_widgets").toggleClass( "show_disabled_widgets " );
        if( $(".show_disabled_widgets_button").hasClass("expanded") ){
            $("html").animate({scrollTop: "+=450px"}, 800);
        }
    }
    function deactive_this_widget(elem){
        var widget = elem.closest(".widget");
        jQuery(widget).detach().appendTo("#disabled_widgets");
        
        var widgets = [];
        $.each($("#widgets .widget"), function() {
            widgets.push($(this).attr("id"));
        });
    
        if (widgets.length === 0) {
            alert("Please select at lease 1 widget");
            return false;
        }
        widgets.join(",");
        // Use AJAX to update.
        $.ajax({
            contentType: "application/json",
            url: "api.php",
            data: JSON.stringify({
                action: "update_dashboard_widgets",
                token: software_token,
                message_text : "deactivate",
                widgets: widgets
            }),
            type: "POST",
            success: function(response) {
                // Check the values in console
                $status = response.status;
                if ($status == "success") {
                    toast(response.message);
                }
            }
        });
      
    }
    function active_this_widget(elem){
        var widget = elem.closest(".widget");
        jQuery(widget).detach().appendTo("#widgets");
        
        var widgets = [];
        $.each($("#widgets .widget"), function() {
            widgets.push($(this).attr("id"));
        });
    
        if (widgets.length === 0) {
            alert("Please select at lease 1 widget");
            return false;
        }
        widgets.join(",");
        // Use AJAX to update.
        $.ajax({
            contentType: "application/json",
            url: "api.php",
            data: JSON.stringify({
                action: "update_dashboard_widgets",
                token: software_token,
                message_text : "activate",
                widgets: widgets
            }),
            type: "POST",
            success: function(response) {
                // Check the values in console
                $status = response.status;
                if ($status == "success") {
                    toast(response.message);
                }
            }
        });
    }
    function reset_widgets() {

        var widget = $("#disabled_widgets .widget");
        widget.each(function(){
            jQuery(this).detach().appendTo("#widgets");
        });
        


        $("#widgets .widget").sort(function (a, b) {
            return parseInt(a.id) > parseInt(b.id);
        }).each(function(){
            var elem = $(this);
            elem.remove();
            $(elem).appendTo("#widgets");
        });
        
        var widgets = [];
        $.each($("#widgets .widget"), function() {
            widgets.push($(this).attr("id"));
        });
    
        if (widgets.length === 0) {
            alert("Please select at lease 1 widget");
            return false;
        }
        widgets.join(",");
        // Use AJAX to update.
        $.ajax({
            contentType: "application/json",
            url: "api.php",
            data: JSON.stringify({
                action: "update_dashboard_widgets",
                token: software_token,
                message_text : "restart",
                widgets: widgets
            }),
            type: "POST",
            success: function(response) {
                // Check the values in console
                $status = response.status;
                if ($status == "success") {
                    toast(response.message);
                }
            }
        });
    };
    $(function() {
        scroll_remove_nav_shadow();
        ' . $output_widget_controls . '
        $("#header").addClass("no-shadow");
    });
    $("select#widget_settings_background").on("change", function() {
        $NewBackground = this.value;
        // Use AJAX to update.
        $.ajax({
            contentType: "application/json",
            url: "api.php",
            data: JSON.stringify({
                action: "update_dashboard",
                token: software_token,
                message_text : "Background Change",
                NewBackground: $NewBackground
            }),
            type: "POST",
            success: function(response) {
                // Check the values in console
                $status = response.status;
                if ($status == "success") {
                    $("body").addClass(response.NewBackground);   
                    $("body").removeClass(response.exBackground);
                    toast(response.message);
                }
            }
        });
    });
    $("select#widget_settings_themes").on("change", function() {
        $NewTheme = this.value;
        // Use AJAX to update.
        $.ajax({
            contentType: "application/json",
            url: "api.php",
            data: JSON.stringify({
                action: "update_dashboard",
                token: software_token,
                message_text : "Theme Change",
                NewTheme: $NewTheme
            }),
            type: "POST",
            success: function(response) {
                // Check the values in console
                $status = response.status;
                if ($status == "success") {
                    toast(response.message);
                    location.reload(); 
                }
            }
        });
    });
    

    function update_settings() {
    
        var background = $("#widget_settings_background option:selected").val();
        var location = $("#widget_settings_location").val();
        var background = $("#widget_settings_background option:selected").val();
        var theme = $("#widget_settings_themes option:selected").val();
        var weatherAppId = $("#widget_settings_location_app_id").val();
        var weatherAppKey = $("#widget_settings_location_consumer_key").val();
        var weatherAppSecret = $("#widget_settings_location_consumer_secret").val();


        if (location == null || location == "") {
            $("#widget_settings_location").addClass("error").val("türkiye");
            $("#widget_settings_location").focus();
        } else {
            // Use AJAX to get various card info.
            $.ajax({
                contentType: "application/json",
                url: "api.php",
                data: JSON.stringify({
                    action: "update_dashboard",
                    token: software_token,
                    location: location,
                    background: background,
                    theme: theme,
                    weatherAppId: weatherAppId,
                    weatherAppKey: weatherAppKey,
                    weatherAppSecret: weatherAppSecret
                }),
                type: "POST",
                success: function(response) {
                    // Check the values in console
                    $status = response.status;
                    if ($status == "success") {
                        reload()
                    }
                }
            });
        }
    }
   
        $(function () {
            $("img.lazy").Lazy();
        });


    google.charts.load("current", {
        "packages": ["bar"]
    });
    google.charts.setOnLoadCallback(drawallChart);
    function drawallChart() {
        var data = google.visualization.arrayToDataTable([
            ["' . $_days_text . '"' . $output_order_chart_header . ', "' . $_forms_text . '", "' . $_new_users_text . '"],
            ["' . $_7_days_ago_text . '",
                ' . $output_order_chart_7_days_ago_data . '
                ' . str_replace(', ', '
                ', $number_of_forms_for_7_days_ago) . ',
                ' . str_replace(', ', '
                ', $number_of_contacts_for_7_days_ago) . '
            ],
    
            ["' . $_6_days_ago_text . '",
               ' .  $output_order_chart_6_days_ago_data . '
                ' . str_replace(', ', '
                ', $number_of_forms_for_6_days_ago) . ',
                ' . str_replace(', ', '
                ', $number_of_contacts_for_6_days_ago) . '
            ],
            ["' . $_5_days_ago_text . '",
               ' .  $output_order_chart_5_days_ago_data . '
                ' . str_replace(', ', '
                ', $number_of_forms_for_5_days_ago) . ',
                ' . str_replace(', ', '
                ', $number_of_contacts_for_5_days_ago) . '
            ],
    
            ["' . $_4_days_ago_text . '",
               ' .  $output_order_chart_4_days_ago_data . '
                ' . str_replace(', ', '
                ', $number_of_forms_for_4_days_ago) . ',
                ' . str_replace(', ', '
                ', $number_of_contacts_for_4_days_ago) . '
            ],
    
            ["' . $_3_days_ago_text . '",
               ' .  $output_order_chart_3_days_ago_data . '
                ' . str_replace(', ', '
                ', $number_of_forms_for_3_days_ago) . ',
                ' . str_replace(', ', '
                ', $number_of_contacts_for_3_days_ago) . '
            ],
    
            ["' . $_2_days_ago_text . '",
               ' .  $output_order_chart_2_days_ago_data . '
                ' . str_replace(', ', '
                ', $number_of_forms_for_2_days_ago) . ',
                ' . str_replace(', ', '
                ', $number_of_contacts_for_2_days_ago) . '
            ],
    
            ["' . $_yesterday_text . '",
                ' . $output_order_chart_yesterday_data . '
                ' . str_replace(', ', '
                ', $number_of_forms_for_yesterday) . ',
                ' . str_replace(', ', '
                ', $number_of_contacts_for_yesterday) . '
            ],
    
            ["' . $_today_text . '",
                ' . $output_order_chart_today_data . '
                ' . str_replace(', ', '
                ', $number_of_forms_for_today) . ',
                ' . str_replace(', ', '
                ', $number_of_contacts_for_today) . '
            ]
        ]);
        var options = {
            animation: {
                duration: 1000,
                easing: "in",
                startup: true
            },
            baselineColor: "#CCCCCC",
            hAxis: {
                format: "decimal",
                textPosition: "in",
                minValue: "6",
                textStyle: {
                    color: "' . $widget_color_main_output . '"
                },
                viewWindowMode: "explicit",
                gridlines: {
                    color: "transparent"
                }
            },
            vAxis: {
                textStyle: {
                    color: "' . $widget_color_main_output . '"
                },
                gridlines: {
                    color: "transparent"
                }
            },
            height: 360,
            backgroundColor: {
                fill: "transparent"
            },
            focusTarget: "category",
            curveType: "function",
            seriesType: "bars",
            "chartArea": {
                "width": "70%",
                "height": "90%"
            },
            legend: {
                textStyle: {
                    color: "' . $widget_color_main_output . '"
                }
            },
            colors: ["#1b9e77", "#c94389", "#673ab7"]
        };
        var chartA = new google.visualization.BarChart(document.getElementById("widget_activity_summary_2x"));
        chartA.draw(data, options);
    }
    
    
    google.charts.load("current", {
        "packages": ["corechart"]
    });
    google.charts.setOnLoadCallback(drawChartWeek);
    google.charts.setOnLoadCallback(drawChartMonth);
    
    function drawChartWeek() {
        var data = google.visualization.arrayToDataTable([
            ["' . $_weeks_text . '", "' . $_visitors_text . '"],
            ["' . $_3_weeks_before_text . '", ' . str_replace(', ', '
                ', $number_of_visitors_for_4_weeks_before) . '
            ],
            ["' . $_2_weeks_before_text . '", ' . str_replace(', ', '
                ', $number_of_visitors_for_3_weeks_before) . '
            ],
            ["' . $_last_week_text . '", ' . str_replace(', ', '
                ', $number_of_visitors_for_week_before_last) . '
            ],
            ["' . $_this_week_text . '", ' . str_replace(', ', '
                ', $number_of_visitors_for_past_7_days) . '
            ],
        ]);
        var options = {
            animation: {
                duration: 1000,
                easing: "in",
                startup: true
            },
            backgroundColor: "transparent",
            legend: {
                position: "right",
                textStyle: {
                    color: "' . $widget_color_main_output . '"
                }
            },
            pieSliceText: "value",
            height: 174,
            tooltip: {
                trigger: "focus"
            },
            "chartArea": {
                "width": "100%",
                "height": "50%"
            },
        };
        var chartW = new google.visualization.PieChart(document.getElementById("chart_div_for_week"));
        chartW.draw(data, options);
    }
    
    function drawChartMonth() {
        var data = google.visualization.arrayToDataTable([
            ["' . $_months_text . '", "' . $_visitors_text . '"],
            ["' . $_5_months_ago_text . '", ' . str_replace(', ', '
                ', $number_of_visitors_for_5_month_before) . '
            ],
            ["' . $_4_months_ago_text . '", ' . str_replace(', ', '
                ', $number_of_visitors_for_4_month_before) . '
            ],
            ["' . $_3_months_ago_text . '", ' . str_replace(', ', '
                ', $number_of_visitors_for_3_month_before) . '
            ],
            ["' . $_2_months_ago_text . '", ' . str_replace(', ', '
                ', $number_of_visitors_for_2_month_before) . '
            ],
            ["' . $_last_month_text . '", ' . str_replace(', ', '
                ', $number_of_visitors_for_past_30_days) . '
            ],
            ["' . $_this_month_text . '", ' . str_replace(', ', '
                ', $number_of_visitors_for_month_before_last) . '
            ],
        ]);
        var options = {
            animation: {
                duration: 1000,
                easing: "in",
                startup: true
            },
            backgroundColor: "transparent",
            legend: {
                position: "right",
                textStyle: {
                    color: "' . $widget_color_main_output . '"
                }
            },
            height: 174,
            pieSliceText: "value",
            "chartArea": {
                "width": "100%",
                "height": "50%"
            },
        };
        var chartM = new google.visualization.PieChart(document.getElementById("chart_div_for_month"));
        chartM.draw(data, options);
    
    
    }
    google.charts.load("current", {
        "packages": ["corechart"]
    });
    google.charts.setOnLoadCallback(drawChart);
    
    function drawChart() {
        var data = google.visualization.arrayToDataTable([
            ["' . $_days_text . '", "' . $_visitors_text . '"],
            ["' . $_7_days_ago_text . '", ' . str_replace(', ', '
                ', $number_of_visitors_for_7_days_ago) . '
            ],
            ["' . $_6_days_ago_text . '", ' . str_replace(', ', '
                ', $number_of_visitors_for_6_days_ago) . '
            ],
            ["' . $_5_days_ago_text . '", ' . str_replace(', ', '
                ', $number_of_visitors_for_5_days_ago) . '
            ],
            ["' . $_4_days_ago_text . '", ' . str_replace(', ', '
                ', $number_of_visitors_for_4_days_ago) . '
            ],
            ["' . $_3_days_ago_text . '", ' . str_replace(', ', '
                ', $number_of_visitors_for_3_days_ago) . '
            ],
            ["' . $_2_days_ago_text . '", ' . str_replace(', ', '
                ', $number_of_visitors_for_2_days_ago) . '
            ],
            ["' . $_yesterday_text . '", ' . str_replace(', ', '
                ', $number_of_visitors_for_yesterday) . '
            ],
            ["' . $_today_text . '", ' . str_replace(', ', '
                ', $number_of_visitors_for_today) . '
            ]
        ]);
        var options = {
            animation: {
                duration: 1000,
                easing: "in",
                startup: true
            },
            legend: {
                position: "none",
                textStyle: {
                    color: "' . $widget_color_main_output . '"
                }
            },
            aggregationTarget: "category",
            backgroundColor: "transparent",
            focusTarget: "category",
            vAxis: {
                textPosition: "in",
    
                gridlines: {
                    color: "transparent"
                },
                baselineColor: "#CCCCCC",
                minValue: 10,
                textStyle: {
                    color: "' . $widget_color_main_output . '"
                }
            },
            hAxis: {
                textPosition: "out",
    
                gridlines: {
                    color: "transparent"
                },
                baselineColor: "#CCCCCC",
                textStyle: {
                    color: "' . $widget_color_main_output . '"
                }
            },
            "chartArea": {
                "width": "85%",
                "height": "60%"
            },
            height: 360,
            is3D: true,
            bar: {
                groupWidth: "50%"
            },
        };
        var chartS = new google.visualization.AreaChart(document.getElementById("visitor_summary_widget_x2"));
        chartS.draw(data, options);
    }

    </script>
    ' . output_footer();
$liveform->remove_form('welcome');

?>