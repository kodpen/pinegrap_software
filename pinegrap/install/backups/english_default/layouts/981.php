
<?=$messages?>

<form <?=$attributes?>>

    <?php if (!$screen): ?>

      	<input type="email" name="email" id="email" placeholder="User Email Address">
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
