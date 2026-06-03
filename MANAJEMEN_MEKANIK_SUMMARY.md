# Modul Manajemen Mekanik & Penugasan - SRS v4.1

## Scope
- SRS Modul 9 (FR-MEC-01~03)
- UC-WRK-07: Penugasan Mekanik
- BR-76: Overlapping Schedule Prevention
- BR-77: Optional Assignment

## Deliverables

### 1. Model: `/workspace/application/models/Mechanic_model.php` (532 baris)

**CRUD Operations:**
- `create_mechanic()` - Tambah mekanik baru dengan user_id
- `update_mechanic()` - Update data mekanik
- `find_by_id()` - Get mechanic detail
- `get_by_workshop()` - List mechanics per bengkel
- `delete_mechanic()` - Soft delete

**Assignment (UC-WRK-07):**
- `assign_mechanics()` - Assign 1-3 mekanik ke booking
- `check_mechanic_availability()` - Cek bentrok jadwal (BR-76)
- `get_booking_mechanics()` - Get assigned mechanics
- `remove_mechanic()` - Remove from booking

**Productivity (FR-MEC-03):**
- `get_productivity_report()` - Report with completed count & avg rating
- `get_mechanic_stats_summary()` - Summary statistics
- `get_mechanic_bookings()` - Booking history per mechanic

### 2. Controller: `/workspace/application/controllers/Mechanic.php` (530 baris)

**Routes:**
```php
mechanic/index              - Dashboard list
mechanic/create             - Form tambah
mechanic/store              - POST create
mechanic/edit/$id           - Form edit
mechanic/update/$id         - POST update
mechanic/delete/$id         - POST delete
mechanic/toggle_availability/$id - Toggle aktif/non-aktif
mechanic/detail/$id         - Detail + booking history
mechanic/productivity       - Laporan produktivitas dengan date filter
mechanic/assign_to_booking  - POST assign mechanics to booking
mechanic/remove_from_booking - POST remove mechanic
mechanic/get_available_for_booking/$id - AJAX get available mechanics
```

### 3. Views

**`/workspace/application/views/workshop/mechanics/index.php`** (205 baris)
- Statistics cards (Total, Available, Unavailable)
- DataTables dengan search/sort
- Actions: View, Edit, Toggle, Delete

**`/workspace/application/views/workshop/mechanics/form.php`** (222 baris)
- User selection (untuk create)
- Multi-checkbox spesialisasi (mesin, kelistrikan, body, ban, oli, ac)
- Experience years input
- Certification textarea
- Availability toggle switch

**`/workspace/application/views/workshop/mechanics/detail.php`** (177 baris)
- Mechanic profile card
- Stats: Total bookings, Completed
- Booking history table dengan filter status

**`/workspace/application/views/workshop/mechanics/productivity.php`** (202 baris)
- Date range filter
- Summary stats cards
- Productivity table: Total, Selesai, Rating, Review count
- Export Excel & Print buttons (DataTables Buttons)

**`/workspace/application/views/workshop/orders/detail.php`** (Updated)
- Added "Tugaskan Mekanik" button (visible when can_process=true)
- Modal assign mechanic dengan:
  - Auto-load available mechanics via AJAX
  - Conflict detection display (bentrok dengan booking X)
  - Max 3 mechanics validation
  - Notes field untuk instruksi

## Database Schema (Existing)

```sql
-- mechanics table
CREATE TABLE mechanics (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    workshop_id INT UNSIGNED,
    specialization JSON,
    experience_years INT UNSIGNED DEFAULT 0,
    certification TEXT,
    rating_avg DECIMAL(3,2) DEFAULT 0,
    total_reviews INT UNSIGNED DEFAULT 0,
    is_available TINYINT(1) DEFAULT 1,
    is_deleted TINYINT(1) DEFAULT 0,
    ...
);

-- booking_mechanics junction table
CREATE TABLE booking_mechanics (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id INT UNSIGNED NOT NULL,
    mechanic_id INT UNSIGNED NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assigned_by INT UNSIGNED,
    notes TEXT,
    is_deleted TINYINT(1) DEFAULT 0,
    UNIQUE KEY unique_booking_mechanic (booking_id, mechanic_id),
    ...
);
```

## Business Rules Implementation

### BR-76: No Overlapping Schedule
```php
// In Mechanic_model::check_mechanic_availability()
foreach ($bookings as $booking) {
    $existing_start = strtotime($date . ' ' . $booking['scheduled_time']);
    $existing_end = $existing_start + (($booking['estimated_duration'] ?? 60) * 60);
    
    // Check overlap
    if (!($new_end <= $existing_start || $new_start >= $existing_end)) {
        $conflicts[] = [...];
    }
}
```

### BR-77: Optional Assignment
- Assignment dapat kosong (pesanan tetap bisa diproses tanpa mekanik)
- Validasi hanya jika ada mechanic_ids yang dipilih

## Integration Points

1. **Order Controller** - Already has state transitions
2. **Booking Model** - Needs `log_activity()` method for audit trail
3. **Workshop_Controller** - Base controller untuk auth check

## Testing Checklist

- [ ] CRUD mekanik (Create, Read, Update, Delete)
- [ ] Toggle availability
- [ ] Assign 1-3 mekanik ke booking
- [ ] Conflict detection (bentrok jadwal)
- [ ] Productivity report dengan date filter
- [ ] Export Excel functionality
- [ ] Mechanic detail dengan booking history

