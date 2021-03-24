
<div id="subnav">
    <h1>
        <?php if ($screen == 'create'): ?>
            [new offer rule]
        <?php else: ?>
            <?=h($offer_rule['name'])?>
        <?php endif ?>
    </h1>
</div>

<div id="content">

    <?=$form->get_messages()?>

    <a href="#" id="help_link">Help</a>

    <h1><?=ucfirst($screen)?> Offer Rule</h1>

    <div class="subheading" style="margin-bottom: 1.5em">
        <?php if ($screen == 'create'): ?>
            Create a new offer rule that can be assigned to any offer.
        <?php else: ?>
            Edit an offer rule that can be assigned to any offer.
        <?php endif ?>
    </div>

    <form method="post">

        <?=get_token_field()?>

        <table class="field">

            <tr>
                <th colspan="2"><h2>Offer Rule Name</h2></th>
            </tr>

            <tr>
                <td><label for="name" style="white-space: nowrap">Offer Rule Name:</label></td>
                <td style="width: 100%">
                    <input type="text" id="name" name="name" size="50" maxlength="50" required>
                </td>
            </tr>

            <tr>
                <th colspan="2">

                    <h2>Require a Subtotal</h2>

                    <p class="help">
                        Require that the customer's cart contain at least a certain subtotal.  You may leave the field blank, if the rule does not require a subtotal.
                    </p>
                </th>
            </tr>

            <tr>
                <td><label for="required_subtotal" style="white-space: nowrap">Required Subtotal:</label></td>
                <td>
                    <?=BASE_CURRENCY_SYMBOL?>
                    <input type="number" id="required_subtotal" name="required_subtotal"
                        step="any" min="0" style="width: 70px">
                </td>
            </tr>

            <tr id="require_product">
                <th colspan="2">

                    <h2>Require a Product</h2>

                    <p class="help">
                        Select a product that the customer must add to the cart, in order to get the offer. If the customer should have the option of adding one of many products, then you may select multiple products. In that case, the customer will only be required to add one of the products (not all). You may leave the field blank if the rule does not require a product.  You should also enter the quantity of the product(s) that the customer must add to the cart.
                    </p>
                </th>
            </tr>

            <tr>
                <td style="padding-top: 27px; vertical-align: top">
                    <label for="required_products" style="white-space: nowrap">Required Product:</label>
                </td>

                <td>

                    <?php if (!defined('CDN') or CDN): ?>

                        <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.1/css/select2.min.css" rel="stylesheet">
                        <link href="select2/select2-livesite.css" rel="stylesheet">
                        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.1/js/select2.min.js"></script>

                    <?php else: ?>
                        
                        <link href="select2/select2.min.css" rel="stylesheet">
                        <link href="select2/select2-livesite.css" rel="stylesheet">
                        <script src="select2/select2.min.js"></script>

                    <?php endif ?>

                    <select id="required_products" name="required_products[]" multiple="multiple"
                        style="width: 500px"></select>

                    <script>
                        $(function() {

                            // Enable Select2 for required products pick list.

                            var $required_products = $('#required_products');

                            $required_products.select2({

                                allowClear: true,

                                // We had to set a placeholder in order for the clear feature to work.
                                placeholder: 'Search',
                                
                                theme: 'livesite'
                            });

                            // When a required product is selected, then set the required quantity
                            // to 1, if there is no quantity already entered.

                            $required_products.on('select2:select', function() {

                                if ($('#required_quantity').val() == '') {
                                    $('#required_quantity').val('1');
                                }
                            });
                        });
                    </script>
                </td>
            </tr>

            <tr>
                <td><label for="required_quantity" style="white-space: nowrap">Required Quantity:</label></td>
                <td>
                    <input type="number" id="required_quantity" name="required_quantity"
                        step="1" min="1" style="width: 70px">
                </td>
            </tr>
        </table>

        <div class="buttons">

            <input type="submit" name="submit_button" value="<?php if ($screen == 'create'): ?>Create<?php else: ?>Save<?php endif ?>" class="submit-primary">&nbsp;&nbsp;

            <input type="button" name="cancel" value="Cancel" onclick="javascript:history.go(-1);" class="submit-secondary">

            <?php if ($screen == 'edit'): ?>
                &nbsp;&nbsp;<input type="submit" name="delete" value="Delete" class="delete" onclick="return confirm('WARNING: This offer rule will be permanently deleted.')">
            <?php endif ?>
        </div>
    </form>
</div>