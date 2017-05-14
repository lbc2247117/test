<?php
function typeToName($type){
    switch ($type) {
        case 0:
            $name = '美食';
            break;
        case 1:
            $name = '住宿';
            break;
        case 2:
            $name = '购物';
            break;
        case 3:
            $name = '娱乐';
            break;
        
        default:
            $name = '美食';
            break;
    }
    return $name;
}
function styleToName($style){
    switch ($style) {
        case 0:
            $name = '自营商家';
            break;
        case 1:
            $name = '景区商家';
            break;
    return $name;
    }
}
?>