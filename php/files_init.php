<?php
function no_end_slash($str) {
	$lng = mb_strlen($str) - 1;
	if($lng >= 0 && $str[$lng] === '/')
		$str = substr($str, 0, $lng);
	return $str;
}

function split_dirname($dirname) {
	$dirname = no_end_slash($dirname);

	if(strpos($dirname, '/') === false)
		$path = '';
	else {
		$path_arr = explode('/', $dirname);
		$nb_path = sizeof($path_arr);
		$path = '';
		for($i = 0; $i < $nb_path - 1; $i++)
			$path .= $path_arr[$i] . '/';
		$dirname = $path_arr[$nb_path - 1];
	}
	return array('path' => $path, 'name' => $dirname);
}

function split_filename($filename) {
	if(strpos($filename, '/') === false)
		$path = '';
	else {
		$path_arr = explode('/', $filename);
		$nb_path = sizeof($path_arr);
		$path = '';
		for($i = 0; $i < $nb_path - 1; $i++)
			$path .= $path_arr[$i] . '/';
		$filename = $path_arr[$nb_path - 1];
	}
	if(strpos($filename, '.') === false || $filename[mb_strlen($filename) - 1] === '.') {
		$dot = '';
		$extension = '';
	}
	else {
		$name_arr = explode('.', $filename);
		$nb_name = sizeof($name_arr);
		$filename = '';
		for($i = 0; $i < $nb_name - 1; $i++) {
			$filename .= $name_arr[$i];
			if($i < $nb_name - 2)
				$filename .= '.';
		}
		$extension = $name_arr[$nb_name - 1];
		$dot = '.' . $extension;
	}
	return array('path' => $path, 'name' => $filename, 'extension' => $extension, 'dot_extension' => $dot);
}

function file_exists_cs($file) {
	if(file_exists($file)) {
		$file = split_filename($file);
		$directory = $file['path'];
		if(empty($directory))
			$directory = '.';
		$filename = $file['name'] . $file['dot_extension'];
		if($handle = opendir($directory)) {
			while(false !== ($entry = readdir($handle))) {
				if($entry != '.' && $entry != '..' && $entry === $filename)
					return true;
			}
			closedir($handle);
			return false;
		}
		else
			return false;
	}
	else
		return false;
}

function size_of_file($size) {
	if($size < 1024)
		return $size . ' o';
	else {
		$m = pow(1024, 2);
		$g = pow(1024, 3);
		if($size < $m)
			return round($size / 1024, 1) . ' Ko';
		elseif($size < $g)
			return round($size / $m, 1) . ' Mo';
		else
			return round($size / $g, 1) . ' Go';
	}
}

function parse_size($size) {
	$unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
	$size = preg_replace('/[^0-9\.]/', '', $size);
	if($unit)
		return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
	else
		return round($size);
}

function rename_exist($filename) {
	$i = 1;
	while(file_exists($filename . ' (' + $i + ')'))
		$i++;
	if(rename($filename, $filename . ' (' + $i + ')'))
		return $i;
	else
		return false;
}

function create_htrashccess() {
	$path = 'Trash/.htaccess';
	if(!file_exists($path) || is_dir($path) || is_link($path)) {
		if(file_exists($path))
			rename_exist($path);
		file_put_contents($path, "RewriteEngine On\nRewriteRule ^(.*)$ https://%{HTTP_HOST}" . server_infos()['script'] . "?trashed=true [L,R=301]\n");
	}
}
