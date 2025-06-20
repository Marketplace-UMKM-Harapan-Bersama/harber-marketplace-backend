# PHB Ecommerce

## Instalasi

1. Clone repository ini:
    ```bash
    git clone https://github.com/Marketplace-UMKM-Harapan-Bersama/harber-marketplace-backend.git
    cd harber-marketplace-backend
    ```
2. Install dependency PHP:
    ```bash
    composer install
    ```
3. Install dependency JavaScript:
    ```bash
    npm install
    ```
4. Atur konfigurasi database pada file `.env`.
5. Generate app key dan migrasi database:
    ```bash
    php artisan key:generate
    php artisan migrate
    ```
6. Jalankan server:
    ```bash
    composer run dev
    ```
