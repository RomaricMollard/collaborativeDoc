<?php

/**
 * PARAMETERS
 */
define("ROOT", realpath(__DIR__."/../"));
define("FILES", ROOT."/files/");
define("BACKUPS", ROOT."/backup/");
define("MAXDIFF", 100);
define("TEMPFILE", "diffs.livedoc");

/**
 * Sauvegarder les fichiers
 */
function backup(){
	shell_exec("zip -jr ".BACKUPS.date("Ymd_hi").".zip ".FILES);
	file_put_contents(ROOT.TEMPFILE, "0");
}

function saveDiffs($diffs){
	$current_diffs = intval(file_get_contents(ROOT.TEMPFILE, ""));
	if($current_diffs+$diffs>MAXDIFF){
		backup();
	}else{
		file_put_contents(ROOT.TEMPFILE, $current_diffs+$diffs);
	}
}


