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

function ajaxRequest(method, url, callback)
{
    const httpRequest = new XMLHttpRequest()
    if(!httpRequest)
    {
        alert("Error : Cannot create instance of XMLHTTP")
        return false
    }
    httpRequest.onreadystatechange = function() {
        if(httpRequest.readyState === XMLHttpRequest.DONE)
        {
            if(httpRequest.status === 200)
                callback(httpRequest.responseText)
            else
                alert("Error : Bad request")
        }
    }
    httpRequest.open(method, url)
    httpRequest.send()
}

function showElements(rep)
{
    const found = rep.match(/(.*)\/\/!current!\\\\(.*)\n\/\/!parent!\\\\(.*)\n\/\/!path!\\\\(.*)\n\/\/!tree!\\\\(.*)\n\/\/!elements!\\\\(.*)\/\/!end!\\\\(.*)/s)
    if(found)
    {
        if(found[1] || found[7])
            console.log(`PHP Errors :\n\n${found[1]}\n\n${found[7]}`)
        connexion.style.display = "none"
        contents.style.display = "flex"
        currentPath = found[2]
        parentPath = found[3]
        path.innerHTML = found[4]
        listTree.innerHTML = found[5]
        listElements.innerHTML = found[6]
        if(parentPath === "false")
            btnParent.className = "disabled"
        else
            btnParent.className = ""
        try {
            tree.scrollTop = document.querySelector(".treeDefault").offsetTop - (listTree.offsetTop + parseInt(window.getComputedStyle(document.querySelector(".treeFirst"), null).getPropertyValue("margin-top"), 10))
        }
        catch {
            console.log("Error : Unable to access parent")
        }
        elements.scrollTop = 0
    }
    else
    {
        alert("Error : Bad regex")
        console.log(rep)
    }
}

function openDir(dir)
{
    ajaxRequest("GET", `?${Date.now()}&dir=` + dir, result => {
        if(result !== "false")
        {
            showElements(result)
            let nbHistory = history.length
            if(nbHistory === 0)
            {
                history.push(dir)
                btnForward.className = "disabled"
                btnBack.className = "disabled"
            }
            else 
            {
                if(dir !== history[nbHistory - historyLevel - 1])
                {
                    if(historyLevel > 0)
                    {
                        for(let i = 0; i < historyLevel; i++)
                        {
                            history.splice(nbHistory - 1, 1)
                            nbHistory--
                        }
                    }
                    history.push(dir)
                    historyLevel = 0
                    btnForward.className = "disabled"
                    btnBack.className = ""
                }
            }
        }
        else
        {
            contents.style.display = "none"
            connexion.style.display = "flex"
        }
    })
}

openDir(currentPath)

function posMenu(event = false)
{
    let menuWidth = popupHtml.offsetWidth
    let menuHeight = popupHtml.offsetHeight

    if(event === false)
    {
        popupHtml.style.left = Math.ceil((window.innerWidth - menuWidth) / 2) + "px"
        popupHtml.style.top = Math.ceil((window.innerHeight - menuHeight) / 2) + "px"
    }
    else
    {
        if(event.clientX + menuWidth > window.innerWidth)
        {
            if(event.clientX - menuWidth < 0)
                popupHtml.style.left = "0px"
            else
                popupHtml.style.left = (event.clientX - menuWidth) + "px"
        }
        else
            popupHtml.style.left = event.clientX + "px"
    
        if(event.clientY + menuHeight > window.innerHeight)
        {
            if(event.clientY - menuHeight < 0)
                popupHtml.style.top = "0px"
            else
                popupHtml.style.top = (event.clientY - menuHeight) + "px"
        }
        else
            popupHtml.style.top = event.clientY + "px"
    }
}

function showPopup(html, callback = false, ev = false)
{
    popupHtml.innerHTML =  html
    setTimeout(() => {
        popupHtml.style.display = "flex"
        posMenu(ev)
        if(callback !== false)
        {
            try {
                callback()
            }
            catch {}
        }
    }, delayMenuMs)
}

function reopenPopup()
{
    setTimeout(() => { popupHtml.style.display = "flex" }, delayMenuMs)
}

function closePopup()
{
    popupHtml.innerHTML = ""
    setTimeout(() => { popupHtml.style.display = "none" }, delayMenuMs * 2)
}

function openPopup(type, vals, ev = false, callback = false)
{
    popupHtml.style.display = "none"
    if(type === "contextMenu")
        showPopup(`<div id="contextMenu">
    ${vals}
</div>`, () => {
            popupHtml.querySelector("span").addEventListener("click", () => {
                reopenPopup()
            })
        }, ev)
    else if(type === "alert") // valss (txt) || vals.txt, vals.btn
    {
        let txt = vals
        let btn = "Ok"
        if(typeof(vals) !== "string")
        {
            txt = vals.txt
            btn = vals.btn
        }
        showPopup(`<div id="popupBox">
    <span>
        ${txt}
    </span>
    <button>${btn}</button>
</div>`, () => {
            popupHtml.querySelector("#popupBox").addEventListener("click", () => {
                reopenPopup()
            })

            popupHtml.querySelector("#popupBox button").addEventListener("click", () => {
                closePopup()
            })
        })
    }
    else if(type === "confirm") // vals (txt) || vals.txt, vals.btnOk, vals.btnNo
    {
        let txt = vals
        let btnOk = "Yes"
        let btnNo = "No"
        if(typeof(vals) !== "string")
        {
            txt = vals.txt
            btnOk = vals.btnOk
            btnNo = vals.btnNo
        }
        showPopup(`<div id="popupBox">
    <span>
        ${txt}
    </span>
    <button id="y">${btnOk}</button>
    <button id="n">${btnNo}</button>
</div>`, () => {
            popupHtml.querySelector("#popupBox").addEventListener("click", () => {
                reopenPopup()
            })

            popupHtml.querySelector("#popupBox button#y").addEventListener("click", () => {
                callback()
                closePopup()
            })

            popupHtml.querySelector("#popupBox button#n").addEventListener("click", () => {
                closePopup()
            })
        })
    }
    else if(type === "prompt") // vals (txt) || vals.txt, vals.value, vals.btnOk, vals.btnNo
    {
        let txt = vals
        let value = ""
        let btnOk = "Ok"
        let btnNo = "Cancel"
        if(typeof(vals) !== "string")
        {
            txt = vals.txt
            value = vals.value
            btnOk = vals.btnOk
            btnNo = vals.btnNo
        }
        showPopup(`<div id="popupBox">
    <span>
        ${txt}
        <input type="text" value="${value}">
    </span>
    <button id="y">${btnOk}</button>
    <button id="n">${btnNo}</button>
</div>`, () => {
            let input = popupHtml.querySelector("#popupBox input")
            input.focus()

            popupHtml.querySelector("#popupBox").addEventListener("click", () => {
                reopenPopup()
                setTimeout(() =>{
                    try {
                        input.focus()
                    }
                    catch {}
                }, delayMenuMs * 2 )
            })

            popupHtml.querySelector("#popupBox button#y").addEventListener("click", () => {
                callback(input.value)
                closePopup()
            })

            popupHtml.querySelector("#popupBox button#n").addEventListener("click", () => {
                closePopup()
            })
        })
    }
    else if(type === "path")
    {

    }
    else if(type === "edit")
    {

    }
    else if(type === "chmods")
    {

    }
    else
    {
        alert("Error : Unknown type")
        return false
    }
}

function menuDir(name, path)
{
    openPopup("contextMenu", `<span>${name}/</span>
<a onclick="openDir('${path}')">Open</a>
<a onclick="">Show (if possible)</a>
<a onclick="">Rename</a>
<a onclick="">Duplicate</a>
<a onclick="">Copy to</a>
<a onclick="">Move to</a>
<a onclick="">Delete</a>
<a onclick="">Change chmods</a>
`, event)
}

function menuFile(name, path)
{
    openPopup("contextMenu", `<span>${name}</span>
<a onclick="">Show (if possible)</a>
<a onclick="">Edit</a>
<a onclick="">Rename</a>
<a onclick="">Duplicate</a>
<a onclick="">Copy to</a>
<a onclick="">Move to</a>
<a onclick="">Delete</a>
<a onclick="">Change chmods</a>
<a onclick="">File information</a>
`, event)
    event.preventDefault()
}

function onMobile()
{
    function checkMobile(navData)
    {
        if(navData && navData != null)
        {
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

let h1Default = h1.innerHTML
let h1Words = h1Default.split(" ")
let h1NbWords = h1Words.length
let h1Lvl = -1

function effectH1Mobile(el)
{
    if(isOnMobile === true)
    {
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

function leftClickDir(dir)
{
    if(isOnMobile === false)
        openDir(dir)
    event.preventDefault()
}

function rightClickDir(name, path)
{
    menuDir(name, path)
    event.preventDefault()
}

let timeClicDir = 0

function startClicDir()
{
    if(isOnMobile === true)
        timeClicDir = Date.now()
    event.preventDefault()
}

function endClicDir(name, path)
{
    if(isOnMobile === true && timeClicDir !== 0)
    {
        if(Date.now() - timeClicDir > mslongClic)
            menuDir(name, path)
        else
            openDir(path)
        timeClicDir = 0
    }
    event.preventDefault()
}

document.body.addEventListener("click", ev => {
    popupHtml.style.display = "none"
    ev.preventDefault()
})

document.body.addEventListener("contextmenu", ev => {
    popupHtml.style.display = "none"
    ev.preventDefault()
})

window.addEventListener("resize", () => {
    isOnMobile = onMobile()
})

btnBack.addEventListener("click", () => {
    if(btnBack.className !== "disabled")
    {
        const nbHistory = history.length
        if(nbHistory > 1)
        {
            ajaxRequest("GET", `?${Date.now()}&dir=` + history[nbHistory - historyLevel - 2], result => {
                if(result !== "false")
                {
                    showElements(result)
                    historyLevel++
                    btnForward.className = ""
                    if(historyLevel === nbHistory - 1)
                        btnBack.className = "disabled"
                }
                else
                {
                    contents.style.display = "none"
                    connexion.style.display = "flex"
                }
            })
        }
    }
})

btnForward.addEventListener("click", () => {
    if(btnForward.className !== "disabled" && historyLevel > 0)
    {
        const nbHistory = history.length
        ajaxRequest("GET", `?${Date.now()}&dir=` + history[nbHistory - historyLevel], result => {
            if(result !== "false")
            {
                showElements(result)
                historyLevel--
                btnBack.className = ""
                if(historyLevel === 0)
                    btnForward.className = "disabled"
            }
            else
            {
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

btnView.addEventListener("click", ev => {
    openPopup("contextMenu", `<span>View :</span>
<a onclick="">Icons</a>
<a onclick="">Small icons</a>
<a onclick="">List</a>
<a onclick="">Details</a>
`, ev)
})

btnSort.addEventListener("click", ev => {
    openPopup("contextMenu", `<span>Sort by :</span>
<a onclick="">Name</a>
<a onclick="">Date modified</a>
<a onclick="">Size</a>
<a onclick="">Type</a>
<a class="gap" onclick="">Ascending</a>
<a class="" onclick="">Descending</a>
`, ev)
})

btnCreate.addEventListener("click", ev => {
    openPopup("contextMenu", `<a onclick="openPopup('prompt', 'Enter a name for the new directory :', false, result => { alert('nom: ' + result) })">Create directory</a>
<a onclick="openPopup('prompt', 'Enter a name for the new file :', false, result => { alert('nom: ' + result) })">Create file</a>
<a onclick="">Upload file(s)</a>
`, ev)
})

btnConnexion.addEventListener("click", ev => {
    ajaxRequest("GET", `?${Date.now()}&pwd=` + inputConnexion.value, result => {
        if(result !== "false")
        {
            inputConnexion.className = ""
            btnConnexion.className = ""
            showElements(result)
            inputConnexion.placeholder = inputConnexionPH
            inputConnexion.value = ""
        }
        else
        {
            inputConnexion.placeholder = "Bad password"
            inputConnexion.className = "err"
            btnConnexion.className = "err"
            inputConnexion.value = ""
            let i = 0
            let clign = setInterval(() => {
                inputConnexion.placeholder = "" 
                setTimeout(() => { inputConnexion.placeholder = "Bad password" }, delayBadCnxBkMs)
                i++
                if(i == 3)
                    clearInterval(clign)
            }, delayBadCnxMs)
        }
    })
    ev.preventDefault()
})

logout.addEventListener("click", () => {
    ajaxRequest("GET", `?${Date.now()}&logout`, result => {
        if(result === "bye")
        {
            contents.style.display = "none"
            connexion.style.display = "flex"
        }
        else
            alert("Error : Logout failed")
    })
})
