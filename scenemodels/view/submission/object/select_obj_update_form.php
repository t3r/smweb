<?php
// If we have more than one object, the user has to choose...

$pageTitle = "Objects update form";
include 'view/header.php';

echo "<p class=\"center\">".count($objects)." object(s) with WGS84 coordinates longitude: ".$long.", latitude: ".$lat." were found in the database.<br />Please select with the left radio button the one you want to update.</p>";

// Starting multi-solutions form
?>

<form id="update_position" method="post" action="app.php?c=UpdateObjects&amp;a=updateForm">
<table>

<?php
// Just used to put the selected button on the first entry
$is_first = true;
foreach ($objects as $object) {
    $modelMetadata = $modelMetadatas[$object->getModelId()];
    $objectPos = $object->getPosition();
?>
    <tr>
        <td colspan="5" background="white"><center><b>Object number #<?=$object->getId()?></b></center>
        </td>
    </tr>
    <tr>
        <th rowspan="7">
            <input type="radio" name="id_to_update" value="<?=$object->getId()?>" <?php echo ($is_first)?"checked=\"checked\"":""; ?> />
        </th>
        <td><span title="This is the family name of the object you want to update."><label>Object's family</label></span></td>
        <td colspan="4"><?=$modelMetadata->getModelsGroup()->getName()?></td>
    </tr>
    <tr>
        <td><span title="This is the model name of the object you want to update.">
        <label>Model name</label></span></td>
        <td colspan="4"><?=htmlspecialchars($modelMetadata->getName())?></td>
    </tr>
    <tr>
        <td><span title="This is the last update or submission date/time of the corresponding object.">
        <label>Date/Time of last update</label></span></td>
        <td colspan="4"><?=\FormatUtils::formatDateTime($object->getLastUpdated())?></td>
    </tr>
    <tr>
        <td><span title="This is the ground elevation (in meters) of the position where the object you want to update is located. Warning : if your model is sunk into the ground, the Elevation offset field is set below.">
        <label>Elevation</label></span></td>
        <td colspan="4"><?=$objectPos->getGroundElevation()?></td>
    </tr>
    <tr>
        <td><span title="This is the offset (in meters) between your model 'zero' and the elevation at the considered place (ie if it is sunk into the ground)."><label>Elevation Offset</label></span></td>
        <td colspan="4"><?=$objectPos->getElevationOffset()?></td>
    </tr>
    <tr>
        <td><span title="The orientation of the object you want to update - as it appears in the STG file (this is NOT the true heading). Let 0 if there is no specific orientation."><label>Orientation</label></span></td>
        <td colspan="4"><?=\ObjectUtils::headingTrue2STG($objectPos->getOrientation())?></td>
    </tr>
    <tr>
        <td><span title="The current text (metadata) shipped with the object. Can be generic, or specific (obstruction, for instance)."><label>Description</label></span></td>
        <td colspan="4"><?=htmlspecialchars($object->getDescription())?></td>
    </tr>
    <tr>
        <td><span title="This is the picture of the object you want to update"><label>Picture</label></span></td>
        <td><a href="app.php?c=Models&amp;a=view&amp;id=<?php echo $object->getModelId(); ?>"><img src="app.php?c=Models&amp;a=thumbnail&amp;id=<?php echo $object->getModelId(); ?>" alt="Thumbnail"/></a></td>
        <td><span title="This is the map around the object you want to update"><a style="cursor: help; ">Map</a></span></td>
        <td>
        <object data="/map/?lon=<?php echo $long; ?>&amp;lat=<?php echo $lat; ?>&amp;z=14" type="text/html" width="300" height="225"></object>
        </td>
    </tr>
<?php
    $is_first = false;
}
?>
        <tr>
            <td colspan="5" class="submit">
            <input type="submit" name="submit" value="I want to update the selected object!" />
            <input type="button" name="cancel" value="Cancel - I made a mistake!" onclick="history.go(-1)"/>
            </td>
        </tr>
    </table>
    </form>
<?php
    include 'view/footer.php';
?>
