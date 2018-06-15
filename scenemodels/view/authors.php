<?php
require 'view/header.php';
?>

<h1>FlightGear Scenery Authors Directory</h1>
  
<table>
    <tr>
        <th>Author</th>
        <th>Comments of the author</th>
    </tr>
<?php
    
    
    foreach ($authors as $author){
        echo "<tr>" .
                 "<td style=\"width: 25%\">\n" .
                     "<b><a href=\"app.php?c=Authors&amp;a=view&amp;id=".$author->getId()."\">".$author->getName()."</a></b>" .
                 "</td>" .
                 "<td>".$author->getDescription()."</td>" .
             "</tr>";
    }
?>
    <tr class="bottom">
        <td colspan="9" align="center">
<?php 
            if ($offset >= $pagesize) {
                echo "<a href=\"app.php?c=Authors&amp;a=browse&amp;offset=".($offset-$pagesize)."\">Prev</a> | ";
            }
?>
            <a href="app.php?c=Authors&amp;a=browse&amp;offset=<?php echo $offset+$pagesize;?>">Next</a>
        </td>
    </tr>
</table>
<?php require 'view/footer.php';?>
