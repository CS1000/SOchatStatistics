<?php
  $room=isset($_GET['where'])?$_GET['where']:$argv[1];
  $roomp=preg_replace('/\D/', '#', $room);
  if ($room!==$roomp) die('in flames! GTFO');
  else unset($roomp);
  
  $word=isset($_GET['word'])?$_GET['word']:$argv[2];
  $word=preg_replace('/[^?\pL^$ \pN.#:;_!-]/i', '', $word);

  $data=file('http://chat.stackoverflow.com/search?q='.urlencode($word).'&Room='.$room.'&page=1&pagesize=100&sort=newest');
  
  $roomname=preg_grep('~class="searchroom~i', $data);
  $roomname=each($roomname);
  preg_match('~href=[^>]+>(.+?)</a>~i', $roomname[1], $matches);
  $roomname=$matches[1];

  $users=preg_grep('~<div class="username"><a href="/users/~', $data);
  
  $count=array();
  $names=array();
  foreach ($users as $user) { 
      preg_match('~/users/(\d+)/[^>]+>([^<]+)~', $user, $m);
      $names[$m[1]]=$m[2];
      @$count[$m[1]]++;
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