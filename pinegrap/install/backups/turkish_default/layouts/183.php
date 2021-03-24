
<?=$messages?>

<form <?=$attributes?>>

<div class="row">
    <!-- first_name -->
    <div class="col-sm-6 form-group">
        <label for="1"><?=$field['1']['label']?>*</label>
        <input type="text" name="1" id="1">
    </div>
    
    <!-- last_name -->
    <div class="col-sm-6 form-group">
        <label for="2"><?=$field['2']['label']?></label>
        <input type="text" name="2" id="2">
    </div>
</div>
    
<div class="row">
    <!-- company -->
    <div class="col-sm-6 form-group">
        <label for="3"><?=$field['3']['label']?></label>
        <input type="text" name="3" id="3">
    </div>
    
    <!-- business_phone -->
    <div class="col-sm-6 form-group">
        <label for="9"><?=$field['9']['label']?></label>
        <input type="text" name="9" id="9">
    </div>
</div>
    
    <!-- e-mail -->
    <div class="form-group">
        <label for="10"><?=$field['10']['label']?>*</label>
        <input type="email" name="10" id="10">
    </div>
    
    <!-- subject -->
    <div class="form-group">
        <input type="text" name="291" id="291" placeholder="Konu*">
    </div>
    
    <!-- details -->
    <div class="form-group">
        <textarea name="12" id="12" placeholder="Nasıl yardımcı olabiliriz?*"></textarea>
    </div>
    
    <?php if ($office_use_only): ?>
        <!-- notes -->
        <div class="form-group">
            <label for="52"><?=$field['52']['label']?></label>
            <textarea name="52" id="52"></textarea>
        </div>
    <?php endif ?>
    

    <?php if ($watcher_option): ?>
        <div class="checkbox">
            <label>
                <input type="checkbox" name="watcher" value="1" checked>
                Bir <?=h(mb_strtolower($comment_label))?> eklendiğinde bana bildir.
            </label>
        </div>
    <?php endif ?>
    
    <?php if ($captcha_question): ?>

        <div class="form-group">
            <input type="number" name="captcha_submitted_answer" id="captcha_submitted_answer" placeholder="<?=h($captcha_question)?>*">
        </div>

    <?php endif ?>

    <button type="submit" class="btn btn-primary"><?=h($submit_button_label)?></button>

    <!-- Required hidden fields and JS (do not remove) -->
    <?=$system?>

</form>
