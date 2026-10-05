const form = document.querySelector('form');
const inpt = form.querySelectorAll('input');
const ul = document.querySelector('ul');
const btnTema = document.getElementById('btnTema');

const video = document.getElementById('kamera');
const canvas = document.getElementById('kanvas');
const btnSnap = document.getElementById('btnSnap');
const hasilFoto = document.getElementById('hasilFoto');
let dataFotoBase64 = '';
let swReg = null;

if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('sw.js')
    .then(function(reg) {
        swReg = reg;
    })
    .catch(function(err) {
        console.log(err);
    });
}

if ('Notification' in window) {
    Notification.requestPermission();
}

navigator.mediaDevices.getUserMedia({ video: true })
    .then(function(stream) {
        video.srcObject = stream;
    })
    .catch(function(err) {
        console.log(err);
    });

btnSnap.addEventListener('click', function() {
    const ctx = canvas.getContext('2d');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    dataFotoBase64 = canvas.toDataURL('image/png');
    hasilFoto.src = dataFotoBase64;
    hasilFoto.style.display = 'block';
});

let temaAktif = localStorage.getItem('tema');

if (temaAktif === 'gelap') {
    document.body.classList.add('dark-mode');
}

btnTema.addEventListener('click', function() {
    document.body.classList.toggle('dark-mode');
    
    if (document.body.classList.contains('dark-mode')) {
        localStorage.setItem('tema', 'gelap');
    } else {
        localStorage.setItem('tema', 'terang');
    }
});

let db;
const request = indexedDB.open('TodoDatabase', 1);

request.onupgradeneeded = function(e) {
    db = e.target.result;
    db.createObjectStore('todos', { keyPath: 'id', autoIncrement: true });
};

request.onsuccess = function(e) {
    db = e.target.result;
    tampilData();
};

form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const tB = {
        tgl: inpt[0].value,
        nama: inpt[1].value,
        jam: inpt[2].value,
        jamNotif: inpt[3].value,
        foto: dataFotoBase64,
        selesai: false
    };

    jadwalkanNotifikasi(tB.tgl, tB.jamNotif, tB.nama);

    const transaction = db.transaction(['todos'], 'readwrite');
    const store = transaction.objectStore('todos');
    store.add(tB);

    transaction.oncomplete = function() {
        form.reset();
        dataFotoBase64 = '';
        hasilFoto.style.display = 'none';
        hasilFoto.src = '';
        tampilData();
    };
});

function jadwalkanNotifikasi(tanggal, jamNotif, namaTugas) {
    if (!swReg || Notification.permission !== 'granted') return;

    const waktuTugas = new Date(`${tanggal}T${jamNotif}:00`).getTime();
    const waktuSekarang = new Date().getTime();
    const selisih = waktuTugas - waktuSekarang;

    if (selisih > 0) {
        setTimeout(function() {
            swReg.showNotification('Task Reminder', {
                body: namaTugas
            });
        }, selisih);
    }
}

function tampilData() {
    ul.innerHTML = '';
    const transaction = db.transaction(['todos'], 'readonly');
    const store = transaction.objectStore('todos');
    const cursorRequest = store.openCursor();

    cursorRequest.onsuccess = function(e) {
        const cursor = e.target.result;
        if (cursor) {
            const li = document.createElement('li');
            li.setAttribute('data-id', cursor.value.id);
            
            let tagGambar = '';
            if (cursor.value.foto !== '') {
                tagGambar = `<img src="${cursor.value.foto}" style="max-height: 100px; display: block; margin: 10px 0;">`;
            }

            li.innerHTML = `
                <input type="checkbox">
                <span>${cursor.value.tgl} | ${cursor.value.nama} | ${cursor.value.jam} | Notif: ${cursor.value.jamNotif}</span>
                ${tagGambar}
                <button>Edit</button>
                <button>Delete</button>
            `;
            ul.appendChild(li);
            cursor.continue();
        }
    };
}

ul.addEventListener('click', function(e) {
    if (e.target.innerText === 'Delete') {
        const id = Number(e.target.parentElement.getAttribute('data-id'));
        const transaction = db.transaction(['todos'], 'readwrite');
        const store = transaction.objectStore('todos');
        
        store.delete(id);
        
        transaction.oncomplete = function() {
            tampilData();
        };
    } 
    else if (e.target.innerText === 'Edit') {
        const id = Number(e.target.parentElement.getAttribute('data-id'));
        const span = e.target.parentElement.querySelector('span');
        const teks = span.innerText.split(' | ');
        
        inpt[0].value = teks[0];
        inpt[1].value = teks[1];
        inpt[2].value = teks[2];
        inpt[3].value = teks[3].replace('Notif: ', '').trim();
        
        const transaction = db.transaction(['todos'], 'readwrite');
        const store = transaction.objectStore('todos');
        
        store.delete(id);
        
        transaction.oncomplete = function() {
            tampilData();
        };
    }
});