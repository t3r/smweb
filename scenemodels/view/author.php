<?php
require 'view/header.php';

echo "<h1>Scenery models by ".$author->getName()."</h1>";
if (strlen($author->getDescription())>0) {
    echo "<p>".$author->getDescription()."</p>";
}
?>
<table>
<?php
    foreach ($modelMetadatas as $modelMetadata) {
        echo "<tr><td style=\"width: 160px\"><a href=\"app.php?c=Models&a=view&id=".$modelMetadata->getId()."\"><img src=\"app.php?c=Models&amp;a=thumbnail&amp;id=".$modelMetadata->getId()."\" width=\"160\" alt=\"\"/></a>".
            "</td><td><p><b>Name:</b> <a href=\"app.php?c=Models&amp;a=view&amp;id=".$modelMetadata->getId()."\">".htmlspecialchars($modelMetadata->getName())."</a></p>".
            "<p><b>Path:</b> <a href=\"app.php?c=Objects&amp;a=search&amp;model=".$modelMetadata->getId()."\">".$modelMetadata->getFilename()."</a></p>".
            "<p><b>Last Updated: </b>".\FormatUtils::formatDateTime($modelMetadata->getLastUpdated())."</p>".
            "</td></tr>";
    }
?>
</table>
<?php

require 'view/footer.php';

?>
