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



// Inserting libs
include_once 'inc/geshi/geshi.php';


$pageTitle = "Models update form";

// Working on the model, now
$newModel = $request->getNewModel();
$newModelMD = $newModel->getMetadata();
$oldModel = $request->getOldModel();
$oldModelMD = $oldModel->getMetadata();

include 'view/header.php';

?>

<p class="center">Model UPDATE Request #<?=$request->getId()?></p>

<p class="center">The following model has passed all (numerous) verifications. It should be fine to validate it. However, it's always sane to eye-check it.</p>

<p class="center">Email: <?=htmlspecialchars($request->getContributorEmail())?></p>
<p class="center">Comment: <?=htmlspecialchars($request->getComment())?></p>

<form id="validation" method="post" action="app.php?c=UpdateModelValidator&amp;a=actionOnRequest" onsubmit="return validateForm();">
<table>
    <tr>
        <th>Data</th>
        <th>Old Value</th>
        <th>New Value</th>
    </tr>
    <tr>
        <td>Author</td>
        <td>
            <?php
            echo $oldModelMD->getAuthor()->getName() ." (".$oldModelMD->getAuthor()->getEmail().")";
            ?>
        </td>
        <td>
            <?php echo $newModelMD->getAuthor()->getName()." (".$newModelMD->getAuthor()->getEmail().")"; ?>
        </td>
    </tr>
    <tr>
        <td>Family</td>
        <td><?php echo $oldModelMD->getModelsGroup()->getName(); ?></td>
        <td><?php echo $newModelMD->getModelsGroup()->getName(); ?></td>
    </tr>
    <tr>
        <td>Proposed Path Name</td>
        <td><?php echo $oldModelMD->getFilename(); ?></td>
        <td><?php echo $newModelMD->getFilename(); ?></td>
    </tr>
    <tr>
        <td>Full Name</td>
        <td><?php echo htmlspecialchars($oldModelMD->getName()); ?></td>
        <td><?php echo htmlspecialchars($newModelMD->getName()); ?></td>
    </tr>
    <tr>
        <td>Description</td>
        <td><?php echo htmlspecialchars($oldModelMD->getDescription()); ?></td>
        <td><?php echo htmlspecialchars($newModelMD->getDescription()); ?></td>
    </tr>
    <tr>
        <td>Corresponding Thumbnail</td>
        <td><img src="app.php?c=Models&amp;a=thumbnail&amp;id=<?php echo $oldModelMD->getId() ?>" alt="Thumbnail"/></td>
        <td><img src="app.php?c=UpdateModelValidator&amp;a=getNewModelThumb&amp;sig=<?=$sig?>" alt="Thumbnail"/></td>
    </tr>
<?php
    // Now (hopefully) trying to manage the AC3D + XML + PNG texture files stuff
    $newModelFiles = $newModel->getModelFiles();
?>
    <tr>
        <td>Download</td>
         <td><a href="app.php?c=Models&a=getPackage&amp;id=<?=$oldModelMD->getId()?>">Download OLD MODEL as .tar.gz</a></td>
        <td><a href="app.php?c=UpdateModelValidator&amp;a=getNewModelPack&sig=<?=$sig?>">Download NEW MODEL as .tar.gz</a></td>
    </tr>
    <tr>
        <td>Corresponding AC3D File</td>
        <td colspan="2">
            <h3>Original model:</h3>
            <object data="app.php?c=Models&a=modelViewer&amp;id=<?=$oldModelMD->getId()?>" type="text/html" width="720" height="620"></object>
            <br/>
            <h3>New model:</h3>
            <object data="app.php?c=UpdateModelValidator&amp;a=modelViewer&sig=<?=$sig?>" type="text/html" width="720" height="620"></object>
        </td>
    </tr>
    <tr>
        <td>Corresponding XML File</td>
        <td>
<?php
    
            $oldXmlContent = $oldModel->getModelFiles()->getXMLFile();
            if (!empty($oldXmlContent)) {
                $geshi = new GeSHi($oldXmlContent, 'xml');
                $geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
                $geshi->set_line_style('background: #fcfcfc;');
            
                echo $geshi->parse_code();
            } else {
                echo "No XML file.";
            }
?>
        </td>
        <td>
<?php
            $xmlContent = $newModelFiles->getXMLFile();
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
            $texturesNames = $oldModel->getModelFiles()->getTexturesNames();
            foreach ($texturesNames as $textureName) {
                $texture_file = "/app.php?c=UpdateModelValidator&a=getOldModelTexture&sig=".$sig."&name=".$textureName;
                $texture_file_tn = "/app.php?c=UpdateModelValidator&a=getOldModelTexture&sig=".$sig."&name=".$textureName;

                $tmp = $oldModel->getModelFiles()->getFileImageInfos($textureName);
                $width  = $tmp[0];
                $height = $tmp[1];
?>
                <a href="<?=$texture_file?>" rel="lightbox[submission]" />
                <img src="<?=$texture_file_tn?>" alt="Texture <?=$textureName?>" />
<?php
                echo $textureName." (Dim: ".$width."x".$height.")</a><br/>";
            }
?>
        </td>
        <td>
<?php
            $texturesNames = $newModelFiles->getTexturesNames();
            // Sending the directory as parameter. This is no user input, so low risk. Needs to be urlencoded.
            foreach ($texturesNames as $textureName) {
                $texture_file = "/app.php?c=UpdateModelValidator&a=getNewModelTexture&sig=".$sig."&name=".$textureName;
                $texture_file_tn = "/app.php?c=UpdateModelValidator&a=getNewModelTextureTN&sig=".$sig."&name=".$textureName;

                $tmp = $newModelFiles->getFileImageInfos($textureName);
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
    <tr>
        <td>Leave a comment to the submitter</td>
        <td colspan="2"><input type="text" name="maintainer_comment" size="85" placeholder="Drop a comment to the submitter" /></td>
    </tr>
    <tr>
        <td>Action</td>
        <td colspan="2" class="submit">
            <input type="hidden" name="sig" value="<?php echo $sig; ?>" />
            <input type="submit" name="accept" value="Accept model update" />
            <input type="submit" name="reject" value="Reject update" />
        </td>
    </tr>
</table>
</form>
<p class="center">This tool uses part of the following software: gl-matrix, by Brandon Jones, and Hangar, by Juan Mellado.</p>
<?php
require 'view/footer.php';
