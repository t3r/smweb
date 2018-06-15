<?php
/**
 * This script creates an xml file containing the country code
 */

header('Content-Type: text/xml');
echo "<?xml version=\"1.0\" standalone=\"yes\" ?>\n".
     "<country>".
     $code.
     "</country>";

?>
