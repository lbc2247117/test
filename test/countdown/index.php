<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/2/24 0024
 * Time: 9:40
 */
ini_set('date.timezone','Asia/Shanghai');
$year = strtotime('2019-02-04');
$time = $year - time();
$day = floor($time / (24 * 60 * 60)) ;
$hour = floor(($time-$day*24*60*60) / (60*60));
$min =  floor(floor($time / 60) % 60);
$sec = $time % 60;
$strtime =  '离2019年春节还有：'.$day . '天'.$hour.'时'. $min.'分'.$sec.'秒';
?>
<html>
<head>
    <title>2019年春节倒计时</title>
    <meta charset="UTF-8">
</head>
<body>
<div id="time">123</div>
<script>
    var _id = document.getElementById('time');
    var _time = <?php echo $time?>;
    function timestampToTime() {
        var _html = '离2019年春节还有：';
        var _day = Math.floor( _time / (24 *60 *60));
        var _hour = Math.floor((_time % (24 *60 *60))/(60*60));
        var _min =  Math.floor(Math.floor(_time / 60) % 60);
        var _sec = _time % 60;
        _html += _day+'天'+_hour+'时'+_min+'分'+_sec+'秒';
        _id.textContent = _html;
        _time -- ;
        setTimeout('timestampToTime()',1000);
    }
    timestampToTime();
</script>
</body>

</html>
