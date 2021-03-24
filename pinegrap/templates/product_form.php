<?php
    // Show product form for every quantity if necessary.
    for ($quantity_number = 1; $quantity_number <= $item['number_of_forms']; $quantity_number++):
?>

    <fieldset>

        <?php if ($item['form_title'] or ($item['number_of_forms'] > 1)): ?>

            <legend>

                <?php if ($item['form_title']): ?>
                    <?=h($item['form_title'])?>
                <?php endif ?>

                <?php if ($item['number_of_forms'] > 1): ?>
                    (<?=$quantity_number?>
                    of
                    <?=$item['number_of_forms']?>)
                <?php endif ?>

            </legend>

        <?php endif ?>

        <?php foreach ($item['fields'] as $field): ?>

            <?php
                // Prepare field name and id.
                $name =
                    'order_item_' . $item['id'] .
                    '_quantity_number_' . $quantity_number .
                    '_form_field_' . $field['id'];
            ?>

            <?php switch($field['type']):
                case 'text box':
                case 'email address':
                case 'date':
                case 'date and time':
                case 'time':
            ?>

                    <div class="form-group">

                        <?php if ($field['label']): ?>
                            <label for="<?=$name?>">
                                <?=$field['label']?><?php if ($field['required']): ?>*<?php endif ?>
                            </label>
                        <?php endif ?>

                        <input

                            type="<?php if ($field['type'] == 'email address'): ?>email<?php else: ?>text<?php endif ?>"

                            name="<?=$name?>"

                            id="<?=$name?>"

                            <?php if ($field['size']): ?>
                                size="<?=$field['size']?>"
                            <?php endif ?>

                            <?php if ($field['maxlength']): ?>
                                maxlength="<?=$field['maxlength']?>"
                            <?php endif ?>

                            <?php if ($field['required']): ?>
                                required
                            <?php endif ?>

                            class="form-control"

                        >

                        <?php if ($field['type'] == 'time'): ?>
                            <p class="help-block">
                                (Format: h:mm AM/PM)
                            </p>
                        <?php endif ?>

                    </div>

                    <?php
                        // If there is a title for this field and quantity
                        // number, then show title label and title.  We show the title
                        // of a submitted form when a customer enters a valid reference
                        // code in order to help the customer understand which submitted
                        // form the reference code is related to (e.g. ordering credits
                        // for a conversation/support ticket).
                        if ($field['titles'][$quantity_number]['title']):
                    ?>

                        <div class="form-group">

                            <label>
                                <?=$field['titles'][$quantity_number]['title_label']?>
                            </label>

                            <p class="form-control-static">
                                <?=h($field['titles'][$quantity_number]['title'])?>
                            </p>

                        </div>

                    <?php endif ?>

                    <?php break ?>

                <?php case 'text area': ?>

                    <div class="form-group">

                        <?php if ($field['label']): ?>
                            <label for="<?=$name?>">
                                <?=$field['label']?><?php if ($field['required']): ?>*<?php endif ?>
                            </label>
                        <?php endif ?>

                        <textarea

                            name="<?=$name?>"

                            id="<?=$name?>"

                            <?php if ($field['rows']): ?>
                                rows="<?=$field['rows']?>"
                            <?php endif ?>

                            <?php if ($field['cols']): ?>
                                cols="<?=$field['cols']?>"
                            <?php endif ?>

                            <?php if ($field['maxlength']): ?>
                                maxlength="<?=$field['maxlength']?>"
                            <?php endif ?>

                            <?php if ($field['required']): ?>
                                required
                            <?php endif ?>

                            class="form-control"

                        ></textarea>

                    </div>

                    <?php break ?>

                <?php case 'pick list': ?>

                    <div class="form-group">

                        <?php if ($field['label']): ?>
                            <label for="<?=$name?>">
                                <?=$field['label']?><?php if ($field['required']): ?>*<?php endif ?>
                            </label>
                        <?php endif ?>

                        <select

                            name="<?=$name?><?php if ($field['multiple']): ?>[]<?php endif ?>"

                            id="<?=$name?>"

                            <?php if ($field['size']): ?>
                                size="<?=$field['size']?>"
                            <?php endif ?>

                            <?php if ($field['required']): ?>
                                required
                            <?php endif ?>

                            <?php if ($field['multiple']): ?>
                                multiple
                            <?php endif ?>

                            class="form-control"

                        ></select>

                    </div>

                    <?php break ?>

                <?php case 'radio button': ?>

                    <?php if ($field['label']): ?>
                        <label><?=$field['label']?></label>
                    <?php endif ?>

                    <?php foreach ($field['options'] as $option): ?>

                        <div class="radio">

                            <label>

                                <input

                                    type="radio"

                                    name="<?=$name?>"

                                    value="<?=h($option['value'])?>"

                                    <?php if ($field['required']): ?>
                                        required
                                    <?php endif ?>

                                >

                                <?=h($option['label'])?>

                            </label>

                        </div>

                    <?php endforeach ?>

                    <?php break ?>

                <?php case 'check box': ?>

                    <?php if ($field['label']): ?>
                        <label><?=$field['label']?></label>
                    <?php endif ?>

                    <?php foreach ($field['options'] as $option): ?>

                        <div class="checkbox">

                            <label>

                                <input

                                    type="checkbox"

                                    name="<?=$name?><?php if (count($field['options']) > 1): ?>[]<?php endif ?>"

                                    value="<?=h($option['value'])?>"

                                    <?php
                                        // If the field is required and there is
                                        // only one check box option, then make
                                        // field required.
                                        if (
                                            $field['required']
                                            and (count($field['options']) == 1)
                                        ):
                                    ?>
                                        required
                                    <?php endif ?>

                                >

                                <?=h($option['label'])?>

                            </label>

                        </div>

                    <?php endforeach ?>

                    <?php break ?>

                <?php case 'information': ?>

                    <?=$field['information']?>

                    <?php break ?>

            <?php endswitch ?>

        <?php endforeach ?>

    </fieldset>

<?php endfor ?>