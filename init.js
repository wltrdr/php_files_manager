const mslongClic = 1500         // LONG CLIC ON MOBILE
const delayMenuMs = 50          // INCREASE IF BUGS ON CONTEXT MENU OR POPUP BOX
const delayH1MobileMs = 1500    // H1 BLINK SPEED ON MOBILE
const delayBadCnxMs = 150       // INPUT BLINK SPEED IF BAD PASSWORD
const delayBadCnxBkMs = 50      // INPUT HIDING SPEED DURING BLINK IF BAD PASSWORD
const historyMax = 50           // MAX ENTRIES IN HISTORY

const loading = document.querySelector("#loading")
const popupBox = document.querySelector("#popupBox")
const popupMask = document.querySelector("#popupMask")
const popupMenu = document.querySelector("#popupMenu")
const connexion = document.querySelector("#connexion")
const formConnexion = document.querySelector("#connexion form")
const inputConnexion = document.querySelector("#connexion form input")
const btnConnexion = document.querySelector("#connexion form button")
const contents = document.querySelector("#contents")
const h1 = document.querySelector("h1")
const inputUpload = document.querySelector("#title #upload")
const btnBack = document.querySelector("#controls #back")
const btnForward = document.querySelector("#controls #forward")
const btnParent = document.querySelector("#controls #parent")
const btnRefresh = document.querySelector("#controls #refresh")
const btnHome = document.querySelector("#controls #home")
const btnView = document.querySelector("#controls #view")
const btnSort = document.querySelector("#controls #sort")
const btnCreate = document.querySelector("#controls #create")
const path = document.querySelector("#path")
const logout = document.querySelector("#logout")
const tree = document.querySelector("#tree")
const listTree = document.querySelector("#tree .list")
const elements = document.querySelector("#elements")
const listElements = document.querySelector("#elements .list")

const inputConnexionPH = inputConnexion.placeholder

let token
let currentPath = "."
let parentPath = "false"
let history = []
let historyLevel = 0
let disableBodyPrevDef = false
let uploadMaxFileSize = 0
let uploadMaxTotalSize = 0
