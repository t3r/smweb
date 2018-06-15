<?php 
require_once '../autoload.php';
$modelDaoRO = \dao\DAOFactory::getInstance()->getModelDaoRO();

header('Content-type: application/rss+xml');
$modelMetadatas = $modelDaoRO->getModelMetadatas(0, 50);

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
?>
<rss version="2.0">
  <channel>
    <title>FGFSDB Model Updates</title>
    <link>https://<?php echo $_SERVER['SERVER_NAME'];?>/app.php?c=Models&amp;a=browseRecent</link>
    <language>en-GB</language>
    <copyright>Jon Stockill 2006-2008.</copyright>
    <description>
        FlightGear scenery object database model additions.
    </description>
    <ttl>720</ttl>
    <lastBuildDate>
        <?php echo $modelMetadatas[0]->getLastUpdated()->format(DateTime::RSS);?>
    </lastBuildDate>
    <?php
      foreach ($modelMetadatas as $modelMetadata){
    ?>
    <item>
      <link>
          https://<?php echo $_SERVER['SERVER_NAME'];?>/app.php?c=Models&amp;a=view&amp;id=<?php echo $modelMetadata->getId();?>
      </link>
      <title><![CDATA[<?php echo $modelMetadata->getName()?> ]]></title> 
      <description>
          <![CDATA[<?php echo $modelMetadata->getName()?> ]]>
      </description> 
      <pubDate>
          <?php echo $modelMetadata->getLastUpdated()->format(DateTime::RSS)?>
      </pubDate>
    </item>
    <?php
      }
    ?>
  </channel>
</rss>
