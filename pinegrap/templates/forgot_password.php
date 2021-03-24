
<?=$messages?>

<form <?=$attributes?>>

    <?php if (!$screen): ?>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control">
        </div>
        
        <button type="submit" class="btn btn-primary">Send Email</button>

    <?php elseif ($screen == 'password_hint'): ?>

        <p>Your Password Hint is: <strong><?=h($password_hint)?></strong></p>

        <p>Do you remember your password now?</p>

        <a href="<?=h($send_to)?>" class="btn btn-primary">Yes, I Remember</a>

        <button type="submit" class="btn btn-primary">No, I Forget</button>

    <?php endif ?>
    
    <!-- Required hidden fields (do not remove) -->
    <?=$system?>
    
</form>
