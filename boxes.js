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

function showBox(txt, icon, inputs, buttons, noForm = true, callback = false, init = false)
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
        if(init !== false)
            init()
        if(callback !== false)
            callback()
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
            ajaxRequest("POST", "", `${Date.now()}&dir=` + currentPath + "&tree_only", result => {
                showBox(txt, icon, `<div id="boxPath"><div class="list">${result}</div></div><input type="text" value="${currentPath}">`, `<button id="y">${btnOk}</button>\n<button id="n">${btnNo}</button>`, false, () => {
                    const input = popupBox.querySelector("input")

                    popupBox.querySelector("button#y").addEventListener("click", () => {
                        callback(input.value)
                        closeBox()
                    })

                    popupBox.querySelector("button#n").addEventListener("click", () => {
                        closeBox()
                    })
                }, () => {
                    try {
                        const boxPath = document.querySelector("#boxPath")
                        boxPath.scrollTop = boxPath.querySelector(".treeDefault").offsetTop - boxPath.querySelector(".list").offsetTop
                    }
                    catch {}
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
