<?php
function get_user_ip()
{
    if(isset($_SERVER['HTTP_CF_CONNECTING_IP']))
    {
        $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
        $_SERVER['HTTP_CLIENT_IP'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
    }

    $client = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    if(isset($_SERVER['REMOTE_ADDR']))
        $remote = $_SERVER['REMOTE_ADDR'];
    else
        $remote = '';

    if(filter_var($client, FILTER_VALIDATE_IP))
        return $client;
    elseif(filter_var($forward, FILTER_VALIDATE_IP))
        return $forward;
    else
        return $remote;
}

function sp_crypt($str)
{
    $remote_addr = get_user_ip();
    if(isset($_SERVER['HTTP_USER_AGENT']))
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
    else
        $user_agent = '';

    if(empty($remote_addr) && empty($user_agent))
        exit('Error : <b>Unable to get user information</b>');

    return sha1($remote_addr . md5($str) . $user_agent);
}

function gencode($nb)
{
    $cars = 'azertyuiopqsdfghjklmwxcvbn0123456789';
    $mt_max = strlen($cars) - 1;
    $return = '';
    for($i = 0; $i < $nb; $i++)
        $return .= $cars[mt_rand(0, $mt_max)];
    return $return;
}

function parse_size($size)
{
    $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
    $size = preg_replace('/[^0-9\.]/', '', $size);
    if($unit)
        return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
    else
        return round($size);
}

function split_filename($filename)
{
    if(strpos($filename, '/') === false)
        $path = '';
    else
    {
        $path_arr = explode('/', $filename);
        $nb_path = sizeof($path_arr);
        $path = '';
        for($i = 0; $i < $nb_path - 1; $i++)
            $path .= $path_arr[$i] . '/';
        $filename = $path_arr[$nb_path - 1];
    }

    if(strpos($filename, '.') === false || $filename[strlen($filename) - 1] === '.')
    {
        $dot = '';
        $extension = '';
    }
    else
    {
        $name_arr = explode('.', $filename);
        $nb_name = sizeof($name_arr);
        $filename = '';
        for($i = 0; $i < $nb_name - 1; $i++)
        {
            $filename .= $name_arr[$i];
            if($i < $nb_name - 2)
                $filename .= '.';
        }
        $extension = $name_arr[$nb_name - 1];
        $dot = '.' . $extension;
    }

    return array('path' => $path, 'name' => $filename, 'extension' => $extension, 'dot_extension' => $dot);
}
