<?php
// Including librairies
require_once 'autoload.php';
require 'view/header.php';
?>
<script type='text/javascript' src='https://www.google.com/jsapi'></script>
<script type='text/javascript'>
    google.load('visualization', '1', {'packages': ['geochart','corechart']});

    google.setOnLoadCallback(function(){

      function drawPie(data, elementId ) {
        var optionsPie = {
          chartArea: {height:"100%"},
          backgroundColor: 'none',
          pieSliceBorderColor: 'none',
          slices: {20: {color: '#ccc'}},
          sliceVisibilityThreshold: 1/100,
          legend: { alignment: 'center' },
          pieHole: 0.4
        };
        var chartPie = new google.visualization.PieChart(document.getElementById(elementId));
        google.visualization.events.addListener(chartPie, 'select', function () {
            // GeoChart selections return an array of objects with a row property; no column information
            var selection = chartPie.getSelection();
            data.removeRow(selection[0].row);
            chartPie.draw(data, optionsPie);
        });
        chartPie.draw(data, optionsPie);
      }

      $.getJSON(' https://api.flightgear.org/scenemodels/stats/models/byauthor', function(data) {
        var dataTable = new google.visualization.DataTable();
        dataTable.addColumn('string', 'Author');
        dataTable.addColumn('number', 'Objects');
        data.modelsbyauthor.forEach(function(entry) {
          dataTable.addRow([ entry.author, Number(entry.count) ]);
        });

        drawPie(dataTable, 'chart_pie_authors_div' );
      }); 

      $.getJSON(' https://api.flightgear.org/scenemodels/stats/models/bycountry', function(data) {
        var dataTable = new google.visualization.DataTable();
        dataTable.addColumn('string', 'Country');
        dataTable.addColumn('number', 'Objects');
        data.modelsbycountry.forEach(function(entry) {
          dataTable.addRow([ entry.name, Number(entry.count) ]);
        });

        drawPie(dataTable, 'chart_pie_div' );
      }); 

      $.getJSON(' https://api.flightgear.org/scenemodels/stats/all', function(data) {
        // Create and populate the data table.
        var dataObjects = new google.visualization.DataTable();
        dataObjects.addColumn('date', 'Date');
        dataObjects.addColumn('number', 'Objects');
        dataObjects.addColumn('number', 'Models');
        dataObjects.addColumn('number', 'Signs');
//        dataObjects.addColumn('number', 'Navaids');
//        dataObjects.addColumn('number', 'Authors');

        data.statistics.forEach(function(entry){
          dataObjects.addRow([
            new Date(entry.date),
            Number(entry.objects),
            Number(entry.models),
            Number(entry.signs),
//            Number(entry.navaids),
//            Number(entry.authors),
          ]);
        });

        // Create and draw the visualization.
        new google.visualization.LineChart(document.getElementById('chart_objects_div')).
        draw(dataObjects, {
//          legend: { position: 'bottom' },
          series:[
            {targetAxisIndex:0},
            {targetAxisIndex:1},
            {targetAxisIndex:1},
//            {targetAxisIndex:0},
//            {targetAxisIndex:3},
          ],
          vAxes: [
              {
                  color: 'blue',
                  textPosition: 'in',
                  title: 'Objects'
              },
              {
                  color: 'red',
                  textPosition: 'out',
                  title: 'Models and signs'
              },
//              {
//                  color: 'blue',
//                  textPosition: 'out',
//                  title: 'Authors'
//              },
          ],
          pointSize: 5,
          backgroundColor: 'none',
          chartArea: {top: 35, height: 430},
          focusTarget: 'category'
          }
        );
      });
    });
</script>

<h1>FlightGear Scenery Statistics</h1>
    <table class="left">
        <tr><th>Objects by country</th></tr>
        <tr><td>Click a country to remove it from the pie.</td></tr>
        <tr><td><div id="chart_pie_div" style="width: 100%; height: 250px;">Loading...</div></td></tr>
    </table>
    <table class="right">
        <tr><th>Models by author</th></tr>
        <tr><td>Click an author to remove him from the pie.</td></tr>
        <tr><td><div id="chart_pie_authors_div" style="width: 100%; height: 250px;">Loading...</div></td></tr>
    </table>

    <div class="clear"></div><br/>

    <table>
        <tr><th>Time evolution</th></tr>
        <tr>
            <td>
                <div id="chart_objects_div" style="width: 100%; height: 500px;"></div>
            </td>
        </tr>
    </table>

    <div class="clear"></div>

<?php require 'view/footer.php';?>
