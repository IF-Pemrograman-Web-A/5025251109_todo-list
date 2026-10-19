const btnTema = document.getElementById('btnTema');
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

const video = document.getElementById('kamera');
const canvas = document.getElementById('kanvas');
const btnSnap = document.getElementById('btnSnap');
const hasilFoto = document.getElementById('hasilFoto');
const inputPhotoBase64 = document.getElementById('photo_base64');

if (video) {
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(function(stream) {
            video.srcObject = stream;
            video.play();
        })
        .catch(function(err) {
            console.log("Kamera tidak dapat diakses: ", err);
        });

    btnSnap.addEventListener('click', function() {
        const ctx = canvas.getContext('2d');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        const dataFotoBase64 = canvas.toDataURL('image/png');
        hasilFoto.src = dataFotoBase64;
        hasilFoto.style.display = 'block';
        
        inputPhotoBase64.value = dataFotoBase64;
    });
}