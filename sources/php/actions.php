<?php
function explode_multiple_files($files) {
	if(strpos($files, '%2F%2F%2F'))
		return explode('%2F%2F%2F', $files);
	else
		return explode('///', $files);
}

/* SET SETTINGS */

if(isset($_POST['set_settings'])) {
	$return = "Updated settings :";
	if(isset($_POST['view'])) {
		$_SESSION['view'] = $_POST['view'];
		$return .= "\nview=" . $_POST['view'];
	}
	if(isset($_POST['trash'])) {
		$_SESSION['trash'] = $_POST['trash'];
		$return .= "\ntrash=" . $_POST['trash'];
	}
	if(isset($_POST['upload_exists'])) {
		$_SESSION['upload_exists'] = $_POST['upload_exists'];
		$return .= "\nupload_exists=" . $_POST['upload_exists'];
	}
	if(isset($_POST['copy_move_exists'])) {
		$_SESSION['copy_move_exists'] = $_POST['copy_move_exists'];
		$return .= "\ncopy_move_exists=" . $_POST['copy_move_exists'];
	}
	exit($return);
}

/* UPLOAD */

elseif(isset($_FILES['upload'])) {
	$return = '';
	$nb_files = count($_FILES['upload']['name']);
	$ask_uploads = array();
	for($i = 0; $i < $nb_files; $i++) {
		$name = $_FILES['upload']['name'][$i];
		$name_html = htmlentities($name, ENT_QUOTES);

		if($_FILES['upload']['error'][$i] === 0) {
			$dont_upload = false;
			if(file_or_link_exists($current . $name)) {
				if($_POST['exists'] === '1') {
					$dont_upload = true;
					$return .= "\n$name_html</b> already exists<b><br><br>";
				}
				elseif($_POST['exists'] === '2') {
					$name = split_filename($name);
					$extension = $name['dot_extension'];
					$name = $name['name'];
					$j = 1;
					while(file_or_link_exists($current . $name . $extension . '.bak' . $j))
						$j++;
					if(@!rename($current . $name . $extension, $current . $name . $extension . '.bak' . $j)) {
						$dont_upload = true;
						$return .= "\n$name_html</b> cannot be renammed<b><br><br>";
					}
					$name = $name . $extension;
				}
				elseif($_POST['exists'] === '3') {
					$name = split_filename($name);
					$extension = $name['dot_extension'];
					$name = $name['name'];
					$j = 1;
					while(file_or_link_exists($current . $name . " ($j)" . $extension))
						$j++;
					$name .= " ($j)" . $extension;
				}
				elseif($_POST['exists'] === '4') {
					if(is_file($current . $name) || is_link($current . $name)) {
						if(@!unlink($current . $name))
							$return .= "\n$name_html</b> cannot be deleted<b><br><br>";
					}
					else {
						if(@!rm_full_dir($current . $name))
							$return .= "\n$name_html/</b> cannot be deleted<b><br><br>";
					}
				}
				else {
					array_push($ask_uploads, $current . $name);

					$j = 1;
					while(file_or_link_exists($current . $name . '.ask' . $j))
						$j++;
					$name .= '.ask' . $j;

					array_push($ask_uploads, $current . $name);
				}
			}
			if($dont_upload === false && @!move_uploaded_file($_FILES['upload']['tmp_name'][$i], $current . $name))
				$return .= "\n$name_html</b> cannot be uploaded (#1)<b><br><br>";
		}
		else
			$return .= "\n$name_html</b> cannot be uploaded (#2)<b><br><br>";
	}
	if(sizeof($ask_uploads) !== 0) {
		if(empty($return))
			$return = '[ask=' . implode('|', $ask_uploads) . ']';
		else {
			$nb_ask_uploads = sizeof($ask_uploads);
			for($i = 0; $i < $nb_ask_uploads; $i++) {
				if($i % 2 !== 0 && @!unlink($current . $ask_uploads[$i]))
					$return .= "\n" . htmlentities($ask_uploads[$i], ENT_QUOTES) . '</b> cannot be deleted<b><br><br>';
				elseif($i % 2 === 0)
					$return .= "\n" . htmlentities($ask_uploads[$i], ENT_QUOTES) . '</b> cannot be uploaded, please try again<b><br><br>';
			}
			$return = substr($return, 0, mb_strlen($return) - 8);
		}
	}
	elseif(empty($return))
		$return = 'uploaded';
	else
		$return = substr($return, 0, mb_strlen($return) - 8);
	exit($return);
}

/* ASK AFTER UPLOAD */

elseif(isset($_POST['ask']) && isset($_POST['files'])) {
	$choice = $_POST['ask'];
	$files_tmp = explode('|', $_POST['files']);
	$nb_files_tmp = sizeof($files_tmp);
	$files = array();
	$j = 0;
	for($i = 0; $i < $nb_files_tmp; $i++) {
		if($i % 2 === 0)
			$files[$j]['old'] = $files_tmp[$i];
		else {
			$files[$j]['ask'] = $files_tmp[$i];
			$j++;
		}
	}

	$return = '';
	$nb_files = sizeof($files);
	if($choice === '0') {
		for($i = 0; $i < $nb_files; $i++) {
			if(@!unlink($current . $files[$i]['ask']))
				$return .= "\n" . htmlentities($files[$i]['ask'], ENT_QUOTES) . '</b> cannot be deleted<b><br><br>';
		}
	}
	elseif($choice === '1') {
		for($i = 0; $i < $nb_files; $i++) {
			$name = $files[$i]['old'];
			$name = split_filename($files[$i]['old']);
			$extension = $name['dot_extension'];
			$name = $name['name'];
			$j = 1;
			while(file_or_link_exists($current . $name . $extension . '.bak' . $j))
				$j++;
			$name .= $extension . '.bak' . $j;
			if(@!rename($current . $files[$i]['old'], $current . $name))
				$return .= "\n" . htmlentities($files[$i]['old'], ENT_QUOTES) . '</b> cannot be renammed<b><br><br>';
			elseif(@!rename($current . $files[$i]['ask'], $current . $files[$i]['old']))
				$return .= "\n" . htmlentities($files[$i]['ask'], ENT_QUOTES) . '</b> cannot be renammed<b><br><br>';
		}
	}
	elseif($choice === '2') {
		for($i = 0; $i < $nb_files; $i++) {
			$name = split_filename($files[$i]['old']);
			$extension = $name['dot_extension'];
			$name = $name['name'];
			$j = 1;
			while(file_or_link_exists($current . $name . " ($j)" . $extension))
				$j++;
			$name .= " ($j)" . $extension;
			if(@!rename($current . $files[$i]['ask'], $current . $name))
				$return .= "\n" . htmlentities($files[$i]['ask'], ENT_QUOTES) . '</b> cannot be renammed<b><br><br>';
		}
	}
	elseif($choice === '3') {
		for($i = 0; $i < $nb_files; $i++) {
			if(@!unlink($current . $files[$i]['old']))
				$return .= "\n" . htmlentities($files[$i]['old'], ENT_QUOTES) . '</b> cannot be deleted<b><br><br>';
			elseif(@!rename($current . $files[$i]['ask'], $current . $files[$i]['old']))
				$return .= "\n" . htmlentities($files[$i]['ask'], ENT_QUOTES) . '</b> cannot be renammed<b><br><br>';
		}
	}
	else
		$return = 'Unknown choice<br><br>';
	if(empty($return))
		$return = 'uploaded';
	else
		$return = substr($return, 0, mb_strlen($return) - 8);
	exit($return);
}

/* NEW FILE OR FOLDER */

elseif(isset($_POST['new']) && isset($_POST['name'])) {
	if(strpos($_POST['name'], "'") === false) {
		$new_name = $_POST['name'];

		if(file_or_link_exists($current . $new_name))
			exit('File or directory already exists');
		else {
			if($_POST['new'] === 'file') {
				if(@file_put_contents($current . $new_name, '') !== false)
					exit('created');
				else
					exit('File not created');
			}
			else {
				if(@mkdir($current . $new_name))
					exit('created');
				else
					exit('Directory not created');
			}
		}
	}
	else
		exit('Apostrophe prohibited');
}

/* RENAME ELEMENT */

elseif(isset($_POST['rename']) && isset($_POST['name'])) {
	if(@rename($current . urldecode($_POST['rename']), $current . $_POST['name']))
		exit('renamed');
	else
		exit('Not renamed');
}

/* DUPLICATE ELEMENT */

elseif(isset($_POST['duplicate'])) {
	$name = urldecode($_POST['duplicate']);
	if(file_or_link_exists($current . $name)) {
		if(@copy_or_move($current . $name, $current, false, 2, 2, 2))
			exit('duplicated');
		else
			exit('File or directory not duplicated');
	}
	else
		exit('File or directory not found');
}

/* COPY ELEMENT */

elseif(isset($_POST['copy']) && isset($_POST['path']) && isset($_POST['if_exists'])) {
	$name = urldecode($_POST['copy']);
	$if_exists = intval($_POST['if_exists']);
	if(file_or_link_exists($current . $name)) {
		if(@copy_or_move($current . $name, $_POST['path'], false, $if_exists, $if_exists, 1))
			exit('copied');
		else
			exit('File or directory not copied');
	}
	else
		exit('File or directory not found');
}

/* MOVE ELEMENT */

elseif(isset($_POST['move']) && isset($_POST['path']) && isset($_POST['if_exists'])) {
	$name = urldecode($_POST['move']);
	$if_exists = intval($_POST['if_exists']);
	if(file_or_link_exists($current . $name)) {
		if(@copy_or_move($current . $name, $_POST['path'], true, $if_exists, $if_exists, 1))
			exit('moved');
		else
			exit('File or directory not moved');
	}
	else
		exit('File or directory not found');
}

/* DELETE ELEMENT */

elseif(isset($_POST['delete'])) {
	$name = urldecode($_POST['delete']);
	if(is_file($current . $name) || is_link($current . $name)) {
		if(($trash_active === true && @to_trash($current . $name)) || ($trash_active === false && @unlink($current . $name)))
			exit('deleted');
		else
			exit('File not deleted');
	}
	elseif(is_dir($current . $name)) {
		if(($trash_active === true && @to_trash($current . $name)) || ($trash_active === false && @rm_full_dir($current . $name)))
			exit('deleted');
		else
			exit('Directory not deleted');
	}
	else
		exit('File not found');
}

/* EDIT ELEMENT */

elseif(isset($_POST['read_file'])) {
	$name = urldecode($_POST['read_file']);
	if(is_file($current . $name) && !is_link($current . $name))
		exit('' . file_get_contents($current . $name));
	else
		exit('[file_edit_not_found]');
}

elseif(isset($_POST['edit_file']) && isset($_POST['name'])) {
	$name = urldecode($_POST['name']);
	if(is_file($current . $name) && !is_link($current . $name)) {
		if(@file_put_contents($current . $name, $_POST['edit_file']))
			exit('edited');
		else
			exit('File not edited');
	}
	else
		exit('File not found');
}

/* GET CHMODS */

elseif(isset($_POST['get_chmods'])) {
	$name = urldecode($_POST['get_chmods']);
	if(file_or_link_exists($current . $name)) {
		$fileperms = @find_chmods($current . $name);
		if($fileperms !== false)
			exit("[chmods=$fileperms]");
		else
			exit('Chmods not found');
	}
	else
		exit('File not found');
}

/* CHANGE CHMODS */

elseif(isset($_POST['set_chmods']) && isset($_POST['name'])) {
	$name = urldecode($_POST['name']);
	if(file_or_link_exists($current . $name)) {
		if(@chmod($current . $name, octdec(intval($_POST['set_chmods']))))
			exit('chmoded');
		else
			exit('Chmods not updated');
	}
	else
		exit('File not found');
}

/* DUPLICATE MULTIPLE ELEMENTS */

elseif(isset($_POST['duplicate_multiple'])) {
	$return = '';
	foreach(explode_multiple_files($_POST['duplicate_multiple']) as $file_to_duplicate) {
		$file_to_duplicate = urldecode($file_to_duplicate);
		if(file_or_link_exists($file_to_duplicate)) {
			if(@!copy_or_move($file_to_duplicate, $current, false, 2, 2, 2))
				$return .= "<b>$file_to_duplicate</b> : File or directory not duplicated<br><br>";
		}
		else
			$return .= "<b>$file_to_duplicate</b> : File or directory not found<br><br>";
	}
	if(empty($return))
		exit('duplicateds');
	else
		exit(substr($return, 0, mb_strlen($return) - 8));
}

/* COPY MULTIPLE ELEMENTS */

elseif(isset($_POST['copy_multiple']) && isset($_POST['if_exists'])) {
	$if_exists = intval($_POST['if_exists']);
	$return = '';
	foreach(explode_multiple_files($_POST['copy_multiple']) as $file_to_copy) {
		$file_to_copy = urldecode($file_to_copy);
		if(file_or_link_exists($file_to_copy)) {
			if(@!copy_or_move($file_to_copy, $current, false, $if_exists, $if_exists, 1))
				$return .= "<b>$file_to_copy</b> : File or directory not copied<br><br>";
		}
		else
			$return .= "<b>$file_to_copy</b> : File or directory not found<br><br>";
	}
	if(empty($return))
		exit('copieds');
	else
		exit(substr($return, 0, mb_strlen($return) - 8));
}

/* MOVE MULTIPLE ELEMENTS */

elseif(isset($_POST['move_multiple']) && isset($_POST['if_exists'])) {
	$if_exists = intval($_POST['if_exists']);
	$return = '';
	foreach(explode_multiple_files($_POST['move_multiple']) as $file_to_move) {
		$file_to_move = urldecode($file_to_move);
		if(file_or_link_exists($file_to_move)) {
			if(@!copy_or_move($file_to_move, $current, true, $if_exists, $if_exists, 1))
				$return .= "<b>$file_to_move</b> : File or directory not moved<br><br>";
		}
		else
			$return .= "<b>$file_to_move</b> : File or directory not found<br><br>";
	}
	if(empty($return))
		exit('moveds');
	else
		exit(substr($return, 0, mb_strlen($return) - 8));
}

/* DELETE MULTIPLE ELEMENTS */

elseif(isset($_POST['delete_multiple'])) {
	$return = '';
	foreach(explode_multiple_files($_POST['delete_multiple']) as $file_to_delete) {
		$file_to_delete = urldecode($file_to_delete);
		if(is_file($file_to_delete) || is_link($file_to_delete)) {
			if(($trash_active === true && @!to_trash($file_to_delete)) || ($trash_active === false && @!unlink($file_to_delete)))
				$return .= "<b>$file_to_delete</b> : File not deleted<br><br>";
		}
		elseif(is_dir($file_to_delete)) {
			if(($trash_active === true && @!to_trash($file_to_delete)) || ($trash_active === false && @!rm_full_dir($file_to_delete)))
				$return .= "<b>$file_to_delete</b> : Directory not deleted<br><br>";
		}
		else
			$return .= "<b>$file_to_delete</b> : File or directory not found<br><br>";
	}
	if(empty($return))
		exit('deleteds');
	else
		exit($return);
}

/* SET MULTIPLE CHMODS */

elseif(isset($_POST['set_multiple_chmods']) && isset($_POST['files'])) {
	$return = '';
	foreach(explode_multiple_files($_POST['files']) as $file_to_chmod) {
		$file_to_chmod = urldecode($file_to_chmod);
		if(file_or_link_exists($file_to_chmod)) {
			if(@!chmod($file_to_chmod, octdec(intval($_POST['set_multiple_chmods']))))
				$return .= "<b>$file_to_chmod</b> : Chmods not updated<br><br>";
		}
		else
			$return .= "<b>$file_to_chmod</b> : File or directory not found<br><br>";
	}
	if(empty($return))
		exit('chmodeds');
	else
		exit(substr($return, 0, mb_strlen($return) - 8));
}

/* TRASH MULTIPLE ELEMENTS */

elseif(isset($_POST['trash'])) {
	$return = '';
	foreach(explode_multiple_files($_POST['trash']) as $file_to_delete) {
		$file_to_delete = urldecode($file_to_delete);
		if(@!to_trash($file_to_delete))
			$return .= "<b>$file_to_delete</b> : File not deleted<br><br>";
	}
	if(empty($return))
		exit('trasheds');
	else
		exit($return);
}

/* DELETE ELEMENT FROM TRASH */

elseif(isset($_POST['permanently_delete'])) {
	$name = urldecode($_POST['permanently_delete']);
	if(is_file($current . $name) || is_link($current . $name)) {
		if(@unlink($current . $name))
			exit('deleted');
		else
			exit('File not deleted');
	}
	elseif(is_dir($current . $name)) {
		if(@rm_full_dir($current . $name))
			exit('deleted');
		else
			exit('Directory not deleted');
	}
	else
		exit('File not found');
}

/* DELETE MULTIPLE ELEMENTS FROM TRASH */

elseif(isset($_POST['permanently_delete_multiple'])) {
	$return = '';
	foreach(explode_multiple_files($_POST['permanently_delete_multiple']) as $file_to_delete) {
		$file_to_delete = urldecode($file_to_delete);
		if(is_file($file_to_delete) || is_link($file_to_delete)) {
			if(@!unlink($file_to_delete))
				$return .= "<b>$file_to_delete</b> : File not deleted<br><br>";
		}
		elseif(is_dir($file_to_delete)) {
			if(@!rm_full_dir($file_to_delete))
				$return .= "<b>$file_to_delete</b> : Directory not deleted<br><br>";
		}
		else
			$return .= "<b>$file_to_delete</b> : File or directory not found<br><br>";
	}
	if(empty($return))
		exit('deleteds');
	else
		exit($return);
}

/* EMPTY TRASH */

elseif(isset($_POST['empty_trash'])) {
	if(@rm_full_dir('Trash')) {
		if(@mkdir('Trash')) {
			if(@create_htrashccess())
				exit('emptied');
			else
				exit('Trash cannot be protected');
		}
		else
			exit('Trash cannot be created');
	}
	else
		exit('Trash cannot emptied');
}

/* UPDATE */

elseif(isset($_POST['update'])) {
	$script_name = split_filename($server_infos['script']);
	$script_name = $script_name['name'] . $script_name['dot_extension'];

	$i = 1;
	while(file_or_link_exists($script_name . '.update' . $i))
		$i++;
	$update_name = $script_name . '.update' . $i;

	$i = 1;
	while(file_or_link_exists("update_temp$i.php"))
		$i++;
	$temp_name = "update_temp$i.php";

	if(@file_put_contents($update_name, @file_get_contents(urldecode($_POST['update'])))) {
		if(@file_put_contents($temp_name, '<?php
unlink($_GET[\'file\']);
rename($_GET[\'update\'], $_GET[\'file\']);
unlink($_GET[\'tmp\']);
header(\'Location: \' . $_GET[\'file\']);
')) {
			exit("[update=$script_name|$update_name|$temp_name]");
		}
		else
			exit('Creation of temporary file failed');
	}
	else
		exit('Download failed');
}

else
	exit('Unknown action');
