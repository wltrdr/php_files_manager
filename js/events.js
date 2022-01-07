document.body.addEventListener("click", () => {
	popupMenu.style.display = "none"
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
	const choices = ["Icons", "Small icons", "Details"]
	let html = ""

	choices.forEach((type, i) => {
		curView = ""
		if(typeView === i)
			curView = "&#8226; "
		html += `<a onclick="changeView(${typeView}, ${i})">${curView}${type}</a>\n`
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
	const choices = ["Ask", "Replace", "Rename old", "Rename new", "Do nothing"]
	let html = ""

	choices.forEach((type, i) => {
		curUpload = ""
		if(typeUploadExists === i)
			curUpload = "&#8226; "
		html += `<a onclick="typeUploadExists = ${i}">${curUpload}${type}</a>\n`
	})

	openMenu(`<a onclick="openBox('prompt', 'Enter a name for the new directory :', null, inputName => { newElement('dir', inputName) })">Create directory</a>
	<a onclick="openBox('prompt', 'Enter a name for the new file :', null, inputName => { newElement('file', inputName) })">Create file</a>
	<a onclick="inputUpload.click()">Upload file(s)</a>
	<span class="simple">If target exists :</span>
	${html}
	`, ev)
})

inputUpload.addEventListener("change", () => {
	getUploadSizes(result => {
		if(result === false)
			openBox("alert", "Error : <b>Cannot get server uploads limits</b>", "err")
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
					openBox("alert", "Error : <b>" + txtErr.substring(0, txtErr.length - 8) + "</b>", "err")
				}
				else
				{
					formData.append(Date.now(), "")
					formData.append("dir", currentPath)
					formData.append("exists", typeUploadExists)
					formData.append("token", token)

					ajaxRequest("FILES", "", formData, result => {
						inputUpload.value = ""
						if(result === "uploaded")
							openDir(currentPath)
						else
						{
							openDir(currentPath)
							openBox("alert", "Error : <b>" + result + "</b>", "err")
						}
					})
				}
			}
		}
	})
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
