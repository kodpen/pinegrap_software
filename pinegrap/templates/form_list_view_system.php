
<?=$messages?>

<?php
    // If browse or search is enabled, then show form.
    if ($browse or $search):
?>

    <form
        <?=$attributes?>
        class="
            browse_and_search_form
            page_<?=$page_id?>

            <?php if ($browse): ?>
                browse_enabled
            <?php endif ?>

            <?php if ($browse_expanded): ?>
                browse_expanded
            <?php endif ?>

            <?php if ($search): ?>
                simple_search
            <?php endif ?>

            <?php if ($query != ''): ?>
                simple_active
            <?php endif ?>

            <?php if ($search_advanced): ?>
                advanced_enabled
            <?php endif ?>

            <?php if ($search_advanced_expanded): ?>
                advanced_expanded
            <?php endif ?>
        "
        style="margin: 0em 0em 1em 0em; clear: both"
    >

        <table class="browse_and_search_table">
            <tr>

                <?php if ($browse): ?>

                    <td class="browse_cell">
                        <div class="browse">

                            <select
                                id="<?=$page_id?>_browse_field_id"
                                name="<?=$page_id?>_browse_field_id"
                                class="software_select"
                            ></select>

                            <a
                                href="javascript:void(0)"
                                class="
                                    browse_toggle
                                    software_button_tiny_secondary"
                                title="Remove Browse"

                                <?php if (!$browse_expanded): ?>
                                    style="display: none"
                                <?php endif ?>
                            >
                                &ndash;
                            </a>
                            
                        </div>
                    </td>

                <?php endif ?>

                <?php if ($search): ?>

                    <td class="search_cell">
                        <div class="search">

                            <?php if ($search_advanced): ?>
                            
                                <a
                                    href="javascript:void(0)"
                                    class="
                                        advanced_toggle software_button_tiny_secondary"
                                    title="<?php if ($search_advanced_expanded): ?>Remove<?php else: ?>Add<?php endif ?> Advanced Search"
                                >
                                    
                                    <span
                                        class="expand_label"
                                        
                                        <?php if ($search_advanced_expanded): ?>
                                            style="display: none"
                                        <?php endif ?>
                                    >
                                        +
                                    </span>

                                    <span
                                        class="collapse_label"
                                        
                                        <?php if (!$search_advanced_expanded): ?>
                                            style="display: none"
                                        <?php endif ?>
                                    >
                                        &ndash;
                                    </span>

                                </a>

                            <?php endif ?>

                            <span class="simple">

                                <input
                                    type="text"
                                    id="<?=$page_id?>_query"
                                    name="<?=$page_id?>_query"
                                    class="
                                        software_input_text
                                        mobile_fixed_width
                                        query"

                                    <?php if ($search_label): ?>
                                        placeholder="<?=h($search_label)?>"
                                    <?php endif ?>
                                >

                                <input
                                    type="submit"
                                    name="<?=$page_id?>_submit"
                                    value=""
                                    class="submit"

                                    <?php if ($search_label): ?>
                                        title="<?=h($search_label)?>"
                                    <?php endif ?>
                                >

                                <?php if ($query != ''): ?>

                                    <input
                                        type="submit"
                                        name="<?=$page_id?>_simple_clear"
                                        value=""
                                        class="clear"
                                        title="Clear"
                                    >

                                <?php endif ?>

                            </span>
                        </div>
                    </td>

                <?php endif ?>

            </tr>

        </table>

        <?php if ($browse): ?>

            <?php foreach($browse_fields as $field): ?>

                <div
                    class="browse_filter_container field_<?=$field['id']?>"

                    <?php if (!$field['current']): ?>
                        style="display: none"
                    <?php endif ?>
                >

                    <ul
                        style="
                            list-style: none;
                            padding: 0 0 0 .5em;
                            column-count: <?=$field['number_of_columns']?>;
                            -moz-column-count: <?=$field['number_of_columns']?>;
                            -webkit-column-count: <?=$field['number_of_columns']?>"
                    >
                    
                        <?php foreach($field['filters'] as $filter): ?>

                            <li
                                <?php if ($filter['current']): ?>
                                    class="current"
                                <?php endif ?>
                            >
                                <a href="<?=h($filter['url'])?>">
                                    <?=h($filter['name'])?>
                                </a>
                            </li>

                        <?php endforeach ?>

                    </ul>

                </div>

            <?php endforeach ?>

        <?php endif ?>

        <?php if ($search_advanced): ?>

            <div
                class="advanced"
                
                <?php if (!$search_advanced_expanded): ?>
                    style="display: none"
                <?php endif ?>
            >

                <?=$search_advanced_content?>

            </div>

        <?php endif ?>

        <!-- Required hidden fields and JS (do not remove) -->
        <?=$system?>

    </form>

<?php endif ?>

<?php
    // If the visitor needs to browse or search first, then tell the visitor that.
    if ($browse_or_search_required):
?>

    <div class="browse_or_search_above_message" style="font-weight:bold">

        You may

        <?php if ($browse and $search): ?>
            browse or search
        <?php elseif ($browse): ?>
            browse
        <?php else: ?>
            search
        <?php endif ?>

        above to find results.

    </div>

<?php
    // Otherwise if there are no forms to show, then tell the visitor that.
    elseif ($number_of_forms == 0):
?>
    
    <div class="no_results_message" style="font-weight:bold">There are no results.</div>

<?php
    // Otherwise, there are forms to show, so output them.
    else:
?>

    <?php
        // If the visitor has browsed or searched, then show number of results
        if ($browse_active or $search_active):
    ?>

        <div class="number_of_results_message" style="font-weight:bold; margin-bottom: .75em">
            Found <?=number_format($total_number_of_forms)?>
            result<?php if ($total_number_of_forms > 1): ?>s<?php endif ?>.
        </div>

    <?php endif ?>

    <?=$header?>

    <?php foreach($forms as $key => $form): ?>

        <div class="row_<?=($key + 1) % 2?>">
            <?=$form['content']?>
        </div>

    <?php endforeach ?>

    <?=$footer?>
    
    <?=$output_pagination?>

<?php endif ?>