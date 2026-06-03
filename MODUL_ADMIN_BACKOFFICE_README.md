# Modul Admin Dashboard, Back Office, dan System Settings
## SRS v4.1 - Prompt #14

**Scope:** SRS UC-ADM-06, Saran Reviewer #3, #6  
**Business Rules:** BR-84~85 (Template Management)  
**Fitur Utama:** Dashboard, User Management, Workshop Verification, Review Moderation, System Settings, Activity Logs

---

## Daftar Isi

1. [Overview](#overview)
2. [Database Schema](#database-schema)
3. [File Structure](#file-structure)
4. [Features](#features)
5. [API Endpoints](#api-endpoints)
6. [Pseudocode Cron Job](#pseudocode-cron-job)
7. [Testing Guide](#testing-guide)

---

## Overview

Modul ini menyediakan Admin Dashboard dan Back Office lengkap untuk manajemen sistem Bengkel Terdekat. Fitur mencakup:

- **Dashboard Statistik**: Real-time statistics dengan Chart.js visualization
- **User Management**: DataTables server-side untuk performa optimal
- **Workshop Management**: Verifikasi bengkel, featured workshops (Saran Reviewer #6)
- **Review Moderation**: Panel khusus untuk review pending (auto-flag jika report_count >= 3)
- **System Settings**: Konfigurasi global editable admin
- **Activity Logs**: Audit trail lengkap untuk compliance

---

## Database Schema

### Tabel Baru: `activity_logs`

```sql
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,                    -- Actor user ID
    workshop_id INT NULL,                -- Workshop involved
    action_type VARCHAR(50) NOT NULL,    -- e.g., USER_DEACTIVATE
    action_description TEXT NOT NULL,
    target_user_id INT NULL,             -- Target of action
    target_workshop_id INT NULL,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_action_type (action_type),
    INDEX idx_created_at (created_at)
);
```

### Kolom Baru di `workshops`

```sql
ALTER TABLE workshops ADD COLUMN is_featured TINYINT(1) DEFAULT 0;
ALTER TABLE workshops ADD COLUMN verified_at TIMESTAMP NULL;
ALTER TABLE workshops ADD COLUMN business_license VARCHAR(255) NULL;
ALTER TABLE workshops ADD COLUMN certification_doc VARCHAR(255) NULL;
```

### Kolom Baru di `reviews`

```sql
ALTER TABLE reviews ADD COLUMN moderation_status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved';
ALTER TABLE reviews ADD COLUMN moderation_notes TEXT NULL;
ALTER TABLE reviews ADD COLUMN moderated_by INT NULL;
ALTER TABLE reviews ADD COLUMN moderated_at TIMESTAMP NULL;
ALTER TABLE reviews ADD COLUMN report_count INT DEFAULT 0;
```

### System Settings Baru

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `radius_darurat` | decimal | 5 | Emergency radius (km) |
| `same_day_booking` | boolean | 1 | Enable same-day booking |
| `moderasi_review_ketat` | boolean | 0 | Strict review moderation |
| `invoice_tax_rate` | decimal | 11 | Tax rate percentage |
| `invoice_due_days` | integer | 7 | Invoice due days |
| `max_upload_size_mb` | integer | 5 | Max upload size (MB) |
| `allowed_file_types` | string | jpg,jpeg,png,pdf | Allowed extensions |
| `featured_workshop_limit` | integer | 10 | Max featured workshops |

---

## File Structure

```
/workspace
├── database/migrations/
│   └── 20240108_add_admin_backoffice_tables.sql
│
├── application/
│   ├── controllers/
│   │   └── Admin.php                    (Updated: 425 lines)
│   │
│   ├── models/
│   │   ├── Admin_model.php              (New: 718 lines)
│   │   └── System_setting_model.php     (New: 322 lines)
│   │
│   └── views/admin/
│       ├── dashboard.php                (Updated: 256 lines)
│       ├── users.php                    (DataTables view)
│       ├── workshops.php                (DataTables view)
│       ├── view_user.php                (User detail)
│       ├── view_workshop.php            (Workshop detail)
│       ├── pending_verification.php     (Verification queue)
│       ├── review_moderation.php        (Moderation panel)
│       ├── settings.php                 (System settings form)
│       └── activity_logs.php            (Audit trail view)
```

---

## Features

### 1. Dashboard Admin (UC-ADM-06)

**Statistics Cards:**
- Total Users (breakdown by role)
- Total Bengkel (verified, pending, featured)
- Booking Hari Ini (by status)
- Emergency Requests Aktif (last 24h)
- Review Pending Moderasi
- Revenue Hari Ini

**Charts (Chart.js):**
- Trend Booking (7 hari terakhir) - Line chart
- Registrasi Bengkel Baru (7 hari) - Bar chart

**Quick Actions:**
- Links to Users, Workshops, Review, Settings

### 2. Manajemen User (DataTables Server-Side)

**Features:**
- Server-side processing untuk performa (ribuan records)
- Searchable columns: name, email, role
- Sortable columns
- Role filter dropdown
- Actions: View, Activate/Deactivate, Reset Password, Delete

**Endpoint:**
```
GET  /admin/users              - View page
GET  /admin/users_data         - DataTables AJAX
POST /admin/reset_password/:id - Reset password
GET  /admin/activate_user/:id  - Activate
GET  /admin/deactivate_user/:id- Deactivate
GET  /admin/delete_user/:id    - Soft delete
```

### 3. Manajemen Bengkel

**Features:**
- DataTables server-side
- Filter by verification status (pending/verified)
- View workshop details
- Verify workshop (sets `verified_at`)
- Set featured status (Saran Reviewer #6)
- Upload business license & certification docs

**Endpoint:**
```
GET  /admin/workshops                  - View page
GET  /admin/workshops_data             - DataTables AJAX
GET  /admin/view_workshop/:id          - Detail
GET  /admin/verify_workshop/:id        - Verify
POST /admin/set_featured/:id           - Set featured
GET  /admin/pending_verification       - Pending queue
```

### 4. Moderasi Review

**Auto-Flag Logic:**
```php
// Reviews with report_count >= 3 automatically flagged
$this->db->where('report_count >=', 3)
    ->update('reviews', ['moderation_status' => 'pending']);
```

**Strict Moderation Mode:**
If `moderasi_review_ketat` = TRUE in system_settings:
- ALL new reviews require approval before display

**Actions:**
- Approve with notes
- Reject with reason (required)

**Endpoint:**
```
GET  /admin/review_moderation          - Moderation panel
GET  /admin/pending_reviews_data       - AJAX data
POST /admin/approve_review/:id         - Approve
POST /admin/reject_review/:id          - Reject
```

### 5. System Settings

**Editable Settings:**
All settings from `system_settings` table with type casting:
- Integer fields (e.g., `reminder_interval_km`)
- Decimal fields (e.g., `radius_darurat`, `invoice_tax_rate`)
- Boolean toggles (e.g., `same_day_booking`)
- String fields (e.g., `allowed_file_types`)

**Audit Logging:**
Every setting change is logged to `activity_logs`.

### 6. Activity Logs (Audit Trail)

**Filters:**
- By user (actor)
- By workshop
- By action type
- Date range

**Common Action Types:**
```
USER_CREATE, USER_UPDATE, USER_ACTIVATE, USER_DEACTIVATE, USER_DELETE
USER_PASSWORD_RESET
WORKSHOP_CREATE, WORKSHOP_UPDATE, WORKSHOP_VERIFY, WORKSHOP_FEATURE
REVIEW_APPROVE, REVIEW_REJECT
SYSTEM_SETTING_UPDATE
BOOKING_CANCEL, BOOKING_REFUND
EMERGENCY_DISPATCH, EMERGENCY_COMPLETE
```

---

## API Endpoints

### Dashboard
```
GET /admin/dashboard
```

### Users
```
GET  /admin/users
GET  /admin/users_data?draw=1&start=0&length=50&role_filter=customer
GET  /admin/view_user/:id
POST /admin/reset_password/:id { new_password: "..." }
GET  /admin/activate_user/:id
GET  /admin/deactivate_user/:id
GET  /admin/delete_user/:id
```

### Workshops
```
GET  /admin/workshops
GET  /admin/workshops_data?verification_status=pending
GET  /admin/view_workshop/:id
GET  /admin/verify_workshop/:id
POST /admin/set_featured/:id { is_featured: true }
GET  /admin/pending_verification
```

### Review Moderation
```
GET  /admin/review_moderation
GET  /admin/pending_reviews_data?draw=1&start=0
POST /admin/approve_review/:id { notes: "..." }
POST /admin/reject_review/:id { notes: "Reason" }
```

### System Settings
```
GET  /admin/settings
POST /admin/settings { settings: {...}, types: {...} }
```

### Activity Logs
```
GET /admin/activity_logs?user_id=1&action_type=USER_DELETE&date_from=2024-01-01
```

---

## Pseudocode Cron Job

### Daily Reminder Servis Berkala (UC-USR-11)

```php
/**
 * Cron Job: Daily Service Reminder
 * Schedule: 00:00 every day
 * 
 * Logic:
 * - Check all vehicles where reminder_enabled = 1
 * - Calculate if service needed based on:
 *   a) KM threshold: last_service_km + avg_daily_km * days_since_service > threshold_km
 *   b) Time threshold: months since last service > threshold_months
 * - Respect BR-73: max 1 reminder per 7 days per vehicle
 * - Respect BR-74: skip if reminder_enabled = 0
 * - Snooze: skip if reminder_snoozed_until > today
 */

function daily_service_reminder() {
    $settings = get_system_settings();
    $threshold_km = $settings['reminder_interval_km'];      // default 5000
    $threshold_months = $settings['reminder_interval_months']; // default 6
    $today = date('Y-m-d');
    $seven_days_ago = date('Y-m-d', strtotime('-7 days'));
    
    // Get all eligible vehicles
    $vehicles = db_query("
        SELECT v.*, u.name, u.email, u.default_city
        FROM vehicles v
        JOIN users u ON v.user_id = u.id
        WHERE v.reminder_enabled = 1
          AND (v.reminder_snoozed_until IS NULL OR v.reminder_snoozed_until < ?)
          AND v.is_deleted = 0
    ", [$today]);
    
    foreach ($vehicles as $vehicle) {
        // Check BR-73: max 1 reminder per 7 days
        $last_reminder = get_last_reminder_date($vehicle->id);
        if ($last_reminder && $last_reminder > $seven_days_ago) {
            continue; // Skip, already reminded recently
        }
        
        $needs_reminder = false;
        $reason = [];
        
        // Check KM threshold
        if ($vehicle->last_service_km > 0) {
            $days_since_service = days_between($vehicle->last_service_date, $today);
            $avg_daily_km = estimate_daily_km($vehicle->id); // Based on historical bookings
            $estimated_current_km = $vehicle->last_service_km + ($avg_daily_km * $days_since_service);
            $km_since_service = $estimated_current_km - $vehicle->last_service_km;
            
            if ($km_since_service >= $threshold_km) {
                $needs_reminder = true;
                $reason[] = "KM threshold reached ({$km_since_service} km)";
            }
        }
        
        // Check time threshold
        if ($vehicle->last_service_date) {
            $months_since_service = months_between($vehicle->last_service_date, $today);
            if ($months_since_service >= $threshold_months) {
                $needs_reminder = true;
                $reason[] = "Time threshold reached ({$months_since_service} months)";
            }
        }
        
        if ($needs_reminder) {
            // Get nearest workshops
            $workshops = get_nearest_workshops($vehicle->user_id, $settings['radius_darurat']);
            
            // Send email
            send_notification(
                $vehicle->user_id,
                'service_reminder',
                [
                    'nama_pengguna' => $vehicle->name,
                    'kendaraan' => $vehicle->vehicle_name,
                    'km_terakhir' => $vehicle->last_service_km,
                    'km_estimasi' => $estimated_current_km ?? 0,
                    'tanggal_servis' => $vehicle->last_service_date,
                    'rekomendasi_bengkel' => format_workshops_list($workshops)
                ]
            );
            
            // Log reminder
            log_reminder_sent($vehicle->id, $today, implode(', ', $reason));
        }
    }
}

/**
 * Helper: Estimate daily KM based on booking history
 */
function estimate_daily_km($vehicle_id) {
    $bookings = get_bookings_for_vehicle($vehicle_id, limit=10);
    
    if (count($bookings) < 2) {
        return 30; // Default assumption: 30 km/day
    }
    
    $total_km = 0;
    $total_days = 0;
    
    for ($i = 1; $i < count($bookings); $i++) {
        $km_diff = $bookings[$i]->odometer - $bookings[$i-1]->odometer;
        $days_diff = days_between($bookings[$i-1]->date, $bookings[$i]->date);
        
        if ($days_diff > 0 && $km_diff > 0) {
            $total_km += $km_diff;
            $total_days += $days_diff;
        }
    }
    
    return $total_days > 0 ? round($total_km / $total_days) : 30;
}

/**
 * Helper: Get nearest workshops based on user's default city
 */
function get_nearest_workshops($user_id, $radius_km) {
    $user = get_user($user_id);
    $city = $user->default_city ?: 'Jakarta';
    
    // Get workshops in same city, sorted by rating
    $workshops = db_query("
        SELECT w.*, AVG(r.rating) as avg_rating
        FROM workshops w
        LEFT JOIN reviews r ON w.id = r.workshop_id AND r.is_deleted = 0
        WHERE w.city = ?
          AND w.is_active = 1
          AND w.is_deleted = 0
          AND w.verified_at IS NOT NULL
        GROUP BY w.id
        ORDER BY avg_rating DESC, w.is_featured DESC
        LIMIT 5
    ", [$city]);
    
    return $workshops;
}
```

### Cron Setup (Linux crontab)

```bash
# Edit crontab
crontab -e

# Add daily job at midnight
0 0 * * * /usr/bin/php /path/to/project/index.php cron daily_reminder >> /var/log/cron_reminder.log 2>&1
```

### CodeIgniter CLI Command

```php
// application/controllers/Cron.php
class Cron extends CI_Controller {
    
    public function daily_reminder() {
        // Verify CLI request
        if (!$this->input->is_cli_request()) {
            show_error('CLI only', 403);
        }
        
        $this->load->model('notification_model');
        $result = $this->notification_model->send_daily_service_reminders();
        
        echo "Sent {$result} reminders\n";
    }
}
```

---

## Testing Guide

### 1. Dashboard Statistics

```bash
# Login as admin
curl -X POST http://localhost/bengkel/auth/login \
  -d "email=admin@example.com&password=admin123"

# Access dashboard
curl http://localhost/bengkel/admin/dashboard
```

**Expected:**
- Stats cards show correct counts
- Charts render with 7-day data
- Quick action links work

### 2. User Management

```bash
# Test DataTables endpoint
curl "http://localhost/bengkel/admin/users_data?draw=1&start=0&length=10"

# Test reset password
curl -X POST http://localhost/bengkel/admin/reset_password/5 \
  -d "new_password=test123456"
```

**Expected:**
- JSON response with user data
- Password reset success message
- Activity logged

### 3. Workshop Verification

```bash
# Get pending workshops
curl http://localhost/bengkel/admin/pending_verification

# Verify workshop
curl http://localhost/bengkel/admin/verify_workshop/3

# Set featured
curl -X POST http://localhost/bengkel/admin/set_featured/3 \
  -d "is_featured=1"
```

**Expected:**
- Workshop shows as verified
- Featured badge appears
- Activity logged

### 4. Review Moderation

```bash
# Create test review with reports
# (Use web interface or direct DB insert)

# Check moderation panel
curl http://localhost/bengkel/admin/review_moderation

# Approve review
curl -X POST http://localhost/bengkel/admin/approve_review/10 \
  -d "notes=Looks good"
```

**Expected:**
- Reviews with report_count >= 3 appear
- Approval updates status
- Notes saved

### 5. System Settings

```bash
# Update setting
curl -X POST http://localhost/bengkel/admin/settings \
  -d "settings[radius_darurat]=10&types[radius_darurat]=decimal"
```

**Expected:**
- Setting updated in database
- Change logged in activity_logs

### 6. Activity Logs

```bash
# Filter logs
curl "http://localhost/bengkel/admin/activity_logs?action_type=USER_DELETE&date_from=2024-01-01"
```

**Expected:**
- Filtered results displayed
- Pagination works

---

## Security Considerations

1. **Authorization**: All endpoints check `Admin_Controller` for admin role
2. **Audit Trail**: Every admin action logged with IP and user agent
3. **Password Reset**: Requires minimum 6 characters
4. **Self-Protection**: Cannot delete own admin account
5. **Input Validation**: All POST data validated/sanitized

---

## Performance Optimization

1. **DataTables Server-Side**: Handles 10,000+ records efficiently
2. **Indexed Columns**: All filter/search columns indexed
3. **Caching**: System settings cached in memory
4. **Pagination**: All lists paginated (default 50 items)

---

## Troubleshooting

### Dashboard charts not showing
- Check Chart.js CDN is accessible
- Verify `$bookings_trend` and `$workshop_trend` arrays not empty

### DataTables not loading
- Check jQuery and DataTables JS loaded
- Verify `/admin/users_data` returns valid JSON
- Check browser console for errors

### Activity logs empty
- Ensure `activity_logs` table exists (run migration)
- Check triggers are logging actions

### Settings not saving
- Verify POST data structure matches expected format
- Check `types` array included for type casting

---

## References

- SRS v4.1 Modul 10 (FR-NOT-02~04)
- Use Case UC-ADM-06: Admin Dashboard
- Saran Reviewer #3: Enhanced back office
- Saran Reviewer #6: Featured workshops for promotion
- Business Rules BR-84~85: Template management

---

**Version:** 4.1  
**Last Updated:** 2024-01-08  
**Author:** Development Team
