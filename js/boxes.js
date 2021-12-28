/* CONTEXT MENU */

function posMenu(event = false)
{
	const menuWidth = popupMenu.offsetWidth
	const menuHeight = popupMenu.offsetHeight

	if(event.clientX + menuWidth > window.innerWidth)
	{
		if(event.clientX - menuWidth < 0)
			popupMenu.style.left = "0px"
		else
			popupMenu.style.left = (event.clientX - menuWidth) + "px"
	}
	else
		popupMenu.style.left = event.clientX + "px"

	if(event.clientY + menuHeight > window.innerHeight)
	{
		if(event.clientY - menuHeight < 0)
			popupMenu.style.top = "0px"
		else
			popupMenu.style.top = (event.clientY - menuHeight) + "px"
	}
	else
		popupMenu.style.top = event.clientY + "px"
}

function openMenu(html, ev)
{
	popupMenu.style.display = "none"
	popupMenu.innerHTML = html
	setTimeout(() => {
		popupMenu.style.display = "flex"
		posMenu(ev)
		try {
			popupMenu.querySelector("span").addEventListener("click", () => {
				openMenu(html, ev)
			})
		}
		catch {}
	}, delayMenuMs)
}

/* POPUP BOX */

function closeBox()
{
	popupBox.innerHTML = ""
	popupMask.style.display = "none"
	popupBox.style.display = "none"
}

function showBox(txt, icon, inputs, buttons, noForm = true, callback = false)
{
	let html = `<div class="popupBox">
		<div class="n1">
			<div class="n3">
				<span class="icon ${icon}"></span>
				<span class="txt">${txt}</span>
			</div>
		</div>
		${inputs}
		<div class="n2">
			${buttons}
		</div>
	</div>`
	if(noForm !== true)
		html = "<form>\n" + html + "</form>"
	popupBox.innerHTML = html
	popupMask.style.display = "block"
	popupBox.style.display = "block"
	setTimeout(() => {
		if(callback !== false)
			callback()
	}, delayMenuMs)
}

function openBox(type, vals, icon = null, callback = false)
{
	setTimeout(() => {
		if(type === "alert")
		{
			if(icon === null)
				icon = "info"
			let txt = vals
			let btn = "Ok"
			if(typeof(vals) !== "string")
			{
				if(vals.txt)
					txt = vals.txt
				if(vals.btn)
					btn = vals.btn
			}
			showBox(txt, icon, `<input type="text" class="hidden" value="">`,  `<button>${btn}</button>`, false, () => {
				const input = popupBox.querySelector("input")
				input.focus()

				popupBox.querySelector("button").addEventListener("click", ev => {
					closeBox()
					ev.preventDefault()
				})
			})
		}
		else if(type === "confirm")
		{
			if(icon === null)
				icon = "ask"
			let txt = vals
			let btnOk = "Yes"
			let btnNo = "No"
			if(typeof(vals) !== "string")
			{
				if(vals.txt)
					txt = vals.txt
				if(vals.btnOk)
					btnOk = vals.btnOk
				if(vals.btnNo)
					btnNo = vals.btnNo
			}
			showBox(txt, icon, "", `<button id="y">${btnOk}</button>\n<button id="n">${btnNo}</button>`, true, () => {
				popupBox.querySelector("button#y").addEventListener("click", ev => {
					callback()
					closeBox()
					ev.preventDefault()
				})

				popupBox.querySelector("button#n").addEventListener("click", ev => {
					closeBox()
					ev.preventDefault()
				})
			})
		}
		else if(type === "prompt")
		{
			if(icon === null)
				icon = "ask"
			let txt = vals
			let value = ""
			let btnOk = "Ok"
			let btnNo = "Cancel"
			if(typeof(vals) !== "string")
			{
				if(vals.txt)
					txt = vals.txt
				if(vals.value)
					value = vals.value
				if(vals.btnOk)
					btnOk = vals.btnOk
				if(vals.btnNo)
					btnNo = vals.btnNo
			}
			showBox(txt, icon, `<input type="text" value="${value}">`, `<button id="y">${btnOk}</button>\n<button id="n">${btnNo}</button>`, false, () => {
				const input = popupBox.querySelector("input")
				input.focus()
				const tmp = input.value
				input.value = ""
				input.value = tmp

				popupBox.querySelector("button#y").addEventListener("click", ev => {
					callback(input.value)
					closeBox()
					ev.preventDefault()
				})

				popupBox.querySelector("button#n").addEventListener("click", ev => {
					closeBox()
					ev.preventDefault()
				})
			})
		}
		else if(type === "path")
		{
			if(icon === null)
				icon = "path"
			let txt = vals
			let btnOk = "Ok"
			let btnNo = "Cancel"
			if(typeof(vals) !== "string")
			{
				if(vals.txt)
					txt = vals.txt
				if(vals.btnOk)
					btnOk = vals.btnOk
				if(vals.btnNo)
					btnNo = vals.btnNo
			}
            let pathDecoded
            try {
                pathDecoded = decodeURIComponent(currentPath)
            }
            catch {
                pathDecoded = currentPath
            }
			ajaxRequest("POST", "", `${Date.now()}&dir=${currentPath}&tree_only`, result => {
				showBox(txt, icon, `<div id="boxPath"><div class="list">${result}</div></div><input type="text" id="pathDecoded" value="${pathDecoded}"><input type="hidden" id="pathEncoded" value="${currentPath}">`, `<button id="y">${btnOk}</button>\n<button id="n">${btnNo}</button>`, false, () => {
					try {
						const boxPath = document.querySelector("#boxPath")
						boxPath.scrollTop = boxPath.querySelector(".treeDefault").offsetTop - boxPath.querySelector(".list").offsetTop
					}
					catch {}

					const inputEncoded = popupBox.querySelector("input#pathEncoded")
					const inputDecoded = popupBox.querySelector("input#pathDecoded")

                    inputDecoded.addEventListener("input", () => {
                        try {
                            inputEncoded.value = encodeURIComponent(inputDecoded.value)
                        }
                        catch {
                            inputEncoded.value = inputDecoded.value
                        }
                    })

					popupBox.querySelector("button#y").addEventListener("click", ev => {
						callback(inputEncoded.value)
						closeBox()
						ev.preventDefault()
					})

					popupBox.querySelector("button#n").addEventListener("click", ev => {
						closeBox()
						ev.preventDefault()
					})
				})
			})
		}
		else if(type === "edit")
		{
			if(icon === null)
				icon = "edit"
			let txt = `Edit <b>ʿ${vals.name}ʿ</b> :`
			let btnOk = "Ok"
			let btnNo = "Cancel"
			if(vals.txt)
				txt = vals.txt
			if(vals.btnOk)
				btnOk = vals.btnOk
			if(vals.btnNo)
				btnNo = vals.btnNo
			ajaxRequest("POST", "", `${Date.now()}&read_file=${vals.nameEncoded}&dir=${currentPath}&token=${token}`, result => {
				if(result === "[file_edit_not_found]")
					openBox("alert", `Error : <b>File not found</b>`, "err")
				else
					showBox(txt, icon, `<textarea>${result}</textarea>`, `<button id="y">${btnOk}</button>\n<button id="n">${btnNo}</button>`, false, () => {
						const input = popupBox.querySelector("textarea")
						input.focus()
						const tmp = input.value
						input.value = ""
						input.value = tmp

						popupBox.querySelector("button#y").addEventListener("click", ev => {
							ajaxRequest("POST", "", `${Date.now()}&edit_file=${input.value}&dir=${currentPath}&name=${vals.nameEncoded}&token=${token}`, result => {
								if(result === "edited")
									openDir(currentPath)
								else
								{
									openDir(currentPath)
									openBox("alert", "Error : <b>" + result + "</b>", "err")
								}
							})
							closeBox()
							ev.preventDefault()
						})

						popupBox.querySelector("button#n").addEventListener("click", ev => {
							closeBox()
							ev.preventDefault()
						})
					})
			})
		}
		else if(type === "chmods")
		{
			if(icon === null)
				icon = "lock"
			let txt = `Change chmods for <b>ʿ${vals.name}ʿ</b> :`
			let btnOk = "Ok"
			let btnNo = "Cancel"
			if(vals.txt)
				txt = vals.txt
			if(vals.btnOk)
				btnOk = vals.btnOk
			if(vals.btnNo)
				btnNo = vals.btnNo
			ajaxRequest("POST", "", `${Date.now()}&get_chmods=${vals.nameEncoded}&dir=${currentPath}&token=${token}`, result => {
				const found = result.match(/\[chmods=([0-9]+)\]/)
				if(found)
				{
					function chmods2checkboxes(chmods, el, input)
					{
						chmods = chmods.toString()
						while(chmods.length < 4)
						{
							chmods = "0" + chmods
							input.value = chmods
						}

						const octs = []
						for(let i = 0; i < 4; i++)
						{
							let nb = parseInt(chmods[i], 10)
							let r = w = x = false
							if(nb >= 4)
							{
								r = true
								nb -= 4
							}
							if(nb >= 2)
							{
								w = true
								nb -= 2
							}
							if(nb == 1)
								x = true
							octs.push([r, w, x])
						}

						octs.forEach((oct, i) => {
							oct.forEach((val, j) => {
								if(val === true)
									el.querySelector(`#chmod_${i}_${j}`).checked = "checked"
								else
									el.querySelector(`#chmod_${i}_${j}`).checked = null
							})
						})
					}

					function checkboxes2chmods(el, input)
					{
						let chmods = ""
						for(let i = 0; i < 4; i++)
						{
							let val = 0
							if(el.querySelector(`#chmod_${i}_0`).checked !== false)
								val += 4
							if(el.querySelector(`#chmod_${i}_1`).checked !== false)
								val += 2
							if(el.querySelector(`#chmod_${i}_2`).checked !== false)
								val ++
							chmods += val.toString()
						}
						input.value = chmods
					}

					showBox(txt, icon, `<div id="boxChmods">
						<div></div>
						<div></div>
						<div></div>
						<div class="center">Owner :</div>
						<div class="center">Group :</div>
						<div class="center">Others :</div>

						<div><label for="chmod_0_0">Set UID :</label></div>
						<div><input type="checkbox" id="chmod_0_0"></div>
						<div>Read :</div>
						<div class="center"><input type="checkbox" id="chmod_1_0"></div>
						<div class="center"><input type="checkbox" id="chmod_2_0"></div>
						<div class="center"><input type="checkbox" id="chmod_3_0"></div>

						<div><label for="chmod_0_1">Set GID :</label></div>
						<div><input type="checkbox" id="chmod_0_1"></div>
						<div>Write :</div>
						<div class="center"><input type="checkbox" id="chmod_1_1"></div>
						<div class="center"><input type="checkbox" id="chmod_2_1"></div>
						<div class="center"><input type="checkbox" id="chmod_3_1"></div>

						<div><label for="chmod_0_2">Sticky bit :</label></div>
						<div><input type="checkbox" id="chmod_0_2"></div>
						<div>Execute :</div>
						<div class="center"><input type="checkbox" id="chmod_1_2"></div>
						<div class="center"><input type="checkbox" id="chmod_2_2"></div>
						<div class="center"><input type="checkbox" id="chmod_3_2"></div>
					</div>
					<input type="text" value="${found[1]}">
					`, `<button id="y">${btnOk}</button>\n<button id="n">${btnNo}</button>`, false, () => {
						const input = popupBox.querySelector("input[type=\"text\"]")

						chmods2checkboxes(found[1], popupBox, input)

						input.addEventListener("change", () => {
							chmods2checkboxes(input.value, popupBox, input)
						})

						popupBox.querySelectorAll("input[type=\"checkbox\"]").forEach(checkbox => {
							checkbox.addEventListener("click", () => {
								checkboxes2chmods(popupBox, input)
							})
						})

						popupBox.querySelector("button#y").addEventListener("click", ev => {
							ajaxRequest("POST", "", `${Date.now()}&set_chmods=${input.value}&dir=${currentPath}&name=${vals.nameEncoded}&token=${token}`, result => {
								if(result === "chmoded")
									openDir(currentPath)
								else
								{
									openDir(currentPath)
									openBox("alert", "Error : <b>" + result + "</b>", "err")
								}
							})
							closeBox()
							ev.preventDefault()
						})

						popupBox.querySelector("button#n").addEventListener("click", ev => {
							closeBox()
							ev.preventDefault()
						})
					})
				}
				else
					openBox("alert", `Error : <b>${result}</b>`, "err")
			})
		}
		else
		{
			alert("Error : Unknown type")
			return false
		}
	}, delayMenuMs)
}

function boxPathNavigate(dir)
{
	document.querySelector("#popupBox input#pathEncoded").value = dir
    const inputDecoded = document.querySelector("#popupBox input#pathDecoded")
    try {
        inputDecoded.value = decodeURIComponent(dir)
    }
    catch {
        inputDecoded.value = dir
    }
	ajaxRequest("POST", "", `${Date.now()}&dir=${dir}&tree_only`, result => {
		document.querySelector("#boxPath .list").innerHTML = result
	})
}
