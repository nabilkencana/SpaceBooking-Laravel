# Backend Schema Rules

## Database
- Users table uses `username` (not `name`/`email`) for authentication
- `detail_reservasis` table is NOT used — merged into `reservasis` table
- `reservasis` table has composite index on (`space_id`, `tanggal_reservasi`)
- Foreign keys: `restrictOnDelete` for spaces and reservasis, `cascadeOnDelete` for user profiles
- `harga_per_jam` in reservasis is a snapshot copy from spaces table at time of booking
