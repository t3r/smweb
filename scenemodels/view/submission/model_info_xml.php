<?php

header('Content-Type: text/xml');
echo "<?xml version=\"1.0\" standalone=\"yes\" ?>\n";

// Showing the results.
echo "<model><name>"
     .htmlspecialchars($modelMD->getName())
     ."</name><notes>"
     .htmlspecialchars($modelMD->getDescription())
     ."</notes><author>"
     .$modelMD->getAuthor()->getId()
         ."</author></model>";

?>
