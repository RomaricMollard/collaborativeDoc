<?php

include("common.php");

/**
 * Load a file compiled into readable html
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
if(file_exists(FILES.$route)) {
	$source = file_get_contents(FILES . $route);


	$type = preg_match("/<!--TYPE=(.*?)-->/i", $source, $matches);
	$type = $matches[1];
	$source = preg_replace("/<!--TYPE=.*?-->/i", "", $source);

	if($type=="html"){
		//Do nothing
	}elseif(file_exists(__DIR__."/modules/".$type.".php")){
		include(__DIR__."/modules/".$type.".php");
	}else{ //Type = texte
		$source = "<pre>".htmlentities($source)."</pre>";
	}

}
echo json_encode(Array("content"=>$source, "type"=>$type));