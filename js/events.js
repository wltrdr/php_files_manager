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
							const found = result.match(/\[ask=([^\]]+)/)
							if(found)
								openBox("multi", { txt: "Error : <b>What to do when a file or a dir with the same name already exists ?</b>", inputs: "[button]Replace olds[button]Rename olds[button]Rename news[button]Do nothing[checkbox]Save choice" }, null, choices => {
									let choice = 0
									choices.forEach(choiceTmp => {
										if(choiceTmp !== 4)
											choice = choiceTmp
									})
									if(choices.indexOf(4) !== -1)
										typeUploadExists = choice + 1

									ajaxRequest("POST", "", `${Date.now()}&ask=${choice}&files=${found[1]}&dir=${currentPath}&token=${token}`, result => {
										if(result === "uploaded")
											openDir(currentPath)
										else
										{
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

/* UPDATE */

ajaxRequest("GET", urlRawGithub, "", result => {
	const found = result.match(/define\('version_script', '([0-9])\.([0-9])\.([0-9])'\);/)
	if(found)
	{
		const found2 = scriptVersion.match(/([0-9])\.([0-9])\.([0-9])/)
		if(found2)
		{
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
						ajaxRequest("POST", "", `${Date.now()}&update=${encodeURIComponent(urlRawGithub)}&dir=${currentPath}&token=${token}`, result => {
							// update recupere le nom du fichier
							// update telecharge le nouveau et lui donne un nom temporaire
							// update cree un fichier temp.php :
							// update renvoie [update=nom.php,nomTmpNouveau.php,temp.php]
							// js ouvre temp.php?nom=nom.php&temp=nomTmpNouveau.php
							// temp.php supprime nom.php
							// temp renomme nomTmpNouveau.php en nom.php
							// temp redirige vers nom.php puis se supprime
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
