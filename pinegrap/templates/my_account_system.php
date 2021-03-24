
<?=$messages?>

<div style="margin-top: 1em">
    
    <div class="account mobile_width" style="margin-bottom: 1em; display: inline-block; vertical-align: top; width: 46%; margin-right: 5%">
        
        <div class="heading">

            Account

            <?php if ($logout_url): ?>
                <a class="software_button_tiny_secondary" href="<?=h($logout_url)?>">
                    Logout
                </a>
            <?php endif ?>

            <?php if ($change_password_url): ?>
                <a href="<?=h($change_password_url)?>" class="software_button_tiny_secondary">
                    Change Password
                </a>
            <?php endif ?>

        </div>

        <table class="data" style="margin-bottom: 2em">

            <tr>
                <td>Username:&nbsp;</td>
                <td><?=h(USER_USERNAME)?></td>
            </tr>

            <?php if ($start_page_url): ?>
                <tr>
                    <td>My Start Page:&nbsp;</td>
                    <td><a href="<?=h($start_page_url)?>"><?=h($start_page_name)?></a></td>
                </tr>
            <?php endif ?>

            <?php if ($reward_points): ?>
                <tr>
                    <td>Reward Points:&nbsp;</td>
                    <td><?=h(number_format($reward_points))?></td>
                </tr>
            <?php endif ?>

        </table>

    </div>

    <div class="email_preferences mobile_width" style="margin-bottom: 1em; display: inline-block; vertical-align: top; width: 46%">
        
        <div class="heading">

            E-mail Preferences

            <?php if ($email_preferences_url): ?>
                <a href="<?=h($email_preferences_url)?>" class="software_button_tiny_secondary">
                    Update
                </a>
            <?php endif ?>

        </div>

        <div class="data" style="margin-bottom: 2em">
            <?=h(USER_EMAIL_ADDRESS)?>
        </div>

    </div>

</div>

<div class="my_profile mobile_width" style="margin-bottom: 1em; display: inline-block; vertical-align: top; width: 46%; margin-right: 5%">
    
    <div class="heading">

        Profile

        <?php if ($my_account_profile_url): ?>
            <a href="<?=h($my_account_profile_url)?>" class="software_button_tiny_secondary">
                Update
            </a>
        <?php endif ?>

    </div>

    <div class="data" style="margin-bottom: 2em">

        <?php if ($first_name or $last_name): ?>
            <?=h($first_name)?> <?=h($last_name)?><br>
        <?php endif ?>

        <?php if ($title): ?>
            <?=h($title)?><br>
        <?php endif ?>

        <?php if ($company): ?>
            <?=h($company)?><br>
        <?php endif ?>

        <?php if ($business_address_1): ?>
            <?=h($business_address_1)?><br>
        <?php endif ?>

        <?php if ($business_address_2): ?>
            <?=h($business_address_2)?><br>
        <?php endif ?>

        <?php if ($business_city): ?>
            <?=h($business_city)?><?php if ($business_state or $business_zip_code): ?>, <?php endif ?>
        <?php endif ?>

        <?php if ($business_state): ?>
            <?=h($business_state)?>
        <?php endif ?>

        <?php if ($business_zip_code): ?>
            <?=h($business_zip_code)?>
        <?php endif ?>

        <?php if (
            $business_city
            or $business_state
            or $business_zip_code
        ):?>
            <br>
        <?php endif ?>

        <?php if ($business_country): ?>
            <?=h($business_country)?><br>
        <?php endif ?>

        <?php if ($business_phone): ?>
            Main: <?=h($business_phone)?><br>
        <?php endif ?>

        <?php if ($mobile_phone): ?>
            Mobile: <?=h($mobile_phone)?><br>
        <?php endif ?>

        <?php if ($home_phone): ?>
            Home: <?=h($home_phone)?><br>
        <?php endif ?>

        <?php if ($business_fax): ?>
            Fax: <?=h($business_fax)?><br>
        <?php endif ?>

        <?php if ($timezone): ?>
            Timezone: <?=h($timezone)?><br>
        <?php endif ?>

    </div>

</div>

<?php if (USER_MEMBER_ID): ?>

    <div class="membership mobile_width" style="margin-bottom: 1em; display: inline-block; vertical-align: top; width: auto;">
        
        <div class="heading">Membership</div>
        
        <div class="data" style="margin-bottom: 2em">

            <table>

                <tr>
                    <td><?=h(MEMBER_ID_LABEL)?>:</td>
                    <td><?=h(USER_MEMBER_ID)?></td>
                </tr>

                <tr>
                    <td>Expiration Date:&nbsp;</td>
                    <td>
                        
                        <?php if (USER_EXPIRATION_DATE == '0000-00-00'): ?>

                            [None]

                        <?php else: ?>

                            <?=get_absolute_time(array(
                                'timestamp' => strtotime(USER_EXPIRATION_DATE),
                                'type' => 'date'))?>

                        <?php endif ?>

                    </td>
                </tr>

            </table>

        </div>

    </div>

<?php endif ?>

<?php if ($affiliate): ?>

    <div class="affiliate">

        <div class="heading">Affiliate</div>

        <div class="data" style="margin-bottom: 2em">

            <table>
                <tr>
                    <td>Affiliate Name:</td>
                    <td><?=h($affiliate_name)?></td>
                </tr>
                <tr>
                    <td>Affiliate Code:</td>
                    <td><?=h($affiliate_code)?></td>
                </tr>
                <tr>
                    <td>Affiliate Link:</td>
                    <td><a href="<?=h($affiliate_url)?>" target="_blank"><?=h($affiliate_url)?></a></td>
                </tr>
                <tr>
                    <td>Commission Rate:&nbsp;</td>
                    <td><?=h($affiliate_commission_rate)?>%</td>
                </tr>
                <tr>
                    <td>Pending Total:</td>
                    <td><?=BASE_CURRENCY_SYMBOL . number_format($affiliate_pending_total, 2)?></td>
                </tr>
                <tr>
                    <td>Payable Total:</td>
                    <td><?=BASE_CURRENCY_SYMBOL . number_format($affiliate_payable_total, 2)?></td>
                </tr>
                <tr>
                    <td>Paid Total:</td>
                    <td><?=BASE_CURRENCY_SYMBOL . number_format($affiliate_paid_total, 2)?></td>
                </tr>
            </table>

            <?php if ($commissions): ?>

                <div class="commissions">

                    <div class="heading">Commissions</div>

                    <div class="data" style="padding-left: 20px; margin-bottom: 2em">

                        <table style="width: 100%">

                            <tr>
                                <th style="text-align: left">Reference Code</th>
                                <th style="text-align: left">Date &amp; Time</th>
                                <th style="text-align: left">Status</th>
                                <th style="text-align: right">Amount</th>
                            </tr>

                            <?php foreach($commissions as $commission): ?>

                                <tr>
                                    <td><?=h($commission['reference_code'])?></td>
                                    <td>
                                        <?=get_relative_time(array(
                                            'timestamp' => $commission['created_timestamp']))?>
                                    </td>
                                    <td><?=h($commission['status_label'])?></td>
                                    <td style="text-align: right">
                                        <?=BASE_CURRENCY_SYMBOL . number_format($commission['amount'], 2)?>
                                    </td>
                                </tr>

                            <?php endforeach ?>
                            
                        </table>

                    </div>

                </div>

            <?php endif ?>

        </div>

    </div>

<?php endif ?>

<?php if ($complete_orders or $incomplete_orders): ?>

    <div class="order_history">

        <div class="heading" style="border: none; padding-bottom: .5em">Order History</div>

        <div class="data" style="margin-bottom: 2em">

            <?php if ($complete_orders): ?>
            
                <div class="complete_orders">
                    <div class="heading" style="margin-bottom: 7px">Complete Orders</div>
                    <div class="data" style="padding-left: 20px; margin-bottom: 15px">

                        <table style="width: 100%">

                            <tr>
                                <th style="text-align: left">Order Number</th>
                                <th style="text-align: left">Date &amp; Time</th>
                                <th style="text-align: right">Total</th>
                                <th style="text-align: left">&nbsp;</th>
                            </tr>

                            <?php foreach($complete_orders as $key => $order): ?>

                                <tr class="row_<?=($key + 1) % 2?>">
                                    <td style="vertical-align: top"><?=h($order['order_number'])?></td>
                                    <td style="vertical-align: top">
                                        <?=get_relative_time(array(
                                            'timestamp' => $order['order_date']))?>
                                    </td>
                                    <td style="text-align: right; vertical-align: top">
                                        <?=$order['total_info']?>
                                    </td>
                                    <td style="text-align: center; vertical-align: top">

                                        <?php if ($order['view_url']): ?>
                                            <a href="<?=h($order['view_url'])?>" class="software_button_small_secondary" style="margin: 0 .5em .5em 0">
                                                View
                                            </a>
                                        <?php endif ?>

                                        <a href="<?=h($order['reorder_url'])?>" class="software_button_small_secondary" style="margin: 0 .5em .5em 0">
                                            Reorder
                                        </a>

                                    </td>
                                </tr>

                            <?php endforeach ?>

                        </table>

                    </div>
                </div>

            <?php endif ?>

            <?php if ($incomplete_orders): ?>
            
                <div class="incomplete_orders">
                    <div class="heading" style="margin-bottom: 7px">Incomplete Orders</div>
                    <div class="data" style="padding-left: 20px; margin-bottom: 15px">

                        <table style="width: 100%">

                            <tr>
                                <th style="text-align: left">Reference Code</th>
                                <th style="text-align: left">Date &amp; Time</th>
                                <th style="text-align: left">&nbsp;</th>
                            </tr>

                            <?php foreach($incomplete_orders as $key => $order): ?>

                                <tr class="row_<?=($key + 1) % 2?>">
                                    <td style="vertical-align: top"><?=h($order['reference_code'])?></td>
                                    <td style="vertical-align: top">
                                        <?=get_relative_time(array(
                                            'timestamp' => $order['order_date']))?>
                                    </td>
                                    <td style="vertical-align: top">

                                        <?php if ($order['view_url']): ?>
                                            <a href="<?=h($order['view_url'])?>" class="software_button_small_secondary" style="margin: 0 .5em .5em 0">
                                                View
                                            </a>
                                        <?php endif ?>

                                        <?php if ($order['active']): ?>
                                            <span style="white-space: nowrap; background: none !important; background-color: none !important">

                                                <?php if ($order['order_url']): ?>
                                                    <a href="<?=h($order['order_url'])?>" class="software_button_small_primary" style="margin: 0 .5em .5em 0">&nbsp;&nbsp;Order&nbsp;&nbsp;</a>
                                                <?php else: ?>
                                                    <a href="#" class="software_button_small_secondary" style="margin: 0 .5em .5em 0">&nbsp;Active&nbsp;&nbsp;</a>
                                                <?php endif ?>

                                            </span>

                                        <?php else: ?>
                                            <a href="<?=h($order['retrieve_url'])?>" class="software_button_small_secondary" style="margin: 0 .5em .5em 0">
                                                Retrieve
                                            </a>
                                        <?php endif ?>

                                        <a href="<?=h($order['delete_url'])?>" class="software_button_small_secondary" style="margin: 0 .5em .5em 0" onclick="return confirm('The order will be deleted.')">
                                            Delete
                                        </a>

                                    </td>
                                </tr>

                            <?php endforeach ?>

                        </table>

                    </div>
                </div>

            <?php endif ?>

        </div>

    </div>

<?php endif ?>

<?php if ($address_book): ?>

    <div class="address_book">

        <div class="heading">

            Address Book

            <?php if ($update_address_book_url): ?>
                <a href="<?=h($update_address_book_url)?>" class="software_button_tiny_secondary">
                    Add Recipient
                </a>
            <?php endif ?>

        </div>

        <?php if ($recipients): ?>

            <table class="data" style="width: 100%">

                <tr>
                    <th style="text-align: left">Ship to Name</th>
                    <th style="text-align: left">Full Name</th>
                    <th style="text-align: left">Delivery Address</th>
                    <th style="text-align: left">&nbsp;</th>
                </tr>

                <?php foreach($recipients as $key => $recipient): ?>

                    <tr class="row_<?=($key + 1) % 2?>">
                        <td style="vertical-align: top"><?=h($recipient['ship_to_name'])?></td>
                        <td style="vertical-align: top">

                            <?=h($recipient['salutation'])?>
                            <?=h($recipient['first_name'])?>
                            <?=h($recipient['last_name'])?>

                        </td>
                        <td style="vertical-align: top">
                            
                            <?php if ($recipient['company']): ?>
                                <?=h($recipient['company'])?><br>
                            <?php endif ?>

                            <?=h($recipient['address_1'])?><br>

                            <?php if ($recipient['address_2']): ?>
                                <?=h($recipient['address_2'])?><br>
                            <?php endif ?>

                            <?=h($recipient['city'])?>,
                            <?=h($recipient['state'])?>
                            <?=h($recipient['zip_code'])?><br>
                            <?=h($recipient['country'])?><br>

                        </td>
                        <td style="vertical-align: top; padding-top: 5px">

                            <?php if ($recipient['update_url']): ?>
                                <a href="<?=h($recipient['update_url'])?>" class="software_button_small_secondary" style="margin: 0 .5em .5em 0">
                                    Update
                                </a>
                            <?php endif ?>

                            <a href="<?=h($recipient['remove_url'])?>" class="software_button_small_secondary" style="margin: 0 .5em .5em 0" onclick="return confirm('<?=h(escape_javascript($recipient['ship_to_name']))?> will be removed.')">
                                Remove
                            </a>

                        </td>
                    </tr>

                <?php endforeach ?>

            </table>

        <?php endif ?>
            
    </div>

<?php endif ?>


