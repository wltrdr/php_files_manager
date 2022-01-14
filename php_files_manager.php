<?php
session_start();
clearstatcache();
$password = 'mindja!';
/* SECURITY */
define('version_script', '0.9.9');
function get_user_ip() {
if(isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
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
function sp_crypt($str) {
$remote_addr = get_user_ip();
if(isset($_SERVER['HTTP_USER_AGENT']))
$user_agent = $_SERVER['HTTP_USER_AGENT'];
else
$user_agent = '';
if(empty($remote_addr) && empty($user_agent))
exit('Error : <b>Unable to get user information</b>');
return sha1($remote_addr . md5($str) . $user_agent);
}
function gencode($nb) {
$cars = 'azertyuiopqsdfghjklmwxcvbn0123456789';
$mt_max = mb_strlen($cars) - 1;
$return = '';
for($i = 0; $i < $nb; $i++)
$return .= $cars[mt_rand(0, $mt_max)];
return $return;
}
function server_infos() {
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
if(isset($_SERVER['DOCUMENT_ROOT'])) {
$server_root = $_SERVER['DOCUMENT_ROOT'];
if($script_name === false) {
if(isset($_SERVER['SCRIPT_FILENAME'])) {
$script_filename = $_SERVER['SCRIPT_FILENAME'];
if(strpos($script_filename, $server_root) === 0)
$script_name = substr($script_filename, mb_strlen($server_root));
else
return false;
}
else
return false;
}
}
elseif(isset($_SERVER['SCRIPT_FILENAME'])) {
$script_filename = $_SERVER['SCRIPT_FILENAME'];
if($script_name !== false) {
$lng_script_filename = mb_strlen($script_filename);
$lng_script_name = mb_strlen($script_name);
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
if(strtolower(substr(PHP_OS, 0, 3)) === 'win')
$return['server_on_windows'] = true;
else
$return['server_on_windows'] = false;
return $return;
}
function no_end_slash($str) {
$lng = mb_strlen($str) - 1;
if($lng >= 0 && $str[$lng] === '/')
$str = substr($str, 0, $lng);
return $str;
}
function split_dirname($dirname) {
$dirname = no_end_slash($dirname);
if(strpos($dirname, '/') === false)
$path = '';
else {
$path_arr = explode('/', $dirname);
$nb_path = sizeof($path_arr);
$path = '';
for($i = 0; $i < $nb_path - 1; $i++)
$path .= $path_arr[$i] . '/';
$dirname = $path_arr[$nb_path - 1];
}
return array('path' => $path, 'name' => $dirname);
}
function split_filename($filename) {
if(strpos($filename, '/') === false)
$path = '';
else {
$path_arr = explode('/', $filename);
$nb_path = sizeof($path_arr);
$path = '';
for($i = 0; $i < $nb_path - 1; $i++)
$path .= $path_arr[$i] . '/';
$filename = $path_arr[$nb_path - 1];
}
if(strpos($filename, '.') === false || $filename[mb_strlen($filename) - 1] === '.') {
$dot = '';
$extension = '';
}
else {
$name_arr = explode('.', $filename);
$nb_name = sizeof($name_arr);
$filename = '';
for($i = 0; $i < $nb_name - 1; $i++) {
$filename .= $name_arr[$i];
if($i < $nb_name - 2)
$filename .= '.';
}
$extension = $name_arr[$nb_name - 1];
$dot = '.' . $extension;
}
return array('path' => $path, 'name' => $filename, 'extension' => $extension, 'dot_extension' => $dot);
}
function size_of_file($size) {
if($size < 1024)
return $size . ' o';
else {
$m = pow(1024, 2);
$g = pow(1024, 3);
if($size < $m)
return round($size / 1024, 1) . ' Ko';
elseif($size < $g)
return round($size / $m, 1) . ' Mo';
return round($size / $g, 1) . ' Go';
}
}
function parse_size($size) {
$unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
$size = preg_replace('/[^0-9\.]/', '', $size);
if($unit)
return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
return round($size);
}
function file_or_link_exists($filename) {
if(is_file($filename) || is_link($filename) || is_dir($filename))
return true;
return false;
}
function rename_exist($filename) {
$i = 1;
while(file_or_link_exists($filename . ' (' + $i + ')'))
$i++;
if(rename($filename, $filename . ' (' + $i + ')'))
return $i;
return false;
}
function create_htrashccess() {
global $server_infos;
$path = 'Trash/.htaccess';
if(!file_or_link_exists($path) || is_dir($path) || is_link($path)) {
if(file_or_link_exists($path)) {
if(!rename_exist($path))
return false;
}
if(file_put_contents($path, "RewriteEngine On\nRewriteRule ^(.*)$ https://%{HTTP_HOST}" . $server_infos['script'] . "?trashed=true [L,R=301]\n"))
return true;
return false;
}
return true;
}
$password = sp_crypt($password);
if(!isset($_SESSION['token']))
$_SESSION['token'] = gencode(32);
$server_infos = server_infos();
/* JAVASCRIPT & CSS */
if(isset($_GET['js'])) {
header('Content-Type: application/javascript; charset=utf-8');
if(isset($_GET['init']))
exit('const longClicMs = 1500// TIME OF LONG CLIC ON MOBILE
const checkIntervMs = 500// TIME INTERVAL TO CHECK IF DIR MUST BE REFRESHED
const BtwRefreshesMs = 3333// TIME BETWEEN EACH AUTO-REFRESH
const delayLoadingMs = 500// TIME BEFORE SHOWING LOADING DURING NAVIGATION
const delayMenuMs = 50// INCREASE IF BUGS ON CONTEXT MENU OR POPUP BOX
const delayH1MobileMs = 1500// H1 BLINK SPEED ON MOBILE
const delayBadCnxMs = 150// INPUT BLINK SPEED IF BAD PASSWORD
const delayBadCnxBkMs = 50// INPUT HIDING SPEED DURING BLINK IF BAD PASSWORD
const historyMax = 50// MAX ENTRIES IN HISTORY
const loading = document.querySelector("#loading")
const popupBox = document.querySelector("#popupBox")
const popupMask = document.querySelector("#popupMask")
const popupMenu = document.querySelector("#popupMenu")
const selection = document.querySelector("#selection")
const connexion = document.querySelector("#connexion")
const formConnexion = document.querySelector("#connexion form")
const inputConnexion = document.querySelector("#connexion form input")
const btnConnexion = document.querySelector("#connexion form button")
const contents = document.querySelector("#contents")
const h1 = document.querySelector("h1")
const wltrdrUpdate = document.querySelector("#wltrdrUpdate")
const inputUpload = document.querySelector("#title #upload")
const btnBack = document.querySelector("#controls #back")
const btnForward = document.querySelector("#controls #forward")
const btnParent = document.querySelector("#controls #parent")
const btnRefresh = document.querySelector("#controls #refresh")
const btnHome = document.querySelector("#controls #home")
const btnView = document.querySelector("#controls #view")
const btnSort = document.querySelector("#controls #sort")
const btnSettings = document.querySelector("#controls #settings")
const btnCreate = document.querySelector("#controls #create")
const path = document.querySelector("#path")
const logout = document.querySelector("#logout")
const tree = document.querySelector("#tree")
const listTree = document.querySelector("#tree .list")
const elements = document.querySelector("#elements")
const listElements = document.querySelector("#elements .list")
const inputConnexionPH = inputConnexion.placeholder
const h1Default = h1.innerHTML
const h1Words = h1Default.split(" ")
const h1NbWords = h1Words.length
const urlRawGithub = "https://raw.githubusercontent.com/wltrdr/php_files_manager/main/php_files_manager.php"
let token
let currentPath = "."
let parentPath = "false"
let webAccessible = false
let timeDirOpened = 0
const history = []
let historyLevel = 0
let uploadMaxFileSize = 0
let uploadMaxTotalSize = 0
let onLoading = false
let willBeOnLoading = false
let srvOnWindows = false
let typeView = 0
let typeOrder = 0
let typeOrderDesc = 0
let typeTrash = 0
let typeUploadExists = 0
let typeCopyMoveExists = 2
let h1Lvl = -1
let disableAutoRefresh = false
let overAdir = false
let selectedElements = []
let selectWcursor = false
let selectionStartX = 0
let selectionStartY = 0
let mouseUpOnEl = false
let mouseDownOnEl = false
let rightClicOnEl = false
let tryToMove = false
let copy = []
let copyNotCut = true
function onMobile() {
function checkMobile(navData) {
if(navData && navData != null) {
if(
/(android|bb\\d+|meego).+mobile|avantgo|bada\\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.
test(navData)
||
/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\\-(n|u)|c55\\/|capi|ccwa|cdm\\-|cell|chtm|cldc|cmd\\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\\-s|devi|dica|dmob|do(c|p)o|ds(12|\\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\\-|_)|g1 u|g560|gene|gf\\-5|g\\-mo|go(\\.w|od)|gr(ad|un)|haie|hcit|hd\\-(m|p|t)|hei\\-|hi(pt|ta)|hp( i|ip)|hs\\-c|ht(c(\\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\\-(20|go|ma)|i230|iac( |\\-|\\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\\/)|klon|kpt |kwc\\-|kyo(c|k)|le(no|xi)|lg( g|\\/(k|l|u)|50|54|\\-[a-w])|libw|lynx|m1\\-w|m3ga|m50\\/|ma(te|ui|xo)|mc(01|21|ca)|m\\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\\-2|po(ck|rt|se)|prox|psio|pt\\-g|qa\\-a|qc(07|12|21|32|60|\\-[2-7]|i\\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\\-|oo|p\\-)|sdk\\/|se(c(\\-|0|1)|47|mc|nd|ri)|sgh\\-|shar|sie(\\-|m)|sk\\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\\-|v\\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\\-|tdg\\-|tel(i|m)|tim\\-|t\\-mo|to(pl|sh)|ts(70|m\\-|m3|m5)|tx\\-9|up(\\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\\-|your|zeto|zte\\-/i.
test(navData.substr(0, 4))
)
return true
}
return false
}
if(checkMobile(navigator.userAgent))
return true
else if(checkMobile(navigator.vendor))
return true
else if(checkMobile(window.opera))
return true
return false
}
let isOnMobile = onMobile()
function effectH1Mobile(el) {
if(isOnMobile === true) {
h1Lvl++
if(h1Lvl >= h1NbWords)
h1Lvl = 0
el.innerHTML = h1Words[h1Lvl]
}
else if(el.innerHTML !== h1Default)
el.innerHTML = h1Default
setTimeout(() => { effectH1Mobile(el) }, delayH1MobileMs)
}
effectH1Mobile(h1)
/* UNSELECT ELEMENTS (FOR OPENDIR) */
function unselectElements() {
disableAutoRefresh = false
selectedElements.forEach((selectedElement, i) => {
selectedElement.element.classList.remove("selected")
})
selectedElements = []
}
');
elseif(isset($_GET['functions']))
exit('function ajaxRequest(method, url, data, callback = false, disableLoading = false, disableBadRequest = false) {
if(disableLoading === false) {
onLoading = true
willBeOnLoading = true
setTimeout(() => {
if(onLoading === true)
loading.style.display = "block"
}, delayLoadingMs)
}
const httpRequest = new XMLHttpRequest()
if(!httpRequest) {
alert("Error : Cannot create instance of XMLHTTP")
return false
}
httpRequest.onreadystatechange = function() {
if(httpRequest.readyState === XMLHttpRequest.DONE) {
if(disableLoading === false) {
onLoading = false
willBeOnLoading = false
loading.style.display = "none"
}
if(httpRequest.status === 200 && callback !== false)
callback(httpRequest.responseText)
else if(httpRequest.status !== 200 && disableBadRequest === false)
alert("Error : Bad request")
}
}
if(method === "POST") {
httpRequest.open("POST", url)
httpRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded")
httpRequest.send(data)
}
else if(method === "FILES") {
httpRequest.open("POST", url)
httpRequest.send(data)
}
else {
httpRequest.open(method, url + "?" + data)
httpRequest.send()
}
}
/* EXPLORER */
function showElements(result, disableFocus = false) {
const found = result.match(/^(.*)\\/\\/!token!\\\\\\\\(.*)\\n\\/\\/!current!\\\\\\\\(.*)\\n\\/\\/!parent!\\\\\\\\(.*)\\n\\/\\/!path!\\\\\\\\(.*)\\n\\/\\/!tree!\\\\\\\\(.*)\\n\\/\\/!elements!\\\\\\\\(.*)\\n\\/\\/!web!\\\\\\\\(.*)\\n\\/\\/!order!\\\\\\\\(.*)\\n\\/\\/!desc!\\\\\\\\(.*)\\n\\/\\/!end!\\\\\\\\(.*)$/s)
if(found) {
if(found[1] || found[11])
console.log(`%cPHP Errors :\\n\\n%c${found[1].replace(/<[^>]+>/g, "")}\\n\\n${found[11].replace(/<[^>]+>/g, "")}`, "font-size: 2em; color: red;", "font-size: 1em; color: auto;")
const scrollTree = tree.scrollTop
const scrollElems = elements.scrollTop
connexion.style.display = "none"
contents.style.display = "flex"
token = found[2]
currentPath = found[3]
parentPath = found[4]
path.innerHTML = found[5]
listTree.innerHTML = found[6]
listElements.innerHTML = found[7]
webAccessible = false
if(found[8] !== "false")
webAccessible = found[8]
typeOrder = parseInt(found[9], 10)
typeOrderDesc = parseInt(found[10], 10)
if(parentPath === "false")
btnParent.className = "disabled"
else
btnParent.className = ""
if(disableFocus === true) {
tree.scrollTop = scrollTree
elements.scrollTop = scrollElems
}
else {
try {
tree.scrollTop = document.querySelector(".treeDefault").offsetTop - (listTree.offsetTop + parseInt(window.getComputedStyle(document.querySelector(".treeFirst"), null).getPropertyValue("margin-top"), 10))
}
catch {
console.log("%cError : %cUnable to access parent", "color: red;", "color: auto;")
}
elements.scrollTop = 0
}
}
else {
const fatal = result.match(/(.*)\\[fatal=([^\\]]+)\\](.*)/s)
if(fatal) {
if(fatal[1] || fatal[3])
console.log(`%cPHP Errors :\\n\\n%c${fatal[1].replace(/<[^>]+>/g, "")}\\n\\n${fatal[3].replace(/<[^>]+>/g, "")}`, "font-size: 2em; color: red;", "font-size: 1em; color: auto;")
alert("Error : " + fatal[2])
}
else {
alert("Error : Bad regex")
console.log(result)
}
}
}
function openDir(dir, disableFocus = false, disableLoading = false, order = "", desc = "") {
unselectElements()
timeDirOpened = Date.now()
if(order !== "")
order = "&order=" + order
if(desc !== "") {
if(desc === false)
desc = "&desc=0"
else
desc = "&desc=1"
}
ajaxRequest("POST", "", `${Date.now()}&dir=${dir}${order}${desc}`, result => {
if(result !== "false") {
showElements(result, disableFocus)
let nbHistory = history.length
if(nbHistory === 0) {
history.push(dir)
btnForward.className = "disabled"
btnBack.className = "disabled"
if(history.length > historyMax)
history.splice(0, 1)
}
else {
if(dir !== history[nbHistory - historyLevel - 1]) /* ISN’T A REFRESH */ {
if(historyLevel > 0) {
for(let i = 0; i < historyLevel; i++) {
history.splice(nbHistory - 1, 1)
nbHistory--
}
}
history.push(dir)
historyLevel = 0
btnForward.className = "disabled"
btnBack.className = ""
if(history.length > historyMax)
history.splice(0, 1)
}
}
}
else {
token = ""
contents.style.display = "none"
connexion.style.display = "flex"
}
}, disableLoading)
}
openDir(currentPath)
setInterval(() => {
if(disableAutoRefresh === false && timeDirOpened < Date.now() - BtwRefreshesMs)
openDir(currentPath, true, true)
}, checkIntervMs)
/* SET SETTINGS */
function changeView(oldView, newView, doRequest = true) {
typeView = newView
if(oldView !== newView) {
if(oldView !== 0)
elements.classList.remove("view" + oldView)
elements.classList.add("view" + newView)
}
if(doRequest !== false)
ajaxRequest("POST", "", `${Date.now()}&set_settings=true&view=${typeView}&token=${token}`, false, true)
}
function changeTypeTrash(type) {
typeTrash = type
ajaxRequest("POST", "", `${Date.now()}&set_settings=true&trash=${type}&token=${token}`, false, true)
openDir(currentPath)
}
function changeTypeUploadExists(type) {
typeUploadExists = type
ajaxRequest("POST", "", `${Date.now()}&set_settings=true&upload_exists=${type}&token=${token}`, false, true)
}
function changeTypeCopyMoveExists(type) {
typeCopyMoveExists = type
ajaxRequest("POST", "", `${Date.now()}&set_settings=true&copy_move_exists=${type}&token=${token}`, false, true)
}
/* GET SETTINGS */
ajaxRequest("GET", "", `${Date.now()}&get_settings=true`, result => {
const foundSrvOnWindows = result.match(/\\[server_on_windows\\]/)
if(foundSrvOnWindows)
srvOnWindows = true
const foundView = result.match(/\\[view=([0-9])\\]/)
if(foundView)
changeView(typeView, parseInt(foundView[1], 10, false))
const foundTrash = result.match(/\\[trash=([0-9])\\]/)
if(foundTrash)
typeTrash = parseInt(foundTrash[1], 10)
const foundUploadExists = result.match(/\\[upload_exists=([0-9])\\]/)
if(foundUploadExists)
typeUploadExists = parseInt(foundUploadExists[1], 10)
const foundCopyMoveExists = result.match(/\\[copy_move_exists=([0-9])\\]/)
if(foundCopyMoveExists)
typeCopyMoveExists = parseInt(foundCopyMoveExists[1], 10)
}, true)
/* GET UPLOAD SIZES */
function getUploadSizes(callback = false) {
if(uploadMaxFileSize === 0 || uploadMaxTotalSize === 0) {
ajaxRequest("GET", "", `${Date.now()}&get_upload_sizes`, result => {
const found = result.match(/\\[max_upload_sizes=([0-9]+)\\|([0-9]+)\\]/)
if(found) {
uploadMaxFileSize = parseInt(found[1], 10)
uploadMaxTotalSize = parseInt(found[2], 10)
if(callback !== false) {
if(uploadMaxFileSize === 0 || uploadMaxTotalSize === 0)
callback(false)
else
callback(true)
}
}
else {
console.log("%cError : %cUnable to access upload sizes", "color: red;", "color: auto;")
if(callback !== false)
callback(false)
}
})
}
else if(callback !== false)
callback(true)
}
getUploadSizes()
/* OTHER FUNCTIONS */
function returnObjInArr(arr, val, param, returnBoolean = false, insensible = false)
{
if(insensible === true)
val = val.toLowerCase()
let ret = false
arr.forEach(el => {
let retTmp = true
if(returnBoolean === false)
retTmp = el
if((insensible === true && el[param].toLowerCase() === val) || (insensible !== true && el[param] === val))
ret = retTmp
})
return ret
}
function removeObjsInArr(arr, val, param, insensible = false)
{
if(insensible === true)
val = val.toLowerCase()
for(let i = 0; i < arr.length; i++)
{
if((insensible === true && arr[i][param].toLowerCase() === val) || (insensible !== true && arr[i][param] === val))
{
arr.splice(i, 1)
i--
}
}
}
function checkReqRep(request, wish) {
ajaxRequest("POST", "", request, result => {
if(result === wish)
openDir(currentPath)
else {
openDir(currentPath)
openBox("alert", "Error : <b>" + result + "</b>", "err")
}
})
}
function isOnCoords(coordsFromX, coordsFromY, coordsToX, coordsToY, objFromX, objFromY, objToX, objToY) {
if(
objFromX >= coordsFromX &&
objFromX <= coordsToX &&
objToX >= coordsFromX &&
objToX <= coordsToX &&
objFromY >= coordsFromY &&
objFromY <= coordsToY &&
objToY >= coordsFromY &&
objToY <= coordsToY
)
return true
return false
}
function setCursorSelection(startX, startY, endX, endY) {
let width = 0
let height = 0
let fromLeft = 0
let fromTop = 0
if(startX > endX) {
fromLeft = endX
width = startX - endX
}
else {
fromLeft = startX
width = endX - startX
}
if(startY < endY) {
fromTop = startY
height = endY - startY
}
else {
fromTop = endY
height = startY - endY
}
selection.style.width = width + "px"
selection.style.height = height + "px"
selection.style.left = fromLeft + "px"
selection.style.top = fromTop + "px"
}
');
elseif(isset($_GET['boxes']))
exit('/* CONTEXT MENU */
function posMenu(event = false) {
const menuWidth = popupMenu.offsetWidth
const menuHeight = popupMenu.offsetHeight
if(event.clientX + menuWidth > window.innerWidth) {
if(event.clientX - menuWidth < 0)
popupMenu.style.left = "0px"
else
popupMenu.style.left = (event.clientX - menuWidth) + "px"
}
else
popupMenu.style.left = event.clientX + "px"
if(event.clientY + menuHeight > window.innerHeight) {
if(event.clientY - menuHeight < 0)
popupMenu.style.top = "0px"
else
popupMenu.style.top = (event.clientY - menuHeight) + "px"
}
else
popupMenu.style.top = event.clientY + "px"
}
function openMenu(html, ev) {
popupMenu.style.display = "none"
popupMenu.innerHTML = html
setTimeout(() => {
popupMenu.style.display = "flex"
posMenu(ev)
try {
popupMenu.querySelector("span").addEventListener("click", () => {
openMenu(html, ev)
})
}
catch {}
}, delayMenuMs)
}
/* POPUP BOX */
function closeBox() {
popupBox.innerHTML = ""
popupMask.style.display = "none"
popupBox.style.display = "none"
}
function showBox(txt, icon, inputs, buttons, noForm = true, callback = false) {
let html = `<div class="popupBox">
<div class="n1">
<div class="n3">
<span class="icon ${icon}"></span>
<span class="txt">${txt}</span>
</div>
</div>
${inputs}
<div class="n2">
${buttons}
</div>
</div>`
if(noForm !== true)
html = "<form>\\n" + html + "</form>"
popupBox.innerHTML = html
popupMask.style.display = "block"
popupBox.style.display = "block"
setTimeout(() => {
if(callback !== false)
callback()
}, delayMenuMs)
}
function openBox(type, vals, icon = null, callback = false) {
setTimeout(() => {
if(type === "alert") {
if(icon === null)
icon = "info"
let txt = vals
let btn = "Ok"
if(typeof(vals) !== "string") {
if(vals.txt)
txt = vals.txt
if(vals.btn)
btn = vals.btn
}
showBox(txt, icon, `<input type="text" class="hidden" value="">`, `<button>${btn}</button>`, false, () => {
const input = popupBox.querySelector("input")
input.focus()
popupBox.querySelector("button").addEventListener("click", ev => {
if(callback !== false)
callback()
closeBox()
ev.preventDefault()
})
})
}
else if(type === "confirm") {
if(icon === null)
icon = "ask"
let txt = vals
let btnOk = "Yes"
let btnNo = "No"
if(typeof(vals) !== "string") {
if(vals.txt)
txt = vals.txt
if(vals.btnOk)
btnOk = vals.btnOk
if(vals.btnNo)
btnNo = vals.btnNo
}
showBox(txt, icon, "", `<button id="y">${btnOk}</button>\\n<button id="n">${btnNo}</button>`, true, () => {
popupBox.querySelector("button#y").addEventListener("click", ev => {
if(callback !== false)
callback()
closeBox()
ev.preventDefault()
})
popupBox.querySelector("button#n").addEventListener("click", ev => {
closeBox()
ev.preventDefault()
})
})
}
else if(type === "prompt") {
if(icon === null)
icon = "ask"
let txt = vals
let value = ""
let btnOk = "Ok"
let btnNo = "Cancel"
if(typeof(vals) !== "string") {
if(vals.txt)
txt = vals.txt
if(vals.value)
value = vals.value
if(vals.btnOk)
btnOk = vals.btnOk
if(vals.btnNo)
btnNo = vals.btnNo
}
showBox(txt, icon, `<input type="text" value="${value}">`, `<button id="y">${btnOk}</button>\\n<button id="n">${btnNo}</button>`, false, () => {
const input = popupBox.querySelector("input")
input.focus()
const tmp = input.value
input.value = ""
input.value = tmp
popupBox.querySelector("button#y").addEventListener("click", ev => {
if(callback !== false)
callback(input.value)
closeBox()
ev.preventDefault()
})
popupBox.querySelector("button#n").addEventListener("click", ev => {
closeBox()
ev.preventDefault()
})
})
}
else if(type === "multi") {
if(icon === null)
icon = "ask"
let txt = ""
let listInputs = vals
if(typeof(vals) !== "string") {
if(vals.txt)
txt = vals.txt
if(vals.inputs)
listInputs = vals.inputs
}
let inputsHTML = ""
const founds = [...listInputs.matchAll(/\\[([^\\]]+)\\]([^\\[]+)/g)]
founds.forEach((found, i) => {
if(found[1] === "checkbox")
inputsHTML += `<br>\\n<label><input type="checkbox" value="${i}" style="display: inline-block; width: auto; min-width: auto;"> &nbsp; ${found[2]}</label>`
else
inputsHTML += `<br>\\n<button value="${i}">${found[2]}</button>`
})
showBox(txt, icon, inputsHTML, ``, true, () => {
const listValues = []
popupBox.querySelectorAll("input").forEach(checkbox => {
checkbox.addEventListener("click", () => {
if(checkbox.checked)
listValues.push(parseInt(checkbox.value, 10))
else {
const posCheckbox = listValues.indexOf(parseInt(checkbox.value, 10))
if(posCheckbox !== -1)
listValues.splice(posCheckbox, 1)
}
})
})
popupBox.querySelectorAll("button").forEach(button => {
button.addEventListener("click", ev => {
listValues.push(parseInt(button.value, 10))
if(callback !== false)
callback(listValues)
closeBox()
ev.preventDefault()
})
})
})
}
else if(type === "path") {
if(icon === null)
icon = "path"
let txt = vals
let btnOk = "Ok"
let btnNo = "Cancel"
if(typeof(vals) !== "string") {
if(vals.txt)
txt = vals.txt
if(vals.btnOk)
btnOk = vals.btnOk
if(vals.btnNo)
btnNo = vals.btnNo
}
let pathDecoded
try {
pathDecoded = decodeURIComponent(currentPath)
}
catch {
pathDecoded = currentPath
}
ajaxRequest("POST", "", `${Date.now()}&dir=${currentPath}&tree_only`, result => {
const found = result.match(/^(.*)\\/\\/!tree!\\\\\\\\(.*)\\n\\/\\/!end!\\\\\\\\(.*)$/s)
if(found) {
if(found[1] || found[3])
console.log(`%cPHP Errors :\\n\\n%c${found[1].replace(/<[^>]+>/g, "")}\\n\\n${found[3].replace(/<[^>]+>/g, "")}`, "font-size: 2em; color: red;", "font-size: 1em; color: auto;")
result = found[2]
}
else
result = "Error : <b>Try to refresh site</b>"
showBox(txt, icon, `<div id="boxPath"><div class="list">${result}</div></div><input type="text" id="pathDecoded" value="${pathDecoded}"><div id="boxPathNewDirectory"><input type="text" id="boxPathNameNewDirectory" placeholder="Name of the new directory"><button id="boxPathCreateNewDirectory">Create</button></div><input type="hidden" id="pathEncoded" value="${currentPath}">`, `<button id="y">${btnOk}</button>\\n<button id="n">${btnNo}</button>\\n<button id="c">Create sub-directory</button>`, false, () => {
try {
const boxPath = document.querySelector("#boxPath")
boxPath.scrollTop = boxPath.querySelector(".treeDefault").offsetTop - boxPath.querySelector(".list").offsetTop
}
catch {}
const inputEncoded = popupBox.querySelector("input#pathEncoded")
const inputDecoded = popupBox.querySelector("input#pathDecoded")
const boxPathNewDirectory = popupBox.querySelector("#boxPathNewDirectory")
const boxPathNameNewDirectory = popupBox.querySelector("#boxPathNameNewDirectory")
const boxPathCreateNewDirectory = popupBox.querySelector("#boxPathCreateNewDirectory")
inputDecoded.addEventListener("input", () => {
try {
inputEncoded.value = encodeURIComponent(inputDecoded.value)
}
catch {
inputEncoded.value = inputDecoded.value
}
})
popupBox.querySelector("button#c").addEventListener("click", ev => {
boxPathNewDirectory.style.display = "flex"
boxPathNameNewDirectory.focus()
ev.preventDefault()
})
boxPathCreateNewDirectory.addEventListener("click", ev => {
ajaxRequest("POST", "", `${Date.now()}&new=dir&dir=${inputEncoded.value}&name=${boxPathNameNewDirectory.value}&token=${token}`, result => {
if(result === "created") {
boxPathNameNewDirectory.value = ""
boxPathNavigate(currentPath)
}
else
console.log("%cError : %c" + result, "color: red;", "color: auto;")
})
ev.preventDefault()
})
popupBox.querySelector("button#y").addEventListener("click", ev => {
if(callback !== false)
callback(inputEncoded.value)
closeBox()
ev.preventDefault()
})
popupBox.querySelector("button#n").addEventListener("click", ev => {
closeBox()
ev.preventDefault()
})
})
})
}
else if(type === "edit") {
if(icon === null)
icon = "edit"
let txt = `Edit <b>ʿ${vals.name}ʿ</b> :`
let btnOk = "Ok"
let btnNo = "Cancel"
if(vals.txt)
txt = vals.txt
if(vals.btnOk)
btnOk = vals.btnOk
if(vals.btnNo)
btnNo = vals.btnNo
ajaxRequest("POST", "", `${Date.now()}&read_file=${vals.nameEncoded}&dir=${currentPath}&token=${token}`, result => {
if(result === "[file_edit_not_found]")
openBox("alert", `Error : <b>File not found</b>`, "err")
else
showBox(txt, icon, `<textarea>${result}</textarea>`, `<button id="y">${btnOk}</button>\\n<button id="n">${btnNo}</button>`, false, () => {
const input = popupBox.querySelector("textarea")
input.focus()
const tmp = input.value
input.value = ""
input.value = tmp
popupBox.querySelector("button#y").addEventListener("click", ev => {
ajaxRequest("POST", "", `${Date.now()}&edit_file=${encodeURIComponent(input.value)}&dir=${currentPath}&name=${vals.nameEncoded}&token=${token}`, result => {
if(result === "edited")
openDir(currentPath, true, true)
else {
openDir(currentPath, true, true)
openBox("alert", "Error : <b>" + result + "</b>", "err")
}
})
closeBox()
ev.preventDefault()
})
popupBox.querySelector("button#n").addEventListener("click", ev => {
closeBox()
ev.preventDefault()
})
})
})
}
else if(type === "chmods") {
if(icon === null)
icon = "lock"
let txt = `Change chmods for <b>ʿ${vals.name}ʿ</b> :`
let files = false
let btnOk = "Ok"
let btnNo = "Cancel"
if(vals.txt)
txt = vals.txt
if(vals.files)
files = JSON.parse(decodeURIComponent(vals.files))
if(vals.btnOk)
btnOk = vals.btnOk
if(vals.btnNo)
btnNo = vals.btnNo
function chmods2checkboxes(chmods, el, input) {
chmods = chmods.toString()
while(chmods.length < 4) {
chmods = "0" + chmods
input.value = chmods
}
const octs = []
for(let i = 0; i < 4; i++) {
let nb = parseInt(chmods[i], 10)
let r = w = x = false
if(nb >= 4) {
r = true
nb -= 4
}
if(nb >= 2) {
w = true
nb -= 2
}
if(nb == 1)
x = true
octs.push([r, w, x])
}
octs.forEach((oct, i) => {
oct.forEach((val, j) => {
if(val === true)
el.querySelector(`#chmod_${i}_${j}`).checked = "checked"
else
el.querySelector(`#chmod_${i}_${j}`).checked = null
})
})
}
function checkboxes2chmods(el, input) {
let chmods = ""
for(let i = 0; i < 4; i++) {
let val = 0
if(el.querySelector(`#chmod_${i}_0`).checked !== false)
val += 4
if(el.querySelector(`#chmod_${i}_1`).checked !== false)
val += 2
if(el.querySelector(`#chmod_${i}_2`).checked !== false)
val ++
chmods += val.toString()
}
input.value = chmods
}
function showChmodBox(chmodDef, callback) {
showBox(txt, icon, `<div id="boxChmods">
<div></div>
<div></div>
<div></div>
<div class="center">Owner :</div>
<div class="center">Group :</div>
<div class="center">Others :</div>
<div><label for="chmod_0_0">Set UID :</label></div>
<div><input type="checkbox" id="chmod_0_0"></div>
<div>Read :</div>
<div class="center"><input type="checkbox" id="chmod_1_0"></div>
<div class="center"><input type="checkbox" id="chmod_2_0"></div>
<div class="center"><input type="checkbox" id="chmod_3_0"></div>
<div><label for="chmod_0_1">Set GID :</label></div>
<div><input type="checkbox" id="chmod_0_1"></div>
<div>Write :</div>
<div class="center"><input type="checkbox" id="chmod_1_1"></div>
<div class="center"><input type="checkbox" id="chmod_2_1"></div>
<div class="center"><input type="checkbox" id="chmod_3_1"></div>
<div><label for="chmod_0_2">Sticky bit :</label></div>
<div><input type="checkbox" id="chmod_0_2"></div>
<div>Execute :</div>
<div class="center"><input type="checkbox" id="chmod_1_2"></div>
<div class="center"><input type="checkbox" id="chmod_2_2"></div>
<div class="center"><input type="checkbox" id="chmod_3_2"></div>
</div>
<input type="text" value="${chmodDef}">
`, `<button id="y">${btnOk}</button>\\n<button id="n">${btnNo}</button>`, false, () => {
const input = popupBox.querySelector("input[type=\\"text\\"]")
chmods2checkboxes(chmodDef, popupBox, input)
input.addEventListener("change", () => {
chmods2checkboxes(input.value, popupBox, input)
})
popupBox.querySelectorAll("input[type=\\"checkbox\\"]").forEach(checkbox => {
checkbox.addEventListener("click", () => {
checkboxes2chmods(popupBox, input)
})
})
popupBox.querySelector("button#y").addEventListener("click", ev => {
callback(input.value)
closeBox()
ev.preventDefault()
})
popupBox.querySelector("button#n").addEventListener("click", ev => {
closeBox()
ev.preventDefault()
})
})
}
if(files !== false) {
showChmodBox("0777", changeResult => {
checkReqRep(`${Date.now()}&set_multiple_chmods=${changeResult}&files=${formatMultiple(files)}&token=${token}`, "chmodeds")
})
}
else {
ajaxRequest("POST", "", `${Date.now()}&get_chmods=${vals.nameEncoded}&dir=${currentPath}&token=${token}`, result => {
const found = result.match(/\\[chmods=([0-9]+)\\]/)
if(found) {
showChmodBox(found[1], changeResult => {
checkReqRep(`${Date.now()}&set_chmods=${changeResult}&dir=${currentPath}&name=${vals.nameEncoded}&token=${token}`, "chmoded")
})
}
else
openBox("alert", `Error : <b>${result}</b>`, "err")
})
}
}
else {
alert("Error : Unknown type")
return false
}
}, delayMenuMs)
}
function boxPathNavigate(dir) {
document.querySelector("#popupBox input#pathEncoded").value = dir
const inputDecoded = document.querySelector("#popupBox input#pathDecoded")
try {
inputDecoded.value = decodeURIComponent(dir)
}
catch {
inputDecoded.value = dir
}
ajaxRequest("POST", "", `${Date.now()}&dir=${dir}&tree_only`, result => {
const found = result.match(/^(.*)\\/\\/!tree!\\\\\\\\(.*)\\n\\/\\/!end!\\\\\\\\(.*)$/s)
if(found) {
if(found[1] || found[3])
console.log(`%cPHP Errors :\\n\\n%c${found[1].replace(/<[^>]+>/g, "")}\\n\\n${found[3].replace(/<[^>]+>/g, "")}`, "font-size: 2em; color: red;", "font-size: 1em; color: auto;")
result = found[2]
}
else
result = "Error : <b>Try to refresh site</b>"
document.querySelector("#boxPath .list").innerHTML = result
})
}
');
elseif(isset($_GET['elements']))
exit('/* CLICK ON ELEMENTS */
function selectElement(el, nameEncoded) {
disableAutoRefresh = true
if(!returnObjInArr(selectedElements, nameEncoded, "nameEncoded", true) && (currentPath !== "." || nameEncoded !== "Trash")) {
el.classList.add("selected")
selectedElements.push({
element : el,
nameEncoded : nameEncoded
})
}
}
function selectAllElements() {
elements.querySelectorAll("a").forEach(element => {
selectElement(element, element.getAttribute("data-name-enc"))
})
}
function unselectElement(nameEncoded) {
returnObjInArr(selectedElements, nameEncoded, "nameEncoded").element.classList.remove("selected")
removeObjsInArr(selectedElements, nameEncoded, "nameEncoded")
if(selectedElements.length === 0)
disableAutoRefresh = false
}
function startClic(el, nameEncoded) {
if(event.button === 2)
rightClicOnEl = true
else if(event.button === 0 && isOnMobile === false) {
mouseDownOnEl = true
rightClicOnEl = false
if(selectedElements.length > 0 && returnObjInArr(selectedElements, nameEncoded, "nameEncoded", true)) {
document.body.querySelectorAll("a").forEach(element => {
if((element.classList.contains("dir") || element.classList.contains("linkdir") || element.classList.contains("dirOpen") || element.classList.contains("trash")) && !returnObjInArr(selectedElements, element.getAttribute("data-name-enc"), "nameEncoded", true))
element.classList.add("unselected")
})
tryToMove = el
}
}
}
function endClic(el, pathEncoded, nameEncoded, webUrl, isLink = false, isFile = false) {
if(event.button === 0 && selectWcursor === false) {
mouseUpOnEl = true
if(isOnMobile === false && isFile === false && tryToMove !== false && tryToMove !== el && !returnObjInArr(selectedElements, nameEncoded, "nameEncoded", true))
moveMultiple(pathEncoded)
else {
if(event.ctrlKey === true) {
if(selectedElements.length === 0 || !returnObjInArr(selectedElements, nameEncoded, "nameEncoded", true))
selectElement(el, nameEncoded)
else
unselectElement(nameEncoded)
}
else if(event.shiftKey === true) {
if(selectedElements.length === 1 && !returnObjInArr(selectedElements, nameEncoded, "nameEncoded", true)) {
let foundFirst = false
elements.querySelectorAll("a").forEach(element => {
if(foundFirst === false && element.getAttribute("data-name-enc") === selectedElements[0].nameEncoded )
foundFirst = true
else if(foundFirst === false && element.getAttribute("data-name-enc") === nameEncoded ) {
foundFirst = true
selectElement(el, nameEncoded)
}
else if(foundFirst === true && element.getAttribute("data-name-enc") === selectedElements[0].nameEncoded )
foundFirst = false
else if(foundFirst === true && element.getAttribute("data-name-enc") === nameEncoded ) {
foundFirst = false
selectElement(el, nameEncoded)
}
else if(foundFirst === true)
selectElement(element, element.getAttribute("data-name-enc"))
})
}
else {
if(selectedElements.length === 0 || !returnObjInArr(selectedElements, nameEncoded, "nameEncoded", true))
selectElement(el, nameEncoded)
else
unselectElement(nameEncoded)
}
}
else if(selectedElements.length > 0 && returnObjInArr(selectedElements, nameEncoded, "nameEncoded", true))
menuMultiple()
else if(selectedElements.length > 0 || (isOnMobile === true && popupMenu.style.display === "flex"))
unselectElements()
else {
unselectElements()
if(isFile === false)
openDir(pathEncoded, isLink)
else
menuFile(pathEncoded, nameEncoded, webUrl, isLink)
}
}
tryToMove = false
}
}
function endClicTree(pathEncoded, nameEncoded, moveForbidden = false) {
if(selectWcursor === false) {
if(isOnMobile === false && event.button === 0 && tryToMove !== false && moveForbidden === false) {
mouseUpOnEl = true
moveMultiple(pathEncoded)
}
else
openDir(pathEncoded)
tryToMove = false
}
}
function rightClic(pathEncoded, nameEncoded, fullPathEncoded, webUrl, isLink = false) {
mouseUpOnEl = true
if(isOnMobile === false && selectedElements.length > 0 && returnObjInArr(selectedElements, nameEncoded, "nameEncoded", true))
menuMultiple()
else {
unselectElements()
if(fullPathEncoded !== false)
menuDir(pathEncoded, nameEncoded, fullPathEncoded, webUrl, isLink)
else
menuFile(pathEncoded, nameEncoded, webUrl, isLink)
}
}
/* CONTEXT MENUS */
function menuDir(pathEncoded, nameEncoded, fullPathEncoded, webUrl, isLink = false) {
const name = decodeURIComponent(nameEncoded).replace(\'\\\'\', \'&#039;\').replace(\'"\', \'&quot;\')
if(typeTrash !== 0 && pathEncoded.substring(0, 8) === "Trash%2F") {
if(isLink === false)
openMenu(`<span>${name}/</span>
<a onclick="openDir(\'${fullPathEncoded}\')">Open</a>
<a onclick="openBox(\'prompt\', { txt: \'Enter the new name for <b>ʿ${name}/ʿ</b> :\', value: \'${name}\' }, null, inputName => { renameElement(\'${pathEncoded}\', \'${nameEncoded}\', inputName) })">Rename</a>
<a onclick="copy = [{ pathEncoded: \'${currentPath}\', nameEncoded: \'${nameEncoded}\' }]; copyNotCut = true">Copy</a>
<a onclick="copy = [{ pathEncoded: \'${currentPath}\', nameEncoded: \'${nameEncoded}\' }]; copyNotCut = false">Cut</a>
<a onclick="duplicateElement(\'${pathEncoded}\', \'${nameEncoded}\')">Duplicate</a>
<a onclick="openBox(\'path\', \'Copy <b>ʿ${name}ʿ/</b> to :\', null, inputPath => { copyElement(\'${pathEncoded}\', \'${nameEncoded}\', inputPath) })">Copy to</a>
<a onclick="openBox(\'path\', \'Move <b>ʿ${name}ʿ/</b> to :\', null, inputPath => { moveElement(\'${pathEncoded}\', \'${nameEncoded}\', inputPath) })">Move to</a>
<a onclick="openBox(\'confirm\', \'Permanently delete the directory <b>ʿ${name}/ʿ</b> ?\', \'warn\', () => { permaDeleteElement(\'${pathEncoded}\', \'${nameEncoded}\') })">Permanently delete</a>
<a onclick="openBox(\'chmods\', { name: \'${name}/\', nameEncoded: \'${nameEncoded}\' })">Change chmods</a>
`, event)
else
openMenu(`<span>${name}</span>
<span class="link">&#9755; &nbsp; ${isLink}</span>
<a onclick="openDir(\'${fullPathEncoded}\')">Open</a>
<a onclick="openBox(\'prompt\', { txt: \'Enter the new name for <b>ʿ${name}ʿ</b> :\', value: \'${name}\' }, null, inputName => { renameElement(\'${pathEncoded}\', \'${nameEncoded}\', inputName) })">Rename</a>
<a onclick="copy = [{ pathEncoded: \'${currentPath}\', nameEncoded: \'${nameEncoded}\' }]; copyNotCut = true">Copy</a>
<a onclick="copy = [{ pathEncoded: \'${currentPath}\', nameEncoded: \'${nameEncoded}\' }]; copyNotCut = false">Cut</a>
<a onclick="duplicateElement(\'${pathEncoded}\', \'${nameEncoded}\')">Duplicate</a>
<a onclick="openBox(\'path\', \'Copy <b>ʿ${name}ʿ/</b> to :\', null, inputPath => { copyElement(\'${pathEncoded}\', \'${nameEncoded}\', inputPath) })">Copy to</a>
<a onclick="openBox(\'path\', \'Move <b>ʿ${name}ʿ/</b> to :\', null, inputPath => { moveElement(\'${pathEncoded}\', \'${nameEncoded}\', inputPath) })">Move to</a>
<a onclick="openBox(\'confirm\', \'Permanently delete the link <b>ʿ${name}ʿ</b> ?\', \'warn\', () => { permaDeleteElement(\'${pathEncoded}\', \'${nameEncoded}\') })">Permanently delete</a>
<a onclick="openBox(\'chmods\', { name: \'${name}\', nameEncoded: \'${nameEncoded}\' })">Change chmods</a>
`, event)
}
else if(typeTrash !== 0 && pathEncoded === "." && nameEncoded === "Trash")
openMenu(`<span>Trash</span>
<a onclick="openDir(\'Trash%2F\')">Open</a>
<a onclick="openBox(\'confirm\', \'Empty trash ?\', \'warn\', () => { emptyTrash() })">Empty trash</a>
`, event)
else {
if(webUrl === false)
webUrl = ""
else
webUrl = `<a onclick="window.open(\'${webUrl}\')">See web version</a>`
if(isLink === false)
openMenu(`<span>${name}/</span>
<a onclick="openDir(\'${fullPathEncoded}\')">Open</a>
${webUrl}
<a onclick="openBox(\'prompt\', { txt: \'Enter the new name for <b>ʿ${name}/ʿ</b> :\', value: \'${name}\' }, null, inputName => { renameElement(\'${pathEncoded}\', \'${nameEncoded}\', inputName) })">Rename</a>
<a onclick="copy = [{ pathEncoded: \'${currentPath}\', nameEncoded: \'${nameEncoded}\' }]; copyNotCut = true">Copy</a>
<a onclick="copy = [{ pathEncoded: \'${currentPath}\', nameEncoded: \'${nameEncoded}\' }]; copyNotCut = false">Cut</a>
<a onclick="duplicateElement(\'${pathEncoded}\', \'${nameEncoded}\')">Duplicate</a>
<a onclick="openBox(\'path\', \'Copy <b>ʿ${name}ʿ/</b> to :\', null, inputPath => { copyElement(\'${pathEncoded}\', \'${nameEncoded}\', inputPath) })">Copy to</a>
<a onclick="openBox(\'path\', \'Move <b>ʿ${name}ʿ/</b> to :\', null, inputPath => { moveElement(\'${pathEncoded}\', \'${nameEncoded}\', inputPath) })">Move to</a>
<a onclick="openBox(\'confirm\', \'Delete the directory <b>ʿ${name}/ʿ</b> ?\', \'warn\', () => { deleteElement(\'${pathEncoded}\', \'${nameEncoded}\') })">Delete</a>
<a onclick="openBox(\'chmods\', { name: \'${name}/\', nameEncoded: \'${nameEncoded}\' })">Change chmods</a>
`, event)
else
openMenu(`<span>${name}</span>
<span class="link">&#9755; &nbsp; ${isLink}</span>
<a onclick="openDir(\'${fullPathEncoded}\')">Open</a>
${webUrl}
<a onclick="openBox(\'prompt\', { txt: \'Enter the new name for <b>ʿ${name}ʿ</b> :\', value: \'${name}\' }, null, inputName => { renameElement(\'${pathEncoded}\', \'${nameEncoded}\', inputName) })">Rename</a>
<a onclick="copy = [{ pathEncoded: \'${currentPath}\', nameEncoded: \'${nameEncoded}\' }]; copyNotCut = true">Copy</a>
<a onclick="copy = [{ pathEncoded: \'${currentPath}\', nameEncoded: \'${nameEncoded}\' }]; copyNotCut = false">Cut</a>
<a onclick="duplicateElement(\'${pathEncoded}\', \'${nameEncoded}\')">Duplicate</a>
<a onclick="openBox(\'path\', \'Copy <b>ʿ${name}ʿ/</b> to :\', null, inputPath => { copyElement(\'${pathEncoded}\', \'${nameEncoded}\', inputPath) })">Copy to</a>
<a onclick="openBox(\'path\', \'Move <b>ʿ${name}ʿ/</b> to :\', null, inputPath => { moveElement(\'${pathEncoded}\', \'${nameEncoded}\', inputPath) })">Move to</a>
<a onclick="openBox(\'confirm\', \'Delete the link <b>ʿ${name}ʿ</b> ?\', \'warn\', () => { deleteElement(\'${pathEncoded}\', \'${nameEncoded}\') })">Delete</a>
<a onclick="openBox(\'chmods\', { name: \'${name}\', nameEncoded: \'${nameEncoded}\' })">Change chmods</a>
`, event)
}
}
function menuFile(pathEncoded, nameEncoded, webUrl, isLink = false) {
const name = decodeURIComponent(nameEncoded).replace(\'\\\'\', \'&#039;\').replace(\'"\', \'&quot;\')
if(typeTrash !== 0 && pathEncoded.substring(0, 8) === "Trash%2F") {
if(isLink === false)
openMenu(`<span>${name}</span>
<a onclick="downloadElement(\'${pathEncoded}\', \'${nameEncoded}\')">Download</a>
<a onclick="openBox(\'edit\', { name: \'${name}\', nameEncoded: \'${nameEncoded}\' })">Edit</a>
<a onclick="openBox(\'prompt\', { txt: \'Enter the new name for <b>ʿ${name}ʿ</b> :\', value: \'${name}\' }, null, inputName => { renameElement(\'${pathEncoded}\', \'${nameEncoded}\', inputName) })">Rename</a>
<a onclick="copy = [{ pathEncoded: \'${currentPath}\', nameEncoded: \'${nameEncoded}\' }]; copyNotCut = true">Copy</a>
<a onclick="copy = [{ pathEncoded: \'${currentPath}\', nameEncoded: \'${nameEncoded}\' }]; copyNotCut = false">Cut</a>
<a onclick="duplicateElement(\'${pathEncoded}\', \'${nameEncoded}\')">Duplicate</a>
<a onclick="openBox(\'path\', \'Copy <b>ʿ${name}ʿ</b> to :\', null, inputPath => { copyElement(\'${pathEncoded}\', \'${nameEncoded}\', inputPath) })">Copy to</a>
<a onclick="openBox(\'path\', \'Move <b>ʿ${name}ʿ</b> to :\', null, inputPath => { moveElement(\'${pathEncoded}\', \'${nameEncoded}\', inputPath) })">Move to</a>
<a onclick="openBox(\'confirm\', \'Permanently delete the file <b>ʿ${name}ʿ</b> ?\', \'warn\', () => { permaDeleteElement(\'${pathEncoded}\', \'${nameEncoded}\') })">Permanently delete</a>
<a onclick="openBox(\'chmods\', { name: \'${name}\', nameEncoded: \'${nameEncoded}\' })">Change chmods</a>
`, event)
else
openMenu(`<span>${name}</span>
<span class="link">&#9755; &nbsp; ${isLink}</span>
<a onclick="openBox(\'prompt\', { txt: \'Enter the new name for <b>ʿ${name}ʿ</b> :\', value: \'${name}\' }, null, inputName => { renameElement(\'${pathEncoded}\', \'${nameEncoded}\', inputName) })">Rename</a>
<a onclick="copy = [{ pathEncoded: \'${currentPath}\', nameEncoded: \'${nameEncoded}\' }]; copyNotCut = true">Copy</a>
<a onclick="copy = [{ pathEncoded: \'${currentPath}\', nameEncoded: \'${nameEncoded}\' }]; copyNotCut = false">Cut</a>
<a onclick="duplicateElement(\'${pathEncoded}\', \'${nameEncoded}\')">Duplicate</a>
<a onclick="openBox(\'path\', \'Copy <b>ʿ${name}ʿ</b> to :\', null, inputPath => { copyElement(\'${pathEncoded}\', \'${nameEncoded}\', inputPath) })">Copy to</a>
<a onclick="openBox(\'path\', \'Move <b>ʿ${name}ʿ</b> to :\', null, inputPath => { moveElement(\'${pathEncoded}\', \'${nameEncoded}\', inputPath) })">Move to</a>
<a onclick="openBox(\'confirm\', \'Permanently delete the link <b>ʿ${name}ʿ</b> ?\', \'warn\', () => { permaDeleteElement(\'${pathEncoded}\', \'${nameEncoded}\') })">Permanently delete</a>
<a onclick="openBox(\'chmods\', { name: \'${name}\', nameEncoded: \'${nameEncoded}\' })">Change chmods</a>
`, event)
}
else {
if(webUrl === false)
webUrl = ""
else
webUrl = `<a onclick="window.open(\'${webUrl}\')">See web version</a>`
if(isLink === false)
openMenu(`<span>${name}</span>
<a onclick="downloadElement(\'${pathEncoded}\', \'${nameEncoded}\')">Download</a>
${webUrl}
<a onclick="openBox(\'edit\', { name: \'${name}\', nameEncoded: \'${nameEncoded}\' })">Edit</a>
<a onclick="openBox(\'prompt\', { txt: \'Enter the new name for <b>ʿ${name}ʿ</b> :\', value: \'${name}\' }, null, inputName => { renameElement(\'${pathEncoded}\', \'${nameEncoded}\', inputName) })">Rename</a>
<a onclick="copy = [{ pathEncoded: \'${currentPath}\', nameEncoded: \'${nameEncoded}\' }]; copyNotCut = true">Copy</a>
<a onclick="copy = [{ pathEncoded: \'${currentPath}\', nameEncoded: \'${nameEncoded}\' }]; copyNotCut = false">Cut</a>
<a onclick="duplicateElement(\'${pathEncoded}\', \'${nameEncoded}\')">Duplicate</a>
<a onclick="openBox(\'path\', \'Copy <b>ʿ${name}ʿ</b> to :\', null, inputPath => { copyElement(\'${pathEncoded}\', \'${nameEncoded}\', inputPath) })">Copy to</a>
<a onclick="openBox(\'path\', \'Move <b>ʿ${name}ʿ</b> to :\', null, inputPath => { moveElement(\'${pathEncoded}\', \'${nameEncoded}\', inputPath) })">Move to</a>
<a onclick="openBox(\'confirm\', \'Delete the file <b>ʿ${name}ʿ</b> ?\', \'warn\', () => { deleteElement(\'${pathEncoded}\', \'${nameEncoded}\') })">Delete</a>
<a onclick="openBox(\'chmods\', { name: \'${name}\', nameEncoded: \'${nameEncoded}\' })">Change chmods</a>
`, event)
else
openMenu(`<span>${name}</span>
<span class="link">&#9755; &nbsp; ${isLink}</span>
${webUrl}
<a onclick="openBox(\'prompt\', { txt: \'Enter the new name for <b>ʿ${name}ʿ</b> :\', value: \'${name}\' }, null, inputName => { renameElement(\'${pathEncoded}\', \'${nameEncoded}\', inputName) })">Rename</a>
<a onclick="copy = [{ pathEncoded: \'${currentPath}\', nameEncoded: \'${nameEncoded}\' }]; copyNotCut = true">Copy</a>
<a onclick="copy = [{ pathEncoded: \'${currentPath}\', nameEncoded: \'${nameEncoded}\' }]; copyNotCut = false">Cut</a>
<a onclick="duplicateElement(\'${pathEncoded}\', \'${nameEncoded}\')">Duplicate</a>
<a onclick="openBox(\'path\', \'Copy <b>ʿ${name}ʿ</b> to :\', null, inputPath => { copyElement(\'${pathEncoded}\', \'${nameEncoded}\', inputPath) })">Copy to</a>
<a onclick="openBox(\'path\', \'Move <b>ʿ${name}ʿ</b> to :\', null, inputPath => { moveElement(\'${pathEncoded}\', \'${nameEncoded}\', inputPath) })">Move to</a>
<a onclick="openBox(\'confirm\', \'Delete the link <b>ʿ${name}ʿ</b> ?\', \'warn\', () => { deleteElement(\'${pathEncoded}\', \'${nameEncoded}\') })">Delete</a>
<a onclick="openBox(\'chmods\', { name: \'${name}\', nameEncoded: \'${nameEncoded}\' })">Change chmods</a>
`, event)
}
}
function menuMultiple() {
const nbSelectedEls = selectedElements.length
let name = nbSelectedEls + " selected element"
if(nbSelectedEls > 1)
name += "s"
if(typeTrash !== 0 && currentPath.substring(0, 8) === "Trash%2F")
openMenu(`<span>${name}</span>
<a onclick="copy = selectedElements.map(x => { return { pathEncoded: \'${currentPath}\', nameEncoded: x.nameEncoded } }); copyNotCut = true">Copy</a>
<a onclick="copy = selectedElements.map(x => { return { pathEncoded: \'${currentPath}\', nameEncoded: x.nameEncoded } }); copyNotCut = false">Cut</a>
<a onclick="duplicateMultiple()">Duplicate</a>
<a onclick="openBox(\'path\', \'Copy <b>ʿ${name}ʿ</b> to :\', null, inputPath => { copyMultiple(inputPath, \'${encodeURIComponent(JSON.stringify(selectedElements))}\') })">Copy to</a>
<a onclick="openBox(\'path\', \'Move <b>ʿ${name}ʿ</b> to :\', null, inputPath => { moveMultiple(inputPath, \'${encodeURIComponent(JSON.stringify(selectedElements))}\') })">Move to</a>
<a onclick="openBox(\'confirm\', \'Permanently delete <b>ʿ${name}ʿ</b> ?\', \'warn\', () => { permaDeleteMultiple(\'${encodeURIComponent(JSON.stringify(selectedElements))}\') })">Permanently delete</a>
<a onclick="openBox(\'chmods\', { name: \'${name}\', files: \'${encodeURIComponent(JSON.stringify(selectedElements))}\' })">Change chmods</a>
`, event)
else
openMenu(`<span>${name}</span>
<a onclick="copy = selectedElements.map(x => { return { pathEncoded: \'${currentPath}\', nameEncoded: x.nameEncoded } }); copyNotCut = true">Copy</a>
<a onclick="copy = selectedElements.map(x => { return { pathEncoded: \'${currentPath}\', nameEncoded: x.nameEncoded } }); copyNotCut = false">Cut</a>
<a onclick="duplicateMultiple()">Duplicate</a>
<a onclick="openBox(\'path\', \'Copy <b>ʿ${name}ʿ</b> to :\', null, inputPath => { copyMultiple(inputPath, \'${encodeURIComponent(JSON.stringify(selectedElements))}\') })">Copy to</a>
<a onclick="openBox(\'path\', \'Move <b>ʿ${name}ʿ</b> to :\', null, inputPath => { moveMultiple(inputPath, \'${encodeURIComponent(JSON.stringify(selectedElements))}\') })">Move to</a>
<a onclick="openBox(\'confirm\', \'Delete <b>ʿ${name}ʿ</b> ?\', \'warn\', () => { deleteMultiple(\'${encodeURIComponent(JSON.stringify(selectedElements))}\') })">Delete</a>
<a onclick="openBox(\'chmods\', { name: \'${name}\', files: \'${encodeURIComponent(JSON.stringify(selectedElements))}\' })">Change chmods</a>
`, event)
}
/* OTHER ACTIONS */
function downloadElement(pathEncoded, nameEncoded) {
window.open(`?${Date.now()}&download=${nameEncoded}&dir=${pathEncoded}&token=${token}`)
}
function newElement(type, name) {
if(name === "")
openBox("alert", "Error : <b>Name can\'t be empty</b>", "err")
else
checkReqRep(`${Date.now()}&new=${type}&dir=${currentPath}&name=${name}&token=${token}`, "created")
}
function renameElement(pathEncoded, oldName, newName) {
if(newName === "")
openBox("alert", "Error : <b>Name can\'t be empty</b>", "err")
else
checkReqRep(`${Date.now()}&rename=${oldName}&dir=${pathEncoded}&name=${newName}&token=${token}`, "renamed")
}
function duplicateElement(pathEncoded, nameEncoded) {
checkReqRep(`${Date.now()}&duplicate=${nameEncoded}&dir=${pathEncoded}&token=${token}`, "duplicated")
}
function copyElement(pathEncoded, nameEncoded, newPath) {
checkReqRep(`${Date.now()}&copy=${nameEncoded}&dir=${pathEncoded}&path=${newPath}&if_exists=${typeCopyMoveExists}&token=${token}`, "copied")
}
function moveElement(pathEncoded, nameEncoded, newPath) {
checkReqRep(`${Date.now()}&move=${nameEncoded}&dir=${pathEncoded}&path=${newPath}&if_exists=${typeCopyMoveExists}&token=${token}`, "moved")
}
function deleteElement(pathEncoded, nameEncoded) {
checkReqRep(`${Date.now()}&delete=${nameEncoded}&dir=${pathEncoded}&token=${token}`, "deleted")
}
function permaDeleteElement(pathEncoded, nameEncoded) {
checkReqRep(`${Date.now()}&permanently_delete=${nameEncoded}&dir=${pathEncoded}&token=${token}`, "deleted")
}
function formatMultiple(elements, path = false) {
let ret = ""
elements.forEach(element => {
if(path === false)
path = element.pathEncoded
if(path !== ".")
ret += path
ret += element.nameEncoded + "%2F%2F%2F"
})
return ret.substring(0, ret.length - 9)
}
function duplicateMultiple() {
checkReqRep(`${Date.now()}&duplicate_multiple=${formatMultiple(selectedElements, currentPath)}&dir=${currentPath}&token=${token}`, "duplicateds")
}
function copyMultiple(pathEncoded, elements = false) {
if(elements === false)
elements = selectedElements
else
elements = JSON.parse(decodeURIComponent(elements))
checkReqRep(`${Date.now()}&copy_multiple=${formatMultiple(elements, currentPath)}&dir=${pathEncoded}&if_exists=${typeCopyMoveExists}&token=${token}`, "copieds")
}
function moveMultiple(pathEncoded, elements = false) {
if(elements === false)
elements = selectedElements
else
elements = JSON.parse(decodeURIComponent(elements))
if(typeTrash !== 0 && pathEncoded === "Trash%2F")
checkReqRep(`${Date.now()}&trash=${formatMultiple(elements, currentPath)}&token=${token}`, "trasheds")
else
checkReqRep(`${Date.now()}&move_multiple=${formatMultiple(elements, currentPath)}&dir=${pathEncoded}&if_exists=${typeCopyMoveExists}&token=${token}`, "moveds")
}
function deleteMultiple(elements) {
checkReqRep(`${Date.now()}&delete_multiple=${formatMultiple(JSON.parse(decodeURIComponent(elements)), currentPath)}&token=${token}`, "deleteds")
}
function permaDeleteMultiple(elements) {
checkReqRep(`${Date.now()}&permanently_delete_multiple=${formatMultiple(JSON.parse(decodeURIComponent(elements)), currentPath)}&token=${token}`, "deleteds")
}
function paste() {
if(copy.length > 0) {
if(copyNotCut === false)
checkReqRep(`${Date.now()}&move_multiple=${formatMultiple(copy)}&dir=${currentPath}&if_exists=${typeCopyMoveExists}&token=${token}`, "moveds")
else
checkReqRep(`${Date.now()}&copy_multiple=${formatMultiple(copy)}&dir=${currentPath}&if_exists=${typeCopyMoveExists}&token=${token}`, "copieds")
copy = []
}
}
function emptyTrash() {
checkReqRep(`${Date.now()}&empty_trash=true&token=${token}`, "emptied")
}
');
elseif(isset($_GET['events']))
exit('function unselectAfterDelay() {
popupMenu.style.display = "none"
if(isOnMobile === false) {
setTimeout(() => {
if(mouseUpOnEl === false && selectWcursor === false)
unselectElements()
else {
mouseUpOnEl = false
selectWcursor === false
}
}, delayMenuMs)
}
}
document.body.addEventListener("click", () => {
unselectAfterDelay()
})
document.body.addEventListener("contextmenu", ev => {
unselectAfterDelay()
ev.preventDefault()
})
elements.addEventListener("contextmenu", ev => {
if(rightClicOnEl === false) {
let webUrl = ""
if(webAccessible !== false)
webUrl = `<a onclick="window.open(\'${webAccessible}\')">See web version</a>`
let pasteLink = ""
if(copy.length > 0)
pasteLink = `<a onclick="paste()">Paste</a>`
if(typeTrash !== 0 && currentPath.substring(0, 8) === "Trash%2F")
openMenu(`${pasteLink}
<a onclick="openBox(\'confirm\', \'Empty trash ?\', \'warn\', () => { emptyTrash() })">Empty trash</a>
<a onclick="viewChoice(event)" class="withArrow" style="display: flex;"><span>View</span><span>&#10148;</span></a>
<a onclick="sortChoice(event)" class="withArrow" style="display: flex;"><span>Sort by</span><span>&#10148;</span></a>
`, ev)
else
openMenu(`${webUrl}
${pasteLink}
<a onclick="viewChoice(event)" class="withArrow" style="display: flex;"><span>View</span><span>&#10148;</span></a>
<a onclick="sortChoice(event)" class="withArrow" style="display: flex;"><span>Sort by</span><span>&#10148;</span></a>
<a onclick="openBox(\'prompt\', \'Enter a name for the new directory :\', null, inputName => { newElement(\'dir\', inputName) })">Create directory</a>
<a onclick="openBox(\'prompt\', \'Enter a name for the new file :\', null, inputName => { newElement(\'file\', inputName) })">Create file</a>
<a onclick="inputUpload.click()">Upload file(s)</a>
`, ev)
}
else
rightClicOnEl = false
})
elements.addEventListener("mousedown", ev => {
if(isOnMobile === false && ev.button === 0 && mouseDownOnEl === false) {
popupMenu.style.display = "none"
disableAutoRefresh = true
selectWcursor = true
selection.style.display = "block"
selectionStartX = ev.clientX
selectionStartY = ev.clientY
setCursorSelection(selectionStartX, selectionStartY, selectionStartX, selectionStartY)
}
else
mouseDownOnEl = false
})
document.body.addEventListener("mousemove", ev => {
if(isOnMobile === false && selectWcursor === true) {
setCursorSelection(selectionStartX, selectionStartY, ev.x, ev.y)
}
})
document.body.addEventListener("mouseup", ev => {
if(isOnMobile === false) {
document.body.querySelectorAll("a").forEach(element => {
if(element.classList.contains("unselected"))
element.classList.remove("unselected")
})
let fromX
let toX
let fromY
let toY
if(selectWcursor === true) {
if(selectionStartX > ev.clientX) {
fromX = ev.clientX
toX = selectionStartX
}
else {
fromX = selectionStartX
toX = ev.clientX
}
if(selectionStartY < ev.clientY) {
fromY = selectionStartY
toY = ev.clientY
}
else {
fromY = ev.clientY
toY = selectionStartY
}
}
elements.querySelectorAll("a").forEach((element, i) => {
if(selectWcursor === true) {
if(isOnCoords(fromX, fromY + elements.scrollTop, toX, toY + elements.scrollTop, element.offsetLeft, element.offsetTop, element.offsetLeft + element.offsetWidth, element.offsetTop + element.offsetHeight))
selectElement(element, element.getAttribute("data-name-enc"))
else
try {
unselectElement(element.getAttribute("data-name-enc"))
}
catch {}
}
})
setTimeout(() => {
if(selectedElements.length === 0)
disableAutoRefresh = false
selectWcursor = false
}, delayMenuMs * 2)
selection.style.display = "none"
}
})
document.body.addEventListener("dragover", ev => {
unselectElements()
ev.preventDefault()
})
document.body.addEventListener("dragleave", ev => {
ev.preventDefault()
})
document.body.addEventListener("drop", ev => {
ev.preventDefault()
})
window.addEventListener("resize", () => {
isOnMobile = onMobile()
})
elements.addEventListener("scroll", () => {
popupMenu.style.display = "none"
})
document.addEventListener("keydown", ev => {
if(popupBox.style.display === "none") {
if((ev.key && (ev.key === "Escape" || ev.key === "Esc")) || (ev.keyCode && ev.keyCode === 27)) {
popupMenu.style.display = "none"
popupBox.style.display = "none"
popupMask.style.display = "none"
unselectElements()
tryToMove = false
}
else if(ev.key && ev.key === "a" && ev.ctrlKey && ev.ctrlKey === true)
selectAllElements()
else if(ev.key && (ev.key === "c" || ev.key === "x") && ev.ctrlKey && ev.ctrlKey === true && selectedElements.length > 0) {
copy = selectedElements.map(x => {
return {
pathEncoded: currentPath,
nameEncoded: x.nameEncoded
}
})
if(ev.key === "c")
copyNotCut = true
else
copyNotCut = false
}
else if(ev.key && ev.key === "v" && ev.ctrlKey && ev.ctrlKey === true)
paste()
else if(ev.key && ev.key === "Delete" && selectedElements.length > 0)
openBox("confirm", `Delete <b>ʿ${selectedElements.length} selected elementʿ</b> ?`, "warn", () => {
deleteMultiple(encodeURIComponent(JSON.stringify(selectedElements)))
})
}
})
/* UPLOAD */
function uploadFiles(dir = false) {
if(dir === false)
dir = currentPath
getUploadSizes(result => {
if(result === false)
openBox("alert", "Error : <b>Cannot get server uploads limits</b>", "err")
else {
const inputFiles = inputUpload.files
const nbFiles = inputFiles.length
if(nbFiles !== 0) {
const formData = new FormData()
const maxSizeExceeded = []
let totalSize = 0
for(let i = 0; i < nbFiles; i++) {
const size = inputFiles[i].size
totalSize += size
if(size > uploadMaxFileSize)
maxSizeExceeded.push(inputFiles[i].name)
formData.append("upload[]", inputFiles[i])
}
if(maxSizeExceeded.length > 0 || totalSize > uploadMaxTotalSize) {
let txtErr = ""
if(totalSize > uploadMaxTotalSize)
txtErr = "Upload size exceeded<br><br>"
for(let i = 0; i < maxSizeExceeded.length; i++)
txtErr += "\\n" + maxSizeExceeded[i] + "</b> is too big<b><br><br>"
inputUpload.value = ""
openBox("alert", "Error : <b>" + txtErr.substring(0, txtErr.length - 8) + "</b>", "err")
}
else {
formData.append(Date.now(), "")
formData.append("dir", dir)
formData.append("exists", typeUploadExists)
formData.append("token", token)
ajaxRequest("FILES", "", formData, result => {
inputUpload.value = ""
if(result === "uploaded")
openDir(currentPath)
else {
const found = result.match(/\\[ask=([^\\]]+)/)
if(found)
openBox("multi", { txt: "Error : <b>What to do when a file or a dir with the same name already exists ?</b>", inputs: "[button]Do nothing[button]Rename old[button]Rename new[button]Replace old[checkbox]Save choice" }, null, choices => {
let choice = 0
choices.forEach(choiceTmp => {
if(choiceTmp !== 4)
choice = choiceTmp
})
if(choices.indexOf(4) !== -1)
typeUploadExists = choice + 1
checkReqRep(`${Date.now()}&ask=${choice}&files=${found[1]}&dir=${dir}&token=${token}`, "uploaded")
})
else
openBox("alert", "Error : <b>" + result + "</b>", "err")
}
})
}
}
}
})
}
inputUpload.addEventListener("change", () => {
uploadFiles()
})
function dragOverAdir(el, dir) {
overAdir = dir
if(el.className === "dir")
el.className = "dirDrag"
else if(el.className === "linkdir")
el.className = "linkdirDrag"
if(elements.classList.contains("dragOver"))
elements.classList.remove("dragOver")
event.preventDefault()
}
function dragLeaveAdir(el) {
overAdir = false
if(el.className === "dirDrag")
el.className = "dir"
else if(el.className === "linkdirDrag")
el.className = "linkdir"
if(!elements.classList.contains("dragOver"))
elements.classList.add("dragOver")
event.preventDefault()
}
function dropOnAdir(el) {
if(el.className === "dirDrag")
el.className = "dir"
else if(el.className === "linkdirDrag")
el.className = "linkdir"
if(!elements.classList.contains("dragOver"))
elements.classList.add("dragOver")
event.preventDefault()
}
elements.addEventListener("dragover", ev => {
if(overAdir !== false && elements.classList.contains("dragOver"))
elements.classList.remove("dragOver")
else if(overAdir === false && !elements.classList.contains("dragOver"))
elements.classList.add("dragOver")
ev.preventDefault()
})
elements.addEventListener("dragleave", ev => {
if(elements.classList.contains("dragOver"))
elements.classList.remove("dragOver")
ev.preventDefault()
})
elements.addEventListener("drop", ev => {
if(elements.classList.contains("dragOver"))
elements.classList.remove("dragOver")
inputUpload.files = ev.dataTransfer.files
if(overAdir === false)
uploadFiles()
else
uploadFiles(overAdir)
overAdir = false
ev.preventDefault()
})
function dragOverAtreeDir(el, dir) {
overAdir = dir
el.className = "dirDrag"
event.preventDefault()
}
function dragLeaveAtreeDir(el) {
overAdir = false
el.className = "dir"
event.preventDefault()
}
function dropOnAtreeDir(el) {
el.className = "dir"
inputUpload.files = event.dataTransfer.files
uploadFiles(overAdir)
overAdir = false
event.preventDefault()
}
/* CONTROLS */
btnBack.addEventListener("click", () => {
if(btnBack.className !== "disabled") {
const nbHistory = history.length
if(nbHistory > 1) {
ajaxRequest("POST", "", `${Date.now()}&dir=` + history[nbHistory - historyLevel - 2], result => {
if(result !== "false") {
showElements(result)
historyLevel++
btnForward.className = ""
if(historyLevel === nbHistory - 1)
btnBack.className = "disabled"
}
else {
contents.style.display = "none"
connexion.style.display = "flex"
}
})
}
}
})
btnForward.addEventListener("click", () => {
if(btnForward.className !== "disabled" && historyLevel > 0) {
const nbHistory = history.length
ajaxRequest("POST", "", `${Date.now()}&dir=` + history[nbHistory - historyLevel], result => {
if(result !== "false") {
showElements(result)
historyLevel--
btnBack.className = ""
if(historyLevel === 0)
btnForward.className = "disabled"
}
else {
contents.style.display = "none"
connexion.style.display = "flex"
}
})
}
})
btnParent.addEventListener("click", () => {
if(btnParent.className !== "disabled")
openDir(parentPath)
})
btnRefresh.addEventListener("click", () => {
openDir(currentPath)
})
btnHome.addEventListener("click", () => {
openDir(".")
})
function viewChoice(ev) {
const choices = ["Icons", "Small icons", "Details"]
let html = ""
choices.forEach((type, i) => {
curView = ""
if(typeView === i)
curView = "&#8226; "
html += `<a onclick="changeView(${typeView}, ${i})">${curView}${type}</a>\\n`
})
openMenu(`<span>View :</span>
${html}
`, ev)
}
function sortChoice(ev) {
const choices = ["Name", "Date modified", "Size", "Type"]
let html = ""
choices.forEach((type, i) => {
curOrder = ""
if(typeOrder === i)
curOrder = "&#8226; "
html += `<a onclick="openDir(currentPath, false, false, ${i}, false)">${curOrder}${type}</a>\\n`
})
let curAsc = ""
let curDesc = ""
if(typeOrderDesc === 1)
curDesc = "&#8226; "
else
curAsc = "&#8226; "
openMenu(`<span>Sort by :</span>
${html}
<a class="gap" onclick="openDir(currentPath, false, false, \'\', false)">${curAsc}Ascending</a>
<a class="" onclick="openDir(currentPath, false, false, \'\', true)">${curDesc}Descending</a>
`, ev)
}
btnView.addEventListener("click", ev => {
viewChoice(ev)
})
btnSort.addEventListener("click", ev => {
sortChoice(ev)
})
btnCreate.addEventListener("click", ev => {
openMenu(`<a onclick="openBox(\'prompt\', \'Enter a name for the new directory :\', null, inputName => { newElement(\'dir\', inputName) })">Create directory</a>
<a onclick="openBox(\'prompt\', \'Enter a name for the new file :\', null, inputName => { newElement(\'file\', inputName) })">Create file</a>
<a onclick="inputUpload.click()">Upload file(s)</a>
`, ev)
})
btnSettings.addEventListener("click", ev => {
let html = "<span>Use trash :</span>"
const choicesTrash = ["No", "Yes"]
choicesTrash.forEach((type, i) => {
let curTrash = ""
if(typeTrash === i)
curTrash = "&#8226; "
html += `<a onclick="changeTypeTrash(${i})">${curTrash}${type}</a>\\n`
})
html += "<span>(Upload) If target exists :</span>"
const choicesUpload = ["Ask", "Do nothing", "Rename old", "Rename new", "Replace"]
choicesUpload.forEach((type, i) => {
let curUpload = ""
if(typeUploadExists === i)
curUpload = "&#8226; "
html += `<a onclick="changeTypeUploadExists(${i})">${curUpload}${type}</a>\\n`
})
html += "<span>(Copy/move) If target exists :</span>"
const choicesCopyMove = ["Do nothing", "Rename old", "Rename new", "Replace"]
choicesCopyMove.forEach((type, i) => {
let curCopyMove = ""
if(typeCopyMoveExists === i)
curCopyMove = "&#8226; "
html += `<a onclick="changeTypeCopyMoveExists(${i})">${curCopyMove}${type}</a>\\n`
})
openMenu(html, ev)
})
/* CONNEXION */
btnConnexion.addEventListener("click", ev => {
ajaxRequest("POST", "", `${Date.now()}&pwd=` + inputConnexion.value, result => {
if(result !== "false") {
inputConnexion.className = ""
btnConnexion.className = ""
showElements(result)
inputConnexion.placeholder = inputConnexionPH
inputConnexion.value = ""
}
else {
inputConnexion.placeholder = "Bad password"
inputConnexion.className = "err"
btnConnexion.className = "err"
inputConnexion.value = ""
let i = 0
const clign = setInterval(() => {
inputConnexion.placeholder = ""
setTimeout(() => { inputConnexion.placeholder = "Bad password" }, delayBadCnxBkMs)
i++
if(i === 3)
clearInterval(clign)
}, delayBadCnxMs)
}
})
ev.preventDefault()
})
logout.addEventListener("click", () => {
ajaxRequest("POST", "", `${Date.now()}&logout`, result => {
if(result === "bye") {
token = ""
contents.style.display = "none"
connexion.style.display = "flex"
}
else
alert("Error : Logout failed")
})
})
/* UPDATE */
ajaxRequest("GET", urlRawGithub, "", result => {
const found = result.match(/define\\(\'version_script\', \'([0-9]+)\\.([0-9]+)\\.([0-9]+)\'\\);/)
if(found) {
const found2 = scriptVersion.match(/([0-9]+)\\.([0-9]+)\\.([0-9]+)/)
if(found2) {
const vNew1 = parseInt(found[1], 10)
const vNew2 = parseInt(found[2], 10)
const vNew3 = parseInt(found[3], 10)
const vThis1 = parseInt(found2[1], 10)
const vThis2 = parseInt(found2[2], 10)
const vThis3 = parseInt(found2[3], 10)
if(
(vNew1 > vThis1) ||
(vNew1 === vThis1 && vNew2 > vThis2) ||
(vNew1 === vThis1 && vNew2 === vThis2 && vNew3 > vThis3)
) {
wltrdrUpdate.querySelector("span").innerHTML = "&#8681;"
wltrdrUpdate.querySelector("a").innerHTML= "<b>UPDATE AVAILABLE</b>"
wltrdrUpdate.querySelector("a").removeAttribute("href")
wltrdrUpdate.addEventListener("click", () => {
openBox("confirm", `<p>Do you really want to update php_files_manager ?</p><br><p>Your version : <b>${vThis1}.${vThis2}.${vThis3}</b></p><br><p>Version available : <b>${vNew1}.${vNew2}.${vNew3}</b></p>`, null, () => {
ajaxRequest("POST", "", `${Date.now()}&update=${encodeURIComponent(urlRawGithub)}&token=${token}`, result => {
const found3 = result.match(/\\[update=([^\\|]+)\\|([^\\|]+)\\|([^\\]]+)\\]/)
if(found3)
location.href = found3[3] + `?file=${found3[1]}&update=${found3[2]}&tmp=` + found3[3]
else {
openDir(currentPath, true)
openBox("alert", "Error : <b>" + result + "</b>", "err")
}
})
})
})
}
else
console.log("No Update available !")
}
else
console.log("%cError : %cUnable to access script version", "color: red;", "color: auto;")
}
else
console.log("%cError : %cUnable to access new script version", "color: red;", "color: auto;")
}, true, true)
');
}
elseif(isset($_GET['css'])) {
header('Content-Type: text/css; charset=utf-8');
if(isset($_GET['style']))
exit('* {
margin: 0px;
padding: 0px;
box-sizing: border-box;
user-select: none;
}
html, body {
height: 100%;
}
html {
min-height: 25em;
}
body {
padding: 1em;
font-family: \'Gill Sans\', \'Gill Sans MT\', Calibri, \'Trebuchet MS\', sans-serif;
font-size: 20px;
font-weight: 400;
color: #654;
background: linear-gradient(217deg, rgba(127, 127, 255, 0.8), rgba(127 ,127, 255, 0) 70.71%),
linear-gradient(127deg, rgba(255, 127, 127, 0.8), rgba(255, 127, 127, 0) 70.71%),
linear-gradient(336deg, rgba(127 ,255, 127, 0.8), rgba(127, 255, 127, 0) 70.71%);
}
a {
color: #935;
text-decoration: none;
cursor: pointer;
}
a:hover {
color: #359;
text-decoration: none;
}
h1 {
font-size: 2.2em;
color: #846;
/* user-select: text; */
}
button {
cursor: pointer;
}
.fill {
background: rgba(255, 255, 255, 0.69);
box-shadow: 0px 0px 0.75em rgba(0, 0, 0, 0.32);
border-radius: 0.25em;
}
.list {
height: 0px;
}
#contents {
display: none;
flex-direction: column;
height: 100%;
}
/* RESPONSIVE DESKTOP */
@media screen and (max-width: 2800px) { body { font-size: 19px; } }
@media screen and (max-width: 2400px) { body { font-size: 18px; } }
@media screen and (max-width: 2000px) { body { font-size: 17px; } }
@media screen and (max-width: 1600px) { body { font-size: 16px; } }
@media screen and (max-width: 1200px) { body { font-size: 15px; } }
/* RESPONSIVE TABLET */
@media screen and (max-width: 1050px) { html { min-height: 20em; } }
@media screen and (max-width: 750px) { body { font-size: 14px; } }
@media screen and (max-width: 650px) { html { min-height: 15em; } body { font-size: 13px; } }
@media screen and (max-width: 600px) { body { font-size: 12px; } }
/* RESPONSIVE MOBILE */
@media screen and (max-width: 550px) { body { font-size: 13px; } }
@media screen and (max-width: 465px) { body { font-size: 12px; } }
@media screen and (max-width: 380px) { body { font-size: 11px; } }
@media screen and (max-width: 360px) { body { font-size: 10px; } }
@media screen and (max-width: 340px) { body { font-size: 9px; } }
@media screen and (max-width: 320px) { body { font-size: 8px; } }
@media screen and (max-width: 300px) { html { min-height: 10em; } body { font-size: 7px; } }
@media screen and (max-width: 225px) { body { font-size: 5px; } }
@media screen and (max-width: 175px) { body { font-size: 3px; } }
@media screen and (max-width: 125px) { body { font-size: 1px; } }
/* LOADING */
#loading {
display: none;
position: absolute;
left: 0px;
top: 0px;
width: 100vw;
height: 100vh;
background-color: rgba(0, 0, 0, 0.75);
z-index: 1111;
}
.lds-roller {
display: block;
position: absolute;
left: 50%;
top: 50%;
transform: translate(-50%, -50%);
width: 5em;
height: 5em;
font-size: 2em;
}
.lds-roller div {
animation: lds-roller 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
transform-origin: 2.5em 2.5em;
}
.lds-roller div:after {
content: " ";
display: block;
position: absolute;
width: 0.44em;
height: 0.44em;
border-radius: 50%;
background: #aef;
margin: -0.25em 0 0 -0.25em;
border: 0.05em solid black;
box-shadow: 0px 0px 1em rgba(0, 87, 127, 0.5);
}
.lds-roller div:nth-child(1) {
animation-delay: -0.036s;
}
.lds-roller div:nth-child(1):after {
top: 3.94em;
left: 3.94em;
}
.lds-roller div:nth-child(2) {
animation-delay: -0.072s;
}
.lds-roller div:nth-child(2):after {
top: 4.25em;
left: 3.5em;
}
.lds-roller div:nth-child(3) {
animation-delay: -0.108s;
}
.lds-roller div:nth-child(3):after {
top: 4.44em;
left: 3em;
}
.lds-roller div:nth-child(4) {
animation-delay: -0.144s;
}
.lds-roller div:nth-child(4):after {
top: 4.5em;
left: 2.5em;
}
.lds-roller div:nth-child(5) {
animation-delay: -0.18s;
}
.lds-roller div:nth-child(5):after {
top: 4.44em;
left: 2em;
}
.lds-roller div:nth-child(6) {
animation-delay: -0.216s;
}
.lds-roller div:nth-child(6):after {
top: 4.25em;
left: 1.5em;
}
.lds-roller div:nth-child(7) {
animation-delay: -0.252s;
}
.lds-roller div:nth-child(7):after {
top: 3.94em;
left: 1.06em;
}
.lds-roller div:nth-child(8) {
animation-delay: -0.288s;
}
.lds-roller div:nth-child(8):after {
top: 3.5em;
left: 0.75em;
}
@keyframes lds-roller {
0% {
transform: rotate(0deg);
}
100% {
transform: rotate(360deg);
}
}
/* POPUPS */
#popupBox {
display: none;
position: absolute;
left: 50%;
top: 50%;
transform: translate(-50%, -50%);
padding: 2em;
text-align: center;
background: linear-gradient(90deg, rgba(255, 255, 255, 0.85), rgba(255, 255, 255, 0.9));
color: #111;
border-radius: 0.25em;
box-shadow: 0px 0px 1em rgba(0, 0, 0, 1);
z-index: 1100;
}
#popupBox div.popupBox {
display: flex;
flex-direction: column;
}
#popupBox div.n1 {
display: flex;
justify-content: center;
}
#popupBox div.n2 {
display: flex;
margin-top: 2em;
justify-content: center;
}
#popupBox div.n3 {
display: flex;
}
#popupBox span.icon {
display: block;
min-width: 1.5em;
width: 1.5em;
aspect-ratio: 1/1;
background-position: center;
background-size: contain;
background-repeat: no-repeat;
align-self: flex-start;
}
#popupBox span.txt {
margin-left: 1em;
align-self: center;
}
#popupBox button {
margin: 0px 0.5em;
padding: 1em;
}
#popupBox input {
display: block;
margin-top: 2em;
padding: 1em;
min-width: 20em;
width: 100%;
}
#popupBox textarea {
display: block;
margin-top: 2em;
padding: 1em;
min-width: 20em;
width: 50vw;
height: 50vh;
}
#popupBox input[type="text"], textarea {
background: linear-gradient(90deg, #fff, #eee);
color: #000;
border: 1px dashed #333;
}
#popupBox button {
padding: 1.25em;
background-color: #eee;
border: 1px solid rgba(47, 47, 47, 0.5);
border-radius: 0.25em;
box-shadow: 0px 0px 0.5em rgba(0, 0, 0, 0.2);
}
#popupBox button:hover {
background: linear-gradient(#fea, #fd9);
color: #000;
}
#popupBox button:active {
background: linear-gradient(#fca, #fb9);
}
#popupBox #boxPath {
display: block;
margin-top: 2em;
padding: 1em;
width: 100%;
min-width: 50vw;
height: 50vh;
overflow: auto;
text-align: left;
background-color: #fff;
border: 1px dashed #555;
box-shadow: 0px 0px 0.75em rgba(0, 0, 0, 0.2);
}
#popupBox #boxPathNewDirectory {
display: none;
flex-direction: row;
justify-content: space-between;
margin-top: 1.5em;
}
#popupBox #boxPathNameNewDirectory {
margin: 0px;
}
#popupBox #boxPathCreateNewDirectory {
margin: 0px;
margin-left: 0.5em;
}
#popupBox #boxChmods input {
display: block;
margin: 0px;
padding: 0px;
min-width: auto;
width: auto;
}
#popupBox #boxChmods {
display: grid;
grid-template-columns: repeat(6, 1fr);
margin-top: 2em;
padding: 1em;
width: 100%;
min-width: 50vw;
text-align: left;
background-color: #fff;
border: 1px dashed #555;
box-shadow: 0px 0px 0.75em rgba(0, 0, 0, 0.2);
}
#popupBox #boxChmods div {
padding: 1em;
}
#popupBox #boxChmods div.center {
text-align: center;
justify-self: center;
}
#popupBox .hidden {
display: inline-block;
margin: 0px;
padding: 0px;
border: 0px;
width: 1px;
height: 1px;
opacity: 0;
}
#popupMask {
display: none;
position: absolute;
left: 0px;
top: 0px;
width: 100vw;
height: 100vh;
background-color: rgba(0, 0, 0, 0.75);
z-index: 1010;
}
#popupMenu {
display: none;
position: absolute;
left: 0px;
top: 0px;
flex-direction: column;
color: #eee;
box-shadow: 0px 0px 1em rgba(0, 0, 0, 0.4);
z-index: 1000;
}
#popupMenu span {
padding: 0.6em 3.5em 0.6em 0.75em;
background: linear-gradient(90deg, rgba(17, 17, 17, 0.95), rgba(34, 34, 34, 0.95));
border: 1px solid rgba(47, 47, 47, 0.8);
border-top: 0px;
font-weight: bold;
}
#popupMenu span.link {
padding: 0.5em 3.5em 0.5em 0.75em;
background: linear-gradient(90deg, rgba(47, 47, 47, 0.95), rgba(64, 64, 64, 0.95));
font-size: 0.9em;
font-weight: normal;
color: #ccc;
}
#popupMenu a {
padding: 0.6em 3.5em 0.6em 0.75em;
background: linear-gradient(90deg, rgba(34, 34, 34, 0.95), rgba(51, 51, 51, 0.95));
color: #ddd;
border: 1px solid rgba(47, 47, 47, 0.8);
}
#popupMenu a:hover {
background: linear-gradient(90deg, #046, #048);
color: #fff;
border: 1px solid #047;
}
#popupMenu a:active {
background: linear-gradient(90deg, #602, #620);
color: #fff;
border: 1px solid #611;
}
#popupMenu a.gap {
border-top: 1px solid #999;
}
#popupMenu .withArrow {
display: flex;
padding: 0.6em 1em 0.6em 0.75em;
width: 100%;
justify-content: space-between;
}
#popupMenu .withArrow span {
padding: 0px;
color: auto;
background: transparent;
border: 0px;
font-weight: normal;
}
/* SELECTION */
#selection {
display: none;
position: absolute;
border: 1px dashed rgba(0, 50, 100, 0.75);
background-color: rgba(0, 100, 150, 0.5);
z-index: 999;
}
/* CONNEXION */
#connexion {
display: none;
flex-direction: column;
height: 100%;
font-size: 1.1em;
}
#connexion form {
position: absolute;
top: 50%;
left: 50%;
transform: translate(-50%, -50%);
padding: 2em 2.5em;
text-align: center;
}
#connexion span {
display: inline-block;
margin: 0.5em;
white-space: nowrap;
}
#connexion input, #connexion button {
height: 3em;
vertical-align: top;
font-size: 1em;
border: 1px solid #999;
border-radius: 0.1em;
}
#connexion input {
padding: 0.5em 0.75em;
background: linear-gradient(#fafafa, #eaeaea);
border-right: 0px;
}
#connexion input:hover {
background: linear-gradient(#ffffff, #efefef);
border: 1px solid #555;
border-right: 0px;
}
#connexion input.err {
border: 1px solid #903;
border-right: 0px;
}
#connexion input.err:hover {
border: 1px solid #930;
border-right: 0px;
}
#connexion input.err::placeholder {
color: #903;
}
#connexion input.err:hover::placeholder {
color: #930;
}
#connexion button {
align-items: center;
width: 3em;
aspect-ratio: 1/1;
background: linear-gradient(#dadada, #eaeaea);
}
#connexion button span.icon {
width: 1.5em;
height: 1.5em;
background-size: contain;
background-position: center;
background-repeat: no-repeat;
}
#connexion button:hover {
background: linear-gradient(#dfdfdf, #efefef);
border: 1px solid #555;
}
#connexion button:active {
background: linear-gradient(#d3d3d3, #e3e3e3);
border: 1px solid #333;
}
/* TITLE + LOGOUT */
#contentsTitleCreditsLogout {
flex: 0;
display: flex;
flex-direction: row;
justify-content: space-between;
}
#title {
display: flex;
flex: 1;
align-items: center;
white-space: nowrap;
}
#title span.icon {
display: block;
width: 1.9em;
height: 1.9em;
margin-right: 0.75em;
background-size: contain;
background-position: center;
background-repeat: no-repeat;
box-shadow: 0px 0px 0.2em rgba(0, 0, 0, 0.4);
border-radius: 0.2em;
}
#title input {
font-size: 0.01em;
opacity: 0.01;
}
h1 span {
font-weight: normal;
}
#contentsCredits {
flex: 0;
position: relative;
}
#credits {
position: absolute;
top: 50%;
right: 0px;
transform: translate(0%, -50%);
text-align: right;
}
#contentsCredits p {
margin: 0.25em 0px;
padding: 0px 1em 0px 0.75em;
font-size: 0.87em;
white-space: nowrap;
/* user-select: text; */
}
#contentsCredits p span {
font-weight: bold;
/* user-select: text; */
}
/* #contentsCredits p a {
user-select: text;
} */
#logout {
flex: 0;
display: flex;
padding: 0.35em;
align-items: center;
white-space: nowrap;
background: rgba(255, 255, 255, 0.2);
box-shadow: 0px 0px 0.75em rgba(0, 0, 0, 0.1);
border-radius: 0.25em;
}
#logout a {
display: block;
width: 2.6em;
height: 2.6em;
background-size: contain;
background-position: center;
background-repeat: no-repeat;
}
/* CONTROLS + PATH */
#contentsCntrlPath {
flex: 0;
display: flex;
flex-direction: row;
justify-content: space-between;
margin: 1em 0px;
}
#contentsCntrlPath span.icon {
display: block;
width: 1.25em;
height: 1.25em;
margin-right: 1em;
background-size: contain;
background-position: center;
background-repeat: no-repeat;
box-shadow: 0px 0px 0.2em rgba(0, 0, 0, 0.4);
border-radius: 1em;
}
#contentsCntrlPath a {
overflow-wrap: break-word;
word-break: break-word;
}
#controls {
flex: 0;
display: flex;
padding: 0.75em;
margin-right: 1em;
align-items: center;
white-space: nowrap;
}
#controls a {
margin-right: 0.5em;
width: 1.5em;
aspect-ratio: 1 / 1;
background-color: #08c;
background-size: contain;
background-position: center;
background-repeat: no-repeat;
box-shadow: 0px 0px 0.2em rgba(0, 0, 0, 0.4);
border-radius: 0.2em;
opacity: 0.8;
}
#controls a:last-child {
margin-right: 0px;
}
#controls a:hover {
background-color: #0c8;
opacity: 1;
}
#controls a.disabled {
background-color: #555;
}
#controls a.disabled:hover {
background-color: #777;
opacity: 0.8;
}
#contentsPath {
flex: 1;
display: flex;
padding: 1em;
align-items: center;
}
/* #contentsPath span, #contentsPath span a {
user-select: text;
} */
#contentsPath span .gap {
padding: 0px 0.3em 0px 0.5em;
}
/* TREE + ELEMENTS */
#contentsTreeElems {
flex: 1;
display: flex;
flex-direction: row;
}
#tree {
margin-right: 1em;
width: 25%;
overflow: auto;
}
#elements {
width: 75%;
overflow-y: auto;
}
#elements.dragOver {
background-color: rgba(0, 100, 150, 0.5);
}
#tree .list a, #boxPath .list a {
display: inline-block;
margin-top: 0px;
margin-bottom: 0px;
margin-right: 1em;
padding-bottom: 1em;
text-align: left;
white-space: nowrap;
opacity: 0.87;
/* user-select: text; */
}
#tree .list a:first-child, #boxPath .list a:first-child {
margin-top: 1em;
}
#tree .list a span.icon, #boxPath .list a span.icon {
display: inline-block;
margin-right: 1em;
width: 2em;
height: 2em;
vertical-align: middle;
background-size: contain;
background-position: left;
background-repeat: no-repeat;
}
#elements .list a {
display: inline-block;
margin: 1em 0px 0px 1em;
padding: 0.5em;
width: 8em;
text-align: center;
vertical-align: top;
overflow-wrap: break-word;
word-break: break-word;
opacity: 0.87;
/* user-select: text; */
border: 1px solid transparent;
}
#elements .list a.selected {
border: 1px solid rgba(0, 50, 100, 0.75);
background-color: rgba(0, 100, 150, 0.5);
cursor: grabbing;
}
#elements .list a span.icon {
display: block;
margin: 0.5em;
margin-top: 0px;
width: 6em;
min-width: 6em;
height: 6em;
background-size: contain;
background-position: left;
background-repeat: no-repeat;
}
#elements .list a span.size, #elements .list a span.date {
display: none;
}
#elements.view1 .list a {
font-size: 0.80em;
}
#elements.view2 .list {
display: flex;
flex-direction: column;
}
#elements.view2 .list a {
display: flex;
justify-content: space-between;
margin: 0px;
padding: 0px 0px 1.5em 1.5em;
width: 100%;
}
#elements.view2 .list a:first-child {
margin-top: 1.5em;
}
#elements.view2 .list span.icon {
margin: 0px;
font-size: 0.4em;
}
#elements.view2 .list span.txt {
display: block;
flex: 1;
padding-left: 1em;
align-self: center;
text-align: left;
}
#elements.view2 .list a span.size {
display: block;
padding: 0px 1em;
align-self: center;
}
#elements.view2 .list a span.date {
display: block;
padding: 0px 1.5em 0px 0px;
align-self: center;
}
#tree .list a:hover, #elements .list a:hover, #boxPath .list a:hover {
opacity: 1;
}
/* RESPONSIVE DESKTOP + TABLET */
@media screen and (max-width: 1200px) {
#tree {
width: 30%;
}
#elements {
width: 70%;
}
}
@media screen and (max-width: 750px) {
#tree {
width: 35%;
}
#elements {
width: 65%;
}
}
/* RESPONSIVE TABLET */
@media screen and (max-width: 1050px) {
#popupMenu, #popupBox {
font-size: 1.1em;
}
#contentsCredits p {
font-size: 0.93em;
}
#contentsCntrlPath {
font-size: 1.1em;
}
#controls {
display: grid;
grid-template-columns: repeat(5, 1fr);
}
#controls a {
width: 1.75em;
}
#controls a.lastLine {
margin-top: 0.5em;
}
#controls a.lastOfFirstLine {
margin-right: 0px;
}
}
/* RESPONSIVE MOBILE */
@media screen and (max-width: 550px) {
#popupBox #boxChmods div {
padding: 1em 0.5em;
}
#popupMenu {
font-size: 1.35em;
}
#contentsCredits p {
font-size: 1em;
}
#contentsCntrlPath {
flex-direction: column;
}
#controls {
display: flex;
margin-bottom: 1em;
margin-right: 0px;
}
#controls a {
flex: 1;
width: auto;
margin-right: 1em;
}
#controls a.lastLine {
margin-top: 0px;
}
#controls a.lastOfFirstLine {
margin-right: 1em;
}
#tree {
display: none;
}
#elements {
width: 100%;
}
}
');
elseif(isset($_GET['images']))
exit('#popupBox span.icon.ask { background-image: url(\'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAIs0lEQVR42tVaW2wcZxX+zv/PZXe9u7ZxnDQXJ3GCFErTGtRKgEJRUYVKHirxgtQiHhBGESiQkoJ4i0pV0paKYNoSqkZE6kNFoj4VWQIhBQlUEpCKWtEk0CgKSexclDr22l7vZW7n8DCzuzP2rr2XBMGRLHlnZv//fOd85/bPGvg/F2OtB9a/dkHZA+u2KqXuV1rvJFCeFBHkzisjEBGWRebgEjOfdcqVqY/Gt3BXAEbenMlp2/quZZjfsg1z1NSKFBFUu9oQkAC5/HOzZwlgAVgEXsDipNJX+t5aOOa77tHprw8X2waw7WTh8ZSdOp61rWFbEwwCNBAqT3eZEwIwCIHW5Ft61AnsF5Yc9+ltJwvjV58YnFwTwOjJwoG+dN9EzjaUTYCpAKK7r3fCG5FDRAALBCttDxeVfnv0ROHg5ScHX2kJYNuJ2cf7UpmJfttQKQK0+i8q3gQHEWApQDOgbENBMhPbTsxevvrk0OQKAFveuJmzjdTxrGUqO6ILRZwVSdA07u2m1I5fo+YsWW7wxFrLv6MA2ACylqlcL3V8yxs3d177xsZiAoBhWvv7bHvYViHfKVJ8+aayijLNrsnalF9zD0Kok01An2UPu661H8CLdQCbXj6nDG3us7WCjlm9GxkwfOzOB9iVY/RbgMvAzQrhYlHhYslAhVXXtNIAbEPB0Oa+TS+fe+nGU7vZAADqHxoxlbFdE0AMCHWjeIDxURdf3GrDtsyVlhag5Pg4dbWC30xZKPi6q7jQBJjK2E79QyMArhoAoJjGTKVJSeS+Dq2/I+3iJw8R1ucyrTcnIJsy8JVdBj6/2cPh9xycXbI7BqEAmEqTYhqrAyDIDiIVBo+son+TyBwyfDz3IGF9zmpbiXVZE88+BBw47WLatZqTHs0jmghQpECQnY0gFuRVjfeySkpoEsHjO11syPetoEu56mOh5EEroD9rIWUlKZPPmDhwbxk/er+JwWRZOpOG+WsZEYJcAwCDhAVgQFTkp5ry1NoD95geHh5J0oBZcOrCPN68auM2WyAIRowKvv0JxthIHhRbb/cGG5tSDq559uopK64DA6GuomIeCHkjEvNCG3nywX4XaSubuPXPGyUc+XcOrjQsfsE1cPi8g9cGqliXT9Wvm4bGWL6M6Zn2Y0EkBFHTwQgvCoQlBMCt6b88Od03QAmLigBvXw7gBCszzAzbODezhEdiAIiATenWe7YCIHVL1ykUAahRqI2iAwAb0snPns84X7Ra1pDb5ZU3NHVWc4QjAMxxAJygUbtyesrFoAmoyA1T8w5m3HxLKudslWSnAIUKd1Y0pcGYRAzUPdDBWm/d6sdvbwV1Gnliw28xMaQV45PrzMQGQcA4W1CdUYhDxiQoJCwxZO33zgJCBcaaqAnA17ZVsXEgk3h0+nYV/1pKt2+0Gv8DrKSQCIce0GEXeqcmRgOCb368gifuy0CB6guXnQCvf+ChKunO5h0GGBx6IQGgFsh3cNy1FePQmIOHR/ugVMMopaqPX79bxDvzA50PbBF9RHhZGhUBs0BFQwB10BI37xwFz3y6ij2jfaBYppkveXj1b0s4Nde/6srSpKuoFcqasWMxwKhX4ghAr17Yu7mMz23PIsYaLJRcHH6njL8u5NveYcVT9UocjwFhgBksDNVDv16vsErw1V0miKhueT9gvP5uCWcKuZ7Mw5GuCLhJIROpAetJ1tseNubtRH6/dKuM393I1GKv+0OLiO6SSKNBaH1hgajeEWzN+NA6lh4F+Mt0AIetqJHBKhPz6pEXGpkhWOYBRB6QO+CBIZuBGH1YBBfnm/U80pUHavrGhvogDGRE6Uli3Va9AW+/aQl8RhAIlKKoRxIUqg23d6F1418SCDPAQQMA13jFDFHxShw7T2mn3Eby549MbPn7LHJ2mBBuFgN8WMzU3d5J37P86FGYm/VCDGEGi0Bx72W4EGj88sNM27zuZKrnyNBhP9GkErPmmDVXGU7vujQ//pLWhSz6U6rLI6qkWEowkgngMuF6RYGF7giYWtFNthLgOrc46D0NjX0swKE9aWzoz0IEOH+zjB+f8XCr2nuRDGtAUE9pkQeCqBuVzgNtmQxajOe+0IehfGPOfWBzHw59ZglP/cmDL71RMaQPQ+JZSIJAmKNUSgQiWtUCq93/1BBjMNsYK2sJ5N570tiYdjBd1l2tm6RQAOawZwjTKPuLEvhgZihSYT1oEcLhoUXreDAlHBGJVkaOYgYH1PLEOr5uqxc8zAwOfHDgLTaykO9f8v1qeCMWxN2E8AezgsWSh/5s8qTuykwZN8oIqdpFF1pvpwMfvl8FfP9SfB446ztLEqQGSGmjLVe2kmtlhVdP38Z3PjuEfDY8NrwxW8JPzxRRCczeaoEIAt+D7yyJiJytA6B0asorF6946fKoNkwoZfQUaJNTBt6bmcX9gwwnAN6f05j3jJ4LGbMPzy3DqxSvqEx6qg6g8PwjnPve5DGnPPuC1hZMKwOlVOu+pA0PXS9rXE8ErKzd76yyLjPDcytwyrOQwDtWeH4vJ97QaMP6lVuae1prexggGKaF+on1He4IOumgw5d9DN9z4ZTn4JbmZgwzc7RxaBDJ/MRji/3f//14ZfH62wJRdmoA2rBASkcxQSt6t05wUMe4w+GdmRH4DpzqPKqLN1hpe7zw8y8VVwAAgIVf7J0cOPiHg5X56YkgU1JmahCGmYLSJkgpEKgt+vRYasM0zgwOPPheFW6lAK8yx4aVPTg/8eXJ5LHNMpmfeOyVwR/88bJXXTjulueGtZ2HYWegtA0iDSJ193SPrB4WKgeBU4bvLIKUnjFTg+OFI4+u/aIbAApHHp1c98PTOwOp7ve98r5qdWG7CBMRAXcRQL21FwGREmWkrpipwWOaUkdv/2xP+z81AIDoCy/mnvnHSylnaSuEHxDhHcKch8JdeAUuAINJqUUidQmkzrp2dqrw7Fh3P/aoSTFc4Er09z8n/wFzjdtWdlVNGgAAAABJRU5ErkJggg==\'); }
#popupBox span.icon.err { background-image: url(\'data:image/png;base64,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\'); }
#popupBox span.icon.info { background-image: url(\'data:image/png;base64,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\'); }
#popupBox span.icon.ok { background-image: url(\'data:image/png;base64,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\'); }
#popupBox span.icon.warn { background-image: url(\'data:image/png;base64,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\'); }
#popupBox span.icon.edit { background-image: url(\'data:image/png;base64,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\'); }
#popupBox span.icon.lock { background-image: url(\'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAMAAABg3Am1AAACf1BMVEUAAACZmZmzs7OMjIytra3IyMipqamqqqqurq6/v7+IiIixsbHIyMjGxsaSkpKenp6zs7PIyMikpKTDw8OLi4uoqKixsbHtwD3twD3IyMicnJyOjo6vr6+Xl5fHx8e9vb2IiIi0tLTHx8ftwD3IyMisrKy6urqIiIilpaWysrLtwD3twD2Wlpazs7O2traIiIiIiIizs7OIiIihoaGwsLCdnZ2rq6uampqoqKiXl5ekpKSUlJSgoKCRkZGPj4/twD2IiIilpaXBwcGcnJy0tLSKiorGxsa3t7eJiYnFxcWpqamQkJCRkZGxsbHIyMimpqaKioqsrKzGxsavr6/Hx8eVlZW6urrExMTtwD3twD2ioqK0tLSIiIi/v7+amprHx8e8vLzDw8PHx8egoKDtwD2NjY2wsLDDw8ONjY2ZmZm1tbWIiIi8vLy/v7/BwcGpqanHx8eysrLGxsbtwD3twD00Tl+IiIi5ubmfn5+2tra4uLiYmJijo6Ourq6xsbG0tLS1tbW3t7e7u7u8vLy9vb2+vr6/v7/AwMDBwcGXi02JiYmSkpKUlJSXl5ebm5ucnJympqaqqqqrq6usrKytra2ysrKzs7PCwsLDw8PExMTFxcU2T183UF44UF5BVl1ja1ZlbFaYjE2KioqLi4uNjY2Pj4+QkJCRkZGTk5OVlZWWlpaZmZmampqdnZ29okagoKC+o0ahoaGioqKkpKSlpaWnp6eoqKipqamvr6+wsLDjuj+6urrnvD7ovT7pvT7qvj43UF85UV4/VV1CV1xPX1pRYFpscFVtcVWRiE6SiE6Wi02MjIyOjo61nke2nke6oEeenp66oUbMrEPNrEPGxsb6l5FjAAAAdHRSTlMAAgIDBAYHBwcHCAkJDg8PEBkaGhwlJSkqLS4vLzExNDU8PD9aW1tdampxcnN0dXZ+foCAgICAgICAgICAgICAgYGEhYWFiImQkZGVrK2ztb2/ycrKysvL1tfZ2dra4eHp6err6/b29/j4+Pj4+fn8/P39/UcOGR4AAAH9SURBVHjaY2DAANyK7mEJuXmJ4W5KPAyEAZtqcGF9Y3lza/v0mUUhKqyE1Av550+urZtWWdXU0tYxY1aBnyB+9VLJm7Zsmzhlx9QGqCVXkiTxqZeN66/YuHnCdqAl9TBL4uVwq+cL7V3dd2F9zZZtk6bsnNZQtbsZpCOIF6cG7+UrVlWvWVsRpSHKziGmGV1Z3gRylicu9SIZS5evWNmb6cwP4Qu4ZFXtBjorXRiHBtfFS84s6+7RYYQJMOlCAtgJu3qumK5Fp5cs9UEW8wUHcCwnVg0K2WVdixanSiOLyaSBAjhHHqsG9TIg6ApgRhZjCQQFcLkaVg36IA1lHqiCXsAAntqgh1WDAViDA6qg49aJk3fUGWLVYATWYIQqaFwzYfukWhPiNZhWbNq8daIZ8RrMgYmrZoIF8Rosq/vWrq+xIl6D9crqNf0VNsRrsO3uAaZgU+I12AET16pqc3S1ypHFJQRAcYQyQr1ESgkRIEUCrkG7hCigDddgT5wG+yGlYe/ZEyfP7SVew675s0tLZ8/fRayGfQtKwWDBPiI1nJ8D0TDnIpEaTpVCQSeRGhbCNCwkUsOGuRD1c9cRqWH/cYiGY/uJDdY980DqD+0hPuIOgjQcICGmj4I0HCFBw4bDpaXz1pGS+C53dl4a4vmBkAYiSw0tUsslcbJKPgDRpH4F36hF8wAAAABJRU5ErkJggg==\'); }
#popupBox span.icon.path { background-image: url(\'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAMAAABg3Am1AAAAllBMVEUAAABDp90zUmJDpt1Dpt0zUmIzUmIzUmJDpt0zUmJDpt1Dpt0zUmJDpt0zUmJDpt0zUmIzUmJDpt0zUmJDpt1Dpt0zUmJDpt1Dpt0zUmJDpt1Dpt0zUmIzUmIzUmJDpt0zUmJDpt0zUmJDpt0zUmIzUmJDpt0zUmJDpt0zUmIzUmIzUmIzUmJDpt0zUmJDpt0zUmJDpt10E14/AAAAMHRSTlMAAwUFEBkhKCswOjw9P0hJSUtNTVdxlJWZmaGmrrPHx8zMzdrh5uvw8fL19/r8/P32iPauAAAA6klEQVR42u2WyxKCIBSGyay0TFO7WNmVzMoseP+Xa4ZUFINw0mnjt2Lxfws45wDAi3ECVAEHFaaZ2ANZHmOLJ1g0EwO6xg5PcHIhRlBMl8FUhIIRXhlCQyi4qIQrFOyyYAuFvr8hRAhF75XfFwopAUKB1ClJCJDWROMJGq0uBKrlJOTyjAC0NGPx2ocRvtMKoDNbJ6xGUsLwmRXuLCUUWqM+4b4nbAeyAm/i6hMeAeGkN7bpX4QxzV+khO7ikLCbtgNUn1D1IitclX/spduSMO9VHaBJMxN35D+7n9H5D7sMlYXC50SG/PfnBdMNkJkPve9OAAAAAElFTkSuQmCC\'); }
#connexion button span.icon { background-image: url(\'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAEkUlEQVR4nO2VXWgcVRTHz70zs98bk20WsiaxJYlbPwpS1EaQWlssJjVJU9AWQaWooIjYFx9ECQriRxtECoIVtE+GYn2QlFJoTbW1gRjRqrUpGqOtuwnZzUdnN7uzu3Nn5l7PnVmLBZ9iJD7swu7sPXP3/H/nf8+ZJbDKL1IDqAHUAP5XAK8/UC/X7f6wtiN2o++eYJQmhBB2cdG5omesUZvZpwZGcrP/CQCKK5TSfcnOyCu3bSSx5vUKBMIchAAo6gRmpjj8+JWTTv1ivInBjxDEWjEAFI9E1/g/29Ib2HbLRk5VP4ZtAcISwB25i4DjCLAqAJOXNBg7aXyypLNnESK3IgCDffGD/U+FX1h7s40BAoIDCMcTF4IAt8GLoRvlPIfsggpfHqucL+TZYcHFBwhiLxsAq++8a0tkbGuvIFSlKIaVo/BflcurbQoXRAJIGLMsINQUhG9HbPh+zDhMCFkAUS1HeN/khXgB/Bkmkr+WOTg3LWYdRegJF+BAT+One/cFH47GBFy5KGD6sgOBqALJOwgEI8QV5zaCCOkCcbNXCgKoj2JaAoGGAB4PXIN29/C/rYXU9tYyntcFfHOuWJrPFLsGPtfPkQ+fbErv3ktapn6icOaUdcIw2CBVaPu6ZPDg9l1KmFIBjuW5YVueC3JtFDj4QypMXChCfQN1ASWIfEsrZM9It+QVKHHBOG6S9xdyAub/gDmosD5y5PmE+eBO8A1/DJnpmXInWpOSKfb3xN/o3hN+ualZoLBXiYMJOfcqyy04EK5TXUd8QcUFkyJOtVJmej0kj8F1z+WQbniDN5d14NJ3hk6Gnkuw7b1EO3EUZtPp0iYEmJYbDvTGX+3aHXktnvAckIllRbbj9UVu0YFgWAGzgveFZ7t7xrJ67KPMrA0KgoX83r1yieNYK6ApBEEE6LoDv14o5cmhx5pm+x6lTdkZFc6erAxhZ7+jKDTZviH0/uZuX4PNOFgmeNVXJ8JCoELBAQ2b9ovTJaivV6Ex4UBIc5sQJn+zYCmrzgTrlGhjC69LTzMQBU1f06zWx2I2mbpsgp3X5n3c3knefig+3LvH35doU2EuQ2E6xXkwROi6DuqOosUEMKySVSHkURRRXFZawIb6YbzyM9pzpCUZGuhYr6oEvf56lNnGYqmfUNqWaI++uzjHmLlUeUT1qU+3JsP9qd/LpmOwroER/Ywcw3s7bg+PbtpKIRxVvLOWo8fAPXv3u+WJO2i/jdecLgMEJicc62rGuB/rHkOxbs2nbZMdyEx2Fjv8GOamGO/BQczielw+8HByHsf1OB71+WsPord2NB66877gMze1E/D7saOxmSzmiUsg6YK0XYLoVy3ILxJITZoXcZ7344YhTCaW/SCSH0hWF4j4j7dt0DavRetDIXRCNhv3RBkCGAZHcRv0eQJzKfO4w6wnUFhfrvB1AFUIFf+MXoq3Bl9sbKE3xOIUsMdAYEcXlwRkMxwWZnmmnK8MYvQ9FGf/Vvw6gCqEXN+q+bVdoahyt6qRVmx9bpb5ZMlwxrjjDKNweiWE/xFgNV41gBpADWDVAf4EuIuVIgkEHv0AAAAASUVORK5CYII=\'); }
#title span.icon { background-image: url(\'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAACkVBMVEUAiMwzoNb///8pgKsAbaP+//8AfLoAbaQAfbwAh8ve7PMAbqYMc6cffq4pga0pgq4zn9RgoMAsi7lEkLYxms4yndIyndN6sMvj7vTk7/T8/f4AbqUAb6cAcKgAfr4Af74Ahskdfa4Kjc4qhbItjr4vlcdDkLYyntMzn9VgpMV5r8qex9yiyt6uz9+z0+TA2+jS5O3m8fYAcqsIcaULc6cMdKgRdqkAfr0Af78AhMYafKwgf68qgq8sgqwrhrMrh7QsibctjLsukMExm88ynNFTmbxlp8eWw9mZxNqjyt6mytzK4Ozc6/Lf7fPl7/Xt9Pj2+vz3+vwBbaMCb6YAcaoGcKYAc60HcaYAdK4JcqYAdbAAd7IOdKYPdagSd6oAfbsTd6oCfboJfbcAgMEAgcIAgsIZe6wAhcgAh8oBiMwCicwhf68lf60pgKwpgawKi8sqgawJjM4KjMwsgq0qhLEug60Mjs4Ojs8vhK4Oj88thbIrhrQrh7UQkM8uh7Muh7QviLQ0h7Asirk2iLA2iLE4ibEtjbw6irIskMM8i7MeltI+jLMuksMhl9Mvk8VBjrUvlMYwlcg/kbpDkLUwlsknmdJEkLUwl8kmmtQwl8owmMsom9QxmMwqm9RJk7gvntUxn9ZOlrpWm71dn79bocRgocFdosVhocFko8Jlo8JppsRrp8Vpqclyq8hvrMtwrct3rsp0r811sM17sct5ss99tNCBtM6Ctc6Dtc6LutGKvNWOvNKNvdaQvdSWwdaXwdafyNyfyN2pzd641eO72ObG3urI3unJ3+rM4evP4+zW5+/X5+/Y6fHe7PLf7PLf7fTg7fTh7fPi7vPj7/Tn8fbs9Pfu9fny9/ry+Pr7/f39/v8yfrSSAAACM0lEQVR42q3RZVcbQRQG4OHeCRtiJESACCEJadOUhgSH4lK0SN3d3b2l7u7u7u7u7v5rOrtEtqHntB/6fpk98z5nzsxdEvGXkH8G6p1OXYRhh9O5Wef6EygrRMSicchHVaxrAVrHYPnVJYhn7ty7dmIVFqx3/Q5Yf/yt8akWb9UDGN/cWKZa7RIDXczY2G8A8Eqbf/47WyUvDqrWioB+DmoagI93xuS7wsfzRaqyIGDnH6iH5jzSLnBXNwL8vIQlAaAuRvcXCOSlFvEkW59NLQyAbdGnGiAU7wqc8BXgw7q8ANigum6USCRCy9bPexHrAOI3RfvBvtLZiw9pNMe8rK/WaI6U5yPGAdRtbQb7iX377iKHIw/bMRCLUxwFSxfyIL6VAPZMJ2Tk3C16fYkA3FiqN6iTgmDXzFFEyMSVy/0nGNhfS8KPPBizZhoJZWCfhyLwCSBuVmciTiS9z0CHtPF2+6RBae8AOraXh4G2DLSh1l4Ws4nKeEDN5tSEEeFA3tWmVI72Afg2KpXKTLk1HNBztTLZe35kPplM5uknDYFK/g6UXhBNHZ70DYJsepQNu4pSRXKoT2niEgIgkbPVsnNvzs8IiRQFFxkVfEZvevoHv1szTHHW36ebRD3JkXd/YOT3q3rS2/wlazI5a5R4EtmWARdfs3s0Xk7PSAaJxyY+X8iQVJp7uNLzuGIeVVypyDWF94QM799NTrlOlOvRhVJLQouef8tgKUtWYk6WdCj5b/kF7oZ0CZFadjcAAAAASUVORK5CYII=\'); }
#logout a { background-image: url(\'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAMAAABg3Am1AAABa1BMVEUAAABFLilHLypHMCpFLylKMSxONC5SNzFSODFSODJTODFSOTJTOTJUOTJUOTNUOjJUOjNVOjRVOzRVOzVWPDVWPDZWPTZWPTdXPTZXPTdXPjdmRz1kS0VmTUZnTkdtTUFuTUFoT0doT0hvTUJvTkJwT0RwUERxUUZyUUZyU0dzU0dzVEh0VEl1Vkt5VUh2V0x7V0p3WU54WU54WlB8WUx5WlB6W1F6XFJ+W097XlN/XVB8X1R9X1SAXlJ9YFZ+YVaCYFR+Ylh/YliDYlZ/ZFmAZFmEZFiBZVuCZl2GZVqDZ12HZliHZ1uEaF+EaV+LaVOIaV2FamCFamGGa2KKal+HbWSLbGGHbmWIbmWMbmOJb2aRcFeScFeOcGWTcVePcmeQcmeMdGyQdGqRdGmSdWuKeHaUd22TeG6TeG+Je3uTeW+DfoSVeW+WenBti6qlgVKmgVKnglKohVRlk7yphlRjmcdfodf0y1D1zFC/zn6nAAAABXRSTlMAmZmZmdmxMVMAAADlSURBVHja7ZW9EoJADIS5A6wsZCysLX0GH98nsLaxsRTtlJ/sOgIWdzIXrtEGmpsw+ZLsEiBLIq9sBn4AmO8CLcPA2r7P1Aw3mlKCQFLsVx04hPZ6eIQ1MF24LamIRuNmiOYSULux1iEhuiO31VBAAdi7yKWpMWLqSIe2m5pV3qc2mgZKL/P2ibUORBvnUiJuiqgaBHEAJXIkH4CqAbEaYAsr92YyANmlhpvj5JEop63BWSYvH1GW7vaq2ypxLsErqb8P3i6pIwGRD45eSYoq2g/N/Ln3AfIpjlUtwy6xvsx/0X8AL4XMf4wvXywdAAAAAElFTkSuQmCC\'); }
#logout a:hover { background-image: url(\'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAE8ElEQVR42u2YW28bVRSFvzMztuNLEpK6TdMEekGFtkIgRF7greIRKtQn/gYS/wHxhsQfAHFTqoqCEBKiRUVqSlXUhDall6gVRIgmcXpJ69hjz+2czYMnju1EJSRqx0g50tie45FmrbP2Xnuf4/A/H842gW0C2wS2CWwTSITA0bHXb7jFscObfrMIKf8BOXcW/PLNM1dvHnm6CvQMEu5/axPADU69RHbpdwa8RWw7wFXq6StgOzZ9/QUCbRD59+eVibCrc2TuXaFv6TcGsoqhA0PMz2ncuvf0Cezqz/DaG3uYmavy932PUJuWf33AA/pBh6iHs2Ruf0+2PkdxoJfRl/bT31vAiFBaKCWTA5m0xcGRApZjUw6EerBCQDDhWQhmUOU3Ude/oae+yO6hIsMvHKa3kMexLEQEE0VsSL4nQcAIuBHYKZuhHTkeVEO09vAWT6P/OIWzENBn/cWe4V0Ui6+Qz/ZgKYWIIMYggIm/EyHgBsL0Qp1lz6Atm4wsUf71E7hzhaKTY3j0AMXiILmeFuAtFzGRxBSIgMCySWdtAMKZaXpLM4w8t5/BwWfI9mSawI0xsXPGwOOrMZ8QActW5HszzbivSoWdQzsZHdmNisGaeIVFBGEFfCsRs1UBtmCjliJfSDXvc/leYDW+W4EbLCKVwdG1tnlJXgEbBMpLs9y9c5ZnV+I6VkBEAOHW3mMYZfHin1+3hZExJn4mEQUgm238flgqUX04CzKCEdMMIYlDxs0U2Xn/ckOdtiSWrQqwFQUgExPYe2iM8qvvIFOXEBOHR8tKH7r1BanQxYhpS+JEQ0gpSKch8Fyu/vIZt6+e4qCMYoxB0W6XqaCyxoFAMEkmsVKC7QjgU3fvoZTdcBVjGo7TmgctoKVbFGi82CdbyHH0+PtcC0ZZPvMTYjTE/k9n4eoIrZUcSYiABqoNNSxwUnHREkGtV3XpJESyhUzQaHm02htRb7YHsk7VhVXgrSGUmI1q7VOtzKGUwnYcIu02vb1poy2r3qzCbUqYJPfEGqMqKMsCFMbUVldVsTb+G+W5e5o5IcJQBgPagDZu3NuYZpisn8R0VOKEFDA6xKvdQykFShGFNcQ0QKUceH6fprjDMHHBWgO8TYGkkthedsldvIaxFcaxseYeICIUBzTH3w7pKwg//my39DstJFhRIEEbtY2hv1ptrKDAYsXFlQyWZZi8bHF9RlGrCdDS/6xxpwQV0BlDtehRKEPas0jHQOdLML9AO/DHJHFiCui0pryviltRDM9kUCudZhzXnZ7PmipMcx6VhI2KRukKYcoiwIrjeZ39QGcVjjc7iR8tihjCekR6PossQ6Q1Iqa5/12v9+mqs1HxFc6tLLm7eYwFoNqq65MGvnUXqjtk5/NYto0BIi1rWomuPp1WK59KIcpCVNRml11/vB5FhvKyh21ZoMCtCZBu2PqGXUUQ1JYqwaYJnDx348jJc6v3x8Zefm9okI82vBUKwmi55v0QRtGHUaQvJtCNbsa5BC8IvXLN+67uBx+MT0xOJ9hO/5eDYKFS8xZrfvCxF0afjp+7NJ94DmyoWhvDsust1Hz/Az+MPj9xfqrcNUn8uDAJIl2p+8GFqud/GYTRyRPnp7yuc6F1gYfh/bJbn6j5wVdBFH07PjEZda2Ndo4g0lOPqvV3g0ifH5+YDLq+DnSO09M3Jkhg/AN5blK0oe/z3wAAAABJRU5ErkJggg==\'); }
#contentsCntrlPath span.icon { background-image: url(\'data:image/png;base64,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\'); }
#controls #back { background-image: url(\'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAABa1BMVEUAAAD///////8AAAAAAAAAAAAAAAD///8AAAAAAAAAAAD///////8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8AAAD///8AAAAAAAAAAAD///////8AAAAAAAD///8AAAD6+vrx8fHs7Ozo6OgcHBzf398pKSnQ0NDGxsY4ODg2NjZBQUGKioq2trZISEitra2qqqpWVlZVVVWgoKBmZmZlZWWXl5doaGiUlJRxcXGMjIxycnKKioqGhoZiYmKAgIB5eXl4eHh0dHRzc3ONjY1xcXGQkJCBgYGJiYmhoaGLi4uPj4+lpaWRkZGUlJSYmJiampqenp6hoaG2tra2traurq6vr6+7u7u8vLzFxcXS0tLU1NTj4+Pm5ubn5+fq6urn5+fs7Ozu7u7t7e3y8vLv7+/z8/Pz8/Pz8/P09PTz8/P09PTz8/Pz8/P09PT09PT19fX8/Pz8/Pz+/v7+/v7+/v7///////8glA2oAAAAeHRSTlMAAQICBAUGCgoLDA4RExUWGB0fJCUmJioqLS4wMTEyMzM0NTY3ODk7PD4+P0BBQkJFRUZGSUtLTExNT1BQUVJTVVhZWltbXF1jZmdpamprbXBxc3V3eH6AiouUlpyxtsDBxcXI0dPV1djZ2drb3t/g4uT1+Pn8/f6x9t1VAAABCUlEQVR42mOQJwAYhpgCJU09DUU8CpQtnVwcTRRxKlC2dfOP8HDRwKVAxdYtOK000s0AhwIVczfPlPKKKDdD7AqU7dy88yoqsCqQhtjvU1gBVoAMHIAOkmFgF5VXs3YLywXJVyQGBSCAn5uTvDg3AwMjj65zYBpYvqK4IB8BcsPtuZkYQEBWO768AguIUWCAAFnVOKwKomEKBGUtkiFCRTnZYJCZmZGRke4OU8AmrOCaClYQa2UKB2ZGClJQBQwsIgr6WSAFoQpIQE6CkwGuQkxBJxWsgJ8BO2AVVNBKKKsIwakAZItNUomvAh8DThXCCsZe6nIcDLhVCEnJSQow4AHMXLwcDMMUAAAn+YWgSe4LXwAAAABJRU5ErkJggg==\'); }
#controls #forward { background-image: url(\'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAABelBMVEUAAAD///8AAAD///8AAAAAAAAAAAAAAAD///8AAAAAAAD///8AAAAAAAD///8AAAAAAAAAAAAAAAD///8AAAAAAAD///8AAAAAAAAAAAAAAAD///8AAAD///8AAAD///8AAAACAgIHBwcFBQXx8fH19fULCwvs7OwWFhbk5OTf398jIyPJycnGxsbCwsJAQEC5ubm8vLywsLBVVVVjY2NkZGSZmZlmZmaSkpJsbGxqampwcHB6enqCgoKAgIB9fX14eHh2dnaIiIh0dHSLi4uHh4dxcXFzc3N0dHR2dnZ5eXl6enp9fX2KioqioqKkpKSRkZGqqqqYmJiZmZmampqwsLC6urqsrKyurq67u7u9vb3AwMDBwcHCwsLR0dHLy8vY2Njj4+Pn5+fy8vLz8/Pz8/Pz8/Pz8/P29vb09PT09PT29vb4+Pj5+fn6+vr4+Pj5+fn6+vr7+/v6+vr7+/v8/Pz8/Pz9/f39/f3+/v7+/v7////////////QKu6sAAAAfXRSTlMAAQECAgMFBgoLDQ4PEBERFB0hJigpKissLS8wMDExMzMzNDQ1NTU2Nzg5Oj0+P0BBQURGSkpLS01OT1BSVFVWWVlZWlpbXF1dXV9gYWdoaWtub3Bxcnp+foqLjpCRlJqsssXU2Nna39/g4eXm6Ovs7O3v8fT19vf5+v39/ouR4UoAAAEMSURBVHjaY5AnABiGqAJNA0MtfAp0bJ2d7XTxKLBzD4/wcNDBqUDFzTe9ON7LQVcVhwJ1t4Ds2rJIN3s9fApqK6LcHCEqGKTl5VUtnN2QAEhBbVW0N9gdsgwcwvJmbj5BwXAQElsCVFBbkeDpYCQvxs3AwMhn5ZWUmYUA+TUgBbXFMe6W3MwMIKBgXYsNFLgqMECAgkkNNgW5NjAFkhqJKSkpqalpEJBTDZYvDFWSgCoQVNA2NUeA0CKQfGmcmpQAVAGLkJwCEjDOAMpXBmpIcTFgA6xgBeX+CjjkIQqK/BTEebDLM7Ao6CfnhQHNZ8KhgEFE0clFGZf5IMApIicjikeegYGNn5edYZgBAFrqh3wJgIS+AAAAAElFTkSuQmCC\'); }
#controls #parent { background-image: url(\'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAACPVBMVEUAAAD///8AAAAAAAAAAAAAAAAAAAAAAAAAAAD///8AAAAAAAD///8AAAD///8AAAAAAAAAAAAAAAAAAAAAAAAAAABFRUUAAAAAAAAAAAAODg4AAAD///8AAAD///8AAAAAAAAAAAAAAAD///8AAAD///8AAAAAAAD///8AAAD6+vr19fXx8fHs7OwQEBASEhLo6OgXFxcYGBjk5OQcHBzb29vf39/Y2NgkJCTU1NQpKSkyMjLJycnGxsYuLi44ODi8vLyzs7OLi4uwsLCqqqqtra2oqKhWVlalpaVaWlpUVFSbm5tjY2NlZWWXl5dpaWmSkpKMjIx0dHSIiIiGhoaEhISCgoJ2dnZ/f397e3t7e3t7e3tpaWl5eXl2dnZ0dHRvb29zc3NxcXFycnJ1dXV3d3d5eXl3d3eOjo57e3t8fHyCgoKGhoaHh4eenp6dnZ2enp6JiYmMjIyVlZWWlpabm5uYmJiYmJitra2cnJydnZ26urq5ubmtra29vb2vr6/CwsLDw8O2trbIyMi+vr6/v7/AwMDNzc3FxcXV1dXV1dXZ2dnPz8/Z2dnb29vg4ODZ2dnb29vb29vj4+Pc3Nze3t7i4uLo6Ojk5OTm5ubo6Ojp6env7+/v7+/s7Ozw8PDz8/Px8fH29vb09PT29vb4+Pj5+fn39/f4+Pj4+Pj5+fn6+vr5+fn6+vr7+/v7+/v8/Pz8/Pz8/Pz8/Pz8/Pz9/f39/f39/f3+/v7+/v7+/v7+/v7///////////+FvjIRAAAAvnRSTlMAAQECAwQGBwkKCgsODxEUFhcZGhsdHh4fICEkJicoKCksLzAwMTEyMzM0NTU2NjY3Nzc4ODk5Ojo7Ozw9Pj4+QUNEREVFRkZHR0lKSktMTE1QUFFSU1RUVVVWV1dYWVpaW1xcXF5fX19gYWNlZWVlZmdpbW1tb3BwcXJ6fH1/gISEhYmNj4+PlJqboKGho6ytsLGxsrS7vL7DxcjLzM/W19nf4ODl6Onp6uzt7/Dx8vLz9fb3+Pn6+vv8/f3+kCw9wAAAAddJREFUeNpj0CAAGEhRoG1qCQPmulqYCnRcopNgIC7QzRRDQUBiWnEZFGQB1XhooylIzJq3Dwa2ze/JTgoxQFWQlLN4HwJsnlGU5KeFqiAXWcG+vbOLkhxRFMRnzlyzFgLW7waq2D0rLVYHWUFwUklLKxi0dUzaBlJRm2SLpMDMIzYJAZp2AlVMTPJGKHBJTEqKdYCGk01E0jSgguVJfhoMymBpLY+ktPqqZD+48qTaLUB3JvmpMnDKgORd4lOnbF9YGKoLVWCZVLkKaESSEw8DAyOfnLZrXPrEvftWlIUZQxU4J9Vv2rdvl50UAxiIeaVM2LVv38pyqAJts4ikqUADlmlKgOUFNPW6QI5eWQ7zQ0JM6TqgQJ+mOEieX9Owcxco7DbURIaDQVRG3TJQWOZp8gLlOdT1J+/chwXs7tZUZAIqENasxiq/b46vpiADWEHDLizS26b7a0ozgxRwKem1r4MIrl4KAwv6K6zVJFkhnhTStOjbA5Lf2FgAAXn5Ppqa6uIsDFAgomkEVrHEUxMOFES5GRCAX8Wkeeu+fYvcFTjYwYCNhQEFMAurWXXvBSqQR5OAAyYhNaPeHXPtZZlxKGBgFlAxygtSF2LADQTVgS5jYxgIAABBtyK9hSmkkwAAAABJRU5ErkJggg==\'); }
#controls #refresh { background-image: url(\'data:image/png;base64,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\'); }
#controls #home { background-image: url(\'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAAB6VBMVEUAAAD///////////8AAAAAAAD///8AAAD///8AAAAAAABeXl4AAAAAAAAAAAAAAAD///8AAAA8PDz///////8sLCwAAAD///82Njb///8AAAD///8AAAD6+voICAj19fXx8fHs7Ozo6Ojk5OQaGhrf39/Y2NggICDQ0NAxMTHJycnMzMw3NzfCwsK/v7+5ubm2trawsLCoqKilpaVbW1uenp6ZmZllZWWSkpJra2uQkJCIiIiKioqGhoaEhIR6enqCgoKAgIB/f39tbW17e3uFhYV5eXl4eHh2dnaIiIh0dHRvb29zc3NwcHBxcXFycnKMjIxzc3N0dHR1dXV9fX18fHx+fn6AgICEhISKioqLi4uSkpKUlJSdnZ2WlpacnJyenp6fn5+hoaGlpaWmpqaoqKisrKyrq6u8vLywsLCxsbGzs7O3t7e7u7vLy8vMzMzBwcHBwcHGxsbR0dHKysrMzMzS0tLS0tLT09PU1NTl5eXg4ODg4ODh4eHk5OTk5OTm5ubr6+vo6Ojo6Oju7u7v7+/x8fHz8/Py8vL09PTy8vLy8vLz8/Pz8/Pz8/P19fX19fX4+Pj39/f39/f4+Pj4+Pj6+vr7+/v6+vr7+/v7+/v8/Pz9/f39/f3+/v7+/v7///////////8WVX0LAAAAonRSTlMAAQIKDA0OEBEXGBkdHyIlJiYmKCotLjAwMTEzMzQ0NTU2Nzg4OTo6PDw9PT4/QEFCREZHR0lLS01NTlFRUlNTVFVVVVdXWFlZWVpaW1tcXFxdXV5hYWJjZGdpbG1tbnFzdHV2eXp9fX2AgYOGioyOkJGVlZmcoKOlpba4ubm/wMLCxcbU1NfX2dnc3d3f4OTl5ujp6+zw8PHz9fX4+vv8/f7axrdEAAABbklEQVR42sXQ9U9CYRTG8Wu32HVRxMBuRQVFxUY8KnZ3d3d3d/fzl1qgKBedm5vf3969n53tHIb9IeaXQJSaESH4BgRKqSgjUj8QpVHPRFF2uD7gn1zcdnY7Wp4VzA2EEuo+Am7GKS2ECwikVL+P5656KctXF3hKqHob2JgBLrpILtQBYmpYAQ4qVct3OBmgVOFnIIijij1gq4WobO4RN00k89EGntGKkgXguJNkYqpZBfZqKMH7Dbi+/EcpSqYfcdZbIPcVSBSNO8BanVL8clJ3xsiOZcNzquYfcDqkShKxrHecsv15xnaLMoZlnUwZxsDcMUE1eQ+MlGa/7u8jK2i9BpbKpY6mhsxrzum7wCzlhKkvKqdB4LLWjdHEC10HxvLFmpUj8pqB80z7z6A/wMFInVVAIQfo41tr3mb83P8CtsbqLPUArbjAVIdWwxzga78DQYuHOm2mfAAbr9hEneL9eO/AxIXPkYcF8yc9AXz206BByzezAAAAAElFTkSuQmCC\'); }
#controls #create { background-image: url(\'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAABcVBMVEUAAAD///8AAAD///8AAAAAAAAAAAD///8AAAD///8AAAAAAAD///8AAAD///8AAAAAAAD///8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8AAAD///////8AAAAAAAD///8AAAD///8AAAD///8AAAACAgL19fXs7Ozk5OTf398nJyfQ0NAxMTEvLy/MzMzCwsK5ubm2trZHR0ezs7OwsLBUVFRSUlKlpaVYWFhaWlqjo6NWVlZfX19gYGBXV1eQkJCOjo6IiIiEhIR9fX17e3t4eHh2dnZ0dHRzc3NxcXFxcXGYmJiZmZmBgYGbm5ucnJyenp6IiIiNjY2MjIyOjo6RkZGWlpaVlZW4uLizs7O1tbXIyMi+vr7FxcXk5OTl5eXn5+fm5ubs7Ozn5+fo6Ojs7Ozp6ent7e3q6urv7+/y8vLy8vL09PTy8vL19fXz8/P29vb19fX29vb4+Pj+/v7+/v7+/v7////////////150kBAAAAenRSTlMAAQECAgQHCQkKCgsMDA4PEBESExQZGh0hIiYmKCosLjAwMTIzMzM1Njg5Ojw8PD0/QUJCQ0RFRUdHR0hISUlNTk9RU1ZXWVlaW1xdYmJjY2RlZmdpaWttbniDhImNlbS2ucPExcXFx8fJ1djb293e3+Hj5ev7/P39/lbfvFcAAAEiSURBVHjazdPLbsIwEIXhP46TkAQIFIqKuu37v0mX7QNUoncIuV9I7C4oJUKkC1Z4Y8n6RjNHGssH/j+SKwP+3DZ0W3/WPcBaCk/qQt6/tOeBK2ZTQbVOrR5gYw3BibDK88BEAMLAuCCF5UsT5/AYuLRt0uxBIwF/KbpVrgsE3ymg5MfUg7mYOSYwAoxgCKhqfZtCFcminPgDOQjGf/UTAMpcU8ZZK9Fh6NyNxelwhs7e08OQ1apdnAL19pUeU6g49wzANIBWAzpOVCdmZT8tTGB5A3qVAGpblh2wscMQYAqQPQOQrzugfnUscIe//eMKdkW3BU0D2IcBm+2FG6XRgN5f50GdCIoNux4QeY8jSVHkWQ/YRWhQZaj6FiaKrvPr/QAXq3IsfGlFwAAAAABJRU5ErkJggg==\'); }
#controls #view { background-image: url(\'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAACi1BMVEUAAAD///8AAAD///8AAAAAAAAAAAAAAAAAAAAAAAD///8AAAAAAAD///8AAAAAAAAAAAAAAAAAAAAAAACEhIQAAAAAAAAAAAAAAAAAAAD///8AAABTU1MAAAAAAAAAAAD///8AAAAAAAAAAAAAAAD///8AAAAAAAD///8AAAAAAAD///8AAAABAQH6+voHBwfx8fH19fXs7OwPDw8VFRUYGBjk5OQbGxvb29vf398gICDU1NTQ0NAuLi42NjYyMjI+Pj46Ojo9PT1CQkK2tratra1TU1NXV1elpaWenp6ZmZmUlJRqamqOjo6MjIxlZWWIiIh3d3eGhoZkZGSEhISCgoJ/f399fX1oaGiAgIB7e3tpaWl5eXl2dnZ4eHiIiIh0dHRvb29wcHBxcXFycnJ4eHh0dHR1dXWQkJCSkpJ5eXl3d3d6enqZmZmAgICCgoKCgoKEhISFhYWHh4eIiIiPj4+kpKSRkZGYmJiWlpasrKygoKChoaGkpKS4uLi1tbWoqKi7u7u8vLyurq6srKzAwMCysrLCwsK4uLjIyMi9vb3KysrBwcHOzs7IyMjKysrV1dXMzMzW1tbZ2dnQ0NDR0dHc3Nzc3NzV1dXf39/e3t7f39/a2trg4ODi4uLb29vd3d3f39/f39/f39/g4ODn5+fi4uLp6enk5OTp6enp6enq6urs7Ozs7Ozp6enq6urq6urq6urr6+vw8PDt7e3t7e3x8fHy8vLz8/Px8fHy8vL19fXy8vLz8/P39/f19fX39/f39/f39/f39/f39/f6+vr6+vr6+vr6+vr7+/v8/Pz7+/v8/Pz9/f39/f39/f39/f39/f3+/v7+/v7+/v7////+/v7////////fiifQAAAA2HRSTlMAAQECAgMEBgcICgoLDg4TFhkaHB0eICIjJCYmJycoKSorLC0uLy8wMTEyMzMzNDQ1NTY2Nzc4ODk5Ojs8PD09Pz9AQUJFRUZHSUtNTk9QUFFRUlJTVFVWVlZXV1hZWVlaWltcXFxdXl5eX19gYmNjZGRlZWdqamtrbm90dXh4eXp9fX5+gIGDiImMjJCQl5manJygoaOkpaapqquur6+ws7W2t7m5ur6/v8HBxMbHycvMzc7Q0dHV1tja3N3e4uPl5ujp6uzu8PHx8vT19vf4+fr6+/z9/f5dB/IHAAABrElEQVR42mPQJgAYhpsCAwsXT9+AID8vV2sjLAosPUJjk5PT8wpyUpITwrxs9FAVGHmEJxc2z1i3c8/eXZvmd5SnxXlbICsw9E5OaVh2/AYUXNjYlZ4cYoZQYBiQXLT4IlDmPFj66o0b17dUJ8eAzWBQ1dbW80tuOXLjxukF/adBCq7NmbL/+o3LE5OjgGZoMHBJ6Xkn1565cXVFSfEJiA3X27Kmnr9xvS85zEiBl4GBUcQ/e8ONG1srk3tgbliSnL/o2o1DpcEyzAxAIK7TeP3G4Zrk5IUwBXuTk4vW37gxV0eOAaKg7vqNgxURSQgFidGpq27cmA1VwKdusvzGjbU+VvUwBbOsnKZdubHPTUsIrIBJTCfy1I2r85wdjkEdmWvaffbG9SYdRTYGiApJnYwDN26cnNx6EuzNCZ27r9+41K6jzM4ABSzSOo7TLwDlzoIUnAcF1JpAHRVOBjhgEdPRjZ95FOaGcyurjHXk2BmQAJugko5dZu/Szdt3bFs9qcxdX02MkwEVcAjKq+voGNvam+vqaCqK8jAxYABWTn5hCWlZSVEBbjaGkQYAXbgKY+edZDYAAAAASUVORK5CYII=\'); }
#controls #sort { background-image: url(\'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAACB1BMVEUAAAD///////8AAAAAAAD///8AAAAAAAAAAAD///8AAAD///8AAAAAAAD///////8AAAAAAAD///////93d3cAAAAAAABeXl4AAAAAAAAAAAD///8AAAD///////8AAAAAAAD///8AAAD///8AAAAAAAD///8AAAD///8AAAD///8AAAD///8AAAD///////8AAAD///8AAAD6+voHBwfx8fH19fXs7OwRERHo6Ojk5OTb29vf398kJCTY2NjU1NQjIyPQ0NDMzMzCwsK/v7+8vLywsLCjo6Oenp6bm5uZmZmXl5eUlJSSkpKQkJBxcXGMjIxzc3OIiIh3d3eGhoaEhISCgoKAgIB/f399fX2FhYV2dnZ4eHh0dHRzc3ONjY2Li4txcXFzc3OOjo50dHRxcXFzc3N2dnZ8fHyXl5eZmZmAgICKioqhoaGQkJCRkZGnp6eRkZGSkpKWlpaZmZmvr6+dnZ2ysrKzs7Ozs7O6urqqqqqsrKyurq7BwcG5ubnKysq/v7/MzMzMzMzExMTPz8/Y2NjR0dHS0tLc3Nzm5ubi4uLp6enp6enq6urs7Ozx8fH19fX09PT19fXy8vL19fX39/f19fX29vb29vb4+Pj4+Pj5+fn4+Pj5+fn4+Pj5+fn6+vr7+/v8/Pz7+/v8/Pz7+/v8/Pz8/Pz9/f3+/v7+/v7///9qcTAmAAAArHRSTlMAAQIDBAUFBgcJCQoKCwwODxARExUXGBkZHB0eHyEkJCUmJigoKSorLS0uLjAwMTIyMzM0NDU1NjY3ODk5OTo7Ozw9P0BBREhJSktMTU1OT1BQUVFSU1RVVVZYWVlaW1tbXFxcXV1dXmFhYmNnZ2tra2xsb3BxcnR1d3t8fX6BiIyOjpCTn5+jpLK5u72/wM/S29vc3eLi5OXm5+jp6+vs7fDy8vPz9PX29/v9A1rUVwAAAcdJREFUeNpjMCQAGFB4Vh7Rsd7WuBVYhSVmZCdG2uNSAJQvm7+sNgVFBZICc5+4mv41a6bUJwVYYVNgFRRXM3UNEMytTwq3x1QANB8iD1KRGmmHrsDYLzGnfw0UTClODDFFVWDpm1g2fQ0cLKxNDLJBUeCWWLJgDRJYXp3ojqLAK7F7DQroT/RGUeCe2DJjxoyZS0ByK2YCmR2JnlAFOmDKMTItLz+/sBWkoLMoPz8vPcYZLKHHwK0IZtj5hYaGJzaCFLQnRoSGBjgZg4TV+BkYGIWUwUrMzJxioQpczczAQsr8TAxgwCkNtsgRpsAFxNOS5WNAACZhOQ19hAI9DUVhJgZUwMjKKWJbCVLQ7CDOycqIJs3ALCGvoGJQClLQYKSqIC/JhirPIW8EAuUgBU1gpgoPigJRo/ievr6+iSAF04CM3gIjSRQFUkZtqEHdZSSDokDMKHMlsvzqCjQT2FWNcpci5FdVGWlyobqSRdUoeCJMfnaWkSY7uj/ZNI0S5kDkFxUY6XIxYABedZPSeSD5xXUW2oIMWACHkknppDVrZtVZqPIyMOBQ4T9hcrKFKhcDDsCrbRQYZaQryIAT8CoYGakKMJAEAKNq/plfo36BAAAAAElFTkSuQmCC\'); }
#controls #settings { background-image: url(\'data:image/png;base64,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\'); }
a.css span.icon { background-image: url(\'data:image/png;base64,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\'); }
a.dir span.icon { background-image: url(\'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAHgAAAB4CAMAAAAOusbgAAABpFBMVEUAAAD/z2D/1VX+2kj/z2D/1FL+2V7/yFv/2GL/0lP+vFn/1VX/0lb/z2D/yFv/z2D/01P/1VX+w1j/z2D/z17/yFv/zlj/z2D+v1j/0F7/0Fr+wln/z17/y1v+v1j5qkz/z13/1GD+wVj/zlr/y1z/zF3+wVn+wlj/z1v/0Fn/0Vn/zlv/0lr5qFL+wFj/z17/0Vr/z17wqU//zlz/z1z/0Fr/z1v/0Fv/z1z/z1z/zl3/zlz/z1z/z13/zlz/0Fv/z1v/z13upk3/zlz/z1vupFD/zl3/z1z/z1v/z1z/z1z/z13/z13/z1z/z1z/z1z/z13/z1v/z1z/zlv/z1zwoU7/z1z/0Fz/z1vwok7/z1z/z1z/zlzwn03/0Fz/z1z/z1z/z1z/z1v/z1z/zlz/z1z/z1zqnE3/z1z/z1z/z1v/0Fz/z1z/z1zum07pmk3/z1z/z1z/z1ztnE7/0Fz/z1z/z1z/z1zql03qlk3plUz/z1z/z1zrlUzrlEvrlU3/0Fz/z1zjiUr/zVvmikvjikv+zVvmjkvki0rmi0zokkz/zltIgvf6AAAAgXRSTlMAAwMEBgYICQkJDAwPEhIVFRUYGh8fHyIiIiUpKjIzMzU1Njo6PD5AQkRERkZHR1dbXmlzeYKGh46QkJyfoaGiqqurrq6vsLCwsrm5v8DDxsnQ1NbX2drd3d/g4uTm5ujt7vDw8fHy8/P09fX19vb39/j5+fn6+/z8/P39/v7+/v6ZEokuAAAByUlEQVR42u3bV1PbQBiFYVENCd0QEnonQCih9947xDRTjCHUUA2yAC+QFS2BP00dZriTd4W+Sea8f+AZjcrsxZGiIIT+lxLrnB7GDcQ8zrpEs9Tg+MohlzH3UXYNVcYHm+JmNLkNqi+2uynDBDkgpc0v9oluSwmQhu0jXKARu7Q7rInA2rCsXLvNhdqulXOjZ5kYzGYjg2Tg714umLf0gww8ykRhNvhZBt7kwi2lycCaOOzJk4HFXX53sGuwnfmuYhPh6yvdaDdHE+UksK7fzqTTwPpJOxF8s0YE/zkmgvVTKvjs34cv/l7603lJflZyXKgJML/wL6b9mmrMNgMWyLfeHEMCc66Ol30kgR+OLjVJJDDn7mobDcznUolgrZ8I5qsJRLCvnghmTiKYq1Qwo4I5YMCAAQMGDBgwYMCAAQMGDBgwYMCAAQMGDBgwYMCmwBqNqynLNPCiMsAoXNat5KgUsJqp2BwEl8wcYYpS8dN6eKXiYY8R1bFvtbvfE/U4QfnS57X4Bvd+el7dxFbtWenuVcW+7IwCIwp+/LbsuRorjAh8HXWFF7VMb/ks+F5tTDZ8DXmzJ/vW6VLf/b1ihwutufhDAyEk2z1pOUwKxC9HTAAAAABJRU5ErkJggg==\'); }
a.dirOpen span.icon, a.dir.unselected:hover span.icon { background-image: url(\'data:image/png;base64,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\'); }
a.dirOpen.unselected:hover span.icon { background-image: url(\'data:image/png;base64,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\'); }
a.dirDrag span.icon { background-image: url(\'data:image/png;base64,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\'); }
a.treeDefault span.icon { background-image: url(\'data:image/png;base64,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\'); }
a.dirOpen.treeDefault.unselected:hover span.icon { background-image: url(\'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAHgAAAB4CAYAAAA5ZDbSAAARrUlEQVR42u1dCXBU5R0PHlUrFmupTo9prT2mrTOdHtZKsrtJyLU5N9fu2w0EkpAsEOS0igdHEAUjyqGgckZy7NtdAkitaBVBRXE6KIqIZxXRosUBDCBHNPv+/f/fWyXk3Pe97+2+LO+b+Y+jQrL7/b7/fSUkmMc85jGPecxjHvOYxzzmMY95zGOeqB8A5/kQHPUT8AujQqK7SfILb0h+9ykkiDKdlEThTWm9Jwh+TzWsL74GttVdYCLECixeHjSN/BECmwmie6YUcG/AS94jie5D+M+OGAB8AgHeJ/k9z4cCZUvB5xoNPs+1sMN5CUDCIBMxNeAGnd9B+j0Ce3uYY7+KAaD9gO3eDj6hFhoqrgaoO89ELWLOrbgYAiOSkFNa8SKPGwzYznQaQd4B/rJiaCy/1OTiSMBd7r0QAp5C5NpX5AvkCYiI1CyA1OACaUUpSA8Ug7SoCKQFhSDVI92LtAD/fTHSshKQVjlBWot/1tfrz5TwER4LiUIDqpHfmFzcH7jEBT7XyDC4IU1ANiGQy0sVAGflA0zJBqjIABCGAzhSAHKTAew2hbK6EP23bPz/eUiF+GfL0gBqMgFuzgFpbgFID+LDWI3gtwj0+75G2gmisxweyL7IRLF3nTsY9W0RGlLPMnOuLwzq7AKACXYAT5oCUE5ydxBZiICnh1GSClCeDjAtG6T5jhCCvQ918SxYP+KnJpI9ukF15yG416G4CyJQR1WDurJU5iz50vM4gamGClNCMDJtH7hSJ4Aj8UrITzFF9VkA+zxDZTfI7/5c0WsRimEEFqbnxg7Y7tSG9BjYk6sgwzrERJbAdWIQI+ApQdBeQmrvF1gyklAHQq1d0aWxB7UnkpD2IM2CbMs1kDns3A2GwELnJYpoFr7ol3vRsCG9B67hPRtHxqPTSNuRaiEn5VID68iEQfsqUi73V1r/3FidVNxcnSTwotfm22e3N7s+C/UVmSK35lYUxaWpAwHUHqktz3pgl5D0r8CYxOVNNYnLOlNztWWJOCZpalO1JcXnTRkaVXC31aVcsGrMsF+t8SZNFasTtzTVJLUjAS969/48+Non9M61S1Acj83iZwnHkE5l22CPywobqrrdQwfS+801ScGm6sTKhnE3XB01gBurb/hdk9eyhieonSkk9gLsGvQzZ+QBWqQDHtiudDTPBltHWkCs7n4fjTWWfzd5h41tnvi37+kO7gfe9CHNNYlz9QJ3623pvYpkGJdpFMtYFzqea4NdLgu09AByU3XSk6vGJtp0B3h1TVI26skdegH8/sL87q4Phg1hRFrcAtuZJKT/FFtgU0W3u9m/psZSrzvADTWWaWJN0kE9wPWPt8KxhtJOAQuk+wsVQ8puOycAJurA73rAgXq58qz7+bKpxrJOd4Cb8RXxNqrOiOc0ONXkOhONmpkXHb+WjDX6PRRudKOkGIk0Ol2JUxONSldizxSzLkpVbIAoPLhD+VZ4uvyM0YUG1zO6A+z3Ji3SSzzvqc9RrGeiux0ABTqCS7qcQJuMseNZeSDdV6gkDChuTRmjBqfiihGRcUehz0dKZAteusehuGjjshTpopM1T+L6CIL8jzPiepvuAIs1lt1IwJs21Nrg02UOBVyylHmDmxNOCNRmgXRngQJYi6A93UgZqqUI/G0IeFUGSDpInCP5Nni2nO4pqe3dm1KG7p2adcUH09OH7P575qV767AIoo5jSlL0Wh7SA+Att6RB26pSmZO4imUSpc7hMgByrrdJ0CfRT/YCcv3R2bnwiZAM7dl8OflQgQ3+WWnZOWAB3lmXBR0o+mQu4wUshi+lu5BbH3VFraLjo8UFsG6cFZ6osMgBjC9z+QH9eYHtyHFX8vUDDuD1tVb46O48JVfL4zIoP4vJeFmnijEo2xEFhZrxYS1CieTmFCcnNTPRrqgWUTWFkA7j59uFMf5WCLhnQNCTDJsKLtMd4K0TUqC9Kp2fSJuRr2SYjFKnRYYafia50ICDZJJm5rPYD1K40uREOAX7HgK+GURXFVbNXPlt7ZgeAH84Olwuw0vvVqKx81CJsYrxCJD5qILKhmv/fmRTUNmRts9EgB/BaplNWDs2Qs6/E8g8AfYjPT/KCiE93Az0WQ0HcjieLrtXWh80qjNZMmgFOeA+GPJ71iLI2RAccwVXgDdVWeCzQh2DBcTJLYLxQEY/G6Zma9PLJKpvz+Px/YiTPwz5hDnQUvJrbgAT977tRO7VOyI02a4EKowGMvr7VJGpKTOGUTW5vFf75/kS9fF6WFvyJ24Ao08HJ3OiENslUTjZbkxORvdNBtmuwRUck8nju51E2oI1cAVcAF4/Bg2rYmt0g/hG5mQMlzKHO0lUz3NodQexBUh4DbsxHFwA3lpuhRM5Uc7SGJmTV6JO9mayfzcKkWp7vF9TrxcWOno0AxyotsB/C2OYjjMqJ1PxoHs48+OVZudr42BReBVFdLlmgJ8abYGvsjVkgLKT45eTMazKHIfHlKaG74Qi2vM2+sIZmgBeh9z7XqmVGVxqBIPpOXzSc0bkZARILtq3M+pidouaas9fhhbnHzUBvBkD8G15jNYi9hXJqcRGtDxvtGsH2aicTKLaySiq8V4Ys2XtIX9ZC4roH2sCmPxeieWDY1WFnITvGvbjUV1pxLAm1aCxxK3x70j3M4UwQ9QmpCkW3Yqu0TGW1Bnq3F5FD4HsHM4vrCkaBGDkQrm1lUXSTcpW//sC7k+oi1MTwFtGWdiiVlhW06vYiWdOxuIEJjWEljhDJu1ljEX/khlgco32Oq1shtWcCMz/eORkzOFCTQabsbVElZgOhUShUVM+eCMmFT51WNle48Mlkafi4o2TsXaMpdCfatpUPNLjyL2VmgB+Gn1fptKVaTnqrNx442TqdR7JUOVC1SxrXZHq34MQLP+FJoB3lFnVBzdY/Lp44+TmcJyaJVe8vDTSWPgzmkt23im1qXePilMjf4XxzMnkMqk1Th0pckdIJD8fa7PGaQb4QCGD/qU0mKixPCYeOJmKA9R+D7S+pbrIYtMQrPyhJoCDaEEfzWPwfalaQevlEMg8wpqx5GSst5bnjaj9zFMj8IdF93Oay2YfQwv6tFr9iy9Wbg/hEdvlEdaMMSfL34HF0Orv5/qFGzUDTNkj1QEO7PeR20F4XBDFrnklKGLFyVgmqzqLRgEisW8PBLNHV2kGeFu5lS16taKU3wXxSlDEipNJ1aj1h4lJ+oxoebZC0DlEM8Avl1nZkgsNnNtNBjInLy5S2lbVWtJrXH1ZzzNoeq9mgHd6rPo66ucCJ6O6Uj1NiMK8q5y9R69EdxqX3qRdbov6y6MWT71aT2ROzh1YnIzhWnkOmFpXqfdgx4vQUPJbLgC/LljZEtd6JuIHGic/UqpMF1ALcC9x/JDoWopdB5dxAfjNMsbKBL2bxwaSTuYL8FF0j7zc2kffr2To9x2fFZ3uwIHCySSiBW4ieg+2jQ7jBvD+2nTjGFkDlZP5Glmbacg6F4Bp7sbBm7MY3aQoVjwanZMXF7O5Sd1dzVPoHs3m1uH/5JRUOH5XPlu6i2egY6BzMq9Ah+g+RF393AB+bnq6vMhCdZiNZ6gyHjiZ5oWxhCq7D3Tdz21GR3CsFd6qz1UantWOROKVbDAKJ2tNNkzkk2zARu9F3ABuHW+FwyuLlaoEtU46pQtp7FGscrC8OVmLT88xXQjr3FZuAG9G/fvtD67OjG7C32icrKVNhlfCH2uvuI5R2jM/5wzA0xianLWU7BiNk7W0yXAq2aHoFVeA21Z30j3zGKxAbc1U8cHJHIvuIOCycQP4iSkpZ39Q2ojCsluBlkwZoTksVpzMr2z2A2gp+z43gHfPy+k+rb0qQ7/C93jl5Ll8Ct9DomcFPO79LjeAD68s6XmiDEvrSl2BcfqFosnJcusKg3Eqt64Uny2eRbcQ8ZLM/gB+atpwONXo6nlTCkt3fl/NZ/HMyfyaz96jFYHchpG+eqcdvurpZdLFsBSj99U+Gq+c3KyhfXRyV//XE5Qbu3kB/MmDvUeg4CbGmVBdG8ANw8m5+nAyxwZw2pbObZwwJRfk6FVfeU2WSTKdRzgYbXAK7zaZVRpGOEzsNsJhPwQ8+dwA3jEzs2f9q1X0fGNwLS4yHsA822RoAQjtOGYdwnJvYdfs0fazOge1AByg5MI9Of1fCL5S5uVWIzE79XCp8UDmycms1H2MUkfI717FbeL745OS4dOHIjCGfIzmf9jgksWQEUU1T07mMwitTR5sxgtgWqpxsjFCQ2hhkRxrZp94nmesie6x5uSeRxkegLXOn3EB2O+1wCtz7OrdDNYvRImIeQ5jAhxtTu51GKnwBLelHOvG2+DjB1VeOJbkaNphgEUEslHhO4c5mYyx6p7HCYPfU80N4A21ydDerDK1J4b9Yi1+JLkW9xaeu5zcx0Bw7Du6ghvAL83IZLsEGts3JlPbl6Tynrscxgpn6tGE3pNovqPXkf5buO5N+vgBxoSAGDa4tO4EppfMZ3+BPgNGeZX/RLiUo8/OBbUA+71W7ZdAix61biGxh5vWlhlw00qjxtH96tbqSLB21A+4AfziHZnaL2C1xonnnf1kyj6RXmox1mIs5jHBahdjBdxPo/4dzA3gj5ZwclcIZJ6r7bwxXG3X2dBCTpN3EvNabUfDRn19jUXy3ALLvRdyAZh2Dp5YyzHLU1/Ifznl3OgupzyrI5C4ltf3+abStO+Oj6MR115FAvD2OzLUu0f9GV16rJctRaBvzVPWy1IyRA+uFsM7CmkqPRWq53P+DhSHX+3sdyySvNyKF8DvLsiHDlHgf1F6Logez3lBND2YpcXfLoiWHyfvZV9kV0SQSaPOBU36tzPAGyckw/8e1il9F60V76TzJ9kVo2VBlxXva7queHeeWfGOqiQaK97lh0Lu0CORZNCEL86aGqsV4G3T0+BYg46pOwKZ5kM5ohDTJeubHhNxOBlEVKo6Ol3JzxJhKo64SG7CpiRJvg5c2mN6NE3NltHXEeC/cgP4tbuyoUPvODD9fKzQl7nEbotdrjXaRBKhSmWZEq2JXei8hAvAGyfYYN/iKJWziuEapRFp5wa49vCMkuWqpOMJ1bVXfQH85NRUOLSiGAuqoxwsQANJc1jTyIQqoo/4cq8j+bFy8mMEOJEbwNuwsfs0BvdD0Q4ikLFDFnYsqib05lrKjN1XxFLIcBoBfiGB12mdYFtO+pcCHKFYRYmoiH5sln7WazSJjMhbcvr3cXtdKomVkz6hlhvAGyYkr3ynPj+2AIdFtuyq8IwURZvQUqd0oobyozZ5sfP64mu4Abxpkm313vpcONXkMkYgn2LY2IUoV0/YB4iFjB37coO2trqyDnnmVfOIogSeh1JRoUDZ3dQxHt7/HnuQ6aKoRbXWHh2/mZWobwiljhxF0+Zi0r1/HvIL9bCx4nK+AC9zDkaA61A07FMsOAPlXcXwsmUK8tNcCyNY3GQQUr8uJT34VZ0cRwbbAK3u6yLuGowY4KB3CBV04S/ZHVbyhlybLgcJ5jpiBzQNLqOU5bxwNoufvXJa7lgIeEoi7vlVBfDm7IsgUJaDHPyYrOSNCHBXsCnFhoXhclsIxZ+pmpOXBW4P558p2kaPiaYS0AgomtTH3whtl8H1uUaDzzMUIGEQf4Dxh0JL8c9DAfed1HuqbI82OMjfiG8SkRQhovjurHylT4pizRRjJt1NQNltPRtr9nDMmqQBcSdF1qhDA0txZPFLiQpydfSpJJFksSwKLyC4hX3OmuQC8raKi6HFeT26SY9KoueQ4XSxWuAJFHK7CHzKGdMDoOwSZY2oLBdz1DQvUq73oqwSzb/wRe0zkrX8WUgUGqC15A9Ql3JBgt4Hufg8ekVYPZCKaapWqiQwjEUdX0T6dgeIzgpY6bkKfcHzE6J1ZFFNemCDJyPkE+bgh3kcQ2Z7kQ6HX50JEIuOVdzPVzF5L6IxO4VSgBCsHQwAgxKifWSDy1dyLS1YCvnL/AjuG5R4NgFmopAiCT1vyS6QX7gZ1eBfuKQAzWMe85jHPOYxj3nMYx7zmMc85jGPec7F83/0yG1TCLIYkwAAAABJRU5ErkJggg==\'); }
a.linkdir span.icon { background-image: url(\'data:image/png;base64,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\'); }
a.linkdir.unselected:hover span.icon { background-image: url(\'data:image/png;base64,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\'); }
a.linkdirDrag span.icon { background-image: url(\'data:image/png;base64,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\'); }
a.linkfile span.icon { background-image: url(\'data:image/png;base64,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\'); }
a.trash span.icon { background-image: url(\'data:image/png;base64,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\'); }
a.trash.unselected:hover span.icon { background-image: url(\'data:image/png;base64,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\'); }
a.trash.treeDefault.unselected:hover span.icon { background-image: url(\'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAHgAAAB4CAYAAAA5ZDbSAAAzNUlEQVR42u29eZAc133n+XmZWVl333c3GvdF3AABkuAlihRF3VJYGNtjy94dr2dmN6ZjYzY8O7O7EbsTG7Ex4YiJmA23xw5rZi3Jkg8NJOsYWaJ4EyAAXiAJ4iCuRgPoRt9HVdeZ13v7Rx1dVV0NdFd3Q8cwIxJdqMrKyszv+/7e7/f9/d57Br8kW19fXzPw/wJfBP5lf3//X/LxtuLN+CUB9xDwQ5/P1wPgOM7/AXwM8K8DwH19fb8HfL2+ocHf1tbOtatXAL75MTS/4gD39fUZwB8D/0vv+g10dnXz9punAY4D/88a/N7ngWeB7/X395/4GOC1BbcO+K4Q4rm9+w7Q1tHBKy/+HKXUSeBr/f39cpV/zwcc13U94HleX19f33eB/7O/v//qxwCvTX/7LcMwdj32xCdoam7hZz/5EZ7nXQe+3N/fb63Bz7rAHU3TNm/fvp2ZmZnfHBsbO9bX1/cd4E/6+/vPfgzwyoEVwKeBbwSDwY5nnn2OhsZGnv/pT8hkMgng8/39/TNr8dv9/f2qr6/vIcdx/urSpUufPXLkCHv37tVOnz7ze8lk4vf6+vreB/4j8LP+/v6RjwFePrg68DvAn3V0doaffOppgsEQ7793lsmJCYDf6+/vv7KW19Df3z8NfK6vr+8P3n777f+4e/du/x/9qz/i1Ok3uXjhwwNjo6P/WUqp+vr63gD+DngDuNjf3+99DPDdwfUDfUKIf7dh4ybjiU88hWH4SCYTXDx/DuCv+vv7f3i/bri/v///6+vru3HhwoUf67oe+Y2vHmPL1m2kUykuf3RJ3Lo1+PjkxMTjnucBzPT19f0UeBF4D7jc39/vfgzwPLhh4N9qmvZH23fs5LEnPoEQAs/z+OC9s7iuawH/2/2+6f7+/lf7+vq+fO7cuefb2tqMB488jJQee/cfYPfefTiOw/DQLW7futU0NTX5u/FY7HeVUgDJvr6+l4EXgLeBc/39/c6vHcBHj5kmcBB4BNgONABm/mMBCJ8WIuVM9NQFOg/tP3CQhx55FKUknpdzkEdHR4hZN1MDsz//z0ePmSL/PSr+rvS9au8DiLNjfyE6wvtTL70k6nfu3Imu6Xieg1IKXddY17uB7p5elJJYlsXoyAijI3ciMzPTX5qLx7+UZ3ji9//ZV26Pp84xZw0v59oW+8wDhoDXgB8CF04ft+V9BfjoMXMX8GfAE4sfJWgL76U+2M3Djxzl4IOH8TyPHAkUoNB1HUMEmgTaZxTyF9K6x1PnaApu5Xvf+x7/9J//jwwNjwAKpUAphVISKRWGYdDds47Orm6Ukriuy9jYGCPDw9GJieCuOrOHuHWbwfjLeNJe6WVtAZ4C/i3wnaPHzP/19HF77L4AfPSYuQP4e2Db3Y4TQsPUw3R2dnL4oYfJt/b8g8sdc/DQYeIvx1hX9yhDc6eoBWQNEAg0BJ2eyVecdtqlSaMyqcMgoDRMpaEjiseCQiHwkHgonNRNvDuT+Iaus76+kfFHnyTd1pk/WoEC1/VwXQdN04v319HRQVtbG54nuX1rkEsXco/y+uzPVqv9CeBrwLqjx8zfrhVkY5lm+V8WwDXNIHu2HWXL+v001LWgGz4EAiEEUkqunx9d9FxKwbreXo48/AhvvwltTb088sRhfD4zB4LIgyHEvP0SOXimp6eJTU5RNxvn6NAU7bZLMGPh8yx8tk0ewyVs+rxR9LIwcA0A/9gIvlgMNxzCjtaRau9i5PCjZOvrmUsm8aQkGAwVmQ6wrnc9ruPy0SWHf/evfowoXHfx+kvuRRTeKbnP/Aeua3Nz+BKvv/33XBl4F0+6AJ8Afhf492vN4G3AVwAa/BvY0PQEG6IP8MTBT7Fhw8ayA+fm5rh98Vs0NDbeFeQtW7djmn7OnDrJyLUsv/WPv0ooFFpwrG1ZnH//fW49/xOO3LpOh5Ul4nkEpMcCRNXKaBMcGc49mHSSwOQEdTeuY85ME5idIdnezuDBRxiMxQhFowSDobwpVyQSc4QjUfbueGxFv79721E+99Qf8JfH/y9+8MKfoZQE+NrRY+bfnT5uD68lwE8BraYepiv6IN0dm0glU3z7m9/g4UeOcujwEQwjd7obA9eZnJzk4IOH73nS7p51PP2pT3Pi9Vf5T3/x5/yj3/pt/H4/Km/PJ0ZGuPK33+bxkSEecSxCTmUfp9a8r26+chGA8MQoZjzOZn+As1t2MtbUSlNLM6Mjdxgeus3OXbsYGrpddl1KLX6dav5DhBDU19cTDkcxDIPPf/IPePODnzIyfgNgb15H/8s1AfjoMbMB+DyAJnwE/fU8cvQx1m/YyOk3TvLmmdO8/vqrWFYWT3oE/EHWr9/I5i1b79JPC6KRMHXRCMGAn2eefY6Tr7/Kn/X/CRkrRZtt85l0iq3ZNA96Dj7vl0NvaLw9CMDDqSSW4/ByaycXHQ/d0Lg+cJnrA5dzJljMd6VCUGayS19LqZCuxGca7N23l63btrFxwzbaW9ZzZN+n+eELf174yteOHjN/dPq4Pb0WDN6d7wvQNIGmg+d5+P1+nnr6Gfbs28dbZ05z48YA6VSK+oYGHnv8CXw+X97BUsUW7DMMGuvraGluRNd1hkdGSabS+Hw+Hn/yk1x4+QX2XR9lWyZBt5X9pY0vG0aGAHh2LsYR3eCl9nYGjDpkCYj32qRU2JlcaObYLm+eepsPzp7j0OEjHDnyMJ98+Dc5+fYPmY6NAjwJPAb8aFUBzseony7EuTu2HmRXzyFisVjxmJaWVj73hS+hlMJ1HAyfL+e/lHjPPsNHS3OU+mikaJ5u3h5iaHgEy8piGjptA9f5J0MD1MUmS0LDX+6txcrSAoRGPOLmJK+0dTAYji7tyyrX8A3DwO/3o2karuty5tQp3jpzhkMPHmZj974CwAL4x0ePmS+ePm6nV5PBncBnAXTN4OEDn6E50MuNgYGqZtdnmmX9i2HoNDXUUVcCrJQSx3G5PTSM67pEx8fY8s4pWm4PYmQz961vXc2ty8rQZWUIuS5vNrXwXnML3j3YrOkamqahlCIYDBIIBDBNE13XicfjnH33HRy7nZbQDqYz11DK+yJwCDi5mgAfyqtWtLf0cnjvp/AsjffOvksqmSQciVS/eE0QCUWoi4bRNA0pZRFcKXNiAdKj46MLbDn1KuHYbK5FC36lt/WZNE1jIzw0M8ULHd1cq6tb1Bp5bu5ZBAJBIpEIfr8f0zQxTZOWlhY2btzIzZs38U0Eifg6GU6cDrjS+s2jx8wzp4/b7ooBPnrMNArsBdi59QhtzeuwLIvm5hZu3hxk1+49FSyGgN9PXTSMoetF1hbALbz2jd5h3/M/pu7qZcxsulyw+xXfop5LNOPyuZFhXnXaON/cUt43K3BsF9fx8Pl8tLW1EYlEiuCaponP58M0TTo6OhgaGuL8eQNN6NyaO/mPPGl9HfhwNRi8sQCw3wxyeM+zecAkU9PTSCnLADZ0nfpohEDALAsFCqxVSiE9D86fw/9fvkPw/Icgfk1QrRYCWhk+Nz7KtkSC1zo6mQqG8DyJa3tFs9zd3U00Gi0CWm3fu3cv0WiU06ch6Yy3TqTOf/noMfP86eO2WinAjwK9AN0dWzi46ykARkZGee31k+zdtZOJiXHa2zsIBQPURcJomlgAbpHBnod4+wy+73wDbfj2rzW4xbDKsTkSn8H0JC83t3LLH0LTNJqbm+ns7Cz2u3fbfT4fBw4cYHx8nFh6mNnsjd9xvNS3gcGaAT56zAwBnyv8f//OJwiH6gG4cPEiUkoymTTXrlxmx7atBAP+BUF8Wb/reWgnXsP3N99CTIz+qvlQK972J2MEleSNng1kt24lGo3eFdBq77e3t+O7GEJD3wY8B/z5Shi8HfgUQDTcyIN7c+bZti1GhofYuW0zmzZt4hNPPrEkcPVXX8L4zjcQM9P8t7ptT83RPD3K+WwvMx0ddwWzctc0jeHhYbJeHFdZAL9z9Jj5vdPH7claAX4aqAdY37OT3VsfBuDqlStMjI2ye/duvva1rxEOhxeAW+ZUSQ/91Rcx/uav7hu4ye51+GdnkD4fns+HIzSkpqG0nMjvNwyE6yIcB8220RwbI5O+L9fWMjXJ/nPvcj0SYW7LVkzTf0/zbJomly5dYnh4mJnMdTxpFbrPJ4DvA/T19QWBrwIh4G/7+/vnFgX46DGzuWCehRA8uPsZdN3HxPg4z//sp2zcuPGu4BadKikRr76M8dffQkxPrR2gnd2YiTmy0TqsljbGHjpKqrkVD4VE4HoeiUQC0/TT3tkFiKJPoDwPX3yGtrdO45+dJjA9iZlM4FtDwBsnxtjx5kmG6urIbt9ZBNkwDAzDwDRNAgE/fr8fn8+Hrut88MEHNDU1s61hO2fOFcvXfufoMfPnhzr+mT9vrr+aj0XeBc7ejcE7gcMAjXVt7NuZy+1f/ugSSkqeffbZBeAucKqkhDffwPje360ZuLO79qIcm9tHHiXW3okUAs0w8Pl8ZQ0NXSdcX09DYzPCMPLv5zLQZiBAqLWFxOYtTGYt7EwGJidoO3OC6NgdIjPTmOnU6odSI3fYeOp1Zrt6sDe2oxRIJbFcieVkSGUsQqEgjQ11zM7OMjw8zO49e4m0buP8tRMk0zGATzYFt34Z+LLQtC8YhiEcx1ElGY6FAB89Zmr5zEUYYPP6fWzu3YvjOIyMjLBu3Tq2bt26OLD513xwFt93/wbtztCqPhi7sQnp9xPbuJmhA0dIRSJ4at56eI6DrusL/ADD8KEbPpSSKAV+06S1tRnLsojF4qRSabKWhW3bOELnzkOPo1yX6OQ42z48S+vYCMHk3OrdiIDgtSuIn/yQ2d/6GtnWDsgXREgpsR2XdCZDLBZnZmaKdDpNKpXiyMNPsX3TIc5eeJmQr6W+PbzvT3RdrwtFono2nQKlXgNu360P7ixkjgzdx4N7nkHXDeLxGeLxGF2dnWiaVgTWcRwcx8EwjKJixeAAxk//K9rgjVUFN7VxM1OPPM7Ylm1kHTdfBqQW9Puu6yKEKGOxZWVxHBtd16mLRmhva2FsfJLpmRmc/LmklAgh0HUdUHhAvK2DN596jlA8xu6PPqTt1gChufiqgRw4/x7Rhnqyv/M/VJWqLdvB9Ac5cOgIH557j/qGBh499EVu3bxJW+Ag0WB7Y119A1Y2i+O4ceDf9/f3T94N4MPAHk0YtNZvpM5cxws/f57rV68yMTFOT3d3LqnguliWRTaby/hEo9EcY1wX3999B/2tM6unTClB/MAhJh5+jMnWNizLXmA1SnfXdTHyprgU+Gw6Tc+6ddTXRbk9NEIimcTzqp+jUF5U2OfCEd5/9Ck6129i+9unqJuZQndXp6AydPZtwtt3kzh0ZD4HUYwjFZqms3HTJlCKd95+iw2betnU9DS6CBCtb0B6Hpl0GlBfB15Z1Is+eswMAF/w61Gjt+5xwmYLJ145jW3ZaJpGa2srvb29pFIpstks2WwWpRQNDQ25S5ES7fVX0C5fWl3ZUSiM6UmsuTjZurqSojhVVQb1PK8o4ksp0Q2DxmiU+oZGlFLcuHkLy7KR0sPzPDxPlrwu3WXZa1cqxjdsZrxnPes+Os/Od08TTCVXfHtaOkXjT76PE46Q2rZjUbqvW7+B+FyMCx9exGeEiNY3gBDMxWJI6Z0C/rS/vz97tzBpPfDpgNFInX9dzlQpCIVC7Nmzh8cee4xgMEgsFsO2bXw+Hw0NDfj9/pxpvnwJ/aXnEbHZVXdKwkO36H3l52Sf/gyzHZ2LsC63CyEIhcPouoHh8+XScJ5keiaG7dg5B0t6RfMtpSwBWS4KvKYFc41GCAa37yYTCLLz7BmapiZWfH++sRGa/uHvSW361yDmWVzKZk0TbN22g3h8jqxloWkaiXiMjBUnlh38QUtw59C94uBdQFfanSbLGOua92JlLVzX4cKFC0SjUR544AGi0SiRSIRAIFDsd6Vj4//e36JfurBm4UXd2B02vnkC66lPk6qrL7+RfLxoGCY+04dl2WStLI6brGgABcerUBZbfa8EHAS6bhSdNCXgTu9G0v4Au957i67hmyu+P//QLereOcPMgw+XgVv61zRNNm/ZyrVrV0nMxUmnk4wl32cyfWnHrfgJA3DuBvB2QDheirYNQb527L/H81wc2+Gvvv1tvvf9H/Al16O1tS13o1LhSYlSHt3Xr7D56pVCreIabYLmgaus0w2uHDpCsq4h53HaNrbrFk1yaaFBMX2jcgWzINA0UdXML9wlUgJINE3Hda15gPPHjAPjW3dyCMXO4VsrM9VWluaf/YBMSwvJ3o1FX6AS5MbGJnRNI5WYYyp9manMZaRydwNNwPjdAG4vvOhoXVfsWwG+9KUv8dJLL3P2g3NImfM8VP4ptmTTdE8NYybmWPtN0Hv1Eolshne2bCceihT7e1H6r1iYfyx9r7Rmaj6HXXyV/0ifL2vNZXARolBPJYp/U81NfBDYj+Yz2D44sDJTPT1F80vPk/jv/nke0HLHL+d0aUSjUTzpEcveLBTb9+aVx7sCXBSV/WawnNrbt7N161YcxymrBkRK6v7yzwldeuc+JnQFu24Psnn9RpJPPY3X0bnK6eTazqRNjJH58fcJvvoieWNRm79x4yrh61eZ27ipgsXzoy66unq4OThANNlJ3LoNqAYgcK8+uOiBZbKpoqkr+4JR/hVjcADfzPQvIFsvCJx8BWUYZL74G3jtnfcItRSlwcfKwrYqJbCA19pO6jNfQKRSBN4+XfPp9XSKtpd/xtw/+Z8WAKvydVyhcJhIJIp/uh5NGEjl6POmZ3GAi8Mj7ozfIJ1KLf5A8gFi8w++iy83DPQXsAmCr76Al80S+9xXcNraFwWgEoylArfs70TqsT7xDE1zcYKXL9YeG9+5SXjgCvFNW8uALYDteR6OY+NJC6U8gEylg1UN4Ev5exGDQxe4M3aLunDTojdqTk3gOg6/2OSuIHLmBI70mHj6OazmtiWBvCiAtXyv4juJ5jbshx+ja2oSf40hlJ5O0/Hai8yu31zVAZyamiAxlyBpjxXGdU0AqXsBfBG4Dmwdn77FqbM/4rEDX1205W588afUv/9uzdC4wdAqpekEjW+dwrUshh5/hmxzy8pNc8GJrPE7yZ71GD29rJuarJkAoZEhgqN3SLZ3Vog6Hrdv3mQuM8acfadw+FVg+l4A387nF/+N69q8/u53aYz00Nuxa2HMNhfHTa0MnOneTUhdp/PSuVUBufWDd7Edl6F9DxLPpwXvxkq1zH52Od9zHIcPzBAPN7bw4OxkTXdkpFN0nnqFq1/+7TKAx8dGmZwYYzJ9CdsrKmlvAsm7Anz6uO0ePWZ+m9wgs+3TsTt8/+U/ZueGx2iu70ET81HuJy7fomvgTs1O6p2GKD9vM/CE4Gh3K5snZgk47opB7r74Ac7YILc2dzPY0rD0vnj5dK3wustPYCdMEukMjzz+FPZbJzHHR2sTd24OEpwaJ9nUgpQKx7G5MXCd2fQQMWuw8LtTwAvVivCqJRsuA/878BdASywxzpnz30fT5mNCDcEXEjsKGcVlb0nh8R/s93jn/Gso4K8RPOdr5p96XTRJ34pB3jA9RyY2ysnwEOf11C/EQ9hU/yxbeg4ReeYzZMdHagbYH5+h862TXH3uyyiluH3rJlNT40ykzuN4RQv6n1ikjHYBwKeP2/LoMfO/AhL4v4E9uXB3PmTS0eiUZs03f1OkeUufyQkm5Ibo/oMxhuN3+ENrHR3Sv+IHvNML8wfpLr5l3uFdI37fQXa8FI7roARMPf0cIpkgeuFcTdFkeHQE5Ummpya5cf0aU6mrzFlDhUf310D/6eO2tSSA8yA7R4+ZP9Z9XDdM8U0pOYSC+voowUCA3ckgSVvRWMPYsLQm+V5TjGigbsFnb+EQSU3z+/E2mryVzw9zwK0jLHx8PTTOBTONKpEtKY72v7uZrv65umeXrYRDNpvNpSnbOnLSWY1SQXB2mqnRO5y7eJ7p5CBT9oc3POWcAF4Gfnr6uL3o/GKLPsXTx2353L8IDJKbFOQQCB7Yv57edV189gcp1mVr6y8zDTqNX9nBJ4VCybzon/8rJSQ9yesDiqcuSBpSK+fdNifAH2bX8aN6i0sRLxdQFGXWcnAKCl0RrJLjSqYXyTWL0veqnMMQPqxsNi8WCSY/+SxGbJZgDXq1sLJMnnyVCX2aCc4gIrM/JMO/OX3cvmdC+l40sYBEodUqFH7DoH6u9gc/tc5PpCmIuktrnumFD1ssHnw9RSi50glaBNuTOr8lgnw/LLkadnFdVeweVAlQ5QDPgzgPeEUarwRsKo4T5LJS6XSaQCBIsns9nqztXgzP46Gxj/h510UskgiNyaWAe0+An//TrPvcvwgUMwiu6xFNKBKNOg3Tyx+QbfsFFw4H7gpuzrzBtT1+pIBDJ1NE4yufhac3IfiNCZNTe8MMtAs8V+G5EteV868diZf/v+t4eO68RLiA2SWgVmsgQnngQWJuDtPM5ctvGwYP1Hj9DW4GT6UKZn7JWZ17d3SqwGBwHZedZzOsu17bVEGZiEasWWcpEoICru3yIbwgh09kCKVWAeQxD9NTyIf8DHca+WkimNd5ZX7EvVco2Fd4jsJ1vdxfp9AgCo1BLQC/OJOQdHIAJxI0NjUxPjHBR8E6VNhgV8qtAWADoUTh2a0ewKrkZI7rEZlTLGMqm7It1qgjteVFmlf3+PGE4PAbaaJzKwe5Y9Lj8bMWZ/abDHbpC+JzTRNoGiil54Dyg1JGWcVjrgGAdCWOK/HywBdAdx2J8jxwIJGYQ0rJnaHbDCmXtlpifQUBpdHp+LllZgDiq8fgUoAdl/BcbXNluAZcOBJY9vckcPUBEyEVD51cDSYLekclPsfGechkqF2v7hGrcj+70JyFEOiGQFOgDIGRbwg5C5AH3ssB7QzmZt/xPMn4+CiunmHKVLTaYrmXTEhqfD7ezp+23FxdgJUqBdjDn1U1sdf1CSa6jKVTt6JPvrzLpOu2w/ZLK55JDoDOKY8nztqc3u/jRqe+MMwBRMWtFruWRR5BruQWNE1DN8AzbdKpFFJ6xGZj6EGXeJ2oIiguUW93fZCb+zq5Jgx2HQefVZsHbfvFPZ2r8oZVzhwp4OyRIMKDbVdWA2RB75jE966DewRutenlcU8pjqpa+1NV5erS2fyEaZPJZLBtB9u20MMeb27S6Ewr1seW/xwbPANyhMuuHsBlTpaH7tUGsOMXKG15pkmVhCUCmGnWOPN4EMcU7DpvrSKT4Y19MFhgsqqEMm+yVWm1cuk1Vhc+NJ9NNpPBtnLXKjSPiTC0pGt4hkIRVgb5sNVeTQYnChZLSomQtTJYq8k8lwItgESd4IMDfnyWYtvVVWLyuOSZD+AFQ3GzZd48KxYxz0u8bmHaZOIZbCffGIVECbC1GlR8BYGch7q6DFaKTN7mR6OWxkSHj+7b9rIfoutjySa60jyrCjlwplnj1OMBHBN2XVidPrltIsunLzTy0vYs1zvEvMChqj+Uxc1zyex1PhvPc0nMzRVZqABPq+0afUqgFPG8ALVqDLbyrSb67GQ93RMWyxZVlcIxazfP1TzcZFTj/f1+fLZi21VnVZjcejvGs3YYW3O43aqVsafAaEWJjq0Wv/YCwAAz09NlTdWtBWAFeo4hc2sFcLfmQU2KuVDLin8rH5Yo7w2LNzzTrHHqaADHJ9h1cXWY3DyW5Glh8voDkoEOrcyK6JpBe+NmhievlFsbVR0QYTggFDOFge95BkvB8rWE/My0q85gpYoAI5WqmR1LahhqEfOsqmRr8hrwXFTjxtFeGrQk3efHVoXJPaMOT9kC96DBzdac8hYKNLCt5yES6ZlFnaoF7wuJFsgwWwBYW5WJz+dW28kqAuyqGi9QCbRlOGdqkfhELYhLIWjWodo2cLnewvUbrH93eFWY3DUt+cQ5jzd2aaR2bWVDxz40YfDRrVPluvQi5rnglBkt4zjjOlo0ixZIFYi8fC1hPrERf+nrWbV6AJcw+EeRcY6aXWwYV8s20Yaz7Pu5q3nOCQsarXUbEUKQrg8w+HAvhuWuGpPXT0iiup/BrT3ENZNYcpyMlViSeS68rwVTmL3Xi29oysCnXBByeRiLXHZ/OTr0UhlsF046o9l0Tddmpg1H5RwVcW/zzBLMs1LQHOnGpwfyiUyYawvz0TNbcU2D9WdXh8lNo3G0F9/n5lOKi8bAXVirFvEfBJquoWs+BDqGBEMlayq0dIQs9MGrB/BLX896T//hfMpQ1liVYC7RqsyT4u7mGSDoq1+QmUo35JisWy49F1aHyQ2D42yw3ub9HUkmm8U9FSzyDpmm+dDyq0UUfAahFD63FqEDspoEVHy1GVx2UlfUMOhGgGkrhFrad0uFDUVFYU3Jy9HYZepDndQF2xAl4xrjbWEuPbMV19TZ8N6dVWFyw8gMTzkaYqfBQJvIOZyqVIfW0DUdTRgIoZVZpVJT3pz0mIpohGeWmbRRgrTw1sREl500aygiy82GKDAtiZBQdXzpXcwzqjygKK2g8KTLdOI2icw0LdH1BIxo8buZBj83HurFsDx6Lq4OkzdMKj7luWT3Gww3iRyoeUBVfqENpeaFkEpWSyV5aNBh/UxtGblZzSlL/qwawKUnTWqSFvRle4GGo2gddRnt9S3LPCuomukp/cByUozMXKYu2EpjuAchjCKTLzyzhThJdl1MrgqTu2ckT19RvHAgyGwknypElZfzlDhhZcoW0JhWNabTFTHdUWvO4BnTZQPLr102HNj9jnVXgKuZZ+6SoitPAnjEUmMkM7M0R9cT9jegEMyGJXd2SrSMzs4b3qoweesdh6wvy8ldQaYjoqx1VoJd6nFrEhrTcvngKkhrHj8OTyRRZFcf4BIGTxoOVYahLmmrn3UXetL3MM8LxA0EmtByZlEpXM8p65sdz2Js9iohfyNNdeuZnhsi2SB47bAf16/Y81FmVUDec9PC0+CNnX5mwlrF/RSuVZU5iM0pxWREo3m5RQsCMprkjpGJLUfFqonBP2uaZX99D+3Xlj9XVCghqZ/xiDVXr6LQNT9+PYDAwND9GFp+z7/2aX50zUSgowmNrJPk8p0TeSdHkBsem3vYtptlMnYD13PwGyGsJp2hx7po1EbpuTi6KiDvv2EhpOLEzgBTEa189TZV2uXkLNLRGzY7xmsrN45rDq5ScbUWACtFKh8Pm2O6RWiiNhb4s4rd72R549PhRZxpQU/jXqLBjnkhYQGbc4vZISDob6A+1EHanps3izJ3sASk8orTLCiluKINM7JP8pAXZM/l1WHyvps2noCTO/xMhwUL3P2CKCMVHSuoDp3QbaRYXqpwOQwuqFkttueSqPcRnalN3G8bcdGlwtNFycQoBfOa5frEKdrrd9JRtx0htOKycDmzLJBqfgVCpaAp0kNm+mK5vKkqOvRCP60U8Yjg7J4gpqPYPpBdFZAPDtpoEl7fbjId0RZ0sUopumKSWoeR2kJyPDqGQq2RiVZYIg+w53lMN/roGqztcUTiks5bLsObfFVMmcKTHqOzF0lnp9nQegS/EWGh7jH/n5a6jYxMX86Nci85TwHQyr5cAFP1Oq8fDpE1YN+V1QF5/608k7ebTIdESZiUe/HYgE3vbG0MzgrJRSOBp5aXKly6iQarUD5rex7v7w7T7Bh0nZ+qwUxL9p9Jc2djfXnFhAJN6BiagYZBxp7j2uhJtnU8iWmEFmBcsN6GbhINthBPjZV9oKrpwyV/42GNsw8EMW3FzkFrVUA+dNNGU4rXtppMhecVr6ZUXuCpcWKWMd3CERIBcbWMTNKSARZ5Ey0A6XnM+iXhmdpbfuOkR8dth5FeI+cVazqG8OUcpBLzGjQb8RnBMpAqS2lQORbHUmNFTVqp6iU35XG2YqpR47UHQ1g+wf6rq8PkA7ccPODEVpOpkACpeHzAYc+oW/Pgs+u+VKGxzL32zeVVPS7ZRKtc68F1PSzHYWZDA/V3ahMPAhnFoVMZrEgdcy3BPLC52V1FUaTX6GnaPa8OFbvXSvVZ0RDqwNCD2G56Afgy/0JSOuBs/qi5iODdB/z4HcnOwdWp8XrwloPPU7zb60P3VM3KFcCsKTkeHi8anmXrD0s10YVQSUqJZdvcfHwL/qRF17napifovOWw960spz8bpuB+iJI+s61+63z/W13bLJo9IQQN4U7GYwPzbC1lrZrvf+drreZbzVSDxiuHgqT8gl2DNkFLrRjkfcMeTSlF1oCORO3nuxpyuKVnigxeE4Bf+6ZlP/n7/uLJbcvGagwhzZWNxu++kaXjZoaR9YEy71cTGq11m8pDpVJlS8HtqQ8J+5toDHchhE57/SYmYgPFJIAqqZ4qm2uqREwsgq9gLqTx4oNBRpt1nvggS/0qjIVaN7uyc1h+nR+1xJHJ4jNYGwZXih22bYGmcfPJ9ZCYo+dybaY6lPA4cHKOVEQj1uwrAtoU7cHQ/WUidGlInMhMMRkfZEIOMBFooqd5D+FAIyF/A8nsbN6Lnh/iWaY4qJyHLUuSAUIVEzZc2GgiJHzyQ4dQanXmg651m2kMcdWXLn0MawdwaaLZyhdyJ9vCK17cqm3YYs+bCU5+dn618GiwvTycLZhwFFJJhqY+RKqcYU9kprly5wQN4W4igWZSmZmy8KhUBS0MRZFVCtsLE41J4NxGH2gGT13QCcd/MUvc2qbOu/vacG64VUm2pgy2CtNBCLjyeBtGIkvHcO2tff21LCMX0lzfFUKSmwdqsbKlifgAKWu2DCBPekzP3UIIo4qErcrYT2k/XAKwpulIzy2a9w96IWg08Oi7swRS1n0HeKo5zGCbiXe96KBJmB9lshYAJ0r74MIW6w6TrNdgBRUyZlZy6I05PCEY2BkgkZmgMbyuzDwLwPFsxmPXiw5ZZZ/qSbfI2krRsHRSTyqELk3o9LTu4NbY+XnnDHiz2yIQ3MD+07cIz2XvK3vfP9iF5bm5tR5zl5xcrky5PIBLMkoFEw0gDB/nH41gOhq9V2vXdyNxj8Mn43i6YmjbCF7TPvQ8IwsXMDL7EbabLkIgqVZpqYp9uVKlHnV5sUDhHSE0elq209awgZtj58uuyZUub3VkMI5uZO/JGwTvC5MFY21hRrrrcGbHSieEXbaKVbuT5dh40suVqGg+ZttDpOpXHkNG4x4PvpFAcxTp1imioY78LStS1iwTc4M54KoWuKkKiEsALck/FoeF5o9qre+lp+0BHDdLSUsomvVEdpbzm1qQYgt7Tw2sLZMVxEPw3r52lBA4bu45568lvqYA56s6JKC5jodtWwQDoXxRGVx6KILPkmz5cGWLSDVOuzx0co6PfO/D058GIVDK4/bUB5h6AN3w5cyvyhfTK5F39HJG2fM84qmJYoFbtRrrQh9cF2plc+fBeb1OLSyFlVIyHrtNcMcuhCfZd2qAYMpeK/JyqctkrLMOrUCkcgZn15LB2Xw/UOd6uSV1goEQQmhoCpINBgP7wjRMu7TcWZkpiyQku16+RerOS4w9e5ChcIKMlaCr6YFcDZQqFyzmMVRk7RRzyfHqhen5EAkFfjPK1u4jaJqByldUlfa/pd+3nQxT8RHkjg5ab0+z+fLEmuB7u17w5rYQwXxk4jh2fjWZ+8PggppV57pesR8WQiCEjlIeY71+xnvMFQMMEEp6hN69hmE53Nlu07i9Gz1fa1UAVLLQmfKkW6JizYNfqpIZup+t3Ufwm+GSInqBofuQSqJkyXoOSBQwmxwnlY3x4kaPbMrPrqHV7Y9nQoKXtmgkwj5C+apMx7Fyq9ncpz54fghLflGsnFXJlc9IlRPTLz0UxmdLtr2/Ouv9NZ6/yYNjPqyrioHH/SQ66krqJlSFl6xwpZvXnUvLZeaB1jSdDZ0HiIaay3psXRjs7D2aBzgXbysl88qYQiqFdF3arFla1G0Q1qpNk20Z8MJmjcutgjqh54sUwHbLuoI4y8wkLdeLtgpKiue5ZQzWhIaXf4DJOp0rB0KE4h49N1bHIWmZdGByFF/W5fbhXsa2tyHzRrXUQ84VdHjVfJd8OKTR3fIALfW95QpKvqk2ROYFlvLZ7BTCk7RevMnm128Rmoyv6goGH7VqvN8tkPkFNwqbY9tleZE3/saS94XBnuth2dl5z0BoeUOW2ya7TD46HCaScGmYdFftQbRdnSQ8nWLzawNcfXIzU1ua8TRRpqZ50q06ZkgAXU3b6GndUXVmutIwSwFK5uTO0HScdacv0XRjFH88he64qwruQJPg5U3gFcYulUzZ7CxkMGsJcLaYUfJkiYkW85X8JdvwlgCmrTj46hzR2OqBHJ5OEybFnp9dQuo6sa46rj+ygXhbBCUEUrrzmSNVcKwUjdFuetp25ScTg0oKl07pL6Si4dY460+eJzw2SyCeYi2WLbhdL/j5VsFYtHRpn3mAbdtakQ69XCerOAhNAdlswURrZa2ueLyAGw8E0R3JoVfnCKbkKj4aQWg2J6pEppIEYxkCySzp+iCTUclbW11iEQ1JboiJBPx6MD88VxZ1aaVkjoyeIjIRo+f0RYKTcQLxJEbGzrF1jbaxiOCFLYLBRlHWdEoBrmDw3JoCfOpvLfnob/vjVdWsEgYLymOMq3tDCE9x8ERilUGe35pv57Tp8EyGViA6YdCQ8LANgW2Aawg87RzB4C18RhAhJZrjoTkuuuVg2Lm/uu3UOonfsrbRKDy/VXClVVSUIakiWaSSOI5TzHTdDxO9qFypoeUHllGVyVf2h1BKcehEkmBastbbujF33gSX1UFNV/TK95ogei3AFTy/VXCpMICtYisw2PNcXNcpvZy5tQeYRQBe0AeXjMfJ68FX9oVQAg6eTBJOrj3I5SZlEV3wPm83GgWvbBRcbq0+txZCFAF23NwMPflGKu8PwEqVmOhsyXVVH4xWCDckoITi8p4glg+OnEhRF/P4b2m73JID90ZTxUjJkk0viYEd28qvcA7k1kPKrDnAqozBds5JEVqRwapCfpAVIoMSMLAjiOPTOHQqRduY82sPbNLMMfflTYLhurtPpKxpevFZFnTofEcSVxJ7rQEWyiNZcPJs28ZTHobQEEKfr1qsZggr4sxbm0zSIcGh0ym6hh1MS/1agjsZhtc2CN7qBinufY+6VsLgQqIhx45ENqG8PF7ecvqXpQBcGNVl2FmV8YdyQ/xdx8WxLYyAr0ofvAjSJf3ORLvB81+sY9vFLEdOp4kk5YpW7Pzl2hTvdQjOdQouti592gtNm58dwHYsPM8t5LwTyVkFYOYBdvPxnlopwILcmHwfYFhpPDNEVkDQcQsZpUhej9YLiySWqESqzGRXJtylBpd3B5ht0njwzSzrb9q/8tCOReBmQ66/nQ4tz5XLMVgrMjinpuUG/6VyAPvz4Gr5v+69QDbuAa5RABfwORklkSKBIOi5HtmCJy0EmtArdODyuSnK59lQZdWOYx0Grz4dYv97GutvOjTO/uo5YFMhmDPhxc2Ca821TVajlZho27GKi3hIj1RiRqo8gzXKZ5a7K8jGEsAtAmxlUFKRFII2x3XJZrPFMEkXOt4i44JEsUpClSfVS0thIxonHwtxfpfHwfcy9Ay71M/JXwlw3+mCC22Ci221z0IEoOu+4upyljUvU3qeSqdjRRMtqgSAi4K8GMB6/jOjFGgrraSSpITIDWHJZjP5X9LQNKOiu1XFBLuqrE0uUYHLWC5gplHj5afCtE65HHzPonPMoS7xy+eEjUYFUiiuN8KJ9YJYcOWRtaHPDySw7PmoSHqkPbcqViVFRrhLBVjLA1y6G4BhJZWnJAn0XEYpnU4VpUpDM4uGuBTQsioJKFlvqIqnXVgEWcB4i85PPxWkLu7nwIcWXeMejTGF3/7Fsnq4XpDxwasbBdeairPPrcpm6GbxiRTIA+C5JPIkqwS1UPdQui8J4Gog68mYsj1PTWmGwPMks7FY8UumHqyqE1UZolsyTltVLHqhysb4AszVCV57NIDmKXruuDz0gU1jTBKwwPDuD7MHWgQNacVYVHBio2CwIWdtZG3LVyza/5o+f17FckiliyMKcbJqOo9VJaBeHhsvj9k9ARYlYdGC3U4r6VoM+fKjSsbHJ3A8C5/uxzRC+PQAlptlYQpuHlVV6nJVMduFwdtSqbKvuhrc6DG40W2geYqOCY8HL9g0xxXBrMLvUNsMctUAbRU0pyDhh/EIvLFRYzwCnlDlA7tXcYsEGzGN3OQ2c4lZkqnignNuJqFGSoAsBbR0F9UEdmMRB0tUxMDFv5k59VEwKlwExujIJGMTd1jXuQldM6kPdZHITuN5TlmxuSo116XDSko87dLSmvkZ4koWrSo9RldM9sDPukBICR60zHjsu5SmLiUJZiV+G3yuRJe5eT9FaYvLj8WWWq7h2IYgYwpSAUE8JHhns4/pkIaXrxkpzN+mV1wTi0y7eA+5txh5CAS67iPkj1AfbUPTclMXD40MksnkRhR6LjcTU2q8GhYVOInVSDYwc0deq2vTrug+diXm0rzzzvu0PdeB3wzhNyKY4UjZamDlizoufDCqtDehfJm44toJqnxsb2k/Xuy62+HCjso6rIoB4cVzVTay+YevFPiVorP0uisbI6WNUS2iwpe/rBwjKfID30vzvwAzs+MMDF4tFtvZafV2bFRO15opuZdnVtmZq5k7cqpjm3op3CB2KaW4dOEa7e3NPHz4MTRhVJcpVXVxnSqTvglFddGzypoY1WbBqzRGosI0IUS+TLbygc8PcFNifvhx5YKUSpXG+avb188lZ3j7vTeIx2KF383EJ+RJzy2GQbIKLhVP+e4Aq4oTlXbkEvCUwh257P3DpoPGk7qP/Y7l8vLPTzNw7RYHDu1hXU8Ppukv0zIoWXpVqeKI3ZIQqjp7FyznWjxAlDtvJYwulOqoPEKq4hEUrEjuOFHC5or1hIvsnb/ewv9Fxejj+axa+Xui8piS2jGlFFK6WHaG6dlJhoYHuT10k0xm3nvOzKn/MnJZXshjULrLKntVkKsxuNRDK3Tgpa/F7IicGG/w/rhji/7Hmk6v50kGrg0xcG0ov/rX0qbwV0v5ZAUsUcv70fuvWFfMZVm62Wmev/mB+x3XVtkSWdJbZK8aIt0N4EovbUEQPXzJ+8i16evarv9rw+Ro6UW77q9nduj+gE46HVffuHHWPZ6ZU8kScJ387i4C+JIBJv+Fu9FQAWrsundj5o78n9ft1g/VtYgvGH5xVAgaP4Zp2aYm6TrqUjbJaxM3vNemhuR4BXilIFcD21uuFl1N+irt2As/rNsZ5Q68454ETmk6eqRJC5tBclPnqLvbaVXTR/xCv6Vq/51qbJV2RlmZOZXKj3mvJmRUglwJdE3JhlKQVRVwjRLnq2jKpYeYm5SZiljt4+3ebUbeRX4sBbiSzfdM/htL+HG3krX59/RKgCvMuv4xwEsG2KsSxVSC7Fb8XZIMvlShw6sIme4Grig598cALx1gtQQWeyyxkqMWJauUxdV0UK1COvuYwcsH+G4srimNZtR4UaU/uJgm+jGDlw6wW0WRWpVc1f8PZ3Z3BqKVMRAAAAAASUVORK5CYII=\'); }
a.docx span.icon { background-image: url(\'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAHgAAAB4CAYAAAA5ZDbSAAAGa0lEQVR42u2c229UVRSHJzExUR/8F8S2liq04P1GmZleoFLEtrSA0RcTfTG++GKiJsaACLSd65lecWjhBUN4wJhYuRSQ+Ef4xoNWkFro0M6cOTNzlr89IQYJigP7nL3n7LWSL7ytk72+vfZtUkIhSbErQ9ndGbq6e4zK+NcFVK9kL9FSiOOW2DH6vp5l3skuq0JT54mOXKosGi32bYv60bXOLlGUADGULtEkBAuypkoeTNPJIQvFCCA7k/bfgquSLxomeShDx4MqVzCQKPxDsGD2YuUPY5blIbGMBRgheAJS72TyQuVGoOV+MEmPD2bcCqAg05/IQ6h7d+YrueAuzRbNBV3uPQUHWfKg5bqAgk5V8Lx7DwImeShF+3di8CbQB8Hj6NR7EyDJg1bl8gCWLxN4K5mnDAT+HybPV64HQvCA5Tos+O5MB+GePDDmuiz435n5uXKlrgX3i8OHIezAHmxBWq1M/1THjyF94vBhCG9CcBrC7ofxC5UlFqy74OT9C05Xu7kOT9d92JtMQQhOQdSDYJ2r3KwrwTvwG6kpbE+sUhKPGQ9K6lwddTILDrhkkwT3ShRcN5J3ZDB4Q+hNrlICYmSiveTtGLgp9OKQFYcU2WgtebvYmwxhG+7BMQjxguQZTSWzYHmk5jX8gcIowUlvBWvZyb1ibzKENyB4FBK8JqbTY8g2DNwUeiB431yZRiDBaw6edgt6CBZLlyH0JAv06XeOL4IFB+Yg2aWHFHdwmUxBdPBH3xZoeL7iHz+WV5QK7hEDN4StqQINTNykQyi8n4ycKedYsB+C00XqSuToy7kSHUTh/SSh6grVM4bBG8LWjEPdyRV67+gKHThf8Z3hswo6eauY2QbRjYNWZ+wGffGDQ1+js/zGd8lbMGiT6E471WV6YHKZ9qPgKjjkp+QtWLqMIlOirlSeOtDF72QNkGycYNBtOdSJvTgaW6IPT+TpKxRcBQfOlr1/8erCgM0DXWwVqSOZo8joIu1BJ6uSvPd02dsXry7sS2ZSoo60TdHEzarkvvHr9DmuT/tQdN85Xc5zB3tA522Sw1iuw8PX6N3ZXFX0XtFdPuKZ5E4sV0YDyZ0WJKdWKBK/TpvRze0jV6nXWqT3j6/Qx6ds+kwIxz3Wc86UVqUL7sAgmRJFM0WKplcpgn05LETH/6T20WtV2ZtGrtCmYcHv8kHu9tgihRPLFLXyFHrksYclCy4Tcwt0cxSHrwiuUWGcssNCduLGLeFLniHyi29F0izYN6J41oxi6RZFj4jO9gHxvdCjkgVHxWAYbZC+B0fGy8ToAwtmwbVFGEkZfZAueDOSqmJhmbRGRU1YMAuuUfAEEitiIUdao6Im0gW3T5RIFQs5V2tU1ES64E3jJVLFwrKrNSpqIl8wZg2jD9IFv46kjD7IFzyJxIw2SBf8GpIy+iBfMJYFRh+kC34Vs0YVv+EqojMqasKCWXBt8cpUiVShu2AVNZEu+GUkVYXuglXUxAPBDqlCf8H+10S64JeQlNEHFsyCa4sXpx1i9EG+YMwaRh+kC34Bs4bRh0AJ1v0UHQjBzyOpKnQXrKIm8gUfLpEqtBesoCbSBT+HpKrQXbCKmrBgFlxbPHvYIUYfWDALri02fuMQow/yBWPWMPogXfAGzBpGHwIl+FecVGXCgu8SbVmHVCFbsMqxyIIFs2AWzIJvi1YkVYVswSrHIgvpgtdni8Tog3zBRxxi9IEFs+DaYh2SMvrAgllwbfEMkjL6IF/wDBIz2iBd8NOYNYw+yBeMWcPoAwtmwbVFC5Iy+iBd8NrZIjH6IF/wDBIz2sAdzB1cWzQjKaMPLJgFs2AWzILNEfwUkjL6IF/wUSRmtIEFs+DaoglJGX2QLrgRSRl9YMEsuMYleoaLqgtNnrxFZ4tu4zF8gFGOcCH/9+CpYqkByRn1tEzZjvy/TUoXLjdgeWDU05rM/yL/v3CI2/u5e/RgQzz/SciLaMra7pP4AKMO4SDkVbTG7TkuslqEg5CX0Txll7jQamietsshr6MtZu9smOVi+00j7r4bD9n9IT+iLVE4sQYfZfyjdXTVCvkZbaP5bMMMF95rRI1FrUMqou1gfnPzpF1kEd6wdqJgixqHVEdrbPVUwxGbpcjqWtSyNVY4GdIt1o/mp1syhStNU3a5MVtw1+Aw9sQx5r8QNWrA3VbUrMUqXF0Xzx+W7eUvRVSv/1T+wNwAAAAASUVORK5CYII=\'); }
a.html span.icon { background-image: url(\'data:image/png;base64,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\'); }
a.java span.icon { background-image: url(\'data:image/png;base64,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\'); }
a.jpg span.icon { background-image: url(\'data:image/png;base64,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\'); }
a.lnk span.icon { background-image: url(\'data:image/png;base64,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\'); }
a.mp3 span.icon { background-image: url(\'data:image/png;base64,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\'); }
a.mp4 span.icon { background-image: url(\'data:image/png;base64,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\'); }
a.nc span.icon { background-image: url(\'data:image/png;base64,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\'); }
a.pdf span.icon { background-image: url(\'data:image/png;base64,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\'); }
a.php span.icon { background-image: url(\'data:image/png;base64,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\'); }
a.ppt span.icon { background-image: url(\'data:image/png;base64,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\'); }
a.svg span.icon { background-image: url(\'data:image/png;base64,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\'); }
a.xls span.icon { background-image: url(\'data:image/png;base64,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\'); }
a.zip span.icon { background-image: url(\'data:image/png;base64,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\'); }
');
}
/* PHP FILE ON TRASH */
elseif(isset($_GET['trashed'])) {
header('Content-Type: text/html; charset=utf-8');
exit('<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>PHP Files Manager</title>
<style>
* {
margin: 0px;
padding: 0px;
box-sizing: border-box;
}
html, body {
height: 100%;
}
body {
font-family: \'Gill Sans\', \'Gill Sans MT\', Calibri, \'Trebuchet MS\', sans-serif;
color: #cef;
background: linear-gradient(217deg, rgba(0, 64, 127, 0.8), rgba(0 ,0, 0, 0.3) 70.71%),
linear-gradient(127deg, rgba(0, 127, 64, 0.8), rgba(0, 0, 0, 0.3) 70.71%),
linear-gradient(336deg, rgba(0 ,127, 127, 0.8), rgba(0, 0, 0, 0.3) 70.71%);
}
h1 {
display: block;
position: absolute;
top: 50%;
left: 50%;
transform: translate(-50%, -50%);
text-align: center;
}
h1 span {
font-weight: normal;
}
</style>
<link rel="icon" type="image/png" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAACkVBMVEUAiMwzoNb///8pgKsAbaP+//8AfLoAbaQAfbwAh8ve7PMAbqYMc6cffq4pga0pgq4zn9RgoMAsi7lEkLYxms4yndIyndN6sMvj7vTk7/T8/f4AbqUAb6cAcKgAfr4Af74Ahskdfa4Kjc4qhbItjr4vlcdDkLYyntMzn9VgpMV5r8qex9yiyt6uz9+z0+TA2+jS5O3m8fYAcqsIcaULc6cMdKgRdqkAfr0Af78AhMYafKwgf68qgq8sgqwrhrMrh7QsibctjLsukMExm88ynNFTmbxlp8eWw9mZxNqjyt6mytzK4Ozc6/Lf7fPl7/Xt9Pj2+vz3+vwBbaMCb6YAcaoGcKYAc60HcaYAdK4JcqYAdbAAd7IOdKYPdagSd6oAfbsTd6oCfboJfbcAgMEAgcIAgsIZe6wAhcgAh8oBiMwCicwhf68lf60pgKwpgawKi8sqgawJjM4KjMwsgq0qhLEug60Mjs4Ojs8vhK4Oj88thbIrhrQrh7UQkM8uh7Muh7QviLQ0h7Asirk2iLA2iLE4ibEtjbw6irIskMM8i7MeltI+jLMuksMhl9Mvk8VBjrUvlMYwlcg/kbpDkLUwlsknmdJEkLUwl8kmmtQwl8owmMsom9QxmMwqm9RJk7gvntUxn9ZOlrpWm71dn79bocRgocFdosVhocFko8Jlo8JppsRrp8Vpqclyq8hvrMtwrct3rsp0r811sM17sct5ss99tNCBtM6Ctc6Dtc6LutGKvNWOvNKNvdaQvdSWwdaXwdafyNyfyN2pzd641eO72ObG3urI3unJ3+rM4evP4+zW5+/X5+/Y6fHe7PLf7PLf7fTg7fTh7fPi7vPj7/Tn8fbs9Pfu9fny9/ry+Pr7/f39/v8yfrSSAAACM0lEQVR42q3RZVcbQRQG4OHeCRtiJESACCEJadOUhgSH4lK0SN3d3b2l7u7u7u7u7v5rOrtEtqHntB/6fpk98z5nzsxdEvGXkH8G6p1OXYRhh9O5Wef6EygrRMSicchHVaxrAVrHYPnVJYhn7ty7dmIVFqx3/Q5Yf/yt8akWb9UDGN/cWKZa7RIDXczY2G8A8Eqbf/47WyUvDqrWioB+DmoagI93xuS7wsfzRaqyIGDnH6iH5jzSLnBXNwL8vIQlAaAuRvcXCOSlFvEkW59NLQyAbdGnGiAU7wqc8BXgw7q8ANigum6USCRCy9bPexHrAOI3RfvBvtLZiw9pNMe8rK/WaI6U5yPGAdRtbQb7iX377iKHIw/bMRCLUxwFSxfyIL6VAPZMJ2Tk3C16fYkA3FiqN6iTgmDXzFFEyMSVy/0nGNhfS8KPPBizZhoJZWCfhyLwCSBuVmciTiS9z0CHtPF2+6RBae8AOraXh4G2DLSh1l4Ws4nKeEDN5tSEEeFA3tWmVI72Afg2KpXKTLk1HNBztTLZe35kPplM5uknDYFK/g6UXhBNHZ70DYJsepQNu4pSRXKoT2niEgIgkbPVsnNvzs8IiRQFFxkVfEZvevoHv1szTHHW36ebRD3JkXd/YOT3q3rS2/wlazI5a5R4EtmWARdfs3s0Xk7PSAaJxyY+X8iQVJp7uNLzuGIeVVypyDWF94QM799NTrlOlOvRhVJLQouef8tgKUtWYk6WdCj5b/kF7oZ0CZFadjcAAAAASUVORK5CYII=">
</head>
<body>
<h1><span>&#9940;</span> &nbsp; Access to the trash is forbidden !</h1>
</body>
</html>
');
}
/* GET UPLOAD SIZES */
elseif(isset($_GET['get_upload_sizes'])) {
header('Content-Type: text/plain; charset=utf-8');
exit('[max_upload_sizes=' . parse_size(ini_get('upload_max_filesize')) . '|' . parse_size(ini_get('post_max_size')) . ']');
}
/* GET SETTINGS */
elseif(isset($_GET['get_settings'])) {
header('Content-Type: text/plain; charset=utf-8');
if($server_infos['server_on_windows'] === true)
echo'[server_on_windows]';
if(isset($_SESSION['view']))
echo'[view=' . $_SESSION['view'] . ']';
if(isset($_SESSION['trash']))
echo'[trash=' . $_SESSION['trash'] . ']';
else {
if(is_dir('Trash') && !is_link('Trash')) {
$_SESSION['trash'] = '1';
echo'[trash=1]';
}
}
if(isset($_SESSION['upload_exists']))
echo'[upload_exists=' . $_SESSION['upload_exists'] . ']';
if(isset($_SESSION['copy_move_exists']))
echo'[copy_move_exists=' . $_SESSION['copy_move_exists'] . ']';
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
if(is_file($file)) {
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
$trash_active = false;
if(isset($_SESSION['trash']) && $_SESSION['trash'] !== '0') {
$trash_active = true;
if(file_or_link_exists('Trash')) {
if(is_file('Trash') || is_link('Trash')) {
@rename_exists('Trash');
@mkdir('Trash');
}
}
else
@mkdir('Trash');
@create_htrashccess();
}
if(isset($_POST['token'])) {
function rm_full_dir($directory) {
$directory = no_end_slash($directory);
if(empty($directory) || $directory === '.') {
$directory = '.';
$path = '';
}
else
$path = $directory . '/';
if(is_dir($directory) && !is_link($directory)) {
if($handle = opendir($directory)) {
while(false !== ($entry = readdir($handle))) {
if($entry != '.' && $entry != '..') {
if(is_file($path . $entry) || is_link($path . $entry)) {
if(!unlink($path . $entry))
return false;
}
elseif(is_dir($path . $entry)) {
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
function copy_or_move($source, $dest, $move = false, $dest_file_exists = 1, $dest_dir_exists = 1, $fusion_dirs = 1) {
$dest = no_end_slash($dest);
if(empty($dest) || $dest === '.')
$dest = '';
else
$dest .= '/';
if(is_link($source)) {
$source = no_end_slash($source);
$source_infos = split_filename($source);
$source_path = $source_infos['path'];
$source_name = $source_infos['name'];
$extension = $source_infos['dot_extension'];
$dest_name = $source_name;
if($source_path === $dest && ($move === true || $dest_file_exists !== 2))
return false;
elseif(file_or_link_exists($dest . $source_name . $extension)) {
if(is_file($dest . $source_name . $extension) || is_link($dest . $source_name . $extension))
$is_file = true;
elseif(is_dir($dest . $source_name . $extension))
$is_file = false;
else
return false;
if(($is_file === false && $dest_dir_exists === 0) || ($is_file === true && $dest_file_exists === 0))
return false;
elseif($is_file === true && $dest_file_exists === 3) {
if(!unlink($dest . $source_name . $extension))
return false;
}
elseif($is_file === false && $dest_dir_exists === 3) {
if(!rm_full_dir($dest . $source_name . $extension))
return false;
}
elseif(($is_file === false && $dest_dir_exists === 1) || ($is_file === true && $dest_file_exists === 1)) {
$i = 1;
while(file_or_link_exists($dest . $source_name . $extension . '.bak' . $i))
$i++;
if(!rename($dest . $source_name . $extension, $dest . $source_name . $extension . '.bak' . $i))
return false;
}
else {
$i = 1;
while(file_or_link_exists($dest . $source_name . " ($i)" . $extension))
$i++;
$dest_name .= " ($i)";
}
}
if($move === true && rename($source, $dest . $dest_name . $extension))
return true;
elseif($move === false && symlink(readlink($source), $dest . $dest_name . $extension))
return true;
else
return false;
}
elseif(is_file($source)) {
$source = no_end_slash($source);
$source_infos = split_filename($source);
$source_path = $source_infos['path'];
$source_name = $source_infos['name'];
$extension = $source_infos['dot_extension'];
$dest_name = $source_name;
if($source_path === $dest && ($move === true || $dest_file_exists !== 2))
return false;
elseif(file_or_link_exists($dest . $source_name . $extension)) {
if(is_file($dest . $source_name . $extension) || is_link($dest . $source_name . $extension))
$is_file = true;
elseif(is_dir($dest . $source_name . $extension))
$is_file = false;
else
return false;
if(($is_file === false && $dest_dir_exists === 0) || ($is_file === true && $dest_file_exists === 0))
return false;
elseif($is_file === true && $dest_file_exists === 3) {
if(!unlink($dest . $source_name . $extension))
return false;
}
elseif($is_file === false && $dest_dir_exists === 3) {
if(!rm_full_dir($dest . $source_name . $extension))
return false;
}
elseif(($is_file === false && $dest_dir_exists === 1) || ($is_file === true && $dest_file_exists === 1)) {
$i = 1;
while(file_or_link_exists($dest . $source_name . $extension . '.bak' . $i))
$i++;
if(!rename($dest . $source_name . $extension, $dest . $source_name . $extension . '.bak' . $i))
return false;
}
else {
$i = 1;
while(file_or_link_exists($dest . $source_name . " ($i)" . $extension))
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
elseif(!empty($source) && $source !== '.' && is_dir($source)) {
$source = no_end_slash($source);
$source_infos = split_dirname($source);
$source_path = $source_infos['path'];
$source_name = $source_infos['name'];
$dest_name = $source_name;
$create_dir = true;
if($source_path === $dest && ($move === true || $fusion_dirs !== 2))
return false;
elseif(file_or_link_exists($dest . $source_name)) {
if($fusion_dirs === 0)
return false;
else {
if(is_file($dest . $source_name) || is_link($dest . $source_name))
$is_file = true;
elseif(is_dir($dest . $source_name))
$is_file = false;
else
return false;
if($fusion_dirs === 3) {
if($is_file === true && !unlink($dest . $source_name))
return false;
elseif($is_file === false && !rm_full_dir($dest . $source_name))
return false;
}
elseif($fusion_dirs === 1 && $is_file === false)
$create_dir = false;
else {
$i = 1;
while(file_or_link_exists($dest . $dest_name . " ($i)"))
$i++;
$dest_name .= " ($i)";
}
}
}
if($move === true && $create_dir === true) {
if(rename($source, $dest . $dest_name))
return true;
return false;
}
else {
if($handle = opendir($source_path . $source_name)) {
if($create_dir === false || ($create_dir === true && mkdir($dest . $dest_name))) {
while(false !== ($entry = readdir($handle))) {
if($entry != '.' && $entry != '..') {
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
function find_chmods($filename) {
if($fileperms = fileperms($filename))
return substr(sprintf('%o', $fileperms), -4);
else
return false;
}
function to_trash($source) {
return copy_or_move($source, 'Trash', true, 2, 2, 2);
}
if($_POST['token'] === $_SESSION['token']) {
if($current === '.')
$current = '';
function explode_multiple_files($files) {
if(strpos($files, '%2F%2F%2F'))
return explode('%2F%2F%2F', $files);
else
return explode('///', $files);
}
/* SET SETTINGS */
if(isset($_POST['set_settings'])) {
$return = 'Updated settings :';
if(isset($_POST['view'])) {
$_SESSION['view'] = $_POST['view'];
$return .= "\nview=" . $_POST['view'];
}
if(isset($_POST['trash'])) {
$_SESSION['trash'] = $_POST['trash'];
$return .= "\ntrash=" . $_POST['trash'];
}
if(isset($_POST['upload_exists'])) {
$_SESSION['upload_exists'] = $_POST['upload_exists'];
$return .= "\nupload_exists=" . $_POST['upload_exists'];
}
if(isset($_POST['copy_move_exists'])) {
$_SESSION['copy_move_exists'] = $_POST['copy_move_exists'];
$return .= "\ncopy_move_exists=" . $_POST['copy_move_exists'];
}
exit($return);
}
/* UPLOAD */
elseif(isset($_FILES['upload'])) {
$return = '';
$nb_files = count($_FILES['upload']['name']);
$ask_uploads = array();
for($i = 0; $i < $nb_files; $i++) {
$name = $_FILES['upload']['name'][$i];
$name_html = htmlentities($name, ENT_QUOTES);
if($_FILES['upload']['error'][$i] === 0) {
$dont_upload = false;
if(file_or_link_exists($current . $name)) {
if($_POST['exists'] === '1') {
$dont_upload = true;
$return .= "\n$name_html</b> already exists<b><br><br>";
}
elseif($_POST['exists'] === '2') {
$name = split_filename($name);
$extension = $name['dot_extension'];
$name = $name['name'];
$j = 1;
while(file_or_link_exists($current . $name . $extension . '.bak' . $j))
$j++;
if(@!rename($current . $name . $extension, $current . $name . $extension . '.bak' . $j)) {
$dont_upload = true;
$return .= "\n$name_html</b> cannot be renammed<b><br><br>";
}
$name = $name . $extension;
}
elseif($_POST['exists'] === '3') {
$name = split_filename($name);
$extension = $name['dot_extension'];
$name = $name['name'];
$j = 1;
while(file_or_link_exists($current . $name . " ($j)" . $extension))
$j++;
$name .= " ($j)" . $extension;
}
elseif($_POST['exists'] === '4') {
if(is_file($current . $name) || is_link($current . $name)) {
if(@!unlink($current . $name))
$return .= "\n$name_html</b> cannot be deleted<b><br><br>";
}
else {
if(@!rm_full_dir($current . $name))
$return .= "\n$name_html/</b> cannot be deleted<b><br><br>";
}
}
else {
array_push($ask_uploads, $current . $name);
$j = 1;
while(file_or_link_exists($current . $name . '.ask' . $j))
$j++;
$name .= '.ask' . $j;
array_push($ask_uploads, $current . $name);
}
}
if($dont_upload === false && @!move_uploaded_file($_FILES['upload']['tmp_name'][$i], $current . $name))
$return .= "\n$name_html</b> cannot be uploaded (#1)<b><br><br>";
}
else
$return .= "\n$name_html</b> cannot be uploaded (#2)<b><br><br>";
}
if(sizeof($ask_uploads) !== 0) {
if(empty($return))
$return = '[ask=' . implode('|', $ask_uploads) . ']';
else {
$nb_ask_uploads = sizeof($ask_uploads);
for($i = 0; $i < $nb_ask_uploads; $i++) {
if($i % 2 !== 0 && @!unlink($current . $ask_uploads[$i]))
$return .= "\n" . htmlentities($ask_uploads[$i], ENT_QUOTES) . '</b> cannot be deleted<b><br><br>';
elseif($i % 2 === 0)
$return .= "\n" . htmlentities($ask_uploads[$i], ENT_QUOTES) . '</b> cannot be uploaded, please try again<b><br><br>';
}
$return = substr($return, 0, mb_strlen($return) - 8);
}
}
elseif(empty($return))
$return = 'uploaded';
else
$return = substr($return, 0, mb_strlen($return) - 8);
exit($return);
}
/* ASK AFTER UPLOAD */
elseif(isset($_POST['ask']) && isset($_POST['files'])) {
$choice = $_POST['ask'];
$files_tmp = explode('|', $_POST['files']);
$nb_files_tmp = sizeof($files_tmp);
$files = array();
$j = 0;
for($i = 0; $i < $nb_files_tmp; $i++) {
if($i % 2 === 0)
$files[$j]['old'] = $files_tmp[$i];
else {
$files[$j]['ask'] = $files_tmp[$i];
$j++;
}
}
$return = '';
$nb_files = sizeof($files);
if($choice === '0') {
for($i = 0; $i < $nb_files; $i++) {
if(@!unlink($current . $files[$i]['ask']))
$return .= "\n" . htmlentities($files[$i]['ask'], ENT_QUOTES) . '</b> cannot be deleted<b><br><br>';
}
}
elseif($choice === '1') {
for($i = 0; $i < $nb_files; $i++) {
$name = $files[$i]['old'];
$name = split_filename($files[$i]['old']);
$extension = $name['dot_extension'];
$name = $name['name'];
$j = 1;
while(file_or_link_exists($current . $name . $extension . '.bak' . $j))
$j++;
$name .= $extension . '.bak' . $j;
if(@!rename($current . $files[$i]['old'], $current . $name))
$return .= "\n" . htmlentities($files[$i]['old'], ENT_QUOTES) . '</b> cannot be renammed<b><br><br>';
elseif(@!rename($current . $files[$i]['ask'], $current . $files[$i]['old']))
$return .= "\n" . htmlentities($files[$i]['ask'], ENT_QUOTES) . '</b> cannot be renammed<b><br><br>';
}
}
elseif($choice === '2') {
for($i = 0; $i < $nb_files; $i++) {
$name = split_filename($files[$i]['old']);
$extension = $name['dot_extension'];
$name = $name['name'];
$j = 1;
while(file_or_link_exists($current . $name . " ($j)" . $extension))
$j++;
$name .= " ($j)" . $extension;
if(@!rename($current . $files[$i]['ask'], $current . $name))
$return .= "\n" . htmlentities($files[$i]['ask'], ENT_QUOTES) . '</b> cannot be renammed<b><br><br>';
}
}
elseif($choice === '3') {
for($i = 0; $i < $nb_files; $i++) {
if(@!unlink($current . $files[$i]['old']))
$return .= "\n" . htmlentities($files[$i]['old'], ENT_QUOTES) . '</b> cannot be deleted<b><br><br>';
elseif(@!rename($current . $files[$i]['ask'], $current . $files[$i]['old']))
$return .= "\n" . htmlentities($files[$i]['ask'], ENT_QUOTES) . '</b> cannot be renammed<b><br><br>';
}
}
else
$return = 'Unknown choice<br><br>';
if(empty($return))
$return = 'uploaded';
else
$return = substr($return, 0, mb_strlen($return) - 8);
exit($return);
}
/* NEW FILE OR FOLDER */
elseif(isset($_POST['new']) && isset($_POST['name'])) {
if(strpos($_POST['name'], "'") === false) {
$new_name = $_POST['name'];
if(file_or_link_exists($current . $new_name))
exit('File or directory already exists');
else {
if($_POST['new'] === 'file') {
if(@file_put_contents($current . $new_name, '') !== false)
exit('created');
else
exit('File not created');
}
else {
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
elseif(isset($_POST['rename']) && isset($_POST['name'])) {
if(@rename($current . urldecode($_POST['rename']), $current . $_POST['name']))
exit('renamed');
else
exit('Not renamed');
}
/* DUPLICATE ELEMENT */
elseif(isset($_POST['duplicate'])) {
$name = urldecode($_POST['duplicate']);
if(file_or_link_exists($current . $name)) {
if(@copy_or_move($current . $name, $current, false, 2, 2, 2))
exit('duplicated');
else
exit('File or directory not duplicated');
}
else
exit('File or directory not found');
}
/* COPY ELEMENT */
elseif(isset($_POST['copy']) && isset($_POST['path']) && isset($_POST['if_exists'])) {
$name = urldecode($_POST['copy']);
$if_exists = intval($_POST['if_exists']);
if(file_or_link_exists($current . $name)) {
if(@copy_or_move($current . $name, $_POST['path'], false, $if_exists, $if_exists, 1))
exit('copied');
else
exit('File or directory not copied');
}
else
exit('File or directory not found');
}
/* MOVE ELEMENT */
elseif(isset($_POST['move']) && isset($_POST['path']) && isset($_POST['if_exists'])) {
$name = urldecode($_POST['move']);
$if_exists = intval($_POST['if_exists']);
if(file_or_link_exists($current . $name)) {
if(@copy_or_move($current . $name, $_POST['path'], true, $if_exists, $if_exists, 1))
exit('moved');
else
exit('File or directory not moved');
}
else
exit('File or directory not found');
}
/* DELETE ELEMENT */
elseif(isset($_POST['delete'])) {
$name = urldecode($_POST['delete']);
if(is_file($current . $name) || is_link($current . $name)) {
if(($trash_active === true && @to_trash($current . $name)) || ($trash_active === false && @unlink($current . $name)))
exit('deleted');
else
exit('File not deleted');
}
elseif(is_dir($current . $name)) {
if(($trash_active === true && @to_trash($current . $name)) || ($trash_active === false && @rm_full_dir($current . $name)))
exit('deleted');
else
exit('Directory not deleted');
}
else
exit('File not found');
}
/* EDIT ELEMENT */
elseif(isset($_POST['read_file'])) {
$name = urldecode($_POST['read_file']);
if(is_file($current . $name) && !is_link($current . $name))
exit(htmlentities(file_get_contents($current . $name), ENT_QUOTES));
else
exit('[file_edit_not_found]');
}
elseif(isset($_POST['edit_file']) && isset($_POST['name'])) {
$name = urldecode($_POST['name']);
if(is_file($current . $name) && !is_link($current . $name)) {
if(@file_put_contents($current . $name, urldecode($_POST['edit_file'])))
exit('edited');
else
exit('File not edited');
}
else
exit('File not found');
}
/* GET CHMODS */
elseif(isset($_POST['get_chmods'])) {
$name = urldecode($_POST['get_chmods']);
if(file_or_link_exists($current . $name)) {
$fileperms = @find_chmods($current . $name);
if($fileperms !== false)
exit("[chmods=$fileperms]");
else
exit('Chmods not found');
}
else
exit('File not found');
}
/* CHANGE CHMODS */
elseif(isset($_POST['set_chmods']) && isset($_POST['name'])) {
$name = urldecode($_POST['name']);
if(file_or_link_exists($current . $name)) {
if(@chmod($current . $name, octdec(intval($_POST['set_chmods']))))
exit('chmoded');
else
exit('Chmods not updated');
}
else
exit('File not found');
}
/* DUPLICATE MULTIPLE ELEMENTS */
elseif(isset($_POST['duplicate_multiple'])) {
$return = '';
foreach(explode_multiple_files($_POST['duplicate_multiple']) as $file_to_duplicate) {
$file_to_duplicate = urldecode($file_to_duplicate);
if(file_or_link_exists($file_to_duplicate)) {
if(@!copy_or_move($file_to_duplicate, $current, false, 2, 2, 2))
$return .= "<b>$file_to_duplicate</b> : File or directory not duplicated<br><br>";
}
else
$return .= "<b>$file_to_duplicate</b> : File or directory not found<br><br>";
}
if(empty($return))
exit('duplicateds');
else
exit(substr($return, 0, mb_strlen($return) - 8));
}
/* COPY MULTIPLE ELEMENTS */
elseif(isset($_POST['copy_multiple']) && isset($_POST['if_exists'])) {
$if_exists = intval($_POST['if_exists']);
$return = '';
foreach(explode_multiple_files($_POST['copy_multiple']) as $file_to_copy) {
$file_to_copy = urldecode($file_to_copy);
if(file_or_link_exists($file_to_copy)) {
if(@!copy_or_move($file_to_copy, $current, false, $if_exists, $if_exists, 1))
$return .= "<b>$file_to_copy</b> : File or directory not copied<br><br>";
}
else
$return .= "<b>$file_to_copy</b> : File or directory not found<br><br>";
}
if(empty($return))
exit('copieds');
else
exit(substr($return, 0, mb_strlen($return) - 8));
}
/* MOVE MULTIPLE ELEMENTS */
elseif(isset($_POST['move_multiple']) && isset($_POST['if_exists'])) {
$if_exists = intval($_POST['if_exists']);
$return = '';
foreach(explode_multiple_files($_POST['move_multiple']) as $file_to_move) {
$file_to_move = urldecode($file_to_move);
if(file_or_link_exists($file_to_move)) {
if(@!copy_or_move($file_to_move, $current, true, $if_exists, $if_exists, 1))
$return .= "<b>$file_to_move</b> : File or directory not moved<br><br>";
}
else
$return .= "<b>$file_to_move</b> : File or directory not found<br><br>";
}
if(empty($return))
exit('moveds');
else
exit(substr($return, 0, mb_strlen($return) - 8));
}
/* DELETE MULTIPLE ELEMENTS */
elseif(isset($_POST['delete_multiple'])) {
$return = '';
foreach(explode_multiple_files($_POST['delete_multiple']) as $file_to_delete) {
$file_to_delete = urldecode($file_to_delete);
if(is_file($file_to_delete) || is_link($file_to_delete)) {
if(($trash_active === true && @!to_trash($file_to_delete)) || ($trash_active === false && @!unlink($file_to_delete)))
$return .= "<b>$file_to_delete</b> : File not deleted<br><br>";
}
elseif(is_dir($file_to_delete)) {
if(($trash_active === true && @!to_trash($file_to_delete)) || ($trash_active === false && @!rm_full_dir($file_to_delete)))
$return .= "<b>$file_to_delete</b> : Directory not deleted<br><br>";
}
else
$return .= "<b>$file_to_delete</b> : File or directory not found<br><br>";
}
if(empty($return))
exit('deleteds');
else
exit($return);
}
/* SET MULTIPLE CHMODS */
elseif(isset($_POST['set_multiple_chmods']) && isset($_POST['files'])) {
$return = '';
foreach(explode_multiple_files($_POST['files']) as $file_to_chmod) {
$file_to_chmod = urldecode($file_to_chmod);
if(file_or_link_exists($file_to_chmod)) {
if(@!chmod($file_to_chmod, octdec(intval($_POST['set_multiple_chmods']))))
$return .= "<b>$file_to_chmod</b> : Chmods not updated<br><br>";
}
else
$return .= "<b>$file_to_chmod</b> : File or directory not found<br><br>";
}
if(empty($return))
exit('chmodeds');
else
exit(substr($return, 0, mb_strlen($return) - 8));
}
/* TRASH MULTIPLE ELEMENTS */
elseif(isset($_POST['trash'])) {
$return = '';
foreach(explode_multiple_files($_POST['trash']) as $file_to_delete) {
$file_to_delete = urldecode($file_to_delete);
if(@!to_trash($file_to_delete))
$return .= "<b>$file_to_delete</b> : File not deleted<br><br>";
}
if(empty($return))
exit('trasheds');
else
exit($return);
}
/* DELETE ELEMENT FROM TRASH */
elseif(isset($_POST['permanently_delete'])) {
$name = urldecode($_POST['permanently_delete']);
if(is_file($current . $name) || is_link($current . $name)) {
if(@unlink($current . $name))
exit('deleted');
else
exit('File not deleted');
}
elseif(is_dir($current . $name)) {
if(@rm_full_dir($current . $name))
exit('deleted');
else
exit('Directory not deleted');
}
else
exit('File not found');
}
/* DELETE MULTIPLE ELEMENTS FROM TRASH */
elseif(isset($_POST['permanently_delete_multiple'])) {
$return = '';
foreach(explode_multiple_files($_POST['permanently_delete_multiple']) as $file_to_delete) {
$file_to_delete = urldecode($file_to_delete);
if(is_file($file_to_delete) || is_link($file_to_delete)) {
if(@!unlink($file_to_delete))
$return .= "<b>$file_to_delete</b> : File not deleted<br><br>";
}
elseif(is_dir($file_to_delete)) {
if(@!rm_full_dir($file_to_delete))
$return .= "<b>$file_to_delete</b> : Directory not deleted<br><br>";
}
else
$return .= "<b>$file_to_delete</b> : File or directory not found<br><br>";
}
if(empty($return))
exit('deleteds');
else
exit($return);
}
/* EMPTY TRASH */
elseif(isset($_POST['empty_trash'])) {
if(@rm_full_dir('Trash')) {
if(@mkdir('Trash')) {
if(@create_htrashccess())
exit('emptied');
else
exit('Trash cannot be protected');
}
else
exit('Trash cannot be created');
}
else
exit('Trash cannot emptied');
}
/* UPDATE */
elseif(isset($_POST['update'])) {
$script_name = split_filename($server_infos['script']);
$script_name = $script_name['name'] . $script_name['dot_extension'];
$i = 1;
while(file_or_link_exists($script_name . '.update' . $i))
$i++;
$update_name = $script_name . '.update' . $i;
$i = 1;
while(file_or_link_exists("update_temp$i.php"))
$i++;
$temp_name = "update_temp$i.php";
if(@file_put_contents($update_name, @file_get_contents(urldecode($_POST['update'])))) {
if(@file_put_contents($temp_name, '<?' . 'php
unlink($_GET[\'file\']);
rename($_GET[\'update\'], $_GET[\'file\']);
unlink($_GET[\'tmp\']);
header(\'Location: \' . $_GET[\'file\']);
')) {
exit("[update=$script_name|$update_name|$temp_name]");
}
else
exit('Creation of temporary file failed');
}
else
exit('Download failed');
}
else
exit('Unknown action');
}
else
exit('Refresh site');
}
else {
/* RETURN DIR INFORMATIONS */
function css_extension($file) {
if(strpos($file, '.') !== false) {
$extension = explode('.', $file);
$extension = $extension[sizeof($extension) - 1];
if($extension === 'css' || $extension === 'json' || $extension === 'xml') return 'css';
elseif($extension === 'doc' || $extension === 'docx' || $extension === 'txt' || $extension === 'rtf' || $extension === 'odt' || $extension === 'ini') return 'docx';
elseif($extension === 'html' || $extension === 'xhtml' || $extension === 'htm') return 'html';
elseif($extension === 'js' || $extension === 'java' || $extension === 'py' || $extension === 'c' || $extension === 'bat' || $extension === 'bash' || $extension === 'sh') return 'java';
elseif($extension === 'jpg' || $extension === 'jpeg' || $extension === 'png' || $extension === 'gif' || $extension === 'webp' || $extension === 'bmp' || $extension === 'psd' || $extension === 'tiff') return 'jpg';
elseif($extension === 'lnk') return 'lnk';
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
function array_sort($array, $case, $order = 'ASC') {
$new_array = array();
$sortable_array = array();
if(count($array) > 0) {
foreach($array as $k => $v) {
if(is_array($v)) {
foreach($v as $k2 => $v2) {
if($k2 === $case)
$sortable_array[$k] = $v2;
}
}
else
$sortable_array[$k] = $v;
}
if($order === 'ASC')
asort($sortable_array);
else
arsort($sortable_array);
foreach($sortable_array as $k => $v)
$new_array[$k] = $array[$k];
}
return $new_array;
}
function path_parents($nb) {
if($nb === 0)
return '.';
else {
$return = '';
for($i = 0; $i < $nb; $i++)
$return .= '../';
return $return;
}
}
/* PATH */
if(!$server_infos)
exit('[fatal=Unable to get server information]');
$script_path = $server_infos['server_root'] . $server_infos['script'];
$no_root = true;
if(strpos($script_path, '/') === false) {
$no_root = false;
$server_dirs[0]['name'] = '/';
$server_dirs[0]['path'] = '.';
$nb_server_dirs = 1;
}
else {
if($script_path[0] === '/')
$no_root = false;
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
$adds_dirs = substr($adds_dirs, 0, mb_strlen($adds_dirs) - 1);
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
if($i === 0 && $no_root === false)
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
$func_js = 'onmouseup="endClicTree';
if($tree_only !== false)
$func_js = 'onclick="boxPathNavigate';
$return = '';
if($lvl === 1) {
$dir_default = $move_forbidden = '';
if($nb_dirs === 1) {
$dir_default = ' treeDefault';
$move_forbidden = ', true';
}
$path_enc = urlencode($path);
$return = "<a class=\"dirOpen treeFirst$dir_default\" style=\"margin-left: 1em;\" $func_js('$path_enc', '" . urlencode($name) . "'$move_forbidden)\" ondragover=\"dragOverAtreeDir(this, '$path_enc')\" ondragleave=\"dragLeaveAtreeDir(this)\" ondrop=\"dropOnAtreeDir(this)\"><span class=\"icon\"></span>" . htmlentities($name, ENT_QUOTES) . "</a><br>\n";
}
$next = false;
if($handle = opendir($path)) {
while(false !== ($entry = readdir($handle))) {
if($entry != '.' && $entry != '..' && is_dir($link . $entry . '/') && !is_link($link . $entry)) {
$entry_html = htmlentities($entry, ENT_QUOTES);
$entry_enc = urlencode($entry);
if(isset($dirs[$lvl]['name']) && $entry === $dirs[$lvl]['name']) {
$dir_default = $move_forbidden = '';
if($lvl === $nb_dirs - 1) {
$dir_default = ' treeDefault';
$move_forbidden = ', true';
}
if($trash_active = true && $cur_rmvs === 0 && $lvl === $nb_server_dirs && $entry === 'Trash')
$css_class = 'trash' . $dir_default;
else
$css_class = 'dirOpen' . $dir_default;
$path_enc = urlencode($dirs[$lvl]['path']);
$return .= "<a class=\"$css_class\" style=\"margin-left: " . ($lvl + 1) . "em;\" $func_js('$path_enc', '$entry_enc'$move_forbidden)\" ondragover=\"dragOverAtreeDir(this, '$path_enc')\" ondragleave=\"dragLeaveAtreeDir(this)\" ondrop=\"dropOnAtreeDir(this)\"><span class=\"icon\"></span>$entry_html</a><br>\n" . show_tree($lvl + 1);
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
if($trash_active = true && $cur_rmvs === 0 && $lvl === $nb_server_dirs && $entry === 'Trash')
$css_class = 'trash';
else
$css_class = 'dir';
$path_enc = urlencode($dir);
$return .= "<a class=\"$css_class\" style=\"margin-left: " . ($lvl + 1) . "em;\" $func_js('$path_enc', '$entry_enc')\" ondragover=\"dragOverAtreeDir(this, '$path_enc')\" ondragleave=\"dragLeaveAtreeDir(this)\" ondrop=\"dropOnAtreeDir(this)\"><span class=\"icon\"></span>$entry_html</a><br>\n";
}
}
}
closedir($handle);
}
else {
$dir_default = $move_forbidden = '';
if($lvl === $nb_dirs - 1) {
$dir_open = 'Open treeDefault';
$move_forbidden = ', true';
}
$path_enc = urlencode($server_dirs[$lvl]['path']);
$return .= "<a class=\"dir$dir_open\" style=\"margin-left: " . ($lvl + 1) . "em;\" $func_js('$path_enc', '" . urlencode($server_dirs[$lvl]['name']) . "'$move_forbidden)\" ondragover=\"dragOverAtreeDir(this, '$path_enc')\" ondragleave=\"dragLeaveAtreeDir(this)\" ondrop=\"dropOnAtreeDir(this)\"><span class=\"icon\"></span>" . htmlentities($server_dirs[$lvl]['name'], ENT_QUOTES) . "</a><br>\n";
if(isset($dirs[$lvl]))
$return .= show_tree($lvl + 1);
$next = true;
}
if($next === false && isset($dirs[$lvl]['name'])) {
$dir_default = $move_forbidden = '';
if($lvl === $nb_dirs - 1) {
$dir_default = ' treeDefault';
$move_forbidden = ', true';
}
$path_enc = urlencode($dirs[$lvl]['path']);
$return .= "<a class=\"dirOpen$dir_default\" style=\"margin-left: " . ($lvl + 1) . "em;\" $func_js('" . $path_enc . "', '" . urlencode($dirs[$lvl]['name']) . "'$move_forbidden)\" ondragover=\"dragOverAtreeDir(this, '$path_enc')\" ondragleave=\"dragLeaveAtreeDir(this)\" ondrop=\"dropOnAtreeDir(this)\"><span class=\"icon\"></span>" . htmlentities($dirs[$lvl]['name'], ENT_QUOTES) . "</a><br>\n" . show_tree($lvl + 1);
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
$web_root_accessible = false;
$web_root_url = $server_infos['web_http'] . $server_infos['web_root'] . '/';
if($cur_rmvs > $nb_script_dirs) {
$web_accessible = false;
if($cur_rmvs === $nb_script_dirs + 1)
$web_root_accessible = $server_dirs[$nb_server_dirs - $nb_script_dirs - 1]['name'];
}
else {
$web_accessible = $web_root_url;
$web_dirs = array();
for($i = 0; $i < $nb_script_dirs - $cur_rmvs; $i++)
$web_dirs[] = $script_dirs[$i];
foreach($adds_dirs as $add_dir)
$web_dirs[] = $add_dir;
foreach($web_dirs as $web_dir)
$web_accessible .= $web_dir . '/';
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
if($trash_active === true && $current === '.' && $entry === 'Trash')
$elems_dirs[$nb_el_dirs]['name_sort'] = '';
else
$elems_dirs[$nb_el_dirs]['name_sort'] = strtolower($entry);
$elems_dirs[$nb_el_dirs]['link'] = is_link($link . $entry);
$nb_el_dirs++;
}
else {
$elems_files[$nb_el_files]['name'] = $entry;
$elems_files[$nb_el_files]['name_sort'] = strtolower($entry);
$elems_files[$nb_el_files]['time'] = filemtime($link . $entry);
$elems_files[$nb_el_files]['size'] = filesize($link . $entry);
$elems_files[$nb_el_files]['type'] = split_filename($entry)['extension'];
$elems_files[$nb_el_files]['type_sort'] = strtolower($elems_files[$nb_el_files]['type']);
$elems_files[$nb_el_files]['link'] = is_link($link . $entry);
$nb_el_files++;
}
}
}
closedir($handle);
}
$elements = '';
if($desc_dirs === '1')
$elems_dirs = array_sort($elems_dirs, 'name_sort', 'DESC');
else
$elems_dirs = array_sort($elems_dirs, 'name_sort');
foreach($elems_dirs as $elem_dir) {
$el_enc = urlencode($elem_dir['name']);
$el_html = htmlentities($elem_dir['name'], ENT_QUOTES);
if($cur_rmvs > 0 && $cur_adds === 0 && $elem_dir['name'] === $server_dirs[$nb_dirs]['name'])
$full_path_enc = urlencode(path_parents($cur_rmvs - 1));
else
$full_path_enc = urlencode($link . $elem_dir['name'] . '/');
$web_url = 'false';
if($web_accessible !== false)
$web_url = '\'' . htmlentities($web_accessible . $el_html, ENT_QUOTES) . '\'';
elseif($elem_dir['name'] === $web_root_accessible)
$web_url = '\'' . htmlentities($web_root_url, ENT_QUOTES) . '\'';
$link_icon = 'dir';
$link_js = 'false';
if($elem_dir['link']) {
$link_icon = 'linkdir';
$link_js = '\'' . htmlentities(readlink($link . $elem_dir['name']) . '/', ENT_QUOTES) . '\'';
}
if($trash_active === true && $current === '.' && $el_enc === 'Trash')
$elements .= "<a class=\"trash\" data-name-enc=\"$el_enc\" onmousedown=\"startClic(this, '$el_enc')\" onmouseup=\"endClic(this, '$full_path_enc', '$el_enc', false, $link_js, false)\" oncontextmenu=\"rightClic('$cur_enc', '$el_enc', '$full_path_enc', $web_url, $link_js)\"><span class=\"icon\"></span><span class=\"txt\">Trash</span></a>\n";
else
$elements .= "<a class=\"$link_icon\" data-name-enc=\"$el_enc\" onmousedown=\"startClic(this, '$el_enc')\" onmouseup=\"endClic(this, '$full_path_enc', '$el_enc', false, $link_js, false)\" oncontextmenu=\"rightClic('$cur_enc', '$el_enc', '$full_path_enc', $web_url, $link_js)\" ondragover=\"dragOverAdir(this, '$full_path_enc')\" ondragleave=\"dragLeaveAdir(this)\" ondrop=\"dropOnAdir(this)\"><span class=\"icon\"></span><span class=\"txt\">$el_html</span></a>\n";
}
if($order === '1')
$arr_order = 'time';
elseif($order === '2')
$arr_order = 'size';
elseif($order === '3')
$arr_order = 'type_sort';
else
$arr_order = 'name_sort';
if($desc === '1')
$arr_desc = 'DESC';
else
$arr_desc = 'ASC';
$elems_files = array_sort($elems_files, $arr_order, $arr_desc);
if(isset($elems_files)) {
foreach($elems_files as $elem_file) {
$el_enc = urlencode($elem_file['name']);
$el_html = htmlentities($elem_file['name'], ENT_QUOTES);
$web_url = 'false';
if($web_accessible !== false)
$web_url = '\'' . htmlentities($web_accessible . $el_html, ENT_QUOTES) . '\'';
if($elem_file['link']) {
$link_icon = 'linkfile';
$link_js = '\'' . htmlentities(readlink($link . $elem_file['name']) . '/', ENT_QUOTES) . '\'';
}
else {
$link_icon = css_extension($elem_file['name']);
$link_js = 'false';
}
$elements .= "<a class=\"$link_icon\" data-name-enc=\"$el_enc\" onmousedown=\"startClic(this, '$el_enc')\" onmouseup=\"endClic(this, '$cur_enc', '$el_enc', $web_url, $link_js, true)\" oncontextmenu=\"rightClic('$cur_enc', '$el_enc', false, $web_url, $link_js)\"><span class=\"icon\"></span><span class=\"txt\">$el_html</span><span class=\"size\">" . size_of_file($elem_file['size']) . '</span><span class="date">' . date('d/m/Y H:i:s', $elem_file['time']) . "</span></a>\n";
}
}
/* RETURN */
if($web_accessible === false)
$web_accessible = 'false';
exit('//!token!\\\\' . $_SESSION['token'] . "\n//!current!\\\\$cur_enc\n//!parent!\\\\" . urlencode($parent) . "\n//!path!\\\\$path\n//!tree!\\\\$tree\n//!elements!\\\\$elements\n//!web!\\\\$web_accessible\n//!order!\\\\$order\n//!desc!\\\\$desc\n//!end!\\\\");
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
exit('<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>PHP Files Manager</title>
<link rel="stylesheet" href="?css&style">
<link rel="stylesheet" href="?css&images">
<link rel="icon" type="image/png" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAACkVBMVEUAiMwzoNb///8pgKsAbaP+//8AfLoAbaQAfbwAh8ve7PMAbqYMc6cffq4pga0pgq4zn9RgoMAsi7lEkLYxms4yndIyndN6sMvj7vTk7/T8/f4AbqUAb6cAcKgAfr4Af74Ahskdfa4Kjc4qhbItjr4vlcdDkLYyntMzn9VgpMV5r8qex9yiyt6uz9+z0+TA2+jS5O3m8fYAcqsIcaULc6cMdKgRdqkAfr0Af78AhMYafKwgf68qgq8sgqwrhrMrh7QsibctjLsukMExm88ynNFTmbxlp8eWw9mZxNqjyt6mytzK4Ozc6/Lf7fPl7/Xt9Pj2+vz3+vwBbaMCb6YAcaoGcKYAc60HcaYAdK4JcqYAdbAAd7IOdKYPdagSd6oAfbsTd6oCfboJfbcAgMEAgcIAgsIZe6wAhcgAh8oBiMwCicwhf68lf60pgKwpgawKi8sqgawJjM4KjMwsgq0qhLEug60Mjs4Ojs8vhK4Oj88thbIrhrQrh7UQkM8uh7Muh7QviLQ0h7Asirk2iLA2iLE4ibEtjbw6irIskMM8i7MeltI+jLMuksMhl9Mvk8VBjrUvlMYwlcg/kbpDkLUwlsknmdJEkLUwl8kmmtQwl8owmMsom9QxmMwqm9RJk7gvntUxn9ZOlrpWm71dn79bocRgocFdosVhocFko8Jlo8JppsRrp8Vpqclyq8hvrMtwrct3rsp0r811sM17sct5ss99tNCBtM6Ctc6Dtc6LutGKvNWOvNKNvdaQvdSWwdaXwdafyNyfyN2pzd641eO72ObG3urI3unJ3+rM4evP4+zW5+/X5+/Y6fHe7PLf7PLf7fTg7fTh7fPi7vPj7/Tn8fbs9Pfu9fny9/ry+Pr7/f39/v8yfrSSAAACM0lEQVR42q3RZVcbQRQG4OHeCRtiJESACCEJadOUhgSH4lK0SN3d3b2l7u7u7u7u7v5rOrtEtqHntB/6fpk98z5nzsxdEvGXkH8G6p1OXYRhh9O5Wef6EygrRMSicchHVaxrAVrHYPnVJYhn7ty7dmIVFqx3/Q5Yf/yt8akWb9UDGN/cWKZa7RIDXczY2G8A8Eqbf/47WyUvDqrWioB+DmoagI93xuS7wsfzRaqyIGDnH6iH5jzSLnBXNwL8vIQlAaAuRvcXCOSlFvEkW59NLQyAbdGnGiAU7wqc8BXgw7q8ANigum6USCRCy9bPexHrAOI3RfvBvtLZiw9pNMe8rK/WaI6U5yPGAdRtbQb7iX377iKHIw/bMRCLUxwFSxfyIL6VAPZMJ2Tk3C16fYkA3FiqN6iTgmDXzFFEyMSVy/0nGNhfS8KPPBizZhoJZWCfhyLwCSBuVmciTiS9z0CHtPF2+6RBae8AOraXh4G2DLSh1l4Ws4nKeEDN5tSEEeFA3tWmVI72Afg2KpXKTLk1HNBztTLZe35kPplM5uknDYFK/g6UXhBNHZ70DYJsepQNu4pSRXKoT2niEgIgkbPVsnNvzs8IiRQFFxkVfEZvevoHv1szTHHW36ebRD3JkXd/YOT3q3rS2/wlazI5a5R4EtmWARdfs3s0Xk7PSAaJxyY+X8iQVJp7uNLzuGIeVVypyDWF94QM799NTrlOlOvRhVJLQouef8tgKUtWYk6WdCj5b/kF7oZ0CZFadjcAAAAASUVORK5CYII=">
</head>
<body>
<div id="loading"><div class="lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div></div>
<div id="popupBox"></div>
<div id="popupMask"></div>
<div id="popupMenu"></div>
<div id="selection"></div>
<div id="connexion">
<form class="fill">
<span>
<input type="password" placeholder="Enter password here" autocomplete="password"><button><span class="icon">&nbsp;</span></button>
</span>
</form>
</div>
<div id="contents">
<div id="contentsTitleCreditsLogout">
<div id="title">
<span class="icon"></span>
<h1>PHP Files Manager</h1>
<input type="file" id="upload" name="upload[]" multiple>
</div>
<div id="contentsCredits">
<div id="credits">
<p>Version : <span>' . version_script . ' (beta)</span></p>
<p id="wltrdrUpdate"><span>&copy;</span> <a target="_blank" href="https://wltrdr.dev/">wltrdr.dev</a></p>
</div>
</div>
<div id="logout">
<a title="Logout"></a>
</div>
</div>
<div id="contentsCntrlPath">
<div id="controls" class="fill">
<a id="back" class="disabled" title="Back"></a>
<a id="forward" class="disabled" title="Forward"></a>
<a id="parent" class="disabled" title="Parent"></a>
<a id="refresh" title="Refresh"></a>
<a id="home" class="lastOfFirstLine" class="lastLine" title="Home"></a>
<a id="view" class="lastLine" title="View"></a>
<a id="sort" class="lastLine" title="Sort by"></a>
<a id="settings" class="lastLine" title="Settings"></a>
<span></span>
<a id="create" class="lastLine" title="Create file or directory"></a>
</div>
<div id="contentsPath" class="fill">
<span class="icon"></span> <span id="path"></span>
</div>
</div>
<div id="contentsTreeElems">
<div id="tree" class="fill">
<div class="list"></div>
</div>
<div id="elements" class="fill">
<div class="list"></div>
</div>
</div>
</div>
<script>const scriptVersion = "' . version_script . '"</script>
<script src="?js&init"></script>
<script src="?js&functions"></script>
<script src="?js&boxes"></script>
<script src="?js&elements"></script>
<script src="?js&events"></script>
</body>
</html>
');
}
