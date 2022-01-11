<?php
function css_extension($file) {
	if(strpos($file, '.') !== false) {
		$extension = explode('.', $file);
		$extension = $extension[sizeof($extension) - 1];
		if($extension === 'css' || $extension === 'json' || $extension === 'xml') return 'css';
		elseif($extension === 'doc' || $extension === 'docx' || $extension === 'txt' || $extension === 'rtf' || $extension === 'odt' || $extension === 'ini') return 'docx';
		elseif($extension === 'html' || $extension === 'xhtml' || $extension === 'htm') return 'html';
		elseif($extension === 'js' || $extension === 'java' || $extension === 'py' || $extension === 'c' || $extension === 'bat' || $extension === 'bash' || $extension === 'sh') return 'java';
		elseif($extension === 'jpg' || $extension === 'jpeg' || $extension === 'png' || $extension === 'gif' || $extension === 'webp' || $extension === 'bmp' || $extension === 'psd' || $extension === 'tiff') return 'jpg';
		elseif($extension === 'lnk') return 'lnk';
		elseif($extension === 'mp3' || $extension === 'wav' || $extension === 'mid' || $extension === 'midi' || $extension === 'ogg' || $extension === 'flac') return 'mp3';
		elseif($extension === 'avi' || $extension === 'mpeg' || $extension === 'mpg' || $extension === 'mp4' || $extension === 'flv' || $extension === 'mov' || $extension === 'ts' || $extension === 'wmv' || $extension === 'mkv' || $extension === 'divx') return 'mp4';
		elseif($extension === 'pdf') return 'pdf';
		elseif($extension === 'php') return 'php';
		elseif($extension === 'ppt' || $extension === 'pps') return 'ppt';
		elseif($extension === 'svg') return 'svg';
		elseif($extension === 'xls' || $extension === 'ods' ) return 'xls';
		elseif($extension === 'zip' || $extension === '7z' || $extension === 'rar' || $extension === 'gz' || $extension === 'bz' || $extension === 'bz2') return 'zip';
	}
	return 'nc';
}

function array_sort($array, $case, $order = 'ASC') {
	$new_array = array();
	$sortable_array = array();
	if(count($array) > 0) {
		foreach($array as $k => $v) {
			if(is_array($v)) {
				foreach($v as $k2 => $v2) {
					if($k2 === $case)
						$sortable_array[$k] = $v2;
				}
			}
			else
				$sortable_array[$k] = $v;
		}
		if($order === 'ASC')
			asort($sortable_array);
		else
			arsort($sortable_array);
		foreach($sortable_array as $k => $v)
			$new_array[$k] = $array[$k];
	}
	return $new_array;
}

function path_parents($nb) {
	if($nb === 0)
		return '.';
	else {
		$return = '';
		for($i = 0; $i < $nb; $i++)
			$return .= '../';
		return $return;
	}
}

function createTrash($lowercase) {
	if($lowercase === '1') {
		$from = 'Trash';
		$to = 'trash';
	}
	else {
		$from = 'trash';
		$to = 'Trash';
	}
	if(file_exists_cs($to)) {
		if(is_file($to) || is_link($to)) {
			$i = 1;
			while(file_exists($to . ' (' + $i + ')'))
				$i++;
			rename($to, $to . ' (' + $i + ')');
			mkdir($to);
		}
	}
	elseif(file_exists($from)) {
		if(is_dir($from)) {
			rename($from, $from . '_tmp');
			rename($from . '_tmp', $to);
		}
		else {
			$i = 1;
			while(file_exists($from . ' (' + $i + ')'))
				$i++;
			rename($from, $from . ' (' + $i + ')');
			mkdir($to);
		}
	}
	else
		mkdir($to);
}
