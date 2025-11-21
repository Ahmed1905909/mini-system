# Mini System Backend

## What is inside?
- Login with token (JWT)
- Products (add, edit, delete)
- Orders (buy things)
- Stock go down when buy

## How to run?
1. `composer install`
2. Copy `.env.example` to `.env`
3. `php artisan key:generate`
4. `php artisan jwt:secret`
5. Make sure database is okay in `.env` (sqlite)
6. `php artisan migrate`
7. `php artisan serve`

Now api is working on `http://127.0.0.1:8000`!

## Database Picture
Here is how database look like:

```mermaid
erDiagram
    users ||--o{ orders : "places"
    orders ||--|{ order_items : "contains"
    products ||--o{ order_items : "included_in"
    
    users {
        id bigint PK
        name string
        email string
        password string
        created_at timestamp
        updated_at timestamp
    }
    
    products {
        id bigint PK
        name string
        description text
        price decimal
        stock integer
        created_at timestamp
        updated_at timestamp
    }
    
    orders {
        id bigint PK
        user_id bigint FK
        address string
        phone string
        total decimal
        status string
        created_at timestamp
        updated_at timestamp
    }
    
    order_items {
        id bigint PK
        order_id bigint FK
        product_id bigint FK
        quantity integer
        price decimal
        created_at timestamp
        updated_at timestamp
    }
```

## Api links
- POST `/api/auth/register` - make new user
- POST `/api/auth/login` - login
- GET `/api/products` - see all products
- POST `/api/orders` - buy something (need token)

