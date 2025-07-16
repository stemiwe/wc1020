console.log('adding og button...')

let ogCheckbox = document.createElement('input');
ogCheckbox.type = 'checkbox';
ogCheckbox.id = 'only-regulars';
ogCheckbox.name = 'only-regulars';
ogCheckbox.checked = false; // Default to checked
ogCheckbox.addEventListener('change', function() {

    if (ogCheckbox.checked) {

    } else {

    }

});

document.getElementsByClassName('submenu')[0].appendChild(ogCheckbox);