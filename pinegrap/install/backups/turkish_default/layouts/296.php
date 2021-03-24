
<?=$messages?>

<form <?=$attributes?>>

    <!-- first_name -->
    <div class="form-group">
        <label for="40"><?=$field['40']['label']?></label>
        <input type="text" name="40" id="40" class="form-control">
    </div>
    
    <!-- last_name -->
    <div class="form-group">
        <label for="41"><?=$field['41']['label']?></label>
        <input type="text" name="41" id="41" class="form-control">
    </div>
    
    <!-- title -->
    <div class="form-group">
        <label for="42"><?=$field['42']['label']?></label>
        <input type="text" name="42" id="42" class="form-control">
    </div>
    
    <!-- phone -->
    <div class="form-group">
        <label for="47"><?=$field['47']['label']?></label>
        <input type="text" name="47" id="47" class="form-control">
    </div>
    
    <!-- bio -->
    <div class="form-group">
        <label for="43"><?=$field['43']['label']?></label>
        <textarea name="43" id="43" class="form-control"></textarea>
    </div>
    
    <!-- photo -->
    <div class="form-group">
        <label for="44"><?=$field['44']['label']?></label>
        <input type="file" name="44" id="44" class="form-control">
    </div>
    
    <!-- twitter -->
    <div class="form-group">
        <label for="323"><?=$field['323']['label']?></label>
        <input type="text" name="323" id="323" class="form-control">
    </div>
    
    <!-- linkedin -->
    <div class="form-group">
        <label for="324"><?=$field['324']['label']?></label>
        <input type="text" name="324" id="324" class="form-control">
    </div>
    
    <!-- alphabetical -->
    <div class="form-group">
        <label for="255"><?=$field['255']['label']?></label>
        <select name="255" id="255" class="form-control"></select>
    </div>
    
    <!-- sort -->
    <div class="form-group">
        <label for="321"><?=$field['321']['label']?></label>
        <input type="text" name="321" id="321" class="form-control">
    </div>
    

    <?php if ($watcher_option): ?>
        <div class="checkbox">
            <label>
                <input type="checkbox" name="watcher" value="1" checked>
                Bir <?=h(mb_strtolower($comment_label))?> eklendiğinde bana bildir.
            </label>
        </div>
    <?php endif ?>
    
    <?php if ($captcha_question): ?>

        <h5>Spam'ı önlemek için lütfen yanıt verin:</h5>

        <div class="form-group">
            <label for="captcha_submitted_answer"><?=h($captcha_question)?>*</label>
            <input type="number" name="captcha_submitted_answer" id="captcha_submitted_answer" class="form-control">
        </div>

    <?php endif ?>

    <?php
        // The name attribute on the buttons below is important for the system
        // to know which button was pressed.
    ?>

    <?php
        // If save-for-later is enabled for custom form then show button.
        if ($save):
    ?>
        <button type="submit" name="save_button" class="btn btn-default btn-secondary">
            Daha Sonra Kaydet
        </button>
    <?php endif ?>

    <button type="submit" name="submit_button" class="btn btn-primary">
        <?=h($submit_button_label)?>
    </button>

    <!-- Required hidden fields and JS (do not remove) -->
    <?=$system?>

</form>
