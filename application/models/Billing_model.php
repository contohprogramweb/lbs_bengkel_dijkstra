<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Billing Model
 * 
 * Handles billing calculations, invoice generation, and reporting
 * for workshop owners and admin.
 * 
 * @package     Bengkel Terdekat
 * @subpackage  Models
 * @version     4.1
 */
class Billing_model extends CI_Model {

    private $table_bookings = 'bookings';
    private $table_invoices = 'invoices';
    private $table_invoice_payments = 'invoice_payments';
    private $table_booking_service_items = 'booking_service_items';
    private $table_booking_sparepart_items = 'booking_sparepart_items';
    private $table_booking_additional_charges = 'booking_additional_charges';
    private $table_workshops = 'workshops';
    private $table_users = 'users';
    private $table_vehicles = 'vehicles';
    private $table_report_settings = 'report_settings';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // ================================================================
    // BILLING CALCULATION
    // ================================================================

    /**
     * Calculate total billing for a completed booking
     * Total = service_cost + sparepart_cost + additional_cost (if approved)
     * 
     * @param int $booking_id
     * @return array ['service_cost' => float, 'sparepart_cost' => float, 'additional_cost' => float, 'final_total' => float]
     */
    public function calculate_booking_total($booking_id)
    {
        $booking_id = (int)$booking_id;
        
        // Get service items total
        $this->db->select_sum('subtotal', 'service_total');
        $this->db->where('booking_id', $booking_id);
        $service_query = $this->db->get($this->table_booking_service_items);
        $service_row = $service_query->row();
        $service_cost = (float)($service_row->service_total ?? 0);

        // Get sparepart items total
        $this->db->select_sum('subtotal', 'sparepart_total');
        $this->db->where('booking_id', $booking_id);
        $sparepart_query = $this->db->get($this->table_booking_sparepart_items);
        $sparepart_row = $sparepart_query->row();
        $sparepart_cost = (float)($sparepart_row->sparepart_total ?? 0);

        // Get additional charges total (only approved ones)
        $this->db->select_sum('amount', 'additional_total');
        $this->db->where('booking_id', $booking_id);
        $this->db->where('is_approved', 1);
        $additional_query = $this->db->get($this->table_booking_additional_charges);
        $additional_row = $additional_query->row();
        $additional_cost = (float)($additional_row->additional_total ?? 0);

        // Calculate final total
        $final_total = $service_cost + $sparepart_cost + $additional_cost;

        return [
            'service_cost' => $service_cost,
            'sparepart_cost' => $sparepart_cost,
            'additional_cost' => $additional_cost,
            'final_total' => $final_total
        ];
    }

    /**
     * Update booking billing fields
     * 
     * @param int $booking_id
     * @param array $billing_data
     * @return bool
     */
    public function update_booking_billing($booking_id, $billing_data)
    {
        $this->db->where('id', $booking_id);
        return $this->db->update($this->table_bookings, $billing_data);
    }

    /**
     * Generate invoice from completed booking
     * 
     * @param int $booking_id
     * @param int $workshop_id
     * @param int $user_id
     * @return array ['success' => bool, 'invoice_id' => int|null, 'message' => string, 'invoice_number' => string|null]
     */
    public function generate_invoice($booking_id, $workshop_id, $user_id)
    {
        $this->db->trans_start();

        try {
            // Check if invoice already exists
            $this->db->where('booking_id', $booking_id);
            $this->db->where('is_deleted', 0);
            $existing = $this->db->get($this->table_invoices)->row();

            if ($existing) {
                return [
                    'success' => FALSE,
                    'invoice_id' => $existing->id,
                    'message' => 'Invoice sudah pernah dibuat untuk booking ini',
                    'invoice_number' => $existing->invoice_number
                ];
            }

            // Get booking details
            $this->db->where('id', $booking_id);
            $booking = $this->db->get($this->table_bookings)->row();

            if (!$booking) {
                throw new Exception('Booking tidak ditemukan');
            }

            // Check booking status must be completed
            if ($booking->status !== 'completed') {
                return [
                    'success' => FALSE,
                    'invoice_id' => NULL,
                    'message' => 'Hanya booking dengan status Completed yang dapat dibuatkan invoice',
                    'invoice_number' => NULL
                ];
            }

            // Calculate totals
            $totals = $this->calculate_booking_total($booking_id);

            // Get report settings
            $tax_rate = (float)$this->get_setting('default_tax_rate', $workshop_id);
            $invoice_prefix = $this->get_setting('invoice_prefix', $workshop_id) ?: 'INV';
            $due_days = (int)$this->get_setting('invoice_due_days', $workshop_id) ?: 7;

            // Calculate tax
            $tax_amount = ($totals['final_total'] * $tax_rate) / 100;
            $total_with_tax = $totals['final_total'] + $tax_amount;

            // Generate invoice number: INV-YYYYMMDD-XXXX
            $invoice_number = $this->generate_invoice_number($invoice_prefix, date('Ymd'));

            // Prepare invoice data
            $invoice_data = [
                'invoice_number' => $invoice_number,
                'booking_id' => $booking_id,
                'workshop_id' => $workshop_id,
                'user_id' => $user_id,
                'issue_date' => date('Y-m-d'),
                'due_date' => date('Y-m-d', strtotime("+{$due_days} days")),
                'service_cost' => $totals['service_cost'],
                'sparepart_cost' => $totals['sparepart_cost'],
                'additional_cost' => $totals['additional_cost'],
                'discount_amount' => 0,
                'tax_rate' => $tax_rate,
                'tax_amount' => $tax_amount,
                'total_amount' => $total_with_tax,
                'payment_status' => 'unpaid',
                'paid_amount' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Insert invoice
            $this->db->insert($this->table_invoices, $invoice_data);
            $invoice_id = $this->db->insert_id();

            if (!$invoice_id) {
                throw new Exception('Gagal menyimpan invoice');
            }

            // Update booking with invoice info
            $booking_update = [
                'invoice_number' => $invoice_number,
                'invoiced_at' => date('Y-m-d H:i:s'),
                'final_total' => $total_with_tax,
                'payment_status' => 'unpaid'
            ];
            $this->update_booking_billing($booking_id, $booking_update);

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaksi database gagal');
            }

            return [
                'success' => TRUE,
                'invoice_id' => $invoice_id,
                'message' => 'Invoice berhasil dibuat',
                'invoice_number' => $invoice_number
            ];

        } catch (Exception $e) {
            $this->db->trans_rollback();
            return [
                'success' => FALSE,
                'invoice_id' => NULL,
                'message' => $e->getMessage(),
                'invoice_number' => NULL
            ];
        }
    }

    /**
     * Generate unique invoice number
     * Format: PREFIX-YYYYMMDD-XXXX
     * 
     * @param string $prefix
     * @param string $date_str
     * @return string
     */
    private function generate_invoice_number($prefix, $date_str)
    {
        $pattern = $prefix . '-' . $date_str . '-%';
        
        $this->db->select('invoice_number');
        $this->db->like('invoice_number', $pattern, 'after');
        $this->db->order_by('invoice_number', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get($this->table_invoices);
        
        $last_invoice = $query->row();
        
        if ($last_invoice) {
            // Extract last sequence number
            $parts = explode('-', $last_invoice->invoice_number);
            $last_seq = (int)end($parts);
            $new_seq = str_pad($last_seq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $new_seq = '0001';
        }

        return $prefix . '-' . $date_str . '-' . $new_seq;
    }

    // ================================================================
    // INVOICE CRUD
    // ================================================================

    /**
     * Get invoice by ID
     * 
     * @param int $invoice_id
     * @return object|null
     */
    public function get_invoice($invoice_id)
    {
        $this->db->where('id', $invoice_id);
        $this->db->where('is_deleted', 0);
        return $this->db->get($this->table_invoices)->row();
    }

    /**
     * Get invoice with booking and workshop details
     * 
     * @param int $invoice_id
     * @return object|null
     */
    public function get_invoice_detail($invoice_id)
    {
        $this->db->select('
            i.*,
            b.booking_number,
            b.scheduled_date,
            b.scheduled_time,
            b.service_description,
            w.name as workshop_name,
            w.address as workshop_address,
            w.phone as workshop_phone,
            u.name as user_name,
            u.email as user_email,
            u.phone as user_phone
        ');
        $this->db->from($this->table_invoices . ' i');
        $this->db->join($this->table_bookings . ' b', 'i.booking_id = b.id');
        $this->db->join($this->table_workshops . ' w', 'i.workshop_id = w.id');
        $this->db->join($this->table_users . ' u', 'i.user_id = u.id');
        $this->db->where('i.id', $invoice_id);
        $this->db->where('i.is_deleted', 0);
        
        return $this->db->get()->row();
    }

    /**
     * Record invoice payment
     * 
     * @param int $invoice_id
     * @param array $payment_data
     * @return array ['success' => bool, 'message' => string]
     */
    public function record_payment($invoice_id, $payment_data)
    {
        $this->db->trans_start();

        try {
            $invoice = $this->get_invoice($invoice_id);
            
            if (!$invoice) {
                throw new Exception('Invoice tidak ditemukan');
            }

            if ($invoice->payment_status === 'paid') {
                return [
                    'success' => FALSE,
                    'message' => 'Invoice sudah lunas'
                ];
            }

            // Insert payment record
            $this->db->insert($this->table_invoice_payments, [
                'invoice_id' => $invoice_id,
                'payment_date' => $payment_data['payment_date'] ?? date('Y-m-d'),
                'amount' => $payment_data['amount'],
                'payment_method' => $payment_data['payment_method'] ?? 'cash',
                'reference_number' => $payment_data['reference_number'] ?? NULL,
                'notes' => $payment_data['notes'] ?? NULL,
                'received_by' => $payment_data['received_by'] ?? NULL,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Update invoice
            $new_paid_amount = $invoice->paid_amount + $payment_data['amount'];
            
            $payment_status = 'unpaid';
            if ($new_paid_amount >= $invoice->total_amount) {
                $payment_status = 'paid';
            } elseif ($new_paid_amount > 0) {
                $payment_status = 'partial';
            }

            $update_data = [
                'paid_amount' => $new_paid_amount,
                'payment_status' => $payment_status,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($payment_status === 'paid') {
                $update_data['paid_at'] = date('Y-m-d H:i:s');
                $update_data['payment_method'] = $payment_data['payment_method'] ?? NULL;
            }

            $this->db->where('id', $invoice_id);
            $this->db->update($this->table_invoices, $update_data);

            // Update booking payment status
            $booking_payment_status = $payment_status;
            $this->db->where('id', $invoice->booking_id);
            $this->db->update($this->table_bookings, [
                'payment_status' => $booking_payment_status,
                'paid_at' => $payment_status === 'paid' ? date('Y-m-d H:i:s') : NULL
            ]);

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaksi database gagal');
            }

            return [
                'success' => TRUE,
                'message' => 'Pembayaran berhasil dicatat'
            ];

        } catch (Exception $e) {
            $this->db->trans_rollback();
            return [
                'success' => FALSE,
                'message' => $e->getMessage()
            ];
        }
    }

    // ================================================================
    // REPORTING - WORKSHOP OWNER
    // ================================================================

    /**
     * Get transaction report for workshop owner
     * 
     * @param int $workshop_id
     * @param string $start_date
     * @param string $end_date
     * @param string|null $status_filter
     * @return array ['transactions' => array, 'summary' => array]
     */
    public function get_transaction_report($workshop_id, $start_date, $end_date, $status_filter = NULL)
    {
        $this->db->select('
            i.id as invoice_id,
            i.invoice_number,
            i.issue_date,
            i.due_date,
            i.total_amount,
            i.paid_amount,
            i.payment_status,
            i.paid_at,
            b.booking_number,
            b.scheduled_date,
            u.name as customer_name,
            u.phone as customer_phone,
            v.brand as vehicle_brand,
            v.model as vehicle_model,
            v.license_plate
        ');
        $this->db->from($this->table_invoices . ' i');
        $this->db->join($this->table_bookings . ' b', 'i.booking_id = b.id');
        $this->db->join($this->table_users . ' u', 'i.user_id = u.id');
        $this->db->join($this->table_vehicles . ' v', 'b.vehicle_id = v.id', 'left');
        $this->db->where('i.workshop_id', $workshop_id);
        $this->db->where('i.is_deleted', 0);
        $this->db->where('i.issue_date >=', $start_date);
        $this->db->where('i.issue_date <=', $end_date);

        if ($status_filter && $status_filter !== 'all') {
            $this->db->where('i.payment_status', $status_filter);
        }

        $this->db->order_by('i.issue_date', 'DESC');
        $this->db->order_by('i.invoice_number', 'DESC');
        
        $transactions = $this->db->get()->result_array();

        // Calculate summary
        $this->db->select('
            COUNT(*) as total_invoices,
            SUM(total_amount) as gross_revenue,
            SUM(CASE WHEN payment_status = "paid" THEN total_amount ELSE 0 END) as paid_revenue,
            SUM(CASE WHEN payment_status = "unpaid" THEN total_amount ELSE 0 END) as unpaid_revenue,
            SUM(CASE WHEN payment_status = "partial" THEN paid_amount ELSE 0 END) as partial_revenue
        ');
        $this->db->from($this->table_invoices);
        $this->db->where('workshop_id', $workshop_id);
        $this->db->where('is_deleted', 0);
        $this->db->where('issue_date >=', $start_date);
        $this->db->where('issue_date <=', $end_date);

        if ($status_filter && $status_filter !== 'all') {
            $this->db->where('payment_status', $status_filter);
        }

        $summary = $this->db->get()->row_array();

        return [
            'transactions' => $transactions,
            'summary' => $summary
        ];
    }

    /**
     * Get all invoices for workshop owner
     * 
     * @param int $workshop_id
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @return array ['data' => array, 'total' => int]
     */
    public function get_workshop_invoices($workshop_id, $filters = [], $limit = 50, $offset = 0)
    {
        $this->db->select('
            i.*,
            b.booking_number,
            u.name as customer_name
        ');
        $this->db->from($this->table_invoices . ' i');
        $this->db->join($this->table_bookings . ' b', 'i.booking_id = b.id');
        $this->db->join($this->table_users . ' u', 'i.user_id = u.id');
        $this->db->where('i.workshop_id', $workshop_id);
        $this->db->where('i.is_deleted', 0);

        // Apply filters
        if (!empty($filters['payment_status'])) {
            $this->db->where('i.payment_status', $filters['payment_status']);
        }
        if (!empty($filters['start_date'])) {
            $this->db->where('i.issue_date >=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $this->db->where('i.issue_date <=', $filters['end_date']);
        }
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('i.invoice_number', $filters['search']);
            $this->db->or_like('b.booking_number', $filters['search']);
            $this->db->or_like('u.name', $filters['search']);
            $this->db->group_end();
        }

        // Get total count - need to clone the query first because count_all_results resets the QB
        $this->db->select('COUNT(*) as total_count', FALSE);
        $total_row = $this->db->get()->row();
        $total = (int)$total_row->total_count;

        // Reset and rebuild query for data fetch
        $this->db->reset_query();
        
        $this->db->select('
            i.*,
            b.booking_number,
            u.name as customer_name
        ');
        $this->db->from($this->table_invoices . ' i');
        $this->db->join($this->table_bookings . ' b', 'i.booking_id = b.id');
        $this->db->join($this->table_users . ' u', 'i.user_id = u.id');
        $this->db->where('i.workshop_id', $workshop_id);
        $this->db->where('i.is_deleted', 0);

        // Re-apply filters
        if (!empty($filters['payment_status'])) {
            $this->db->where('i.payment_status', $filters['payment_status']);
        }
        if (!empty($filters['start_date'])) {
            $this->db->where('i.issue_date >=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $this->db->where('i.issue_date <=', $filters['end_date']);
        }
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('i.invoice_number', $filters['search']);
            $this->db->or_like('b.booking_number', $filters['search']);
            $this->db->or_like('u.name', $filters['search']);
            $this->db->group_end();
        }

        // Get data
        $this->db->order_by('i.issue_date', 'DESC');
        $this->db->order_by('i.invoice_number', 'DESC');
        $this->db->limit($limit, $offset);
        $data = $this->db->get()->result_array();

        return [
            'data' => $data,
            'total' => $total
        ];
    }

    // ================================================================
    // REPORTING - ADMIN (GLOBAL AGGREGATE)
    // ================================================================

    /**
     * Get global transaction report across all workshops
     * 
     * @param string $start_date
     * @param string $end_date
     * @param string|null $workshop_filter
     * @return array ['summary' => array, 'by_workshop' => array]
     */
    public function get_global_report($start_date, $end_date, $workshop_filter = NULL)
    {
        // Overall summary
        $this->db->select('
            COUNT(*) as total_invoices,
            SUM(total_amount) as gross_revenue,
            SUM(CASE WHEN payment_status = "paid" THEN total_amount ELSE 0 END) as paid_revenue,
            SUM(CASE WHEN payment_status = "unpaid" THEN total_amount ELSE 0 END) as unpaid_revenue,
            COUNT(DISTINCT workshop_id) as active_workshops
        ');
        $this->db->from($this->table_invoices);
        $this->db->where('is_deleted', 0);
        $this->db->where('issue_date >=', $start_date);
        $this->db->where('issue_date <=', $end_date);

        if ($workshop_filter) {
            $this->db->where('workshop_id', $workshop_filter);
        }

        $summary = $this->db->get()->row_array();

        // Breakdown by workshop
        $this->db->select('
            w.id as workshop_id,
            w.name as workshop_name,
            COUNT(i.id) as total_invoices,
            SUM(i.total_amount) as gross_revenue,
            SUM(CASE WHEN i.payment_status = "paid" THEN i.total_amount ELSE 0 END) as paid_revenue
        ');
        $this->db->from($this->table_invoices . ' i');
        $this->db->join($this->table_workshops . ' w', 'i.workshop_id = w.id');
        $this->db->where('i.is_deleted', 0);
        $this->db->where('i.issue_date >=', $start_date);
        $this->db->where('i.issue_date <=', $end_date);

        if ($workshop_filter) {
            $this->db->where('i.workshop_id', $workshop_filter);
        }

        $this->db->group_by('w.id, w.name');
        $this->db->order_by('gross_revenue', 'DESC');
        $by_workshop = $this->db->get()->result_array();

        return [
            'summary' => $summary,
            'by_workshop' => $by_workshop
        ];
    }

    // ================================================================
    // BOOKING SERVICE ITEMS
    // ================================================================

    /**
     * Add service item to booking
     * 
     * @param int $booking_id
     * @param array $item_data
     * @return int|bool Insert ID or FALSE
     */
    public function add_service_item($booking_id, $item_data)
    {
        $item_data['booking_id'] = $booking_id;
        $item_data['subtotal'] = $item_data['quantity'] * $item_data['unit_price'];
        $item_data['created_at'] = date('Y-m-d H:i:s');
        
        $this->db->insert($this->table_booking_service_items, $item_data);
        return $this->db->insert_id();
    }

    /**
     * Get service items for booking
     * 
     * @param int $booking_id
     * @return array
     */
    public function get_service_items($booking_id)
    {
        $this->db->where('booking_id', $booking_id);
        return $this->db->get($this->table_booking_service_items)->result_array();
    }

    /**
     * Delete service item
     * 
     * @param int $item_id
     * @return bool
     */
    public function delete_service_item($item_id)
    {
        $this->db->where('id', $item_id);
        return $this->db->delete($this->table_booking_service_items);
    }

    // ================================================================
    // BOOKING SPAREPART ITEMS
    // ================================================================

    /**
     * Add sparepart item to booking
     * 
     * @param int $booking_id
     * @param array $item_data
     * @return int|bool Insert ID or FALSE
     */
    public function add_sparepart_item($booking_id, $item_data)
    {
        $item_data['booking_id'] = $booking_id;
        $item_data['subtotal'] = $item_data['quantity'] * $item_data['unit_price'];
        $item_data['created_at'] = date('Y-m-d H:i:s');
        
        $this->db->insert($this->table_booking_sparepart_items, $item_data);
        return $this->db->insert_id();
    }

    /**
     * Get sparepart items for booking
     * 
     * @param int $booking_id
     * @return array
     */
    public function get_sparepart_items($booking_id)
    {
        $this->db->where('booking_id', $booking_id);
        return $this->db->get($this->table_booking_sparepart_items)->result_array();
    }

    /**
     * Delete sparepart item
     * 
     * @param int $item_id
     * @return bool
     */
    public function delete_sparepart_item($item_id)
    {
        $this->db->where('id', $item_id);
        return $this->db->delete($this->table_booking_sparepart_items);
    }

    // ================================================================
    // BOOKING ADDITIONAL CHARGES
    // ================================================================

    /**
     * Add additional charge to booking
     * 
     * @param int $booking_id
     * @param array $charge_data
     * @return int|bool Insert ID or FALSE
     */
    public function add_additional_charge($booking_id, $charge_data)
    {
        $charge_data['booking_id'] = $booking_id;
        $charge_data['created_at'] = date('Y-m-d H:i:s');
        
        $this->db->insert($this->table_booking_additional_charges, $charge_data);
        return $this->db->insert_id();
    }

    /**
     * Get additional charges for booking
     * 
     * @param int $booking_id
     * @return array
     */
    public function get_additional_charges($booking_id)
    {
        $this->db->where('booking_id', $booking_id);
        return $this->db->get($this->table_booking_additional_charges)->result_array();
    }

    /**
     * Approve additional charge
     * 
     * @param int $charge_id
     * @return bool
     */
    public function approve_charge($charge_id)
    {
        $this->db->where('id', $charge_id);
        return $this->db->update($this->table_booking_additional_charges, ['is_approved' => 1]);
    }

    // ================================================================
    // SETTINGS
    // ================================================================

    /**
     * Get report setting value
     * 
     * @param string $key
     * @param int|null $workshop_id
     * @return mixed
     */
    public function get_setting($key, $workshop_id = NULL)
    {
        $this->db->where('setting_key', $key);
        
        if ($workshop_id) {
            $this->db->where('workshop_id', $workshop_id);
        } else {
            $this->db->where('workshop_id IS NULL', NULL, FALSE);
        }
        
        $row = $this->db->get($this->table_report_settings)->row();
        
        return $row ? $row->setting_value : NULL;
    }

    /**
     * Update report setting
     * 
     * @param string $key
     * @param mixed $value
     * @param int|null $workshop_id
     * @return bool
     */
    public function update_setting($key, $value, $workshop_id = NULL)
    {
        $data = [
            'setting_value' => $value,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->where('setting_key', $key);
        
        if ($workshop_id) {
            $this->db->where('workshop_id', $workshop_id);
        } else {
            $this->db->where('workshop_id IS NULL', NULL, FALSE);
        }

        return $this->db->update($this->table_report_settings, $data);
    }

    // ================================================================
    // EXPORT DATA
    // ================================================================

    /**
     * Get transactions for export (CSV/Excel)
     * 
     * @param int $workshop_id
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    public function get_export_data($workshop_id, $start_date, $end_date)
    {
        $this->db->select('
            i.invoice_number,
            i.issue_date,
            i.due_date,
            i.total_amount,
            i.paid_amount,
            i.payment_status,
            i.paid_at,
            b.booking_number,
            b.scheduled_date,
            u.name as customer_name,
            u.email as customer_email,
            u.phone as customer_phone,
            v.brand as vehicle_brand,
            v.model as vehicle_model,
            v.license_plate,
            w.name as workshop_name
        ');
        $this->db->from($this->table_invoices . ' i');
        $this->db->join($this->table_bookings . ' b', 'i.booking_id = b.id');
        $this->db->join($this->table_users . ' u', 'i.user_id = u.id');
        $this->db->join($this->table_vehicles . ' v', 'b.vehicle_id = v.id', 'left');
        $this->db->join($this->table_workshops . ' w', 'i.workshop_id = w.id');
        $this->db->where('i.workshop_id', $workshop_id);
        $this->db->where('i.is_deleted', 0);
        $this->db->where('i.issue_date >=', $start_date);
        $this->db->where('i.issue_date <=', $end_date);
        $this->db->order_by('i.issue_date', 'DESC');
        
        return $this->db->get()->result_array();
    }
}
