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

include('init.php');
include_once('liveform.class.php');
$liveform = new liveform('view_folders');
$user = validate_user();
validate_area_access($user, 'user');

print
    output_header() .
    '<script type="text/javascript">
        window.onload = init_folder_tree;
    </script>
    <div id="subnav"></div>
    <div id="button_bar">
        <a href="' . OUTPUT_PATH . OUTPUT_SOFTWARE_DIRECTORY . '/add_folder.php">Create Folder</a>
    </div>
    <div id="content">
        
        ' . $liveform->output_errors() . '
        ' . $liveform->output_notices() . '
        <a href="#" id="help_link">Help</a>
        <h1>My Folders</h1>
        <div class="subheading" style="margin-bottom: .5em;">All visible folders, including their pages & files.</div>
        <br />
        <div style="margin-top: 2em;" id="folder_tree">
             <div style="text-align: right; margin-top: -4em; padding-bottom: 4em;">
                <a href="#" onclick="update_folder_tree(0, expand_all = true)" class="button_small"><img src="images/icon_folder_expanded.png" width="20" height="20" border="0" alt="" style="vertical-align: middle" class="icon_folder" alt="" /></a>
                <a href="#" onclick="collapse_folder_tree()" class="button_small"><img src="images/icon_folder_collapsed.png" width="20" height="20" border="0" alt="" style="vertical-align: middle" class="icon_folder" alt="" /></a>
            </div>
            <ul id="ul_0" style="margin: 0px; display: none"></ul>
        </div>
    </div>' .
    output_footer();

$liveform->remove_form('view_folders');
?>
