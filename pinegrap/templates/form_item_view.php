
<?=$messages?>

<?=$content?>

<?php if ($auto_registration): ?>

    <h2>New Account</h2>

    <p>We have created a new account for you on our site. You can find your login info below.</p>

    <dl class="dl-horizontal">

        <dt>Email</dt>
        <dd><?=h($auto_registration_email_address)?></dd>

        <dt>Password</dt>
        <dd><?=h($auto_registration_password)?></dd>
    </dl>

<?php endif ?>

<div class="form-group">

    <?php if ($back_button_url): ?>
        <a href="<?=h($back_button_url)?>" class="btn btn-default btn-secondary">
            Back
        </a>
    <?php endif ?>

    <?php if ($edit_button_url): ?>
        <a href="<?=h($edit_button_url)?>" class="btn btn-primary">
            Edit
        </a>
    <?php endif ?>

</div>