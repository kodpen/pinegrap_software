
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
    >

        <div class="row">

            <?php if ($browse): ?>

                <div class="col-sm-6">

                    <div class="form-group input-group">

                    	<div class="select-option">
							<i class="ti-angle-down"></i>
                        	<select
                            	id="<?=$page_id?>_browse_field_id"
                            	name="<?=$page_id?>_browse_field_id"
                        	></select>
                        </div>

                        <span
                            class="browse_toggle input-group-btn"
                            title="Remove Browse"

                            <?php if (!$browse_expanded): ?>
                                style="display: none"
                            <?php endif ?>
                        >

                            <button
                                type="button"
                                class="btn btn-secondary btn-thin" style="width:3em;padding:0"
                            >
                                <span class="ti-close"></span>
                            </button>

                        </span>

                    </div>



                </div>

            <?php endif ?>


            <?php if ($search): ?>

                <div class="col-sm-6">

                    <div class="form-group input-group">

                        <?php if ($search_advanced): ?>

                            <span
                                class="advanced_toggle input-group-btn"
                                title="<?php if ($search_advanced_expanded): ?>Remove<?php else: ?>Add<?php endif ?> Advanced Search"
                            >

                                <button
                                    type="button"
                                    class="btn btn-secondary btn-thin" style="width:3em;padding:0"
                                >

                                    <span class="expand_label ti-angle-down"
                                        <?php if ($search_advanced_expanded): ?>
                                            style="display: none; font-size:16px"
                                        <?php endif ?>
                                    ></span>

                                    <span class="collapse_label ti-angle-up"
                                        <?php if (!$search_advanced_expanded): ?>
                                            style="display: none; font-size:16px"
                                        <?php endif ?>
                                    ></span>

                                </button>

                            </span>

                        <?php endif ?>
   
                        <span class="input-group-btn">

                            <button
                                type="submit"
                                name="<?=$page_id?>_submit"
                                class="btn btn-default btn-primary btn-thin"
								style="width:69px" 
                                <?php if ($search_label): ?>
                                    title="<?=h($search_label)?>"
                                <?php endif ?>
                            >
                                <span class="ti-search" style="font-size:16px"></span>
                            </button>
                            
                        </span>
                        
                        <input
                            type="search"
                            id="<?=$page_id?>_query"
                            name="<?=$page_id?>_query"
                            class="form-control"

                            <?php if ($search_label): ?>
                                placeholder="<?=h($search_label)?>"
                            <?php endif ?>
                        >                            

                            <?php if ($query != ''): ?>

                        	<span class="input-group-btn">
                                
                                <button
                                    type="submit"
                                    name="<?=$page_id?>_simple_clear"
                                    class="btn btn-default btn-secondary btn-thin"
                                    style="width:3em;padding:0"
                                    title="Clear"
                                >
                                    <span class="ti-close"></span>
                                </button>
                                
                        	</span>

                            <?php endif ?>
                        
                    </div>

                </div>

            <?php endif ?>

        </div>
        
        <?php if ($browse): ?>
        
            <?php foreach($browse_fields as $field): ?>

            	<div
                    class="browse_filter_container field_<?=$field['id']?>"

                    <?php if (!$field['current']): ?>
                        style="display: none"
                    <?php endif ?>
                >

                    <hr>
                    <ul
                        class="list-unstyled"
                        style="
                            column-count: <?=$field['number_of_columns']?>;
                            -moz-column-count: <?=$field['number_of_columns']?>;
                            -webkit-column-count: <?=$field['number_of_columns']?>
                        "
                    >
                    
                        <?php foreach($field['filters'] as $filter): ?>

                            <li
                                <?php if ($filter['current']): ?>
                                    class="current"
                                <?php endif ?>
                            >
                                <a href="<?=h($filter['url'])?>">

                                    <?php if ($filter['current']): ?>
                                        <strong>
                                    <?php endif ?>

                                    <?=h($filter['name'])?>

                                    <?php if ($filter['current']): ?>
                                        </strong>
                                    <?php endif ?>

                                </a>
                            </li>

                        <?php endforeach ?>

                    </ul>
                    <hr>

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

                <hr>
                <?=$search_advanced_content?>
				<hr>
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

    <p>
        <strong>

            You may

            <?php if ($browse and $search): ?>
                browse or search
            <?php elseif ($browse): ?>
                browse
            <?php else: ?>
                search
            <?php endif ?>

            above to find results.

        </strong>
    </p>

<?php
    // Otherwise if there are no forms to show, then tell the visitor that.
    elseif ($number_of_forms == 0):
?>
    
    <p><strong>There are no results.</strong></p>

<?php
    // Otherwise, there are forms to show, so output them.
    else:
?>

    <?php
        // If the visitor has browsed or searched, then show number of results
        if ($browse_active or $search_active):
    ?>

        <p>
            <strong>
                Found <?=number_format($total_number_of_forms)?>
                result<?php if ($total_number_of_forms > 1): ?>s<?php endif ?>.
            </strong>
        </p>

    <?php endif ?>

    <?=$header?>

    <?php foreach($forms as $form): ?>

        <div>
            <?=$form['content']?>
        </div>

    <?php endforeach ?>

    <?=$footer?>

    <?php
        // If there is more than one page of results, then show pagination.
        if ($number_of_pages > 1):
    ?>
    
        <nav>
            <ul class="pager">

                <?php
                    // If this is not the first page, then show previous button.
                    if ($page_number != 1):
                ?>

                    <li>
                        <a href="<?=h($previous_page_url)?>">&laquo; Previous</a>
                    </li>

                <?php endif ?>

                <li>
                    &nbsp;
                    Page
                    <?=number_format($page_number)?>
                    of
                    <?=number_format($number_of_pages)?>
                    &nbsp;
                </li>

                <?php
                    // If this is not the last page, then show next button.
                    if ($page_number != $number_of_pages):
                ?>

                    <li>
                        <a href="<?=h($next_page_url)?>">Next &raquo;</a>
                    </li>

                <?php endif ?>

            </ul>
        </nav>    

    <?php endif ?>

<?php endif ?>