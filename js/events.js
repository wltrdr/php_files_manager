function unselectAfterDelay() {
	popupMenu.style.display = "none"
	setTimeout(() => {
		if(clicOnElement === false)
			unselectElements()
		else
			clicOnElement = false
	}, delayMenuMs)
}

document.body.addEventListener("click", ev => {
	unselectAfterDelay()
})

document.body.addEventListener("contextmenu", ev => {
	unselectAfterDelay()
	ev.preventDefault()
})

document.body.addEventListener("mouseup", ev => {
	document.body.querySelectorAll("a").forEach(element => {
		if(element.classList.contains("unselected"))
			element.classList.remove("unselected")
	})
})

elements.addEventListener("mousedown", ev => {
	// cherche si sur element ou non =>
	// si non
		// commence la selection
})

document.body.addEventListener("dragover", ev => {
	unselectElements()
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
		unselectElements()
		tryToMove = false
	}
	else if(ev.key && ev.key === "a" && ev.ctrlKey && ev.ctrlKey === true)
		selectAllElements()
})

/* UPLOAD */

function uploadFiles(dir = false) {
	if(dir === false)
		dir = currentPath
	getUploadSizes(result => {
		if(result === false)
			openBox("alert", "Error : <b>Cannot get server uploads limits</b>", "err")
		else {
			const inputFiles = inputUpload.files
			const nbFiles = inputFiles.length
			if(nbFiles !== 0) {
				const formData = new FormData()
				const maxSizeExceeded = []
				let totalSize = 0

				for(let i = 0; i < nbFiles; i++) {
					const size = inputFiles[i].size
					totalSize += size
					if(size > uploadMaxFileSize)
						maxSizeExceeded.push(inputFiles[i].name)
					formData.append("upload[]", inputFiles[i])
				}

				if(maxSizeExceeded.length > 0 || totalSize > uploadMaxTotalSize) {
					let txtErr = ""

					if(totalSize > uploadMaxTotalSize)
						txtErr = "Upload size exceeded<br><br>"

					for(let i = 0; i < maxSizeExceeded.length; i++)
						txtErr += "\n" + maxSizeExceeded[i] + "</b> is too big<b><br><br>"

					inputUpload.value = ""
					openBox("alert", "Error : <b>" + txtErr.substring(0, txtErr.length - 8) + "</b>", "err")
				}
				else {
					formData.append(Date.now(), "")
					formData.append("dir", dir)
					formData.append("exists", typeUploadExists)
					formData.append("token", token)

					ajaxRequest("FILES", "", formData, result => {
						inputUpload.value = ""
						if(result === "uploaded")
							openDir(currentPath)
						else {
							const found = result.match(/\[ask=([^\]]+)/)
							if(found)
								openBox("multi", { txt: "Error : <b>What to do when a file or a dir with the same name already exists ?</b>", inputs: "[button]Do nothing[button]Rename old[button]Rename new[button]Replace old[checkbox]Save choice" }, null, choices => {
									let choice = 0
									choices.forEach(choiceTmp => {
										if(choiceTmp !== 4)
											choice = choiceTmp
									})
									if(choices.indexOf(4) !== -1)
										typeUploadExists = choice + 1

									ajaxRequest("POST", "", `${Date.now()}&ask=${choice}&files=${found[1]}&dir=${dir}&token=${token}`, result => {
										if(result === "uploaded")
											openDir(currentPath)
										else {
											openDir(currentPath)
											openBox("alert", "Error : <b>" + result + "</b>", "err")
										}
									})
								})
							else
								openBox("alert", "Error : <b>" + result + "</b>", "err")
						}
					})
				}
			}
		}
	})
}

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

/* UPDATE */

ajaxRequest("GET", urlRawGithub, "", result => {
	const found = result.match(/define\('version_script', '([0-9])\.([0-9])\.([0-9])'\);/)
	if(found) {
		const found2 = scriptVersion.match(/([0-9])\.([0-9])\.([0-9])/)
		if(found2) {
			const vNew1 = parseInt(found[1], 10)
			const vNew2 = parseInt(found[2], 10)
			const vNew3 = parseInt(found[3], 10)
			const vThis1 = parseInt(found2[1], 10)
			const vThis2 = parseInt(found2[2], 10)
			const vThis3 = parseInt(found2[3], 10)
			if(
				(vNew1 > vThis1) ||
				(vNew1 === vThis1 && vNew2 > vThis2) ||
				(vNew1 === vThis1 && vNew2 === vThis2 && vNew3 > vThis3)
			) {
				wltrdrUpdate.querySelector("span").innerHTML = "&#8681;"
				wltrdrUpdate.querySelector("a").innerHTML= "<b>UPDATE AVAILABLE</b>"
				wltrdrUpdate.querySelector("a").removeAttribute("href")
				wltrdrUpdate.addEventListener("click", () => {
					openBox("confirm", `<p>Do you really want to update php_files_manager ?</p><br><p>Your version : <b>${vThis1}.${vThis2}.${vThis3}</b></p><br><p>Version available : <b>${vNew1}.${vNew2}.${vNew3}</b></p>`, null, () => {
						ajaxRequest("POST", "", `${Date.now()}&update=${encodeURIComponent(urlRawGithub)}&token=${token}`, result => {
							const found3 = result.match(/\[update=([^,]+)\|([^,]+)\|([^\]]+)\]/)
							if(found3)
								location.href = found3[3] + `?file=${found3[1]}&update=${found3[2]}&tmp=` + found3[3]
							else {
								openDir(currentPath, true)
								openBox("alert", "Error : <b>" + result + "</b>", "err")
							}
						})
					})
				})
			}
			else
				console.log("No Update available !")
		}
		else
			console.log("%cError : %cUnable to access script version", "color: red;", "color: auto;")
	}
	else
		console.log("%cError : %cUnable to access new script version", "color: red;", "color: auto;")

}, true, true)
