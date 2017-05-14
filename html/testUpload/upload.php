<?php

header("Content-type:text/html;charset=utf-8");
$file = $_FILES['cover']['tmp_name'];
$fileVideo = $_FILES['video']['tmp_name'];
$name = $_REQUEST['Desc'];
/* $move_uploaded_file($file, $name.$_FILES['cover']['name']);
  move_uploaded_file($fileVideo, $name.$_FILES['video']['name']); */
$result['status'] = 0;
$result['msg'] = $file . '|' . $fileVideo . '|' . $name;
exit(json_encode($result));
