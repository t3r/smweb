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

$pageTitle = "Objects deletion form";
include 'view/header.php';

?>


<script src="/inc/js/check_form.js" type="text/javascript"></script>
<script type="text/javascript">
/*<![CDATA[*/
function validateForm()
{
    var form = document.getElementById("delete_position");

    if (!checkStringNotDefault(form["comment"], "") || !checkComment(form['comment']) ||
        (form['email'].value!=="" && !checkEmail(form['email'])))
        return false;

}
/*]]>*/
</script>

    <p class="center"><?php echo count($candidateObjects);?> objects with WGS84 coordinates longitude: <?php echo $long;?>, latitude: <?php echo $lat;?> have been found in the database.<br />Please select with the left radio button the one you want to delete.</p>

    <ul class="warning">If you want to replace an object which is set as an "OBSTRUCTION" (see the object's family hereunder) by a 3D model, please consider adding the 3D model <b>first</b> - ie before deleting the shared object.</ul>

    <form id="delete_position" method="post" action="app.php?c=DeleteObjects&amp;a=requestForDelete" onsubmit="return validateForm();">
    <table>

<?php

    // Starting multi-solutions form
    $is_first = true; // Just used to put the selected button on the first entry
    foreach ($candidateObjects as $candidateObj) {
        $candidateModelMD = $candidateModelMDs[$candidateObj->getModelId()];
        $candidateObjPos = $candidateObj->getPosition();
?>
        <tr>
            <th colspan="5">Object number #<?php echo $candidateObj->getId(); ?></th>
        </tr>
        <tr>
            <th rowspan="10">
                <input type="radio" name="delete_choice" value="<?php echo $candidateObj->getId();?>" <?php echo ($is_first)?"checked=\"checked\"":"";?> />
            </th>
            <td><span title="This is the family name of the object you want to delete."><label>Object's family</label></span></td>
            <td colspan="4"><?php echo $candidateModelMD->getModelsGroup()->getName(); ?></td>
        </tr>
        <tr>
            <td><span title="This is the model name of the object you want to delete."><label>Model name</label></span></td>
            <td colspan="4"><?php echo htmlspecialchars($candidateModelMD->getName()); ?></td>
        </tr>
        <tr>
            <td><span title="This is the WGS84 longitude of the object you want to update. Has to be between -180.000000 and +180.000000."><label>Longitude</label></span></td>
            <td colspan="4"><?php $longitude = $candidateObjPos->getLongitude(); echo $longitude; ?></td>
        </tr>
        <tr>
            <td><span title="This is the WGS84 latitude of the object you want to update. Has to be between -90.000000 and +90.000000."><label>Latitude</label></span></td>
            <td colspan="4"><?php $latitude = $candidateObjPos->getLatitude(); echo $latitude; ?></td>
        </tr>
        <tr>
            <td><span title="This is the last update or submission date/time of the corresponding object."><label>Date/Time of last update</label></span></td>
            <td colspan="4"><?php echo \FormatUtils::formatDateTime($candidateObj->getLastUpdated()); ?></td>
        </tr>
        <tr>
            <td><span title="This is the ground elevation (in meters) of the position where the object you want to delete is located. Warning: if your model is sunk into the ground, the Elevation offset field is set below."><label>Elevation</label></span></td>
            <td colspan="4"><?php echo $candidateObjPos->getGroundElevation(); ?></td>
        </tr>
        <tr>
            <td><span title="This is the offset (in meters) between your model 'zero' and the elevation at the considered place (ie if it is sunk into the ground)."><label>Elevation Offset</label></span></td>
            <td colspan="4"><?php echo $candidateObjPos->getElevationOffset(); ?></td>
        </tr>
        <tr>
            <td><span title="The orientation of the object you want to delete - as it appears in the STG file (this is NOT the true heading). Let 0 if there is no specific orientation."><label>Orientation</label></span></td>
            <td colspan="4"><?php echo \ObjectUtils::headingTrue2STG($candidateObjPos->getOrientation()); ?></td>
        </tr>
        <tr>
            <td><span title="Object's family (OBSTRUCTION, LANDMARK, ...)."><label>Object's family</label></span></td>
            <td colspan="4"><?php echo $objGroups[$candidateObj->getGroupId()]->getName(); ?></td>
        </tr>
        <tr>
            <td><span title="The current text (metadata) shipped with the object. Can be generic, or specific (obstruction, for instance)."><label>Description</label></span></td>
            <td colspan="4"><?php echo htmlspecialchars($candidateObj->getDescription()); ?></td>
        </tr>
        <tr>
            <td><span title="This is the picture of the object you want to delete"><label>Picture</label></span></td>
            <td><a href="http://<?php echo $_SERVER['SERVER_NAME'];?>/app.php?c=Models&amp;a=view&amp;id=<?php $model_id = $candidateObj->getModelId(); echo $model_id; ?>"><img src="app.php?c=Models&amp;a=thumbnail&amp;id=<?php echo $model_id; ?>" alt="Thumbnail"/></a></td>
            <td><span title="This is the map around the object you want to delete"><label>Map</label></span></td>
            <td><object data="/map/?lon=<?=$longitude?>&amp;lat=<?=$latitude?>&amp;z=14" type="text/html" width="300" height="225"></object></td>
        </tr>
<?php
        $is_first = false;
    }
?>
        <tr>
            <td><span title="Please add a short (max 100 letters) statement why you are deleting this data. This will help the maintainers understand what you are doing. eg: this model is in a river, so please delete it"><label for="comment">Comment<em>*</em></label></span></td>
            <td colspan="4"><input type="text" id="comment" name="comment" maxlength="100" size="100" value="" onchange="checkComment(this);"/></td>
        </tr>
        <tr>
            <td><span title="Please leave YOUR VALID email address over here. This will help you be informed of your submission process."><label for="email">Email address</label></span></td>
            <td colspan="4"><input type="text" id="email" name="email" maxlength="50" size="50" value="" onchange="checkEmail(this);"/></td>
        </tr>
        <tr>
            <td colspan="5" class="submit">
            <input name="step" type="hidden" value="3" />
<?php
            require 'view/captcha_form.php';
?>
            <br />
            <input type="submit" name="submit" value="Forward for deletion!" />
            <input type="button" name="cancel" value="Cancel this deletion!" onclick="history.go(-1)"/>
            </td>
        </tr>
    </table>
    </form>
<?php
    include 'view/footer.php';
