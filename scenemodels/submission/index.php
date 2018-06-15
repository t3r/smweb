<?php
require_once '../autoload.php';
$modelDaoRO = \dao\DAOFactory::getInstance()->getModelDaoRO();

$pageTitle = "Automated Scenery Submission Forms";
require '../view/header.php';
?>

<h1>FlightGear scenery objects and models submission</h1>

<p>
    Please read <a href="//<?php echo $_SERVER['SERVER_NAME'];?>/contribute.php">this page</a> in order to understand what items those forms are looking for. All submissions are being followed and logged, so <b>DO NOT TAKE THIS</b> as a sandbox.
</p>

<table>
    <tr align="left">
        <td align="left">
            Now select the operation you would like to perform:
            <ul>
                <li>on objects (eg windturbines, pylons, generic buildings...):</li>
                <ul>
                    <li><a href="../app.php?c=AddObjects&amp;a=form">adding objects</a>.</li>
                    <li><a href="../app.php?c=AddObjects&amp;a=massiveform">massive import of objects</a> (adding tens of lines of objects in one click).</li>
                    <li><a href="../app.php?c=DeleteObjects&amp;a=findform">deleting objects</a> (delete an existing shared object).</li>
                    <li><a href="../app.php?c=UpdateObjects&amp;a=findform">updating objects</a> (updating position, offset of an object...).</li>
                </ul>
                <li>on 3D models (models designed for a specific location, eg Eiffel Tower):</li>
                <ul>
                    <li><a href="../app.php?c=AddModel&amp;a=form">adding a new static or shared 3D model</a>.</li>
                    <li><a href="../app.php?c=UpdateModel&amp;a=selectModelForm">updating an existing static or shared 3D model</a> (improve 3D model).</li>
                </ul>
            </ul>
            Comments or contributions propositions are always welcome through the usual channels (<a href="https://sourceforge.net/mailarchive/forum.php?forum_name=flightgear-devel">devel list</a>, <a href="http://www.flightgear.org/forums/viewtopic.php?f=5&amp;t=14671">forum</a>).
        </td>
    </tr>
</table>

<?php require '../view/footer.php'; ?>
