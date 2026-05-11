# ERD Perpustakaan Sekolah

ERD ini disusun langsung dari migrasi di folder `database/migrations`, jadi isinya mengikuti struktur database project saat ini.

## Keterangan Simbol

- `PK` = Primary Key
- `FK` = Foreign Key
- `UK` = Unique Key

## Mermaid ERD

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

    LOGIN_OTP_TOKENS {
        BIGINT user_id PK, FK
        VARCHAR token
        TIMESTAMP created_at
    }

    PASSWORD_RESET_TOKENS {
        VARCHAR email PK
        VARCHAR token
        TIMESTAMP created_at
    }

    SESSIONS {
        VARCHAR id PK
        BIGINT user_id
        VARCHAR ip_address
        TEXT user_agent
        LONGTEXT payload
        INT last_activity
    }

    CACHE {
        VARCHAR key PK
        MEDIUMTEXT value
        INT expiration
    }

    CACHE_LOCKS {
        VARCHAR key PK
        VARCHAR owner
        INT expiration
    }

    JOBS {
        BIGINT id PK
        VARCHAR queue
        LONGTEXT payload
        UINT attempts
        UINT reserved_at
        UINT available_at
        UINT created_at
    }

    JOB_BATCHES {
        VARCHAR id PK
        VARCHAR name
        INT total_jobs
        INT pending_jobs
        INT failed_jobs
        LONGTEXT failed_job_ids
        MEDIUMTEXT options
        INT cancelled_at
        INT created_at
        INT finished_at
    }

    FAILED_JOBS {
        BIGINT id PK
        VARCHAR uuid UK
        TEXT connection
        TEXT queue
        LONGTEXT payload
        LONGTEXT exception
        TIMESTAMP failed_at
    }

    ROLES ||--o{ USERS : has
    ROLES ||--o{ PERMISSION_ROLE : maps
    PERMISSIONS ||--o{ PERMISSION_ROLE : maps
    CATEGORIES ||--o{ BOOKS : groups
    BOOKS ||--o{ LOANS : borrowed_in
    USERS ||--o{ LOANS : member_id
    USERS ||--o{ LOANS : processed_by
    USERS ||--o{ ACTIVITY_LOGS : writes
    USERS ||--o{ BACKUPS : creates
    LOANS ||--o{ SANCTIONS : causes
    USERS ||--o{ SANCTIONS : member_id
    USERS ||--o{ SANCTIONS : processed_by
    CATEGORIES ||--o{ BOOK_PROCUREMENTS : requested_for
    USERS ||--o{ BOOK_PROCUREMENTS : proposed_by
    USERS ||--o{ BOOK_PROCUREMENTS : approved_by
    USERS ||--o{ BOOK_PROCUREMENTS : rejected_by
    USERS ||--|| LOGIN_OTP_TOKENS : owns

    USERS ||--o{ USERS : deleted_by
    USERS ||--o{ ROLES : deleted_by
    USERS ||--o{ PERMISSIONS : deleted_by
    USERS ||--o{ CATEGORIES : deleted_by
    USERS ||--o{ BOOKS : deleted_by
    USERS ||--o{ LOANS : deleted_by
    USERS ||--o{ SETTINGS : deleted_by
    USERS ||--o{ ACTIVITY_LOGS : deleted_by
    USERS ||--o{ BACKUPS : deleted_by
    USERS ||--o{ SANCTIONS : deleted_by
    USERS ||--o{ BOOK_PROCUREMENTS : deleted_by
```

## Catatan Penting

- `sessions.user_id` hanya `index`, bukan foreign key.
- `users.email` saat ini **tidak unique** karena unique email sudah dihapus pada migrasi `2026_04_09_140000_allow_duplicate_user_emails.php`.
- `books.barcode` **tidak masuk ERD** karena fitur barcode sudah dihapus pada migrasi `2026_04_18_000000_remove_scan_barcode_feature.php`.
- `loans.member_id` sekarang nullable, dan ada kolom tambahan `borrower_name`.
- `login_otp_tokens.user_id` adalah `PK` sekaligus `FK` ke `users.id`.
- Hampir semua tabel utama punya kolom audit soft delete:
  `delete`, `deleted_at`, `deleted_by`, `deleted_ip`.

## Relasi Inti Yang Biasanya Digambar Besar

- `roles.id` -> `users.role_id`
- `roles.id` -> `permission_role.role_id`
- `permissions.id` -> `permission_role.permission_id`
- `categories.id` -> `books.category_id`
- `books.id` -> `loans.book_id`
- `users.id` -> `loans.member_id`
- `users.id` -> `loans.processed_by`
- `loans.id` -> `sanctions.loan_id`
- `users.id` -> `sanctions.member_id`
- `users.id` -> `sanctions.processed_by`
- `categories.id` -> `book_procurements.category_id`
- `users.id` -> `book_procurements.proposed_by`
- `users.id` -> `book_procurements.approved_by`
- `users.id` -> `book_procurements.rejected_by`
- `users.id` -> `login_otp_tokens.user_id`

