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
## API

### Untuk generate client_id dan client_secret:
    ```bash
    php artisan passport:client --personal --name="Marketplace Personal Access Client" --provider="marketplace_users"
    php artisan passport:client
    ```

### API Examples:
1. Register `/api/register-seller`:
    Headers:
    ```http
    Content-Type: application/json
    ```
    Request Body (seller):
    ```
    {
        "grant_type": "password",
        "client_id": "client_id",
        "client_secret": "client_secret",
        "name": "John Doe",
        "email": "johndoe@example.com",
        "password": "password",
        "role": "seller",
        "shop_name": "John Doe Shop",
        "shop_url": "example.com",
        "shop_description": "Lorem Ipsum"
    }
    ```
    Request Body (customer):
    ```
    {
        "grant_type": "password",
        "client_id": "client_id",
        "client_secret": "client_secret",
        "name": "John Doe",
        "email": "johndoe@example.com",
        "password": "password",
        "role": "customer",
    }
    ```
2. Login `/api/login`:
    Headers:
    ```http
    Content-Type: application/json
    ```
    Request body:
    ```
    {
        "grant_type":"passort",
        "client_id":"client_id",
        "client_sectret":"client_secret",
        "email": "johndoe@example.com",
        "password": "password"
    }
    ```