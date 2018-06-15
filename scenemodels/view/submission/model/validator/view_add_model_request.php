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

$newModel = $request->getNewModel();
$newModelMD = $newModel->getMetadata();
$newObj = $request->getNewObject();
$newObjPos = $newObj->getPosition();
        
// Inserting libs
include_once 'inc/geshi/geshi.php';
$pageTitle = "Model Submission Form";

include 'view/header.php';

?>

<script type="text/javascript">
var lastAction;
$(document).ready(function () {
    // when a submit button is clicked, put its name into the action hidden field
    $("input[type='submit']").click(function () {lastAction = this.name; });
});    

function validateForm() {
    if (lastAction === 'accept' && $("input[name='au_add']").length > 0
            && $("input[name='au_add']:checked").length === 0) {
        alert("Please choose if the new author should be added.");
        return false;
    }
    
    return true;
}    
</script>

<p class="center">Model ADD request #<?=$request->getId()?></p>
<p class="center">The following model has passed all (numerous) verifications by the forementionned script. It should be fine to validate it. However, it's always sane to eye-check it.</p>

<p class="center">Email: <?=$request->getContributorEmail()?></p>

<form id="validation" method="post" action="app.php?c=AddModelValidator&amp;a=actionOnRequest" onsubmit="return validateForm();">
    <h3>New model information</h3>
    <table>
        <tr>
            <th>Data</th>
            <th>Value</th>
        </tr>
        <tr>
            <td>Author</td>
            <td>
<?php 
    echo $newModelMD->getAuthor()->getName().' ('.$newModelMD->getAuthor()->getEmail().")";
    if ($request->getNewAuthor() != null) {
        if ($authorExist) {
            echo ' - <strong>Author with same email already exists, will not be added</strong>';
        } else {
            echo ' - <strong>New author!</strong>';
            echo '<input type="radio" name="au_add" value="true"/> Add this author';
            echo '<input type="radio" name="au_add" value="false"/> Ignore';
        }
        
    }
?>
            </td>
        </tr>
        <tr>
            <td>Family</td>
            <td><?php echo $newModelMD->getModelsGroup()->getName(); ?></td>
        </tr>
        <tr>
            <td>Proposed Path Name</td>
            <td><?php echo $newModelMD->getFilename(); ?></td>
        </tr>
        <tr>
            <td>Full Name</td>
            <td><?php echo htmlspecialchars($newModelMD->getName()); ?></td>
        </tr>
        <tr>
            <td>Description</td>
            <td><?php echo htmlspecialchars($newModelMD->getDescription()); ?></td>
        </tr>
        <tr>
            <td>Corresponding Thumbnail</td>
            <td><img src="app.php?c=AddModelValidator&amp;a=getNewModelThumb&amp;sig=<?=$sig?>" alt="Thumbnail"/></td>
        </tr>
<?php
    // Now (hopefully) trying to manage the AC3D + XML + PNG texture files stuff
    $modelFiles = $newModel->getModelFiles();
?>
        <tr>
            <td>Download</td>
            <td><a href="app.php?c=AddModelValidator&amp;a=getNewModelPack&sig=<?=$sig?>">Download the submission as .tar.gz for external viewing.</a></td>
        </tr>
        <tr>
            <td>Corresponding AC3D File</td>
            <td>
                <object data="app.php?c=AddModelValidator&amp;a=modelViewer&sig=<?=$sig?>" type="text/html" width="720" height="620"/>
            </td>
        </tr>
        <tr>
            <td>Corresponding XML File</td>
            <td>
<?php
            $xmlContent = $modelFiles->getXMLFile();
            // Geshi stuff
            if (!empty($xmlContent)) {
                $geshi = new GeSHi($xmlContent, 'xml');
                $geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
                $geshi->set_line_style('background: #fcfcfc;');
                echo $geshi->parse_code();
            } else {
                echo "No XML file submitted.";
            }
?>
            </td>
        </tr>
        <tr>
            <td>Corresponding PNG Texture Files<br />(click on the pictures to get them bigger)</td>
            <td>
<?php
            $texturesNames = $modelFiles->getTexturesNames();
            foreach ($texturesNames as $textureName) {
                $texture_file = "/app.php?c=AddModelValidator&a=getNewModelTexture&sig=".$sig."&name=".$textureName;
                $texture_file_tn = "/app.php?c=AddModelValidator&a=getNewModelTextureTN&sig=".$sig."&name=".$textureName;

                $tmp = $modelFiles->getFileImageInfos($textureName);
                $width  = $tmp[0];
                $height = $tmp[1];
?>
                <a href="<?php echo $texture_file; ?>" rel="lightbox[submission]" />
                <img src="<?php echo $texture_file_tn; ?>" alt="Texture <?php echo $textureName; ?>" />
<?php
                echo $textureName." (Dim: ".$width."x".$height.")</a><br/>";
            }
?>
            </td>
        </tr>
    </table>
    <h3>Object position</h3>
    <table>
        <tr>
            <td>Latitude</td>
            <td><?php echo $newObjPos->getLatitude(); ?></td>
        </tr>
        <tr>
            <td>Longitude</td>
            <td><?php echo $newObjPos->getLongitude(); ?></td>
        </tr>
        <tr>
            <td>Map</td>
            <td>
                <object data="/map/?lon=<?=$newObjPos->getLongitude()?>&amp;lat=<?=$newObjPos->getLatitude()?>&amp;z=14" type="text/html" width="320" height="240"></object>
            </td>
        </tr>
        <tr>
            <td>Country</td>
            <td><?php echo $newObj->getCountry()->getName(); ?></td>
        </tr>
        <tr>
            <td>Elevation offset</td>
            <td><?php echo $newObjPos->getElevationOffset(); ?></td>
        </tr>
        <tr>
            <td>True DB orientation</td>
            <td><?php echo $newObjPos->getOrientation(); ?></td>
        </tr>
    </table>
    <h3>Conclusion</h3>
    <table>
        <tr>
            <td>Leave a comment to the submitter</td>
            <td><input type="text" name="maintainer_comment" size="85" placeholder="Drop a comment to the submitter"/></td>
        </tr>
        <tr>
            <td>Action</td>
            <td class="submit">
                <input type="hidden" name="sig" value="<?php echo $sig; ?>" />
                <input type="submit" name="accept" value="Submit model" />
                <input type="submit" name="reject" value="Reject model" />
            </td>
        </tr>
    </table>
</form>
<p class="center">This tool uses part of the following software: gl-matrix, by Brandon Jones, and Hangar, by Juan Mellado.</p>
<?php
require 'view/footer.php';
