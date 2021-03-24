
<div class="row">
    
<?=$messages?>

<form <?=$attributes?>>

    <div class="col-sm-12">
    <!-- subject -->
    <div class="form-group">
        <input type="text" name="166" id="166" class="form-control" placeholder="Subject">
    </div>
    </div>
    
    <div class="col-sm-12">
    <!-- details -->
    <div class="form-group">
        <textarea name="167" id="167" placeholder="How can we help you?"></textarea>
    </div>
    </div>
    
    <div class="col-sm-12">
    <!-- information_01 -->
    <?=$field['168']['information']?>
    </div>
    
    <div class="col-sm-4">
    <!-- first_name -->
    <div class="form-group">
        <label for="169"><?=$field['169']['label']?></label>
        <input type="text" name="169" id="169" class="form-control">
    </div>
    </div>
    
    <div class="col-sm-4">
    <!-- last_name -->
    <div class="form-group">
        <label for="170"><?=$field['170']['label']?></label>
        <input type="text" name="170" id="170" class="form-control">
    </div>
    </div>
    
    <div class="col-sm-4">
    <!-- e-mail -->
    <div class="form-group">
        <label for="171"><?=$field['171']['label']?></label>
        <input type="email" name="171" id="171" class="form-control">
    </div>
    </div>

    <?php if ($watcher_option): ?>
        <div class="col-sm-12">
        <div class="checkbox">
            <label class="check-box">
                <input type="checkbox" name="watcher" value="1" checked>
                <span class="unchecked"><span class="glyphicon glyphicon-ok"></span></span>
                <span>Notify me when a <?=h(mb_strtolower($comment_label))?> is added.</span>
            </label>
        </div>
        </div>
    <?php endif ?>
    
    <?php if ($captcha_question): ?>

        <div class="col-sm-12">
        <h5>To prevent spam, please tell us:</h5>

        <div class="form-group">
            <label for="captcha_submitted_answer"><?=h($captcha_question)?>*</label>
            <input type="number" name="captcha_submitted_answer" id="captcha_submitted_answer" class="form-control">
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
