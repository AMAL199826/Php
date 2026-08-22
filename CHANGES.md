# CHANGES.md — Legacy CRM: Debug, Secure & Extend

This document covers every bug fixed, every feature built, the challenges faced along the way, and how to test everything.

---

## PART 1 — Bugs Fixed (8/8)

### 1. Search
**Broken:** `Customers::index()` read the `search` GET parameter but never applied it to the query — every request just returned the full unfiltered list.
**Where:** `app/Controllers/Customers.php`
**Fix:** Added `like()`/`orLike()` conditions against `name`, `email`, and `phone` when a `search` term is present.

### 2. Delete
**Broken:** The actual delete call was commented out: `// $this->customerModel->delete($id);`. The UI showed a "Customer deleted successfully" message, but nothing was ever removed from the database.
**Where:** `app/Controllers/Customers.php::delete()`
**Fix:** Uncommented the delete call. Also had to reorder logic: the activity log entry is now inserted **before** the delete (see Challenges below), otherwise a foreign key constraint violation occurs.

### 3. Edit / Update
**Broken:** `$this->customerModel->update($customer, $data)` passed the entire customer **array** as the first argument, when CodeIgniter's `Model::update($id, $data)` expects a primary key **ID**. This silently corrupted the update target.
**Where:** `app/Controllers/Customers.php::update()`
**Fix:** Changed to `$this->customerModel->update($id, $data)`.

### 4. Dashboard Count
**Broken:** `Dashboard::index()` hardcoded `'total_customers' => 0` and `'active_customers' => 0` — the dashboard always displayed zero regardless of actual data.
**Where:** `app/Controllers/Dashboard.php`
**Fix:** Replaced hardcoded values with real queries: `$customerModel->countAllResults()` for total, and `$customerModel->where('status', 'active')->countAllResults()` for active.

### 5. Status Filter
**Broken:** Same root cause as Search — the `status` GET parameter was captured but never applied as a `WHERE` clause.
**Where:** `app/Controllers/Customers.php::index()`
**Fix:** Added `where('status', $status)` when a status filter is present.

### 6. CSV Export
**Broken:** `export()` wrote only the CSV header row via `fputcsv()`, then immediately closed the file handle — customer data was fetched but never written out.
**Where:** `app/Controllers/Customers.php::export()`
**Fix:** Added a `foreach` loop over the fetched customers, writing one `fputcsv()` row per customer.

### 7. Form Validation
**Broken:** `store()` and `update()` had no server-side validation at all — only HTML5 `required` attributes on the client, which are trivially bypassed. Failed submissions also didn't display any error messages or preserve the user's typed input.
**Where:** `app/Controllers/Customers.php`, `app/Views/customers/create.php`, `app/Views/customers/edit.php`
**Fix:** Added `$this->validate()` rules (`required`, `min_length`, `valid_email`) in both controller methods. On failure, redirects back with `withInput()` and validation errors via flashdata. Views updated to display errors and repopulate fields using `old()`.

### 8. Pagination
**Broken:** Two issues — (a) `'pager' => null` was hardcoded in the view data instead of the real pager object CodeIgniter generates, so no pagination links rendered; (b) an earlier fix attempt called `paginate()` on the raw query `builder()`, which doesn't have that method (`paginate()` only exists on the Model).
**Where:** `app/Controllers/Customers.php::index()`
**Fix:** Applied filters directly on the model (not `builder()`), then called `paginate()` on the model, and passed `$this->customerModel->pager` (the real pager object) to the view.

---

## PART 2 — REST API (JWT Authenticated)

- **`POST /api/login`** — accepts `email`/`password` as JSON, returns a JWT + expiry.
- **`GET /api/customers`** — list with pagination (`page`, `per_page`), filtering (`status`, `city`), sorting (`sort`, `order`).
- **`GET /api/customers/{id}`**, **`POST /api/customers`**, **`PUT /api/customers/{id}`**, **`DELETE /api/customers/{id}`**.
- JWT implemented from scratch (`app/Libraries/Jwt.php`, HS256) — no external package dependency required.
- `app/Filters/JwtAuthFilter.php` validates the `Authorization: Bearer <token>` header on every route except `/api/login`.
- Role-aware: sales-role tokens only see/manage their own assigned customers via the API; only admin tokens can delete.
- Proper status codes throughout: `200`, `201`, `400`, `401`, `403`, `404`.

**Testing:** see `API_README.md` and `CRM_API.postman_collection.json` for full curl examples and a ready-to-import Postman collection.

---

## PART 3 — Email Notifications

- **`app/Services/EmailService.php`** — reusable service class wrapping CodeIgniter's Email library.
- **Welcome email** automatically sent when a new customer is created (`Customers::store()`), using the customer's own email address.
- **Templates:** `app/Views/emails/layout.php` (shared HTML wrapper/branding) + `app/Views/emails/welcome.php` (welcome content).
- **SMTP:** configured in `app/Config/Email.php` for Mailtrap (sandbox SMTP testing).
- **Error handling:** the entire send is wrapped in try/catch inside both `EmailService::send()` and the calling controller. If the email fails (bad credentials, network issue, etc.), it's logged via `log_message('error', ...)` and the customer is still created successfully — email failure never blocks the core create flow.

---

## PART 4 — Role-Based Access Control (RBAC)

**Roles:** `admin`, `manager`, `sales` (stored in a `role` ENUM column on `users`).

| Role | View | Edit | Delete |
|---|---|---|---|
| Admin | All customers | All customers | All customers |
| Manager | All customers | Only customers assigned to someone on their own `team_id` | — |
| Sales | Only customers assigned to them | Only customers assigned to them | — |

- **`assigned_to`** column added to `customers` (FK → `users.id`, `ON DELETE SET NULL`) to track ownership.
- **`app/Filters/RoleCheck.php`** — route-level filter restricting actions like delete to `admin` only.
- **Per-record permission checks** live in `Customers::canManage()` since team/ownership logic can't be expressed as a static route filter — it depends on the specific record.
- **UI:** Edit/Delete buttons are conditionally rendered based on a `can_manage` flag computed server-side per row.
- **Access Denied page** (`/access-denied`) shown when a user attempts an action outside their permission (e.g. typing an edit URL directly).
- **"Assigned To" dropdown** added to create/edit forms (visible only to admin/manager) so customers can actually be assigned/reassigned to specific users.

**Test users** (already seeded):
| Role | Username | Email | Password | team_id |
|---|---|---|---|---|
| Admin | admin | admin@crm.test | admin123 | — |
| Manager | manager | manager@crm.test | 12345 | 1 |
| Sales | sales | sales@crm.test | sales123 | 1 |

---

## PART 5 — Analytics Dashboard with Charts

Built out the dashboard with Chart.js visualizations on top of the existing summary cards:
- Customer growth line chart (last 6 months)
- Status distribution pie chart (Active/Inactive/Pending)
- Top 5 cities bar chart
- Recent activity feed (last 10 activities)
- Dashboard data caching (1 hour) with a manual refresh button to bust the cache

---

## Challenges Faced & How They Were Solved

1. **Local environment setup (biggest time sink).** The initial Apache/FastCGI vhost was misconfigured on multiple levels in sequence: wrong PHP version being loaded (old 5.6.9 install instead of 8.2.23), `DocumentRoot` pointing at the project root instead of `public/`, and FastCGI not passing `PATH_INFO` through (`AcceptPathInfo`, "No input file specified"). Ultimately resolved by switching local development entirely to `php spark serve`, which sidesteps Apache/FastCGI complexity — Apache is only needed for the live hosting deployment, not local dev.

2. **Malformed SQL on activity logging.** Every customer create/update/delete threw a `#1064` syntax error with a stray trailing comma in the generated `INSERT`. Root cause: `ActivityModel` had `protected $updatedField = null;` instead of `''`. CodeIgniter's timestamp logic checks `!empty($updatedField)`, and `null` vs `''` behaved differently across the insert path, producing a malformed column list. Fixed by setting it to an empty string.

3. **Foreign key violation on delete.** Deleting a customer failed with a `#1452` FK constraint error, because the code tried to log a "deleted" activity **after** the customer row (and its FK target) no longer existed. Fixed by logging the activity **before** deleting the customer. Noted for future improvement: since the FK is `ON DELETE CASCADE`, that log row is itself removed the instant the customer is deleted — a permanent audit trail would require changing the constraint to `ON DELETE SET NULL` with a nullable `customer_id`.

4. **Pagination bug had two layers.** First fix mistakenly called `paginate()` on the raw query builder (`->builder()`), which doesn't expose that method — only the Model class does. Second issue was the hardcoded `null` pager object being passed to the view instead of the real one CodeIgniter generates during `paginate()`.

5. **JWT filter not firing.** After writing the `JwtAuthFilter`, requests to protected API routes failed with "jwt filter must have a matching alias defined" — the filter class existed but was never registered in `app/Config/Filters.php`'s `$aliases` array. Adding the `'jwt' => \App\Filters\JwtAuthFilter::class` entry resolved it.

6. **Team-based manager permissions.** There's no separate "teams" table — team membership is just a shared `team_id` integer on `users`. Manager edit permission is resolved at request time by looking up the assigned sales rep's `team_id` and comparing it to the manager's own, rather than via a static role check.

---

## Testing Instructions

### Web app (local)
```
php spark serve
```
Visit `http://localhost:8080/`. Log in with any of the 3 test users above.

### REST API
```bash
# Login
curl -X POST http://localhost:8080/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@crm.test","password":"admin123"}'

# Use the returned token for all other endpoints
curl "http://localhost:8080/api/customers?page=1&per_page=20&status=active&sort=name&order=asc" \
  -H "Authorization: Bearer <token>"
```
Full endpoint documentation with all curl examples: `API_README.md`.
Postman collection: `CRM_API.postman_collection.json` (import into Postman, run "Login", copy the token into the collection's `token` variable).

### RBAC
Log in as each of the 3 test users above and confirm:
- **Sales** only sees their own assigned customers, and cannot see a Delete button anywhere.
- **Manager** sees all customers, but Edit only appears on customers assigned to their own team.
- **Admin** sees everything with full Edit/Delete access.
- Manually visiting an edit URL for a customer outside your permission redirects to `/access-denied`.

### Email
Configure Mailtrap SMTP credentials in `app/Config/Email.php`, then create a new customer through the UI — a welcome email should appear in the Mailtrap inbox within seconds. Creating a customer succeeds even if SMTP fails (check `writable/logs/` for the logged error in that case).

### Database migration
```bash
php spark migrate
```
This applies the `assigned_to` column addition and (on a fresh clone) the `users` table creation. See `migrations/your_changes.sql` for the raw SQL equivalent and seed data.