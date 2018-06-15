<?php
$pageTitle = "Objects addition form";
$body_onload = "update_models();";
include 'view/header.php';
?>
<script src="/inc/js/update_objects.js" type ="text/javascript"></script>
<script src="/inc/js/check_form.js" type="text/javascript"></script>
<script type="text/javascript">
/*<![CDATA[*/
window.onload = function() {
  update_map('long1','lat1');
  update_country('long1','lat1','countryId1');
}

function validateForm()
{
    var form = document.getElementById("positions");

    if (!checkStringNotDefault(form["long1"], "") || !checkNumeric(form["long1"],-180,180) ||
        !checkStringNotDefault(form["lat1"], "") || !checkNumeric(form["lat1"],-90,90) ||
        !checkNumeric(form['offset1'],-999,999) ||
        !checkStringNotDefault(form["heading1"], "") || !checkNumeric(form['heading1'],0,359.999) ||
        !checkStringNotDefault(form["comment"], "") || !checkComment(form['comment']) ||
        !checkStringNotDefault(form["email"], "") || !checkEmail(form['email']) ||
        !checkStringNotDefault(form["recaptcha_response_field"], ""))
            return false;
}

function validateTabs()
{
    var form = document.getElementById("positions");
    $( "#tabs" ).tabs({ disabled: false });

    // Tab 1
    if (form["model_group_id"].value === 0) {
        $( "#tabs" ).tabs({ disabled: [1, 2] });
        return false;
    }
    // Tab 2
    if (form["long1"].value === "" || !checkNumeric(form["long1"],-180,180) ||
        form["lat1"].value === "" || !checkNumeric(form["lat1"],-90,90) ||
        form["offset1"].value === "" || !checkNumeric(form["offset1"],-10000,10000) ||
        form["heading1"].value === "" ||  !checkNumeric(form["heading1"],0,359.999)) {
        $( "#tabs" ).tabs({ disabled: [2] });
        return false;
    }
}
$(function() {
    $( "#tabs" ).tabs({ disabled: [1, 2] });
});
/*]]>*/
</script>
<link rel="stylesheet" href="/css/jquery-ui.min.css" type="text/css"/>
<script src="/inc/js/jquery-ui.min.js" type="text/javascript"></script>

<h1>Objects addition form</h1>

<p>
    This form's goal is to ease the submission of objects into the FlightGear Scenery database. There are currently <?php echo number_format($nbObjects, '0', '', ' ');?> objects in the database. Help us to make it more!<br/>
    Please read <a href="https://<?php echo $_SERVER['SERVER_NAME'];?>/contribute.php">this page</a> in order to understand what recommendations this script is looking for.<br />
    If you need some more help, just place your mouse over the left column (eg "Elevation Offset").
</p>
<p>
    <em style="color: red">*</em> mandatory field
</p>

<div id="tabs">
    <ul>
        <li><a href="#tabs-1">1: Model</a></li>
        <li><a href="#tabs-2">2: Location</a></li>
        <li><a href="#tabs-3">3: Submit</a></li>
    </ul>

    <form id="positions" method="post" action="app.php?c=AddObjects&amp;a=check" onsubmit="return validateForm();">
        <div id="tabs-1">
            <table>
                <tr>
                    <td><label for="model_group_id">Object's family<em>*</em><span>This is the family name of the object you want to add.</span></label></td>
                    <td>
            <?php
                        // Start the select form
                        echo "<select id=\"model_group_id\" name=\"model_group_id\" onchange=\"update_models(null,'modelId1'); validateTabs();\">" .
                             "<option selected=\"selected\" value=\"\">Please select a family</option>" .
                             "<option value=\"\">----</option>";
                        foreach ($modelsGroups as $modelsGroup) {
                            echo "<option value=\"".$modelsGroup->getId()."\">".$modelsGroup->getName()."</option>";
                        }
                        echo "</select>";

            ?>
                    </td>
                </tr>
                <tr>
                    <td><label for="modelId1">Model name<em>*</em><span>This is the name of the object you want to add, ie the name as it's supposed to appear in the .stg file.</span></label></td>
                    <td id="form_objects">
                        <!--Now everything is done via the Ajax stuff, and the results inserted here.-->
                    </td>
                </tr>
                <tr>
                    <td>Model thumbnail</td>
                    <td>
                        <img id="form_objects_thumb" src="" alt=""/>
                    </td>
                </tr>
            </table>
        </div>
        <div id="tabs-2">
            <table>
                <tr>
                    <td><label for="long1">Longitude<em>*</em><span>This is the WGS84 longitude of the object you want to add. Has to be between -180 and 180.</span></label></td>
                    <td>
                        <input type="text" name="long1" id="long1" maxlength="13" value="<?php echo $defaultLon;?>" onkeyup="checkNumeric(form['long1'],-180,180);validateTabs();" onchange="update_map('long1','lat1');update_country('long1','lat1','countryId1');" />
                    </td>
                    <td rowspan="5" style="width: 300px; height: 225px;">
                        <object id="map" data="/map/?z=1&lat=0&lon=0" type="text/html" width="300" height="225"></object>
                    </td>
                </tr>
                <tr>
                    <td><label for="lat1">Latitude<em>*</em><span>This is the WGS84 latitude of the object you want to add. Has to be between -90 and 90.</span></label></td>
                    <td>
                        <input type="text" name="lat1" id="lat1" maxlength="13" value="<?php echo $defaultLat;?>" onkeyup="checkNumeric(form['lat1'],-90,90);validateTabs();" onchange="update_map('long1','lat1');update_country('long1','lat1','countryId1');" />
                    </td>
                </tr>
                <tr>
                    <td><label for="countryId1">Country<em>*</em><span>This is the country where the model is located.</span></label></td>
                    <td>
                        <select name="countryId1" id="countryId1">
                            <?php
                                foreach($countries as $country) {
                                    echo "<option value=\"".$country->getCode()."\">".$country->getName()."</option>";
                                }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="offset1">Elevation offset<em>*</em><span>This is the vertical offset (in meters) between your model 'zero' (usually the bottom) and the terrain elevation at the specified coordinates. Use negative numbers to sink it into the ground, positive numbers to make it float, or 0 if there's no offset.</span></label> (see <a href="contribute.php#offset">here</a> for more help)
                    </td>
                    <td>
                        <input type="text" name="offset1" id="offset1" maxlength="10" value="0" onkeyup="checkNumeric(form['offset1'],-10000,10000);validateTabs();" />
                    </td>
                </tr>
                <tr>
                    <td><label for="heading1">Orientation<em>*</em><span>The orientation (in degrees) for the object you want to add - as it appears in the STG file (this is NOT the true heading). Let 0 if there is no specific orientation.</span></label></td>
                    <td>
                        <input type="text" name="heading1" id="heading1" maxlength="7" value="" onkeyup="checkNumeric(form['heading1'],0,359.999);validateTabs();" />
                    </td>
                </tr>
            </table>
        </div>
        <div id="tabs-3">
            <table>
                <tr>
                    <td><label for="comment">Comment<em>*</em><span>Please add a short (max 100 letters) statement why you are inserting this data. This will help the maintainers understand what you are doing. eg: I have placed a couple of aircraft shelters and static F16's at EHVK, please commit. Only alphanumerical, colon, semi colon, question and exclamation mark, arobace, minus, underscore, antislash and point are granted.</span></label></td>
                    <td>
                        <input type="text" name="comment" id="comment" maxlength="100" style="width: 100%;" value="" onkeyup="checkComment(this);" />
                    </td>
                </tr>
                <tr>
                    <td><label for="email">Email address<em>*</em><span>Please leave YOUR VALID email address over here. This will help you be informed of your submission process.</span></label></td>
                    <td>
                        <input type="text" name="email" id="email" maxlength="50" size="40" value="" onkeyup="checkEmail(this);" />
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="submit">
<?php
                        require_once 'view/captcha_form.php';
?>
                        <br />
                        <input type="submit" value="Submit position" />
                    </td>
                </tr>
            </table>
        </div>
    </form>
</div>

<?php include 'view/footer.php';
?>
