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
$user = validate_user();
validate_ecommerce_access($user);

include_once('liveform.class.php');
$liveform = new liveform('view_product_groups');

// output product group tree screen
echo
    output_header() . '
    <script type="text/javascript">
        window.onload = init_product_group_tree;
    </script>
    <div id="subnav">
        ' . render(array('template' => 'commerce_subnav.php')) . '
    </div>
    <div id="button_bar">
        <a href="add_product_group.php">Create Product Group</a>
        <a href="edit_featured_and_new_items.php?from=view_product_groups&send_to=' . h(urlencode(get_request_uri())) . '">Edit Featured &amp; New Items</a>
    </div>
    <div id="content">
        
        ' . $liveform->output_errors() . '
        ' . $liveform->output_notices() . '
        <a href="#" id="help_link">Help</a>
        <h1>All Product Groups</h1>
        <div class="subheading" style="padding-bottom: 2em;">Organize all products and product groups for publishing.</div>
        <div style="margin-top: 2em;" id="product_group_tree">
            <div style="text-align: right; margin-top: -4em; padding-bottom: 4em;">
                <a href="#" onclick="update_product_group_tree(0, expand_all = true)" class="button_small"><img src="images/icon_product_group_expanded.png" width="20" height="20" border="0" alt="" style="vertical-align: middle;" class="icon_product_group" alt="" /></a>
                &nbsp;&nbsp;
                <a href="#" onclick="collapse_product_group_tree()" class="button_small"><img src="images/icon_product_group_collapsed.png" width="20" height="20" border="0" alt="" style="vertical-align: middle;" class="icon_product_group" alt="" /></a>
            </div>
            <ul id="ul_0" style="margin: 0px; display: none"></ul>
        </div>
    </div>' .
    output_footer();
    
$liveform->remove_form('view_product_groups');
?>