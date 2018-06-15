<?php
require_once 'autoload.php';
$modelDaoRO = \dao\DAOFactory::getInstance()->getModelDaoRO();

require 'view/header.php';
?>
<h1>Scenery Downloads</h1>
<p>
    In order to have the latest up-to-date scenery, it is <strong>RECOMMENDED</strong> to use <strong>TerraSync</strong>, included with FlightGear, to download the scenery automatically when you are flying. More information about TerraSync can be found <a href="http://wiki.flightgear.org/TerraSync">at our wiki</a>.</p>
<p>
    However, for any reason, you can still download the files here:
    <ul>
        <li><a href="scenery_download.php">Terrain and objects </a>: but <strong>the objects are not up-to-date</strong></li>
        <li><a href="objects_download.php">Objects</a>: <strong>only</strong> the latest up-to-date objects</li>
        <ul>
            <li><a href="https://sourceforge.net/projects/flightgear/files/scenery/GlobalObjects.tgz/download">Global objects </a>: contains all objects in the world.</li>
        </ul>
        <li><a href="https://sourceforge.net/projects/flightgear/files/scenery/SharedModels.tgz/download">Shared models</a>: eg. windturbines, jetways, trees. This file is <strong>*REQUIRED*</strong> if you want to see all the objects, and should be unpacked in your $FG_ROOT directory.</li>
    </ul>
</p>

<?php require 'view/footer.php'; ?>
