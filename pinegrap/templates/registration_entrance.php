
<?=$messages?>

<div class="row">

    <div class="col-sm-6">

        <h2>Login</h2>

        <form <?=$login_attributes?>>

            <div class="form-group">
                <label for="login_email">Email</label>
                <input
                    type="email"
                    id="login_email"
                    name="email"
                    class="form-control"
                    autocomplete="email"
                    spellcheck="false">
            </div>

            <div class="form-group">
                <label for="login_password">Password</label>
                <input
                    type="password"
                    id="login_password"
                    name="password"
                    class="form-control"
                    autocomplete="current-password"
                    spellcheck="false">
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

        <?php if ($guest): ?>

            <h2>Continue as a Guest</h2>

            <form <?=$guest_attributes?>>

                <button type="submit" class="btn btn-primary">Continue</button>
                
                <!-- Required hidden fields (do not remove) -->
                <?=$guest_system?>
                
            </form>

        <?php endif ?>

        <h2>Register</h2>

        <form <?=$register_attributes?>>

            <div class="form-group">
                <label for="first_name">First Name*</label>
                <input
                    type="text"
                    id="first_name"
                    name="first_name"
                    class="form-control"
                    autocomplete="given-name"
                    spellcheck="false">
            </div>

            <div class="form-group">
                <label for="last_name">Last Name*</label>
                <input
                    type="text"
                    id="last_name"
                    name="last_name"
                    class="form-control"
                    autocomplete="family-name"
                    spellcheck="false">
            </div>

            <div class="form-group">
                <label for="username">Username*</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    class="form-control"
                    autocomplete="username"
                    spellcheck="false">
            </div>

            <div class="form-group">
                <label for="register_email">Email*</label>
                <input
                    type="email"
                    id="register_email"
                    name="email"
                    class="form-control"
                    autocomplete="email"
                    spellcheck="false">
            </div>

            <div class="form-group">
                <label for="email_verify">Confirm Email*</label>
                <input
                    type="email"
                    id="email_verify"
                    name="email_verify"
                    class="form-control"
                    autocomplete="email"
                    spellcheck="false">
            </div>

            <?php if ($strong_password_help): ?>
                <?=$strong_password_help?>
            <?php endif ?>

            <div class="form-group">
                <label for="register_password">Password*</label>
                <input
                    type="password"
                    id="register_password"
                    name="password"
                    class="form-control"
                    autocomplete="new-password"
                    spellcheck="false">
            </div>

            <div class="form-group">
                <label for="password_verify">Confirm Password*</label>
                <input
                    type="password"
                    id="password_verify"
                    name="password_verify"
                    class="form-control"
                    autocomplete="new-password"
                    spellcheck="false">
            </div>

            <?php if (PASSWORD_HINT): ?>
                <div class="form-group">
                    <label for="password_hint">Password Hint</label>
                    <input
                        type="text"
                        id="password_hint"
                        name="password_hint"
                        class="form-control">
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

            <?php if ($captcha_question): ?>

                <h5>To prevent spam, please tell us:</h5>

                <div class="form-group">
                    <label for="captcha_submitted_answer"><?=h($captcha_question)?>*</label>
                    <input type="number" name="captcha_submitted_answer" id="captcha_submitted_answer" class="form-control">
                </div>

            <?php endif ?>

            <button type="submit" class="btn btn-primary">Register</button>
            
            <!-- Required hidden fields (do not remove) -->
            <?=$register_system?>
            
        </form>

    </div>

</div>
