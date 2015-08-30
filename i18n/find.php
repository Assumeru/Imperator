<?php
//Find variable replacements

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('../imperator/', FilesystemIterator::SKIP_DOTS));

header('Content-type: text/plain');

foreach($files as $file) {
	if($file->isReadable() && in_array($file->getExtension(), array('phtml', 'php'))) {
		filter($file->getPathname());
	}
}

function filter($filename) {
	$file = file_get_contents($filename);
	preg_match_all('/(translate|__)\s*\((.*?)\)/', $file, $matches);
	if(!empty($matches) && !empty($matches[2])) {
		echo '#', $filename, "\n";
		foreach($matches[2] as $match) {
			echo $match, "\n";
		}
	}
}