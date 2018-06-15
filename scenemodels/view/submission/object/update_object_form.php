<?php

/* 
 * Copyright (C) 2015 FlightGear Team
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */


$pageTitle = "Object update form";
$body_onload = "update_models();";
include 'view/header.php';

$objToUpPos = $objectToUp->getPosition();

?>
<script src="/inc/js/update_objects.js" type ="text/javascript"></script>
<script src="/inc/js/check_form.js" type="text/javascript"></script>
<script type="text/javascript">
/*<![CDATA[*/
window.onload = function() {
  update_map('new_long','new_lat');
  update_country('new_long','new_lat','new_country');
}

function validateForm()
{
    var form = document.getElementById("update");

    if (!checkStringNotDefault(form["new_long"], "") || !checkNumeric(form["new_long"],-180,180) ||
        !checkStringNotDefault(form["new_lat"], "") || !checkNumeric(form["new_lat"],-90,90) ||
        !checkNumeric(form['new_offset'],-999,999) ||
        !checkStringNotDefault(form["new_heading"], "") || !checkNumeric(form['new_heading'],0,359.999) ||
        !checkComment(form['new_ob_text']) ||
        !checkStringNotDefault(form["comment"], "") || !checkComment(form['comment']) ||
        !checkStringNotDefault(form["email"], "") || !checkEmail(form['email']) ||
        !checkStringNotDefault(form["recaptcha_response_field"], ""))
            return false;
}

/*]]>*/
</script>
    <p class="center">You have asked to update object <?php echo "<a href=\"app.php?c=Objects&amp;a=view&amp;id=".$idToUpdate."\">#".$idToUpdate."</a>";?>.</p>

    <form id="update" method="post" action="app.php?c=UpdateObjects&amp;a=check" onsubmit="return validateForm();">
      <input type="hidden" name="id_to_update" value="<?php echo $idToUpdate; ?>" />
      <table>
        <tr>
          <th></th>
          <th>Current value</th>
          <th>New value</th>
        </tr>
        <tr>
          <td>
            <span title="This is the family name of the object you want to update."><label for="model_group_id">Object's family<em>*</em></label></span>
          </td>
          <td>
            <?php echo $modelMDToUp->getModelsGroup()->getName(); ?>
          </td>
          <td>
<?php
$id_family = $modelMDToUp->getModelsGroup()->getId();

if (!$modelMDToUp->getModelsGroup()->isStatic()) {
    // Show all the families other than the static family
    
    // Start the select form
    echo "<select id=\"model_group_id\" name=\"model_group_id\" onchange=\"update_models();\">";
    foreach ($modelsGroups as $modelsGroup) {
        $name = preg_replace('/ /',"&nbsp;",$modelsGroup->getName());
        if ($id_family == $modelsGroup->getId()) {
            echo "<option selected=\"selected\" value=\"".$modelsGroup->getId()."\">".$name."</option>";
        } else {
            echo "<option value=\"".$modelsGroup->getId()."\">".$name."</option>";
        }
    }
    echo "</select>";
}
else {
    echo "Static";
    echo "      <input name=\"model_group_id\" type=\"hidden\" value=\"0\"/>";
}
?>
          </td>
        </tr>
        <tr>
          <td>
            <span title="This is the model name of the object you want to update, ie the name as it's supposed to appear in the .stg file.">
            <label for="modelId">Model name<em>*</em></label></span>
          </td>
          <td><?php echo htmlspecialchars($modelMDToUp->getName());?></td>
          <td>
<?php

if (!$modelMDToUp->getModelsGroup()->isStatic()) {

    echo "<div id=\"form_objects\">";
    echo "    <select name='modelId' id='modelId' onchange='change_thumb()'>";
    
    // TODO move it to controller layer
    $modelMetadatas = $modelDaoRO->getModelMetadatasByGroup($id_family, 0, "ALL", "mo_path");

    // Showing the results.
    foreach ($modelMetadatas as $modelMetadata) {
        $id   = $modelMetadata->getId();
        $path = $modelMetadata->getFilename();

        if ($modelMDToUp->getId() == $modelMetadata->getId()) {
            echo "<option selected=\"selected\" value='".$id."'>".$path."</option>";
        } else {
            echo "<option value='".$id."'>".$path."</option>";
        }
    }

    echo "</select>";
    echo "</div>";

} else {
    echo "      <input name=\"modelId\" type=\"hidden\" value=\"".$objectToUp->getModelId()."\"/>";
    echo htmlspecialchars($modelMDToUp->getName());
}
?>
          </td>
        </tr>
        <tr>
          <td>
            <span title="This is the WGS84 longitude of the object you want to update. Has to be between -180.000000 and +180.000000.">
            <label for="new_long">Longitude<em>*</em></label></span>
          </td>
          <td>
            <?=$objToUpPos->getLongitude()?>
          </td>
          <td>
            <input type="text" name="new_long" id="new_long" maxlength="13" value="<?php echo $defaultLon;?>" onchange="update_map('new_long','new_lat');" onkeyup="checkNumeric(this,-180,180);" />
          </td>
        </tr>
        <tr>
          <td>
            <span title="This is the WGS84 latitude of the object you want to update. Has to be between -90.000000 and +90.000000.">
            <label for="new_lat">Latitude<em>*</em></label></span>
          </td>
          <td>
            <?=$objToUpPos->getLatitude()?>
          </td>
          <td>
            <input type="text" name="new_lat" id="new_lat" maxlength="13" value="<?php echo $defaultLat;?>" onchange="update_map('new_long','new_lat');" onkeyup="checkNumeric(this,-90,90);" />
          </td>
        </tr>
        <tr>
            <td>
                <span title="This is the country of the object you want to update. Not editable, though, cause automatic procedures are doing it.">
                <label for="new_country">Country</label></span>
            </td>
            <td>
<?php
        $countryName = $objectToUp->getCountry()->getName();
        echo ($countryName == '')?"Unknown!":$countryName;
?>
            </td>
            <td>
                <select name="new_country" id="ob_country">
<?php
                    foreach($countries as $country) {
                        if ($objectToUp->getCountry()->getCode() == $country->getCode()) {
                            echo "<option value=\"".$country->getCode()."\" selected=\"selected\">".$country->getName()."</option>";
                        } else {
                            echo "<option value=\"".$country->getCode()."\">".$country->getName()."</option>";
                        }
                       
                    }
?>
                </select>
            </td>
        </tr>
        <tr>
          <td>
            <span title="This is the vertical offset (in meters) between your model 'zero' (usually the bottom) and the terrain elevation at the specified coordinates. Use negative numbers to sink it into the ground, positive numbers to make it float, or 0 if there's no offset.">
            <label for="new_offset">Elevation Offset<em>*</em></label> (see <a href="../../contribute.php#offset">here</a> for more help)</span>
          </td>
          <td>
            <?=$objToUpPos->getElevationOffset()?>
          </td>
          <td>
            <input type="text" name="new_offset" id="new_offset" maxlength="10" value="<?=$objToUpPos->getElevationOffset()?>" onkeyup="checkNumeric(this,-10000,10000);" />
          </td>
        </tr>
        <tr>
          <td>
            <span title="The orientation of the object you want to update - as it appears in the STG file (this is NOT the true heading). Let 0 if there is no specific orientation."><label for="new_heading">Orientation<em>*</em></label></span>
          </td>
          <td>
            <?php $actual_orientation = \ObjectUtils::headingTrue2STG($objToUpPos->getOrientation()); echo $actual_orientation; ?>
          </td>
          <td>
            <input type="text" name="new_heading" id="new_heading" maxlength="7" value="<?php echo $actual_orientation; ?>" onkeyup="checkNumeric(this,0,359.999);" />
          </td>
        </tr>
        <tr>
            <td><span title="The current text (metadata) shipped with the object. Can be generic, or specific (obstruction, for instance)."><label>Description</label></span></td>
            <td><?=htmlspecialchars($objectToUp->getDescription())?></td>
            <td>
                <input type="text" name="new_ob_text" id="new_ob_text" size="50" maxlength="100" value="<?=$objectToUp->getDescription()?>" onkeyup="checkComment(this);" />
            </td>
        </tr>
        <tr>
            <td><span title="This is the picture of the object you want to update"><label>Picture</label></span></td>
            <td><img src="app.php?c=Models&amp;a=thumbnail&amp;id=<?php $model_id = $objectToUp->getModelId(); echo $model_id; ?>" alt="Actual thumbnail"/></td>
            <td><img id="form_objects_thumb" src="app.php?c=Models&amp;a=thumbnail&amp;id=<?php echo $model_id; ?>" alt="New thumbnail"/></td>
        </tr>
        <tr>
            <td><span title="This is the map around the object you want to update"><label>Map</label></span></td>
            <td><object data="/map/?lon=<?php echo $objToUpPos->getLongitude(); ?>&amp;lat=<?php echo $objToUpPos->getLatitude(); ?>&amp;z=14" type="text/html" width="100%" height="225"></object></td>
            <td><object id="map" data="/map/?lon=<?php echo $objToUpPos->getLongitude(); ?>&amp;lat=<?php echo $objToUpPos->getLatitude(); ?>&amp;z=14" type="text/html" width="100%" height="225"></object></td>
        </tr>
        <tr>
          <td><span title="Please add a short (max 100 letters) statement why you are updating this data. This will help the maintainers understand what you are doing. eg: this model was misplaced, so I'm updating it.">
            <label for="comment">Comment<em>*</em></label></span>
          </td>
          <td colspan="2">
            <input type="text" name="comment" id="comment" maxlength="100" size="100" value="" onkeyup="checkComment(this)"/>
          </td>
        </tr>
        <tr>
          <td><span title="Please leave YOUR VALID email address over here. This will help you be informed of your submission process. EXPERIMENTAL">
            <label for="email">Email address<em>*</em></label></span>
          </td>
          <td colspan="2">
            <input type="text" name="email" id="email" maxlength="50" size="50" value="" onkeyup="checkEmail(this);"/>
          </td>
        </tr>
        <tr>
          <td colspan="3" class="submit">
<?php
            require 'view/captcha_form.php';
?>
            <input type="submit" name="submit" value="Update this object!" />
            <input type="button" name="cancel" value="Cancel - Do not update!" onclick="history.go(-1)"/>
          </td>
        </tr>
      </table>
    </form>
<?php
    include 'view/footer.php';
?>
