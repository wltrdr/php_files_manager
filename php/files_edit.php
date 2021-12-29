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

	if(is_dir($directory))
	{
		if($handle = opendir($directory))
		{
			while(false !== ($entry = readdir($handle)))
			{
				if($entry != '.' && $entry != '..')
				{
					if(is_dir($path . $entry))
					{
						if(!rm_full_dir($path . $entry))
							return false;
					}
					elseif(is_file($path . $entry))
					{
						if(!unlink($path . $entry))
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
	(IF TYPE = DIR, ONLY IF $fusion_dirs == 1)
	IF FILE EXISTS WHERE THE FILE MUST BE COPIED :
		0 : RETURN FALSE
		1 : RENAME NEW FILE
		2 : DELETE EXISTING FILE
$dest_dir_exists :
	(IF TYPE = DIR, ONLY IF $fusion_dirs == 1)
	IF DIR EXISTS WHERE THE FILE MUST BE COPIED :
		0 : RETURN FALSE
		1 : RENAME NEW FILE
		2 : DELETE EXISTING DIR
$fusion_dirs :
	IF FILE OR DIR EXISTS WHERE THE DIR MUST BE COPIED :
		0 : RETURN FALSE
		1 : FUSION DIRS IF DIR, RENAME NEW DIR IF EXISTING IS FILE
		2 : RENAME NEW DIR IF EXISTING IS DIR OR FILE
		3 : DELETE EXISTING DIR OR FILE (ONLY FOR COPYING DIRS)
*/
function copy_or_move($source, $dest, $move = false, $dest_file_exists = 0, $dest_dir_exists = 0, $fusion_dirs = 0)
{
	$source = no_end_slash($source);
	if(is_file($source))
	{
		$source_infos = split_filename($source);
		$source_path = $source_infos['path'];
		$source_name = $source_infos['name'];
		$source_extension = $source_infos['dot_extension'];

		$dest = no_end_slash($dest);
		if(empty($dest) || $dest === '.')
			$dest = '';
		else
			$dest .= '/';
		$dest_exists = false;

		$name_src_tmp = $name_dst_tmp = $source_name;

		if(file_exists($dest . $source_name . $source_extension))
		{
			if(is_dir($dest . $source_name . $source_extension))
				$is_file = false;
			elseif(is_file($dest . $source_name . $source_extension))
				$is_file = true;
			else
				return false;

			if(($is_file === false && $dest_dir_exists === 0) || ($is_file === true && $dest_file_exists === 0))
				return false;
			elseif($is_file === false && $dest_dir_exists === 2)
			{
				if(!rm_full_dir($dest . $source_name . $source_extension))
					return false;
			}
			elseif($is_file === true && $dest_file_exists === 2)
			{
				if(!unlink($dest . $source_name . $source_extension))
					return false;
			}
			else
			{
				$new_name = $source_name;
				$i = 1;
				while(file_exists($dest . $new_name . " ($i)" . $source_extension))
					$i++;
				$new_name .= " ($i)";
	
				if($source_path === $dest && $move === true)
					return false;
				elseif($source_path === $dest)
					$name_dst_tmp = $new_name;
				else
				{
					$dest_exists = true;
					$name_src_tmp = $name_dst_tmp = gencode(32);
	
					if(!rename($source, $source_path . $name_src_tmp . $source_extension))
						return false;
				}
			}
		}

		if(copy($source_path . $name_src_tmp . $source_extension, $dest . $name_dst_tmp . $source_extension))
		{
			if($dest_exists === true && !rename($dest . $name_src_tmp . $source_extension, $dest . $new_name . $source_extension))
				return false;

			if($move === true && !unlink($source_path . $name_src_tmp . $source_extension))
				return false;
			elseif($move === false && $dest_exists === true && !rename($source_path . $name_src_tmp . $source_extension, $source))
				return false;
			else
				return true;
		}
		else
			return false;
	}
	elseif(!empty($source) && $source !== '.' && is_dir($source))
	{
		$source_infos = split_dirname($source);
		$source_path = $source_infos['path'];
		$source_name = $source_infos['name'];

		$dest = no_end_slash($dest);
		if(empty($dest) || $dest === '.')
			$dest = '';
		else
			$dest .= '/';

		$new_name = $source_name;
		$create_dir = true;

		if(file_exists($dest . $source_name))
		{
			if($fusion_dirs === 0)
				return false;
			else
			{
				if(is_dir($dest . $source_name))
					$is_file = false;
				elseif(is_file($dest . $source_name))
					$is_file = true;
				else
					return false;

				if($fusion_dirs === 3 && $is_file === false)
				{
					if(!rm_full_dir($dest . $source_name))
						return false;
				}
				elseif($fusion_dirs === 3 && $is_file === true)
				{
					if(!unlink($dest . $source_name))
						return false;
				}
				elseif($fusion_dirs === 1 && $is_file === false)
					$create_dir = false;
				else
				{
					$i = 1;
					while(file_exists($dest . $new_name . " ($i)"))
						$i++;
					$new_name .= " ($i)";
				}
			}
		}

		if($handle = opendir($source_path . $source_name))
		{
			if($create_dir === false || ($create_dir === true && mkdir($dest . $new_name)))
			{
				while(false !== ($entry = readdir($handle)))
				{
					if($entry != '.' && $entry != '..')
					{
						if(!copy_or_move($source_path . $source_name . '/' . $entry, $dest . $new_name, $move, $dest_file_exists, $dest_dir_exists, $fusion_dirs))
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
