
<?=$messages?>

<form <?=$attributes?>>
    
    <div class="form-group">
        <label for="email">Email</label>
        <input
            type="email"
            id="email"
            name="email"
            class="form-control"
            autocomplete="email"
            spellcheck="false">
    </div>

    <div class="form-group">
        <label for="password">Password</label>
        <input
            type="password"
            id="password"
            name="password"
            class="form-control"
            autocomplete="current-password"
            spellcheck="false">
    </div>

    <?php if (REMEMBER_ME): ?>
        <div class="checkbox">
            <label>
                <input type="checkbox" name="remember_me" value="1">
                Remember Me
            </label>
        </div>
    <?php endif ?>

    <div class="form-group">
        <button type="submit" class="btn btn-primary">Login</button>
    </div>
    
    <!-- Required hidden fields (do not remove) -->
    <?=$system?>
    
</form>

<?php if ($forgot_password_url): ?>
    <p><a href="<?=h($forgot_password_url)?>">Forgot password?</a></p>
<?php endif ?>
