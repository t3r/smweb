<?php
require 'view/header.php';

?>

<script type="text/javascript">
  function popmap(lat,lon,zoom) {
    popup = window.open("/map?z="+zoom+"&lat="+lat+"&lon="+lon, "map", "height=500,width=500,scrollbars=no,resizable=no");
    popup.focus();
  }
</script>

<h1>FlightGear Scenery Model Directory</h1>
  <p><a href="rss/models.php"><img src="img/icons/rss.png"> Subscribe to recent models feed</a></p>
  <table>
    <tr class="bottom">
        <td colspan="2" align="center">
<?php 
            if ($offset >= $pagesize) {
                echo "<a href=\"app.php?c=Models&amp;a=browseRecent&amp;offset=".($offset-$pagesize)."\">Prev</a> | ";
            }
?>
            <a href="app.php?c=Models&amp;a=browseRecent&amp;offset=<?php echo $offset+$pagesize;?>">Next</a>
        </td>
    </tr>
<?php
    foreach ($modelMetadatas as $modelMetadata) {
        echo "<tr>" .
             "<td style=\"width: 320px\">" .
             "<a href=\"app.php?c=Models&amp;a=view&amp;id=".$modelMetadata->getId()."\"><img src=\"app.php?c=Models&amp;a=thumbnail&amp;id=".$modelMetadata->getId()."\" alt=\"Model ".$modelMetadata->getId()."\"/></a>" .
             "</td>" .
             "<td>" .
             "<ul class=\"table\">" .
             "<li><b>Name:</b> ".htmlspecialchars($modelMetadata->getName())."</li>" .
             "<li><b>Path:</b> ".$modelMetadata->getFilename()."</li>";
        if (strlen($modelMetadata->getDescription())>0) {
            echo "<li><b>Notes:</b> ".htmlspecialchars($modelMetadata->getDescription())."</li>";
        }
        echo "<li><b>Author: </b><a href=\"app.php?c=Authors&amp;a=view&amp;id=".$modelMetadata->getAuthor()->getId()."\">".$modelMetadata->getAuthor()->getName()."</a></li>" .
             "<li><b>Last Updated: </b>".\FormatUtils::formatDateTime($modelMetadata->getLastUpdated())."</li>" .
             "<li><b>Type: </b><a href=\"app.php?c=Models&amp;a=browse&amp;shared=".$modelMetadata->getModelsGroup()->getId()."\">".$modelMetadata->getModelsGroup()->getName()."</a></li>";

        if ($modelMetadata->getModelsGroup()->isStatic()) {
            foreach ($objectsByModel[$modelMetadata->getName()] as $object) {
                $objPos = $object->getPosition();
                echo "<li>(<a href=\"download/".$object->getDir().".tgz\">".$object->getDir()."</a>) ";
                echo "<a href=\"javascript:popmap(".$objPos->getLatitude().",".$objPos->getLongitude().",13)\">Map</a></li>";
            }
        }

        echo "<li><a href=\"app.php?c=Models&amp;a=view&amp;id=".$modelMetadata->getId()."\">View more about this model.</a></li>";
        echo "</ul>";
        echo "</td>";
        echo "</tr>";
    }
?>
    <tr class="bottom">
        <td colspan="2" align="center">
            <?php 
            if ($offset >= $pagesize) {
                echo "<a href=\"app.php?c=Models&amp;a=browseRecent&amp;offset=".($offset-$pagesize)."\">Prev</a> | ";
            }
            ?>
            <a href="app.php?c=Models&amp;a=browseRecent&amp;offset=<?php echo $offset+$pagesize;?>">Next</a>
        </td>
    </tr>
  </table>
<?php require 'view/footer.php';?>
