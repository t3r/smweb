<?php

$pageTitle = "Objects addition requests form";
include 'view/header.php';
echo "<p class=\"center\">Request #". $request->getId()."</p>";
echo "<p class=\"center\">Email: ".$request->getContributorEmail()."</p>";
echo "<p class=\"center\">Comment: ".htmlspecialchars($request->getComment())."</p>";

$sig = $request->getSig();

echo "<form id=\"check_mass\" method=\"post\" action=\"app.php?c=AddObjectsValidator&amp;a=actionOnRequest\">";
echo "<table><tr><th>Line #</th><th>Longitude</th><th>Latitude</th><th>Country</th><th>Elevation</th><th>Elev. offset</th><th>True orientation</th><th>Model</th><th>Map</th></tr>";
$i = 1;
foreach ($request->getNewObjects() as $newObj) {
    $modelMD = $modelMDs[$newObj->getModelId()];
    $newObjPos = $newObj->getPosition();

    echo "<tr>" .
         "<td>".$i."</td>" .
         "<td>".$newObjPos->getLongitude()."</td>" .
         "<td>".$newObjPos->getLatitude()."</td>" .
         "<td>".$newObj->getCountry()->getName()."</td>" .
         "<td>".$newObjPos->getGroundElevation()."</td>" .
         "<td>".$newObjPos->getElevationOffset()."</td>" .
         "<td>".$newObjPos->getOrientation()."</td>" .
         "<td><a href='http://".$_SERVER['SERVER_NAME']."/app.php?c=Models&a=view&id=".$newObj->getModelId()."' target='_blank'>".htmlspecialchars($modelMD->getName())."</a></td>" .
         "<td><a href=\"/map/?lon=".$newObjPos->getLongitude()."&amp;lat=".$newObjPos->getLatitude()."&amp;z=14\">Map</a></td>" .
         "</tr>";

    $i++;
}
?>
    <tr>
        <td colspan="3">Leave a comment to the submitter</td>
        <td colspan="6"><input type="text" name="maintainer_comment" size="85" placeholder="Drop a comment to the submitter"/></td>
    </tr>
    <tr>
        <td colspan="9" class="submit">
            <input type="hidden" name="sig" value="<?php echo $sig;?>" />
            <input type="submit" name="accept" value="Accept object(s)" />
            <input type="submit" name="reject" value="Reject!" />
        </td>
    </tr>
</table>
<?php
    include 'view/footer.php';
