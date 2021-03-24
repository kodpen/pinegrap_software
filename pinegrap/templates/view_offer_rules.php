
<div id="subnav"><?=render(array('template' => 'commerce_subnav.php'))?></div>

<div id="button_bar">
    <a href="add_offer_rule.php">Create Offer Rule</a>
</div>

<div id="content">

    <?=$liveform->get_messages()?>

    <a href="#" id="help_link">Help</a>

    <h1>All Offer Rules</h1>

    <div class="subheading">All rules available to any offer.</div>

    <div class="view_summary">
        Viewing <?=number_format($number_of_results)?> of <?=number_format($number_of_results)?> Total
    </div>

    <table class="chart">

        <tr>
            <th>
                <?=get_column_heading('Name', $_SESSION['software']['ecommerce']['view_offer_rules']['sort'], $_SESSION['software']['ecommerce']['view_offer_rules']['order'])?>
            </th>

            <th style="text-align: right">
                <?=get_column_heading('Required Subtotal', $_SESSION['software']['ecommerce']['view_offer_rules']['sort'], $_SESSION['software']['ecommerce']['view_offer_rules']['order'])?>
            </th>

            <th>Required Product</th>

            <th style="text-align: center">
                <?=get_column_heading('Required Quantity', $_SESSION['software']['ecommerce']['view_offer_rules']['sort'], $_SESSION['software']['ecommerce']['view_offer_rules']['order'])?>
            </th>

            <th>
                <?=get_column_heading('Last Modified', $_SESSION['software']['ecommerce']['view_offer_rules']['sort'], $_SESSION['software']['ecommerce']['view_offer_rules']['order'])?>
            </th>
        </tr>

        <?php foreach($offer_rules as $offer_rule): ?>

            <tr class="pointer" onclick="window.location.href='edit_offer_rule.php?id=<?=h($offer_rule['id'])?>'">

                <td class="chart_label" style="vertical-align: top">
                    <?=h($offer_rule['name'])?>
                </td>

                <td style="text-align: right; vertical-align: top">
                    <?php if ($offer_rule['required_subtotal'] > 0): ?>
                        <?=prepare_amount($offer_rule['required_subtotal'])?>
                    <?php endif ?>
                </td>

                <td style="vertical-align: top">
                    <?php foreach($offer_rule['products'] as $product): ?>
                        <div style="margin-bottom: 4px"><?=h($product['name'])?> - <?=h($product['short_description'])?></div>
                    <?php endforeach ?>
                </td>

                <td style="text-align: center; vertical-align: top">
                    <?php if ($offer_rule['products'] and $offer_rule['required_quantity']): ?>
                        <?=h(number_format($offer_rule['required_quantity']))?>
                    <?php endif ?>
                </td>

                <td style="vertical-align: top">
                    <?=get_relative_time(array('timestamp' => $offer_rule['last_modified_timestamp']))?>

                    <?php if ($offer_rule['last_modified_username'] != ''): ?>
                        by <?=h($offer_rule['last_modified_username'])?>
                    <?php endif ?>
                </td>
            </tr>
        <?php endforeach ?>
    </table>
</div>