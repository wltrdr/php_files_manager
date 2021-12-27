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

function copy_move_file($source, $dest, $move = false)
{
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
            $name_src_tmp = $name_dst_tmp = gencode(32);
            $new_name = $source_name;
            $i = 1;
            while(file_exists($dest . $new_name . " ($i)" . $source_extension))
                $i++;
            $new_name .= " ($i)";

            if($source_path === $dest)
            {
                if($move === true)
                    return false;
                $name_src_tmp = $source_name;
                $name_dst_tmp = $new_name;
            }
            else
            {
                if(rename($source, $source_path . $name_src_tmp . $source_extension))
                    $dest_exists = true;
                else
                    return false;
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
    else
        return false;
}

function copy_move_dir($source, $dest, $move = false) // SOURCE CANNOT BE '.' OR EMPTY, USE '../current' INSTEAD
{
    $source = no_end_slash($source);
    if(!empty($source) && $source !== '.' && is_dir($source))
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
        if(file_exists($dest . $source_name . '/'))
        {
            $i = 1;
            while(file_exists($dest . $new_name . " ($i)/"))
                $i++;
            $new_name .= " ($i)";

            $dest_exists = true;
        }

        if($handle = opendir($source_path . $source_name . '/'))
        {
            if(mkdir($dest . $new_name . '/'))
            {
                while(false !== ($entry = readdir($handle)))
                {
                    if($entry != '.' && $entry != '..')
                    {
                        if(is_dir($source_path . $source_name . '/' . $entry))
                        {
                            if(!copy_move_dir($source_path . $source_name . '/' . $entry, $dest . $new_name . '/', $move))
                                return false;
                        }
                        elseif(is_file($source_path . $source_name . '/' . $entry))
                        {
                            if(!copy($source_path . $source_name . '/' . $entry, $dest . $new_name . '/' . $entry))
                                return false;
                        }
                        else
                            return false;
                    }
                }
                closedir($handle);
                if($move === true && !rm_full_dir($source_path . $source_name . '/'))
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
