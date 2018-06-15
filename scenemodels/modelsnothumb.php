<?php
require_once 'autoload.php';
$modelDaoRO = \dao\DAOFactory::getInstance()->getModelDaoRO();
$objectDaoRO = \dao\DAOFactory::getInstance()->getObjectDaoRO();

require "view/header.php";
?>

<script type="text/javascript">
  function popmap(lat,lon,zoom) {
    popup = window.open("/map?z="+zoom+"&lat="+lat+"&lon="+lon, "map", "height=500,width=500,scrollbars=no,resizable=no");
    popup.focus();
  }
</script>

<h1>Models without thumbnail</h1>
<?php

$number = $modelDaoRO->countModelsNoThumb();
$pagesize = 20;
?>
<p>This page lists models that lack a thumbnail. There are currently <?php echo $number; ?> of such models in the database. Please help us bringing this number to 0!</p>

  <table>
<?php
    if(isset($_REQUEST['offset']) && preg_match(FormChecker::$regex['pageoffset'],$_GET['offset'])){
        $offset = $_REQUEST['offset'];
    } else {
        $offset = 0;
    }
?>
    <tr class="bottom">
        <td colspan="2" align="center">
<?php 
            if ($offset >= $pagesize) {
                echo "<a href=\"modelsnothumb.php?offset=".($offset-$pagesize)."\">Prev</a> | ";
            }
?>
            <a href="modelsnothumb.php?offset=<?php echo $offset+$pagesize;?>">Next</a>
        </td>
    </tr>
<?php
    $modelMetadatas = $modelDaoRO->getModelMetadatasNoThumb($offset, $pagesize);


    $odd = true;
    foreach ($modelMetadatas as $modelMetadata) {
        if ($odd) {
            echo "<tr>";
        }
        echo "<td>" .
             "<ul class=\"table\">" .
             "<li><b>Name:</b> ".htmlspecialchars($modelMetadata->getName())."</li>" .
             "<li><b>Path:</b> ".$modelMetadata->getFilename()."</li>";
        if (strlen($modelMetadata->getDescription())>0) {
            echo "<li><b>Notes:</b> ".htmlspecialchars($modelMetadata->getDescription())."</li>";
        }
        echo "<li><b>Author: </b><a href=\"app.php?c=Authors&amp;a=view&amp;id=".$modelMetadata->getAuthor()->getId()."\">".$modelMetadata->getAuthor()->getName()."</a></li>" .
             "<li><b>Last Updated: </b>".\FormatUtils::formatDateTime($modelMetadata->getLastUpdated())."</li>" .
             "<li><b>Type: </b><a href=\"app.php?c=Models&a=browse&shared=".$modelMetadata->getModelsGroup()->getId()."\">".$modelMetadata->getModelsGroup()->getName()."</a></li>";

        if ($modelMetadata->getModelsGroup()->isStatic()) {
            $objects = $objectDaoRO->getObjectsByModel($modelMetadata->getId());
            
            foreach ($objects as $object) {
                $objPos = $object->getPosition();
                echo "<li>(<a href=\"download/".$object->getDir().".tgz\">".$object->getDir()."</a>) ";
                echo "<a href=\"javascript:popmap(".$objPos->getLatitude().",".$objPos->getLongitude().",13)\">Map</a></li>";
            }
        }

        echo "<li><a href=\"app.php?c=Models&a=view&id=".$modelMetadata->getId()."\">View more about this model.</a></li>";
        echo "</ul>";
        echo "</td>";
        if (!$odd) {
            echo "</tr>";
        }
        $odd = !$odd;
    }
?>
    <tr class="bottom">
        <td colspan="2" align="center">
<?php 
            if ($offset >= $pagesize) {
                echo "<a href=\"modelsnothumb.php?offset=".($offset-$pagesize)."\">Prev</a> | ";
            }
?>
            <a href="modelsnothumb.php?offset=<?php echo $offset+$pagesize;?>">Next</a>
        </td>
    </tr>
  </table>
<?php require 'view/footer.php';?>
