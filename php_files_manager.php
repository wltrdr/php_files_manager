<?php
session_start();

$password = 'admin';

/* SECURITY */

function get_user_ip()
{
    if(isset($_SERVER["HTTP_CF_CONNECTING_IP"]))
    {
        $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
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

function file_extension($filename)
{
    if(strpos($filename, '.') === false)
        return array($filename, '');
    else
    {
        $filename = explode('.', $filename);
        $nb_arr = sizeof($filename);
        if($nb_arr > 2)
        {
            $name = '';
            for($i = 0; $i < $nb_arr - 1; $i++)
            {
                $name .= $filename[$i];
                if($i < $nb_arr - 2)
                    $name .= '.';
            }
            return array($name, $filename[$nb_arr - 1]);
        }
        else
            return $filename;
    }
}

$password = sp_crypt($password);

if(!isset($_SESSION['token']))
    $_SESSION['token'] = gencode(32);

/* JAVASCRIPT */

if(isset($_GET['js']) && isset ($_GET['init']))
{
	header('Content-Type: application/javascript');
    exit(file_get_contents('init.js'));
}
elseif(isset($_GET['js']) && isset ($_GET['functions']))
{
	header('Content-Type: application/javascript');
    exit(file_get_contents('functions.js'));
}
elseif(isset($_GET['js']) && isset ($_GET['elements']))
{
	header('Content-Type: application/javascript');
    exit(file_get_contents('elements.js'));
}
elseif(isset($_GET['js']) && isset ($_GET['events']))
{
	header('Content-Type: application/javascript');
    exit(file_get_contents('events.js'));
}

/* CSS */

elseif(isset($_GET['css']) && isset ($_GET['style']))
{
	header('Content-Type: text/css');
    exit(file_get_contents('style.css'));
}
elseif(isset($_GET['css']) && isset ($_GET['images']))
{
	header('Content-Type: text/css');
    exit(file_get_contents('images.css'));
}

/* DOWNLOAD FILE */

elseif(isset($_GET['download']))
{
    if((isset($_SESSION['pfm']) && $_SESSION['pfm'] === $password))
    {
        if(isset($_GET['token']) && $_GET['token'] === $_SESSION['token'])
        {
            if(isset($_GET['dir']))
            {
                $dir = urldecode($_GET['dir']);
                if($dir === '.')
                    $dir = '';
                $file = $dir . urldecode($_GET['download']);
                if(is_file($file))
                {
                    clearstatcache();
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/octet-stream');
                    header("Cache-Control: no-cache, must-revalidate");
                    header("Expires: 0");
                    header('Content-Disposition: attachment; filename="'.basename($file).'"');
                    header('Content-Length: ' . filesize($file));
                    header('Pragma: public');
                    flush();
                    readfile($file, true);
                    die();
                }
                else
                    exit('Error : <b>File "' . $file . '" not found</b>');
            }
            else
                exit('Error : <b>Undefined directory</b>');
        }
        else
            exit('Error : <b>Bad token</b>');
    }
    else
        exit('Error : <b>Session expired</b>');
}

/* LOGOUT */

elseif(isset($_POST['logout']))
{
	header('Content-Type: text/plain');
	unset($_SESSION['pfm']);
    exit('bye');
}
elseif(isset($_POST) && !empty($_POST))
{
	header('Content-Type: text/plain');
    if((isset($_SESSION['pfm']) && $_SESSION['pfm'] === $password) || (isset($_POST['pwd']) && sp_crypt($_POST['pwd']) === $password))
    {
        /* SECURITY */

        if(!isset($_SESSION['pfm']) || $_SESSION['pfm'] !== $password)
            $_SESSION['pfm'] = $password;

        /* LOCATE CURRENT DIRECTORY */

        $current = '.';
        if(isset($_POST['dir']) && !empty($_POST['dir']) && $_POST['dir'] !== '.')
            $current = urldecode($_POST['dir']);

        /* ACTIONS */

        if(isset($_POST['get_upload_sizes']))
        {
            function parse_size($size)
            {
                $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
                $size = preg_replace('/[^0-9\.]/', '', $size);
                if($unit)
                    return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
                else
                    return round($size);
            }
            
            exit('[max_upload_sizes=' . parse_size(ini_get('upload_max_filesize')) . '|' . parse_size(ini_get('post_max_size')) . ']');
        }
        elseif(isset($_POST['token']))
        {
            function move_file($source, $dest)
            {
                if(is_file($source))
                {
                    if(copy($source, $dest))
                    {
                        if(unlink($source))
                            return true;
                        else
                            return false;
                    }
                    else return false;
                }
                else
                    return false;
            }

            function rm_full_dir($directory)
            {
                $long = strlen($directory);
                if($long > 0 && $directory[$long - 1] === '/')
                    $directory = substr($directory, 0, $long - 1);
            
                if($handle = opendir($directory . '/'))
                {
                    while(false !== ($entry = readdir($handle)))
                    {
                        if($entry != '.' && $entry != '..')
                        {
                            if(is_dir($directory . '/' . $entry))
                            {
                                if(!rm_full_dir($directory . '/' . $entry))
                                    return false;
                            }
                            elseif(is_file($directory . '/' . $entry))
                            {
                                if(!unlink($directory . '/' . $entry))
                                    return false;
                            }
                            else
                                return false;
                        }
                    }
                    closedir($handle);
                    if(rmdir($directory . '/'))
                        return true;
                    else
                        return false;
                }
                else
                    return false;
            }

            function copy_move_dir($source, $dest, $move = false)
            {
                $lng_source = strlen($source);
                if($lng_source > 0 && $source[$lng_source - 1] === '/')
                    $source = substr($source, 0, $lng_source - 1);

                $lng_dest = strlen($dest);
                if($lng_dest > 0 && $dest[$lng_dest - 1] === '/')
                    $dest = substr($dest, 0, $lng_dest - 1);

                if($handle = opendir($source . '/'))
                {
                    if(mkdir($dest))
                    {
                        while(false !== ($entry = readdir($handle)))
                        {
                            if($entry != '.' && $entry != '..')
                            {
                                if(is_dir($source . '/' . $entry))
                                {
                                    if(!copy_move_dir($source . '/' . $entry, $dest . '/' . $entry, $move))
                                        return false;
                                }
                                elseif(is_file($source . '/' . $entry))
                                {
                                    if(copy($source . '/' . $entry, $dest . '/' . $entry))
                                    {
                                        if($move === true)
                                        {
                                            if(!unlink($source . '/' . $entry))
                                                return false;
                                        }
                                    }
                                    else
                                        return false;
                                }
                                else
                                    return false;
                            }
                        }
                        closedir($handle);
                        if($move === true)
                        {
                            if(rmdir($source . '/'))
                                return true;
                            else
                                return false;
                        }
                        else
                            return true;
                    }
                    else
                        return false;
                }
                else
                    return false;
            }

            if($_POST['token'] === $_SESSION['token'])
            {
                if($current === '.')
                    $current = '';

                /* UPLOAD */

                if(isset($_FILES['upload']))
                {
                    $return = '';
                    $nb_files = count($_FILES['upload']['name']);
                    for($i = 0; $i < $nb_files; $i++)
                    {
                        $name = $_FILES['upload']['name'][$i];
                        if($_FILES['upload']['error'][$i] === 0)
                        {
                            if(@file_exists($current . $name))
                                $return .= "\n" . $name . '</b> already exists<b><br><br>';
                            elseif(@!move_uploaded_file($_FILES['upload']['tmp_name'][$i], $current . $name))
                                $return .= "\n" . $name . '</b> cannot be uploaded (#1)<b><br><br>';
                        }
                        else
                            $return .= "\n" . $name . '</b> cannot be uploaded (#2)<b><br><br>';
                    }
                    if(empty($return))
                        $return = 'uploaded';
                    else
                        $return = substr($return, 0, strlen($return) - 8);
                    exit($return);
                }

                /* NEW FILE OR FOLDER */

                elseif(isset($_POST['new']))
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

                elseif(isset($_POST['rename']))
                {
                    if(@rename($current . urldecode($_POST['rename']), $current . $_POST['name']))
                        exit('renamed');
                    else
                        exit('Not renamed');
                }

                /* DUPLICATE ELEMENT */


                elseif(isset($_POST['duplicate']))
                {
                    $name = urldecode($_POST['duplicate']);

                    if(@is_file($current . $name))
                    {
                        $new_name = file_extension($name);
                        $extension = $new_name[1];
                        $new_name = $new_name[0];

                        $i = 1;
                        while(@file_exists($current . $new_name . " ($i)." . $extension))
                            $i++;

                        $new_name .= " ($i)." . $extension;

                        if(@copy($current . $name, $current . $new_name))
                            exit('duplicated');
                        else
                            exit('File not duplicated');
                    }
                    elseif(@is_dir($current . $name))
                    {
                        $i = 1;
                        while(@file_exists($current . $name . " ($i)"))
                            $i++;

                        $new_name = $name . " ($i)";

                        if(copy_move_dir($current . $name, $current . $new_name))
                            exit('duplicated');
                        else
                            exit('Directory not duplicated');
                    }
                    else
                        exit('File not found');
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

                else
                    exit('Unknown action');
            }
            else
                exit('Refresh site');
        }
        else
        {
            /* RETURN DIR INFORMATIONS */

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
    
            /* PATH */

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

            $server_infos = server_infos();
            if(!$server_infos)
                exit('[fatal=Unable to get server information]');

            $script_path = $server_infos['server_root'] . $server_infos['script'];
    
            $win_fs = true;
    
            if(strpos($script_path, '/') === false)
            {
                $win_fs = false;
                $server_dirs[0]['name'] = '/';
                $server_dirs[0]['path'] = '.';
                $nb_server_dirs = 1;
            }
            else
            {
                if($script_path[0] === '/')
                    $win_fs = false;
    
                $server_dirs = explode('/', $script_path);
                $nb_server_dirs = sizeof($server_dirs) - 1;
                unset($server_dirs[$nb_server_dirs]);
    
                for($i = 0; $i < $nb_server_dirs; $i++)
                {
                    if($i === 0 && empty($server_dirs[0]))
                        $tmp = '/';
                    else
                        $tmp = $server_dirs[$i];
    
                    $server_dirs[$i] = null;
                    $server_dirs[$i]['name'] = $tmp;
                    $server_dirs[$i]['path'] = path_parents($nb_server_dirs - ($i + 1));
                }
            }
    
            $cur_rmvs = 0;
            $cur_adds = 0;
            
            $adds_dirs = $current;
            while(strpos($adds_dirs, '../') === 0)
            {
                $cur_rmvs++;
                $adds_dirs = substr($adds_dirs, 3);
            }
    
            if(!empty($adds_dirs) && $adds_dirs !== '.')
            {
                $adds_dirs = substr($adds_dirs, 0, strlen($adds_dirs) - 1);
                if(strpos($adds_dirs, '/') !== false)
                {
                    $adds_dirs = explode('/', $adds_dirs);
                    $cur_adds = sizeof($adds_dirs);
                }
                else
                {
                    $adds_dirs = array($adds_dirs);
                    $cur_adds = 1;
                }
            }
            else
                $adds_dirs = array();
    
            $nb_dirs = 0;
            $cur_tmp = '';
    
            for($i = 0; $i < $nb_server_dirs - $cur_rmvs; $i++)
            {
                $dirs[$i]['name'] = $server_dirs[$i]['name'];
                $dirs[$i]['path'] = $cur_tmp = $server_dirs[$i]['path'];
                $nb_dirs++;
            }
    
            if($cur_tmp === '.')
                $cur_tmp = '';
    
            if($cur_adds !== 0)
            {
                foreach($adds_dirs as $cur_dir)
                {
                    $cur_tmp .= $cur_dir .'/';
                    $dirs[$nb_dirs]['name'] = $cur_dir;
                    $dirs[$nb_dirs]['path'] = $cur_tmp;
                    $nb_dirs++;
                }
            }
    
            $parent = 'false';
            if($nb_dirs > 1)
                $parent = $dirs[$nb_dirs - 2]['path'];
    
            $path = '';
            for($i = 0; $i < $nb_dirs; $i++)
            {
                $name = $dirs[$i]['name'];
                if($i === 0 && $win_fs === false)
                    $name = '';
    
                $path .= '<a onclick="openDir(\'' . urlencode($dirs[$i]['path']) . '\')">' . $name . "<span class=\"gap\">/</span></a>\n";
            }
    
            /* TREE */
    
            function show_tree($lvl = 1)
            {
                global $dirs;
                global $nb_dirs;
                $name = $dirs[$lvl - 1]['name'];
                $path = $link = $dirs[$lvl - 1]['path'];
    
                if($link === '.')
                    $link = '';
    
                if($lvl === 1)
                {
                    $dir_default = '';
                    if($nb_dirs === 1)
                        $dir_default = ' treeDefault';
                    $return = "<a class=\"dirOpen treeFirst$dir_default\" style=\"margin-left: 1em;\" onclick=\"openDir('" . urlencode($path) . "')\"><span class=\"icon\"></span>$name</a><br>\n";
                }
                else
                    $return = '';
    
                $next = false;
                if($handle = opendir($path))
                {
                    while(false !== ($entry = readdir($handle)))
                    {
                        if($entry != '.' && $entry != '..' && is_dir($link . $entry . '/'))
                        {
                            if(isset($dirs[$lvl]['name']) && $entry === $dirs[$lvl]['name'])
                            {
                                $dir_default = '';
                                if($lvl === $nb_dirs - 1)
                                    $dir_default = ' treeDefault';
                                
                                $return .= "<a class=\"dirOpen$dir_default\" style=\"margin-left: " . ($lvl + 1) . "em;\" onclick=\"openDir('" . urlencode($dirs[$lvl]['path']) . "')\"><span class=\"icon\"></span>$entry</a><br>\n" . show_tree($lvl + 1);
                                $next = true;
                            }
                            else
                                $return .= '<a class="dir" style="margin-left: ' . ($lvl + 1) . 'em;" onclick="openDir(\'' . urlencode($link . $entry . '/') . "')\"><span class=\"icon\"></span>$entry</a><br>\n";
                        }
                    }
                    closedir($handle);
                }
                if($next === false && isset($dirs[$lvl]['name']))
                    $return .= show_tree($lvl + 1);
    
                return $return;
            }
    
            $tree = show_tree();

            if(!isset($_POST['tree_only']))
            {
                /* ELEMENTS */

                $script_dirs = substr($server_infos['script'], 1);
                if(strpos($script_dirs, '/') === false)
                {
                    $script_dirs = array();
                    $nb_script_dirs = 0;
                }
                else
                {
                    $script_dirs = explode('/', $script_dirs);
                    $nb_script_dirs = sizeof($script_dirs) - 1;
                    unset($script_dirs[$nb_script_dirs]);
                }

                if($cur_rmvs > $nb_script_dirs)
                    $web_view = false;
                else
                {
                    $web_view = $server_infos['web_http'] . $server_infos['web_root'] . '/';

                    $web_dirs = array();
                    for($i = 0; $i < $nb_script_dirs - $cur_rmvs; $i++)
                        $web_dirs[] = $script_dirs[$i];
                    foreach($adds_dirs as $add_dir)
                        $web_dirs[] = $add_dir;
                    foreach($web_dirs as $web_dir)
                        $web_view .= $web_dir . '/';
                }

                $cur_enc = urlencode($current);
                $link = $current;
                if($current === '.')
                    $link = '';

                $order = '0';
                if(isset($_POST['order']))
                {
                    $order = $_POST['order'];
                    $_SESSION['order_' . $cur_enc] = $order;
                }
                elseif(isset($_SESSION['order_' . $cur_enc]))
                    $order = $_SESSION['order_' . $cur_enc];

                $desc = '0';
                if(isset($_POST['desc']))
                {
                    $desc = $_POST['desc'];
                    $_SESSION['desc_' . $cur_enc] = $desc;
                }
                elseif(isset($_SESSION['desc_' . $cur_enc]))
                    $desc = $_SESSION['desc_' . $cur_enc];

                $desc_dirs = '0';
                if($order === '0')
                    $desc_dirs = $desc;
        
                $elems_dirs = array();
                $nb_files = 0;
                if($handle = opendir($current))
                {
                    while(false !== ($entry = readdir($handle)))
                    {
                        if($entry != '.' && $entry != '..')
                        {
                            if(is_dir($link . $entry))
                                $elems_dirs[] = $entry;
                            else
                            {
                                $elems_files[$nb_files]['name'] = $entry;
                                $elems_files[$nb_files]['time'] = @filemtime($link . $entry);
                                $elems_files[$nb_files]['size'] = @filesize($link . $entry);
                                $elems_files[$nb_files]['type'] = file_extension($entry)[1];
                                $nb_files++;
                            }
                        }
                    }
                    closedir($handle);
                }
        
                $elements = '';
        
                if($desc_dirs === '1')
                    $elems_dirs = array_reverse($elems_dirs);

                foreach($elems_dirs as $elem_dir)
                {
                    if($cur_rmvs > 0 && $cur_adds === 0 && $elem_dir === $server_dirs[$nb_dirs]['name'])
                        $url_enc = urlencode(path_parents($cur_rmvs - 1));
                    else
                        $url_enc = urlencode($link . $elem_dir . '/');

                    if($web_view !== false)
                        $web_url = "'" . $web_view . $elem_dir . "/'";
                    else
                        $web_url = 'false';

                    $el_enc = urlencode($elem_dir);
                    $elements .= "<a class=\"dir\" onclick=\"leftClickDir('$url_enc')\" oncontextmenu=\"rightClickDir('$elem_dir', '$cur_enc', '$el_enc', '$url_enc', $web_url)\" onmousedown=\"startClicDir()\" onmouseup=\"endClicDir('$elem_dir', '$cur_enc', '$el_enc', '$url_enc', $web_url)\"><span class=\"icon\"></span><span class=\"txt\">$elem_dir</span></a>\n";
                }

                if($order === '0')
                {
                    if($desc === '1')
                        $elems_files = array_reverse($elems_files);
                }
                else
                {
                    if($order === '1')
                        $arr_order = 'time';
                    elseif($order === '2')
                        $arr_order = 'size';
                    else
                        $arr_order = 'type';

                    if($desc === '0')
                        $arr_desc = 'SORT_ASC';
                    else
                        $arr_desc = 'SORT_DESC';

                    $elems_files = array_sort($elems_files, $arr_order, $arr_desc);
                }

                if(isset($elems_files))
                {
                    foreach($elems_files as $elem_file)
                    {
                        $el_enc = urlencode($elem_file['name']);
    
                        if($web_view !== false)
                            $web_url = "'" . $web_view . $elem_file['name'] . "'";
                        else
                            $web_url = 'false';
    
                        $elements .= '<a class="'. css_extension($elem_file['name']) . '" onclick="menuFile(\'' . $elem_file['name'] . "', '$cur_enc', '$el_enc', $web_url)\" oncontextmenu=\"menuFile('" . $elem_file['name'] . "', '$cur_enc', '$el_enc', $web_url)\"><span class=\"icon\"></span><span class=\"txt\">" . $elem_file['name'] . '</span><span class="size">' . @size_of_file($elem_file['size']) . '</span><span class="date">' . @date('d/m/Y H:i:s', $elem_file['time']) . "</span></a>\n";
                    }
                }

                /* RETURN */
        
                exit('//!token!\\\\' . $_SESSION['token'] . "\n//!current!\\\\$cur_enc\n//!parent!\\\\" . urlencode($parent) . "\n//!path!\\\\$path\n//!tree!\\\\$tree\n//!elements!\\\\$elements\n//!order!\\\\$order\n//!desc!\\\\$desc\n//!end!\\\\");
            }
            else
                exit($tree);
        }
    }
    else
        exit('false');
}
else
{
	header('Content-Type: text/html');
    exit(file_get_contents('template.html'));
}
