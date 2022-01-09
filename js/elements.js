/* CONTEXT MENUS */

function menuDir(name, pathEncoded, nameEncoded, fullPathEncoded, webUrl, isLink = false) {
	if(webUrl === false)
		webUrl = ""
	else
		webUrl = `<a onclick="window.open('${webUrl}')">See web version</a>`
	if(isLink === false)
		openMenu(`<span>${name}/</span>
		<a onclick="openDir('${fullPathEncoded}')">Open</a>
		${webUrl}
		<a onclick="openBox('prompt', { txt: 'Enter the new name for <b>ʿ${name}/ʿ</b> :', value: '${name}' }, null, inputName => { renameElement('${pathEncoded}', '${nameEncoded}', inputName) })">Rename</a>
		<a onclick="duplicateElement('${pathEncoded}', '${nameEncoded}')">Duplicate</a>
		<a onclick="openBox('path', 'Copy <b>ʿ${name}ʿ/</b> to :', null, inputPath => { copyElement('${pathEncoded}', '${nameEncoded}', inputPath) })">Copy to</a>
		<a onclick="openBox('path', 'Move <b>ʿ${name}ʿ/</b> to :', null, inputPath => { moveElement('${pathEncoded}', '${nameEncoded}', inputPath) })">Move to</a>
		<a onclick="openBox('confirm', 'Delete the directory <b>ʿ${name}/ʿ</b> ?', 'warn', () => { deleteElement('${pathEncoded}', '${nameEncoded}') })">Delete</a>
		<a onclick="openBox('chmods', { name: '${name}/', nameEncoded: '${nameEncoded}' })">Change chmods</a>
		`, event)
	else
		openMenu(`<span>${name}</span>
		<a onclick="openDir('${fullPathEncoded}')">Open</a>
		${webUrl}
		<a onclick="openBox('prompt', { txt: 'Enter the new name for <b>ʿ${name}ʿ</b> :', value: '${name}' }, null, inputName => { renameElement('${pathEncoded}', '${nameEncoded}', inputName) })">Rename</a>
		<a onclick="openBox('confirm', 'Delete the link <b>ʿ${name}ʿ</b> ?', 'warn', () => { deleteElement('${pathEncoded}', '${nameEncoded}') })">Delete</a>
		<a onclick="openBox('chmods', { name: '${name}', nameEncoded: '${nameEncoded}' })">Change chmods</a>
		`, event)
}

function menuFile(name, pathEncoded, nameEncoded, webUrl, isLink = false) {
	if(webUrl === false)
		webUrl = ""
	else
		webUrl = `<a onclick="window.open('${webUrl}')">See web version</a>`
	if(isLink === false)
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
	else
		openMenu(`<span>${name}</span>
		${webUrl}
		<a onclick="openBox('prompt', { txt: 'Enter the new name for <b>ʿ${name}ʿ</b> :', value: '${name}' }, null, inputName => { renameElement('${pathEncoded}', '${nameEncoded}', inputName) })">Rename</a>
		<a onclick="openBox('confirm', 'Delete the link <b>ʿ${name}ʿ</b> ?', 'warn', () => { deleteElement('${pathEncoded}', '${nameEncoded}') })">Delete</a>
		<a onclick="openBox('chmods', { name: '${name}', nameEncoded: '${nameEncoded}' })">Change chmods</a>
		`, event)
}

/* CLICK ON ELEMENTS */

function startClicDir() {
	if(event.button !== 2)
		timeClicDir = Date.now()
}

function endClicDir(name, pathEncoded, nameEncoded, fullPathEncoded, webUrl, isLink = false) {
	popupMenu.style.display = "none"
	if(event.button === 2)
		menuDir(name, pathEncoded, nameEncoded, fullPathEncoded, webUrl, isLink)
	else {
		if(isOnMobile === true) {
			if(Date.now() - timeClicDir > longClicMs)
				menuDir(name, pathEncoded, nameEncoded, fullPathEncoded, webUrl, isLink)
			else
				openDir(fullPathEncoded, isLink)
			timeClicDir = 0
		}
		else
			openDir(fullPathEncoded, isLink)
	}
}

/* ADD ACTIONS */

function newElement(type, name) {
	if(name === "")
		openBox("alert", "Error : <b>Name can't be empty</b>", "err")
	else {
		ajaxRequest("POST", "", `${Date.now()}&new=${type}&dir=${currentPath}&name=${name}&token=${token}`, result => {
			if(result === "created")
				openDir(currentPath, true)
			else {
				openDir(currentPath, true)
				openBox("alert", "Error : <b>" + result + "</b>", "err")
			}
		})
	}
}

/* OTHER ACTIONS */

function downloadElement(path, name) {
	window.open(`?${Date.now()}&download=${name}&dir=${path}&token=${token}`)
}

function renameElement(path, oldName, newName) {
	if(newName === "")
		openBox("alert", "Error : <b>Name can't be empty</b>", "err")
	else {
		ajaxRequest("POST", "", `${Date.now()}&rename=${oldName}&dir=${path}&name=${newName}&token=${token}`, result => {
			if(result === "renamed")
				openDir(currentPath, true)
			else {
				openDir(currentPath, true)
				openBox("alert", "Error : <b>" + result + "</b>", "err")
			}
		})
	}
}

function duplicateElement(path, name) {
	ajaxRequest("POST", "", `${Date.now()}&duplicate=${name}&dir=${path}&path=${path}&token=${token}`, result => {
		if(result === "duplicated")
			openDir(currentPath, true)
		else {
			openDir(currentPath, true)
			openBox("alert", "Error : <b>" + result + "</b>", "err")
		}
	})
}

function copyElement(path, name, newPath) {
	ajaxRequest("POST", "", `${Date.now()}&copy=${name}&dir=${path}&path=${newPath}&if_exists=${typeCopyMoveExists}&token=${token}`, result => {
		if(result === "copied")
			openDir(currentPath, true)
		else {
			openDir(currentPath, true)
			openBox("alert", "Error : <b>" + result + "</b>", "err")
		}
	})
}

function moveElement(path, name, newPath) {
	ajaxRequest("POST", "", `${Date.now()}&move=${name}&dir=${path}&path=${newPath}&if_exists=${typeCopyMoveExists}&token=${token}`, result => {
		if(result === "moved")
			openDir(currentPath, true)
		else {
			openDir(currentPath, true)
			openBox("alert", "Error : <b>" + result + "</b>", "err")
		}
	})
}

function deleteElement(path, name) {
	ajaxRequest("POST", "", `${Date.now()}&delete=${name}&dir=${path}&token=${token}`, result => {
		if(result === "deleted")
			openDir(currentPath, true)
		else {
			openDir(currentPath, true)
			openBox("alert", "Error : <b>" + result + "</b>", "err")
		}
	})
}
