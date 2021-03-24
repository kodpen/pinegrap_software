
<?=$messages?>

<form <?=$attributes?>>

    <?php if ($strong_password_help): ?>
        <?=$strong_password_help?>
    <?php endif ?>

    <div class="form-group">
        <label for="new_password">New Password*</label>
        <input type="password" name="new_password" id="new_password" class="form-control">
    </div>

    <div class="form-group">
        <label for="new_password_verify">Confirm New Password*</label>
        <input type="password" name="new_password_verify" id="new_password_verify" class="form-control">
    </div>

    <?php if (PASSWORD_HINT): ?>
        <div class="form-group">
            <label for="password_hint">Password Hint</label>
            <input type="text" name="password_hint" id="password_hint" class="form-control">
        </div>
    <?php endif ?>

    <button type="submit" class="btn btn-primary">Change Password</button>
    
    <!-- Required hidden fields (do not remove) -->
    <?=$system?>
    
</form>
