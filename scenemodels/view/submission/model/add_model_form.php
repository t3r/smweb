<?php
$pageTitle = "Models addition form";
require 'view/header.php';
?>
<script type="text/javascript" src="/inc/js/update_objects.js"></script>
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

    if (!checkNumeric(form["longitude"],-180,180) ||
        !checkNumeric(form["latitude"],-90,90) ||
        form["mo_name"].value === "" || !checkComment(form["mo_name"]) ||
        !checkNumeric(form["offset"],-999,999) ||
        !checkNumeric(form["heading"],0,359.999) ||
        !checkComment(form["notes"]))
        return false;
<?php
    if (\Config::isCaptchaEnabled())
        echo 'if (form["recaptcha_response_field"].value === "")return false;';
?>    
    
    return !ajaxSubmit("positions",
            "app.php?c=AddModel&a=addRequest&ajaxCheck=1",
            "app.php?c=AddModel&a=success&id=");
}

function validateTabs()
{
    var form = document.getElementById("positions");
    $( "#tabs" ).tabs({ disabled: false });

    // Tab 1
    if (!checkComment(form["mo_name"]) ||
        form["mo_name"].value === "" ||
        !ac3DSelected ||
        !thumbSelected) {
        $( "#tabs" ).tabs({ disabled: [1, 2] });
        return false;
    }
    // Tab 2
    if (form["longitude"].value === "" || !checkNumeric(form["longitude"],-180,180) ||
        form["latitude"].value === "" || !checkNumeric(form["latitude"],-90,90) ||
        form["offset"].value === "" || !checkNumeric(form['offset'],-10000,10000) ||
        form["heading"].value === "" ||  !checkNumeric(form['heading'],0,359.999)) {
        $( "#tabs" ).tabs({ disabled: [2] });
        return false;
    }
}

$(function() {
    $( "#tabs" ).tabs({ disabled: [1, 2] });
    
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
    
    $("#mo_author").change(function() {
        if ($("#mo_author").val() === "1") {
            $("#newAuthorName").show();
            $("#newAuthorEmail").show();
        } else {
            $("#newAuthorName").hide();
            $("#newAuthorEmail").hide();
        }
    });
});
/*]]>*/
</script>
<link rel="stylesheet" href="/css/jquery-ui.min.css" type="text/css"/>
<script src="/inc/js/jquery-ui.min.js" type="text/javascript"></script>

<div id="loadingScreen" style="display:none"></div>
<div id="submit-dialog" style="display:none">
    <div id="submit-dialog-errors"></div>
    Please correct the models directly in your computer and submit again
    (no need to reselect them!)
</div>


<h1>Submit a model</h1>

<p>
    This form goal is to ease the submission of static and shared 3D models into the FlightGear scenery database.
    There are currently <?php echo number_format($nbModels, '0', '', ' '); ?> unique models in <a href="http://<?php echo $_SERVER['SERVER_NAME'];?>/app.php?c=Models&amp;a=browseRecent">our database</a>. Help us to make it more!
</p>
<p>
    Hover your mouse over the various field titles (left column) to view some information about what to do with that particular field. Please read <a href="http://<?php echo $_SERVER['SERVER_NAME'];?>/contribute.php">this page</a> for a better understanding of the various requirements.
</p>

<div id="tabs">
    <ul>
        <li><a href="#tabs-1">1: Model</a></li>
        <li><a href="#tabs-2">2: Location</a></li>
        <li><a href="#tabs-3">3: Submit</a></li>
    </ul>

    <form id="positions" method="post" action="app.php?c=AddModel&amp;a=addRequest" enctype="multipart/form-data" onsubmit="return validateForm();">
        <div id="tabs-1">
            <ul>
                <li>Files have to share a common name, for instance: modelname.ac, modelname.xml and modelname.png.</li>
                <li>Please do not group separate buildings into one AC file. The terrain elevation is subject to updates, so this could lead to inaccuracies.</li>
                <li>Do not add trees or flat surfaces (such as soccer fields) into your AC file.</li>
                <li>PNG resolution must be a power of 2 in width and height.</li>
                <li>If you have multiple textures, name them modelname1.png, modelname2.png etc.</li>
                <li>XML file must start with a classic XML header, such as: &lt;?xml version="1.0" encoding="UTF-8" ?&gt;. See <a href="TheNameOfYourACFile.xml">here</a> for a quick example. Only include XML if necessary for the model.</li>
                <li>The thumbnail must be in JPEG and 320*240 resolution.</li>
            </ul>
            <table style="width: auto; margin-left: auto; margin-right: auto;">
                <tr>
                    <td style="width: 200px;">
                        <label for="model_group_id">Model's family<em>*</em><span>This is the family name of the model you want to add. If your 3D model is going to be shared, use the proper family. If it's going to be a static one, then choose the static family.</span></label>
                    </td>
                    <td>
                        <select name="model_group_id" id="model_group_id">
                            <?php
                            foreach ($modelsGroups as $modelsGroup) {
                                // Selecting static family by default
                                if($modelsGroup->isStatic()) {
                                    echo "<option value=\"".$modelsGroup->getId()."\" selected=\"selected\">".$modelsGroup->getName()."</option>";
                                } else {
                                    echo "<option value=\"".$modelsGroup->getId()."\">".$modelsGroup->getName()."</option>";
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="mo_name">Model name<em>*</em><span>Please add a short (max 100 letters) name of your model (eg : Cornet antenna radome - Brittany - France).</span></label>
                    </td>
                    <td>
                        <input type="text" name="mo_name" id="mo_name" maxlength="100" size="40" onkeyup="checkComment(this);validateTabs();"/>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="notes">Model description<span>Please add a short statement giving more details on this data. eg: The Cite des Telecoms, colocated with the cornet radome, is a telecommunications museum.</span></label>
                    </td>
                    <td>
                        <input type="text" name="notes" id="notes" maxlength="500" size="40" value="" onkeyup="checkComment(this);validateTabs();" />
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
                    <td>
                        <input type="file" name="xml_file" id="xml_file" class="multi" maxlength="1" accept="text/xml" />
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="png_files">PNG texture file(s)<span>This (Those) is (are) the PNG texture(s) file(s) of your model. Has to be a power of 2 in width and height.</span></label>
                    </td>
                    <td>
                        <input type="file" name="png_file[]" id="png_files" class="multi" maxlength="12" accept="image/png" />
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="mo_thumbfile">320x240 JPEG thumbnail<em>*</em><span>This is a nice picture representing your model in FlightGear in the best way.</span></label>
                    </td>
                    <td>
                        <input type="file" name="mo_thumbfile" id="mo_thumbfile" />
                    </td>
                </tr>
            </table>
        </div>
        <div id="tabs-2">
            <ul>
                <li>Please locate your model, even when you are adding a shared model.</li>
                <li>The country is the one where the model is located. After entering longitude and latitude, we will try to propose a country. Please check if it is correct.</li>
                <li>For the elevation, use the elevation offset, which is the difference between ground elevation and model's "zero".</li>
            </ul>
            <table style="width: auto; margin-left: auto; margin-right: auto;">
                <tr>
                    <td style="width: 200px;">
                        <label for="longitude">Longitude<em>*</em><span>This is the WGS84 longitude of the object you want to add. Has to be between -180 and 180 and not 0.</span></label>
                    </td>
                    <td>
                        <input type="text" name="longitude" id="longitude" maxlength="11" value="" onchange="update_map('longitude','latitude');" onkeyup="checkNumeric(this,-180,180);update_country('longitude','latitude','ob_country');validateTabs();" />
                    </td>
                    <td rowspan="5" style="width: 300px; height: 225px;">
                        <object id="map" data="/map/?z=1&lat=0&lon=0" type="text/html" width="300" height="225"></object>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="latitude">Latitude<em>*</em><span>This is the WGS84 latitude of the object you want to add. Has to be between -90 and 90 and not 0.</span></label>
                    </td>
                    <td>
                        <input type="text" name="latitude" id="latitude" maxlength="10" value="" onchange="update_map('longitude','latitude');" onkeyup="checkNumeric(this,-90,90);update_country('longitude','latitude','ob_country');validateTabs();" />
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="ob_country">Country<span>This is the country code where the model is located.</span></label>
                    </td>
                    <td>
                        <select name="ob_country" id="ob_country">
                            <?php
                            foreach($countries as $country) {
                                echo "<option value=\"".$country->getCode()."\">".rtrim($country->getName())."</option>";
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="offset">Elevation offset<em>*</em><span>This is the vertical offset (in meters) between your model 'zero' (usually the bottom) and the terrain elevation at the specified coordinates. Use negative numbers to sink it into the ground, positive numbers to make it float, or 0 if there's no offset.</span></label> (see <a href="../../contribute.php#offset">here</a> for more help)
                    </td>
                    <td>
                        <input type="text" name="offset" id="offset" maxlength="10" value="0" onkeyup="checkNumeric(this,-10000,10000);validateTabs();" />
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="heading">Orientation<em>*</em><span>The orientation (in degrees) of the object you want to add - as it appears in the .stg file (this is NOT the true heading). Let 0 if there is no specific orientation.</span></label>
                    </td>
                    <td>
                        <input type="text" name="heading" id="heading" maxlength="7" value="" onkeyup="checkNumeric(this,0,359.999);validateTabs();" />
                    </td>
                </tr>
            </table>
        </div>
        <div id="tabs-3">
            <ul>
                <li>Choose the author for the model. If you are not listed, choose "Unknown" or ask for addition on the forums or mailing list. If you are building a new model based on another one, put your name here, and the original author's one into the "Model description" field.</li>
                <!--<li>Don't forget to feed the Captcha, it's a mandatory item as well. Don't know what a Captcha is or what its goal is? Learn more <a href="http://en.wikipedia.org/wiki/Captcha">here</a></li>-->
                <li>Be patient, there are human beings with real life constraints behind, and don't feel blamed if your models are rejected, but try to understand why.</li>
            </ul>
            <table style="width: auto; margin-left: auto; margin-right: auto;">
                <tr>
                    <td>
                        <label for="mo_author">Author<em>*</em><span>This is the name of the author. If the author is not listed, choose "Other" and complete the author's information in the fields that appear. This name is the author of the true creator of the model, if you just converted a model and were granted to do so, then also use the line below.</span></label>
                    </td>
                    <td>
                        <select name="mo_author" id="mo_author">
                            <?php
                            foreach($authors as $author) {
                                if ($author->getId() == 1) {
                                    echo "<option value=\"".$author->getId()."\" selected=\"selected\">".$author->getName()."</option>";
                                } else {
                                    echo "<option value=\"".$author->getId()."\">".$author->getName()."</option>";
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr id="newAuthorName">
                    <td>
                        <label for="au_name">Your name<em>*</em><span>Your name.</span></label>
                    </td>
                    <td>
                        <input type="text" name="au_name" id="au_name" maxlength="50" size="30" value="" onkeyup="checkComment(this);" />
                    </td>
                </tr>
                <tr id="newAuthorEmail">
                    <td>
                        <label for="au_email">Your email<em>*</em><span>Your email.</span></label>
                    </td>
                    <td>
                        <input type="text" name="au_email" id="au_email" maxlength="50" size="30" value="" onkeyup="checkEmail(this);" />
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="submit">
                        <input type="checkbox" name="gpl"/> I accept to release all my contribution under <a href="http://www.gnu.org/licenses/gpl-2.0.html">GNU GENERAL PUBLIC LICENSE Version 2, June 1991.</a><br/>
<?php
                        require 'view/captcha_form.php';
?>
                        <br />
                        <input type="hidden" name="MAX_FILE_SITE" value="2000000" />
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
