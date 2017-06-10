<?php

//$source
//$type

function module_apidoc_compileTable($data){
	/*$res = "<table class='table' style='margin-bottom:0px'>";

	foreach(preg_split("/((\r?\n)|(\r\n?))/", $data) as $row){
		$res .= "<tr>";

		$res .= "<td>";
		$res .= $row;
		$res .= "</td>";

		$res .= "</tr>";
	}

	$res .= "</table>";
	return $res;*/

	/*$tab = 0;
	$data_temp = "";
	$data = str_replace("{","\n{\n",$data);
	$data = str_replace("}","\n}\n",$data);
	foreach(preg_split("/((\r?\n)|(\r\n?))/", $data) as $row){
		$trim = trim($row);
		if($trim!="") {
			$data_temp .= str_repeat(" ", $tab) . $trim;
			if (substr($trim, 0, 1) == "{") {
				$tab++;
			}
			if (substr($trim, 0, 1) == "}") {
				$tab -= 1;
			}
		}
	}*/

	return "<pre>".trim($data)."</pre>";
}

function module_apidoc_compileTree($tree){
	$res = "";
	foreach($tree as $i=>$chapter){

		$res .= "<div class='panel-group' style='margin-top: 20px;'>";
		$res .= '<div class="panel panel-default">';
		$res .= '<div class="panel-heading" data-toggle="collapse" data-target="#chapter'.$i.'"><h4 class="panel-title">';
		$res .= "<a role='button'>".$chapter["title"]." (".count($chapter["content"]).")</a>";
		$res .= "</h4></div>";

		if(strlen($chapter["description"])>1) {
			$res .= '<div class="bs-callout bs-callout-info">';
			$res .= $chapter["description"];
			$res .= '</div>';
		}

		$res .= "<div aria-expanded='false' class='panel-collapse collapse in' id='chapter".$i."'>";

		$res .= '<div style="margin:10px">';

		foreach($chapter["content"] as $j=>$route){

			$res .= "<div class='panel-group' style='margin-bottom: 5px;'>";
			$res .= '<div class="panel panel-default" style="border-left: 5px solid #d22222;">';
			$res .= '<div class="panel-heading" data-toggle="collapse" data-target="#route'.$i.'_'.$j.'" style="display:flex;"><div style="width:70%;max-width: 400px;"><h4 class="panel-title" style="font-family: monospace;">';
			$res .= "<a role='button'>".$route["route"]."</a>";
			$res .= "</h4></div>";
			$res .= "<div style='flex:1;text-align:right;font-size: 12px;overflow-x: hidden;white-space: nowrap;text-overflow: ellipsis;'>".$route['description']."</div>";
			$res .= "</div>";

			$res .= "<div aria-expanded='false' class='panel-collapse collapse' id='route".$i."_".$j."'>";

			if(strlen($route["description"])>1) {
				$res .= '<div class="bs-callout bs-callout-info">';
				$res .= $route["description"];
				$res .= '</div>';
			}

			if(strlen($route["controller"])>1) {
				$res .= '<div class="bs-callout bs-callout-controller">';
				$res .= $route["controller"];
				$res .= '</div>';
			}

			if(strlen($route["input"])>1) {
				$res .= '<div class="bs-callout">';
				$res .= '<i class="glyphicon glyphicon-log-in" style="margin-right: 10px"></i><b>Input</b><br>';
				$res .= module_apidoc_compileTable($route["input"]);
				$res .= '</div>';
			}

			if(strlen($route["output"])>1) {
				$res .= '<div class="bs-callout">';
				$res .= '<i class="glyphicon glyphicon-log-out" style="margin-right: 10px"></i><b>Output</b><br>';
				$res .= module_apidoc_compileTable($route["output"]);
				$res .= '</div>';
			}

			if(strlen($route["errors"])>1) {
				$res .= '<div class="bs-callout">';
				$res .= '<i class="glyphicon glyphicon-flash" style="margin-right: 10px"></i><b>Errors</b><br>';
				$res .= "<div class='badge code'>".join("</div> <div class='badge code'>",preg_split(" *, *",$route["errors"]))."</div>";
				$res .= '</div>';
			}

			$res .= "</div>";
			$res .= "</div>";
			$res .= "</div>";

		}

		$res .= "</div>";

		$res .= "</div>";
		$res .= "</div>";
		$res .= "</div>";


	}
	return $res;
}

function module_apidoc_parseRoute($route_content){

	$res = Array(
		"description" => "",
		"controller" => "",
		"input" => "",
		"output" => "",
		"errors" => ""
	);

	$route_content = "\n".$route_content;

	$parameters = preg_split("/\\n {0,2}([a-z]+) *:/", $route_content, -1, PREG_SPLIT_DELIM_CAPTURE);

	$pre_description = array_shift($parameters);

	for ($i = 0; $i < count($parameters); $i += 2) {
		if(isset($res[$parameters[$i]])) {
			$res[$parameters[$i]] .= $parameters[$i + 1];
		}
	}

	$res["description"] = $pre_description.$res["description"];

	return $res;
}

function module_apidoc_parseChapter($chapter_content)
{
	$tree = Array();
	$routes = preg_split("/\\n *route *: *(.*?)\\n/", $chapter_content, -1, PREG_SPLIT_DELIM_CAPTURE);

	$tree["description"] = array_shift($routes);
	$tree["content"] = Array();

	for ($i = 0; $i < count($routes); $i += 2) {
		$parseContent = module_apidoc_parseRoute($routes[$i + 1]);
		$tree["content"][] = Array(
			"route" => $routes[$i],
			"description" => $parseContent["description"],
			"controller" => $parseContent["controller"],
			"input" => $parseContent["input"],
			"output" => $parseContent["output"],
			"errors" => $parseContent["errors"]
		);
	}

	return $tree;
}

function module_apidoc($source)
{
	$source = "\n" . $source ;

	$sourceTree = Array();

	$chapters = preg_split("/\\n *# *(.*?)\\n/", $source, -1, PREG_SPLIT_DELIM_CAPTURE);

	$introduction_html = array_shift($chapters);

	if (count($chapters) % 2 != 0) {
		return "error";
	}
	for ($i = 0; $i < count($chapters); $i += 2) {
		$parseContent = module_apidoc_parseChapter(htmlentities($chapters[$i + 1]));
		$sourceTree[] = Array(
			"title" => htmlentities($chapters[$i]),
			"description" => $parseContent["description"],
			"content" => $parseContent["content"]
		);
	}

	return $introduction_html ."<br>". module_apidoc_compileTree($sourceTree);

}

$CSS = "<style>";
$CSS .= ".panel-default>.panel-heading {background-color: #ffffff;}";
$CSS .= ".panel-group .panel {border: none;box-shadow: none;}";
$CSS .= "</style>";

$source = module_apidoc($CSS.$source);