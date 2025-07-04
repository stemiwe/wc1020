console.log('setting active menu items...')

// Set active menu items.
const pathname = window.location.pathname
const filename = pathname.split('/').pop()
const params = new URLSearchParams(window.location.search);
const time = params.get('time')

var parent = null
const menu = document.getElementsByClassName('menu-item')
for (let i = 0; i < menu.length; i++) {
    if (menu[i].href && menu[i].href.includes(filename)) {
        menu[i].classList.add('active')
        parent = menu[i].dataset.parent
    }
}

// Set parent menu item active.
if (parent) {
    let parentMenu = document.querySelector(`[data-id="${parent}"]`)
    if (parentMenu) {
        parentMenu.classList.add('active')
    }
}

// Set submenu item active.
const submenu = document.getElementsByClassName('submenu-item')
for (let i = 0; i < submenu.length; i++) {
    if (submenu[i].dataset.param == time) {
        submenu[i].classList.add('active')
    }
}