<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice <?= $invoice->invoice_number ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        .workshop-info {
            font-size: 11px;
            color: #666;
        }
        .invoice-title {
            text-align: right;
        }
        .invoice-title h1 {
            font-size: 28px;
            color: #007bff;
            margin-bottom: 5px;
        }
        .invoice-meta {
            font-size: 11px;
            color: #666;
        }
        .customer-section {
            margin-bottom: 20px;
        }
        .customer-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .customer-info h3 {
            font-size: 14px;
            margin-bottom: 10px;
            color: #007bff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        th {
            background-color: #007bff;
            color: white;
            font-weight: 600;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals-section {
            margin-top: 20px;
        }
        .totals-table {
            width: 300px;
            margin-left: auto;
        }
        .totals-table td {
            padding: 8px 10px;
        }
        .totals-table .label {
            font-weight: 600;
            color: #666;
        }
        .totals-table .total-row {
            background-color: #007bff;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }
        .payment-status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 11px;
        }
        .status-paid {
            background-color: #28a745;
            color: white;
        }
        .status-unpaid {
            background-color: #dc3545;
            color: white;
        }
        .status-partial {
            background-color: #ffc107;
            color: #1f2d3d;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .notes {
            margin-top: 20px;
            padding: 15px;
            background: #fff3cd;
            border-left: 3px solid #ffc107;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div class="logo"><?= esc($invoice->workshop_name) ?></div>
                    <div class="workshop-info">
                        <?= esc($invoice->workshop_address) ?><br>
                        Telp: <?= esc($invoice->workshop_phone) ?>
                    </div>
                </div>
                <div class="invoice-title">
                    <h1>INVOICE</h1>
                    <div class="invoice-meta">
                        <strong>No: <?= esc($invoice->invoice_number) ?></strong><br>
                        Tanggal: <?= date('d/m/Y', strtotime($invoice->issue_date)) ?><br>
                        Jatuh Tempo: <?= date('d/m/Y', strtotime($invoice->due_date)) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="customer-section">
            <div class="customer-info">
                <h3>Ditagihkan Kepada:</h3>
                <strong><?= esc($invoice->user_name) ?></strong><br>
                <?= esc($invoice->user_email) ?><br>
                <?= esc($invoice->user_phone) ?>
            </div>
        </div>

        <!-- Booking Info -->
        <div class="customer-section">
            <div class="customer-info">
                <h3>Detail Servis:</h3>
                <table style="margin: 0; background: transparent;">
                    <tr>
                        <td style="border: none; padding: 5px 0;"><strong>No. Booking:</strong></td>
                        <td style="border: none; padding: 5px 0 5px 20px;"><?= esc($invoice->booking_number) ?></td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 5px 0;"><strong>Tanggal Servis:</strong></td>
                        <td style="border: none; padding: 5px 0 5px 20px;"><?= date('d/m/Y', strtotime($invoice->scheduled_date)) ?></td>
                    </tr>
                    <tr>
                        <td style="border: none; padding: 5px 0;"><strong>Keterangan:</strong></td>
                        <td style="border: none; padding: 5px 0 5px 20px;"><?= esc($invoice->service_description) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Service Items -->
        <?php if (!empty($service_items)): ?>
        <h3 style="font-size: 14px; margin: 20px 0 10px 0; color: #007bff;">Layanan</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 45%;">Deskripsi</th>
                    <th class="text-center" style="width: 10%;">Qty</th>
                    <th class="text-right" style="width: 15%;">Harga Satuan</th>
                    <th class="text-right" style="width: 15%;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($service_items as $item): ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td>
                        <strong><?= esc($item['service_name']) ?></strong>
                        <?php if (!empty($item['description'])): ?>
                        <br><small style="color: #666;"><?= esc($item['description']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td class="text-center"><?= $item['quantity'] ?></td>
                    <td class="text-right">Rp <?= number_format($item['unit_price'], 0, ',', '.') ?></td>
                    <td class="text-right">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <!-- Sparepart Items -->
        <?php if (!empty($sparepart_items)): ?>
        <h3 style="font-size: 14px; margin: 20px 0 10px 0; color: #007bff;">Sparepart</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 40%;">Nama Part</th>
                    <th style="width: 15%;" class="text-center">Part Number</th>
                    <th class="text-center" style="width: 10%;">Qty</th>
                    <th class="text-right" style="width: 15%;">Harga Satuan</th>
                    <th class="text-right" style="width: 15%;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($sparepart_items as $item): ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td>
                        <strong><?= esc($item['sparepart_name']) ?></strong>
                        <?php if (!empty($item['description'])): ?>
                        <br><small style="color: #666;"><?= esc($item['description']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td class="text-center"><?= esc($item['part_number'] ?? '-') ?></td>
                    <td class="text-center"><?= $item['quantity'] ?></td>
                    <td class="text-right">Rp <?= number_format($item['unit_price'], 0, ',', '.') ?></td>
                    <td class="text-right">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <!-- Additional Charges -->
        <?php if (!empty($additional_charges)): ?>
        <h3 style="font-size: 14px; margin: 20px 0 10px 0; color: #007bff;">Biaya Tambahan</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 60%;">Keterangan</th>
                    <th class="text-right" style="width: 15%;">Jumlah</th>
                    <th class="text-center" style="width: 10%;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($additional_charges as $charge): ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td>
                        <strong><?= esc($charge['charge_name']) ?></strong>
                        <?php if (!empty($charge['description'])): ?>
                        <br><small style="color: #666;"><?= esc($charge['description']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td class="text-right">Rp <?= number_format($charge['amount'], 0, ',', '.') ?></td>
                    <td class="text-center">
                        <?php if ($charge['is_approved']): ?>
                        <span style="color: #28a745;">✓ Disetujui</span>
                        <?php else: ?>
                        <span style="color: #dc3545;">✗ Belum</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <!-- Totals -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td class="label">Subtotal Layanan:</td>
                    <td class="text-right">Rp <?= number_format($invoice->service_cost, 0, ',', '.') ?></td>
                </tr>
                <tr>
                    <td class="label">Subtotal Sparepart:</td>
                    <td class="text-right">Rp <?= number_format($invoice->sparepart_cost, 0, ',', '.') ?></td>
                </tr>
                <tr>
                    <td class="label">Biaya Tambahan:</td>
                    <td class="text-right">Rp <?= number_format($invoice->additional_cost, 0, ',', '.') ?></td>
                </tr>
                <?php if ($invoice->tax_rate > 0): ?>
                <tr>
                    <td class="label">Pajak (<?= $invoice->tax_rate ?>%):</td>
                    <td class="text-right">Rp <?= number_format($invoice->tax_amount, 0, ',', '.') ?></td>
                </tr>
                <?php endif; ?>
                <tr class="total-row">
                    <td class="label">TOTAL:</td>
                    <td class="text-right">Rp <?= number_format($invoice->total_amount, 0, ',', '.') ?></td>
                </tr>
                <tr>
                    <td class="label">Dibayar:</td>
                    <td class="text-right">Rp <?= number_format($invoice->paid_amount, 0, ',', '.') ?></td>
                </tr>
                <tr>
                    <td class="label">Sisa Tagihan:</td>
                    <td class="text-right">Rp <?= number_format($invoice->total_amount - $invoice->paid_amount, 0, ',', '.') ?></td>
                </tr>
            </table>
        </div>

        <!-- Payment Status -->
        <div style="margin-top: 20px; text-align: right;">
            <span class="payment-status status-<?= $invoice->payment_status ?>">
                <?php
                $status_labels = [
                    'paid' => 'LUNAS',
                    'unpaid' => 'BELUM DIBAYAR',
                    'partial' => 'DIBAYAR SEBAGIAN'
                ];
                echo $status_labels[$invoice->payment_status] ?? $invoice->payment_status;
                ?>
            </span>
        </div>

        <!-- Notes -->
        <?php if (!empty($invoice->notes)): ?>
        <div class="notes">
            <strong>Catatan:</strong><br>
            <?= nl2br(esc($invoice->notes)) ?>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="footer">
            Terima kasih telah menggunakan layanan kami.<br>
            Invoice ini dibuat secara otomatis dan sah tanpa tanda tangan.
        </div>
    </div>
</body>
</html>
