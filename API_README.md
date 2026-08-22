# CRM REST API Documentation

Base URL (local dev): `http://localhost:8080/api`

## Authentication

### POST /api/login
No token required. Returns a JWT valid for 1 hour.

**Request:**
```bash
curl -X POST http://localhost:8080/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@crm.test","password":"admin123"}'
```

**Response (200):**
```json
{
  "status": 200,
  "message": "Login successful",
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "token_type": "Bearer",
  "expires_in": 3600,
  "expires_at": "2026-08-22T15:30:00+00:00"
}
```

Every endpoint below requires this header:
```
Authorization: Bearer <token>
```

---

## GET /api/customers
List customers with pagination, filtering, and sorting.

**Query params:** `page`, `per_page`, `status`, `city`, `sort` (id|name|email|city|status|created_at), `order` (asc|desc)

```bash
curl "http://localhost:8080/api/customers?page=1&per_page=20&status=active&sort=name&order=asc" \
  -H "Authorization: Bearer <token>"
```

**Response (200):**
```json
{
  "status": 200,
  "data": [ { "id": 1, "name": "...", "email": "...", ... } ],
  "pagination": { "page": 1, "per_page": 20, "total": 104, "total_pages": 6 }
}
```

## GET /api/customers/{id}
```bash
curl http://localhost:8080/api/customers/1 -H "Authorization: Bearer <token>"
```
Returns `404` if not found, `403` if a sales-role token requests a customer not assigned to them.

## POST /api/customers
```bash
curl -X POST http://localhost:8080/api/customers \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"name":"John Doe","email":"john@example.com","city":"Mumbai","status":"active"}'
```
Returns `201` with the created record, or `400` if `name`/`email` are missing.

## PUT /api/customers/{id}
```bash
curl -X PUT http://localhost:8080/api/customers/1 \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"status":"inactive"}'
```
Send only the fields you want to change.

## DELETE /api/customers/{id}
```bash
curl -X DELETE http://localhost:8080/api/customers/1 -H "Authorization: Bearer <token>"
```
Admin role only — returns `403` for manager/sales tokens.

---

## Status Codes Used
| Code | Meaning |
|---|---|
| 200 | OK |
| 201 | Created |
| 400 | Bad Request (missing/invalid fields) |
| 401 | Unauthorized (missing/invalid/expired token, bad login credentials) |
| 403 | Forbidden (valid token, but role lacks permission) |
| 404 | Not Found |

## Test Credentials
| Role | Email | Password |
|---|---|---|
| Admin | admin@crm.test | admin123 |
| Manager | manager@crm.test | 12345 |
| Sales | sales@crm.test | sales123 |

## Postman
Import `CRM_API.postman_collection.json`. After running "Login", copy the returned `token` value into the collection's `token` variable to authenticate the rest of the requests.