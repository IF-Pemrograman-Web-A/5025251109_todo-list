# ToDo List RESTful API & PWA - [E06] The Endpoint of Portals

Upgrade aplikasi ToDo List (E05) dengan memigrasikan operasi CRUD backend pada tab **Shared** menjadi **RESTful API** (`api.php`) berformat JSON lengkap dengan HTTP methods, HTTP response status codes, serta autentikasi API Key.

## Spesifikasi Endpoint REST API (`api.php`)

### Autentikasi (Optional Feature)
Setiap permintaan ke `api.php` wajib menyertakan header autentikasi:
- **Header:** `X-API-Key: todo-secret-key-2026`
- Jika header tidak disertakan atau kunci salah, server mengembalikan status **`401 Unauthorized`**.

### Daftar Endpoint & HTTP Methods

| Method | Endpoint | Deskripsi | Sukses Code | Error Code |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `api.php` | Mengambil seluruh daftar tugas Shared | `200 OK` | `401`, `405`, `500` |
| `GET` | `api.php?id={id}` | Mengambil detail 1 tugas berdasarkan ID | `200 OK` | `400`, `401`, `404`, `500` |
| `POST` | `api.php` | Menambahkan tugas baru (JSON / Form) | `201 Created` | `400`, `401`, `405`, `500` |
| `PUT` | `api.php?id={id}` | Memperbarui seluruh field tugas berdasarkan ID | `200 OK` | `400`, `401`, `404`, `500` |
| `PATCH` | `api.php?id={id}` | Memperbarui sebagian field (misal status `selesai`) | `200 OK` | `400`, `401`, `404`, `500` |
| `DELETE` | `api.php?id={id}` | Menghapus tugas berdasarkan ID | `200 OK` | `400`, `401`, `404`, `500` |

### Ringkasan HTTP Response Codes
- **`200 OK`**: Permintaan `GET`, `PUT`, `PATCH`, atau `DELETE` berhasil diproses.
- **`201 Created`**: Permintaan `POST` berhasil membuat data baru.
- **`400 Bad Request`**: Input kosong, format tanggal/waktu salah, format gambar tidak didukung, atau parameter `id` tidak valid.
- **`401 Unauthorized`**: Header `X-API-Key` tidak ada atau tidak cocok.
- **`404 Not Found`**: Data tugas dengan `id` yang diminta tidak ditemukan di database.
- **`405 Method Not Allowed`**: Metode HTTP selain `GET`, `POST`, `PUT`, `PATCH`, `DELETE` digunakan.
- **`500 Internal Server Error`**: Koneksi database gagal atau terjadi kesalahan eksekusi SQL.
