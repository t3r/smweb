<?php

/* 
 * Copyright (C) 2016 FlightGear Team
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

require 'view/header.php';
?>

    <h1>FlightGear Scenery Website</h1>

    <p>Welcome to the <a href="http://www.flightgear.org">FlightGear</a> scenery website!</p>
    <p>This website is used to share common tools and data for all FlightGear scenery related items. It also features webforms to help gathering 3D models and objects positions all around the world. You can here contribute to FlightGear scenery by adding objects in your favorite place. Please don't hesitate, your help is welcomed!</p>
    <p>We currently have <span id="nummodels" class="statscounter">?</span> models at <span id="numobjects" class="statscounter">?</span> individual positions from <span id="numauthors" class="statscounter">?</span> authors in our database.</p>
  
    <table class="left">
        <tr><th colspan="2">Recently updated objects</th></tr>
<?php
        foreach ($objects as $object) {
            echo "<tr>" .
                    "<td><a href=\"app.php?c=Objects&amp;a=view&amp;id=".$object->getId()."\">".htmlspecialchars($object->getDescription())."</a><br/>" .
                    \FormatUtils::formatDateTime($object->getLastUpdated())."</td>" .
                    "<td style=\"width: 100px; padding: 0px;\">".
                    "<a href=\"app.php?c=Objects&amp;a=view&amp;id=". $object->getId() . "\">" .
                    "    <img title=\"". htmlspecialchars($object->getDescription())."\"" .
                    "    src=\"app.php?c=Models&amp;a=thumbnail&amp;id=". $object->getModelId() . "\" width=\"100\" height=\"75\" style=\"vertical-align: middle\"" .
                    "    alt=\"\" />" .
                    "</a>" .
                    "</td>" .
                 "</tr>";
        }
?>
        <tr class="bottom">
            <td colspan="2" align="center">
                <a href="app.php?c=Objects&amp;a=search">More recently updated objects</a>
            </td>
        </tr>
    </table>
    <table class="right">
        <tr><th colspan="2">Recently updated models</th></tr>
<?php
        foreach ($models as $model) {
            echo "<tr>" .
                    "<td><a href=\"/app.php?c=Models&amp;a=view&amp;id=".$model->getId()."\">".htmlspecialchars($model->getName())."</a><br/>" .
                    \FormatUtils::formatDateTime($model->getLastUpdated()). "</td>" .
                    "<td style=\"width: 100px; padding: 0px;\">".
                    "<a href=\"/app.php?c=Models&amp;a=view&amp;id=". $model->getId() ."\">" .
                    "    <img title=\"". htmlspecialchars($model->getName()).' ['.$model->getFilename().']'."\"" .
                    "    src=\"app.php?c=Models&amp;a=thumbnail&amp;id=". $model->getId() ."\" width=\"100\" height=\"75\" style=\"vertical-align: middle\"" .
                    "    alt=\"\" />" .
                    "</a>" .
                    "</td>" .
                "</tr>";
        }
?>
        <tr class="bottom">
            <td colspan="2" align="center">
                <a href="app.php?c=Models&amp;a=browseRecent">More recently updated models</a>
            </td>
        </tr>
    </table>
    <div class="clear"></div>
    <script type="text/javascript">
      $(function() {
        var cnt = 10;
        var t = -1;
        function fakeCounter() {
          $("#nummodels").text(cnt);
          $("#numobjects").text(100*cnt+Math.floor(Math.random()*100));
          $("#numauthors").text(cnt);
          cnt++;
          t = setTimeout(fakeCounter, 10);
        }

        fakeCounter();

        $.getJSON( "/scenemodels/stats/", function( data ) {
          clearTimeout(t);
          data = data || {};
          stats = data.stats || {}
          $("#nummodels").text( stats.models || 0);
          $("#numobjects").text(stats.objects || 0);
          $("#numauthors").text(stats.authors || 0);
        });
      });
    </script>
  
<?php require 'view/footer.php';?>
