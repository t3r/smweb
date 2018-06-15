<?php
$pageTitle = "Objects deletion form";
require 'view/header.php';
?>

<script src="/inc/js/check_form.js" type="text/javascript"></script>
<script type="text/javascript">
/*<![CDATA[*/
function validateForm()
{
    var form = document.getElementById("deletion");

    if (!checkStringNotDefault(form["longitude"], "") || !checkNumeric(form["longitude"],-180,180) ||
        !checkStringNotDefault(form["latitude"], "") || !checkNumeric(form["latitude"],-90,90))
        return false;
}
/*]]>*/
</script>

<h1>Objects deletion form</h1>

<p class="center">
  <b>Foreword:</b> This form's goal is to ease the deletion of objects within FG Scenery database.
  <br />There are currently <?php echo $countObjs?> objects in the database.
</p>

<form id="deletion" method="post" action="app.php?c=DeleteObjects&amp;a=findObjWithPos" onsubmit="return validateForm();">
<table>
    <tr>
        <td><label for="longitude">Longitude<em>*</em><span>This is the WGS84 longitude of the object you want to delete. Has to be between -180 and 180.</span></label></td>
        <td>
            <input type="text" name="longitude" id="longitude" maxlength="13" value="0" onkeyup="checkNumeric(this,-180,180);" />
        </td>
    </tr>
    <tr>
        <td><label for="latitude">Latitude<em>*</em><span>This is the WGS84 latitude of the object you want to delete. Has to be between -90 and 90.</span></label></td>
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

<?php require 'view/footer.php';
?>