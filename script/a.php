#!/usr/bin/env php
<?php

// TODO: 汉字测试 / @@ 后新开一行 / chmod 777

// cmd:
// cat diff | ./git_diff.php | less

$aConfig = array(
	"file" => 222,
	"add_line" => 118,
	"sub_line" => 208,
	"comment" => 39,
	"normal" => 252,
	"tabs" => 4,
);

function hex2bin($s) {
	return pack("H*", $s);
}

function color($sControl = "0") {
	return hex2bin("1b").'['.$sControl.'m';
}

function fg($iColor) {
	return color("38;5;".$iColor);
}

function bg($iColor) {
	return color("48;5;".$iColor);
}

while ($sLine = fgets(STDIN)) {
	// $sLine = str_replace("\t", bg(155)."    ".color(), $sLine);

	$sMode = substr($sLine, 0, 1);
	$sLine = substr($sLine, 1);

	if (!trim($sLine)) {
		continue;
	}

	// $sLine = preg_replace("^.*?\t");

	$iCount = 1;
	while ($iCount) {

		$sLine = preg_replace_callback('#^(.*?)\t#', function ($aMatch) use ($aConfig) {

			$sReturn = $aMatch[1];

			$iLength = mb_strwidth($sReturn, "UTF-8");
			$iLength = $aConfig["tabs"] - $iLength % $aConfig["tabs"];
			$sReturn .= str_repeat(" ", $iLength);

			return $sReturn;
		}, $sLine, 1, $iCount);
	}
	$sLine = $sMode.$sLine;

	switch ($sMode) {
		case "-":
			if (substr($sLine, 0, 3) === "---") {
				continue 2;
			}
			$sLine = fg($aConfig["sub_line"]).$sLine.color();
			break;
		case "+":
			if (substr($sLine, 0, 3) === "+++") {
				continue 2;
			}
			$sLine = fg($aConfig["add_line"]).$sLine.color();
			break;
		case "@":
			$sLine = preg_replace("#@@(.*?)@@#", fg($aConfig["comment"]).'@$1@'.fg($aConfig["normal"]), $sLine);
			$sLine = "\n".$sLine.color();
			break;
		case " ":
			$sLine = fg($aConfig["normal"]).$sLine.color();
			break;
		case "i": // index
			continue 2;
		case "d": // diff
			$sLine = preg_replace('#^.*?\-\-git a/(.+) b/.*$#', '$1', $sLine);
			$sLine = "\n    ".fg($aConfig["file"]).color(1).$sLine.color();
			break;
	}

	echo $sLine;
}

