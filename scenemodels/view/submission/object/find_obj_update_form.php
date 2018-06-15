<?php
$pageTitle = "Objects update form";
require 'view/header.php';
?>

<script src="/inc/js/check_form.js" type="text/javascript"></script>
<script type="text/javascript">
/*<![CDATA[*/
function validateForm()
{
    var form = document.getElementById("edition");

    if (!checkStringNotDefault(form["longitude"], "")
        || !checkNumeric(form["longitude"],-180,180) ||
        !checkStringNotDefault(form["latitude"], "")
        || !checkNumeric(form["latitude"],-90,90))
        return false;
}
/*]]>*/
</script>

<h1>Objects update form</h1>

<p>
    Through this form you can update a shared or static object (eg. windturbine, power pylon, Eiffel Tower) at a given location. You can alternatively look for the object on <a href="http://<?php echo $_SERVER['SERVER_NAME'];?>/coverage.php">the map</a> if you are unsure of the exact coordinates of the object.
</p>

<form id="edition" method="post" action="app.php?c=UpdateObjects&amp;a=findObjWithPos" onsubmit="return validateForm();">
<table>
    <tr>
        <td><label for="longitude">Longitude<em>*</em><span>This is the WGS84 longitude of the object. Has to be between -180 and 180.</span></label></td>
        <td>
            <input type="text" name="longitude" id="longitude" maxlength="13" value="0" onkeyup="checkNumeric(this,-180,180);" />
        </td>
    </tr>
    <tr>
        <td><label for="latitude">Latitude<em>*</em><span>This is the WGS84 latitude of the object. Has to be between -90 and 90.</span></label></td>
        <td>
            <input type="text" name="latitude" id="latitude" maxlength="13" value="0" onkeyup="checkNumeric(this,-90,90);" />
        </td>
    </tr>
    <tr>
        <td colspan="2" class="submit">
            <input type="submit" value="Check for objects at this position" />
        </td>
    </tr>
</table>
</form>

<?php require 'view/footer.php'; ?>
