<?php
require_once 'autoload.php';
$modelDaoRO = \dao\DAOFactory::getInstance()->getModelDaoRO();

$pageTitle = "FlightGear World Scenery v2.10.0";
require 'view/header.php';
?>
<br />
<center>
<object data="http://www.flightgear.org/legacy-Downloads/scenery-v2.10.html" type="text/html" width="800" height="600"></object>
</center>
<?php
require 'view/footer.php';
?>
