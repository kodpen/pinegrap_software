
<?=$messages?>

<div class="row">

    <div class="col-sm-6">

        <h2>

            Account

            <?php if ($logout_url): ?>
                <a href="<?=h($logout_url)?>" class="btn btn-default btn-secondary btn-xs">
                    Logout
                </a>
            <?php endif ?>

            <?php if ($change_password_url): ?>
                <a href="<?=h($change_password_url)?>" class="btn btn-default btn-secondary btn-xs">
                    Change Password
                </a>
            <?php endif ?>

        </h2>

        <dl class="dl-horizontal">

            <dt>Username</dt>
            <dd><?=h(USER_USERNAME)?></dd>

            <?php if ($start_page_url): ?>
                <dt>My Start Page</dt>
                <dd><a href="<?=h($start_page_url)?>"><?=h($start_page_name)?></a></dd>
            <?php endif ?>

            <?php if ($reward_points): ?>
                <dt>Reward Points</dt>
                <dd><?=h(number_format($reward_points))?></dd>
            <?php endif ?>

        </dl>

    </div>

    <div class="col-sm-6">

        <h2>
            Email Preferences

            <?php if ($email_preferences_url): ?>
                <a href="<?=h($email_preferences_url)?>" class="btn btn-default btn-secondary btn-xs">
                    Update
                </a>
            <?php endif ?>
        </h2>

        <p><?=h(USER_EMAIL_ADDRESS)?></p>

    </div>

</div>

<div class="row">

    <div class="col-sm-6">

        <h2>

            Profile

            <?php if ($my_account_profile_url): ?>
                <a href="<?=h($my_account_profile_url)?>" class="btn btn-default btn-secondary btn-xs">
                    Update
                </a>
            <?php endif ?>

        </h2>

        <dl class="dl-horizontal">

            <?php if (
                $first_name or $last_name or $title or $company or $business_address_1
                or $business_address_2 or $business_city or $business_state or $business_zip_code
                or $business_country
            ):?>

                <dt>Contact</dt>
                <dd>

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

                </dd>

            <?php endif ?>

            <?php if ($business_phone): ?>
                <dt>Main</dt>
                <dd><?=h($business_phone)?></dd>
            <?php endif ?>

            <?php if ($mobile_phone): ?>
                <dt>Mobile</dt>
                <dd><?=h($mobile_phone)?></dd>
            <?php endif ?>

            <?php if ($home_phone): ?>
                <dt>Home</dt>
                <dd><?=h($home_phone)?></dd>
            <?php endif ?>

            <?php if ($business_fax): ?>
                <dt>Fax</dt>
                <dd><?=h($business_fax)?></dd>
            <?php endif ?>

            <?php if ($timezone): ?>
                <dt>Timezone</dt>
                <dd><?=h($timezone)?></dd>
            <?php endif ?>

        </dl>

    </div>

    <?php if (USER_MEMBER_ID): ?>

        <div class="col-sm-6">

            <h2>Membership</h2>

            <dl class="dl-horizontal">

                <dt><?=h(MEMBER_ID_LABEL)?></dt>
                <dd><?=h(USER_MEMBER_ID)?></dd>

                <dt>Expiration Date</dt>
                <dd>

                    <?php if (USER_EXPIRATION_DATE == '0000-00-00'): ?>

                        [None]

                    <?php else: ?>

                        <?=get_absolute_time(array(
                            'timestamp' => strtotime(USER_EXPIRATION_DATE),
                            'type' => 'date'))?>

                    <?php endif ?>

                </dd>

            </dl>

        </div>

    <?php endif ?>

</div>

<?php if ($affiliate): ?>

    <h2>Affiliate</h2>

    <dl class="dl-horizontal">

        <dt>Affiliate Name</dt>
        <dd><?=h($affiliate_name)?></dd>

        <dt>Affiliate Code</dt>
        <dd><?=h($affiliate_code)?></dd>

        <dt>Affiliate Link</dt>
        <dd><a href="<?=h($affiliate_url)?>" target="_blank"><?=h($affiliate_url)?></a></dd>

        <dt>Commission Rate</dt>
        <dd><?=h($affiliate_commission_rate)?>%</dd>

        <dt>Pending Total</dt>
        <dd><?=BASE_CURRENCY_SYMBOL . number_format($affiliate_pending_total, 2)?></dd>

        <dt>Payable Total</dt>
        <dd><?=BASE_CURRENCY_SYMBOL . number_format($affiliate_payable_total, 2)?></dd>

        <dt>Paid Total</dt>
        <dd><?=BASE_CURRENCY_SYMBOL . number_format($affiliate_paid_total, 2)?></dd>

    </dl>

    <?php if ($commissions): ?>

        <h3>Commissions</h3>

        <div class="table-responsive">

            <table class="table table-striped">

                <tr>
                    <th>Reference Code</th>
                    <th>Date &amp; Time</th>
                    <th>Status</th>
                    <th class="text-right">Amount</th>
                </tr>

                <?php foreach($commissions as $commission): ?>

                    <tr>
                        <td><?=h($commission['reference_code'])?></td>
                        <td>
                            <?=get_relative_time(array(
                                'timestamp' => $commission['created_timestamp']))?>
                        </td>
                        <td><?=h($commission['status_label'])?></td>
                        <td class="text-right">
                            <?=BASE_CURRENCY_SYMBOL . number_format($commission['amount'], 2)?>
                        </td>
                    </tr>

                <?php endforeach ?>

            </table>

        </div>

    <?php endif ?>

<?php endif ?>

<?php if ($complete_orders or $incomplete_orders): ?>

    <h2>Order History</h2>

    <?php if ($complete_orders): ?>

        <h3>Complete Orders</h3>

        <div class="table-responsive">

            <table class="table table-striped">

                <tr>
                    <th>Order Number</th>
                    <th>Date &amp; Time</th>
                    <th class="text-right">Total</th>
                    <th></th>
                </tr>

                <?php foreach($complete_orders as $order): ?>

                    <tr>
                        <td><?=h($order['order_number'])?></td>
                        <td>
                            <?=get_relative_time(array(
                                'timestamp' => $order['order_date']))?>
                        </td>
                        <td class="text-right">
                            <?=$order['total_info']?>
                        </td>
                        <td class="text-center">

                            <?php if ($order['view_url']): ?>
                                <a href="<?=h($order['view_url'])?>" class="btn btn-default btn-secondary btn-sm">
                                    View
                                </a>
                            <?php endif ?>

                            <a href="<?=h($order['reorder_url'])?>" class="btn btn-default btn-secondary btn-sm">
                                Reorder
                            </a>

                        </td>
                    </tr>

                <?php endforeach ?>

            </table>

        </div>

    <?php endif ?>

    <?php if ($incomplete_orders): ?>

        <h3>Incomplete Orders</h3>

        <div class="table-responsive">

            <table class="table table-striped">

                <tr>
                    <th>Reference Code</th>
                    <th>Date &amp; Time</th>
                    <th></th>
                </tr>

                <?php foreach($incomplete_orders as $order): ?>

                    <tr>
                        <td><?=h($order['reference_code'])?></td>
                        <td>
                            <?=get_relative_time(array(
                                'timestamp' => $order['order_date']))?>
                        </td>
                        <td class="text-center">

                            <?php if ($order['view_url']): ?>
                                <a href="<?=h($order['view_url'])?>" class="btn btn-default btn-secondary btn-sm">
                                    View
                                </a>
                            <?php endif ?>

                            <?php if ($order['active']): ?>
                                <?php if ($order['order_url']): ?>
                                    <a href="<?=h($order['order_url'])?>" class="btn btn-primary btn-sm">
                                        Order
                                    </a>
                                <?php else: ?>
                                    <a href="#" class="btn btn-primary btn-sm disabled">
                                        Active
                                    </a>
                                <?php endif ?>

                            <?php else: ?>
                                <a href="<?=h($order['retrieve_url'])?>" class="btn btn-default btn-secondary btn-sm">
                                    Retrieve
                                </a>
                            <?php endif ?>

                            <a href="<?=h($order['delete_url'])?>" class="btn btn-default btn-secondary btn-sm" onclick="return confirm('The order will be deleted.')">
                                Delete
                            </a>

                        </td>
                    </tr>

                <?php endforeach ?>

            </table>

        </div>

    <?php endif ?>

<?php endif ?>

<?php if ($address_book): ?>

    <h2>

        Address Book

        <?php if ($update_address_book_url): ?>
            <a href="<?=h($update_address_book_url)?>" class="btn btn-default btn-secondary btn-xs">
                Add Recipient
            </a>
        <?php endif ?>

    </h2>

    <?php if ($recipients): ?>

        <div class="table-responsive">

            <table class="table table-striped">

                <tr>
                    <th>Ship to Name</th>
                    <th>Full Name</th>
                    <th>Delivery Address</th>
                    <th></th>
                </tr>

                <?php foreach($recipients as $recipient): ?>

                    <tr>
                        <td><?=h($recipient['ship_to_name'])?></td>
                        <td>

                            <?=h($recipient['salutation'])?>
                            <?=h($recipient['first_name'])?>
                            <?=h($recipient['last_name'])?>

                        </td>
                        <td>

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
                        <td class="text-center">

                            <?php if ($recipient['update_url']): ?>
                                <a href="<?=h($recipient['update_url'])?>" class="btn btn-default btn-secondary btn-sm">
                                    Update
                                </a>
                            <?php endif ?>

                            <a href="<?=h($recipient['remove_url'])?>" class="btn btn-default btn-secondary btn-sm" onclick="return confirm('<?=h(escape_javascript($recipient['ship_to_name']))?> will be removed.')">
                                Remove
                            </a>

                        </td>
                    </tr>

                <?php endforeach ?>

            </table>

        </div>

    <?php endif ?>

<?php endif ?>
