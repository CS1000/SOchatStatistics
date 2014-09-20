<?php require('so_chat_parser.php');?>
<html>
  <head>
    <meta charset="UTF-8" />
    <title><?=$roomname?> - Room Statistics</title>
    <link rel="stylesheet" href="/default.css" />
  </head>
  <body>
    <div id="wrapper" class="clear">
      <section id="piechart"></section>
      <section>
         <h1>Occurrence of "<span class="highlight"><?=$word?></span>" in "<span class="highlight"><?=$roomname?></span>" room</h1>
         <div id="notices">
          <table>
            <tr>
              <td>Period range:</td>
              <td><?=$range?></td>
            </tr>
            <tr>
              <td>Statistics</td>
              <td><?=($allTimePercent==100) ? '<b>All time data</b> where "' : '<b>recent data</b> ('.$prc.'% of all time) where search phrase "',$word,'" occurred.';?></td>
            </tr>
          </table>
          <p class="notice">* Users with word count less than 2 are not shown in the list, and counted totards "Others" group.</p>
        </div>
      </section>
    </div>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          <?=$list?>
        ]);
  
        var options = {
          title: 'StackOverflow Chat Statistics'
        };
  
        var chart = new google.visualization.PieChart(document.getElementById('piechart'));
        chart.draw(data, options);
      }
    </script>
  </body>
</html>