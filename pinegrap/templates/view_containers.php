<div id="subnav"><?=render(array('template' => 'commerce_subnav.php'))?></div>
<div id="button_bar">
    <a href="add_container.php">Create Container</a>
</div>
<div id="content">
    <?=$form->get_messages()?>
    <a href="#" id="help_link">Help</a>
    <h1>All Containers</h1>
    <div class="subheading">All shipping containers (e.g. boxes) that products are packaged in.</div>
    <form class="search_form">
        <input type="text" name="query" value="<?=h($_SESSION['software']['ecommerce']['view_containers']['query'])?>" /> <input type="submit" value="Search" class="submit_small_secondary">
        <?php if ($_SESSION['software']['ecommerce']['view_containers']['query'] != ''): ?>
             <input type="button" value="Clear" onclick="document.location.href = '<?=h(escape_javascript($_SERVER['PHP_SELF'])) . '?clear=true'?>'" class="submit_small_secondary">
        <?php endif ?>
    </form>
    <div class="view_summary">
        Viewing <?=number_format(count($containers))?> of <?=number_format($all_containers)?> Total
    </div>
    <table class="chart">
        <tr>
            <th><?=get_column_heading('Name', $_SESSION['software']['ecommerce']['view_containers']['sort'], $_SESSION['software']['ecommerce']['view_containers']['order'])?></th>
            <th style="text-align: center"><?=get_column_heading('Enabled', $_SESSION['software']['ecommerce']['view_containers']['sort'], $_SESSION['software']['ecommerce']['view_containers']['order'])?></th>
            <th><?=get_column_heading('Length', $_SESSION['software']['ecommerce']['view_containers']['sort'], $_SESSION['software']['ecommerce']['view_containers']['order'])?></th>
            <th><?=get_column_heading('Width', $_SESSION['software']['ecommerce']['view_containers']['sort'], $_SESSION['software']['ecommerce']['view_containers']['order'])?></th>
            <th><?=get_column_heading('Height', $_SESSION['software']['ecommerce']['view_containers']['sort'], $_SESSION['software']['ecommerce']['view_containers']['order'])?></th>
            <th><?=get_column_heading('Weight', $_SESSION['software']['ecommerce']['view_containers']['sort'], $_SESSION['software']['ecommerce']['view_containers']['order'])?></th>
            <th style="text-align: right"><?=get_column_heading('Cost', $_SESSION['software']['ecommerce']['view_containers']['sort'], $_SESSION['software']['ecommerce']['view_containers']['order'])?></th>
            <th><?=get_column_heading('Created', $_SESSION['software']['ecommerce']['view_containers']['sort'], $_SESSION['software']['ecommerce']['view_containers']['order'])?></th>
            <th><?=get_column_heading('Last Modified', $_SESSION['software']['ecommerce']['view_containers']['sort'], $_SESSION['software']['ecommerce']['view_containers']['order'])?></th>
        </tr>
        <?php foreach($containers as $container): ?>
            <tr class="pointer" onclick="window.location.href='edit_container.php?id=<?=$container['id']?>'">
                <td class="chart_label <?php if ($container['enabled']): ?>status_enabled<?php else: ?>status_disabled<?php endif ?>">
                    <?=h($container['name'])?>
                </td>
                <td style="text-align: center">
                    <?php if ($container['enabled']): ?>
                        <img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="">
                    <?php endif ?>
                </td>
                <td><?=$container['length']+0?>&Prime;</td>
                <td><?=$container['width']+0?>&Prime;</td>
                <td><?=$container['height']+0?>&Prime;</td>
                <td>
                    <?php if ($container['weight'] > 0): ?>
                        <?=$container['weight']+0?> lb
                    <?php endif ?>
                </td>
                <td style="text-align: right">
                    <?php if ($container['cost'] > 0): ?>
                        <?=BASE_CURRENCY_SYMBOL . number_format($container['cost'], 2)?>
                    <?php endif ?>
                </td>
                <td>
                    <?=get_relative_time(array('timestamp' => $container['created_timestamp']))?>
                    <?php if ($container['created_username'] != ''): ?>
                        by <?=h($container['created_username'])?>
                    <?php endif ?>
                </td>
                <td>
                    <?=get_relative_time(array('timestamp' => $container['last_modified_timestamp']))?>
                    <?php if ($container['last_modified_username'] != ''): ?>
                        by <?=h($container['last_modified_username'])?>
                    <?php endif ?>
                </td>
            </tr>
        <?php endforeach ?>
    </table>
</div>