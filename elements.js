function leftClickDir(dir)
{
    if(isOnMobile === false)
        openDir(dir)
    event.preventDefault()
}

function rightClickDir(name, pathEncoded, nameEncoded, urlEncoded, webUrl)
{
    menuDir(name, pathEncoded, nameEncoded, urlEncoded, webUrl)
    event.preventDefault()
}

let timeClicDir = 0

function startClicDir()
{
    if(isOnMobile === true)
        timeClicDir = Date.now()
    event.preventDefault()
}

function endClicDir(name, pathEncoded, nameEncoded, urlEncoded, webUrl)
{
    if(isOnMobile === true && timeClicDir !== 0)
    {
        if(Date.now() - timeClicDir > mslongClic)
            menuDir(name, pathEncoded, nameEncoded, urlEncoded, webUrl)
        else
            openDir(urlEncoded)
        timeClicDir = 0
    }
    event.preventDefault()
}

/* ADD ACTIONS */

function newElement(type, name)
{
    if(name === "")
        openBox("alert", "Error : <b>Name can't be empty</b>", "err")
    else
    {
        ajaxRequest("POST", "", `${Date.now()}&new=${type}&dir=${currentPath}&name=${name}&token=${token}`, result => {
            if(result === "created")
                openDir(currentPath)
            else
            {
                openDir(currentPath)
                openBox("alert", "Error : <b>" + result + "</b>", "err")
            }
        })
    }
}

function getUploadSizes(callback = false)
{
    if(uploadMaxFileSize === 0 || uploadMaxTotalSize === 0)
    {
        ajaxRequest("POST", "", `${Date.now()}&get_upload_sizes`, result => {
            const found = result.match(/\[max_upload_sizes=([0-9]+)\|([0-9]+)\]/)
            if(found)
            {
                uploadMaxFileSize = parseInt(found[1], 10)
                uploadMaxTotalSize = parseInt(found[2], 10)
                if(callback !== false)
                {
                    if(uploadMaxFileSize === 0 || uploadMaxTotalSize === 0)
                        callback(false)
                    else
                        callback(true)
                }
            }
            else if(callback !== false)
                callback(false)
        })
    }
    else if(callback !== false)
        callback(true)
}

getUploadSizes()

function uploadFiles()
{
    disableBodyPrevDef = true
    setTimeout(() => {
        inputUpload.click()
        disableBodyPrevDef = false
    }, delayMenuMs)
}

/* OTHER ACTIONS */

function downloadElement(path, name)
{
    window.open(`?${Date.now()}&download=${name}&dir=${path}&token=${token}`)
}

function renameElement(path, oldName, newName)
{
    if(newName === "")
        openBox("alert", "Error : <b>Name can't be empty</b>", "err")
    else
    {
        ajaxRequest("POST", "", `${Date.now()}&rename=${oldName}&dir=${path}&name=${newName}&token=${token}`, result => {
            if(result === "renamed")
                openDir(currentPath)
            else
            {
                openDir(currentPath)
                openBox("alert", "Error : <b>" + result + "</b>", "err")
            }
        })
    }
}

function duplicateElement(path, name)
{
    ajaxRequest("POST", "", `${Date.now()}&duplicate=${name}&dir=${path}&token=${token}`, result => {
        if(result === "duplicated")
            openDir(currentPath)
        else
        {
            openDir(currentPath)
            openBox("alert", "Error : <b>" + result + "</b>", "err")
        }
    })
}

function deleteElement(path, name)
{
    ajaxRequest("POST", "", `${Date.now()}&delete=${name}&dir=${path}&token=${token}`, result => {
        if(result === "deleted")
            openDir(currentPath)
        else
        {
            openDir(currentPath)
            openBox("alert", "Error : <b>" + result + "</b>", "err")
        }
    })
}
