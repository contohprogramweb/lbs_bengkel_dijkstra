<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Workshop Billing Controller
 * 
 * Handles billing operations for workshop owners:
 * - Generate invoices from completed bookings
 * - View and manage invoices
 * - Record payments
 * - Transaction reports
 * - Export data (CSV/Excel)
 * 
 * @package     Bengkel Terdekat
 * @subpackage  Controllers
 * @version     4.1
 */
class Billing extends Workshop_Controller {

    private $workshop_id;

    public function __construct()
    {
        parent::__construct();
        
        $this->workshop_id = $this->session->userdata('workshop_id');
        
        $this->load->model('billing_model');
        $this->load->model('booking_model');
        $this->load->helper(['form', 'text']);
    }

    /**
     * Dashboard - List invoices
     */
    public function index()
    {
        $data['title'] = 'Tagihan & Invoice';
        $data['page_title'] = 'Manajemen Tagihan';

        // Get filter parameters
        $filters = [
            'payment_status' => $this->input->get('payment_status'),
            'start_date' => $this->input->get('start_date'),
            'end_date' => $this->input->get('end_date'),
            'search' => $this->input->get('search')
        ];

        // Set default date range (current month)
        if (empty($filters['start_date'])) {
            $filters['start_date'] = date('Y-m-01');
        }
        if (empty($filters['end_date'])) {
            $filters['end_date'] = date('Y-m-t');
        }

        // Pagination
        $page = max(1, (int)$this->input->get('page'));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        // Get invoices
        $result = $this->billing_model->get_workshop_invoices($this->workshop_id, $filters, $limit, $offset);
        
        $data['invoices'] = $result['data'];
        $data['total'] = $result['total'];
        $data['pagination'] = [
            'current_page' => $page,
            'total_pages' => ceil($result['total'] / $limit),
            'total_items' => $result['total']
        ];
        $data['filters'] = $filters;

        // Summary stats
        $report = $this->billing_model->get_transaction_report(
            $this->workshop_id,
            $filters['start_date'],
            $filters['end_date'],
            NULL
        );
        $data['summary'] = $report['summary'];

        $this->load->view('workshop/layouts/header', $data);
        $this->load->view('workshop/billing/invoices', $data);
        $this->load->view('workshop/layouts/footer');
    }

    /**
     * Generate invoice from completed booking
     */
    public function generate_invoice($booking_id)
    {
        $booking_id = (int)$booking_id;

        // Verify booking belongs to this workshop
        $booking = $this->booking_model->get_booking_detail($booking_id);
        
        if (!$booking || $booking->workshop_id != $this->workshop_id) {
            $this->session->set_flashdata('error', 'Booking tidak ditemukan');
            redirect('workshop/orders');
        }

        // Check status
        if ($booking->status !== 'completed') {
            $this->session->set_flashdata('error', 'Hanya booking dengan status Completed yang dapat dibuatkan invoice');
            redirect('workshop/orders/view/' . $booking_id);
        }

        // Generate invoice
        $user_id = $this->session->userdata('user_id');
        $result = $this->billing_model->generate_invoice($booking_id, $this->workshop_id, $user_id);

        if ($result['success']) {
            $this->session->set_flashdata('success', 'Invoice berhasil dibuat: ' . $result['invoice_number']);
            redirect('workshop/billing/view/' . $result['invoice_id']);
        } else {
            $this->session->set_flashdata('error', $result['message']);
            redirect('workshop/orders/view/' . $booking_id);
        }
    }

    /**
     * View invoice detail
     */
    public function view($invoice_id)
    {
        $invoice_id = (int)$invoice_id;

        $data['invoice'] = $this->billing_model->get_invoice_detail($invoice_id);

        if (!$data['invoice'] || $data['invoice']->workshop_id != $this->workshop_id) {
            show_error('Invoice tidak ditemukan', 404);
        }

        $data['title'] = 'Detail Invoice';
        $data['page_title'] = 'Invoice #' . $data['invoice']->invoice_number;

        // Get line items
        $data['service_items'] = $this->billing_model->get_service_items($data['invoice']->booking_id);
        $data['sparepart_items'] = $this->billing_model->get_sparepart_items($data['invoice']->booking_id);
        $data['additional_charges'] = $this->billing_model->get_additional_charges($data['invoice']->booking_id);

        // Get payment history
        $this->db->where('invoice_id', $invoice_id);
        $this->db->order_by('payment_date', 'DESC');
        $data['payments'] = $this->db->get('invoice_payments')->result_array();

        $this->load->view('workshop/layouts/header', $data);
        $this->load->view('workshop/billing/invoice_detail', $data);
        $this->load->view('workshop/layouts/footer');
    }

    /**
     * Print invoice as PDF
     */
    public function print_invoice($invoice_id)
    {
        $invoice_id = (int)$invoice_id;

        $data['invoice'] = $this->billing_model->get_invoice_detail($invoice_id);

        if (!$data['invoice'] || $data['invoice']->workshop_id != $this->workshop_id) {
            show_error('Invoice tidak ditemukan', 404);
        }

        $data['service_items'] = $this->billing_model->get_service_items($data['invoice']->booking_id);
        $data['sparepart_items'] = $this->billing_model->get_sparepart_items($data['invoice']->booking_id);
        $data['additional_charges'] = $this->billing_model->get_additional_charges($data['invoice']->booking_id);

        // Load PDF library (mPDF or DomPDF)
        $this->load->library('pdf');
        
        $html = $this->load->view('workshop/billing/invoice_pdf', $data, TRUE);
        
        $this->pdf->loadHtml($html);
        $this->pdf->setPaper('A4', 'portrait');
        $this->pdf->render();
        $this->pdf->stream('Invoice_' . $data['invoice']->invoice_number . '.pdf');
    }

    /**
     * Record payment for invoice
     */
    public function record_payment($invoice_id)
    {
        $invoice_id = (int)$invoice_id;

        $invoice = $this->billing_model->get_invoice($invoice_id);

        if (!$invoice || $invoice->workshop_id != $this->workshop_id) {
            $this->session->set_flashdata('error', 'Invoice tidak ditemukan');
            redirect('workshop/billing');
        }

        if ($this->input->post()) {
            $payment_data = [
                'amount' => (float)$this->input->post('amount'),
                'payment_method' => $this->input->post('payment_method'),
                'reference_number' => $this->input->post('reference_number'),
                'notes' => $this->input->post('notes'),
                'received_by' => $this->session->userdata('user_id')
            ];

            // Validate amount
            $remaining = $invoice->total_amount - $invoice->paid_amount;
            if ($payment_data['amount'] > $remaining) {
                $this->session->set_flashdata('error', 'Jumlah pembayaran melebihi sisa tagihan');
                redirect('workshop/billing/view/' . $invoice_id);
            }

            $result = $this->billing_model->record_payment($invoice_id, $payment_data);

            if ($result['success']) {
                $this->session->set_flashdata('success', $result['message']);
            } else {
                $this->session->set_flashdata('error', $result['message']);
            }

            redirect('workshop/billing/view/' . $invoice_id);
        }

        // Show payment form
        $data['title'] = 'Catat Pembayaran';
        $data['page_title'] = 'Catat Pembayaran';
        $data['invoice'] = $invoice;
        $data['remaining'] = $invoice->total_amount - $invoice->paid_amount;

        $this->load->view('workshop/layouts/header', $data);
        $this->load->view('workshop/billing/payment_form', $data);
        $this->load->view('workshop/layouts/footer');
    }

    /**
     * Transaction Report
     */
    public function report()
    {
        $data['title'] = 'Laporan Transaksi';
        $data['page_title'] = 'Laporan Pemasukan';

        // Get filter parameters
        $start_date = $this->input->get('start_date') ?: date('Y-m-01');
        $end_date = $this->input->get('end_date') ?: date('Y-m-t');
        $status_filter = $this->input->get('status');

        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;
        $data['status_filter'] = $status_filter;

        // Get report data
        $report = $this->billing_model->get_transaction_report(
            $this->workshop_id,
            $start_date,
            $end_date,
            $status_filter
        );

        $data['transactions'] = $report['transactions'];
        $data['summary'] = $report['summary'];

        $this->load->view('workshop/layouts/header', $data);
        $this->load->view('workshop/billing/report', $data);
        $this->load->view('workshop/layouts/footer');
    }

    /**
     * Export report to CSV
     */
    public function export_csv()
    {
        $start_date = $this->input->get('start_date') ?: date('Y-m-01');
        $end_date = $this->input->get('end_date') ?: date('Y-m-t');

        $data = $this->billing_model->get_export_data($this->workshop_id, $start_date, $end_date);

        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="Laporan_Transaksi_' . $start_date . '_to_' . $end_date . '.csv"');

        $output = fopen('php://output', 'w');

        // Header row
        fputcsv($output, [
            'Invoice Number',
            'Issue Date',
            'Due Date',
            'Total Amount',
            'Paid Amount',
            'Payment Status',
            'Paid At',
            'Booking Number',
            'Scheduled Date',
            'Customer Name',
            'Customer Email',
            'Customer Phone',
            'Vehicle',
            'Workshop Name'
        ]);

        // Data rows
        foreach ($data as $row) {
            fputcsv($output, [
                $row['invoice_number'],
                $row['issue_date'],
                $row['due_date'],
                $row['total_amount'],
                $row['paid_amount'],
                $row['payment_status'],
                $row['paid_at'],
                $row['booking_number'],
                $row['scheduled_date'],
                $row['customer_name'],
                $row['customer_email'],
                $row['customer_phone'],
                $row['vehicle_brand'] . ' ' . $row['vehicle_model'] . ' (' . $row['license_plate'] . ')',
                $row['workshop_name']
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Add service item to booking (AJAX)
     */
    public function add_service_item()
    {
        if (!$this->input->is_ajax_request()) {
            show_error('Invalid request', 400);
        }

        $booking_id = (int)$this->input->post('booking_id');
        
        // Verify ownership
        $booking = $this->booking_model->get_booking($booking_id);
        if (!$booking || $booking->workshop_id != $this->workshop_id) {
            echo json_encode(['success' => FALSE, 'message' => 'Booking tidak ditemukan']);
            return;
        }

        $item_data = [
            'service_name' => $this->input->post('service_name'),
            'description' => $this->input->post('description'),
            'quantity' => max(1, (int)$this->input->post('quantity')),
            'unit_price' => (float)$this->input->post('unit_price')
        ];

        $item_id = $this->billing_model->add_service_item($booking_id, $item_data);

        if ($item_id) {
            echo json_encode(['success' => TRUE, 'item_id' => $item_id]);
        } else {
            echo json_encode(['success' => FALSE, 'message' => 'Gagal menyimpan item']);
        }
    }

    /**
     * Add sparepart item to booking (AJAX)
     */
    public function add_sparepart_item()
    {
        if (!$this->input->is_ajax_request()) {
            show_error('Invalid request', 400);
        }

        $booking_id = (int)$this->input->post('booking_id');
        
        // Verify ownership
        $booking = $this->booking_model->get_booking($booking_id);
        if (!$booking || $booking->workshop_id != $this->workshop_id) {
            echo json_encode(['success' => FALSE, 'message' => 'Booking tidak ditemukan']);
            return;
        }

        $item_data = [
            'sparepart_name' => $this->input->post('sparepart_name'),
            'part_number' => $this->input->post('part_number'),
            'description' => $this->input->post('description'),
            'quantity' => max(1, (int)$this->input->post('quantity')),
            'unit_price' => (float)$this->input->post('unit_price')
        ];

        $item_id = $this->billing_model->add_sparepart_item($booking_id, $item_data);

        if ($item_id) {
            echo json_encode(['success' => TRUE, 'item_id' => $item_id]);
        } else {
            echo json_encode(['success' => FALSE, 'message' => 'Gagal menyimpan item']);
        }
    }

    /**
     * Delete service item (AJAX)
     */
    public function delete_service_item($item_id)
    {
        if (!$this->input->is_ajax_request()) {
            show_error('Invalid request', 400);
        }

        $this->billing_model->delete_service_item($item_id);
        echo json_encode(['success' => TRUE]);
    }

    /**
     * Delete sparepart item (AJAX)
     */
    public function delete_sparepart_item($item_id)
    {
        if (!$this->input->is_ajax_request()) {
            show_error('Invalid request', 400);
        }

        $this->billing_model->delete_sparepart_item($item_id);
        echo json_encode(['success' => TRUE]);
    }
}
