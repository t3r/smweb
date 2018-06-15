<?php
require 'view/header.php';
?>

  <h1>FlightGear Scenery Database Latest News</h1>
  <p><a href="rss/news.php"><img src="img/icons/rss.png"> Subscribe to news feed</a></p>
<?php
    foreach ($newsPosts as $newsPost) {
        echo "<div class=\"paragraph_bloc\">" .
             "<div class=\"header\">" .
             "<div class=\"newsdate\">".\FormatUtils::formatDateTime($newsPost->getDate())."</div>" .
             "<div class=\"newsnormal\">by</div>" .
             "<div class=\"newsauthor\"><a href=\"app.php?c=Authors&amp;a=view&amp;id=".$newsPost->getAuthor()->getId()."\">".$newsPost->getAuthor()->getName()."</a></div>" .
             "<div class=\"clear\"></div></div>" .
             "<div class=\"body\">".$newsPost->getText()."</div>" .
             "</div>";
    }
?>
  <table>  
    <tr class="bottom">
        <td colspan="9" align="center">
<?php 
            if ($offset >= 10) {
                echo "<a href=\"app.php?c=News&amp;a=display&amp;offset=".($offset-10)."\">&lt; Newer news</a> | ";
            }
?>
            <a href="app.php?c=News&amp;a=display&amp;offset=<?php echo $offset+10;?>">Older news &gt;</a>
        </td>
    </tr>
  </table>
<?php require 'view/footer.php';?>
