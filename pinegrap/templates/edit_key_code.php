<div id="subnav">
    <h1>
        <?php if ($screen == 'create'): ?>
            [new key code]
        <?php else: ?>
            <?=h($key_code['code'])?>
        <?php endif ?>
    </h1>
</div>
<div id="content">
    <?=$liveform->get_messages()?>
    <a href="#" id="help_link">Help</a>
    <h1><?=ucfirst($screen)?> Key Code</h1>
    <div class="subheading" style="margin-bottom: 1.5em">
        <?php if ($screen == 'create'): ?>
            Create one or more key codes (offer code alias) to allow redeemed offers to be tracked by customer segments.
        <?php else: ?>
            Edit a key code (offer code alias) to allow redeemed offers to be tracked by customer segments.
        <?php endif ?>
    </div>
    <form method="post">
        <?=get_token_field()?>

        <?php if ($screen == 'create'): ?>
            <?=$liveform->field(array(
                    'type' => 'hidden',
                    'name' => 'limit'))?>
        <?php endif ?>

        <table class="field">

            <?php if ($screen == 'create'): ?>
                <tr>
                    <th colspan="2"><h2>Increase Quantity to Create Multiple Key Codes</h2></th>
                </tr>
                <tr>
                    <td><label for="quantity">Quantity:</label></td>
                    <td>
                        <?=$liveform->output_field(array(
                            'type' => 'number',
                            'id' => 'quantity',
                            'name' => 'quantity',
                            'value' => '1',
                            'min' => '1',
                            'max' => $quantity_max,
                            'style' => 'width: 5em'))?>
                    </td>
                </tr>
            <?php endif ?>
            <tr id="code_heading_row">
                <th colspan="2"><h2>New Key Code for Redemption &amp; Order Reporting</h2></th>
            </tr>
            <tr id="code_row">
                <td><label for="code">Key Code:</label></td>
                <td>
                    <?=$liveform->output_field(array(
                        'type' => 'text',
                        'id' => 'code',
                        'name' => 'code',
                        'size' => '40',
                        'maxlength' => '50'))?>
                </td>
            </tr>
            <tr>
                <th colspan="2"><h2>Alias of Existing Offer Code</h2></th>
            </tr>
            <tr>
                <td><label for="offer_code">Offer Code:</label></td>
                <td>
                    <?=$liveform->output_field(array(
                        'type' => 'text',
                        'id' => 'offer_code',
                        'name' => 'offer_code',
                        'size' => '40',
                        'maxlength' => '50'))?>
                </td>
            </tr>
            <tr>
                <th colspan="2"><h2>Availability</h2></th>
            </tr>
            <tr>
                <td><label for="enabled">Enable:</label></td>
                <td>
                    <?=$liveform->output_field(array(
                        'type' => 'checkbox',
                        'id' => 'enabled',
                        'name' => 'enabled',
                        'value' => '1',
                        'class' => 'checkbox'))?>
                </td>
            </tr>
            <tr>
                <td>Expiration Date:</td>
                <td>
                    <?=$liveform->output_field(array(
                        'type' => 'text',
                        'id' => 'expiration_date',
                        'name' => 'expiration_date',
                        'size' => '10',
                        'maxlength' => '10'))?>&nbsp;
                    (leave blank for no expiration)
                    <?=get_date_picker_format()?>
                    <script>
                        $('#expiration_date').datepicker({
                            dateFormat: date_picker_format
                        });
                    </script>
                </td>
            </tr>
            <tr>
                <td style="vertical-align: top">Notes:</td>
                <td>
                    <?=$liveform->output_field(array(
                        'type' => 'textarea',
                        'name' => 'notes',
                        'style' => 'width: 600px; height: 100px'))?>
                </td>
            </tr>
            <tr>
                <th colspan="2"><h2>Prevent Code from Being Used in Multiple Orders</h2></th>
            </tr>
            <tr>
                <td><label for="single_use">Single-Use:</label></td>
                <td>
                    <?=$liveform->output_field(array(
                        'type' => 'checkbox',
                        'id' => 'single_use',
                        'name' => 'single_use',
                        'value' => '1',
                        'class' => 'checkbox'))?>
                </td>
            </tr>
            <tr>
                <th colspan="2"><h2>Code for Order Reporting &amp; Exporting</h2></th>
            </tr>
            <tr>
                <td>Report:</td>
                <td>
                    <?=$liveform->output_field(array(
                        'type' => 'radio',
                        'id' => 'report_key_code',
                        'name' => 'report',
                        'value' => 'key_code',
                        'class' => 'radio'))
                    ?><label for="report_key_code"> Key Code</label>&nbsp;

                    <?=$liveform->output_field(array(
                        'type' => 'radio',
                        'id' => 'report_offer_code',
                        'name' => 'report',
                        'value' => 'offer_code',
                        'class' => 'radio'))
                    ?><label for="report_offer_code"> Offer Code</label>
                </td>
            </tr>
        </table>
        <div class="buttons">
            <input type="submit" name="submit_button" value="<?php if ($screen == 'create'): ?>Create<?php else: ?>Save<?php endif ?>" class="submit-primary">&nbsp;&nbsp;
            <input type="button" name="cancel" value="Cancel" onclick="javascript:history.go(-1);" class="submit-secondary">
            <?php if ($screen == 'edit'): ?>
                &nbsp;&nbsp;<input type="submit" name="delete" value="Delete" class="delete" onclick="return confirm('WARNING: This key code will be permanently deleted.')">
            <?php endif ?>
        </div>
    </form>
</div>

<?php if ($screen == 'create'): ?>

    <script>

        (function () {

            function update_key_code() {

                if ($('#quantity').val() == 1) {
                    $('#code_heading_row').show();
                    $('#code_row').show();
                } else {
                    $('#code_heading_row').hide();
                    $('#code_row').hide();
                }
            }

            update_key_code();

            $('#quantity').change(function() {
                update_key_code();
            });
        })();
    </script>
<?php endif ?>