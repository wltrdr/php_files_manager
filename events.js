/* GENERAL */

document.body.addEventListener("click", ev => {
    popupMenu.style.display = "none"
    if(disableBodyPrevDef !== true)
        ev.preventDefault()
})

document.body.addEventListener("contextmenu", ev => {
    popupMenu.style.display = "none"
    ev.preventDefault()
})

window.addEventListener("resize", () => {
    isOnMobile = onMobile()
})

/* CONTROLS */

btnBack.addEventListener("click", () => {
    if(btnBack.className !== "disabled")
    {
        const nbHistory = history.length
        if(nbHistory > 1)
        {
            ajaxRequest("POST", "", `${Date.now()}&dir=` + history[nbHistory - historyLevel - 2], result => {
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
        ajaxRequest("POST", "", `${Date.now()}&dir=` + history[nbHistory - historyLevel], result => {
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
    openMenu(`<span>View :</span>
<a onclick="">Icons</a>
<a onclick="">Small icons</a>
<a onclick="">List</a>
<a onclick="">Details</a>
`, ev)
})

btnSort.addEventListener("click", ev => {
    openMenu(`<span>Sort by :</span>
<a onclick="">Name</a>
<a onclick="">Date modified</a>
<a onclick="">Size</a>
<a onclick="">Type</a>
<a class="gap" onclick="">Ascending</a>
<a class="" onclick="">Descending</a>
`, ev)
})

btnCreate.addEventListener("click", ev => {
    openMenu(`<a onclick="openBox('prompt', 'Enter a name for the new directory :', null, inputName => { newElement('dir', inputName) })">Create directory</a>
<a onclick="openBox('prompt', 'Enter a name for the new file :', null, inputName => { newElement('file', inputName) })">Create file</a>
<a onclick="uploadFiles()">Upload file(s)</a>
`, ev)
})

inputUpload.addEventListener("change", ev => {
    getUploadSizes(result => {
        if(result === false)
            openBox("alert", "Error : <b>Cannot get server uploads limits</b> !", "err")
        else
        {
            const inputFiles = inputUpload.files
            const nbFiles = inputFiles.length
            if(nbFiles !== 0)
            {
                const formData = new FormData()
                const maxSizeExceeded = []
                let totalSize = 0

                for(let i = 0; i < nbFiles; i++)
                {
                    const size = inputFiles[i].size
                    totalSize += size
                    if(size > uploadMaxFileSize)
                        maxSizeExceeded.push(inputFiles[i].name)
                    formData.append("upload[]", inputFiles[i])
                }
    
                if(maxSizeExceeded.length > 0 || totalSize > uploadMaxTotalSize)
                {
                    let txtErr = ""
    
                    if(totalSize > uploadMaxTotalSize)
                        txtErr = "Upload size exceeded<br><br>"
    
                    for(let i = 0; i < maxSizeExceeded.length; i++)
                        txtErr += "\n" + maxSizeExceeded[i] + "</b> is too big<b><br><br>"
    
                    inputUpload.value = ""
                    openBox("alert", "Error : <b>" + txtErr.substring(0, txtErr.length - 8) + "</b> !", "err")
                }
                else
                {
                    loading.style.display = "block"
                    formData.append(Date.now(), "")
                    formData.append("dir", currentPath)
                    formData.append("token", token)
        
                    ajaxRequest("FILES", "", formData, result => {
                        inputUpload.value = ""
                        loading.style.display = "none"
    
                        if(result === "uploaded")
                            openDir(currentPath)
                        else
                        {
                            openDir(currentPath)
                            openBox("alert", "Error : <b>" + result + "</b> !", "err")
                        }
                    })
                }
            }
        }
    })
    ev.preventDefault()
})

/* CONNEXION */

btnConnexion.addEventListener("click", ev => {
    ajaxRequest("POST", "", `${Date.now()}&pwd=` + inputConnexion.value, result => {
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
        if(result === "bye")
        {
            token = ""
            contents.style.display = "none"
            connexion.style.display = "flex"
        }
        else
            alert("Error : Logout failed")
    })
})
