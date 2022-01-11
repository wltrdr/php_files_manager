const longClicMs = 1500			// TIME OF LONG CLIC ON MOBILE
const checkIntervMs = 500		// TIME INTERVAL TO CHECK IF DIR MUST BE REFRESHED
const BtwRefreshesMs = 3333		// TIME BETWEEN EACH AUTO-REFRESH
const delayLoadingMs = 500		// TIME BEFORE SHOWING LOADING DURING NAVIGATION
const delayMenuMs = 50			// INCREASE IF BUGS ON CONTEXT MENU OR POPUP BOX
const delayH1MobileMs = 1500	// H1 BLINK SPEED ON MOBILE
const delayBadCnxMs = 150		// INPUT BLINK SPEED IF BAD PASSWORD
const delayBadCnxBkMs = 50		// INPUT HIDING SPEED DURING BLINK IF BAD PASSWORD
const historyMax = 50			// MAX ENTRIES IN HISTORY

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
let typeView = 0
let typeOrder = 0
let typeOrderDesc = 0
let typeUploadExists = 0
let typeCopyMoveExists = 1
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
				/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.
				test(navData)
				||
				/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.
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
