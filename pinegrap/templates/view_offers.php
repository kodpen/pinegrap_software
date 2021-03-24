<div id="subnav"><?=render(array('template' => 'commerce_subnav.php'))?></div>
<div id="button_bar">
    <a href="add_offer.php">Create Offer</a>
</div>
<div id="content">
    
    <a href="#" id="help_link">Help</a>
    <h1>All Offers</h1>
    <div class="subheading">All order and product discounts.</div>
    <form class="search_form">
        <input type="text" name="query" value="<?=h($_SESSION['software']['ecommerce']['view_offers']['query'])?>" /> <input type="submit" value="Search" class="submit_small_secondary">
        <?php if ($_SESSION['software']['ecommerce']['view_offers']['query'] != ''): ?>
             <input type="button" value="Clear" onclick="document.location.href = '<?=h(escape_javascript($_SERVER['PHP_SELF'])) . '?clear=true'?>'" class="submit_small_secondary">
        <?php endif ?>
    </form>
    <div class="view_summary">
        Viewing <?=number_format($number_of_results)?> of <?=number_format($all_offers)?> Total
    </div>
    <table class="chart">
        <tr>
            <th><?=get_column_heading('Offer Code', $_SESSION['software']['ecommerce']['view_offers']['sort'], $_SESSION['software']['ecommerce']['view_offers']['order'])?></th>
            <th><?=get_column_heading('Message', $_SESSION['software']['ecommerce']['view_offers']['sort'], $_SESSION['software']['ecommerce']['view_offers']['order'])?></th>
            <th><?=get_column_heading('Rule', $_SESSION['software']['ecommerce']['view_offers']['sort'], $_SESSION['software']['ecommerce']['view_offers']['order'])?></th>
            <th>Actions</th>
            <th><?=get_column_heading('Status', $_SESSION['software']['ecommerce']['view_offers']['sort'], $_SESSION['software']['ecommerce']['view_offers']['order'])?></th>
            <th><?=get_column_heading('Start Date', $_SESSION['software']['ecommerce']['view_offers']['sort'], $_SESSION['software']['ecommerce']['view_offers']['order'])?></th>
            <th><?=get_column_heading('End Date', $_SESSION['software']['ecommerce']['view_offers']['sort'], $_SESSION['software']['ecommerce']['view_offers']['order'])?></th>
            <th style="text-align: center"><?=get_column_heading('Require Code', $_SESSION['software']['ecommerce']['view_offers']['sort'], $_SESSION['software']['ecommerce']['view_offers']['order'])?></th>
            <th style="text-align: center"><?=get_column_heading('Best', $_SESSION['software']['ecommerce']['view_offers']['sort'], $_SESSION['software']['ecommerce']['view_offers']['order'])?></th>
            <th><?=get_column_heading('Last Modified', $_SESSION['software']['ecommerce']['view_offers']['sort'], $_SESSION['software']['ecommerce']['view_offers']['order'])?></th>
        </tr>
        <?php foreach($offers as $offer): ?>
            <tr class="pointer" onclick="window.location.href='edit_offer.php?id=<?=$offer['id']?>'">
                <td class="chart_label <?php if ($offer['status_enabled']): ?>status_enabled<?php else: ?>status_disabled<?php endif ?>">
                    <?=h($offer['code'])?>
                </td>
                <td>
                    <?=h($offer['description'])?>
                </td>
                <td>
                    <?php if ($offer['offer_rule_id']): ?>
                        <a href="edit_offer_rule.php?id=<?=$offer['offer_rule_id']?>" title="Edit Offer Rule">
                            <?=h($offer['offer_rule_name'])?>
                        </a>
                    <?php endif ?>
                </td>
                <td>
                    <?php foreach($offer['actions'] as $key => $action): ?><?php if ($key): ?>,<br><?php endif ?><a href="edit_offer_action.php?id=<?=$action['id']?>"><?=h($action['name'])?></a><?php endforeach ?>
                </td>
                <td>
                    <?=h(ucwords($offer['status']))?>
                </td>
                <td>
                    <?=prepare_form_data_for_output($offer['start_date'], 'date')?>
                </td>
                <td>
                    <?=prepare_form_data_for_output($offer['end_date'], 'date')?>
                </td>
                <td style="text-align: center">
                    <?php if ($offer['require_code']): ?>
                        <img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="">
                    <?php endif ?>
                </td>
                <td style="text-align: center">
                    <?php if ($offer['only_apply_best_offer']): ?>
                        <img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="">
                    <?php endif ?>
                </td>
                <td>
                    <?=get_relative_time(array('timestamp' => $offer['last_modified_timestamp']))?>
                    <?php if ($offer['last_modified_username'] != ''): ?>
                        by <?=h($offer['last_modified_username'])?>
                    <?php endif ?>
                </td>
            </tr>
        <?php endforeach ?>
    </table>

    <?php require('pagination.php') ?>

</div>