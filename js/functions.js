function ajaxRequest(method, url, data, callback, disableLoading = false)
{
	if(disableLoading === false)
	{
		onLoading = true
		willBeOnLoading = true
		setTimeout(() => {
			if(onLoading === true)
				loading.style.display = "block"
		}, delayLoadingMs)
	}

	const httpRequest = new XMLHttpRequest()
	if(!httpRequest)
	{
		alert("Error : Cannot create instance of XMLHTTP")
		return false
	}
	httpRequest.onreadystatechange = function() {
		if(httpRequest.readyState === XMLHttpRequest.DONE)
		{
			if(disableLoading === false)
			{
				onLoading = false
				willBeOnLoading = false
				loading.style.display = "none"
			}

			if(httpRequest.status === 200)
				callback(httpRequest.responseText)
			else
				alert("Error : Bad request")
		}
	}
	if(method === "POST")
	{
		httpRequest.open("POST", url)
		httpRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded")
		httpRequest.send(data)
	}
	else if(method === "FILES")
	{
		httpRequest.open("POST", url)
		httpRequest.send(data)
	}
	else
	{
		httpRequest.open(method, url + "?" + data)
		httpRequest.send()
	}
}

/* EXPLORER */

function showElements(result, disableFocus = false)
{
	const found = result.match(/(.*)\/\/!token!\\\\(.*)\n\/\/!current!\\\\(.*)\n\/\/!parent!\\\\(.*)\n\/\/!path!\\\\(.*)\n\/\/!tree!\\\\(.*)\n\/\/!elements!\\\\(.*)\n\/\/!order!\\\\(.*)\n\/\/!desc!\\\\(.*)\n\/\/!end!\\\\(.*)/s)
	if(found)
	{
		if(found[1] || found[10])
			console.log(`%cPHP Errors :\n\n%c${found[1].replace(/<[^>]+>/g, "")}\n\n${found[10].replace(/<[^>]+>/g, "")}`, "font-size: 2em; color: red;", "font-size: 1em; color: auto;")

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
		if(disableFocus === false)
		{
			try {
				tree.scrollTop = document.querySelector(".treeDefault").offsetTop - (listTree.offsetTop + parseInt(window.getComputedStyle(document.querySelector(".treeFirst"), null).getPropertyValue("margin-top"), 10))
			}
			catch {
				console.log("%cError : %cUnable to access parent", "color: red;", "color: auto;")
			}
		}
		elements.scrollTop = 0
	}
	else
	{
		const fatal = result.match(/(.*)\[fatal=([^\]]+)\](.*)/s)
		if(fatal)
		{
			if(fatal[1] || fatal[3])
				console.log(`%cPHP Errors :\n\n%c${fatal[1].replace(/<[^>]+>/g, "")}\n\n${fatal[3].replace(/<[^>]+>/g, "")}`, "font-size: 2em; color: red;", "font-size: 1em; color: auto;")
			alert("Error : " + fatal[2])
		}
		else
		{
			alert("Error : Bad regex")
			console.log(result)
		}
	}
}

function openDir(dir, disableFocus = false, disableLoading = false, order = "", desc = "")
{
	timeDirOpened = Date.now()
	if(order !== "")
		order = "&order=" + order
	if(desc !== "")
	{
		if(desc === false)
			desc = "&desc=0"
		else
			desc = "&desc=1"
	}
	ajaxRequest("POST", "", `${Date.now()}&dir=${dir}${order}${desc}`, result => {
		if(result !== "false")
		{
			showElements(result, disableFocus)
			let nbHistory = history.length
			if(nbHistory === 0)
			{
				history.push(dir)
				btnForward.className = "disabled"
				btnBack.className = "disabled"
				if(history.length > historyMax)
					history.splice(0, 1)
			}
			else
			{
				if(dir !== history[nbHistory - historyLevel - 1]) // ISN'T A REFRESH
				{
					if(historyLevel > 0)
					{
						for(let i = 0; i < historyLevel; i++)
						{
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
		else
		{
			token = ""
			contents.style.display = "none"
			connexion.style.display = "flex"
		}
	}, disableLoading)
}

openDir(currentPath)

setInterval(() => {
	if(timeDirOpened < Date.now() - BtwRefreshesMs)
		openDir(currentPath, true, true)
}, checkIntervMs)

function changeView(oldView, newView)
{
	if(oldView !== newView)
	{
		typeView = newView
		if(oldView !== 0)
			elements.classList.remove("view" + oldView)
		elements.classList.add("view" + newView)
	}
}
