
<?=$messages?>

<div class="row">
    
<form <?=$attributes?>>

    <div class="col-sm-6">
    <!-- first_name -->
    <div class="form-group">
        <label for="180"><?=$field['180']['label']?>*</label>
        <input type="text" name="180" id="180" class="form-control">
    </div>
    </div>
    
    <div class="col-sm-6">
    <!-- last_name -->
    <div class="form-group">
        <label for="181"><?=$field['181']['label']?>*</label>
        <input type="text" name="181" id="181" class="form-control">
    </div>
    </div>
    
    <div class="col-sm-12">
    <!-- company -->
    <div class="form-group">
        <label for="182"><?=$field['182']['label']?>*</label>
        <input type="text" name="182" id="182" class="form-control">
    </div>
    </div>
    
    <div class="col-sm-4">
    <!-- business_phone -->
    <div class="form-group">
        <label for="183"><?=$field['183']['label']?>*</label>
        <input type="text" name="183" id="183" class="form-control">
    </div>
    </div>
    
	<div class="col-sm-8">
    <!-- email_address -->
    <div class="form-group">
        <label for="184"><?=$field['184']['label']?>*</label>
        <input type="email" name="184" id="184" class="form-control">
    </div>
    </div>
    
    <?php if ($watcher_option): ?>
    	<div class="col-sm-12">
        <div class="checkbox">
            <label>
                <input type="checkbox" name="watcher" value="1" checked>
                Bir <?=h(mb_strtolower($comment_label))?> eklendiğinde bana bildir.
            </label>
        </div>
        </div>
    <?php endif ?>
           
    <?php if ($captcha_question): ?>
    <div class="col-sm-12"> 
        <h5>Spam'ı önlemek için lütfen yanıtlayın:</h5>
        <div class="form-group">
            <label for="captcha_submitted_answer"><?=h($captcha_question)?>*</label>
            <input type="number" name="captcha_submitted_answer" id="captcha_submitted_answer" class="form-control">
        </div>
    </div>
    <?php endif ?>

    <div class="col-sm-12">
    	<?php
        // The name attribute on the buttons below is important for the system
        // to know which button was pressed.
        // If save-for-later is enabled for custom form then show button.
        if ($save):
    	?>
        <button type="submit" name="save_button" class="btn btn-secondary">
        	Daha Sonrası için Kaydet
        </button>
    	<?php endif ?>

    	<button type="submit" name="submit_button" class="btn btn-primary">
        	<?=h($submit_button_label)?>
    	</button>    
    </div>

    <!-- Required hidden fields and JS (do not remove) -->
    <?=$system?>

</form>
    
</div>
