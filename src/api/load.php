<?php

include("common.php");

/**
 * Load sources of a file
 */
header('Content-Type: application/json');

$postdata = file_get_contents("php://input");
$request = json_decode($postdata);

if(!isset($request->route)){
	echo json_encode($_POST);
	die();
}

$route = $request->route;

$route = str_replace("..","", $route);//Remove back directories

$source = "";
$type = "";
if($route!="" && file_exists(FILES.$route)) {
	$source = file_get_contents(FILES . $route);

	$type = preg_match("/<!--TYPE=(.*?)-->/i", $source, $matches);
	$type = $matches[1];
	$source = preg_replace("/<!--TYPE=.*?-->/i", "", $source);

	echo json_encode(Array("source"=>$source, "type"=>$type));

}else{
	echo json_encode(Array("error"=>"nosuchfile"));
}
