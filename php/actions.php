<?php
function add_zeros($val)
{
	if($val < 10)
		$ret = '00';
	elseif($val < 100)
		$ret = '0';
	else
		$ret = '';
	return($ret . $val);
}

/* UPLOAD */

if(isset($_FILES['upload']))
{
	$return = '';
	$nb_files = count($_FILES['upload']['name']);
	$ask_uploads = array();
	for($i = 0; $i < $nb_files; $i++)
	{
		$name = $_FILES['upload']['name'][$i];
		$name_html = htmlentities($name, ENT_QUOTES);

		if($_FILES['upload']['error'][$i] === 0)
		{
			$dont_upload = false;
			if(@file_exists($current . $name))
			{
				if($_POST['exists'] === '0')
				{
					array_push($ask_uploads, $current . $name);

					$j = 1;
					while(file_exists($current . $name . '.ask' . add_zeros($j)))
						$j++;
					$name .= '.ask' . add_zeros($j);

					array_push($ask_uploads, $current . $name);
				}
				elseif($_POST['exists'] === '1')
				{
					if(@is_file($current . $name) || @is_link($current . $name))
					{
						if(@!unlink($current . $name))
							$return .= "\n$name_html</b> cannot be deleted<b><br><br>";
					}
					else
					{
						if(@!rm_full_dir($current . $name))
							$return .= "\n$name_html/</b> cannot be deleted<b><br><br>";
					}
				}
				elseif($_POST['exists'] === '2')
				{
					$name = split_filename($name);
					$extension = $name['dot_extension'];
					$name = $name['name'];
					$j = 1;
					while(file_exists($current . $name . '.bak' . add_zeros($j) . $extension))
						$j++;
					if(@!rename($current . $name . $extension, $current . $name . '.bak' . add_zeros($j) . $extension))
					{
						$dont_upload = true;
						$return .= "\n$name_html</b> cannot be renammed<b><br><br>";
					}
					$name = $name . $extension;
				}
				elseif($_POST['exists'] === '3')
				{
					$name = split_filename($name);
					$extension = $name['dot_extension'];
					$name = $name['name'];
					$j = 1;
					while(file_exists($current . $name . " ($j)" . $extension))
						$j++;
					$name .= " ($j)" . $extension;
				}
				else
				{
					$dont_upload = true;
					$return .= "\n$name_html</b> already exists<b><br><br>";
				}
			}
			if($dont_upload === false && @!move_uploaded_file($_FILES['upload']['tmp_name'][$i], $current . $name))
				$return .= "\n$name_html</b> cannot be uploaded (#1)<b><br><br>";
		}
		else
			$return .= "\n$name_html</b> cannot be uploaded (#2)<b><br><br>";
	}
	if(sizeof($ask_uploads) !== 0)
	{
		if(empty($return))
			$return = '[ask=' . implode(',', $ask_uploads) . ']';
		else
		{
			$nb_ask_uploads = sizeof($ask_uploads);
			for($i = 0; $i < $nb_ask_uploads; $i++)
			{
				if($i % 2 !== 0 && @!unlink($current . $ask_uploads[$i]))
					$return .= "\n" . htmlentities($ask_uploads[$i], ENT_QUOTES) . '</b> cannot be deleted<b><br><br>';
				elseif($i % 2 === 0)
					$return .= "\n" . htmlentities($ask_uploads[$i], ENT_QUOTES) . '</b> cannot be uploaded, please try again<b><br><br>';
			}
			$return = substr($return, 0, strlen($return) - 8);
		}
	}
	elseif(empty($return))
		$return = 'uploaded';
	else
		$return = substr($return, 0, strlen($return) - 8);
	exit($return);
}

/* ASK AFTER UPLOAD */

elseif(isset($_POST['ask']) && isset($_POST['files']))
{
	$choice = $_POST['ask'];
	$files_tmp = explode(',', $_POST['files']);
	$nb_files_tmp = sizeof($files_tmp);
	$files = array();
	$j = 0;
	for($i = 0; $i < $nb_files_tmp; $i++)
	{
		if($i % 2 === 0)
			$files[$j]['old'] = $files_tmp[$i];
		else
		{
			$files[$j]['ask'] = $files_tmp[$i];
			$j++;
		}
	}

	$return = '';
	$nb_files = sizeof($files);
	if($choice === '0')
	{
		for($i = 0; $i < $nb_files; $i++)
		{
			if(@!unlink($current . $files[$i]['old']))
				$return .= "\n" . htmlentities($files[$i]['old'], ENT_QUOTES) . '</b> cannot be deleted<b><br><br>';
			elseif(@!rename($current . $files[$i]['ask'], $current . $files[$i]['old']))
				$return .= "\n" . htmlentities($files[$i]['ask'], ENT_QUOTES) . '</b> cannot be renammed<b><br><br>';
		}
	}
	elseif($choice === '1')
	{
		for($i = 0; $i < $nb_files; $i++)
		{
			$name = $files[$i]['old'];
			$name = split_filename($files[$i]['old']);
			$extension = $name['dot_extension'];
			$name = $name['name'];
			$j = 1;
			while(file_exists($current . $name . '.bak' . add_zeros($j) . $extension))
				$j++;
			$name .= '.bak' . add_zeros($j) . $extension;
			if(@!rename($current . $files[$i]['old'], $current . $name))
				$return .= "\n" . htmlentities($files[$i]['old'], ENT_QUOTES) . '</b> cannot be renammed<b><br><br>';
			elseif(@!rename($current . $files[$i]['ask'], $current . $files[$i]['old']))
				$return .= "\n" . htmlentities($files[$i]['ask'], ENT_QUOTES) . '</b> cannot be renammed<b><br><br>';
		}
	}
	elseif($choice === '2')
	{
		for($i = 0; $i < $nb_files; $i++)
		{
			$name = split_filename($files[$i]['old']);
			$extension = $name['dot_extension'];
			$name = $name['name'];
			$j = 1;
			while(file_exists($current . $name . " ($j)" . $extension))
				$j++;
			$name .= " ($j)" . $extension;
			if(@!rename($current . $files[$i]['ask'], $current . $name))
				$return .= "\n" . htmlentities($files[$i]['ask'], ENT_QUOTES) . '</b> cannot be renammed<b><br><br>';
		}
	}
	elseif($choice === '3')
	{
		for($i = 0; $i < $nb_files; $i++)
		{
			if(@!unlink($current . $files[$i]['ask']))
				$return .= "\n" . htmlentities($files[$i]['ask'], ENT_QUOTES) . '</b> cannot be deleted<b><br><br>';
		}
	}
	else
		$return = 'Unknown choice<br><br>';
	if(empty($return))
		$return = 'uploaded';
	else
		$return = substr($return, 0, strlen($return) - 8);
	exit($return);
}

/* NEW FILE OR FOLDER */

elseif(isset($_POST['new']) && isset($_POST['name']))
{
	if(strpos($_POST['name'], "'") === false)
	{
		$new_name = $_POST['name'];

		if(@file_exists($current . $new_name))
			exit('File or directory already exists');
		else
		{
			if($_POST['new'] === 'file')
			{
				if(@file_put_contents($current . $new_name, '') !== false)
					exit('created');
				else
					exit('File not created');
			}
			else
			{
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

elseif(isset($_POST['rename']) && isset($_POST['name']))
{
	if(@rename($current . urldecode($_POST['rename']), $current . $_POST['name']))
		exit('renamed');
	else
		exit('Not renamed');
}

/* DUPLICATE ELEMENT */

elseif(isset($_POST['duplicate']) && isset($_POST['path']))
{
	$name = urldecode($_POST['duplicate']);
	$path = $_POST['path'];

	if(@file_exists($current . $name))
	{
		if(@copy_or_move($current . $name, $path, false, 1, 1, 2))
			exit('duplicated');
		else
			exit('File or directory not duplicated');
	}
	else
		exit('File or directory not found');
}

/* COPY ELEMENT */

elseif(isset($_POST['copy']) && isset($_POST['path']))
{
	$name = urldecode($_POST['copy']);
	$path = $_POST['path'];

	if(@file_exists($current . $name))
	{
		if(@copy_or_move($current . $name, $path, false, 1, 1, 1))
			exit('copied');
		else
			exit('File or directory not copied');
	}
	else
		exit('File or directory not found');
}

/* MOVE ELEMENT */

elseif(isset($_POST['move']) && isset($_POST['path']))
{
	$name = urldecode($_POST['move']);
	$path = $_POST['path'];

	if(@file_exists($current . $name))
	{
		if(@copy_or_move($current . $name, $path, true, 1, 1, 1))
			exit('moved');
		else
			exit('File or directory not moved');
	}
	else
		exit('File or directory not found');
}

/* DELETE ELEMENT */

elseif(isset($_POST['delete']))
{
	$name = urldecode($_POST['delete']);

	if(@is_file($current . $name) || @is_link($current . $name))
	{
		if(@unlink($current . $name))
			exit('deleted');
		else
			exit('File not deleted');
	}
	elseif(@is_dir($current . $name))
	{
		if(@rm_full_dir($current . $name))
			exit('deleted');
		else
			exit('Directory not deleted');
	}
	else
		exit('File not found');
}

/* EDIT ELEMENT */

elseif(isset($_POST['read_file']))
{
	$name = urldecode($_POST['read_file']);

	if(@is_file($current . $name) || @is_link($current . $name))
		exit('' . file_get_contents($current . $name));
	else
		exit('[file_edit_not_found]');
}

elseif(isset($_POST['edit_file']) && isset($_POST['name']))
{
	$name = urldecode($_POST['name']);

	if(@is_file($current . $name) || @is_link($current . $name))
	{
		if(@file_put_contents($current . $name, $_POST['edit_file']))
			exit('edited');
		else
			exit('File not edited');
	}
	else
		exit('File not found');
}

/* CHANGE CHMODS */

elseif(isset($_POST['get_chmods']))
{
	$name = urldecode($_POST['get_chmods']);

	if(@file_exists($current . $name))
	{
		$fileperms = @find_chmods($current . $name);
		if($fileperms !== false)
			exit("[chmods=$fileperms]");
		else
			exit('Chmods not found');
	}
	else
		exit('File not found');
}

elseif(isset($_POST['set_chmods']) && isset($_POST['name']))
{
	$name = urldecode($_POST['name']);

	if(@file_exists($current . $name))
	{
		if(@chmod($current . $name, octdec(intval($_POST['set_chmods']))))
			exit('chmoded');
		else
			exit('Chmods not updated');
	}
	else
		exit('File not found');
}

/* UPDATE */

elseif(isset($_POST['update']))
{
	$script_name = split_filename(server_infos()['script']);
	$script_name = $script_name['name'] . $script_name['dot_extension'];

	$i = 1;
	while(@file_exists($script_name . '.update' . $i))
		$i++;
	$update_name = $script_name . '.update' . $i;

	$i = 1;
	while(@file_exists("update_temp$i.php"))
		$i++;
	$temp_name = "update_temp$i.php";

	if(@file_put_contents($update_name, @file_get_contents(urldecode($_POST['update']))))
	{
		if(@file_put_contents($temp_name, '<?php
unlink($_GET[\'file\']);
rename($_GET[\'update\'], $_GET[\'file\']);
unlink($_GET[\'tmp\']);
header(\'Location: \' . $_GET[\'file\']);
'))
		{
			exit("[update=$script_name,$update_name,$temp_name]");
		}
		else
			exit('Creation of temporary file failed');
	}
	else
		exit('Download failed');
}

else
	exit('Unknown action');
