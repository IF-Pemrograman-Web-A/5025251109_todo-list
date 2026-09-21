const form = document.querySelector('form');
const inpt = form.querySelectorAll('input');
const ul = document.querySelector('ul');
let dT = [];

form.addEventListener('submit', function(e) {
    e.preventDefault();

    const tB = {
        tgl: inpt[0].value,
        nama: inpt[1].value,
        jam: inpt[2].value,
        selesai: false
    };

    dT.push(tB);
    form.reset();

    const li = document.createElement('li');
    li.innerHTML = `
        <input type="checkbox">
        <span>${tB.tgl} | ${tB.nama} | ${tB.jam}</span>
        <button>Edit</button>
        <button>Delete</button>
    `;
    ul.appendChild(li);
});

ul.addEventListener('click', function(e) {
    if (e.target.innerText === 'Delete') {
        e.target.parentElement.remove();
    } 
    else if (e.target.innerText === 'Edit') {
        const li = e.target.parentElement;
        const span = li.querySelector('span');
        const teks = span.innerText.split(' | ');
        
        inpt[0].value = teks[0];
        inpt[1].value = teks[1];
        inpt[2].value = teks[2];
        
        li.remove();
    }
});

const btnTema = document.getElementById('btnTema');

btnTema.addEventListener('click', function() {
    document.body.classList.toggle('dark-mode');
});