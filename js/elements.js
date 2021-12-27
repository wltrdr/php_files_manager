/* CONTEXT MENUS */

function menuDir(name, pathEncoded, nameEncoded, urlEncoded, webUrl)
{
    if(webUrl === false)
        webUrl = ""
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
    <a onclick="openBox('chmods', { name: '${name}/', nameEncoded: '${nameEncoded}' })">Change chmods</a>
    `, event)
}

function menuFile(name, pathEncoded, nameEncoded, webUrl)
{
    if(webUrl === false)
        webUrl = ""
    else
        webUrl = `<a onclick="window.open('${webUrl}')">See web version</a>`
    openMenu(`<span>${name}</span>
    <a onclick="downloadElement('${pathEncoded}', '${nameEncoded}')">Download</a>
    ${webUrl}
    <a onclick="openBox('edit', { name: '${name}', nameEncoded: '${nameEncoded}' })">Edit</a>
    <a onclick="openBox('prompt', { txt: 'Enter the new name for <b>ʿ${name}ʿ</b> :', value: '${name}' }, null, inputName => { renameElement('${pathEncoded}', '${nameEncoded}', inputName) })">Rename</a>
    <a onclick="duplicateElement('${pathEncoded}', '${nameEncoded}')">Duplicate</a>
    <a onclick="openBox('path', 'Copy <b>ʿ${name}ʿ</b> to :', null, inputPath => { copyElement('${pathEncoded}', '${nameEncoded}', inputPath) })">Copy to</a>
    <a onclick="openBox('path', 'Move <b>ʿ${name}ʿ</b> to :', null, inputPath => { moveElement('${pathEncoded}', '${nameEncoded}', inputPath) })">Move to</a>
    <a onclick="openBox('confirm', 'Delete the file <b>ʿ${name}ʿ</b> ?', 'warn', () => { deleteElement('${pathEncoded}', '${nameEncoded}') })">Delete</a>
    <a onclick="openBox('chmods', { name: '${name}', nameEncoded: '${nameEncoded}' })">Change chmods</a>
    `, event)
}

/* CLICK ON ELEMENTS */

function leftClickDir(dir)
{
    if(isOnMobile === false)
        openDir(dir)
}

function startClicDir()
{
    if(isOnMobile === true)
        timeClicDir = Date.now()
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
        ajaxRequest("GET", "", `${Date.now()}&get_upload_sizes`, result => {
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
    ajaxRequest("POST", "", `${Date.now()}&copy=${name}&dir=${path}&path=${path}&token=${token}`, result => {
        if(result === "copied")
            openDir(currentPath)
        else
        {
            openDir(currentPath)
            openBox("alert", "Error : <b>" + result + "</b>", "err")
        }
    })
}

function copyElement(path, name, newPath)
{
    ajaxRequest("POST", "", `${Date.now()}&copy=${name}&dir=${path}&path=${newPath}&token=${token}`, result => {
        if(result === "copied")
            openDir(currentPath)
        else
        {
            openDir(currentPath)
            openBox("alert", "Error : <b>" + result + "</b>", "err")
        }
    })
}

function moveElement(path, name, newPath)
{
    ajaxRequest("POST", "", `${Date.now()}&move=${name}&dir=${path}&path=${newPath}&token=${token}`, result => {
        if(result === "moved")
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
