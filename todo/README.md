# ToDo List PWA - [E05] The Offline Abyss

Upgrade aplikasi ToDo List modular PHP (E04) menjadi Progressive Web App (PWA) yang dapat diinstal dan tetap berfungsi dalam kondisi offline.

## Fitur PWA & Strategi Caching per Resource

1. **Web App Manifest (`manifest.json`)**: Dikonfigurasi dengan nama aplikasi, mode tampilan `standalone`, warna tema, serta ikon `icon-192.png` dan `icon-512.png`, lalu dihubungkan di `header.php`.
2. **Precaching App Shell & Offline Page (`sw.js` & `offline.html`)**: Pada event `install`, Service Worker menyimpan kerangka utama aplikasi (`index.php`, `index.php?menu=personal`, `style.css`, `script.js`, `manifest.json`, ikon) serta halaman cadangan `offline.html`.
3. **Caching Strategy per Resource**:
   - **Cache-First** (`todo-shell-v1`): Digunakan untuk aset statis (`style.css`, `script.js`, `manifest.json`, `icon-192.png`, `icon-512.png`) agar antarmuka langsung dimuat dari cache.
   - **Network-First with Offline Fallback** (`todo-pages-v1`): Digunakan untuk halaman PHP dinamis (`index.php`, `index.php?menu=shared`) agar data database selalu terbaru saat online, dan otomatis menampilkan halaman cache atau `offline.html` saat koneksi terputus.
   - **Stale-While-Revalidate** (`todo-images-v1`): Digunakan untuk berkas gambar unggahan di folder `uploads/` agar gambar tampil instan sekaligus diperbarui di latar belakang.
4. **Tombol Install (`menu-bar.php` & `script.js`)**: Menangkap event `beforeinstallprompt` untuk menampilkan tombol **Install App** pada bilah navigasi.

## Bukti Pengujian Offline (DevTools)

### 1. DevTools - Application > Manifest
![Bukti Manifest](proof-manifest.png)

### 2. DevTools - Application > Cache Storage
![Bukti Cache Storage](proof-cache-storage.png)

### 3. Pengujian Mode Offline (Network Off)
![Bukti Offline Mode](proof-offline.png)
