# Modul Manajemen Pesanan & Approval Estimasi - SRS v4.1

## Implementasi Selesai

### 1. Controller yang Dibuat

#### `application/controllers/Order.php` (Workshop Owner)
- **index()**: Dashboard dengan statistik pesanan
- **bookings()**: Daftar semua pesanan dengan filter
- **detail($id)**: Detail pesanan dengan panel aksi
- **accept($id)**: Terima pesanan (Pending → Accepted)
- **reject($id)**: Tolak pesanan (Pending/Accepted → Cancelled)
- **start_processing($id)**: Mulai pengerjaan (Accepted → Processed)
- **add_finding($id)**: Tambah temuan & minta approval (UC-WRK-08)
- **complete($id)**: Selesaikan pesanan (Processed → Completed)
- **handle_timeout($id, $action)**: Handle timeout 48 jam (BR-80)

#### `application/controllers/Booking_management.php` (User/Customer)
- **index()**: Dashboard pesanan user
- **bookings()**: Riwayat pesanan dengan filter
- **detail($id)**: Detail dengan status tracking
- **approve_additional($id)**: Setujui tambahan (approval_status: pending → approved)
- **reject_additional($id)**: Tolak tambahan (approval_status: pending → rejected)
- **cancel($id)**: Batalkan pesanan (BR-62)
- **reschedule($id)**: Ubah jadwal (BR-63)
- **pending_approvals_count()**: API untuk notifikasi badge

### 2. Model Methods Ditambahkan ke `Booking_model.php`

#### Workshop Management
- `get_workshop_bookings($workshop_id, $filters)`
- `get_workshop_booking_stats($workshop_id)`

#### User Management
- `get_user_booking_stats($user_id)`
- `get_user_pending_approvals($user_id)`
- `count_user_pending_approvals($user_id)`

#### Approval Management
- `get_booking_approvals($booking_id, $status)`
- `create_approval($data)`
- `update_approval($approval_id, $data)`
- `update_approval_status($booking_id, $status)`

#### Activity Logging (Audit Trail)
- `log_activity($booking_id, $action, $description, $user_id)`
- `get_booking_activity_logs($booking_id)`

#### Slot Management
- `release_booking_slot($booking)`

### 3. Views Dibuat

#### Workshop Owner Views
- `application/views/workshop/orders/index.php`: Dashboard statistik
- `application/views/workshop/orders/detail.php`: Detail dengan state-based actions

#### User Views
- `application/views/user/bookings/detail.php`: Detail dengan approval response UI

### 4. State Lifecycle (SRS v4.0 Diagram)

```
┌─────────┐     ┌───────────┐     ┌───────────┐
│ PENDING │────▶│ ACCEPTED  │────▶│ PROCESSED │
└─────────┘     └───────────┘     └───────────┘
     │                │                  │
     │                │                  │ [approval_status = pending]
     │                │                  ▼
     │                │           ┌──────────────────┐
     │                │           │ waiting_approval │
     │                │           └──────────────────┘
     │                │                  │
     │                │                  │ [user approves/rejects or timeout]
     │                │                  ▼
     │                │            ┌───────────┐
     │                └───────────▶│ PROCESSED │
     │                             └───────────┘
     │                                  │
     ▼                                  ▼
┌───────────┐                    ┌───────────┐
│ CANCELLED │                    │ COMPLETED │
└───────────┘                    └───────────┘
```

### 5. Approval Flow (UC-WRK-08, BR-78~80)

1. **Workshop menambahkan temuan** → `booking_approvals` record dibuat
2. **approval_status** di `bookings` diubah ke `pending`
3. **User menerima notifikasi** dengan ringkasan:
   - Deskripsi temuan
   - Sparepart yang diperlukan
   - Biaya tambahan
   - Total baru
   - Waktu tersisa (48 jam countdown)
4. **User merespons**:
   - **Setuju**: approval_status → `approved`, total diupdate
   - **Tolak**: approval_status → `rejected`/`none`, lanjut pekerjaan awal
5. **Timeout (48 jam)**:
   - Workshop dapat "Lanjutkan" (tanpa tambahan) atau "Batalkan Tambahan"

### 6. Audit Log (activity_logs)

Setiap perubahan status dan approval dicatat:
- `accepted`: Pesanan diterima
- `rejected`: Pesanan ditolak
- `processed`: Pekerjaan dimulai
- `approval_requested`: Permintaan approval dibuat
- `approval_approved`: User menyetujui tambahan
- `approval_rejected`: User menolak tambahan
- `approval_timeout`: Timeout 48 jam
- `completed`: Pesanan selesai
- `cancelled_by_user`: Pembatalan oleh user
- `rescheduled`: Perubahan jadwal

### 7. Keamanan

- **Role checking**: Semua action divalidasi berdasarkan role
- **Ownership verification**: Workshop hanya bisa akses booking milik bengkelnya
- **State validation**: Transisi status dicek sesuai diagram state
- **CSRF protection**: Semua form menggunakan token CSRF
- **Input validation**: Form validation untuk semua input

### 8. Database Tables Required

```sql
-- bookings table (existing, tambah kolom)
ALTER TABLE bookings ADD COLUMN approval_status VARCHAR(20) DEFAULT 'none';
ALTER TABLE bookings ADD COLUMN final_cost DECIMAL(10,2);
ALTER TABLE bookings ADD COLUMN additional_notes TEXT;
ALTER TABLE bookings ADD COLUMN completed_at DATETIME;

-- booking_approvals table (new)
CREATE TABLE booking_approvals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    requested_by INT NOT NULL,
    description TEXT NOT NULL,
    additional_amount DECIMAL(10,2) NOT NULL,
    spareparts VARCHAR(500),
    status VARCHAR(20) DEFAULT 'pending',
    expires_at DATETIME,
    responded_at DATETIME,
    response_note TEXT,
    created_at DATETIME,
    FOREIGN KEY (booking_id) REFERENCES bookings(id),
    FOREIGN KEY (requested_by) REFERENCES users(id)
);

-- booking_activity_logs table (new)
CREATE TABLE booking_activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    description TEXT,
    user_id INT,
    ip_address VARCHAR(45),
    created_at DATETIME,
    FOREIGN KEY (booking_id) REFERENCES bookings(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### 9. Routes Required (config/routes.php)

```php
// Workshop Owner
$route['order'] = 'order/index';
$route['order/bookings'] = 'order/bookings';
$route['order/detail/(:num)'] = 'order/detail/$1';
$route['order/accept/(:num)'] = 'order/accept/$1';
$route['order/reject/(:num)'] = 'order/reject/$1';
$route['order/start_processing/(:num)'] = 'order/start_processing/$1';
$route['order/add_finding/(:num)'] = 'order/add_finding/$1';
$route['order/complete/(:num)'] = 'order/complete/$1';
$route['order/handle_timeout/(:num)/(:any)'] = 'order/handle_timeout/$1/$2';

// User/Customer
$route['booking_management'] = 'booking_management/index';
$route['booking_management/bookings'] = 'booking_management/bookings';
$route['booking_management/detail/(:num)'] = 'booking_management/detail/$1';
$route['booking_management/approve_additional/(:num)'] = 'booking_management/approve_additional/$1';
$route['booking_management/reject_additional/(:num)'] = 'booking_management/reject_additional/$1';
$route['booking_management/cancel/(:num)'] = 'booking_management/cancel/$1';
$route['booking_management/reschedule/(:num)'] = 'booking_management/reschedule/$1';
$route['booking_management/pending_approvals_count'] = 'booking_management/pending_approvals_count';
```

## Deliverable Checklist

✅ Controller Order (Workshop Owner) - Complete  
✅ Controller Booking_management (User) - Complete  
✅ Model methods extended - Complete  
✅ View workshop/orders/index.php - Complete  
✅ View workshop/orders/detail.php - Complete  
✅ View user/bookings/detail.php - Complete  
✅ State transition validation - Complete  
✅ Approval flow with timeout - Complete  
✅ Activity logging - Complete  
✅ Role-based access control - Complete  

