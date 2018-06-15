<?php
/**
 * This script creates an xml file for errors
 */
header('Content-Type: text/xml');
?>
<?xml version="1.0" standalone="yes" ?>
<errors>
<?php
    if (isset($errors)) {
        foreach ($errors as $error) {
            echo "<error>".$error->getMessage()."</error>";
        }
    }
?>
</errors>