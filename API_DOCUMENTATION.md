# API Documentation for Frontend Team

**Base URL**: `http://127.0.0.1:8000/api/v1`  
**Last Updated**: 2025-12-18

---

## 📌 Response Format

Semua endpoint menggunakan format response standar:

```json
{
  "success": true,
  "message": "Success",
  "data": { ... }
}
```

**Error Response:**

```json
{
  "success": false,
  "message": "Error message here",
  "errors": { ... }  // Optional, untuk validation errors
}
```

---

## 🔐 Authentication

### Login

```
POST /login
Content-Type: application/json

Body:
{
  "email": "admin@example.com",
  "password": "password123"
}

Response:
{
  "success": true,
  "data": {
    "token": "1|xxxxx...",
    "user": { ... }
  }
}
```

### Protected Endpoints

Tambahkan header untuk endpoint yang butuh auth:

```
Authorization: Bearer {token}
```

### Logout

```
POST /logout
Authorization: Bearer {token}
```

### Get Current User

```
GET /me
Authorization: Bearer {token}
```

---

## 🌐 Public Endpoints (No Auth Required)

### Products

#### List Products

```
GET /products
GET /products?per_page=12
GET /products?q=search_term
GET /products?featured=true
GET /products?category_id=1
GET /products?limit=5          // For carousel (returns collection, not paginated)
```

**Response Data:**

```json
{
    "id": 65,
    "name": "Cordele Dining Chair",
    "description": "...",
    "material": "Teak Wood",
    "is_featured": true,
    "master_category": { "id": 1, "name": "Chairs" },
    "dimension": { "id": 5, "width": 50, "height": 90, "depth": 45 },
    "creator": { "id": 1, "name": "Admin", "email": "admin@example.com" },
    "cover_images": [{ "id": 1, "product_id": 65, "image_url": "/storage/cover_images/abc123.jpg" }],
    "product_images": [
        { "id": 48, "product_id": 65, "image_url": "/storage/product_images/def456.jpg", "alt": null, "order": 0 },
        { "id": 60, "product_id": 65, "image_url": "/storage/product_images/ghi789.jpg", "alt": null, "order": 1 }
    ],
    "teak_images": [{ "id": 10, "product_id": 65, "image_url": "/storage/teak_images/jkl012.jpg" }]
}
```

#### Show Product

```
GET /products/{id}
```

---

### 🖼️ Frontend: Menampilkan Gambar Product

> **PENTING**: `image_url` yang dikembalikan API adalah **relative path**. Frontend HARUS menambahkan base URL API.

#### Konfigurasi Base URL

```javascript
// services/api.js atau config.js
const API_BASE_URL = 'http://localhost:8000'; // Development
// const API_BASE_URL = 'https://api.yoursite.com';  // Production
```

#### Helper Function untuk Image URL

```javascript
// utils/imageHelper.js
const API_BASE_URL = 'http://localhost:8000';

export const getImageUrl = (relativePath) => {
    if (!relativePath) return '/placeholder.jpg';

    // Jika sudah full URL, return as is
    if (relativePath.startsWith('http')) return relativePath;

    // Tambahkan base URL
    return `${API_BASE_URL}${relativePath}`;
};

export const getProductImage = (product) => {
    // Prioritas: cover_images → product_images → placeholder
    const coverImage = product.cover_images?.[0]?.image_url;
    const productImage = product.product_images?.[0]?.image_url;

    return getImageUrl(coverImage || productImage);
};
```

#### Component Product Card

```jsx
// components/ProductCard.jsx
import { getProductImage } from '../utils/imageHelper';

function ProductCard({ product }) {
    const imageUrl = getProductImage(product);

    return (
        <div className="product-card">
            <img
                src={imageUrl}
                alt={product.name}
                onError={(e) => {
                    e.target.src = '/placeholder.jpg';
                }}
            />
            <h3>{product.name}</h3>
            <p>{product.description}</p>
        </div>
    );
}
```

#### Halaman Collections

```jsx
// pages/Collections.jsx
import { useState, useEffect } from 'react';
import api from '../services/api';
import ProductCard from '../components/ProductCard';

function Collections() {
    const [products, setProducts] = useState([]);

    useEffect(() => {
        api.get('/products').then((res) => {
            // Data ada di res.data.data.data untuk paginated
            // atau res.data.data untuk non-paginated (dengan ?limit=)
            const productData = res.data.data.data || res.data.data;
            setProducts(productData);
        });
    }, []);

    return (
        <div className="grid">
            {products.map((product) => (
                <ProductCard key={product.id} product={product} />
            ))}
        </div>
    );
}
```

#### ⚠️ Common Mistakes

| ❌ Salah                                         | ✅ Benar                                                         |
| ------------------------------------------------ | ---------------------------------------------------------------- |
| `<img src={product.image}`                       | `<img src={getProductImage(product)}`                            |
| `<img src={product.product_images[0].image_url}` | `<img src={getImageUrl(product.product_images?.[0]?.image_url)}` |
| Mengakses `product.images`                       | Mengakses `product.product_images` atau `product.cover_images`   |

---

### News

#### List News

```
GET /news
GET /news?per_page=20
```

**Response Data:**

```json
{
  "id": 1,
  "title": "News Title",
  "is_top_news": false,
  "thumbnail_url": "https://...",  // Extracted from content, null jika tidak ada gambar
  "creator": { ... },
  "created_at": "2025-12-18T00:00:00.000000Z"
}
```

#### Get Top News (Featured)

```
GET /news/top-news
```

Returns single news item yang di-set sebagai top news.

#### Show News Detail

```
GET /news/{id}
```

Includes `content` relation dengan HTML content lengkap.

---

### CSR (Corporate Social Responsibility)

#### List CSR

```
GET /csrs
```

**Response Data:**

```json
{
  "id": 1,
  "title": "CSR Title",
  "thumbnail_url": "https://...",  // Extracted from content
  "creator": { ... },
  "created_at": "..."
}
```

#### Show CSR Detail

```
GET /csrs/{id}
```

Includes `content` relation.

---

### Brands ⚡ (Cached - 5 min)

```
GET /brands
GET /brands/{id}
```

**Response Data:**

```json
{
    "id": 1,
    "name": "Brand Name",
    "image_url": "storage/brands/filename.jpg"
}
```

---

### Certifications ⚡ (Cached - 5 min)

```
GET /certifications
GET /certifications/{id}
```

---

### Master Categories ⚡ (Cached - 5 min)

```
GET /master-categories
GET /master-categories/{id}
```

**Response Data:**

```json
{
    "id": 1,
    "name": "Category Name"
}
```

---

### Dimensions ⚡ (Cached - 5 min)

```
GET /dimensions
GET /dimensions/{id}
```

**Response Data:**

```json
{
    "id": 1,
    "width": 100,
    "height": 50,
    "depth": 30
}
```

---

### Banners (Public - Active Only)

```
GET /banners
```

Returns only `is_active: true` banners, sorted by `order`.

**Response Data:**

```json
{
    "id": 1,
    "title": "Banner Title",
    "subtitle": "...",
    "image_url": "http://localhost:8000/storage/banners/...",
    "link_url": "https://...",
    "is_active": true,
    "order": 1
}
```

---

### Contact Form

```
POST /contact
Content-Type: application/json

Body:
{
  "name": "John Doe",          // Required, max 100 chars
  "email": "john@example.com", // Required, valid email
  "phone": "08123456789",      // Optional, max 20 chars
  "message": "Hello...",       // Required, max 2000 chars
  "product_id": 5              // Optional, ID produk dari tombol "Order Now"
}
```

**Response (dengan product_id):**

```json
{
    "success": true,
    "message": "Message sent successfully",
    "data": {
        "id": 1,
        "product_name": "Modern Teak Chair",
        "created_at": "2025-12-19T09:00:00.000000Z"
    }
}
```

**Rate Limit**: 5 requests per minute

#### 🛒 Flow "Order Now" (Product → Contact)

```
┌─────────────────────────────────────────────────────────────────┐
│  FRONTEND FLOW                                                   │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  1. User di halaman /collections atau /products/{id}             │
│     └─ Klik tombol "Order Now"                                   │
│                                                                  │
│  2. Frontend redirect ke /contact?product_id=5                   │
│     └─ Simpan product_id dari URL params                         │
│                                                                  │
│  3. Di halaman Contact, tampilkan info produk:                   │
│     └─ GET /products/5 → Ambil nama produk untuk ditampilkan     │
│                                                                  │
│  4. User isi form contact dan submit                             │
│     └─ POST /contact { ..., product_id: 5 }                      │
│                                                                  │
│  5. Backend simpan dengan relasi ke produk                       │
│     └─ Response includes product_name                            │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🔒 Protected Endpoints (Auth Required)

### CRUD Operations

Semua endpoint berikut membutuhkan `Authorization: Bearer {token}`:

| Resource          | Create                    | Update                        | Delete                           |
| ----------------- | ------------------------- | ----------------------------- | -------------------------------- |
| Products          | `POST /products`          | `PUT /products/{id}`          | `DELETE /products/{id}`          |
| News              | `POST /news`              | `PUT /news/{id}`              | `DELETE /news/{id}`              |
| CSR               | `POST /csrs`              | `PUT /csrs/{id}`              | `DELETE /csrs/{id}`              |
| Brands            | `POST /brands`            | `PUT /brands/{id}`            | `DELETE /brands/{id}`            |
| Certifications    | `POST /certifications`    | `PUT /certifications/{id}`    | `DELETE /certifications/{id}`    |
| Master Categories | `POST /master-categories` | `PUT /master-categories/{id}` | `DELETE /master-categories/{id}` |
| Dimensions        | `POST /dimensions`        | `PUT /dimensions/{id}`        | `DELETE /dimensions/{id}`        |

---

### Contact Messages Management

```
GET    /admin/contacts
GET    /admin/contacts?q=search
GET    /admin/contacts?filter[status]=new|read|replied
GET    /admin/contacts?date_from=2025-01-01&date_to=2025-12-31
GET    /admin/contacts/{id}     // Auto-marks as "read"
PUT    /admin/contacts/{id}     // Update status
DELETE /admin/contacts/{id}
```

**Response Data (includes product info):**

```json
{
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "08123456789",
    "message": "Saya tertarik dengan produk ini...",
    "product_id": 5,
    "product": {
        "id": 5,
        "name": "Modern Teak Chair"
    },
    "status": "new",
    "created_at": "2025-12-19T09:00:00.000000Z"
}
```

> 💡 **Note**: Jika `product_id` tidak ada (contact tanpa "Order Now"), field `product` akan `null`.

---

### Banners Management

```
GET    /admin/banners
GET    /admin/banners?q=search
GET    /admin/banners?filter[is_active]=true
POST   /admin/banners           // multipart/form-data with "image"
GET    /admin/banners/{id}
POST   /admin/banners/{id}      // Update (use POST, not PUT for file upload)
DELETE /admin/banners/{id}
```

---

### Product Form Data

```
POST /products
Content-Type: multipart/form-data

Fields:
- name: string (required)
- description: string
- material: string
- is_featured: boolean
- master_category_id: integer
- dimension_id: integer
- product_images[]: File[] (multiple)
- teak_images[]: File[] (multiple)
- cover_images[]: File[] (multiple)
```

**Update dengan hapus gambar:**

```
PUT /products/{id}

Fields:
- ... (same as create)
- product_images_delete[]: integer[] (IDs to delete)
- teak_images_delete[]: integer[]
- cover_images_delete[]: integer[]
```

---

### News Form

```
POST /news
Content-Type: application/json

Body:
{
  "title": "News Title",
  "content": "<p>HTML content here...</p>",
  "is_top_news": false
}
```

> **Note**: Jika `is_top_news: true`, backend otomatis akan set semua news lain menjadi `is_top_news: false`.

---

## 👑 Super Admin Only Endpoints

Membutuhkan role `Super Admin`:

### Users Management

```
GET    /users
GET    /users?q=search&per_page=20
POST   /users
GET    /users/{id}
PUT    /users/{id}
DELETE /users/{id}
```

### Roles Management

```
GET    /roles
GET    /roles?q=search
POST   /roles
GET    /roles/{id}
PUT    /roles/{id}
DELETE /roles/{id}
```

---

## 📊 Pagination Format

Semua list endpoints menggunakan format pagination Laravel:

```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [...],
    "first_page_url": "...",
    "from": 1,
    "last_page": 5,
    "last_page_url": "...",
    "links": [...],
    "next_page_url": "...",
    "path": "...",
    "per_page": 20,
    "prev_page_url": null,
    "to": 20,
    "total": 100
  }
}
```

---

## ⚠️ Error Codes

| Code | Meaning                        |
| ---- | ------------------------------ |
| 200  | Success                        |
| 201  | Created                        |
| 400  | Bad Request                    |
| 401  | Unauthenticated                |
| 403  | Forbidden (tidak punya akses)  |
| 404  | Not Found                      |
| 422  | Validation Error               |
| 429  | Too Many Requests (rate limit) |
| 500  | Server Error                   |

---

## 🔄 Perubahan Terbaru (2025-12-19)

### New Features

1. **Order Now → Contact Flow**: `POST /contact` sekarang menerima `product_id` optional
    - Frontend bisa kirim `product_id` saat user klik "Order Now" di halaman produk
    - Response akan include `product_name`
    - Admin contact list sekarang menampilkan info produk terkait

### Breaking Changes

**Tidak ada breaking changes.** Field `product_id` adalah optional.

### Performance Improvements

1. **Caching**: Brands, Certifications, Master Categories, Dimensions sekarang di-cache 5 menit
2. **Indexes**: Query 30% lebih cepat untuk endpoint dengan JOIN
3. **N+1 Fixed**: News dan CSR list tidak lagi trigger query tambahan

### Notes

- `thumbnail_url` pada News/CSR akan return `null` jika tidak ada gambar di content
- Endpoint cached akan otomatis invalidate saat ada create/update/delete
- Contact messages dengan `product_id` akan menampilkan `product` object dalam response

---

## 🧪 Testing

### Ping Endpoint

```
GET /ping

Response:
{
  "status": "ok",
  "message": "Laravel connected"
}
```

### CURL Examples

```bash
# Public endpoint
curl http://127.0.0.1:8000/api/v1/products

# With auth
curl -H "Authorization: Bearer YOUR_TOKEN" http://127.0.0.1:8000/api/v1/me

# Create product with images
curl -X POST \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "name=Product Name" \
  -F "description=Description" \
  -F "product_images[]=@image1.jpg" \
  -F "product_images[]=@image2.jpg" \
  http://127.0.0.1:8000/api/v1/products
```
