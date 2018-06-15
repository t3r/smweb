<?php
$pageTitle = "Models update form";
require 'view/header.php';
?>
<script type="text/javascript" src="/inc/js/check_form.js"></script>
<script type="text/javascript" src="/inc/js/jquery.multifile.js"></script>
<script type="text/javascript" src="/inc/js/submit.js"></script>
<script type="text/javascript">
/*<![CDATA[*/

var ac3DSelected = false;
var thumbSelected = false;

function validateForm()
{
    var form = document.getElementById("positions");

    if (form["mo_name"].value === "" || !checkComment(form["mo_name"]) ||
        !checkComment(form["notes"]) ||
        !checkComment(form["comment"]) ||
        !checkEmail(form["email"]))
        return false;
<?php
    if (\Config::isCaptchaEnabled())
        echo 'if (form["recaptcha_response_field"].value === "")return false;';
?>
    return !ajaxSubmit("positions",
            "app.php?c=UpdateModel&a=addRequest&ajaxCheck=1",
            "app.php?c=UpdateModel&a=success&id=");
}

function validateTabs()
{
    var form = document.getElementById("positions");
    $( "#tabs" ).tabs({ disabled: false });

    // Tab 1
    if (!checkComment(form["mo_name"]) ||
            form["mo_name"].value === "" ||
            !checkComment(form["notes"]) ||
            !ac3DSelected ||
            !thumbSelected ||
            form["comment"].value === "") {
        $( "#tabs" ).tabs({ disabled: [1] });
        return false;
    }
}
$(function() {
    $( "#tabs" ).tabs({ disabled: [1] });

    $('#ac3d_file').MultiFile({
        max: 1,
        accept: 'ac',
        afterFileRemove: function(element, value, master_element) {
          ac3DSelected = false;
          validateTabs();
        },
        afterFileAppend: function(element, value, master_element) {
          ac3DSelected = true;
          validateTabs();
        }
    });
    
    $('#mo_thumbfile').MultiFile({
        max: 1,
        accept: 'jpg',
        afterFileRemove: function(element, value, master_element) {
          thumbSelected = false;
          validateTabs();
        },
        afterFileAppend: function(element, value, master_element) {
          if (value !== "") {
              thumbSelected = true;
          }

          validateTabs();
        }
    });
});
/*]]>*/
</script>
<link rel="stylesheet" href="/css/jquery-ui.min.css" type="text/css"/>
<script src="/inc/js/jquery-ui.min.js" type="text/javascript"></script>

<h1>Updating model #<?=$modelMD->getId()?></h1>

<div id="loadingScreen" style="display:none"></div>
<div id="submit-dialog" style="display:none">
    <div id="submit-dialog-errors"></div>
    Please correct the models directly in your computer and submit again
    (no need to reselect them!)
</div>

<p>
    Hover your mouse over the various field titles (left column) to view some information about what to do with that particular field. Please read <a href="http://<?php echo $_SERVER['SERVER_NAME'];?>/contribute.php">this page</a> for a better understanding of the various requirements.
</p>

<div id="tabs">
    <ul>
        <li><a href="#tabs-1">1: Model</a></li>
        <li><a href="#tabs-2">2: Submit</a></li>
    </ul>

    <form id="positions" method="post" action="app.php?c=UpdateModel&amp;a=addRequest" enctype="multipart/form-data" onsubmit="return validateForm();">
        <div id="tabs-1">
            <ul>
                <li>Add ALL files related to the model, INCLUDING those that you did not change. Any file not included will get lost.</li>
                <li>Files have to share a common name, for instance: modelname.ac, modelname.xml and modelname.png.</li>
                <li>Please do not group separate buildings into one AC file. The terrain elevation is subject to updates, so this could lead to inaccuracies.</li>
                <li>Do not add trees or flat surfaces (such as soccer fields) into your AC file.</li>
                <li>PNG resolution must be a power of 2 in width and height.</li>
                <li>If you have multiple textures, name them modelname1.png, modelname2.png etc.</li>
                <li>XML file must start with a classic XML header, such as: &lt;?xml version="1.0" encoding="UTF-8" ?&gt;. See <a href="TheNameOfYourACFile.xml">here</a> for a quick example. Only include XML if necessary for the model.</li>
                <li>The thumbnail must be in JPEG and 320*240 resolution.</li>
            </ul>
            <table style="width: 100%;">
                <tr>
                    <td><label for="model_group_id">Model's family<em>*</em><span>This is the family name of the object.</span></label></td>
                    <td>
                        <select id="model_group_id" name="model_group_id" onchange="validateTabs();">
            <?php
                        foreach ($modelsGroups as $modelsGroup) {
                            echo "<option value=\"".$modelsGroup->getId()."\"";
                            if ($modelsGroup->getId() == $modelMD->getModelsGroup()->getId()) {
                                echo " selected=\"selected\"";
                            }
                            echo ">".$modelsGroup->getName()."</option>";
                        }
            ?>
                        </select>
                    </td>
                    <td rowspan="4" style="width: 200px">
                        <img id="form_objects_thumb" style="width: 200px" src="app.php?c=Models&amp;a=thumbnail&amp;id=<?=$idToUpdate?>" alt=""/>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="mo_name">Model name<em>*</em><span>Please add a short (max 100 letters) name of your model (eg : Cornet antenna radome - Brittany - France).</span></label>
                    </td>
                    <td>
                        <input type="text" name="mo_name" id="mo_name" maxlength="100" style="width: 95%" onkeyup="checkComment(this);validateTabs();" value="<?=htmlspecialchars($modelMD->getName())?>"/>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="notes">Model description<span>Please add a short statement giving more details on this data. eg: The Cite des Telecoms, colocated with the cornet radome, is a telecommunications museum.</span></label>
                    </td>
                    <td>
                        <input type="text" name="notes" id="notes" maxlength="500" style="width: 95%" onkeyup="checkComment(this);validateTabs();" value="<?php echo htmlspecialchars($modelMD->getDescription());?>"/>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="ac3d_file">AC3D file<em>*</em><span >This is the AC3D file of your model (eg: tower.ac).</span></label>
                    </td>
                    <td>
                        <input type="file" name="ac3d_file" id="ac3d_file" />
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="xml_file">XML file<span>This is the XML file of your model (eg: tower.xml).</span></label>
                    </td>
                    <td colspan="2">
                        <input type="file" name="xml_file" id="xml_file" class="multi" maxlength="1" accept="text/xml" />
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="png_files">PNG texture file(s)<span>This (Those) is (are) the PNG texture(s) file(s) of your model. Has to be a power of 2 in width and height.</span></label>
                    </td>
                    <td colspan="2">
                        <input type="file" name="png_file[]" id="png_files" class="multi" maxlength="12" accept="image/png" />
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="mo_thumbfile">320x240 JPEG thumbnail<em>*</em><span>This is a nice picture representing your model in FlightGear in the best way.</span></label>
                    </td>
                    <td colspan="2">
                        <input type="file" name="mo_thumbfile" id="mo_thumbfile" />
                    </td>
                </tr>
                <tr>
                    <td><label for="comment">Comment<em>*</em><span>Please add a short (max 100 letters) statement why you are updating this model. This will help the maintainers understand what you are doing. eg: 'I have improved texture and clean the meshes, please commit.' Only alphanumerical, colon, semi colon, question and exclamation mark, arobace, minus, underscore, antislash and point are granted.</span></label></td>
                    <td colspan="2">
                        <input type="text" name="comment" id="comment" maxlength="100" size="90" value="" onkeyup="checkComment(this);validateTabs();" />
                    </td>
                </tr>
            </table>
        </div>
        <div id="tabs-2">
            <ul>
                <li>Choose the author for the model. <em>If you've just made some fixes to the model, please keep the original author.</em></li>
                <!--<li>Don't forget to feed the Captcha, it's a mandatory item as well. Don't know what a Captcha is or what its goal is? Learn more <a href="http://en.wikipedia.org/wiki/Captcha">here</a></li>-->
                <li>Be patient, there are human beings with real life constraints behind, and don't feel blamed if your models are rejected, but try to understand why.</li>
            </ul>
            <table style="width: auto; margin-left: auto; margin-right: auto;">
                <tr>
                    <td>
                        <label for="mo_author">Author<em>*</em><span>This is the name of the author. If the author is not listed, please ask the scenery maintainers to add it. This name is the author of the true creator of the model, if you just converted a model and were granted to do so, then also use the line below.</span></label>
                    </td>
                    <td>
                        <select name="mo_author" id="mo_author">
                            <?php
                            foreach($authors as $author) {
                                if ($author->getId() == $modelMD->getAuthor()->getId()) {
                                    echo "<option value=\"".$author->getId()."\" selected=\"selected\">".$author->getName()."</option>";
                                } else {
                                    echo "<option value=\"".$author->getId()."\">".$author->getName()."</option>";
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="email">Your email<em>*</em><span>Your email which can be different from the author's.</span></label>
                    </td>
                    <td>
                        <input type="text" name="email" id="email" maxlength="50" size="30" value="" onkeyup="checkEmail(this);" />
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="submit">
                        <input type="checkbox" name="gpl"/> I accept to release all my contribution under <a href="http://www.gnu.org/licenses/gpl-2.0.html">GNU GENERAL PUBLIC LICENSE Version 2, June 1991.</a><br/>
<?php
                        require_once 'view/captcha_form.php';
?>
                        <br />
                        <input type="hidden" name="MAX_FILE_SITE" value="2000000" />
                        <input type="hidden" name="modelId" value="<?=$idToUpdate?>" />
                        <input type="submit" value="Submit model" />
                    </td>
                </tr>
            </table>
        </div>
    </form>
</div>

<script type="text/javascript">
$(document).ready(function(){
    // Checks if the GPL checkbox is checked
    $('input[type="submit"]').attr('disabled','disabled');

    $('input[name="gpl"]').change(function(){
        if($('input[name="gpl"]').is(':checked')){
            $('input[type="submit"]').removeAttr('disabled');
        }else{
            $('input[type="submit"]').attr('disabled','disabled');
        }
    });
});
</script>
<?php require 'view/footer.php'; ?>
