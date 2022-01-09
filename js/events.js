document.body.addEventListener("click", () => {
	popupMenu.style.display = "none"
})

document.body.addEventListener("contextmenu", ev => {
	popupMenu.style.display = "none"
	ev.preventDefault()
})

document.body.addEventListener("dragover", ev => {
    ev.preventDefault()
})

document.body.addEventListener("dragleave", ev => {
    ev.preventDefault()
})

document.body.addEventListener("drop", ev => {
    ev.preventDefault()
})

window.addEventListener("resize", () => {
	isOnMobile = onMobile()
})

document.addEventListener("keydown", ev => {
	if((ev.key && (ev.key === "Escape" || ev.key === "Esc")) || (ev.keyCode && ev.keyCode === 27)) {
		popupMenu.style.display = "none"
		popupBox.style.display = "none"
		popupMask.style.display = "none"
	}
})

/* UPLOAD */

inputUpload.addEventListener("change", () => {
	uploadFiles()
})

function dragOverAdir(el, dir) {
	overAdir = dir
	if(el.className === "dir")
		el.className = "dirDrag"
	else if(el.className === "linkdir")
		el.className = "linkdirDrag"
	if(elements.classList.contains("dragOver"))
		elements.classList.remove("dragOver")
	event.preventDefault()
}

function dragLeaveAdir(el) {
	overAdir = false
	if(el.className === "dirDrag")
		el.className = "dir"
	else if(el.className === "linkdirDrag")
		el.className = "linkdir"
	if(!elements.classList.contains("dragOver"))
		elements.classList.add("dragOver")
	event.preventDefault()
}

function dropOnAdir(el) {
	if(el.className === "dirDrag")
		el.className = "dir"
	else if(el.className === "linkdirDrag")
		el.className = "linkdir"
	if(!elements.classList.contains("dragOver"))
		elements.classList.add("dragOver")
	event.preventDefault()
}

elements.addEventListener("dragover", ev => {
	if(overAdir !== false && elements.classList.contains("dragOver"))
		elements.classList.remove("dragOver")
	else if(overAdir === false && !elements.classList.contains("dragOver"))
		elements.classList.add("dragOver")
    ev.preventDefault()
})

elements.addEventListener("dragleave", ev => {
	if(elements.classList.contains("dragOver"))
		elements.classList.remove("dragOver")
    ev.preventDefault()
})

elements.addEventListener("drop", ev => {
	if(elements.classList.contains("dragOver"))
		elements.classList.remove("dragOver")
	inputUpload.files = ev.dataTransfer.files
	if(overAdir === false)
		uploadFiles()
	else
		uploadFiles(overAdir)
	overAdir = false
    ev.preventDefault()
})

function dragOverAtreeDir(el, dir) {
	overAdir = dir
	el.className = "dirDrag"
	event.preventDefault()
}

function dragLeaveAtreeDir(el) {
	overAdir = false
	el.className = "dir"
	event.preventDefault()
}

function dropOnAtreeDir(el) {
	el.className = "dir"
	inputUpload.files = event.dataTransfer.files
	uploadFiles(overAdir)
	overAdir = false
	event.preventDefault()
}

/* CONTROLS */

btnBack.addEventListener("click", () => {
	if(btnBack.className !== "disabled") {
		const nbHistory = history.length
		if(nbHistory > 1) {
			ajaxRequest("POST", "", `${Date.now()}&dir=` + history[nbHistory - historyLevel - 2], result => {
				if(result !== "false") {
					showElements(result)
					historyLevel++
					btnForward.className = ""
					if(historyLevel === nbHistory - 1)
						btnBack.className = "disabled"
				}
				else {
					contents.style.display = "none"
					connexion.style.display = "flex"
				}
			})
		}
	}
})

btnForward.addEventListener("click", () => {
	if(btnForward.className !== "disabled" && historyLevel > 0) {
		const nbHistory = history.length
		ajaxRequest("POST", "", `${Date.now()}&dir=` + history[nbHistory - historyLevel], result => {
			if(result !== "false") {
				showElements(result)
				historyLevel--
				btnBack.className = ""
				if(historyLevel === 0)
					btnForward.className = "disabled"
			}
			else {
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
	const choices = ["Icons", "Small icons", "Details"]
	let html = ""

	choices.forEach((type, i) => {
		curView = ""
		if(typeView === i)
			curView = "&#8226; "
		html += `<a onclick="changeView(${typeView}, ${i}, true)">${curView}${type}</a>\n`
	})

	openMenu(`<span>View :</span>
${html}
`, ev)
})

btnSort.addEventListener("click", ev => {
	const choices = ["Name", "Date modified", "Size", "Type"]
	let html = ""

	choices.forEach((type, i) => {
		curOrder = ""
		if(typeOrder === i)
			curOrder = "&#8226; "
		html += `<a onclick="openDir(currentPath, false, false, ${i}, false)">${curOrder}${type}</a>\n`
	})

	let curAsc = ""
	let curDesc = ""
	if(typeOrderDesc === 1)
		curDesc = "&#8226; "
	else
		curAsc = "&#8226; "
	openMenu(`<span>Sort by :</span>
	${html}
	<a class="gap" onclick="openDir(currentPath, false, false, '', false)">${curAsc}Ascending</a>
	<a class="" onclick="openDir(currentPath, false, false, '', true)">${curDesc}Descending</a>
	`, ev)
})

btnCreate.addEventListener("click", ev => {
	openMenu(`<a onclick="openBox('prompt', 'Enter a name for the new directory :', null, inputName => { newElement('dir', inputName) })">Create directory</a>
	<a onclick="openBox('prompt', 'Enter a name for the new file :', null, inputName => { newElement('file', inputName) })">Create file</a>
	<a onclick="inputUpload.click()">Upload file(s)</a>
	`, ev)
})

btnSettings.addEventListener("click", ev => {
	let html = "<span class=\"simple\">(Upload) If target exists :</span>"
	const choicesUpload = ["Ask", "Do nothing", "Rename old", "Rename new", "Replace"]
	choicesUpload.forEach((type, i) => {
		let curUpload = ""
		if(typeUploadExists === i)
			curUpload = "&#8226; "
		html += `<a onclick="changeTypeUploadExists(${i}, true)">${curUpload}${type}</a>\n`
	})

	html += "<span class=\"simple\">(Copy/move) If target exists :</span>"
	const choicesCopyMove = ["Do nothing", "Rename old", "Rename new", "Replace"]
	choicesCopyMove.forEach((type, i) => {
		let curCopyMove = ""
		if(typeCopyMoveExists === i)
			curCopyMove = "&#8226; "
		html += `<a onclick="changeTypeCopyMoveExists(${i}, true)">${curCopyMove}${type}</a>\n`
	})

	openMenu(html, ev)
})

/* CONNEXION */

btnConnexion.addEventListener("click", ev => {
	ajaxRequest("POST", "", `${Date.now()}&pwd=` + inputConnexion.value, result => {
		if(result !== "false") {
			inputConnexion.className = ""
			btnConnexion.className = ""
			showElements(result)
			inputConnexion.placeholder = inputConnexionPH
			inputConnexion.value = ""
		}
		else {
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
		if(result === "bye") {
			token = ""
			contents.style.display = "none"
			connexion.style.display = "flex"
		}
		else
			alert("Error : Logout failed")
	})
})
