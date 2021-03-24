<div id="subnav">
    <h1><?=h($folder['name'])?></h1>
</div>
<div id="content">
    <?=$form->get_messages()?>
    <a href="#" id="help_link">Help</a>
    <h1>Duplicate Folder</h1>
    <div class="subheading" style="margin-bottom: 1.5em">
        Press Duplicate to duplicate this folder and pages directly inside of it.  Optionally, you may configure content that you want the system to find and replace in the new duplicated items.
    </div>
    <form method="post">
        <?=get_token_field()?>

        <button type="button" class="find_replace_button button" style="margin-bottom: 15px">+ Find &amp; Replace</button>

        <div class="find_replace" style="display: none">
            <?php for ($number = 1; $number <= 10; $number++): ?>
                <div class="find_replace_row" style="margin-bottom: 10px;">
                    Find: <input type="text" id="find_<?=$number?>" name="find_<?=$number?>" size="50">&nbsp;&nbsp;
                    Replace: <input type="text" id="replace_<?=$number?>" name="replace_<?=$number?>" size="50">
                </div>
            <?php endfor ?>
            <input type="hidden" name="find_replace">
        </div>
        <div class="buttons">
            <input type="submit" name="submit_duplicate" value="Duplicate" class="submit-primary">&nbsp;&nbsp;
            <input type="button" name="cancel" value="Cancel" onclick="javascript:history.go(-1);" class="submit-secondary">
        </div>
    </form>
</div>

<script>
    
    if ($('[name="find_replace"]').val() == 'true') {
        $('.find_replace').fadeIn();
        $('.find_replace_button').html('- Find &amp; Replace');
    }

    $('.find_replace_button').click(function() {
        if ($('[name="find_replace"]').val() == 'true') {
            $('[name="find_replace"]').val('false');
            $('.find_replace').fadeOut();
            $('.find_replace_button').html('+ Find &amp; Replace');
        } else {
            $('[name="find_replace"]').val('true');
            $('.find_replace').fadeIn();
            $('.find_replace_button').html('- Find &amp; Replace');
        }
    });
</script>