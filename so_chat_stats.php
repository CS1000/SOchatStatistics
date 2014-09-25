<?php require('so_chat_parser.php');?>
<html>
  <head>
    <meta charset="UTF-8">
    <title><?=$roomname?> - Room Statistics</title>
    <link rel="stylesheet" href="incload/style.css">
    <?php if ($messageNumber>0): //phpif?>
      <script type="text/javascript">
          window.onload = function () {
              userchart = new CanvasJS.Chart("user_chart", {
                  legend: {
                      verticalAlign: "bottom",
                      horizontalAlign: "center"
                  },
                  data: [{
                      startAngle: 210,
                      indexLabelFontSize: 12,
                      indexLabelFontFamily: "Open Sans",
                      //indexLabelPlacement: "inside",
                      indexLabel: "{label} {percent}%", 
                      toolTipContent: "{place} <b>{label}</b><br> said it {y} times",
                      type: "doughnut",
                      dataPoints: 
                        <?=$list?>
                  }]
              });
              userchart.render();
          }
      </script>
    <script type="text/javascript" src="incload/canvasjs.min.js"></script>
  <?php endif; //endphp?>
  </head>
<body>
    <div id="wrapper" class="clear">
      <section id="user_chart"><?=($messageNumber<1)?'<h1>-.-</h1>':''?></section>
      <section>
         <h1>Occurrence of "<span class="highlight"><?=$word?></span>" in "<span class="highlight"><?=$roomname?></span>" room</h1>
         <div id="notices">
          <table>
            <tr>
              <td>Period range:</td>
              <td><b><?=$range?></b><?=$rangeDetailed?></td>
            </tr>
            <tr>
              <td>Dataset details:</td>
              <td><?=$statisticsDetails?></td>
            </tr>
            <tr>
              <td>Average:</td>
              <td><span class="highlight">one</span> <?=$word?> <span class="highlight">every ~<?=$avg.$avgPeriod?></span></td>
            </tr>
          </table>
          <p class="notice"><?=isset($messageNumber)?$footerNotice:''?></p>
        </div>
      </section>
    </div>
  </body>
</html>
 
