<?php
$image=trim(strip_tags(htmlspecialchars($_GET['image'])));
$image2 = $image;
$maxWidth=trim(strip_tags(htmlspecialchars($_GET['w'])));
$maxHeight=trim(strip_tags(htmlspecialchars($_GET['h'])));

$pathArray = explode('/', $image);
if(!empty($maxHeight) && !empty($maxWidth))
{
	
	array_push($pathArray, $pathArray[sizeof($pathArray)-1]);
	$pathArray[sizeof($pathArray)-2] = $maxWidth.'_'.$maxHeight;
	$image = implode('/', $pathArray);



}

header('Content-type: image/jpg');
if(file_exists($image))
	readfile($image);
else if(file_exists($image2))
	readfile($image2);
else
	readfile('images/imgnotfoundsmall.jpg');



