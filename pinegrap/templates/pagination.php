<?php if ($number_of_screens > 1): ?>

    <div class="pagination">

        <?php if ($previous): ?>
            <a class="submit-secondary" href="<?=h(escape_url($_SERVER['PHP_SELF']))?>?screen=<?=h($previous)?>">&lt;</a>&nbsp;&nbsp;
        <?php endif ?>
        
        <select name="screens" onchange="window.location.href=('<?=h(escape_javascript($_SERVER['PHP_SELF']))?>?screen=' + this.options[this.selectedIndex].value)">
            <?php for ($i = 1; $i <= $number_of_screens; $i++): ?>
                <option
                    value="<?=h($i)?>"
                    <?php if ($i == $screen): ?>
                        selected="selected"
                    <?php endif ?>
                >
                    <?=h($i)?>
                </option>
            <?php endfor ?>
        </select>

        <?php if ($next <= $number_of_screens): ?>
            &nbsp;&nbsp;<a class="submit-secondary" href="<?=h(escape_url($_SERVER['PHP_SELF']))?>?screen=<?=h($next)?>">&gt;</a>
        <?php endif ?>

    </div>
<?php endif ?>