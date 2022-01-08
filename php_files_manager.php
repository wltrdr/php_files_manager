<?php
session_start();
clearstatcache();

$password = 'mindja!';

/* SECURITY */

define('version_script', '0.8.3');
include('php/init.php');

$password = sp_crypt($password);

if(!isset($_SESSION['token']))
	$_SESSION['token'] = gencode(32);

/* JAVASCRIPT & CSS */

function show_js_css($type, $name) {
	if(isset($_GET[$type]) && isset($_GET[$name])) {
		if($type === 'css') {
			header('Content-Type: text/css; charset=utf-8');
			$dir = 'template';
		}
		else {
			header('Content-Type: application/javascript; charset=utf-8');
			$dir = 'js';
		}
		exit(file_get_contents($dir . "/$name." . $type));
	}
}

show_js_css('js', 'init');
show_js_css('js', 'functions');
show_js_css('js', 'boxes');
show_js_css('js', 'elements');
show_js_css('js', 'events');
show_js_css('css', 'style');
show_js_css('css', 'images');

/* GET UPLOAD SIZES */

include('php/files_init.php');
if(isset($_GET['get_upload_sizes'])) {
	header('Content-Type: text/plain; charset=utf-8');
	exit('[max_upload_sizes=' . parse_size(ini_get('upload_max_filesize')) . '|' . parse_size(ini_get('post_max_size')) . ']');
}

/* GET SETTINGS */

elseif(isset($_GET['get_settings'])) {
	header('Content-Type: text/plain; charset=utf-8');
	if(isset($_SESSION['view']))
		echo '[view=' . $_SESSION['view'] . ']';
	if(isset($_SESSION['upload_exists']))
		echo '[upload_exists=' . $_SESSION['upload_exists'] . ']';
	if(isset($_SESSION['copy_move_exists']))
		echo '[copy_move_exists=' . $_SESSION['copy_move_exists'] . ']';
	exit();
}

/* DOWNLOAD FILE */

elseif(isset($_GET['download'])) {
	if((isset($_SESSION['pfm']) && $_SESSION['pfm'] === $password)) {
		if(isset($_GET['token']) && $_GET['token'] === $_SESSION['token']) {
			if(isset($_GET['dir'])) {
				$dir = urldecode($_GET['dir']);
				if($dir === '.')
					$dir = '';
				$file = $dir . urldecode($_GET['download']);
				if(is_file($file) || is_link($file)) {
					header('Content-Description: File Transfer');
					header('Content-Type: application/octet-stream');
					header('Cache-Control: no-cache, must-revalidate');
					header('Expires: 0');
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

elseif(isset($_POST['logout'])) {
	header('Content-Type: text/plain; charset=utf-8');
	unset($_SESSION['pfm']);
	exit('bye');
}
elseif(isset($_POST) && !empty($_POST)) {
	header('Content-Type: text/plain; charset=utf-8');
	if((isset($_SESSION['pfm']) && $_SESSION['pfm'] === $password) || (isset($_POST['pwd']) && sp_crypt($_POST['pwd']) === $password)) {
		/* SECURITY */

		if(!isset($_SESSION['pfm']) || $_SESSION['pfm'] !== $password)
			$_SESSION['pfm'] = $password;

		/* LOCATE CURRENT DIRECTORY */

		$current = '.';
		if(isset($_POST['dir']) && !empty($_POST['dir']) && $_POST['dir'] !== '.')
			$current = urldecode($_POST['dir']);

		/* ACTIONS */

		if(isset($_POST['token'])) {
			include('php/files_edit.php');

			if($_POST['token'] === $_SESSION['token']) {
				if($current === '.')
					$current = '';

				include('php/actions.php');
			}
			else
				exit('Refresh site');
		}
		else {
			/* RETURN DIR INFORMATIONS */

			include('php/show_elements.php');

			/* PATH */

			$server_infos = server_infos();
			if(!$server_infos)
				exit('[fatal=Unable to get server information]');

			$script_path = $server_infos['server_root'] . $server_infos['script'];

			$win_fs = true;

			if(strpos($script_path, '/') === false) {
				$win_fs = false;
				$server_dirs[0]['name'] = '/';
				$server_dirs[0]['path'] = '.';
				$nb_server_dirs = 1;
			}
			else {
				if($script_path[0] === '/')
					$win_fs = false;

				$server_dirs = explode('/', $script_path);
				$nb_server_dirs = sizeof($server_dirs) - 1;
				unset($server_dirs[$nb_server_dirs]);

				for($i = 0; $i < $nb_server_dirs; $i++) {
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
			while(strpos($adds_dirs, '../') === 0) {
				$cur_rmvs++;
				$adds_dirs = substr($adds_dirs, 3);
			}

			if(!empty($adds_dirs) && $adds_dirs !== '.') {
				$adds_dirs = substr($adds_dirs, 0, strlen($adds_dirs) - 1);
				if(strpos($adds_dirs, '/') !== false) {
					$adds_dirs = explode('/', $adds_dirs);
					$cur_adds = sizeof($adds_dirs);
				}
				else {
					$adds_dirs = array($adds_dirs);
					$cur_adds = 1;
				}
			}
			else
				$adds_dirs = array();

			$nb_dirs = 0;
			$cur_tmp = '';
			for($i = 0; $i < $nb_server_dirs - $cur_rmvs; $i++) {
				$dirs[$i]['name'] = $server_dirs[$i]['name'];
				$dirs[$i]['path'] = $cur_tmp = $server_dirs[$i]['path'];
				$nb_dirs++;
			}
			if($cur_tmp === '.')
				$cur_tmp = '';

			if($cur_adds !== 0) {
				foreach($adds_dirs as $cur_dir) {
					$cur_tmp .= $cur_dir .'/';
					$dirs[$nb_dirs]['name'] = $cur_dir;
					$dirs[$nb_dirs]['path'] = $cur_tmp;
					$nb_dirs++;
				}
			}

			$path = '';
			for($i = 0; $i < $nb_dirs; $i++) {
				$name = $dirs[$i]['name'];
				if($i === 0 && $win_fs === false)
					$name = '';

				$path .= '<a onclick="openDir(\'' . urlencode($dirs[$i]['path']) . '\')">' . htmlentities($name, ENT_QUOTES) . "<span class=\"gap\">/</span></a>\n";
			}

			$parent = 'false';
			if($nb_dirs > 1)
				$parent = $dirs[$nb_dirs - 2]['path'];

			/* TREE */

			$tree_only = false;

			if(isset($_POST['tree_only']))
				$tree_only = true;

			function show_tree($lvl = 1) {
				global $tree_only;
				global $dirs;
				global $nb_dirs;
				global $cur_rmvs;
				global $server_dirs;
				global $nb_server_dirs;
				$name = $dirs[$lvl - 1]['name'];
				$path = $link = $dirs[$lvl - 1]['path'];

				if($link === '.')
					$link = '';

				$func_js = 'openDir';
				if($tree_only !== false)
					$func_js = 'boxPathNavigate';

				$return = '';
				if($lvl === 1) {
					$dir_default = '';
					if($nb_dirs === 1)
						$dir_default = ' treeDefault';

					$return = "<a class=\"dirOpen treeFirst$dir_default\" style=\"margin-left: 1em;\" onclick=\"$func_js('" . urlencode($path) . '\')"><span class="icon"></span>' . htmlentities($name, ENT_QUOTES) . "</a><br>\n";
				}

				$next = false;
				if($handle = opendir($path)) {
					while(false !== ($entry = readdir($handle))) {
						if($entry != '.' && $entry != '..' && is_dir($link . $entry . '/') && !is_link($link . $entry)) {
							$entry_html = htmlentities($entry, ENT_QUOTES);

							if(isset($dirs[$lvl]['name']) && $entry === $dirs[$lvl]['name']) {
								$dir_default = '';
								if($lvl === $nb_dirs - 1)
									$dir_default = ' treeDefault';

								$return .= "<a class=\"dirOpen$dir_default\" style=\"margin-left: " . ($lvl + 1) . "em;\" onclick=\"$func_js('" . urlencode($dirs[$lvl]['path']) . "')\"><span class=\"icon\"></span>$entry_html</a><br>\n" . show_tree($lvl + 1);
								$next = true;
							}
							else {
								$dir = $link . $entry . '/';
								if(isset($server_dirs[$lvl]['name'])) {
									if($server_dirs[$lvl]['name'] === $entry) {
										$parent_on_srv_dirs = true;
										for($i = 0; $i < $lvl; $i++) {
											if($server_dirs[$i]['name'] !== $dirs[$i]['name'])
												$parent_on_srv_dirs = false;
										}
										if($parent_on_srv_dirs == true)
											$dir = $server_dirs[$lvl]['path'];
									}
								}

								$return .= '<a class="dir" style="margin-left: ' . ($lvl + 1) . "em;\" onclick=\"$func_js('" . urlencode($dir) . "')\"><span class=\"icon\"></span>$entry_html</a><br>\n";
							}
						}
					}
					closedir($handle);
				}
				else {
					$dir_default = '';
					if($lvl === $nb_dirs - 1)
						$dir_open = 'Open treeDefault';

					$return .= "<a class=\"dir$dir_open\" style=\"margin-left: " . ($lvl + 1) . "em;\" onclick=\"$func_js('" . urlencode($server_dirs[$lvl]['path']) . '\')"><span class="icon"></span>' . htmlentities($server_dirs[$lvl]['name'], ENT_QUOTES) . "</a><br>\n";

					if(isset($dirs[$lvl]))
						$return .= show_tree($lvl + 1);
					$next = true;
				}
				if($next === false && isset($dirs[$lvl]['name'])) {
					$dir_default = '';
					if($lvl === $nb_dirs - 1)
						$dir_default = ' treeDefault';

					$return .= "<a class=\"dirOpen$dir_default\" style=\"margin-left: " . ($lvl + 1) . "em;\" onclick=\"$func_js('" . urlencode($dirs[$lvl]['path']) . '\')"><span class="icon"></span>' . htmlentities($dirs[$lvl]['name'], ENT_QUOTES) . "</a><br>\n" . show_tree($lvl + 1);
				}

				return $return;
			}

			$tree = show_tree();

			if($tree_only === false) {

				/* ELEMENTS */

				$script_dirs = substr($server_infos['script'], 1);
				if(strpos($script_dirs, '/') === false) {
					$script_dirs = array();
					$nb_script_dirs = 0;
				}
				else {
					$script_dirs = explode('/', $script_dirs);
					$nb_script_dirs = sizeof($script_dirs) - 1;
					unset($script_dirs[$nb_script_dirs]);
				}

				if($cur_rmvs > $nb_script_dirs)
					$web_view = false;
				else {
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
				if(isset($_POST['order'])) {
					$order = $_POST['order'];
					$_SESSION['order_' . $cur_enc] = $order;
				}
				elseif(isset($_SESSION['order_' . $cur_enc]))
					$order = $_SESSION['order_' . $cur_enc];

				$desc = '0';
				if(isset($_POST['desc'])) {
					$desc = $_POST['desc'];
					$_SESSION['desc_' . $cur_enc] = $desc;
				}
				elseif(isset($_SESSION['desc_' . $cur_enc]))
					$desc = $_SESSION['desc_' . $cur_enc];

				$desc_dirs = '0';
				if($order === '0')
					$desc_dirs = $desc;

				$elems_files = array();
				$nb_el_files = 0;
				$elems_dirs = array();
				$nb_el_dirs = 0;
				if($handle = opendir($current)) {
					while(false !== ($entry = readdir($handle))) {
						if($entry != '.' && $entry != '..') {
							if(is_dir($link . $entry)) {
								$elems_dirs[$nb_el_dirs]['name'] = $entry;
								$elems_dirs[$nb_el_dirs]['link'] = is_link($link . $entry);
								$nb_el_dirs++;
							}
							else {
								$elems_files[$nb_el_files]['name'] = $entry;
								$elems_files[$nb_el_files]['time'] = filemtime($link . $entry);
								$elems_files[$nb_el_files]['size'] = filesize($link . $entry);
								$elems_files[$nb_el_files]['type'] = split_filename($entry)['extension'];
								$elems_files[$nb_el_files]['link'] = is_link($link . $entry);
								$nb_el_files++;
							}
						}
					}
					closedir($handle);
				}

				$elements = '';

				if($desc_dirs === '1')
					$elems_dirs = array_sort($elems_dirs, 'name', 'DESC');
				else
					$elems_dirs = array_sort($elems_dirs, 'name');

				foreach($elems_dirs as $elem_dir) {
					$el_enc = urlencode($elem_dir['name']);
					$el_html = htmlentities($elem_dir['name'], ENT_QUOTES);

					if($cur_rmvs > 0 && $cur_adds === 0 && $elem_dir['name'] === $server_dirs[$nb_dirs]['name'])
						$full_path_enc = urlencode(path_parents($cur_rmvs - 1));
					else
						$full_path_enc = urlencode($link . $elem_dir['name'] . '/');

					if($web_view !== false)
						$web_url = "'" . $web_view . $el_html . "/'";
					else
						$web_url = 'false';

					$link_icon = 'dir';
					$link_js = 'false';
					if($elem_dir['link']) {
						$link_icon = 'linkdir';
						$link_js = 'true';
					}

					$elements .= "<a class=\"$link_icon\" onclick=\"leftClickDir('$full_path_enc', $link_js)\" oncontextmenu=\"menuDir('$el_html', '$cur_enc', '$el_enc', '$full_path_enc', $web_url, $link_js)\" onmousedown=\"startClicDir()\" onmouseup=\"endClicDir('$el_html', '$cur_enc', '$el_enc', '$full_path_enc', $web_url, $link_js)\"><span class=\"icon\"></span><span class=\"txt\">$el_html</span></a>\n";
				}

				if($order === '1')
					$arr_order = 'time';
				elseif($order === '2')
					$arr_order = 'size';
				elseif($order === '3')
					$arr_order = 'type';
				else
					$arr_order = 'name';

				if($desc === '1')
					$arr_desc = 'DESC';
				else
					$arr_desc = 'ASC';

				$elems_files = array_sort($elems_files, $arr_order, $arr_desc);

				if(isset($elems_files)) {
					foreach($elems_files as $elem_file) {
						$el_enc = urlencode($elem_file['name']);
						$el_html = htmlentities($elem_file['name'], ENT_QUOTES);

						if($web_view !== false)
							$web_url = "'" . $web_view . $el_html . "'";
						else
							$web_url = 'false';

						if($elem_file['link']) {
							$link_icon = 'linkfile';
							$link_js = 'true';
						}
						else {
							$link_icon = css_extension($elem_file['name']);
							$link_js = 'false';
						}

						$elements .= "<a class=\"$link_icon\" onclick=\"menuFile('$el_html', '$cur_enc', '$el_enc', $web_url, $link_js)\" oncontextmenu=\"menuFile('$el_html', '$cur_enc', '$el_enc', $web_url, $link_js)\"><span class=\"icon\"></span><span class=\"txt\">$el_html</span><span class=\"size\">" . size_of_file($elem_file['size']) . '</span><span class="date">' . date('d/m/Y H:i:s', $elem_file['time']) . "</span></a>\n";
					}
				}

				/* RETURN */

				exit('//!token!\\\\' . $_SESSION['token'] . "\n//!current!\\\\$cur_enc\n//!parent!\\\\" . urlencode($parent) . "\n//!path!\\\\$path\n//!tree!\\\\$tree\n//!elements!\\\\$elements\n//!order!\\\\$order\n//!desc!\\\\$desc\n//!end!\\\\");
			}
			else
				exit("//!tree!\\\\$tree\n//!end!\\\\");
		}
	}
	else
		exit('false');
}
else {
	header('Content-Type: text/html; charset=utf-8');
	exit(str_replace('\' . version_script . \'', version_script, file_get_contents('template/template.html')));
}
