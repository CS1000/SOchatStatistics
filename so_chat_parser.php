<?php
    
    $minimumRepeat=2; //importance treshold, overridden by dynamic adjust

    isset($_GET['where']) && $room=$_GET['where'];

    if ($room===preg_replace('/[^0-9]/', '#', $room)) $room='&Room='.$room;
    else $room='';

    isset($_GET['word']) && $word=$_GET['word'];

    $word=preg_replace('/[^?\pL^$ \pN.#:;_!><-]/i', '', $word);
    ''!=$word or die('404 word does not exist!');
    $data=file('http://chat.stackoverflow.com/search?q='.urlencode($word).$room.'&page=1&pagesize=100&sort=newest');

    $start=preg_grep('~<div class="timestamp">~i', $data);
    $end=trim(strip_tags(array_shift($start)));
    if (count($start)<1) $start=$end;
    else $start=trim(strip_tags(array_pop($start)));
    $start=str_replace("'", '20', $start);
    $end=str_replace("'", '20', $end);
    $start=new DateTime($start);
    $end=new DateTime($end);
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
    preg_match('~>([0-9]+)~', array_pop($messageNumber), $allTimeMsg);
    $messageNumber=(int)$allTimeMsg[1];

    if ($room=='') {
        $roomname='All Rooms';
    } else {
        $roomname=preg_grep('~class="searchroom~i', $data);
        $roomname=each($roomname);
        preg_match('~href=[^>]+>(.+?)</a>~i', $roomname[1], $matches);
        $roomname=$matches[1];
    }

    $users=preg_grep('~<div class="username"><a href="/users/~', $data);

    $count=array(); // =[] //php5.4+
    $names=array(); // =[] //php5.4+
    $shownMessages=0;
    foreach ($users as $user) { 
        preg_match('~/users/(\d+)/[^>]+>([^<]+)~', $user, $m);
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
            if ($minimumRepeat<$num) {
                $minimumRepeat=$num; 
            }
        }
        if ($num>=$minimumRepeat) { 
            $list.=",['".$names[$id]."', ".$num."]";
            $topUsers++;
        } else {
            $other+=$num; //add messages
            $otherUsers++; //add users
        }
    }
    $list="['Who', 'times']".$list.",['Others', $other]";
    $allTimePercent=round($shownMessages*100/$messageNumber, 1);
    if ($allTimePercent==100) { 
        $statisticsDetails='<b>All time data</b>';
    } else {
        $statisticsDetails='<b>recent data</b> ('.$allTimePercent.'% of all time)';
    }
    if ($totalUsersShown>1) $statisticsDetails.=" showing $totalUsersShown users";
    else $statisticsDetails.=" only 1 user";
    if ($otherUsers>0) $statisticsDetails.=" ($topUsers trending and $otherUsers others)";

    $footerNotice='';
    if ($minimumRepeat>1) {
        $footerNotice="* Users reiterating the phrase less than $minimumRepeat times ";
        $footerNotice.='<b>are not shown in the list</b>, and counted towards "Others" group for readability.';
    }

 
