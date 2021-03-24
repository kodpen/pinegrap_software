<div id="subnav">
    <h1>
        <?=h($page['name'])?>
    </h1>
</div>
<div id="content">
    <a href="#" id="help_link">Help</a>
    <h1>Generate Layout</h1>
    <div class="subheading" style="margin-bottom: 1.5em">
        Copy &amp; paste the code below into your custom layout.
    </div>
    <div>
        <?=$form->field(array(
            'type' => 'textarea',
            'name' => 'layout',
            'id' => 'layout',
            'style' => 'width: 99%; height: 500px'))?>

        <?=get_codemirror_includes()?>
        <?=get_codemirror_javascript(array(
            'id' => 'layout',
            'code_type' => 'php',
            'readonly' => true))?>
    </div>
    <div class="buttons">
        <input type="button" value="Back" onclick="javascript:history.go(-1);" class="submit-secondary">
    </div>
</div>