<?php 
require_once '../autoload.php';
$newsPostDaoRO = \dao\DAOFactory::getInstance()->getNewsPostDaoRO();    

header('Content-type: application/rss+xml');

$newsPosts = $newsPostDaoRO->getNewsPosts(0, 10);

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
?>


<rss version="2.0">
  <channel>
    <title>FGFSDB Updates</title>
    <link>https://<?php echo $_SERVER['SERVER_NAME'];?></link>
    <language>en-GB</language>
    <copyright>Jon Stockill 2006.</copyright>
    <description>FlightGear scenery object database updates.</description>
    <ttl>720</ttl>
    <lastBuildDate>
        <?php echo $newsPosts[0]->getDate()->format(DateTime::RSS);?>
    </lastBuildDate>
    <?php
      foreach ($newsPosts as $newsPost){
    ?>
    <item>
      <link>
        https://<?php echo $_SERVER['SERVER_NAME'];?>/newsarticle.php?article=<?php echo $newsPost->getId();?>
      </link>
      <description><![CDATA[<?php echo $newsPost->getText();?> ]]></description> 
      <pubDate>
          <?php echo $newsPost->getDate()->format(DateTime::RSS);?>
      </pubDate>
    </item>
    <?php
      }
    ?>
  </channel>
</rss>
