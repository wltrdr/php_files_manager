function ajaxRequest(method, url, data, callback = false, disableLoading = false, disableBadRequest = false) {
	if(disableLoading === false) {
		onLoading = true
		willBeOnLoading = true
		setTimeout(() => {
			if(onLoading === true)
				loading.style.display = "block"
		}, delayLoadingMs)
	}

	const httpRequest = new XMLHttpRequest()
	if(!httpRequest) {
		alert("Error : Cannot create instance of XMLHTTP")
		return false
	}
	httpRequest.onreadystatechange = function() {
		if(httpRequest.readyState === XMLHttpRequest.DONE) {
			if(disableLoading === false) {
				onLoading = false
				willBeOnLoading = false
				loading.style.display = "none"
			}

			if(httpRequest.status === 200 && callback !== false)
				callback(httpRequest.responseText)
			else if(httpRequest.status !== 200 && disableBadRequest === false)
				alert("Error : Bad request")
		}
	}
	if(method === "POST") {
		httpRequest.open("POST", url)
		httpRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded")
		httpRequest.send(data)
	}
	else if(method === "FILES") {
		httpRequest.open("POST", url)
		httpRequest.send(data)
	}
	else {
		httpRequest.open(method, url + "?" + data)
		httpRequest.send()
	}
}

/* EXPLORER */

function showElements(result, disableFocus = false) {
	const found = result.match(/^(.*)\/\/!token!\\\\(.*)\n\/\/!current!\\\\(.*)\n\/\/!parent!\\\\(.*)\n\/\/!path!\\\\(.*)\n\/\/!tree!\\\\(.*)\n\/\/!elements!\\\\(.*)\n\/\/!order!\\\\(.*)\n\/\/!desc!\\\\(.*)\n\/\/!end!\\\\(.*)$/s)
	if(found) {
		if(found[1] || found[10])
			console.log(`%cPHP Errors :\n\n%c${found[1].replace(/<[^>]+>/g, "")}\n\n${found[10].replace(/<[^>]+>/g, "")}`, "font-size: 2em; color: red;", "font-size: 1em; color: auto;")

		const scrollTree = tree.scrollTop
		const scrollElems = elements.scrollTop

		connexion.style.display = "none"
		contents.style.display = "flex"
		token = found[2]
		currentPath = found[3]
		parentPath = found[4]
		path.innerHTML = found[5]
		listTree.innerHTML = found[6]
		listElements.innerHTML = found[7]
		typeOrder = parseInt(found[8], 10)
		typeOrderDesc = parseInt(found[9], 10)
		if(parentPath === "false")
			btnParent.className = "disabled"
		else
			btnParent.className = ""
		if(disableFocus === true) {
			tree.scrollTop = scrollTree
			elements.scrollTop = scrollElems
		}
		else {
			try {
				tree.scrollTop = document.querySelector(".treeDefault").offsetTop - (listTree.offsetTop + parseInt(window.getComputedStyle(document.querySelector(".treeFirst"), null).getPropertyValue("margin-top"), 10))
			}
			catch {
				console.log("%cError : %cUnable to access parent", "color: red;", "color: auto;")
			}
			elements.scrollTop = 0
		}
	}
	else {
		const fatal = result.match(/(.*)\[fatal=([^\]]+)\](.*)/s)
		if(fatal) {
			if(fatal[1] || fatal[3])
				console.log(`%cPHP Errors :\n\n%c${fatal[1].replace(/<[^>]+>/g, "")}\n\n${fatal[3].replace(/<[^>]+>/g, "")}`, "font-size: 2em; color: red;", "font-size: 1em; color: auto;")
			alert("Error : " + fatal[2])
		}
		else {
			alert("Error : Bad regex")
			console.log(result)
		}
	}
}

function openDir(dir, disableFocus = false, disableLoading = false, order = "", desc = "") {
	unselectElements()
	timeDirOpened = Date.now()
	if(order !== "")
		order = "&order=" + order
	if(desc !== "") {
		if(desc === false)
			desc = "&desc=0"
		else
			desc = "&desc=1"
	}
	ajaxRequest("POST", "", `${Date.now()}&dir=${dir}${order}${desc}`, result => {
		if(result !== "false") {
			showElements(result, disableFocus)
			let nbHistory = history.length
			if(nbHistory === 0) {
				history.push(dir)
				btnForward.className = "disabled"
				btnBack.className = "disabled"
				if(history.length > historyMax)
					history.splice(0, 1)
			}
			else {
				if(dir !== history[nbHistory - historyLevel - 1]) /* ISN’T A REFRESH */ {
					if(historyLevel > 0) {
						for(let i = 0; i < historyLevel; i++) {
							history.splice(nbHistory - 1, 1)
							nbHistory--
						}
					}
					history.push(dir)
					historyLevel = 0
					btnForward.className = "disabled"
					btnBack.className = ""
					if(history.length > historyMax)
						history.splice(0, 1)
				}
			}
		}
		else {
			token = ""
			contents.style.display = "none"
			connexion.style.display = "flex"
		}
	}, disableLoading)
}

openDir(currentPath)

setInterval(() => {
	if(disableAutoRefresh === false && timeDirOpened < Date.now() - BtwRefreshesMs)
		openDir(currentPath, true, true)
}, checkIntervMs)

/* SET SETTINGS */

function changeView(oldView, newView, doRequest = false) {
	typeView = newView
	if(oldView !== newView) {
		if(oldView !== 0)
			elements.classList.remove("view" + oldView)
		elements.classList.add("view" + newView)
	}
	if(doRequest !== false)
		ajaxRequest("POST", "", `${Date.now()}&set_settings=true&view=${typeView}&token=${token}`, false, true)
}

function changeTypeUploadExists(type, doRequest = false) {
	typeUploadExists = type
	if(doRequest !== false)
		ajaxRequest("POST", "", `${Date.now()}&set_settings=true&upload_exists=${type}&token=${token}`, false, true)
}

function changeTypeCopyMoveExists(type, doRequest = false) {
	typeCopyMoveExists = type
	if(doRequest !== false)
		ajaxRequest("POST", "", `${Date.now()}&set_settings=true&copy_move_exists=${type}&token=${token}`, false, true)
}

/* GET SETTINGS */

ajaxRequest("GET", "", `${Date.now()}&get_settings=true`, result => {
	const foundView = result.match(/\[view=([0-9])\]/)
	if(foundView)
		changeView(typeView, parseInt(foundView[1], 10))

	const foundUploadExists = result.match(/\[upload_exists=([0-9])\]/)
	if(foundUploadExists)
		typeUploadExists = parseInt(foundUploadExists[1], 10)

	const foundCopyMoveExists = result.match(/\[copy_move_exists=([0-9])\]/)
	if(foundCopyMoveExists)
		typeCopyMoveExists = parseInt(foundCopyMoveExists[1], 10)
}, true)

/* GET UPLOAD SIZES */

function getUploadSizes(callback = false) {
	if(uploadMaxFileSize === 0 || uploadMaxTotalSize === 0) {
		ajaxRequest("GET", "", `${Date.now()}&get_upload_sizes`, result => {
			const found = result.match(/\[max_upload_sizes=([0-9]+)\|([0-9]+)\]/)
			if(found) {
				uploadMaxFileSize = parseInt(found[1], 10)
				uploadMaxTotalSize = parseInt(found[2], 10)
				if(callback !== false) {
					if(uploadMaxFileSize === 0 || uploadMaxTotalSize === 0)
						callback(false)
					else
						callback(true)
				}
			}
			else {
				console.log("%cError : %cUnable to access upload sizes", "color: red;", "color: auto;")
				if(callback !== false)
					callback(false)
			}
		})
	}
	else if(callback !== false)
		callback(true)
}

getUploadSizes()

/* OTHERS FUNCTIONS */

function returnObjInArr(arr, val, param, returnBoolean = false, insensible = false)
{
    if(insensible === true)
        val = val.toLowerCase()
    let ret = false
    arr.forEach(el => {
        let retTmp = true
        if(returnBoolean === false)
            retTmp = el
        if((insensible === true && el[param].toLowerCase() === val) || (insensible !== true && el[param] === val))
            ret = retTmp
    })
    return ret
}

function removeObjsInArr(arr, val, param, insensible = false)
{
    if(insensible === true)
        val = val.toLowerCase()
    for(let i = 0; i < arr.length; i++)
    {
        if((insensible === true && arr[i][param].toLowerCase() === val) || (insensible !== true && arr[i][param] === val))
        {
            arr.splice(i, 1)
            i--
        }
    }
}

function checkReqRep(request, wish) {
	ajaxRequest("POST", "", request, result => {
		if(result === wish)
			openDir(currentPath, disableFocus)
		else {
			openDir(currentPath, disableFocus)
			openBox("alert", "Error : <b>" + result + "</b>", "err")
		}
	})
}
