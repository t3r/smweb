<?php
require 'view/header.php';
?>
<script type="text/javascript">
  function popmap(lat,lon) {
    popup = window.open("/map?z=12&lat="+lat+"&lon="+lon, "map", "height=500,width=500,scrollbars=no,resizable=no");
    popup.focus();
  }
</script>

<form action="app.php?c=Objects&a=search" method="get">
    <input type="hidden" name="c" value="Objects"/>
    <input type="hidden" name="a" value="search"/>
    <table>
        <tr style="vertical-align:bottom">
            <th>ID</th>
            <th>Description</th>
            <th>Model<br/>Group</th>
            <th>Country</th>
            <th>Lon<br/>Lat</th>
            <th>Ground&nbsp;elev.<br/>Offset (m)</th>
            <th>Heading</th>
            <th>&nbsp;</th>
        </tr>
        <tr style="vertical-align:bottom">
            <th>&nbsp;</th>
            <th><input type="text" name="description" size="12" value="<?php echo $description; ?>"/></th>
            <th>
                <select name="model" style="font-size: 0.7em; width: 100%">
                    <option value="0"></option>
<?php                    
                    
                    foreach ($modelPaths as $mo_id => $path) {
                        echo "<option value=\"".$mo_id."\"";
                        if ($mo_id == $model) {
                            echo " selected=\"selected\"";
                        }
                        echo ">".$path."</option>\n";
                    }
?>
                </select>
                <br/>
                <select name="groupid" style="font-size: 0.7em;">
                    <option value="0"></option>
<?php
                    
                    foreach ($objectsGroups as $objectsGroup){
                        $groups[$objectsGroup->getId()] = $objectsGroup->getName();
                        echo "<option value=\"".$objectsGroup->getId()."\"";
                        if ($objectsGroup->getId() == $groupid) {
                            echo " selected=\"selected\"";
                        }
                        echo ">".$objectsGroup->getName()."</option>\n";
                    }
?>
                </select>
            </th>
            <th>
                <select name="country" style="font-size: 0.7em; width: 100%">
                    <option value="0"></option>
<?php
                    foreach ($countries as $country){
                        echo "<option value=\"".$country->getCode()."\"";
                        if ($country->getCode() == $countryId) {
                            echo " selected=\"selected\"";
                        }
                        echo ">".$country->getName()."</option>\n";
                    }
?>
                </select>
            </th>
            <th><input type="text" name="lon" size="12" <?php echo "value=\"".$lon."\""; ?>/>
              <br/><input type="text" name="lat" size="12" <?php echo "value=\"".$lat."\""; ?>/></th>
            <th><input type="text" name="elevation" size="6" <?php echo "value=\"".$elevation."\""; ?>/>
              <br/><input type="text" name="elevoffset" size="6" <?php echo "value=\"".$elevoffset."\""; ?>/></th>
            <th><input type="text" name="heading" size="3" <?php echo "value=\"".$heading."\""; ?>/></th>
            <th><input type="submit" name="filter" value="Filter"/></th>
        </tr>
        <tr class="bottom">
            <td colspan="8" align="center">
<?php
                $prev = $offset-$pagesize;
                $next = $offset+$pagesize;

                $filter_text = "&amp;model=".$model."&amp;groupid=".$groupid."&amp;elevation=".$elevation.
                    "&amp;elevoffset=".$elevoffset."&amp;heading=".$heading.
                    "&amp;lat=".$lat."&amp;lon=".$lon.
                    "&amp;country=".$countryId."&amp;description=".$description;
                
                if ($prev >= 0) {
                    echo "<a href=\"app.php?c=Objects&amp;a=search&amp;filter=Filter&amp;offset=".$prev . $filter_text."\">Prev</a> | ";
                }
?>
                <a href="app.php?c=Objects&amp;a=search&amp;filter=Filter&amp;offset=<?php echo $next . $filter_text;?>">Next</a>
            </td>
        </tr>
<?php
        foreach ($objects as $object) {
            $objPos = $object->getPosition();
            $objOffset = $objPos->getElevationOffset();
            echo "<tr class=\"object\">\n";
            echo "  <td><a href='app.php?c=Objects&amp;a=view&amp;id=".$object->getId()."'>#".$object->getId()."</a></td>\n" .
                 "  <td>".htmlspecialchars($object->getDescription())."</td>\n" .
                 "  <td><a href=\"app.php?c=Models&amp;a=view&amp;id=".$object->getModelId()."\">".$modelPaths[$object->getModelId()]."</a><br/>".$groups[$object->getGroupId()]."</td>\n" .
                 "  <td>".$object->getCountry()->getName() ."</td>\n" .
                 "  <td>".$objPos->getLongitude()."<br/>".$objPos->getLatitude()."</td>\n" .
                 "  <td>".$objPos->getGroundElevation()."<br/>".$objOffset."</td>\n" .
                 "  <td>".$objPos->getOrientation()."</td>\n" .
                 "  <td style=\"width: 58px; text-align: center\">\n" .
                 "  <a href=\"app.php?c=UpdateObjects&amp;a=updateForm&amp;id_to_update=".$object->getId()."\"><img class=\"icon\" src=\"/img/icons/edit.png\" alt=\"edit\"/></a>";
            if (!$modelIsStaticMap[$object->getModelId()]) {
?>
                <a href="app.php?c=DeleteObjects&amp;a=confirmDeleteForm&amp;delete_choice=<?php echo $object->getId(); ?>">
                    <img class="icon" src="/img/icons/delete.png" alt="delete"/>
                </a>
<?php
            }
            echo "    <a href=\"javascript:popmap(".$objPos->getLatitude().",".$objPos->getLongitude().")\"><img class=\"icon\" src=\"/img/icons/world.png\" alt=\"map\"/></a>" .
                 "  </td>\n" .
                 "</tr>\n";
        }
?>
        <tr class="bottom">
            <td colspan="8" align="center">
<?php
                if ($prev >= 0) {
                    echo "<a href=\"app.php?c=Objects&amp;a=search&amp;filter=Filter&amp;offset=".$prev . $filter_text."\">Prev</a> | ";
                }
?>
                <a href="app.php?c=Objects&amp;a=search&amp;filter=Filter&amp;offset=<?php echo $next . $filter_text;?>">Next</a>
            </td>
        </tr>
    </table>
</form>

<?php require 'view/footer.php';?>
