<?php
header('Content-Type: text/plain; charset=utf-8');
$file = file_get_contents('php_files_manager.src.php');
function m_stripslashes($str) {
    return str_replace('\'', '\\\'', str_replace('\\', '\\\\', $str));
}

/* CSS, JS && TRASH.HTML */
preg_match_all('#exit\(file_get_contents\(\'([^\']+)\'\)\);#', $file, $matches, PREG_SET_ORDER);
foreach($matches as $matche) {
    $by = 'exit(\'' . m_stripslashes(file_get_contents($matche[1])) . '\');';
    $file = str_replace($matche[0], $by, $file);
}

/* TEMPLATE.HTML */
$rep = 'str_replace(\'\\\' . version_script . \\\'\', version_script, file_get_contents(\'template/template.html\'))';
$by = '\'' . m_stripslashes(file_get_contents('template/template.html')) . '\'';
$file = str_replace($rep, $by, $file);
$file = str_replace('\\\\\' . version_script . \\\\\'', '\' . version_script . \'', $file);

/* INCLUDES */
preg_match_all('#include\(\'([^\']+)\'\);#', $file, $matches, PREG_SET_ORDER);
foreach($matches as $matche) {
    $by = str_replace('<?php', '', file_get_contents($matche[1]));
    $file = str_replace($matche[0], $by, $file);
}

/* MINIMIZE && COMPILE */
$file = str_replace("\t", '', $file);
$file = str_replace("\r\n\r\n", "\r\n", $file);
$file = str_replace("\r\n\r\n", "\r\n", $file);
$file = str_replace("\n\n", "\n", $file);
$file = str_replace("\n\n", "\n", $file);
file_put_contents('../php_files_manager.php', $file);
exit('File generated !');
