# ERD Print Perpustakaan Sekolah

Dokumen ini fokus ke tabel inti aplikasi perpustakaan agar lebih rapi saat digambar atau diprint, tetapi tetap mengikuti struktur database yang sudah termigrasi.

## Yang Sebaiknya Diprint

Untuk tugas sekolah, utamakan tabel inti berikut:

- `users`
- `roles`
- `permissions`
- `permission_role`
- `categories`
- `books`
- `loans`
- `sanctions`
- `book_procurements`
- `settings`
- `activity_logs`
- `backups`
- `login_otp_tokens`

Tabel bawaan Laravel seperti `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `password_reset_tokens`, dan `sessions` bisa ditaruh kecil di samping atau tidak dijadikan fokus utama, kecuali guru meminta semua tabel.

## Simbol ERD

- `PK` = Primary Key
- `FK` = Foreign Key
- `UK` = Unique Key

## ERD Inti

```mermaid
erDiagram
    USERS {
        BIGINT id PK
        VARCHAR name
        VARCHAR username UK
        VARCHAR nik UK
        VARCHAR email
        TIMESTAMP email_verified_at
        VARCHAR password
        VARCHAR phone
        VARCHAR kelas
        VARCHAR jurusan
        VARCHAR profile_photo
        VARCHAR google_id UK
        BIGINT role_id FK
        BOOLEAN is_active
        VARCHAR remember_token
        TIMESTAMP created_at
        TIMESTAMP updated_at
        BOOLEAN delete
        TIMESTAMP deleted_at
        BIGINT deleted_by FK
        VARCHAR deleted_ip
    }

    ROLES {
        BIGINT id PK
        VARCHAR name UK
        VARCHAR label
        TIMESTAMP created_at
        TIMESTAMP updated_at
        BOOLEAN delete
        TIMESTAMP deleted_at
        BIGINT deleted_by FK
        VARCHAR deleted_ip
    }

    PERMISSIONS {
        BIGINT id PK
        VARCHAR name UK
        VARCHAR label
        TIMESTAMP created_at
        TIMESTAMP updated_at
        BOOLEAN delete
        TIMESTAMP deleted_at
        BIGINT deleted_by FK
        VARCHAR deleted_ip
    }

    PERMISSION_ROLE {
        BIGINT id PK
        BIGINT role_id FK
        BIGINT permission_id FK
        TIMESTAMP created_at
        TIMESTAMP updated_at
    }

    CATEGORIES {
        BIGINT id PK
        VARCHAR name UK
        VARCHAR slug UK
        TEXT description
        TIMESTAMP created_at
        TIMESTAMP updated_at
        BOOLEAN delete
        TIMESTAMP deleted_at
        BIGINT deleted_by FK
        VARCHAR deleted_ip
    }

    BOOKS {
        BIGINT id PK
        VARCHAR title
        VARCHAR author
        VARCHAR isbn UK
        VARCHAR cover_image
        BIGINT category_id FK
        VARCHAR publisher
        VARCHAR place_of_publication
        VARCHAR edition
        YEAR published_year
        UINT page_count
        UINT stock_total
        UINT stock_available
        VARCHAR status
        TEXT description
        TIMESTAMP created_at
        TIMESTAMP updated_at
        BOOLEAN delete
        TIMESTAMP deleted_at
        BIGINT deleted_by FK
        VARCHAR deleted_ip
    }

    LOANS {
        BIGINT id PK
        BIGINT book_id FK
        BIGINT member_id FK
        VARCHAR borrower_name
        BIGINT processed_by FK
        DATE borrowed_at
        DATE due_at
        DATE returned_at
        VARCHAR status
        DECIMAL fine_amount
        TEXT notes
        TIMESTAMP created_at
        TIMESTAMP updated_at
        BOOLEAN delete
        TIMESTAMP deleted_at
        BIGINT deleted_by FK
        VARCHAR deleted_ip
    }

    SANCTIONS {
        BIGINT id PK
        BIGINT loan_id FK
        BIGINT member_id FK
        BIGINT processed_by FK
        VARCHAR type
        VARCHAR status
        TEXT reason
        UINT duration_days
        DATE starts_at
        DATE ends_at
        TEXT notes
        TIMESTAMP created_at
        TIMESTAMP updated_at
        BOOLEAN delete
        TIMESTAMP deleted_at
        BIGINT deleted_by FK
        VARCHAR deleted_ip
    }

    BOOK_PROCUREMENTS {
        BIGINT id PK
        VARCHAR title
        VARCHAR author
        VARCHAR isbn
        VARCHAR publisher
        YEAR published_year
        UINT quantity
        TEXT notes
        BIGINT category_id FK
        VARCHAR status
        BIGINT proposed_by FK
        BIGINT approved_by FK
        TIMESTAMP approved_at
        BIGINT rejected_by FK
        TIMESTAMP rejected_at
        TIMESTAMP created_at
        TIMESTAMP updated_at
        BOOLEAN delete
        TIMESTAMP deleted_at
        BIGINT deleted_by FK
        VARCHAR deleted_ip
    }

    SETTINGS {
        BIGINT id PK
        VARCHAR key UK
        VARCHAR label
        VARCHAR type
        TEXT value
        TIMESTAMP created_at
        TIMESTAMP updated_at
        BOOLEAN delete
        TIMESTAMP deleted_at
        BIGINT deleted_by FK
        VARCHAR deleted_ip
    }

    ACTIVITY_LOGS {
        BIGINT id PK
        BIGINT user_id FK
        VARCHAR module
        VARCHAR action
        TEXT description
        JSON properties
        TIMESTAMP created_at
        TIMESTAMP updated_at
        BOOLEAN delete
        TIMESTAMP deleted_at
        BIGINT deleted_by FK
        VARCHAR deleted_ip
    }

    BACKUPS {
        BIGINT id PK
        VARCHAR file_name
        VARCHAR file_path
        BIGINT size_bytes
        BIGINT created_by FK
        TIMESTAMP created_at
        TIMESTAMP updated_at
        BOOLEAN delete
        TIMESTAMP deleted_at
        BIGINT deleted_by FK
        VARCHAR deleted_ip
    }

    LOGIN_OTP_TOKENS {
        BIGINT user_id PK, FK
        VARCHAR token
        TIMESTAMP created_at
    }

    ROLES ||--o{ USERS : role_id
    ROLES ||--o{ PERMISSION_ROLE : role_id
    PERMISSIONS ||--o{ PERMISSION_ROLE : permission_id
    CATEGORIES ||--o{ BOOKS : category_id
    BOOKS ||--o{ LOANS : book_id
    USERS ||--o{ LOANS : member_id
    USERS ||--o{ LOANS : processed_by
    LOANS ||--o{ SANCTIONS : loan_id
    USERS ||--o{ SANCTIONS : member_id
    USERS ||--o{ SANCTIONS : processed_by
    CATEGORIES ||--o{ BOOK_PROCUREMENTS : category_id
    USERS ||--o{ BOOK_PROCUREMENTS : proposed_by
    USERS ||--o{ BOOK_PROCUREMENTS : approved_by
    USERS ||--o{ BOOK_PROCUREMENTS : rejected_by
    USERS ||--o{ ACTIVITY_LOGS : user_id
    USERS ||--o{ BACKUPS : created_by
    USERS ||--|| LOGIN_OTP_TOKENS : user_id

    USERS ||--o{ USERS : deleted_by
    USERS ||--o{ ROLES : deleted_by
    USERS ||--o{ PERMISSIONS : deleted_by
    USERS ||--o{ CATEGORIES : deleted_by
    USERS ||--o{ BOOKS : deleted_by
    USERS ||--o{ LOANS : deleted_by
    USERS ||--o{ SANCTIONS : deleted_by
    USERS ||--o{ BOOK_PROCUREMENTS : deleted_by
    USERS ||--o{ SETTINGS : deleted_by
    USERS ||--o{ ACTIVITY_LOGS : deleted_by
    USERS ||--o{ BACKUPS : deleted_by
```

## Relasi Yang Harus Benar Saat Digambar

- `users.role_id` -> `roles.id`
- `permission_role.role_id` -> `roles.id`
- `permission_role.permission_id` -> `permissions.id`
- `books.category_id` -> `categories.id`
- `loans.book_id` -> `books.id`
- `loans.member_id` -> `users.id`
- `loans.processed_by` -> `users.id`
- `sanctions.loan_id` -> `loans.id`
- `sanctions.member_id` -> `users.id`
- `sanctions.processed_by` -> `users.id`
- `book_procurements.category_id` -> `categories.id`
- `book_procurements.proposed_by` -> `users.id`
- `book_procurements.approved_by` -> `users.id`
- `book_procurements.rejected_by` -> `users.id`
- `activity_logs.user_id` -> `users.id`
- `backups.created_by` -> `users.id`
- `login_otp_tokens.user_id` -> `users.id`
- `deleted_by` di banyak tabel -> `users.id`

## Catatan Penting

- `users.email` saat ini tidak `unique` karena unique email sudah dihapus.
- `books.barcode` sudah tidak ada, karena migration penghapus barcode sudah dijalankan.
- `loans.member_id` sekarang boleh `null`.
- `sessions.user_id` hanya index, bukan foreign key.
- `login_otp_tokens.user_id` adalah `PK` sekaligus `FK`.
- Jika diagram terasa terlalu penuh, buat satu diagram utama untuk tabel inti perpustakaan lalu letakkan tabel Laravel bawaan di bagian kecil terpisah.
