<?php
function rm_full_dir($directory)
{
	$directory = no_end_slash($directory);
	if(empty($directory) || $directory === '.')
	{
		$directory = '.';
		$path = '';
	}
	else
		$path = $directory . '/';

	if(is_dir($directory) && !is_link($directory))
	{
		if($handle = opendir($directory))
		{
			while(false !== ($entry = readdir($handle)))
			{
				if($entry != '.' && $entry != '..')
				{
					if(is_file($path . $entry) || is_link($path . $entry))
					{
						if(!unlink($path . $entry))
							return false;
					}
					elseif(is_dir($path . $entry))
					{
						if(!rm_full_dir($path . $entry))
							return false;
					}
					else
						return false;
				}
			}
			closedir($handle);
			if(rmdir($directory))
				return true;
			else
				return false;
		}
		else
			return false;
	}
	else
		return false;
}

/*
$source :
	CANNOT BE '.' OR EMPTY => USE '../current' INSTEAD
$dest_file_exists :
	IF FILE EXISTS WHERE THE FILE MUST BE COPIED :
		0 : RETURN FALSE
		1 : RENAME OLD FILE
		2 : RENAME NEW FILE
		3 : DELETE EXISTING FILE
$dest_dir_exists :
	IF DIR EXISTS WHERE THE FILE MUST BE COPIED :
		0 : RETURN FALSE
		1 : RENAME OLD DIR
		2 : RENAME NEW FILE
		3 : DELETE EXISTING DIR
$fusion_dirs :
	IF FILE OR DIR EXISTS WHERE THE DIR MUST BE COPIED :
		0 : RETURN FALSE
		1 : FUSION DIRS IF DIR, RENAME NEW DIR IF EXISTING IS FILE
		2 : RENAME NEW DIR IF EXISTING IS DIR OR FILE
		3 : DELETE EXISTING DIR OR FILE (ONLY FOR COPYING DIRS)
*/
function copy_or_move($source, $dest, $move = false, $dest_file_exists = 1, $dest_dir_exists = 1, $fusion_dirs = 1)
{
	$dest = no_end_slash($dest);
	if(empty($dest) || $dest === '.')
		$dest = '';
	else
		$dest .= '/';

	if(is_file($source) || is_link($source))
	{
		$source = no_end_slash($source);
		$source_infos = split_filename($source);
		$source_path = $source_infos['path'];
		$source_name = $source_infos['name'];
		$extension = $source_infos['dot_extension'];
		$dest_name = $source_name;

		if($source_path === $dest && ($move === true || $dest_file_exists !== 2))
			return false;
		elseif(file_exists($dest . $source_name . $extension))
		{
			if(is_file($dest . $source_name . $extension) || is_link($dest . $source_name . $extension))
				$is_file = true;
			elseif(is_dir($dest . $source_name . $extension))
				$is_file = false;
			else
				return false;

			if(($is_file === false && $dest_dir_exists === 0) || ($is_file === true && $dest_file_exists === 0))
				return false;
			elseif($is_file === true && $dest_file_exists === 3)
			{
				if(!unlink($dest . $source_name . $extension))
					return false;
			}
			elseif($is_file === false && $dest_dir_exists === 3)
			{
				if(!rm_full_dir($dest . $source_name . $extension))
					return false;
			}
			elseif(($is_file === false && $dest_dir_exists === 1) || ($is_file === true && $dest_file_exists === 1))
			{
				$i = 1;
				while(file_exists($dest . $source_name . $extension . '.bak' . $i))
					$i++;
				if(!rename($dest . $source_name . $extension, $dest . $source_name . $extension . '.bak' . $i))
					return false;
			}
			else
			{
				$i = 1;
				while(file_exists($dest . $source_name . " ($i)" . $extension))
					$i++;
				$dest_name .= " ($i)";
			}
		}

		if($move === true && rename($source, $dest . $dest_name . $extension))
			return true;
		elseif($move === false && copy($source, $dest . $dest_name . $extension))
			return true;
		else
			return false;
	}
	elseif(!empty($source) && $source !== '.' && is_dir($source))
	{
		$source = no_end_slash($source);
		$source_infos = split_dirname($source);
		$source_path = $source_infos['path'];
		$source_name = $source_infos['name'];
		$dest_name = $source_name;
		$create_dir = true;

		if($source_path === $dest && ($move === true || $fusion_dirs !== 2))
			return false;
		elseif(file_exists($dest . $source_name))
		{
			if($fusion_dirs === 0)
				return false;
			else
			{
				if(is_file($dest . $source_name) || is_link($dest . $source_name))
					$is_file = true;
				elseif(is_dir($dest . $source_name))
					$is_file = false;
				else
					return false;

				if($fusion_dirs === 3)
				{
					if($is_file === true && !unlink($dest . $source_name))
						return false;
					elseif($is_file === false && !rm_full_dir($dest . $source_name))
						return false;
				}
				elseif($fusion_dirs === 1 && $is_file === false)
					$create_dir = false;
				else
				{
					$i = 1;
					while(file_exists($dest . $dest_name . " ($i)"))
						$i++;
					$dest_name .= " ($i)";
				}
			}
		}

		if($move === true && $create_dir === true)
		{
			if(rename($source, $dest . $dest_name))
				return true;
			return false;
		}
		else
		{
			if($handle = opendir($source_path . $source_name))
			{
				if($create_dir === false || ($create_dir === true && mkdir($dest . $dest_name)))
				{
					while(false !== ($entry = readdir($handle)))
					{
						if($entry != '.' && $entry != '..')
						{
							if(!copy_or_move($source_path . $source_name . '/' . $entry, $dest . $dest_name, $move, $dest_file_exists, $dest_dir_exists, $fusion_dirs))
								return false;
						}
					}
					closedir($handle);
					if($move === true && !rmdir($source_path . $source_name))
						return false;
					else
						return true;
				}
				else
					return false;
			}
			else
				return false;
		}
	}
	else
		return false;
}

function find_chmods($filename)
{
	if($fileperms = fileperms($filename))
		return substr(sprintf('%o', $fileperms), -4);
	else
		return false;
}
