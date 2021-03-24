
<?=$messages?>

<div class="row">

    <div class="col-sm-6">

        <h2>Login</h2>

        <form <?=$attributes?>>
            
            <div class="form-group">
                <label for="login_email_address">Email</label>
                <input type="text" name="u" id="login_email_address" class="form-control">
            </div>

            <div class="form-group">
                <label for="login_password">Password</label>
                <input type="password" name="p" id="login_password" class="form-control">
            </div>

            <?php if (REMEMBER_ME): ?>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="login_remember_me" value="1">
                        Remember Me
                    </label>
                </div>
            <?php endif ?>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
            
            <!-- Required hidden fields (do not remove) -->
            <?=$login_system?>
            
        </form>

        <?php if ($forgot_password_url): ?>
            <p><a href="<?=h($forgot_password_url)?>">Forgot password?</a></p>
        <?php endif ?>

    </div>

    <div class="col-sm-6">

        <h2>Register</h2>

        <form <?=$attributes?>>

            <div class="form-group">
                <label for="member_id"><?=h(MEMBER_ID_LABEL)?>*</label>
                <input type="text" name="member_id" id="member_id" class="form-control">
            </div>
            
            <div class="form-group">
                <label for="first_name">First Name*</label>
                <input type="text" name="first_name" id="first_name" class="form-control">
            </div>

            <div class="form-group">
                <label for="last_name">Last Name*</label>
                <input type="text" name="last_name" id="last_name" class="form-control">
            </div>

            <div class="form-group">
                <label for="username">Username*</label>
                <input type="text" name="username" id="username" class="form-control">
            </div>

            <div class="form-group">
                <label for="register_email_address">Email*</label>
                <input type="email" name="email_address" id="register_email_address" class="form-control">
            </div>

            <div class="form-group">
                <label for="email_address_verify">Confirm Email*</label>
                <input type="email" name="email_address_verify" id="email_address_verify" class="form-control">
            </div>

            <?php if ($strong_password_help): ?>
                <?=$strong_password_help?>
            <?php endif ?>

            <div class="form-group">
                <label for="register_password">Password*</label>
                <input type="password" name="password" id="register_password" class="form-control">
            </div>

            <div class="form-group">
                <label for="password_verify">Confirm Password*</label>
                <input type="password" name="password_verify" id="password_verify" class="form-control">
            </div>

            <?php if (PASSWORD_HINT): ?>
                <div class="form-group">
                    <label for="password_hint">Password Hint</label>
                    <input type="text" name="password_hint" id="password_hint" class="form-control">
                </div>
            <?php endif ?>

            <?php if (REMEMBER_ME): ?>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="register_remember_me" value="1">
                        Remember Me
                    </label>
                </div>
            <?php endif ?>

            <div class="checkbox">
                <label>
                    <input type="checkbox" name="opt_in" value="1">
                    <?=h($opt_in_label)?>
                </label>
            </div>

            <button type="submit" class="btn btn-primary">Register</button>
            
            <!-- Required hidden fields (do not remove) -->
            <?=$register_system?>
            
        </form>

    </div>

</div>
