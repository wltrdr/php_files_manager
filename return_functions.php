<?php
function css_extension($file)
{
    if(strpos($file, '.') !== false)
    {
        $extension = explode('.', $file);
        $extension = $extension[sizeof($extension) - 1];
        if($extension === 'css' || $extension === 'json' || $extension === 'xml') return 'css';
        elseif($extension === 'doc' || $extension === 'docx' || $extension === 'txt' || $extension === 'rtf' || $extension === 'odt' || $extension === 'ini') return 'docx';
        elseif($extension === 'html' || $extension === 'xhtml' || $extension === 'htm') return 'html';
        elseif($extension === 'js' || $extension === 'java' || $extension === 'py' || $extension === 'c' || $extension === 'bat' || $extension === 'bash' || $extension === 'sh') return 'java';
        elseif($extension === 'jpg' || $extension === 'jpeg' || $extension === 'png' || $extension === 'gif' || $extension === 'webp' || $extension === 'bmp' || $extension === 'psd' || $extension === 'tiff') return 'jpg';
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

function size_of_file($size)
{
    if($size < 1024)
        return $size . ' o';
    else
    {
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

function array_sort($array, $on, $order = 'SORT_ASC')
{
    $new_array = array();
    $sortable_array = array();
    if(count($array) > 0)
    {
        foreach($array as $k => $v)
        {
            if (is_array($v))
            {
                foreach($v as $k2 => $v2)
                {
                    if ($k2 == $on)
                        $sortable_array[$k] = $v2;
                }
            }
            else
                $sortable_array[$k] = $v;
        }
        if($order === 'SORT_ASC')
            asort($sortable_array);
        else
            arsort($sortable_array);
        foreach($sortable_array as $k => $v)
            $new_array[$k] = $array[$k];
    }
    return $new_array;
}

function server_infos()
{
    $web_http = 'http';
    if(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        $web_http .= 's';
    elseif(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
        $web_http .= 's';
    elseif(isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] === 'https')
        $web_http .= 's';
    $web_http .= '://';

    if(isset($_SERVER['HTTP_HOST']))
        $web_root = $_SERVER['HTTP_HOST'];
    elseif(isset($_SERVER['SERVER_NAME']))
        $web_root = $_SERVER['SERVER_NAME'];
    else
        $web_root = 'domain-not-found';

    if(isset($_SERVER['SCRIPT_NAME']))
        $script_name = $_SERVER['SCRIPT_NAME'];
    elseif(isset($_SERVER['PHP_SELF']))
        $script_name = $_SERVER['PHP_SELF'];
    else
        $script_name = false;

    if(isset($_SERVER['DOCUMENT_ROOT']))
    {
        $server_root = $_SERVER['DOCUMENT_ROOT'];
        if($script_name === false)
        {
            if(isset($_SERVER['SCRIPT_FILENAME']))
            {
                $script_filename = $_SERVER['SCRIPT_FILENAME'];
                if(strpos($script_filename, $server_root) === 0)
                    $script_name = substr($script_filename, strlen($server_root));
                else
                    return false;
            }
            else
                return false;
        }
    }
    elseif(isset($_SERVER['SCRIPT_FILENAME']))
    {
        $script_filename = $_SERVER['SCRIPT_FILENAME'];
        if($script_name !== false)
        {
            $lng_script_filename = strlen($script_filename);
            $lng_script_name = strlen($script_name);
            if(strpos($script_filename, $script_name) === $lng_script_filename - $lng_script_name)
                $server_root = substr($script_filename, 0, $lng_script_filename - $lng_script_name);
            else
                return false;
        }
        else
            return false;
    }
    else
        return false;

    $return['web_root'] = $web_root;
    $return['web_http'] = $web_http;
    $return['server_root'] = $server_root;
    $return['script'] = $script_name;
    return $return;
}

function path_parents($nb)
{
    if($nb === 0)
        return '.';
    else
    {
        $return = '';
        for($i = 0; $i < $nb; $i++)
            $return .= '../';
        return $return;
    }
}
