# MODUL PENAGIHAN DAN LAPORAN - README
## Bengkel Terdekat v4.1

## Ringkasan Modul

Modul Penagihan dan Laporan menyediakan fitur lengkap untuk:
- **Kalkulasi Tagihan**: Otomatis generate tagihan dari booking completed
- **Cetak PDF Invoice**: Template profesional dengan logo bengkel
- **Laporan Transaksi**: Workshop owner lihat pemasukan per rentang tanggal
- **Export Excel/CSV**: Download laporan untuk analisis lebih lanjut
- **Admin Dashboard**: Laporan global aggregate semua bengkel

---

## Struktur File

```
application/
├── controllers/
│   ├── Billing.php          # Controller workshop owner (tagihan & invoice)
│   └── Report.php           # Controller admin (laporan global)
├── models/
│   └── Billing_model.php    # Model billing calculation & reporting
└── views/
    ├── workshop/
    │   └── billing/
    │       ├── invoices.php         # Daftar invoice
    │       ├── invoice_detail.php   # Detail invoice
    │       ├── invoice_pdf.php      # Template PDF invoice
    │       ├── payment_form.php     # Form catat pembayaran
    │       └── report.php           # Laporan transaksi workshop
    └── admin/
        └── report/
            ├── global.php           # Laporan global semua bengkel
            └── workshop_detail.php  # Detail per bengkel

database/
└── migrations/
    └── 20240107_add_billing_tables.sql
```

---

## Database Schema

### Tabel Baru

#### 1. `booking_service_items` - Detail layanan per booking
```sql
- id, booking_id, service_name, description, quantity, unit_price, subtotal
```

#### 2. `booking_sparepart_items` - Detail sparepart per booking
```sql
- id, booking_id, sparepart_name, part_number, description, quantity, unit_price, subtotal
```

#### 3. `booking_additional_charges` - Biaya tambahan
```sql
- id, booking_id, charge_name, description, amount, is_approved, approval_id
```

#### 4. `invoices` - Invoice formal
```sql
- id, invoice_number, booking_id, workshop_id, user_id
- issue_date, due_date
- service_cost, sparepart_cost, additional_cost
- discount_amount, tax_amount, tax_rate, total_amount
- payment_status (unpaid/paid/partial), paid_amount, paid_at
- payment_method, payment_note, notes
```

#### 5. `invoice_payments` - Riwayat pembayaran (multiple payments)
```sql
- id, invoice_id, payment_date, amount, payment_method
- reference_number, notes, received_by
```

#### 6. `report_settings` - Pengaturan laporan
```sql
- id, workshop_id (NULL=global), setting_key, setting_value, setting_type
```

### Kolom Baru di `bookings`
```sql
- service_cost DECIMAL(12,2)
- sparepart_cost DECIMAL(12,2)
- additional_cost DECIMAL(12,2)
- final_total DECIMAL(12,2)
- payment_status ENUM('unpaid','paid','partial')
- invoice_number VARCHAR(50)
- invoiced_at DATETIME
- paid_at DATETIME
```

---

## Fitur Utama

### 1. Kalkulasi Tagihan Otomatis

**Total = Harga Layanan + Sparepart + Biaya Tambahan (jika approval disetujui)**

```php
// Usage di controller
$totals = $this->billing_model->calculate_booking_total($booking_id);
// Returns: ['service_cost' => X, 'sparepart_cost' => Y, 'additional_cost' => Z, 'final_total' => Total]
```

### 2. Generate Invoice dari Completed Booking

```php
$result = $this->billing_model->generate_invoice($booking_id, $workshop_id, $user_id);
/*
Returns:
[
    'success' => TRUE/FALSE,
    'invoice_id' => int,
    'message' => string,
    'invoice_number' => 'INV-20240115-0001'
]
*/
```

**Validasi:**
- Hanya booking dengan status `completed` yang bisa dibuatkan invoice
- Invoice number format: `{prefix}-{YYYYMMDD}-{XXXX}`
- Prefix configurable di report_settings (default: "INV")

### 3. Cetak PDF Invoice

Template PDF include:
- Logo & info bengkel
- Detail customer
- Detail servis (layanan, sparepart, biaya tambahan)
- Breakdown total (subtotal, pajak, diskon)
- Payment status badge
- Catatan & footer

**Usage:**
```php
// Controller
public function print_invoice($invoice_id)
{
    $data['invoice'] = $this->billing_model->get_invoice_detail($invoice_id);
    $data['service_items'] = $this->billing_model->get_service_items($invoice->booking_id);
    $data['sparepart_items'] = $this->billing_model->get_sparepart_items($invoice->booking_id);
    $data['additional_charges'] = $this->billing_model->get_additional_charges($invoice->booking_id);
    
    $this->load->library('pdf'); // mPDF atau DomPDF
    $html = $this->load->view('workshop/billing/invoice_pdf', $data, TRUE);
    $this->pdf->loadHtml($html);
    $this->pdf->render();
    $this->pdf->stream('Invoice_' . $data['invoice']->invoice_number . '.pdf');
}
```

### 4. Laporan Transaksi Workshop Owner

**Filter:**
- Rentang tanggal (start_date, end_date)
- Status pembayaran (unpaid/paid/partial/all)
- Search (invoice number, booking number, customer name)

**Summary Stats:**
- Total invoice count
- Gross revenue (total omzet)
- Paid revenue (sudah dibayar)
- Unpaid revenue (belum dibayar)
- Partial revenue (dibayar sebagian)

**Endpoint:**
```
GET /workshop/billing/report?start_date=2024-01-01&end_date=2024-01-31&status=paid
```

### 5. Export CSV/Excel

**Format CSV columns:**
```
Invoice Number, Issue Date, Due Date, Total Amount, Paid Amount, 
Payment Status, Paid At, Booking Number, Scheduled Date, 
Customer Name, Customer Email, Customer Phone, Vehicle, Workshop Name
```

**Download URL:**
```
/workshop/billing/export_csv?start_date=2024-01-01&end_date=2024-01-31
```

### 6. Admin Global Report

**Aggregate data semua bengkel:**
- Total invoice across all workshops
- Total gross revenue
- Total paid/unpaid revenue
- Active workshops count
- Breakdown per workshop dengan % terbayar

**Endpoint:**
```
GET /admin/report?start_date=2024-01-01&end_date=2024-01-31&workshop_id=5
```

---

## Business Rules Implementation

| BR ID | Rule | Implementation |
|-------|------|----------------|
| BR-XX | Tagihan hanya untuk booking completed | Validasi di `generate_invoice()` |
| BR-XX | Invoice number unik per hari | Sequence reset harian, unique constraint DB |
| BR-XX | Multiple payments diperbolehkan | Tabel `invoice_payments` terpisah |
| BR-XX | Partial payment tracking | `payment_status` enum + `paid_amount` tracking |
| BR-XX | Pajak configurable | `report_settings.default_tax_rate` |
| BR-XX | Jatuh tempo configurable | `report_settings.invoice_due_days` |

---

## API Endpoints Workshop Owner

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/workshop/billing` | List invoices dengan filter |
| GET | `/workshop/billing/view/{id}` | Detail invoice |
| POST | `/workshop/billing/generate/{booking_id}` | Generate invoice dari booking |
| GET | `/workshop/billing/print_invoice/{id}` | Cetak PDF invoice |
| GET/POST | `/workshop/billing/record_payment/{id}` | Catat pembayaran |
| GET | `/workshop/billing/report` | Laporan transaksi |
| GET | `/workshop/billing/export_csv` | Export ke CSV |
| POST | `/workshop/billing/add_service_item` | AJAX: Tambah item layanan |
| POST | `/workshop/billing/add_sparepart_item` | AJAX: Tambah item sparepart |
| DELETE | `/workshop/billing/delete_service_item/{id}` | AJAX: Hapus item layanan |
| DELETE | `/workshop/billing/delete_sparepart_item/{id}` | AJAX: Hapus item sparepart |

## API Endpoints Admin

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/admin/report` | Laporan global semua bengkel |
| GET | `/admin/report/export_global_csv` | Export laporan global ke CSV |
| GET | `/admin/report/workshop_detail/{id}` | Detail transaksi bengkel spesifik |

---

## Cron Job: Reminder Servis Berkala (Pseudocode)

File: `scripts/service_reminder_cron.php`

```php
#!/usr/bin/php
<?php
/**
 * Cron job untuk reminder servis berkala
 * Dijalankan setiap hari pukul 00:00
 * 
 * Setup crontab:
 * 0 0 * * * /usr/bin/php /path/to/project/scripts/service_reminder_cron.php
 */

// Bootstrap CodeIgniter
require_once '/path/to/project/index.php';

class ServiceReminderCron {
    
    public function run()
    {
        echo "[" . date('Y-m-d H:i:s') . "] Starting service reminder cron...\n";
        
        $this->load->model('vehicle_model');
        $this->load->model('notification_model');
        $this->load->model('billing_model');
        
        // Get system settings
        $km_threshold = (int)$this->get_setting('reminder_interval_km', 5000);
        $month_threshold = (int)$this->get_setting('reminder_interval_months', 6);
        $max_per_week = (int)$this->get_setting('reminder_max_per_week', 1);
        
        // Get all active vehicles with reminder enabled
        $vehicles = $this->vehicle_model->get_reminder_eligible_vehicles();
        
        $reminders_sent = 0;
        $reminders_skipped = 0;
        
        foreach ($vehicles as $vehicle) {
            // BR-74: Skip if reminder disabled for this vehicle
            if (!$vehicle->reminder_enabled) {
                continue;
            }
            
            // Check snooze status
            if (!empty($vehicle->reminder_snoozed_until) && 
                $vehicle->reminder_snoozed_until >= date('Y-m-d')) {
                echo "Vehicle {$vehicle->license_plate}: Snoozed until {$vehicle->reminder_snoozed_until}\n";
                continue;
            }
            
            // BR-73: Check max 1 reminder per 7 days
            $last_reminder = $this->get_last_reminder_date($vehicle->id);
            if ($last_reminder && strtotime($last_reminder) > strtotime('-7 days')) {
                echo "Vehicle {$vehicle->license_plate}: Max reminder limit reached\n";
                $reminders_skipped++;
                continue;
            }
            
            // Calculate current estimated KM
            $last_service_km = (int)$vehicle->last_odometer;
            $days_since_service = $this->days_since($vehicle->last_service_date);
            $avg_daily_km = 50; // Default average, could be from vehicle history
            $estimated_current_km = $last_service_km + ($days_since_service * $avg_daily_km);
            $km_since_service = $estimated_current_km - $last_service_km;
            
            // Check if reminder needed
            $needs_reminder = FALSE;
            $reason = [];
            
            // Condition 1: KM threshold exceeded
            if ($km_since_service >= $km_threshold) {
                $needs_reminder = TRUE;
                $reason[] = "KM threshold ({$km_since_service} km >= {$km_threshold} km)";
            }
            
            // Condition 2: Time threshold exceeded (6 months)
            if ($days_since_service >= ($month_threshold * 30)) {
                $needs_reminder = TRUE;
                $reason[] = "Time threshold ({$days_since_service} days >= " . ($month_threshold * 30) . " days)";
            }
            
            if ($needs_reminder) {
                // Get nearest workshops based on user's default city
                $nearest_workshops = $this->get_nearest_workshops($vehicle->user_id);
                
                // Send email notification
                $template_data = [
                    'nama_pengguna' => $vehicle->user_name,
                    'kendaraan' => "{$vehicle->brand} {$vehicle->model} ({$vehicle->license_plate})",
                    'km_terakhir' => number_format($last_service_km, 0, ',', '.'),
                    'km_estimasi' => number_format($estimated_current_km, 0, ',', '.'),
                    'tanggal_servis' => date('d/m/Y', strtotime($vehicle->last_service_date)),
                    'rekomendasi_bengkel' => $this->format_workshop_list($nearest_workshops)
                ];
                
                $result = $this->notification_model->send_template_notification(
                    $vehicle->user_id,
                    'service_reminder',
                    $template_data,
                    'email'
                );
                
                if ($result['success']) {
                    $reminders_sent++;
                    echo "Reminder sent to {$vehicle->user_email} for vehicle {$vehicle->license_plate}\n";
                } else {
                    echo "Failed to send reminder: {$result['message']}\n";
                }
            }
        }
        
        echo "[" . date('Y-m-d H:i:s') . "] Cron completed. Sent: {$reminders_sent}, Skipped: {$reminders_skipped}\n";
    }
    
    private function days_since($date)
    {
        if (empty($date)) return 9999;
        $datetime1 = new DateTime($date);
        $datetime2 = new DateTime();
        $interval = $datetime1->diff($datetime2);
        return $interval->days;
    }
    
    private function get_nearest_workshops($user_id)
    {
        // Get user's default city
        $user = $this->db->get_where('users', ['id' => $user_id])->row();
        $city = $user->default_city ?? 'Jakarta';
        
        // Get top 3 workshops in that city
        $this->db->select('id, name, address, phone, rating');
        $this->db->where('city', $city);
        $this->db->where('is_active', 1);
        $this->db->order_by('rating', 'DESC');
        $this->db->limit(3);
        return $this->db->get('workshops')->result_array();
    }
    
    private function format_workshop_list($workshops)
    {
        $output = "<ul>";
        foreach ($workshops as $ws) {
            $output .= "<li><strong>{$ws['name']}</strong><br>";
            $output .= "{$ws['address']}<br>";
            $output .= "Telp: {$ws['phone']}<br>";
            $output .= "Rating: " . str_repeat('★', round($ws['rating'])) . "</li>";
        }
        $output .= "</ul>";
        return $output;
    }
}

// Run the cron
$cron = new ServiceReminderCron();
$cron->run();
```

### Setup Crontab

```bash
# Edit crontab
crontab -e

# Add line to run daily at midnight
0 0 * * * /usr/bin/php /var/www/bengkel-terdekat/scripts/service_reminder_cron.php >> /var/log/service_reminder.log 2>&1

# Verify crontab
crontab -l

# Check logs
tail -f /var/log/service_reminder.log
```

---

## Testing Checklist

### Unit Tests
- [ ] `calculate_booking_total()` returns correct sum
- [ ] `generate_invoice()` creates unique invoice number
- [ ] `generate_invoice()` rejects non-completed bookings
- [ ] `record_payment()` updates payment_status correctly
- [ ] `get_transaction_report()` filters by date range
- [ ] `get_global_report()` aggregates correctly

### Integration Tests
- [ ] Generate invoice from completed booking
- [ ] Print PDF invoice with all items
- [ ] Record partial payment → status changes to "partial"
- [ ] Record full payment → status changes to "paid"
- [ ] Export CSV downloads correctly formatted file
- [ ] Admin report shows correct aggregate data

### Manual Testing
1. Login sebagai workshop_owner
2. Buka menu Tagihan & Invoice
3. Filter berdasarkan tanggal
4. Klik "Generate Invoice" pada booking completed
5. Verifikasi invoice number format
6. Cetak PDF invoice
7. Catat pembayaran (partial lalu full)
8. Verifikasi status berubah
9. Export CSV dan buka di Excel
10. Login sebagai admin
11. Lihat laporan global
12. Verifikasi aggregate data benar

---

## Troubleshooting

### Issue: Invoice number tidak unik
**Solution:** Pastikan unique index pada kolom `invoice_number` dan sequence logic benar.

### Issue: PDF tidak tercetak
**Solution:** 
- Install mPDF: `composer require mpdf/mpdf`
- Atau DomPDF: `composer require dompdf/dompdf`
- Check folder permissions untuk temp files

### Issue: Laporan kosong
**Solution:**
- Verifikasi booking status = 'completed'
- Check date range filter
- Pastikan invoice sudah digenerate

### Issue: Cron job tidak jalan
**Solution:**
- Check crontab: `crontab -l`
- Test manual: `php scripts/service_reminder_cron.php`
- Check logs: `tail -f /var/log/service_reminder.log`
- Verify PHP CLI path: `which php`

---

## Security Considerations

1. **Authorization**: Semua endpoint divalidasi role-based (workshop_owner/admin)
2. **Data Isolation**: Workshop owner hanya lihat data bengkel sendiri
3. **SQL Injection**: Semua query menggunakan Query Builder/Prepared Statements
4. **XSS Prevention**: Output escaped dengan `esc()` helper
5. **CSRF Protection**: Form submissions include CSRF token
6. **File Permissions**: PDF temp files restricted access

---

## Performance Optimization

1. **Indexing**: 
   - `idx_bookings_payment_status` pada bookings
   - `idx_workshop` pada invoices
   - `idx_issue_date` pada invoices

2. **Query Optimization**:
   - Use SELECT specific columns instead of SELECT *
   - Pagination untuk large datasets
   - Cache summary calculations (optional Redis)

3. **Lazy Loading**:
   - Load service/sparepart items only when viewing detail
   - Don't load all invoices at once

---

## Future Enhancements

- [ ] Multi-currency support
- [ ] Automated recurring invoices for fleet customers
- [ ] Payment gateway integration (Midtrans, Xendit)
- [ ] WhatsApp notification for invoice reminders
- [ ] Advanced analytics dashboard (charts, trends)
- [ ] Mobile app push notifications
- [ ] QR code payment on invoice
- [ ] Automatic late fee calculation for overdue invoices

---

**Version:** 4.1  
**Last Updated:** June 2024  
**Author:** Development Team
