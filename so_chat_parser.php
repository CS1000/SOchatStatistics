<?php
    
    $minimumRepeat=2; //importance treshold, overridden by dynamic adjust

    isset($_GET['where']) && $room=$_GET['where'];
    $room=preg_replace('/[^0-9]/', '', $room);
    $room===$_GET['where'] or die('404 does not exist!');

    isset($_GET['word']) && $word=$_GET['word'];

    $word=preg_replace('/[^?\pL^$ \pN.#:;_!><-]/i', '', $word);
    ''!=$word or die('404 word does not exist!');
    
    $requestURL='http://chat.stackoverflow.com/search?q=';
    $requestURL.=urlencode($word);
    if ($room != '') {
        $requestURL.='&Room='.$room;
        $room.='/'; //used in redirect
    }
    $requestURL.='&page=1&pagesize=100&sort=newest';
    
    try {
        $data=file($requestURL);
        if (count($data)<30) {
            header('Location: http://chat.stackoverflow.com/rooms/'.$room);
            die();
        }
    } catch (Exception $e) { 
        $rangeDetailed='Too many requests!';
        $statisticsDetails='DATA ERROR: please come back later, later!';
        $list="['What','code'], ['Error','408']";
        goto theEndOfTheRoad; //don't worry, carry on        
    }

    $start=preg_grep('~<div class="timestamp">~i', $data);
    $end=trim(strip_tags(array_shift($start)));
    count($start)<1 ? $start=$end : $start=trim(strip_tags(array_pop($start)));
    $start=new DateTime(str_replace("'", '20', $start));
    $end=new DateTime(str_replace("'", '20', $end));
    $interval=$start->diff($end);

    $range=$interval->days.' days'; //failsafe
    $format="F jS Y"; //failsafe
    if ($interval->days<=1) {
        $range='1 day: ';
        $format="l ( F jS Y";
        $rangeDetailed=$start->format($format).' )';
        goto endInterval;
    } elseif ($interval->days>92) {
        $range=$interval->m.' months';
    } 
    $rangeDetailed=' ( '.$start->format($format).' &mdash; '.$end->format($format).' )';
    endInterval:;

    $messageNumber=preg_grep('~<p>([0-9]+) messages? found</p>~i', $data);
    preg_match('~>([0-9]+)~', current($messageNumber), $allTimeMsg);
    $messageNumber=(int)$allTimeMsg[1];

    $roomname='All Rooms';
    if ($room!='') {
        $roomname=current(preg_grep('~class="searchroom~i', $data));
        preg_match('~href=[^>]+>(.+?)</a>~i', $roomname, $matches);
        $roomname=$matches[1];
    }

    $users=preg_grep('~<div class="username"><a href="/users/~', $data);

    $count=array(); // [] //php5.4+
    $names=array();
    $shownMessages=0;
    foreach ($users as $user) { 
        preg_match('~/users/(-?\d+)/[^>]+>([^<]+)~', $user, $m);
        isset($names[$m[1]]) || $names[$m[1]]=$m[2];
        @$count[$m[1]]++; //fu
        $shownMessages++;
    }
    arsort($count);

    $list='';
    $other=0; //counts messages
    $totalUsersShown=count($names);
    if ($totalUsersShown<=13) $minimumRepeat=1; //dynamic userlist adjustment
    $topUsers=0; 
    $otherUsers=0; //counts users
    foreach ($count as $id=>$num) {
        if ($topUsers==12) { //dynamic userlist adjustment
            if ($minimumRepeat<$num) $minimumRepeat=$num; 
        }
        if ($num>=$minimumRepeat) { 
            $name=$names[$id];
            //if ($id==-2) // FEEDS
            $list.=",['".$name."', ".$num."]";
            $topUsers++;
        } else {
            $other+=$num; //add messages
            $otherUsers++; //add users
        }
    }
    $list="['Who', 'times']".$list.",['Others', $other]";

    $allTimePercent=round($shownMessages*100/$messageNumber, 1);
    if ($allTimePercent==100) $statisticsDetails='<b>All time data</b>';
    else $statisticsDetails='<b>recent data</b> ('.$allTimePercent.'% of all time)';
    if ($totalUsersShown>1) {
        $statisticsDetails.=" showing $totalUsersShown users";
    } else {
        $statisticsDetails.=" only 1 user";
    }
    if ($otherUsers>0) $statisticsDetails.=" ($topUsers trending and $otherUsers others)";

    $footerNotice='';
    if ($minimumRepeat>1) {
        $footerNotice="* Users reiterating the phrase less than $minimumRepeat times ";
        $footerNotice.='<b>are not shown in the list</b>, and counted towards "Others" group for readability.';
    }

theEndOfTheRoad:;
 
