  
<div id="subnav">
    <table>
        <tbody>
            <tr>   
               <td>
                    <ul>
                        <li><a href="view_pages.php">My Pages</a></li>
                    </ul>
                </td>
                <td>
                    <ul>
                        <li><a href="view_short_links.php">My Short Links</a></li>
                    </ul>
                </td>
                <td>
                    <ul>
                        <li><a href="view_auto_dialogs.php">Auto Dialogs</a></li>
                    </ul>
                </td>
                <td>
                    <ul>
                        <li><a href="view_comments.php">Comments</a></li>
                    </ul>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div id="button_bar">
    <a href="add_auto_dialog.php">Create Auto Dialog</a>
</div>
<div id="content">
    <?=$liveform->get_messages()?>
    <a href="#" id="help_link">Help</a>
    <h1>All Auto Dialogs</h1>
    <div class="subheading">All dialogs that can automatically popup for visitors.</div>
    <form class="search_form">
        <input type="text" name="query" value="<?=h($_SESSION['software']['view_auto_dialogs']['query'])?>" /> <input type="submit" value="Search" class="submit_small_secondary">
        <?php if ($_SESSION['software']['view_auto_dialogs']['query'] != ''): ?>
             <input type="button" value="Clear" onclick="document.location.href = '<?=h(escape_javascript($_SERVER['PHP_SELF'])) . '?clear=true'?>'" class="submit_small_secondary">
        <?php endif ?>
    </form>
    <div class="view_summary">
        Viewing <?=number_format(count($auto_dialogs))?> of <?=number_format($all_auto_dialogs)?> Total
    </div>
    <table class="chart">
        <tr>
            <th><?=get_column_heading('Name', $_SESSION['software']['view_auto_dialogs']['sort'], $_SESSION['software']['view_auto_dialogs']['order'])?></th>
            <th style="text-align: center"><?=get_column_heading('Enabled', $_SESSION['software']['view_auto_dialogs']['sort'], $_SESSION['software']['view_auto_dialogs']['order'])?></th>
            <th><?=get_column_heading('URL', $_SESSION['software']['view_auto_dialogs']['sort'], $_SESSION['software']['view_auto_dialogs']['order'])?></th>
            <th><?=get_column_heading('Width', $_SESSION['software']['view_auto_dialogs']['sort'], $_SESSION['software']['view_auto_dialogs']['order'])?></th>
            <th><?=get_column_heading('Height', $_SESSION['software']['view_auto_dialogs']['sort'], $_SESSION['software']['view_auto_dialogs']['order'])?></th>
            <th><?=get_column_heading('Delay', $_SESSION['software']['view_auto_dialogs']['sort'], $_SESSION['software']['view_auto_dialogs']['order'])?></th>
            <th><?=get_column_heading('Frequency', $_SESSION['software']['view_auto_dialogs']['sort'], $_SESSION['software']['view_auto_dialogs']['order'])?></th>
            <th><?=get_column_heading('Only on Page', $_SESSION['software']['view_auto_dialogs']['sort'], $_SESSION['software']['view_auto_dialogs']['order'])?></th>
            <th><?=get_column_heading('Created', $_SESSION['software']['view_auto_dialogs']['sort'], $_SESSION['software']['view_auto_dialogs']['order'])?></th>
            <th><?=get_column_heading('Last Modified', $_SESSION['software']['view_auto_dialogs']['sort'], $_SESSION['software']['view_auto_dialogs']['order'])?></th>
        </tr>
        <?php foreach($auto_dialogs as $auto_dialog): ?>
            <tr class="pointer" onclick="window.location.href='edit_auto_dialog.php?id=<?=$auto_dialog['id']?>'">
                <td class="chart_label <?php if ($auto_dialog['enabled']): ?>status_enabled<?php else: ?>status_disabled<?php endif ?>">
                    <?=h($auto_dialog['name'])?>
                </td>
                <td style="text-align: center">
                    <?php if ($auto_dialog['enabled']): ?>
                        <img src="images/check_mark.gif" width="7" height="7" alt="check mark" title="">
                    <?php endif ?>
                </td>
                <td><?=h($auto_dialog['url'])?></td>
                <td>
                    <?php if ($auto_dialog['width']): ?>
                        <?=number_format($auto_dialog['width'])?>px
                    <?php endif ?>
                </td>
                <td>
                    <?php if ($auto_dialog['height']): ?>
                        <?=number_format($auto_dialog['height'])?>px
                    <?php endif ?>
                </td>
                <td>
                    <?php if ($auto_dialog['delay']): ?>
                        <?=number_format($auto_dialog['delay'])?> second<?php if ($auto_dialog['delay'] > 1): ?>s<?php endif ?>
                    <?php endif ?>
                </td>
                <td>
                    <?php if ($auto_dialog['frequency']): ?>
                        <?=number_format($auto_dialog['frequency'])?> hour<?php if ($auto_dialog['frequency'] > 1): ?>s<?php endif ?>
                    <?php endif ?>
                </td>
                <td>
                    <?=h($auto_dialog['page'])?>
                </td>
                <td>
                    <?=get_relative_time(array('timestamp' => $auto_dialog['created_timestamp']))?>
                    <?php if ($auto_dialog['created_username'] != ''): ?>
                        by <?=h($auto_dialog['created_username'])?>
                    <?php endif ?>
                </td>
                <td>
                    <?=get_relative_time(array('timestamp' => $auto_dialog['last_modified_timestamp']))?>
                    <?php if ($auto_dialog['last_modified_username'] != ''): ?>
                        by <?=h($auto_dialog['last_modified_username'])?>
                    <?php endif ?>
                </td>
            </tr>
        <?php endforeach ?>
    </table>
</div>