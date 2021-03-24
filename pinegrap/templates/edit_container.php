<div id="subnav">
    <h1>
        <?php if ($screen == 'create'): ?>
            [new container]
        <?php else: ?>
            <?=h($container['name'])?>
        <?php endif ?>
    </h1>
</div>
<div id="content">
    <?=$form->get_messages()?>
    <a href="#" id="help_link">Help</a>
    <h1><?=ucfirst($screen)?> Container</h1>
    <div class="subheading" style="margin-bottom: 1.5em">
        <?php if ($screen == 'create'): ?>
            Create a new shipping container (e.g. box) that products are packaged in.
        <?php else: ?>
            Edit a shipping container (e.g. box) that products are packaged in.
        <?php endif ?>
    </div>
    <form method="post">
        <?=get_token_field()?>
        <table class="field">
            <tr>
                <td><label for="name">Name:</label></td>
                <td>
                    <input type="text" id="name" name="name" size="50" maxlength="100" required>
                </td>
            </tr>
            <tr>
                <td><label for="enabled">Enable:</label></td>
                <td>
                    <input type="checkbox" id="enabled" name="enabled" value="1" class="checkbox">
                </td>
            </tr>
            <tr>
                <td style="padding-left: 2em"><label for="length">Length Type:</label></td>
                <td><input type="radio" class="radio" value="Length type Inches" id="length_type_inches" name="length_type" checked="checked" /><label for="length_type_inches">Inches</label>
                <input type="radio" class="radio" value="Length type Cm" id="length_type_cm" name="length_type" /><label for="length_type_cm">Cm</label></td>
            </tr>
            <tr>
                <td style="padding-left: 2em"><label for="length">Dimensions:</label></td>
                <td id="length_inches">
                    <label for="length">L:</label>
                    <input
                        type="number"
                        step="any"
                        id="length"
                        name="length" 
                        placeholder="Length"
                        style="width: 90px"> &nbsp;

                     <label for="width">W:</label>

                     <input
                        type="number"
                        step="any"
                        id="width"
                        name="width"
                        placeholder="Width"
                        style="width: 90px"> &nbsp;

                     <label for="height">H:</label>

                     <input
                        type="number"
                        step="any"
                        id="height"
                        name="height"
                        placeholder="Height"
                        style="width: 90px"/> 
                    &nbsp;inches
                </td>

                 <td id="length_cm" style="display:none">
                    <label for="length">L:</label>
                    <input
                        type="number"
                        step="any"
                        id="lengthcm"
                        name="lengthcm" 
                        placeholder="Length"
                        style="width: 90px"> &nbsp;

                     <label for="width">W:</label>

                     <input
                        type="number"
                        step="any"
                        id="widthcm"
                        name="widthcm"
                        placeholder="Width"
                        style="width: 90px"/> &nbsp;

                     <label for="height">H:</label>

                     <input
                        type="number"
                        step="any"
                        id="heightcm"
                        name="heightcm"
                        placeholder="Height"
                        style="width: 90px"> 
                    &nbsp;cm
                </td>
                <script>
                    $("input[name=length_type]").click(function() {    
                        if($("#length_type_inches").is(":checked")) {  
                            $("#length_cm").attr("style","display:none")
                            $("#length_inches").attr("style","")
                        }
                        if($("#length_type_cm").is(":checked")) {  
                            $("#length_inches").attr("style","display:none")
                            $("#length_cm").attr("style","")
                        }
                    });

                     var lil = $("#length");
                    var liw = $("#width");
                    var lih = $("#height");

                     var lilcm = $("#lengthcm");
                    var liwcm = $("#widthcm");
                    var lihcm = $("#heightcm");

                     var lilval = $("#length").val();
                    var liwval = $("#width").val();
                    var lihval = $("#height").val();

                     var lilcmval = $("#lengthcm").val();
                    var liwcmval = $("#widthcm").val();
                    var lihcmval = $("#heightcm").val();

                     lil.change(function(){  
                        var lilcm = $("#lengthcm");
                        var lilval = $("#length").val();
                        lilcm.val(lilval*2.54); 
                    }).change();
                    liw.change(function(){  
                        var liwcm = $("#widthcm");
                        var liwval = $("#width").val();
                        liwcm.val(liwval*2.54); 
                    }).change();
                    lih.change(function(){  
                        var lihcm = $("#heightcm");
                        var lihval = $("#height").val();
                        lihcm.val(lihval*2.54); 
                    }).change();

                     lilcm.change(function(){
                        var lil = $("#length");
                        var lilcmval = $("#lengthcm").val();
                        lil.val(lilcmval*0.39370078740158); 
                    }).change();
                    liwcm.change(function(){
                        var liw = $("#width");
                        var liwcmval = $("#widthcm").val();
                        liw.val(liwcmval*0.39370078740158); 
                    }).change();
                    lihcm.change(function(){
                        var lih = $("#height");
                        var lihcmval = $("#heightcm").val();
                        lih.val(lihcmval*0.39370078740158); 
                    }).change();
                </script>
            </tr>
            <tr>
                <td style="padding-left: 2em"><label for="weight">Weight Type:</label></td>
                <td><input type="radio" class="radio" value="Weight type pounds" id="weight_type_pounds" name="weight_type" checked="checked" /><label for="weight_type_pounds">Pounds</label>
                <input type="radio" class="radio" value="Weight type kg" id="weight_type_kg" name="weight_type" /><label for="weight_type_kg">Kg</label></td>
            </tr>
            <tr>
                <td style="padding-left: 2em"><label for="weight">Weight:</label></td>
                <td id="weight_pound">
                    <input
                        type="number"
                        step="any"
                        id="weight"
                        name="weight"
                        style="width: 90px"
                    />&nbsp;pounds
                </td>
                <td id="weight_kg" style="display:none">
                    <input
                        type="number"
                        step="any"
                        id="weightkg"
                        name="weightkg"
                        style="width: 90px"
                    />&nbsp;kg
                </td>
                <script>
                    $("input[name=weight_type]").click(function() {    
                        if($("#weight_type_kg").is(":checked")) {  
                            $("#weight_pound").attr("style","display:none")
                            $("#weight_kg").attr("style","")
                        }
                        if($("#weight_type_pounds").is(":checked")) {  
                            $("#weight_kg").attr("style","display:none")
                            $("#weight_pound").attr("style","")
                        }
                    });
                    var wp = $("#weight");
                    wp.change(function(){
                        var wp = $("#weight");
                        var wkg = $("#weightkg");
                        var wkgval = wkg.val();
                        var wpval = wp.val();
                        wkg.val(wpval*2.20462262); 
                    }).change();
                    var wkg = $("#weightkg");
                    wkg.change(function(){
                        var wkg = $("#weightkg");
                        var wkgval = wkg.val();
                        var wp = $("#weight");
                        var wpval = wp.val();
                        wp.val(wkgval*0.45359237); 
                    }).change();
                </script>
            </tr>
            <tr>
                <td><label for="cost">Cost:</label></td>
                <td>
                    <?=BASE_CURRENCY_SYMBOL?>
                    <input
                        type="number"
                        step="any"
                        id="cost"
                        name="cost"
                        style="width: 70px">
                </td>
            </tr>
        </table>
        <div class="buttons">
            <input type="submit" name="submit_button" value="<?php if ($screen == 'create'): ?>Create<?php else: ?>Save<?php endif ?>" class="submit-primary">&nbsp;&nbsp;
            <input type="button" name="cancel" value="Cancel" onclick="javascript:history.go(-1);" class="submit-secondary">
            <?php if ($screen == 'edit'): ?>
                &nbsp;&nbsp;<input type="submit" name="delete" value="Delete" class="delete" onclick="return confirm('WARNING: This container will be permanently deleted.')">
            <?php endif ?>
        </div>
    </form>
</div>