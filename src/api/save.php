<?php

include("common.php");

/**
 * Save sources of a file
 */



header('Content-Type: application/json');

$postdata = file_get_contents("php://input");
$request = json_decode($postdata);

if(!isset($request->route) or !isset($request->source) or !isset($request->type)){
	echo json_encode($_POST);
	die();
}

$route = $request->route;
$source = $request->source;
$type = $request->type;

$route = str_replace("..","", $route);//Remove back directories

if(file_exists(FILES.$route)) {
	$oldLen = strlen(file_get_contents(FILES . $route));
}else {
    mkdir(dirname(FILES.$route), 0777, true);
	$oldLen = 0;
}

$source = "<!--TYPE=".$type."-->".$source;
file_put_contents(FILES . $route, $source);

$diffs = abs(strlen($source)-$oldLen);
saveDiffs($diffs+1);



echo json_encode(Array());