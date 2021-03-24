<div id="subnav">
    <h1>
        <?php if ($screen == 'create'): ?>
            [new auto dialog]
        <?php else: ?>
            <?=h($auto_dialog['name'])?>
        <?php endif ?>
    </h1>
</div>
<?php if ($screen == 'edit'): ?>
    <div id="button_bar">
        <a href="<?=h($preview_url)?>" target="_blank">Preview</a>
    </div>
<?php endif ?>
<div id="content">
    <?=$liveform->get_messages()?>
    <a href="#" id="help_link">Help</a>
    <h1><?=ucfirst($screen)?> Auto Dialog</h1>
    <div class="subheading" style="margin-bottom: 1.5em">
        <?php if ($screen == 'create'): ?>
            Create a new auto dialog that can automatically popup for visitors.  You may preview the auto dialog on the next screen and then enable it for all visitors when desired.
        <?php else: ?>
            Edit an auto dialog that can automatically popup for visitors.
        <?php endif ?>
    </div>
    <form method="post">
        <?=get_token_field()?>
        <table class="field">
            <tr>
                <td><label for="name">Name:</label></td>
                <td>
                    <?=$liveform->output_field(array(
                        'type' => 'text',
                        'id' => 'name',
                        'name' => 'name',
                        'size' => '50',
                        'maxlength' => '100'))?>
                </td>
            </tr>
            <?php if ($screen == 'edit'): ?>
                <tr>
                    <td><label for="enabled">Enable:</label></td>
                    <td>
                        <?=$liveform->output_field(array(
                            'type' => 'checkbox',
                            'id' => 'enabled',
                            'name' => 'enabled',
                            'value' => '1',
                            'class' => 'checkbox'))?>
                    </td>
                </tr>
            <?php endif ?>
            <tr>
                <td><label for="url">URL:</label></td>
                <td>
                    <?=$liveform->output_field(array(
                        'type' => 'text',
                        'id' => 'url',
                        'name' => 'url',
                        'size' => '80',
                        'maxlength' => '255'))?>
                </td>
            </tr>
            <tr>
                <td><label for="width">Width:</label></td>
                <td>
                    <?=$liveform->output_field(array(
                        'type' => 'text',
                        'id' => 'width',
                        'name' => 'width',
                        'size' => '3'))?>
                    pixels &nbsp;(leave blank for auto)
                </td>
            </tr>
            <tr>
                <td><label for="height">Height:</label></td>
                <td>
                    <?=$liveform->output_field(array(
                        'type' => 'text',
                        'id' => 'height',
                        'name' => 'height',
                        'size' => '3'))?>
                    pixels &nbsp;(leave blank for auto)
                </td>
            </tr>
            <tr>
                <td><label for="delay">Delay:</label></td>
                <td>
                    <?=$liveform->output_field(array(
                        'type' => 'text',
                        'id' => 'delay',
                        'name' => 'delay',
                        'size' => '3'))?>
                    seconds &nbsp;(leave blank for instant)
                </td>
            </tr>
            <tr>
                <td><label for="frequency">Frequency:</label></td>
                <td>
                    <?=$liveform->output_field(array(
                        'type' => 'text',
                        'id' => 'frequency',
                        'name' => 'frequency',
                        'size' => '3'))?>
                    hour(s) &nbsp;(leave blank for one-time)
                </td>
            </tr>
            <tr>
                <td><label for="page">Only on Page:</label></td>
                <td>
                    <?=$liveform->output_field(array(
                        'type' => 'text',
                        'id' => 'page',
                        'name' => 'page',
                        'size' => '50',
                        'maxlength' => '100',
                        'placeholder' => ''))?>&nbsp;
                    (leave blank to open on any page)
                </td>
            </tr>
        </table>
        <div class="buttons">
            <input type="submit" name="submit_button" value="<?php if ($screen == 'create'): ?>Create<?php else: ?>Save<?php endif ?>" class="submit-primary">&nbsp;&nbsp;
            <input type="button" name="cancel" value="Cancel" onclick="javascript:history.go(-1);" class="submit-secondary">
            <?php if ($screen == 'edit'): ?>
                &nbsp;&nbsp;<input type="submit" name="delete" value="Delete" class="delete" onclick="return confirm('WARNING: This auto dialog will be permanently deleted.')">
            <?php endif ?>
        </div>
    </form>
</div>