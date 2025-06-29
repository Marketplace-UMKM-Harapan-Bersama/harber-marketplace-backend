# 📦 Product & ProductCategory API Documentation

## Daftar Isi
- [Deskripsi](#deskripsi)
- [Autentikasi](#autentikasi)
- [Endpoint](#endpoint)
  - [Product Categories](#product-categories)
  - [Products](#products)
- [Struktur Response](#struktur-response)
- [Contoh Response](#contoh-response)
- [Error Handling](#error-handling)
- [Cara Uji Coba](#cara-uji-coba)
- [Catatan Tambahan](#catatan-tambahan)

---

## Deskripsi

API ini menyediakan endpoint untuk mengelola dan menampilkan data produk serta kategori produk.  
Response API sudah terstruktur menggunakan Laravel Resource agar mudah digunakan di frontend/mobile.

---

## Autentikasi

- Endpoint `/api/user` menggunakan Laravel Sanctum (`auth:sanctum`).
- Endpoint produk dan kategori **tidak membutuhkan autentikasi** (public).

---

## Endpoint

### Product Categories

#### 1. List Kategori Produk
- **URL:** `/api/product-categories`
- **Method:** `GET`
- **Query Params:**  
  - `page` (opsional): nomor halaman
- **Response:** Paginated list of categories

#### 2. Detail Kategori Produk (opsional, jika ada)
- **URL:** `/api/product-categories/{id}`
- **Method:** `GET`
- **Response:** Detail kategori

---

### Products

#### 1. List Produk
- **URL:** `/api/products`
- **Method:** `GET`
- **Query Params:**  
  - `page` (opsional): nomor halaman
- **Response:** Paginated list of products

#### 2. Detail Produk (opsional, jika ada)
- **URL:** `/api/products/{id}`
- **Method:** `GET`
- **Response:** Detail produk

---

## Struktur Response

### Sukses (List)
```json
{
  "data": [
    {
      "success": 200,
      "message": "Success",
      "data": {
        "id": 1,
        "name": "Kategori A",
        "slug": "kategori-a"
      }
    }
  ],
  "status": 200,
  "message": "List Data Product Category",
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 10,
    "total": 1
  }
}
```

### Sukses (Detail)
```json
{
  "success": 200,
  "message": "Success",
  "data": {
    "id": 1,
    "name": "Kategori A",
    "slug": "kategori-a"
  }
}
```

### Produk dengan Kategori
```json
{
  "data": [
    {
      "success": 200,
      "message": "Success",
      "data": {
        "id": 1,
        "name": "Produk A",
        "slug": "produk-a",
        "price": 10000,
        "description": "Deskripsi produk",
        "category": {
          "success": 200,
          "message": "Success",
          "data": {
            "id": 1,
            "name": "Kategori A",
            "slug": "kategori-a"
          }
        }
      }
    }
  ],
  "status": 200,
  "message": "List Data Product"
}
```

---

## Error Handling

- Jika data tidak ditemukan:
  ```json
  {
    "message": "No query results for model [App\\Models\\ProductCategory] 999",
    "status": 404
  }
  ```
- Jika terjadi error validasi atau server, response akan mengikuti standar Laravel.

---

## Cara Uji Coba

1. Jalankan server:
   ```sh
   php artisan serve
   ```
2. Buka Postman atau API client lain.
3. Masukkan endpoint, misal:
   - `GET http://127.0.0.1:8000/api/product-categories`
   - `GET http://127.0.0.1:8000/api/products`
4. Klik **Send** dan lihat hasilnya.

---

## Catatan Tambahan

- Pagination default: 10 data per halaman.
- Endpoint dapat dikembangkan untuk fitur tambah, edit, hapus, dan pencarian.
- Untuk endpoint yang membutuhkan autentikasi, gunakan token dari Laravel Sanctum.

---

**Dokumentasi ini berlaku untuk commit:**  
`feat: Implement Product and ProductCategory API with resource