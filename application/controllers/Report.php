<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin Report Controller
 * 
 * Handles global reporting across all workshops:
 * - Aggregate transaction reports
 * - Workshop performance comparison
 * - Export data (CSV/Excel)
 * 
 * @package     Bengkel Terdekat
 * @subpackage  Controllers
 * @version     4.1
 */
class Report extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();
        
        $this->load->model('billing_model');
        $this->load->helper(['form', 'text']);
    }

    /**
     * Global Transaction Report Dashboard
     */
    public function index()
    {
        $data['title'] = 'Laporan Global';
        $data['page_title'] = 'Laporan Transaksi Semua Bengkel';

        // Get filter parameters
        $start_date = $this->input->get('start_date') ?: date('Y-m-01');
        $end_date = $this->input->get('end_date') ?: date('Y-m-t');
        $workshop_id = $this->input->get('workshop_id');

        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;
        $data['workshop_filter'] = $workshop_id;

        // Get all workshops for filter dropdown
        $this->db->select('id, name');
        $this->db->where('is_active', 1);
        $this->db->order_by('name', 'ASC');
        $data['workshops'] = $this->db->get('workshops')->result_array();

        // Get global report
        $report = $this->billing_model->get_global_report($start_date, $end_date, $workshop_id);

        $data['summary'] = $report['summary'];
        $data['by_workshop'] = $report['by_workshop'];

        $this->render('admin/report/global', $data);
    }

    /**
     * Export global report to CSV
     */
    public function export_global_csv()
    {
        $start_date = $this->input->get('start_date') ?: date('Y-m-01');
        $end_date = $this->input->get('end_date') ?: date('Y-m-t');
        $workshop_id = $this->input->get('workshop_id');

        // Get workshop breakdown data
        $report = $this->billing_model->get_global_report($start_date, $end_date, $workshop_id);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="Laporan_Global_' . $start_date . '_to_' . $end_date . '.csv"');

        $output = fopen('php://output', 'w');

        // Summary section
        fputcsv($output, ['=== RINGKASAN GLOBAL ===']);
        fputcsv($output, ['Periode', $start_date . ' s/d ' . $end_date]);
        fputcsv($output, []);
        fputcsv($output, ['Total Invoice', 'Total Omzet', 'Sudah Dibayar', 'Belum Dibayar', 'Bengkel Aktif']);
        fputcsv($output, [
            $report['summary']['total_invoices'],
            $report['summary']['gross_revenue'],
            $report['summary']['paid_revenue'],
            $report['summary']['unpaid_revenue'],
            $report['summary']['active_workshops']
        ]);
        fputcsv($output, []);

        // Workshop breakdown
        fputcsv($output, ['=== RINCIAN PER BENGKEL ===']);
        fputcsv($output, ['ID Bengkel', 'Nama Bengkel', 'Total Invoice', 'Total Omzet', 'Sudah Dibayar']);
        
        foreach ($report['by_workshop'] as $row) {
            fputcsv($output, [
                $row['workshop_id'],
                $row['workshop_name'],
                $row['total_invoices'],
                $row['gross_revenue'],
                $row['paid_revenue']
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Detail transaction report for specific workshop
     */
    public function workshop_detail($workshop_id)
    {
        $workshop_id = (int)$workshop_id;

        // Get workshop info
        $this->db->select('id, name, address, phone');
        $workshop = $this->db->get_where('workshops', ['id' => $workshop_id])->row();

        if (!$workshop) {
            show_error('Bengkel tidak ditemukan', 404);
        }

        $data['title'] = 'Laporan Detail Bengkel';
        $data['page_title'] = 'Laporan: ' . $workshop->name;
        $data['workshop'] = $workshop;

        // Get filter parameters
        $start_date = $this->input->get('start_date') ?: date('Y-m-01');
        $end_date = $this->input->get('end_date') ?: date('Y-m-t');
        $status_filter = $this->input->get('status');

        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;
        $data['status_filter'] = $status_filter;

        // Get detailed transactions
        $this->billing_model->get_transaction_report($workshop_id, $start_date, $end_date, $status_filter);
        $report = $this->billing_model->get_transaction_report($workshop_id, $start_date, $end_date, $status_filter);

        $data['transactions'] = $report['transactions'];
        $data['summary'] = $report['summary'];

        $this->render('admin/report/workshop_detail', $data);
    }
}
