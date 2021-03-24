<div id="subnav"><?=render(array('template' => 'commerce_subnav.php'))?></div>
<div id="button_bar">
    <a href="add_key_code.php">Create Key Code</a>
    <a href="import_key_codes.php">Import Key Codes</a>
</div>
<div id="content">
    <?=$liveform->get_messages()?>
    <a href="#" id="help_link">Help</a>
    <h1>All Key Codes</h1>
    <div class="subheading">All key codes assigned to specific offers.</div>
    <form class="search_form">
        <input type="text" name="query" value="<?=h($_SESSION['software']['ecommerce']['view_key_codes']['query'])?>" /> <input type="submit" value="Search" class="submit_small_secondary">
        <?php if ($_SESSION['software']['ecommerce']['view_key_codes']['query'] != ''): ?>
             <input type="button" value="Clear" onclick="document.location.href = '<?=h(escape_javascript($_SERVER['PHP_SELF'])) . '?clear=true'?>'" class="submit_small_secondary">
        <?php endif ?>
    </form>
    <div class="view_summary">
        Viewing <?=number_format($number_of_results)?> of <?=number_format($all_key_codes)?> Total&nbsp;&nbsp;&nbsp;&nbsp;<form method="get" style="margin: 0; display: inline"><input type="submit" name="submit_data" value="Export Key Codes" class="submit_small_secondary"></form>
    </div>
    <table class="chart">
        <tr>
            <th><?=get_column_heading('Key Code', $_SESSION['software']['ecommerce']['view_key_codes']['sort'], $_SESSION['software']['ecommerce']['view_key_codes']['order'])?></th>
            <th><?=get_column_heading('Offer Code', $_SESSION['software']['ecommerce']['view_key_codes']['sort'], $_SESSION['software']['ecommerce']['view_key_codes']['order'])?></th>
            <th><?=get_column_heading('Offer Message', $_SESSION['software']['ecommerce']['view_key_codes']['sort'], $_SESSION['software']['ecommerce']['view_key_codes']['order'])?></th>
            <th style="text-align: center"><?=get_column_heading('Enabled', $_SESSION['software']['ecommerce']['view_key_codes']['sort'], $_SESSION['software']['ecommerce']['view_key_codes']['order'])?></th>
            <th><?=get_column_heading('Expiration Date', $_SESSION['software']['ecommerce']['view_key_codes']['sort'], $_SESSION['software']['ecommerce']['view_key_codes']['order'])?></th>
            <th><?=get_column_heading('Notes', $_SESSION['software']['ecommerce']['view_key_codes']['sort'], $_SESSION['software']['ecommerce']['view_key_codes']['order'])?></th>
            <th style="text-align: center"><?=get_column_heading('Single-Use', $_SESSION['software']['ecommerce']['view_key_codes']['sort'], $_SESSION['software']['ecommerce']['view_key_codes']['order'])?></th>
            <th><?=get_column_heading('Report', $_SESSION['software']['ecommerce']['view_key_codes']['sort'], $_SESSION['software']['ecommerce']['view_key_codes']['order'])?></th>
            <th><?=get_column_heading('Last Modified', $_SESSION['software']['ecommerce']['view_key_codes']['sort'], $_SESSION['software']['ecommerce']['view_key_codes']['order'])?></th>
        </tr>
        <?php foreach($key_codes as $key_code): ?>
            <tr class="pointer" onclick="window.location.href='edit_key_code.php?id=<?=$key_code['id']?>'">
                <td class="chart_label <?php if ($key_code['status_enabled']): ?>status_enabled<?php else: ?>status_disabled<?php endif ?>">
                    <?=h($key_code['code'])?>
                </td>
                <td>
                    <?php if ($key_code['offer_id']): ?>
                        <a href="edit_offer.php?id=<?=$key_code['offer_id']?>" title="Edit Offer">
                            <?=h($key_code['offer_code'])?>
                        </a>
                    <?php else: ?>
                        <?=h($key_code['offer_code'])?>
                    <?php endif ?>
                </td>
                <td>
                    <?=h($key_code['offer_description'])?>
                </td>
                <td style="text-align: center">
                    <?php if ($key_code['enabled']): ?>
                        <img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="">
                    <?php endif ?>
                </td>
                <td>
                    <?php if ($key_code['expiration_date'] != '0000-00-00'): ?>
                        <?=get_absolute_time(array(
                            'timestamp' => strtotime($key_code['expiration_date']),
                            'type' => 'date'))?>
                    <?php endif ?>
                </td>
                <td>
                    <?=nl2br(h($key_code['notes']))?>
                </td>
                <td style="text-align: center">
                    <?php if ($key_code['single_use']): ?>
                        <img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="">
                    <?php endif ?>
                </td>
                <td>
                    <?php if ($key_code['report'] == 'key_code'): ?>
                        Key Code
                    <?php else: ?>
                        Offer Code
                    <?php endif ?>
                </td>
                <td>
                    <?=get_relative_time(array('timestamp' => $key_code['last_modified_timestamp']))?>
                    <?php if ($key_code['last_modified_username'] != ''): ?>
                        by <?=h($key_code['last_modified_username'])?>
                    <?php endif ?>
                </td>
            </tr>
        <?php endforeach ?>
    </table>

    <?php require('pagination.php') ?>

    <div class="buttons">
        <a href="delete_key_codes.php" class="delete">Delete All Key Codes</a>
    </div>
</div>