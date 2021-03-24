<table>
    <tr>

        <td>
            <ul>

                <?php if (USER_MANAGE_ECOMMERCE): ?>
                    <li><a href="view_orders.php">All Orders</a></li>
                <?php endif ?>

                <?php if (USER_MANAGE_ECOMMERCE_REPORTS): ?>
                    <li><a href="view_order_reports.php">All Order Reports</a></li>
                <?php endif ?>

                <?php if (USER_MANAGE_ECOMMERCE and AFFILIATE_PROGRAM): ?>
                    <li><a href="view_commissions.php">All Commissions</a></li>
                    <li><a href="view_recurring_commission_profiles.php">All Commission Profiles</a></li>
                <?php endif ?>
                
            </ul>
        </td>

        <?php if (USER_MANAGE_ECOMMERCE): ?>
            <td>
                <ul>
                    <li><a href="view_products.php">All Products</a></li>
                    <li><a href="view_product_groups.php">All Product Groups</a></li>
                    <li><a href="view_product_attributes.php">All Product Attributes</a></li>
                    <li><a href="view_gift_cards.php">All Gift Cards</a></li>
                </ul>
            </td>
            <td>
                <ul>
                    <li><a href="view_offers.php">All Offers</a></li>
                    <li><a href="view_offer_rules.php">All Offer Rules</a></li>
                    <li><a href="view_offer_actions.php">All Offer Actions</a></li>
                    <li><a href="view_key_codes.php">All Key Codes</a></li>
                </ul>
            </td>
        <?php endif ?>

        <?php if (USER_MANAGE_ECOMMERCE and ECOMMERCE_SHIPPING): ?>
            <td>
                <ul>
                    <li><a href="view_zones.php">All Shipping Zones</a></li>
                    <li><a href="view_shipping_methods.php">All Shipping Methods</a></li>
                    <li><a href="view_arrival_dates.php">All Shipping Arrival Dates</a></li>
                    <li><a href="view_verified_shipping_addresses.php">All Verified Shipping Addresses</a></li>
                </ul>
            </td>
        <?php endif ?>

        <td>
            <ul>

                <?php if (USER_MANAGE_ECOMMERCE and ECOMMERCE_SHIPPING): ?>
                    <li><a href="view_containers.php">All Containers</a></li>
                <?php endif ?>

                <?php if (USER_MANAGE_ECOMMERCE_REPORTS and ECOMMERCE_SHIPPING): ?>
                    <li><a href="view_shipping_report.php">Shipping Report</a></li>
                <?php endif ?>

                <?php if (USER_MANAGE_ECOMMERCE): ?>
                    <li><a href="view_tax_zones.php">All Tax Zones</a></li>
                <?php endif ?>

            </ul>
        </td>

        <?php if (USER_MANAGE_ECOMMERCE): ?>
            <td>
                <ul>
                    <li><a href="view_referral_sources.php">All Referral Sources</a></li>
                    <li><a href="view_currencies.php">All Currencies</a></li>
                    <li><a href="view_countries.php">All Countries</a></li>
                    <li><a href="view_states.php">All States</a></li>
                </ul>
            </td>
        <?php endif ?>

    </tr>
</table>