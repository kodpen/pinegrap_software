
<?=$messages?>
	
<div class="row">
        
<form <?=$attributes?>>

    <!-- description -->
    <div class="col-sm-12">
    <?=$field['267']['information']?>
    </div>
    
    <!-- email -->
    <div class="col-sm-12">
    <div class="form-group">
        <input type="email" name="208" id="208" class="form-control" placeholder="E-POSTA ADRESİNİZ">
    </div>
    </div>
    

    <?php if ($watcher_option): ?>
    
     	<div class="col-sm-12">
        <div class="checkbox">
            <label>
                <input type="checkbox" name="watcher" value="1" checked>
                <?=h(mb_strtolower($comment_label))?> eklendiğinde bana bildirim gönder.
            </label>
        </div>
    	</div>
    
    <?php endif ?>
    
    <?php if ($captcha_question): ?>

        <div class="col-sm-12">
        <div class="form-group">
            <input type="number" name="captcha_submitted_answer" id="captcha_submitted_answer" class="form-control" placeholder="<?=h($captcha_question)?>">
        </div>
    	</div>

    <?php endif ?>

    <div class="col-sm-12">
    	<button type="submit" class="btn btn-primary"><?=h($submit_button_label)?></button>
    </div>
    <!-- Required hidden fields and JS (do not remove) -->
    <?=$system?>

</form>
    
</div>