
<?=$messages?>

<form <?=$attributes?>>
    
    <p><?=h($email)?></p>

    <?php if ($strong_password_help): ?>
        <?=$strong_password_help?>
    <?php endif ?>

    <input type="password" name="new_password" id="new_password" placeholder="New Password*">
    

    <?php if (PASSWORD_HINT): ?>
    	<input type="text" name="password_hint" id="password_hint" placeholder="Password Hint (optional)">
    <?php endif ?>

    <button type="submit" class="btn btn-primary">Set Password</button>
    
    <!-- Required hidden fields (do not remove) -->
    <?=$system?>
    
</form>
