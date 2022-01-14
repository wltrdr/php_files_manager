/* CONTEXT MENU */

function posMenu(event = false) {
	const menuWidth = popupMenu.offsetWidth
	const menuHeight = popupMenu.offsetHeight

	if(event.clientX + menuWidth > window.innerWidth) {
		if(event.clientX - menuWidth < 0)
			popupMenu.style.left = "0px"
		else
			popupMenu.style.left = (event.clientX - menuWidth) + "px"
	}
	else
		popupMenu.style.left = event.clientX + "px"

	if(event.clientY + menuHeight > window.innerHeight) {
		if(event.clientY - menuHeight < 0)
			popupMenu.style.top = "0px"
		else
			popupMenu.style.top = (event.clientY - menuHeight) + "px"
	}
	else
		popupMenu.style.top = event.clientY + "px"
}

function openMenu(html, ev) {
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

function closeBox() {
	popupBox.innerHTML = ""
	popupMask.style.display = "none"
	popupBox.style.display = "none"
}

function showBox(txt, icon, inputs, buttons, noForm = true, callback = false) {
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

function openBox(type, vals, icon = null, callback = false) {
	setTimeout(() => {
		if(type === "alert") {
			if(icon === null)
				icon = "info"
			let txt = vals
			let btn = "Ok"
			if(typeof(vals) !== "string") {
				if(vals.txt)
					txt = vals.txt
				if(vals.btn)
					btn = vals.btn
			}
			showBox(txt, icon, `<input type="text" class="hidden" value="">`, `<button>${btn}</button>`, false, () => {
				const input = popupBox.querySelector("input")
				input.focus()

				popupBox.querySelector("button").addEventListener("click", ev => {
					if(callback !== false)
						callback()
					closeBox()
					ev.preventDefault()
				})
			})
		}
		else if(type === "confirm") {
			if(icon === null)
				icon = "ask"
			let txt = vals
			let btnOk = "Yes"
			let btnNo = "No"
			if(typeof(vals) !== "string") {
				if(vals.txt)
					txt = vals.txt
				if(vals.btnOk)
					btnOk = vals.btnOk
				if(vals.btnNo)
					btnNo = vals.btnNo
			}
			showBox(txt, icon, "", `<button id="y">${btnOk}</button>\n<button id="n">${btnNo}</button>`, true, () => {
				popupBox.querySelector("button#y").addEventListener("click", ev => {
					if(callback !== false)
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
		else if(type === "prompt") {
			if(icon === null)
				icon = "ask"
			let txt = vals
			let value = ""
			let btnOk = "Ok"
			let btnNo = "Cancel"
			if(typeof(vals) !== "string") {
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
					if(callback !== false)
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
		else if(type === "multi") {
			if(icon === null)
				icon = "ask"
			let txt = ""
			let listInputs = vals
			if(typeof(vals) !== "string") {
				if(vals.txt)
					txt = vals.txt
				if(vals.inputs)
					listInputs = vals.inputs
			}

			let inputsHTML = ""
			const founds = [...listInputs.matchAll(/\[([^\]]+)\]([^\[]+)/g)]
			founds.forEach((found, i) => {
				if(found[1] === "checkbox")
					inputsHTML += `<br>\n<label><input type="checkbox" value="${i}" style="display: inline-block; width: auto; min-width: auto;"> &nbsp; ${found[2]}</label>`
				else
					inputsHTML += `<br>\n<button value="${i}">${found[2]}</button>`
			})
			showBox(txt, icon, inputsHTML, ``, true, () => {

				const listValues = []

				popupBox.querySelectorAll("input").forEach(checkbox => {
					checkbox.addEventListener("click", () => {
						if(checkbox.checked)
							listValues.push(parseInt(checkbox.value, 10))
						else {
							const posCheckbox = listValues.indexOf(parseInt(checkbox.value, 10))
							if(posCheckbox !== -1)
								listValues.splice(posCheckbox, 1)
						}
					})
				})

				popupBox.querySelectorAll("button").forEach(button => {
					button.addEventListener("click", ev => {
						listValues.push(parseInt(button.value, 10))
						if(callback !== false)
							callback(listValues)
						closeBox()
						ev.preventDefault()
					})
				})
			})
		}
		else if(type === "path") {
			if(icon === null)
				icon = "path"
			let txt = vals
			let btnOk = "Ok"
			let btnNo = "Cancel"
			if(typeof(vals) !== "string") {
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
				const found = result.match(/^(.*)\/\/!tree!\\\\(.*)\n\/\/!end!\\\\(.*)$/s)
				if(found) {
					if(found[1] || found[3])
						console.log(`%cPHP Errors :\n\n%c${found[1].replace(/<[^>]+>/g, "")}\n\n${found[3].replace(/<[^>]+>/g, "")}`, "font-size: 2em; color: red;", "font-size: 1em; color: auto;")

					result = found[2]
				}
				else
					result = "Error : <b>Try to refresh site</b>"
				showBox(txt, icon, `<div id="boxPath"><div class="list">${result}</div></div><input type="text" id="pathDecoded" value="${pathDecoded}"><div id="boxPathNewDirectory"><input type="text" id="boxPathNameNewDirectory" placeholder="Name of the new directory"><button id="boxPathCreateNewDirectory">Create</button></div><input type="hidden" id="pathEncoded" value="${currentPath}">`, `<button id="y">${btnOk}</button>\n<button id="n">${btnNo}</button>\n<button id="c">Create sub-directory</button>`, false, () => {
					try {
						const boxPath = document.querySelector("#boxPath")
						boxPath.scrollTop = boxPath.querySelector(".treeDefault").offsetTop - boxPath.querySelector(".list").offsetTop
					}
					catch {}

					const inputEncoded = popupBox.querySelector("input#pathEncoded")
					const inputDecoded = popupBox.querySelector("input#pathDecoded")
					const boxPathNewDirectory = popupBox.querySelector("#boxPathNewDirectory")
					const boxPathNameNewDirectory = popupBox.querySelector("#boxPathNameNewDirectory")
					const boxPathCreateNewDirectory = popupBox.querySelector("#boxPathCreateNewDirectory")

					inputDecoded.addEventListener("input", () => {
						try {
							inputEncoded.value = encodeURIComponent(inputDecoded.value)
						}
						catch {
							inputEncoded.value = inputDecoded.value
						}
					})

					popupBox.querySelector("button#c").addEventListener("click", ev => {
						boxPathNewDirectory.style.display = "flex"
						boxPathNameNewDirectory.focus()
						ev.preventDefault()
					})

					boxPathCreateNewDirectory.addEventListener("click", ev => {
						ajaxRequest("POST", "", `${Date.now()}&new=dir&dir=${inputEncoded.value}&name=${boxPathNameNewDirectory.value}&token=${token}`, result => {
							if(result === "created") {
								boxPathNameNewDirectory.value = ""
								boxPathNavigate(currentPath)
							}
							else
								console.log("%cError : %c" + result, "color: red;", "color: auto;")
						})
						ev.preventDefault()
					})

					popupBox.querySelector("button#y").addEventListener("click", ev => {
						if(callback !== false)
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
		else if(type === "edit") {
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
							ajaxRequest("POST", "", `${Date.now()}&edit_file=${encodeURIComponent(input.value)}&dir=${currentPath}&name=${vals.nameEncoded}&token=${token}`, result => {
								if(result === "edited")
									openDir(currentPath, true, true)
								else {
									openDir(currentPath, true, true)
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
		else if(type === "chmods") {
			if(icon === null)
				icon = "lock"
			let txt = `Change chmods for <b>ʿ${vals.name}ʿ</b> :`
			let files = false
			let btnOk = "Ok"
			let btnNo = "Cancel"
			if(vals.txt)
				txt = vals.txt
			if(vals.files)
				files = JSON.parse(decodeURIComponent(vals.files))
			if(vals.btnOk)
				btnOk = vals.btnOk
			if(vals.btnNo)
				btnNo = vals.btnNo

			function chmods2checkboxes(chmods, el, input) {
				chmods = chmods.toString()
				while(chmods.length < 4) {
					chmods = "0" + chmods
					input.value = chmods
				}
				const octs = []
				for(let i = 0; i < 4; i++) {
					let nb = parseInt(chmods[i], 10)
					let r = w = x = false
					if(nb >= 4) {
						r = true
						nb -= 4
					}
					if(nb >= 2) {
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

			function checkboxes2chmods(el, input) {
				let chmods = ""
				for(let i = 0; i < 4; i++) {
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

			function showChmodBox(chmodDef, callback) {
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
				<input type="text" value="${chmodDef}">
				`, `<button id="y">${btnOk}</button>\n<button id="n">${btnNo}</button>`, false, () => {
					const input = popupBox.querySelector("input[type=\"text\"]")

					chmods2checkboxes(chmodDef, popupBox, input)

					input.addEventListener("change", () => {
						chmods2checkboxes(input.value, popupBox, input)
					})

					popupBox.querySelectorAll("input[type=\"checkbox\"]").forEach(checkbox => {
						checkbox.addEventListener("click", () => {
							checkboxes2chmods(popupBox, input)
						})
					})

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

			if(files !== false) {
				showChmodBox("0777", changeResult => {
					checkReqRep(`${Date.now()}&set_multiple_chmods=${changeResult}&files=${formatMultiple(files)}&token=${token}`, "chmodeds")
				})
			}
			else {
				ajaxRequest("POST", "", `${Date.now()}&get_chmods=${vals.nameEncoded}&dir=${currentPath}&token=${token}`, result => {
					const found = result.match(/\[chmods=([0-9]+)\]/)
					if(found) {
						showChmodBox(found[1], changeResult => {
							checkReqRep(`${Date.now()}&set_chmods=${changeResult}&dir=${currentPath}&name=${vals.nameEncoded}&token=${token}`, "chmoded")
						})
					}
					else
						openBox("alert", `Error : <b>${result}</b>`, "err")
				})
			}
		}
		else {
			alert("Error : Unknown type")
			return false
		}
	}, delayMenuMs)
}

function boxPathNavigate(dir) {
	document.querySelector("#popupBox input#pathEncoded").value = dir
	const inputDecoded = document.querySelector("#popupBox input#pathDecoded")
	try {
		inputDecoded.value = decodeURIComponent(dir)
	}
	catch {
		inputDecoded.value = dir
	}
	ajaxRequest("POST", "", `${Date.now()}&dir=${dir}&tree_only`, result => {
		const found = result.match(/^(.*)\/\/!tree!\\\\(.*)\n\/\/!end!\\\\(.*)$/s)
		if(found) {
			if(found[1] || found[3])
				console.log(`%cPHP Errors :\n\n%c${found[1].replace(/<[^>]+>/g, "")}\n\n${found[3].replace(/<[^>]+>/g, "")}`, "font-size: 2em; color: red;", "font-size: 1em; color: auto;")

			result = found[2]
		}
		else
			result = "Error : <b>Try to refresh site</b>"
		document.querySelector("#boxPath .list").innerHTML = result
	})
}
