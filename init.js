const mslongClic = 1500
const delayMenuMs = 50
const delayH1MobileMs = 1500
const delayBadCnxMs = 150
const delayBadCnxBkMs = 50

const popupHtml = document.querySelector("#popupHtml")
const connexion = document.querySelector("#connexion")
const formConnexion = document.querySelector("#connexion form")
const inputConnexion = document.querySelector("#connexion form input")
const btnConnexion = document.querySelector("#connexion form button")
const contents = document.querySelector("#contents")
const h1 = document.querySelector("h1")
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

let currentPath = "."
let parentPath = "false"
let history = []
let historyLevel = 0
