<?php
header('Content-Type: text/plain; charset=utf-8');
$file = file_get_contents('php_files_manager.src.php');
function addsomeslashes($str) {
	return str_replace('\'', '\\\'', str_replace('\\', '\\\\', $str));
}

/* CSS, JS && TRASH.HTML */
preg_match_all('#exit\(file_get_contents\(\'([^\']+)\'\)\);#', $file, $matches, PREG_SET_ORDER);
foreach($matches as $matche) {
	$file = str_replace($matche[0], 'exit(\'' . addsomeslashes(file_get_contents($matche[1])) . '\');', $file);
}

/* TEMPLATE.HTML */
$file = str_replace('\\\' . script_version . \\\'', '\' . script_version . \'', str_replace('str_replace(\'\\\' . script_version . \\\'\', script_version, file_get_contents(\'template/template.html\'))', '\'' . addsomeslashes(file_get_contents('template/template.html')) . '\'', $file));

/* INCLUDES */
preg_match_all('#include\(\'([^\']+)\'\);#', $file, $matches, PREG_SET_ORDER);
foreach($matches as $matche) {
	$file = str_replace($matche[0], str_replace('<?php', '', file_get_contents($matche[1])), $file);
}

/* MINIMIZE && COMPILE */
file_put_contents('../php_files_manager.php', str_replace("\n\n", "\n", str_replace("\n\n", "\n", str_replace("\r\n\r\n", "\r\n", str_replace("\r\n\r\n", "\r\n", str_replace("\t", '', $file))))));

exit('File generated !');
