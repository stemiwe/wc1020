console.log('ois guad soweit...');

// Set active menu items.
const pathname = window.location.pathname;
const filename = pathname.split('/').pop();
var parent = null;
let menu = document.getElementsByClassName('menu tab');
for (let i = 0; i < menu.length; i++) {        
    if (menu[i].href && menu[i].href.includes(filename)) {        
        menu[i].classList.add('active');
        parent = menu[i].dataset.parent
    }
}
if (parent) {    
    let parentMenu = document.querySelector(`[data-id="${parent}"]`);
    if (parentMenu) {
        parentMenu.classList.add('active');
    }
}
