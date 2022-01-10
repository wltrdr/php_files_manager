/* CLICK ON ELEMENTS */

function selectElement(el, nameEncoded) {
	disableAutoRefresh = true
	if(!returnObjInArr(selectedElements, nameEncoded, "nameEncoded", true)) {
		el.classList.add("selected")
		selectedElements.push({
			element : el,
			nameEncoded : nameEncoded
		})
	}
}

function selectAllElements() {
	elements.querySelectorAll("a").forEach(element => {
		selectElement(element, element.getAttribute("data-name-enc"))
	})
}

function unselectElement(nameEncoded) {
	returnObjInArr(selectedElements, nameEncoded, "nameEncoded").element.classList.remove("selected")
	removeObjsInArr(selectedElements, nameEncoded, "nameEncoded")
	if(selectedElements.length === 0)
		disableAutoRefresh = false
}

function startClic(el, nameEncoded) {
	if(isOnMobile === false && event.button === 0) {
		mouseDownOnEl = true
		if(selectedElements.length > 0 && returnObjInArr(selectedElements, nameEncoded, "nameEncoded", true)) {
			document.body.querySelectorAll("a").forEach(element => {
				if((element.classList.contains("dir") || element.classList.contains("linkdir") || element.classList.contains("dirOpen")) && !returnObjInArr(selectedElements, element.getAttribute("data-name-enc"), "nameEncoded", true))
					element.classList.add("unselected")
			})
			tryToMove = el
		}
	}
}

function endClicTree(pathEncoded, nameEncoded, moveForbidden = false) {
	if(selectWcursor === false) {
		if(isOnMobile === false && event.button === 0 && tryToMove !== false && moveForbidden === false) {
			mouseUpOnEl = true
			moveMultiple(pathEncoded)
		}
		else
			openDir(pathEncoded)
		tryToMove = false
	}
}

function endClic(el, name, pathEncoded, nameEncoded, webUrl, isLink = false) {
	if(event.button === 0 && selectWcursor === false) {
		mouseUpOnEl = true
		if(isOnMobile === false && name === false && tryToMove !== false && tryToMove !== el && !returnObjInArr(selectedElements, nameEncoded, "nameEncoded", true))
			moveMultiple(pathEncoded)
		else {
			if(event.ctrlKey === true) {
				if(selectedElements.length === 0 || !returnObjInArr(selectedElements, nameEncoded, "nameEncoded", true))
					selectElement(el, nameEncoded)
				else
					unselectElement(nameEncoded)
			}
			else if(event.shiftKey === true) {
				if(selectedElements.length === 1 && !returnObjInArr(selectedElements, nameEncoded, "nameEncoded", true)) {
					let foundFirst = false
					elements.querySelectorAll("a").forEach(element => {
						if(foundFirst === false && element.getAttribute("data-name-enc") === selectedElements[0].nameEncoded )
							foundFirst = true
						else if(foundFirst === false && element.getAttribute("data-name-enc") === nameEncoded  ) {
							foundFirst = true
							selectElement(el, nameEncoded)
						}
						else if(foundFirst === true && element.getAttribute("data-name-enc") === selectedElements[0].nameEncoded )
							foundFirst = false
						else if(foundFirst === true && element.getAttribute("data-name-enc") === nameEncoded ) {
							foundFirst = false
							selectElement(el, nameEncoded)
						}
						else if(foundFirst === true)
							selectElement(element, element.getAttribute("data-name-enc"))
					})
				}
				else {
					if(selectedElements.length === 0 || !returnObjInArr(selectedElements, nameEncoded, "nameEncoded", true))
						selectElement(el, nameEncoded)
					else
						unselectElement(nameEncoded)
				}
			}
			else if(selectedElements.length > 0 && returnObjInArr(selectedElements, nameEncoded, "nameEncoded", true))
				menuMultiple()
			else if(selectedElements.length !== 0)
				unselectElements()
			else {
				unselectElements()
				if(name === false)
					openDir(pathEncoded, isLink)
				else
					menuFile(name, pathEncoded, nameEncoded, webUrl, isLink)
			}
		}
		tryToMove = false
	}
}

function rightClic(name, pathEncoded, nameEncoded, fullPathEncoded, webUrl, isLink = false) {
	mouseUpOnEl = true
	if(isOnMobile === false && selectedElements.length > 0 && returnObjInArr(selectedElements, nameEncoded, "nameEncoded", true))
		menuMultiple()
	else {
		unselectElements()
		if(fullPathEncoded !== false)
			menuDir(name, pathEncoded, nameEncoded, fullPathEncoded, webUrl, isLink)
		else
			menuFile(name, pathEncoded, nameEncoded, webUrl, isLink)
	}
}

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

function menuMultiple() {
	let nbSelectedEls = selectedElements.length
	let name = nbSelectedEls + " selected element"
	if(nbSelectedEls > 1)
		name += "s"
	openMenu(`<span>${name}</span>
	<a onclick="duplicateMultiple()">Duplicate</a>
	<a onclick="openBox('path', 'Copy <b>ʿ${name}ʿ</b> to :', null, inputPath => { copyMultiple(inputPath, '${encodeURIComponent(JSON.stringify(selectedElements))}') })">Copy to</a>
	<a onclick="openBox('path', 'Move <b>ʿ${name}ʿ</b> to :', null, inputPath => { moveMultiple(inputPath, '${encodeURIComponent(JSON.stringify(selectedElements))}') })">Move to</a>
	<a onclick="openBox('confirm', 'Delete <b>ʿ${name}ʿ</b> ?', 'warn', () => { deleteMultiple('${encodeURIComponent(JSON.stringify(selectedElements))}') })">Delete</a>
	<a onclick="openBox('chmods', { name: '${name}', files: '${encodeURIComponent(JSON.stringify(selectedElements))}' })">Change chmods</a>
	`, event)
}

/* OTHERS ACTIONS */

function downloadElement(pathEncoded, nameEncoded) {
	window.open(`?${Date.now()}&download=${nameEncoded}&dir=${pathEncoded}&token=${token}`)
}

function newElement(type, name) {
	if(name === "")
		openBox("alert", "Error : <b>Name can't be empty</b>", "err")
	else 
		checkReqRep(`${Date.now()}&new=${type}&dir=${currentPath}&name=${name}&token=${token}`, "created")
}

function renameElement(pathEncoded, oldName, newName) {
	if(newName === "")
		openBox("alert", "Error : <b>Name can't be empty</b>", "err")
	else
		checkReqRep(`${Date.now()}&rename=${oldName}&dir=${pathEncoded}&name=${newName}&token=${token}`, "renamed")
}

function duplicateElement(pathEncoded, nameEncoded) {
	checkReqRep(`${Date.now()}&duplicate=${nameEncoded}&dir=${pathEncoded}&token=${token}`, "duplicated")
}

function copyElement(pathEncoded, nameEncoded, newPath) {
	checkReqRep(`${Date.now()}&copy=${nameEncoded}&dir=${pathEncoded}&path=${newPath}&if_exists=${typeCopyMoveExists}&token=${token}`, "copied")
}

function moveElement(pathEncoded, nameEncoded, newPath) {
	checkReqRep(`${Date.now()}&move=${nameEncoded}&dir=${pathEncoded}&path=${newPath}&if_exists=${typeCopyMoveExists}&token=${token}`, "moved")
}

function deleteElement(pathEncoded, nameEncoded) {
	checkReqRep(`${Date.now()}&delete=${nameEncoded}&dir=${pathEncoded}&token=${token}`, "deleted")
}

function formatMultiple(elements) {
	let ret = ""
	elements.forEach(element => {
		if(currentPath !== ".")
			ret += currentPath
		ret += element.nameEncoded + "%2F%2F%2F"
	})
	return ret.substring(0, ret.length - 9)
}

function duplicateMultiple() {
	checkReqRep(`${Date.now()}&duplicate_multiple=${formatMultiple(selectedElements)}&dir=${currentPath}&token=${token}`, "duplicateds")
}

function copyMultiple(pathEncoded, elements = false) {
	if(elements === false)
		elements = selectedElements
	else
		elements = JSON.parse(decodeURIComponent(elements))
	checkReqRep(`${Date.now()}&copy_multiple=${formatMultiple(elements)}&dir=${pathEncoded}&if_exists=${typeCopyMoveExists}&token=${token}`, "copieds")
}

function moveMultiple(pathEncoded, elements = false) {
	if(elements === false)
		elements = selectedElements
	else
		elements = JSON.parse(decodeURIComponent(elements))
	checkReqRep(`${Date.now()}&move_multiple=${formatMultiple(elements)}&dir=${pathEncoded}&if_exists=${typeCopyMoveExists}&token=${token}`, "moveds")
}

function deleteMultiple(elements) {
	checkReqRep(`${Date.now()}&delete_multiple=${formatMultiple(JSON.parse(decodeURIComponent(elements)))}&token=${token}`, "deleteds")
}
