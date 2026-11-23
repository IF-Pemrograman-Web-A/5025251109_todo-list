const btnTema = document.getElementById('btnTema');
const btnInstall = document.getElementById('btnInstall');
const video = document.getElementById('kamera');
const canvas = document.getElementById('kanvas');
const btnSnap = document.getElementById('btnSnap');
const hasilFoto = document.getElementById('hasilFoto');
const API_URL = 'api.php';
const API_KEY = 'todo-secret-key-2026';
let dataFotoBase64 = '';
let swReg = null;
let deferredPrompt = null;

if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('sw.js')
    .then(function(reg) {
        swReg = reg;
    })
    .catch(function(err) {
        console.log(err);
    });
}

window.addEventListener('beforeinstallprompt', function(e) {
    e.preventDefault();
    deferredPrompt = e;
    if (btnInstall) {
        btnInstall.style.display = 'inline-block';
    }
});

if (btnInstall) {
    btnInstall.addEventListener('click', async function() {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        await deferredPrompt.userChoice;
        deferredPrompt = null;
        btnInstall.style.display = 'none';
    });
}

window.addEventListener('appinstalled', function() {
    deferredPrompt = null;
    if (btnInstall) {
        btnInstall.style.display = 'none';
    }
});

if ('Notification' in window) {
    Notification.requestPermission();
}

if (video) {
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(function(stream) {
            video.srcObject = stream;
        })
        .catch(function(err) {
            console.log(err);
        });
}

if (btnSnap) {
    btnSnap.addEventListener('click', function() {
        const ctx = canvas.getContext('2d');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        dataFotoBase64 = canvas.toDataURL('image/png');
        hasilFoto.src = dataFotoBase64;
        hasilFoto.style.display = 'block';
    });
}

let temaAktif = localStorage.getItem('tema');

if (temaAktif === 'gelap') {
    document.body.classList.add('dark-mode');
}

if (btnTema) {
    btnTema.addEventListener('click', function() {
        document.body.classList.toggle('dark-mode');

        if (document.body.classList.contains('dark-mode')) {
            localStorage.setItem('tema', 'gelap');
        } else {
            localStorage.setItem('tema', 'terang');
        }
    });
}

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

const form = document.getElementById('personalForm');
const ul = document.getElementById('personalList');

if (form && ul) {
    const inpt = form.querySelectorAll('input');
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
        } else if (e.target.innerText === 'Edit') {
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
}

const sharedForm = document.getElementById('sharedForm');
const sharedList = document.getElementById('sharedList');
const apiStatus = document.getElementById('apiStatus');
const sharedFormTitle = document.getElementById('sharedFormTitle');
const editIdInput = document.getElementById('editId');
const sharedTgl = document.getElementById('sharedTgl');
const sharedNama = document.getElementById('sharedNama');
const sharedJam = document.getElementById('sharedJam');
const sharedJamNotif = document.getElementById('sharedJamNotif');
const sharedFile = document.getElementById('sharedFile');
const btnSharedSubmit = document.getElementById('btnSharedSubmit');
const btnCancelEdit = document.getElementById('btnCancelEdit');
let sharedCache = [];

function showStatus(msg, isOk) {
    if (!apiStatus) return;
    apiStatus.textContent = msg;
    apiStatus.className = isOk ? 'api-status ok' : 'api-status err';
    apiStatus.style.display = 'block';
}

function resetSharedForm() {
    if (!sharedForm) return;
    sharedForm.reset();
    editIdInput.value = '';
    sharedFormTitle.textContent = 'Add Task';
    btnSharedSubmit.textContent = 'Save';
    btnCancelEdit.style.display = 'none';
    dataFotoBase64 = '';
    if (hasilFoto) {
        hasilFoto.style.display = 'none';
        hasilFoto.src = '';
    }
}

function fileToBase64(file) {
    return new Promise(function(resolve, reject) {
        const reader = new FileReader();
        reader.onload = function() {
            resolve(reader.result);
        };
        reader.onerror = function(err) {
            reject(err);
        };
        reader.readAsDataURL(file);
    });
}

async function loadSharedTodos() {
    if (!sharedList) return;
    try {
        const res = await fetch(API_URL, {
            method: 'GET',
            headers: {
                'X-API-Key': API_KEY
            }
        });
        const body = await res.json();
        if (!res.ok) {
            showStatus(`[${res.status}] ${body.error || 'Failed to load data'}`, false);
            return;
        }
        sharedCache = body.data || [];
        renderSharedTodos();
    } catch (err) {
        showStatus('Network error while connecting to REST API', false);
    }
}

function renderSharedTodos() {
    sharedList.innerHTML = '';
    let i = 0;
    for (i = 0; i < sharedCache.length; i++) {
        const item = sharedCache[i];
        const li = document.createElement('li');
        if (Number(item.selesai) === 1) {
            li.classList.add('completed');
        }

        const cb = document.createElement('input');
        cb.type = 'checkbox';
        cb.checked = Number(item.selesai) === 1;
        cb.addEventListener('change', function() {
            toggleSharedTodo(item.id, cb.checked ? 1 : 0);
        });

        const span = document.createElement('span');
        const jamStr = String(item.jam).slice(0, 5);
        const notifStr = String(item.jam_notif).slice(0, 5);
        span.textContent = ` ${item.tgl} | ${item.nama} | ${jamStr} | Notif: ${notifStr} `;

        li.appendChild(cb);
        li.appendChild(span);

        if (item.foto && item.foto !== '') {
            const img = document.createElement('img');
            img.src = item.foto;
            img.style.maxHeight = '100px';
            img.style.display = 'block';
            img.style.margin = '10px 0';
            li.appendChild(img);
        }

        const btnEdit = document.createElement('button');
        btnEdit.type = 'button';
        btnEdit.textContent = 'Edit';
        btnEdit.style.marginRight = '6px';
        btnEdit.addEventListener('click', function() {
            startEditShared(item);
        });

        const btnDel = document.createElement('button');
        btnDel.type = 'button';
        btnDel.textContent = 'Delete';
        btnDel.addEventListener('click', function() {
            deleteSharedTodo(item.id);
        });

        li.appendChild(btnEdit);
        li.appendChild(btnDel);
        sharedList.appendChild(li);
    }
}

function startEditShared(item) {
    editIdInput.value = item.id;
    sharedTgl.value = item.tgl;
    sharedNama.value = item.nama;
    sharedJam.value = String(item.jam).slice(0, 5);
    sharedJamNotif.value = String(item.jam_notif).slice(0, 5);
    sharedFormTitle.textContent = 'Edit Task';
    btnSharedSubmit.textContent = 'Update';
    btnCancelEdit.style.display = 'inline-block';
    dataFotoBase64 = '';
    if (item.foto && item.foto !== '') {
        hasilFoto.src = item.foto;
        hasilFoto.style.display = 'block';
    } else {
        hasilFoto.style.display = 'none';
        hasilFoto.src = '';
    }
}

async function toggleSharedTodo(id, selesaiVal) {
    try {
        const res = await fetch(`${API_URL}?id=${id}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-API-Key': API_KEY
            },
            body: JSON.stringify({ selesai: selesaiVal })
        });
        const body = await res.json();
        if (!res.ok) {
            showStatus(`[${res.status}] ${body.error || 'PATCH failed'}`, false);
            return;
        }
        showStatus(`[${res.status}] ${body.message}`, true);
        loadSharedTodos();
    } catch (err) {
        showStatus('Network error during PATCH request', false);
    }
}

async function deleteSharedTodo(id) {
    try {
        const res = await fetch(`${API_URL}?id=${id}`, {
            method: 'DELETE',
            headers: {
                'X-API-Key': API_KEY
            }
        });
        const body = await res.json();
        if (!res.ok) {
            showStatus(`[${res.status}] ${body.error || 'DELETE failed'}`, false);
            return;
        }
        showStatus(`[${res.status}] ${body.message}`, true);
        if (String(editIdInput.value) === String(id)) {
            resetSharedForm();
        }
        loadSharedTodos();
    } catch (err) {
        showStatus('Network error during DELETE request', false);
    }
}

if (btnCancelEdit) {
    btnCancelEdit.addEventListener('click', function() {
        resetSharedForm();
    });
}

if (sharedForm && sharedList) {
    loadSharedTodos();

    sharedForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const idVal = editIdInput.value.trim();

        let uploadedBase64 = dataFotoBase64;
        if (sharedFile && sharedFile.files && sharedFile.files[0]) {
            uploadedBase64 = await fileToBase64(sharedFile.files[0]);
        }

        if (idVal === '') {
            const payload = {
                tgl: sharedTgl.value,
                nama: sharedNama.value,
                jam: sharedJam.value,
                jam_notif: sharedJamNotif.value,
                fotoBase64: uploadedBase64
            };
            try {
                const res = await fetch(API_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-API-Key': API_KEY
                    },
                    body: JSON.stringify(payload)
                });
                const body = await res.json();
                if (!res.ok) {
                    showStatus(`[${res.status}] ${body.error || 'POST failed'}`, false);
                    return;
                }
                jadwalkanNotifikasi(payload.tgl, payload.jam_notif, payload.nama);
                showStatus(`[${res.status}] ${body.message}`, true);
                resetSharedForm();
                loadSharedTodos();
            } catch (err) {
                showStatus('Network error during POST request', false);
            }
        } else {
            const payload = {
                tgl: sharedTgl.value,
                nama: sharedNama.value,
                jam: sharedJam.value,
                jam_notif: sharedJamNotif.value,
                fotoBase64: uploadedBase64
            };
            try {
                const res = await fetch(`${API_URL}?id=${idVal}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-API-Key': API_KEY
                    },
                    body: JSON.stringify(payload)
                });
                const body = await res.json();
                if (!res.ok) {
                    showStatus(`[${res.status}] ${body.error || 'PUT failed'}`, false);
                    return;
                }
                showStatus(`[${res.status}] ${body.message}`, true);
                resetSharedForm();
                loadSharedTodos();
            } catch (err) {
                showStatus('Network error during PUT request', false);
            }
        }
    });
}
