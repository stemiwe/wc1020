console.log('soweit warma...');

// Menu.
let menu = document.getElementsByClassName('menu');
for (let i = 0; i < menu.length; i++) {    
    if (menu[i].href == window.location.href) {
        menu[i].classList.add('active');
    }
}