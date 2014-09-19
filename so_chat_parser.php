<?php
  $room=isset($_GET['where'])?$_GET['where']:$argv[1];
  $roomp=preg_replace('/\D/', '#', $room);
  if ($room!==$roomp) die('in flames! GTFO');
  else unset($roomp);
  
  $word=isset($_GET['word'])?$_GET['word']:$argv[2];
  $word=preg_replace('/[^?\pL^$ \pN.#:;_!-]/i', '', $word);

  $data=file('http://chat.stackoverflow.com/search?q='.urlencode($word).'&Room='.$room.'&page=1&pagesize=100&sort=newest');
  
  $start=preg_grep('~<div class="timestamp">~i', $data);
  $end=trim(strip_tags(array_shift($start)));
  if (count($start)<1) $start=$end;
  else $start=trim(strip_tags(array_pop($start)));
  $start=str_replace("'", '20', $start);
  $end=str_replace("'", '20', $end);
  $start=new DateTime($start);
  $end=new DateTime($end);
  $interval=$start->diff($end);
  $interval=$interval->days;
  $range=$interval.'days, ';
  if ($interval>0) $range.='from '.$start->format("d M Y").' to '.$end->format("d M Y");
  else $range.=$start->format("d M Y");


  $messageNumber=preg_grep('~<p>([0-9]+) messages? found</p>~i', $data);
  preg_match('~>([0-9]+)~', array_pop($messageNumber), $allTimeMsg);
  $messageNumber=(int)$allTimeMsg[1];

  $roomname=preg_grep('~class="searchroom~i', $data);
  $roomname=each($roomname);
  preg_match('~href=[^>]+>(.+?)</a>~i', $roomname[1], $matches);
  $roomname=$matches[1];

  $users=preg_grep('~<div class="username"><a href="/users/~', $data);

  $count=array();
  $names=array();
  $shownMessages=0;
  foreach ($users as $user) { 
      preg_match('~/users/(\d+)/[^>]+>([^<]+)~', $user, $m);
      $names[$m[1]]=$m[2];
      @$count[$m[1]]++;
      $shownMessages++;
  }
  arsort($count);
  $list='';
  $other=0;
  foreach ($count as $id=>$num) {
      //loop entry point
      //if ($list==='') {  }

      if ($num>1) {
        //must say it at least twice
        $list.=",['".$names[$id]."', ".$num."]\n";
      } else {
        //add to the others
        $other+=$num;
      }
  }
  $list="['Who', 'times']\n".$list.",['Others', $other]\n";

  //debug:
  //echo $list;