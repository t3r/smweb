<?php
require_once 'autoload.php';
$modelDaoRO = \dao\DAOFactory::getInstance()->getModelDaoRO();

$pageTitle = "TelaScience / OSGeo / FlightGear Landcover Database Mapserver";
$body_onload = "init()";
require 'view/header.php';
?>
<br />
<center>
<object data="http://mapserver.flightgear.org/" type="text/html" width="800" height="1500"></object>
</center>
<?php require 'view/footer.php'; ?>