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
        httpRequest.open("POST", url)
        httpRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded")
        httpRequest.send(data)
    }
    else if(method === "FILES")
    {
        httpRequest.open("POST", url)
        httpRequest.send(data)
    }
    else
    {
        httpRequest.open(method, url + "?" + data)
        httpRequest.send()
    }
}

/* EXPLORER */

function showElements(result)
{
    const found = result.match(/(.*)\/\/!token!\\\\(.*)\n\/\/!current!\\\\(.*)\n\/\/!parent!\\\\(.*)\n\/\/!path!\\\\(.*)\n\/\/!tree!\\\\(.*)\n\/\/!elements!\\\\(.*)\n\/\/!order!\\\\(.*)\n\/\/!desc!\\\\(.*)\n\/\/!end!\\\\(.*)/s)
    if(found)
    {
        if(found[1] || found[10])
            console.log(`PHP Errors :\n\n${found[1].replace(/<[^>]+>/g, '')}\n\n${found[10].replace(/<[^>]+>/g, '')}`)
        connexion.style.display = "none"
        contents.style.display = "flex"
        token = found[2]
        currentPath = found[3]
        parentPath = found[4]
        path.innerHTML = found[5]
        listTree.innerHTML = found[6]
        listElements.innerHTML = found[7]
        typeOrder = parseInt(found[8], 10)
        typeOrderDesc = parseInt(found[9], 10)
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
        const fatal = result.match(/(.*)\[fatal=([^\]]+)\](.*)/s)
        if(fatal)
        {
            if(fatal[1] || fatal[3])
                console.log(`PHP Errors :\n\n${fatal[1].replace(/<[^>]+>/g, '')}\n\n${fatal[3].replace(/<[^>]+>/g, '')}`)
            alert("Error : " + fatal[2])
        }
        else
        {
            alert("Error : Bad regex")
            console.log(result)
        }
    }
}

function openDir(dir, order = "", desc = "")
{
    dirLoaded = false
    setTimeout(() => {
        if(dirLoaded === false)
            loading.style.display = "block"
    }, delayLoadingMs)
    if(order !== "")
        order = "&order=" + order
    if(desc !== "")
    {
        if(desc === false)
            desc = "&desc=0"
        else
            desc = "&desc=1"
    }
    ajaxRequest("POST", "", `${Date.now()}&dir=` + dir + order + desc, result => {
        dirLoaded = true
        loading.style.display = "none"
        if(result !== "false")
        {
            showElements(result)
            let nbHistory = history.length
            if(nbHistory === 0)
            {
                history.push(dir)
                btnForward.className = "disabled"
                btnBack.className = "disabled"
                if(history.length > historyMax)
                    history.splice(0, 1)
            }
            else
            {
                if(dir !== history[nbHistory - historyLevel - 1]) // ISN'T A REFRESH
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
                    if(history.length > historyMax)
                        history.splice(0, 1)
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

function changeView(oldView, newView)
{
    if(oldView !== newView)
    {
        typeView = newView
        if(oldView !== 0)
            elements.classList.remove("view" + oldView)
        elements.classList.add("view" + newView)
    }
}

/* CONTEXT MENU */

function posMenu(event = false)
{
    const menuWidth = popupMenu.offsetWidth
    const menuHeight = popupMenu.offsetHeight

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

function showBox(txt, icon, inputs, buttons, noForm = true, callback = false)
{

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
        html = "<form>\n" + html + "</form>"
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

function openBox(type, vals, icon = null, callback = false)
{
    setTimeout(() => {
        if(type === "alert") // valss (txt) || vals.txt, vals.btn
        {
            if(icon === null)
                icon = "info"
            let txt = vals
            let btn = "Ok"
            if(typeof(vals) !== "string")
            {
                if(vals.txt)
                    txt = vals.txt
                if(vals.btn)
                    btn = vals.btn
            }
            showBox(txt, icon, `<input type="text" class="hidden" value="">`,  `<button>${btn}</button>`, false, () => {
                const input = popupBox.querySelector("input")
                input.focus()

                popupBox.querySelector("button").addEventListener("click", () => {
                    closeBox()
                })
            })
        }
        else if(type === "confirm") // vals (txt) || vals.txt, vals.btnOk, vals.btnNo
        {
            if(icon === null)
                icon = "ask"
            let txt = vals
            let btnOk = "Yes"
            let btnNo = "No"
            if(typeof(vals) !== "string")
            {
                if(vals.txt)
                    txt = vals.txt
                if(vals.btnOk)
                    btnOk = vals.btnOk
                if(vals.btnNo)
                    btnNo = vals.btnNo
            }
            showBox(txt, icon, "", `<button id="y">${btnOk}</button>\n<button id="n">${btnNo}</button>`, true, () => {
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
            if(icon === null)
                icon = "ask"
            let txt = vals
            let value = ""
            let btnOk = "Ok"
            let btnNo = "Cancel"
            if(typeof(vals) !== "string")
            {
                if(vals.txt)
                    txt = vals.txt
                if(vals.value)
                    value = vals.value
                if(vals.btnOk)
                    btnOk = vals.btnOk
                if(vals.btnNo)
                    btnNo = vals.btnNo
            }
            showBox(txt, icon, `<input type="text" value="${value}">`, `<button id="y">${btnOk}</button>\n<button id="n">${btnNo}</button>`, false, () => {
                const input = popupBox.querySelector("input")
                input.focus()
                const tmp = input.value
                input.value = ""
                input.value = tmp

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
            if(icon === null)
                icon = "path"
            let txt = vals
            let btnOk = "Ok"
            let btnNo = "Cancel"
            if(typeof(vals) !== "string")
            {
                if(vals.txt)
                    txt = vals.txt
                if(vals.btnOk)
                    btnOk = vals.btnOk
                if(vals.btnNo)
                    btnNo = vals.btnNo
            }
            showBox(txt, icon, `ICI LE PATH EXPLORER => <input type="text" readonly="readonly">`, `<button id="y">${btnOk}</button>\n<button id="n">${btnNo}</button>`, false, () => {
                const input = popupBox.querySelector("input")

                popupBox.querySelector("button#y").addEventListener("click", () => {
                    callback(input.value)
                    closeBox()
                })

                popupBox.querySelector("button#n").addEventListener("click", () => {
                    closeBox()
                })
            })
        }
        else if(type === "edit")
        {
            if(icon === null)
                icon = "edit"
            let txt = vals
            let value = ""
            let btnOk = "Ok"
            let btnNo = "Cancel"
            if(typeof(vals) !== "string")
            {
                if(vals.txt)
                    txt = vals.txt
                if(vals.value)
                    value = vals.value
                if(vals.btnOk)
                    btnOk = vals.btnOk
                if(vals.btnNo)
                    btnNo = vals.btnNo
            }
            showBox(txt, icon, `<textarea>${value}</textarea>`, `<button id="y">${btnOk}</button>\n<button id="n">${btnNo}</button>`, false, () => {
                const input = popupBox.querySelector("textarea")
                input.focus()
                const tmp = input.value
                input.value = ""
                input.value = tmp

                popupBox.querySelector("button#y").addEventListener("click", () => {
                    callback(input.value)
                    closeBox()
                })

                popupBox.querySelector("button#n").addEventListener("click", () => {
                    closeBox()
                })
            })
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

/* CONTEXT MENUS */

function menuDir(name, pathEncoded, nameEncoded, urlEncoded, webUrl)
{
    if(webUrl === false)
        webUrl = ''
    else
        webUrl = `<a onclick="window.open('${webUrl}')">See web version</a>`
    openMenu(`<span>${name}/</span>
<a onclick="openDir('${urlEncoded}')">Open</a>
${webUrl}
<a onclick="openBox('prompt', { txt: 'Enter the new name for <b>ʿ${name}/ʿ</b> :', value: '${name}' }, null, inputName => { renameElement('${pathEncoded}', '${nameEncoded}', inputName) })">Rename</a>
<a onclick="duplicateElement('${pathEncoded}', '${nameEncoded}')">Duplicate</a>
<a onclick="openBox('path', 'Copy <b>ʿ${name}ʿ/</b> to :', null, inputPath => { copyElement('${pathEncoded}', '${nameEncoded}', inputPath) })">Copy to</a>
<a onclick="openBox('path', 'Move <b>ʿ${name}ʿ/</b> to :', null, inputPath => { moveElement('${pathEncoded}', '${nameEncoded}', inputPath) })">Move to</a>
<a onclick="openBox('confirm', 'Delete the directory <b>ʿ${name}/ʿ</b> ?', 'warn', () => { deleteElement('${pathEncoded}', '${nameEncoded}') })">Delete</a>
<a onclick="">Change chmods</a>
`, event)
    event.preventDefault()
}

function menuFile(name, pathEncoded, nameEncoded, webUrl)
{
    if(webUrl === false)
        webUrl = ''
    else
        webUrl = `<a onclick="window.open('${webUrl}')">See web version</a>`
    openMenu(`<span>${name}</span>
<a onclick="downloadElement('${pathEncoded}', '${nameEncoded}')">Download</a>
${webUrl}
<a onclick="">Edit</a>
<a onclick="openBox('prompt', { txt: 'Enter the new name for <b>ʿ${name}ʿ</b> :', value: '${name}' }, null, inputName => { renameElement('${pathEncoded}', '${nameEncoded}', inputName) })">Rename</a>
<a onclick="duplicateElement('${pathEncoded}', '${nameEncoded}')">Duplicate</a>
<a onclick="openBox('path', 'Copy <b>ʿ${name}ʿ</b> to :', null, inputPath => { copyElement('${pathEncoded}', '${nameEncoded}', inputPath) })">Copy to</a>
<a onclick="openBox('path', 'Move <b>ʿ${name}ʿ</b> to :', null, inputPath => { moveElement('${pathEncoded}', '${nameEncoded}', inputPath) })">Move to</a>
<a onclick="openBox('confirm', 'Delete the file <b>ʿ${name}ʿ</b> ?', 'warn', () => { deleteElement('${pathEncoded}', '${nameEncoded}') })">Delete</a>
<a onclick="">Change chmods</a>
`, event)
    event.preventDefault()
}





// openBox('path', 'texte', null, inputPath => { alert("ok=" + inputPath); })
