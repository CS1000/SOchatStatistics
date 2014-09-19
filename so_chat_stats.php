<?php require('so_chat_parser.php');?>
<html>
  <head>
    <meta charset="UTF-8">
    <title><?=$roomname?> - Room Statistics</title>
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
    <style type="text/css">
      body {
        font-family: Verdana,Arial,sans-serif;
      }
      .notice {
        color: #777777;
        font-size: x-small;
      }
      .timestamp {
        display: inline;
        color: #009933;
      }
      h1 {
        font-size: x-large;
      }
    </style>
  </head>
  <body>
    <h1>Ocurence of word "<span class="timestamp"><?=$word?></span>" in 
      "<span class="timestamp"><?=$roomname?></span>" room</h1>
    <div id="piechart" style="width: 900px; height: 500px;"></div><br>
    <div id="notices">
      <?php 
        $shownMessages=count($users);
        echo 'Period range: ',$start,' to ',$end,'<br>'; 
        echo '<p>Showing statistics with ';
        $prc=round($shownMessages*100/$messageNumber, 1);
        if ((int)$prc==100) echo '<b>all time data</b> ';
        else echo '<b>recent data</b> ('.$prc.'% of all time) ';
        echo 'where word "'.$word.'" occured.</p>';
      ?>
      <p class="notice">* users with word count less than 2 are not shown in the list, 
        and counted totards "Others" group.</p>
    </div>
  </body>
</html>