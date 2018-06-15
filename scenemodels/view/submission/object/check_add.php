<?php

$pageTitle = "Objects addition form";
require 'view/header.php';

// Display errors if exist
foreach ($errors as $error) {
    echo '<p class="warning">'. $error->getMessage() .'</p>';
}

// Email
if (isset($safeEmail)) {
    echo "<p class=\"ok\">Email: ".$safeEmail."</p>";
} else {
    echo "<p class=\"warning\">No email was given (not mandatory) or email mismatch!</p>";
}

// Display objects and their errors
echo "<table>".
     "<tr><th>#</th><th>Model</th><th>Longitude</th><th>Latitude</th><th>Elev. offset</th><th>Heading</th><th>Country</th><th>Results</th></tr>";
foreach ($objectLinesRequests as $lineNb => $objectLineRequest) {
    echo '<tr><td>'.$lineNb.'</td>';

    if ($objectLineRequest->getObject() != null) {
        $object = $objectLineRequest->getObject();
        $objPos = $object->getPosition();

        echo '<td>'.htmlspecialchars($modelMDs[$object->getModelId()]->getName()).'</td>'.
             '<td>'.$objPos->getLongitude().'</td>'.
             '<td>'.$objPos->getLatitude().'</td>'.
             '<td>'.$objPos->getElevationOffset().'</td>'.
             '<td>'.\ObjectUtils::headingTrue2STG($objPos->getOrientation()).'</td>'.
             '<td>'.$object->getCountry()->getName().'</td>';
    } else {
        echo '<td colspan="6"></td>';
    }
    
    if (!empty($objectLineRequest->getErrors())) {
        echo "<td style='background-color: rgb(200, 0, 0);'>";
        foreach ($objectLineRequest->getErrors() as $error) {
            echo $error->getMessage()."<br/>";
        }
        echo "</td>";
    } else {
        echo "<td style='background-color: rgb(0, 200, 0);'>OK</td>";
    }
}

echo "</table>";
    
if (isset($updatedReq)) {
    echo "<br />Your object request has been successfully queued into the FG scenery database update requests!<br />";
    echo "Unless it's rejected, it should appear in Terrasync within a few days.<br />";
    echo "The FG community would like to thank you for your contribution!<br />";
    echo "Want to submit another object? <a href=\"http://".$_SERVER['SERVER_NAME']."/app.php?c=AddObjects&amp;a=form\">Click here to go back to the submission page.</a><br />";
    echo "Please remember to use the <a href=\"http://".$_SERVER['SERVER_NAME']."/app.php?c=AddObjects&amp;a=massiveform\">massive insertion script</a> should you have many objects to add.";
}

require 'view/footer.php';

?>