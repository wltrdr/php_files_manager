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
