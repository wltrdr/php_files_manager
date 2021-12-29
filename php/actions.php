<?php
/* UPLOAD */

if(isset($_FILES['upload']))
{
	$return = '';
	$nb_files = count($_FILES['upload']['name']);
	for($i = 0; $i < $nb_files; $i++)
	{
		$name = $_FILES['upload']['name'][$i];
		$name_html = htmlentities($name, ENT_QUOTES);

		if($_FILES['upload']['error'][$i] === 0)
		{
			if(@file_exists($current . $name))
				$return .= "\n" . $name_html . '</b> already exists<b><br><br>';
			elseif(@!move_uploaded_file($_FILES['upload']['tmp_name'][$i], $current . $name))
				$return .= "\n" . $name_html . '</b> cannot be uploaded (#1)<b><br><br>';
		}
		else
			$return .= "\n" . $name_html . '</b> cannot be uploaded (#2)<b><br><br>';
	}
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

	if(@is_file($current . $name))
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

	if(@is_file($current . $name))
		exit('' . file_get_contents($current . $name));
	else
		exit('[file_edit_not_found]');
}

elseif(isset($_POST['edit_file']) && isset($_POST['name']))
{
	$name = urldecode($_POST['name']);

	if(@is_file($current . $name))
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

else
	exit('Unknown action');
