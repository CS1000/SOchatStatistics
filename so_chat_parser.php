<?php
    
    $minimumRepeat=2; //importance treshold, overridden by dynamic adjust

    isset($_GET['where']) && $room=$_GET['where'];
    $room=preg_replace('/[^0-9]/', '', $room);
    $room===$_GET['where'] or die('404 does not exist!');

    isset($_GET['word']) && $word=$_GET['word'];

    $word=preg_replace('/[^$\pL@ \pN_.:;!?#^-]/i', '', $word);
    ''!=$word or die('404 word does not exist!');
    
    $requestURL='http://chat.stackoverflow.com/search?q=';
    $requestURL.=urlencode($word);
    if ($room != '') {
        $requestURL.='&Room='.$room;
        $room.='/'; //used in redirect
    }
    $requestURL.='&page=1&pagesize=100&sort=newest';

    $messageNumber=0;
    try {
        $data=file($requestURL);
        if (count($data)<30) {
            header('Location: http://chat.stackoverflow.com/rooms/'.$room); //page has unknown output
            die();
        }
    } catch (Exception $e) { 
        $range='Wait';
        $rangeDetailed="! Too many requests.";
        $statisticsDetails="Nope";
        goto theEndOfTheRoad; //don't worry, carry on        
    }

    $roomname='All Rooms';
    if ($room!='') {
        $roomname=current(preg_grep('~class="searchroom~i', $data));
        preg_match('~href=[^>]+>(.+?)</a>~i', $roomname, $matches);
        $roomname=$matches[1];
    }
    
    $messageNumber=preg_grep('~<p>([0-9]+) messages? found</p>~i', $data);
    preg_match('~>([0-9]+)~', current($messageNumber), $allTimeMsg);
    $messageNumber=$allTimeMsg[1]+0;
    if ($messageNumber<1) {
        $range='';
        $rangeDetailed="The begining of time <small><small>clock on chat.stackoverflow server</small></small> &mdash; Today, Current Time";
        $statisticsDetails="Zero Results";
        goto theEndOfTheRoad;
    }
    

    $start=preg_grep('~<div class="timestamp">~i', $data);
    $end=trim(strip_tags(array_shift($start)));
    count($start)<1 ? $start=$end : $start=trim(strip_tags(array_pop($start)));
    $start=new DateTime(str_replace("'", '20', $start));
    $end=new DateTime(str_replace("'", '20', $end));
    $interval=$start->diff($end);

    $format="F jS Y";
    $rangeDetailed=' ( '.$start->format($format).' &mdash; '.$end->format($format).' )';
    if ($interval->days<=1) {
        $range='1 day: ';
        $format="l ( F jS Y";
        $rangeDetailed=$start->format($format).' )';
    } elseif ($interval->days>92) {
        $range=$interval->m.' months';
    } else {
        $range=$interval->days.' days';
    }

    $users=preg_grep('~<div class="username"><a href="/users/~', $data);

    $count=[];
    $names=[];
    $shownMessages=0;
    foreach ($users as $user) { 
        preg_match('~/users/(-?\d+)/[^>]+>([^<]+)~', $user, $m);
        isset($names[$m[1]]) || $names[$m[1]]=$m[2];
        @$count[$m[1]]++; //fu
        $shownMessages++;
    }
    arsort($count);

    $list=[];
    $other=0; //counts messages
    $totalUsersShown=count($names);
    if ($totalUsersShown<=12) $minimumRepeat=1; //dynamic userlist adjustment
    $topUsers=0; 
    $otherUsers=0; //counts users
    foreach ($count as $id=>$num):
        if ($topUsers==10) { //dynamic userlist adjustment
            if ($minimumRepeat<$num) $minimumRepeat=$num; 
        }
        if ($num>=$minimumRepeat) { 
            $topUsers++;
            $name=$names[$id];
            //if ($id==-2) // FEEDS
            $tlist=[];
            $tlist['y']=$num;
            $tlist['label']="$name";
            $tlist["place"]="#$topUsers";
            $percent=round($num*100/$shownMessages, 1);
            $tlist["percent"]=$percent;
            $uniqNameColor=substr(hash('md4', $id),1 ,6); //color algo #1
            $tlist["color"]='#'.$uniqNameColor;
            //$tlist["indexLabelFontColor"]='#'.substr(preg_replace('/[^0-9a-f]/', '', $name.$id), 0 ,6); //color algo #2
            $font=round(16/strlen($name)*$percent);
            if ($font<12) $font=12;
            elseif ($font>24) $font=24;
            $tlist['indexLabelFontSize']=$font;
            if ($topUsers==1) $tlist['exploded']='true';
            $list[]=$tlist;
        } else {
            $other+=$num; //add messages
            $otherUsers++; //add users
        }
    endforeach;
    if ($other) $list[]=['y'=>$other, 'label'=>"Others", "percent"=>round($other*100/$shownMessages, 1)];

    $allTimePercent=round($shownMessages*100/$messageNumber, 1);
    if ($allTimePercent==100) $statisticsDetails='<b>All time data</b>';
    else $statisticsDetails='<b>recent data</b> ('.$allTimePercent.'% of all time)';
    if ($totalUsersShown>1) {
        $statisticsDetails.=" showing $totalUsersShown users";
    } else {
        $statisticsDetails.=" only 1 user";
    }
    if ($otherUsers>0) $statisticsDetails.=" ($topUsers trending and $otherUsers others)";

    if ($minimumRepeat>1) {
        $footerNotice="* Users reiterating the phrase less than $minimumRepeat times ";
        $footerNotice.='<b>are not shown in the list</b>, and counted towards "Others" group for readability.';
    }

    $list=json_encode($list); 

theEndOfTheRoad:
 
