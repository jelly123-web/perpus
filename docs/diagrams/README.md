# Diagram Sistem Perpustakaan

File utama draw.io:

- [perpus-system-diagrams.drawio](/abs/path/C:/Users/HP/Downloads/laravel/perpustakaan%20sekolah/perpus/docs/diagrams/perpus-system-diagrams.drawio)

Isi file draw.io:

- `Context Diagram`
- `DFD Level 1`
- `DFD Level 2 - Peminjaman`
- `Use Case Diagram`
- `Activity Diagram - Peminjaman`
- `Class Diagram`
- `Sequence Diagram - Peminjaman`

Sumber acuan yang dipakai:

- Route aplikasi: [routes/web.php](/abs/path/C:/Users/HP/Downloads/laravel/perpustakaan%20sekolah/perpus/routes/web.php)
- Model: [app/Models](/abs/path/C:/Users/HP/Downloads/laravel/perpustakaan%20sekolah/perpus/app/Models)
- Migrasi database: [database/migrations](/abs/path/C:/Users/HP/Downloads/laravel/perpustakaan%20sekolah/perpus/database/migrations)
- Seeder role dan permission: [database/seeders/DatabaseSeeder.php](/abs/path/C:/Users/HP/Downloads/laravel/perpustakaan%20sekolah/perpus/database/seeders/DatabaseSeeder.php)

Catatan:

- Diagram dibuat agar konsisten dengan fitur yang benar-benar ada di kode dan tabel database.
- `DFD Level 2` difokuskan ke proses peminjaman, pengembalian, dan sanksi karena itu alur transaksi utama sistem.
- `Class Diagram` memakai kelas model/domain utama Laravel, bukan seluruh controller.
