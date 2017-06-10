<?php

include("common.php");

/**
 * Get files structure and return it in json
 */



header('Content-Type: application/json');

function recursive_dir($path, $parent)
{
	$dossier = opendir($path) or die("Fatal error");
	$tree = array();

	while ($element = readdir($dossier)) {
		if (substr($element,0,1)!=".") {
			if (file_exists($path . "/" . $element) && is_dir($path . "/" . $element)) {
				$tree[] = Array(
					"realpath"=>$parent . "/" .$element,
					"path"=>$element,
					"content"=>recursive_dir($path . "/" . $element, $parent."/".$element),
					"type"=>"dir"
				);
			} else {
				$tree[] = Array(
					"path"=>$element,
					"realpath"=>$parent."/".$element,
					"type"=>"file"
				);
			}
		}
	}
	closedir($dossier);
	return $tree;
}

$all = recursive_dir(FILES,"");

echo json_encode($all);