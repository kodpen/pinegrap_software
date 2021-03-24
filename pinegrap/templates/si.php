
<div class="site_info">

    <h1>Site Info</h1>

    <div class="left">

        <h2>General</h2>

        <dl>

            <dt>Hostname</dt>
            <dd><?=h(HOSTNAME_SETTING)?></dd>

            <dt>Hosted</dt>
            <dd>
                <?php if (we_host()): ?>
                    Yes
                <?php else: ?>
                    No
                <?php endif ?>
            </dd>

            <dt>Website IP Address</dt>
            <dd><?=h($_SERVER['SERVER_ADDR'])?></dd>

            <dt>Secure Mode</dt>
            <dd>
                <?php if (URL_SCHEME == 'https://'): ?>
                    On
                <?php else: ?>
                    Off
                <?php endif ?>
            </dd>

            <dt>Version</dt>
            <dd><?=h(VERSION)?> <?=h(EDITION)?></dd>

            <dt>Disk Usage</dt>
            <dd><?=h(convert_bytes_to_string($disk_usage, 2))?></dd>

            <dt>Private Label</dt>
            <dd>
                <?php if (PRIVATE_LABEL): ?>
                    On
                <?php else: ?>
                    Off
                <?php endif ?>
            </dd>

            <dt>Environment</dt>
            <dd>
                <?php if (ENVIRONMENT == 'development'): ?>
                    development
                <?php else: ?>
                    production
                <?php endif ?>
            </dd>

            <dt>Email Campaign Job</dt>
            <dd>
                <?php if (defined('EMAIL_CAMPAIGN_JOB') and EMAIL_CAMPAIGN_JOB): ?>
                    On
                <?php else: ?>
                    Off
                <?php endif ?>
            </dd>

        </dl>

        <h2>Server</h2>

        <dl>

            <dt>PHP Version</dt>
            <dd><?=h(phpversion())?></dd>

            <dt>MySQL Version</dt>
            <dd><?=h(db("SELECT VERSION()"))?></dd>

            <dt>System</dt>
            <dd><?=h(php_uname())?></dd>

            <dt>Operating System</dt>
            <dd><?=h(PHP_OS)?></dd>

            <dt>Web Server</dt>
            <dd><?=h($_SERVER['SERVER_SOFTWARE'])?></dd>

        </dl>

    </div>

    <div class="clear"></div>

    <h2>PHP</h2>

    <iframe src="pi.php"></iframe>

</div>
