<?php
/*
        design 100% responsive
        chargement des elements en ajax
        protection par mot de passe
        affichage du chemin courant et de l'arborescense des fichiers depuis la racine (accessible) du serveur
        boutons de controle : historique back forward, dossier parent, retour a l'accueil, boutons de rafraifhissement
        création de nouveaux dossiers et fichiers
        renommage et suppression des fichiers et dossiers
        protection scripts malicieux par url connue masquée

[>>>] empecher creer fichier avec apostrophe

upload fichier clic
menu fichiers + dossiers
{
    affichage (si accessible)
    telecharger (fichier)

    dupliquer
    deplacer vers
    copier vers
    chmods
    infos (fileperms()+stat())
    editer (fichier)
}
type affichage elements
ordonner par
drag drop upload
drag drop upload dans dossier
supprimer partie historique si trop long
drag drop deplacement dans dossier
si multi select > deplacement dans dossier + copie coupe colle
clic droit zone elements
{
    afficher (si accessible)
    type voir
    type sort
    nouveau dir
    nouveau file
    coller
}
’ʿ
si possible sans chgmt url : history push a chaque requete ajax (sauf login)
*/

session_start();

$password = 'admin';

/* SECURITY */

function sp_crypt($str)
{
    return sha1($_SERVER['REMOTE_ADDR'] . md5($str));
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

$password = sp_crypt($password);

/* JAVASCRIPT INITIALIZATION */

if(isset($_GET['js']) && isset ($_GET['init']))
{
	header('Content-Type: application/javascript');
    exit(file_get_contents('init.js'));
}

/* JAVASCRIPT FUNCTIONS */

elseif(isset($_GET['js']) && isset ($_GET['functions']))
{
	header('Content-Type: application/javascript');
    exit(file_get_contents('functions.js'));
}

/* JAVASCRIPT EVENTS */

elseif(isset($_GET['js']) && isset ($_GET['events']))
{
	header('Content-Type: application/javascript');
    exit(file_get_contents('events.js'));
}

/* CSS */

elseif(isset($_GET['css']))
{
	header('Content-Type: text/css');
    exit(file_get_contents('style.css'));
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

        if(!isset($_SESSION['token']))
            $_SESSION['token'] = gencode(32);

        /* LOCATE CURRENT DIRECTORY */

        $current = '.';
        if(isset($_POST['dir']) && !empty($_POST['dir']) && $_POST['dir'] !== '.')
            $current = urldecode($_POST['dir']);

        /* ACTIONS */

        if(isset($_POST['token']))
        {
            if($_POST['token'] === $_SESSION['token'])
            {
                if($current === '.')
                    $current = '';

                /* NEW FILE OR FOLDER */

                if(isset($_POST['new']))
                {
                    $new_name = urldecode($_POST['name']);

                    if($_POST['new'] === 'file')
                    {
                        if(@is_file($current . $new_name))
                            exit('File already exists');
                        else
                        {
                            if(@file_put_contents($current . $new_name, '') !== false)
                                exit('created');
                            else
                                exit('File not created');
                        }
                    }
                    else
                    {
                        if(@is_dir($current . $new_name))
                            exit('Directory already exists');
                        else
                        {
                            if(@mkdir($current . $new_name))
                                exit('created');
                            else
                                exit('Directory not created');
                        }
                    }
                }

                /* RENAME ELEMENT */

                elseif(isset($_POST['ren']))
                {
                    if(@rename($current . urldecode($_POST['ren']), $current . urldecode($_POST['name'])))
                        exit('renamed');
                    else
                        exit('Not renamed');
                }

                /* DELETE ELEMENT */

                elseif(isset($_POST['del']))
                {
                    $name = urldecode($_POST['del']);

                    if(@is_file($current . $name))
                    {
                        if(@unlink($current . $name))
                            exit('deleted');
                        else
                            exit('File not deleted');
                    }
                    else
                    {
                        function rmfulldir($dir)
                        {
                            if($handle = opendir($dir . '/'))
                            {
                                while(false !== ($entry = readdir($handle)))
                                {
                                    if($entry != '.' && $entry != '..')
                                    {
                                        if(is_dir($dir . '/' . $entry))
                                            rmfulldir($dir . '/' . $entry);
                                        else
                                            unlink($dir . '/' . $entry);
                                    }
                                }
                                closedir($handle);
                                if(rmdir($dir . '/'))
                                    return true;
                                return false;
                            }
                        }

                        if(@rmfulldir($current . $name))
                            exit('deleted');
                        else
                            exit('Directory not deleted');
                    }
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
                if(strpos($file, '.'))
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
    
            /* PATH */
    
            function path_parents($nb)
            {
                if($nb == 0)
                    return '.';
                else
                {
                    $return = '';
                    for($i = 0; $i < $nb; $i++)
                        $return .= '../';
                    return $return;
                }
            }
    
            $script_path = $_SERVER['SCRIPT_FILENAME'];
    
            $win_fs = true;
    
            if(strpos($script_path, '/') === false)
            {
                $win_fs = false;
                $script_dirs[0]['name'] = '/';
                $script_dirs[0]['path'] = '.';
                $nb_script_dirs = 1;
            }
            else
            {
                if($script_path[0] === '/')
                    $win_fs = false;
    
                $script_dirs = explode('/', $script_path);
                $nb_script_dirs = sizeof($script_dirs) - 1;
                unset($script_dirs[$nb_script_dirs]);
    
                for($i = 0; $i < $nb_script_dirs; $i++)
                {
                    if($i === 0 && empty($script_dirs[0]))
                        $tmp = '/';
                    else
                        $tmp = $script_dirs[$i];
    
                    $script_dirs[$i] = null;
                    $script_dirs[$i]['name'] = $tmp;
                    $script_dirs[$i]['path'] = path_parents($nb_script_dirs - ($i + 1));
                }
            }
    
            $cur_rmvs = 0;
            $cur_adds = 0;
            
            $current_dirs = $current;
            while(strpos($current_dirs, '../') === 0)
            {
                $cur_rmvs++;
                $current_dirs = substr($current_dirs, 3);
            }
    
            if(!empty($current_dirs) && $current_dirs !== '.')
            {
                $current_dirs = substr($current_dirs, 0, strlen($current_dirs) - 1);
                if(strpos($current_dirs, '/'))
                {
                    $current_dirs = explode('/', $current_dirs);
                    $cur_adds = sizeof($current_dirs);
                }
                else
                {
                    $current_dirs = array($current_dirs);
                    $cur_adds = 1;
                }
            }
    
            $nb_dirs = 0;
            $cur_tmp = '';
    
            for($i = 0; $i < $nb_script_dirs - $cur_rmvs; $i++)
            {
                $dirs[$i]['name'] = $script_dirs[$i]['name'];
                $dirs[$i]['path'] = $cur_tmp = $script_dirs[$i]['path'];
                $nb_dirs++;
            }
    
            if($cur_tmp === '.')
                $cur_tmp = '';
    
            if($cur_adds !== 0)
            {
                foreach($current_dirs as $cur_dir)
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
    
                $path .= '<a onclick="openDir(\'' . urlencode($dirs[$i]['path']) . '\');">' . $name . "<span class=\"gap\">/</span></a>\n";
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
                    $return = "<a class=\"dirOpen treeFirst$dir_default\" style=\"margin-left: 1em;\" onclick=\"openDir('" . urlencode($path) . "');\"><span></span>$name</a><br>\n";
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
                                
                                $return .= "<a class=\"dirOpen$dir_default\" style=\"margin-left: " . ($lvl + 1) . "em;\" onclick=\"openDir('" . urlencode($dirs[$lvl]['path']) . "');\"><span></span>$entry</a><br>\n" . show_tree($lvl + 1);
                                $next = true;
                            }
                            else
                                $return .= '<a class="dir" style="margin-left: ' . ($lvl + 1) . 'em;" onclick="openDir(\'' . urlencode($link . $entry . '/') . "');\"><span></span>$entry</a><br>\n";
                        }
                    }
                    closedir($handle);
                }
                if($next === false && isset($dirs[$lvl]['name']))
                    $return .= show_tree($lvl + 1);
    
                return $return;
            }
    
            $tree = show_tree();
    
            /* ELEMENTS */
    
            $link = $current;
            if($current === '.')
                $link = '';
    
            $elems_dirs = array();
            $elems_files = array();
            if($handle = opendir($current))
            {
                while(false !== ($entry = readdir($handle)))
                {
                    if($entry != '.' && $entry != '..')
                    {
                        if(is_dir($link . $entry))
                            $elems_dirs[] = $entry;
                        else
                            $elems_files[] = $entry;
                    }
                }
                closedir($handle);
            }
    
            $elements = '';
            $cur_enc = urlencode($current);
    
            foreach($elems_dirs as $elem_dir)
            {
                if($cur_rmvs > 0 && $cur_adds === 0 && $elem_dir === $script_dirs[$nb_dirs]['name'])
                    $url_enc = urlencode(path_parents($cur_rmvs - 1));
                else
                    $url_enc = urlencode($link . $elem_dir . '/');

                $el_enc = urlencode($elem_dir);
                $elements .= "<a class=\"dir\" onclick=\"leftClickDir('$url_enc');\" oncontextmenu=\"rightClickDir('$elem_dir', '$cur_enc', '$el_enc', '$url_enc');\" onmousedown=\"startClicDir();\" onmouseup=\"endClicDir('$elem_dir', '$cur_enc', '$el_enc', '$url_enc');\"><span></span>$elem_dir</a>\n";
            }

            foreach($elems_files as $elem_file)
            {
                $el_enc = urlencode($elem_file);
                $elements .= '<a class="'. css_extension($elem_file) . "\" onclick=\"menuFile('$elem_file', '$cur_enc', '$el_enc');\" oncontextmenu=\"menuFile('$elem_file', '$cur_enc', '$el_enc');\"><span></span>$elem_file</a>\n";
            }
    
            /* RETURN */
    
            exit('//!token!\\\\' . $_SESSION['token'] . "\n//!current!\\\\" . urlencode($current) . "\n//!parent!\\\\" . urlencode($parent) . "\n//!path!\\\\$path\n//!tree!\\\\$tree\n//!elements!\\\\$elements//!end!\\\\");
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
