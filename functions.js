function ajaxRequest(method, url, data, callback)
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
    if(method === "POST")
    {
        httpRequest.open(method, url)
        httpRequest.setRequestHeader('Content-type', 'application/x-www-form-urlencoded')
        httpRequest.send(data)
    }
    else
    {
        httpRequest.open(method, url + "?" + data)
        httpRequest.send()
    }
}

/* EXPLORER */

function showElements(rep)
{
    const found = rep.match(/(.*)\/\/!token!\\\\(.*)\n\/\/!current!\\\\(.*)\n\/\/!parent!\\\\(.*)\n\/\/!path!\\\\(.*)\n\/\/!tree!\\\\(.*)\n\/\/!elements!\\\\(.*)\/\/!end!\\\\(.*)/s)
    if(found)
    {
        if(found[1] || found[8])
            console.log(`PHP Errors :\n\n${found[1]}\n\n${found[8]}`)
        connexion.style.display = "none"
        contents.style.display = "flex"
        token = found[2]
        currentPath = found[3]
        parentPath = found[4]
        path.innerHTML = found[5]
        listTree.innerHTML = found[6]
        listElements.innerHTML = found[7]
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
    ajaxRequest("POST", "", `${Date.now()}&dir=` + dir, result => {
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
            token = ""
            contents.style.display = "none"
            connexion.style.display = "flex"
        }
    })
}

openDir(currentPath)

/* CONTEXT MENU */

function posMenu(event = false)
{
    let menuWidth = popupMenu.offsetWidth
    let menuHeight = popupMenu.offsetHeight

    if(event.clientX + menuWidth > window.innerWidth)
    {
        if(event.clientX - menuWidth < 0)
            popupMenu.style.left = "0px"
        else
            popupMenu.style.left = (event.clientX - menuWidth) + "px"
    }
    else
        popupMenu.style.left = event.clientX + "px"

    if(event.clientY + menuHeight > window.innerHeight)
    {
        if(event.clientY - menuHeight < 0)
            popupMenu.style.top = "0px"
        else
            popupMenu.style.top = (event.clientY - menuHeight) + "px"
    }
    else
        popupMenu.style.top = event.clientY + "px"
}

function openMenu(html, ev)
{
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

function closeBox()
{
    popupBox.innerHTML = ""
    popupMask.style.display = "none"
    popupBox.style.display = "none"
}

function showBox(html, callback = false)
{
    popupBox.innerHTML = html
    popupMask.style.display = "block"
    popupBox.style.display = "block"
    setTimeout(() => {
        if(callback !== false)
        {
            try {
                callback()
            }
            catch {}
        }
    }, delayMenuMs)
}

function openBox(type, vals, callback = false)
{
    setTimeout(() => {
        if(type === "alert") // valss (txt) || vals.txt, vals.btn
        {
            let txt = vals
            let btn = "Ok"
            if(typeof(vals) !== "string")
            {
                txt = vals.txt
                btn = vals.btn
            }
            showBox(`<form>
    <span>
        ${txt}
        <input type="text" class="hidden" value="">
    </span>
    <button>${btn}</button>
</form>`, () => {
                let input = popupBox.querySelector("input")
                input.focus()

                popupBox.querySelector("button").addEventListener("click", () => {
                    closeBox()
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
            showBox(`<span>
    ${txt}
</span>
<button id="y">${btnOk}</button>
<button id="n">${btnNo}</button>`, () => {
                popupBox.querySelector("button#y").addEventListener("click", () => {
                    callback()
                    closeBox()
                })

                popupBox.querySelector("button#n").addEventListener("click", () => {
                    closeBox()
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
            showBox(`<form>
    <span>
        ${txt}
        <input type="text" value="${value}">
    </span>
    <button id="y">${btnOk}</button>
    <button id="n">${btnNo}</button>
</form>`, () => {
                let input = popupBox.querySelector("input")
                input.focus()

                popupBox.querySelector("button#y").addEventListener("click", () => {
                    callback(input.value)
                    closeBox()
                })

                popupBox.querySelector("button#n").addEventListener("click", () => {
                    closeBox()
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
    }, delayMenuMs)
}

/* CONTEXT MENUS "`’ʿʾ′ˊˈꞌ‘ˋ‵ */

function menuDir(name, path)
{
    openMenu(`<span>${name}/</span>
<a onclick="openDir('${path}')">Open</a>
<a onclick="">Show (if possible)</a>
<a onclick="openBox('prompt', 'Enter the new name for <b>ˈ${name}/ˈ</b> :', inputName => { renElement('${path}', '${name}/', inputName) })">Rename</a>
<a onclick="">Duplicate</a>
<a onclick="">Copy to</a>
<a onclick="">Move to</a>
<a onclick="">Delete</a>
<a onclick="">Change chmods</a>
`, event)
    event.preventDefault()
}

function menuFile(name, path)
{
    openMenu(`<span>${name}</span>
<a onclick="">Show (if possible)</a>
<a onclick="">Download</a>
<a onclick="">Edit</a>
<a onclick="openBox('prompt', 'Enter the new name for <b>ˈ${name}ˈ</b> :', inputName => { renElement('${path}', '${name}', inputName) })">Rename</a>
<a onclick="">Duplicate</a>
<a onclick="">Copy to</a>
<a onclick="">Move to</a>
<a onclick="">Delete</a>
<a onclick="">Change chmods</a>
<a onclick="">File information</a>
`, event)
    event.preventDefault()
}

/* MOBILE */

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

/* CLICS ON ELEMENTS */

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

/* GLOBAL ACTIONS */

function newElement(type, name)
{
    if(name === "")
        openBox('alert', 'Error : <b>Name can\'t be empty</b> !')
    else
    {
        ajaxRequest("POST", "", `${Date.now()}&new=${type}&name=${name}&dir=${currentPath}&token=${token}`, result => {
            if(result === "created")
                openDir(currentPath)
            else
                openBox('alert', 'Error : <b>' + result + '</b> !')
        })
    }
}

function uploadFiles()
{
    disableBodyPrevDef = true
    setTimeout(() => {
        upload.click()
        disableBodyPrevDef = false
    }, delayMenuMs)
}

/* ELEMENTS ACTIONS */

function renElement(path, oldName, newName)
{
    if(newName === "")
        openBox('alert', 'Error : <b>Name can\'t be empty</b> !')
    else
    {
        ajaxRequest("POST", "", `${Date.now()}&ren=${oldName}&dir=${path}&name=${newName}&token=${token}`, result => {
            if(result === "renamed")
                openDir(currentPath)
            else
                openBox('alert', 'Error : <b>' + result + '</b> !')
        })
    }
}
