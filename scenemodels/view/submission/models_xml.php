<?php
header('Content-Type: text/xml');
echo "<?xml version=\"1.0\" standalone=\"yes\" ?>";

// Showing the results.
echo "<models>";
foreach($modelMDs as $modelMD) {
    echo "<model><id>".$modelMD->getId()."</id><name>".htmlspecialchars($modelMD->getFilename())."</name></model>";
}
echo "</models>";

?>