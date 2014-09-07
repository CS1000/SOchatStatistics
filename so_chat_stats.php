<?php require('so_chat_parser.php');?>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Room 11 Statistics</title>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          <?=$list?>
        ]);
  
        var options = {
          title: 'Ocurence of word "<?=$word?>" in room 11'
        };
  
        var chart = new google.visualization.PieChart(document.getElementById('piechart'));
        chart.draw(data, options);
      }
    </script>
  </head>
  <body>
    <div id="piechart" style="width: 900px; height: 500px;"></div>
  </body>
</html>