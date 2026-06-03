<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Notification Controller (Admin Side)
 * 
 * Handles notification template management and admin notifications.
 * Implements UC-ADM-06: Kelola Notifikasi & Template
 * 
 * @package     Bengkel Terdekat
 * @version     4.0
 */
class Notification extends Admin_Controller {

    /**
     * Notification model instance
     * @var Notification_model
     */
    private $notification_model;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Notification_model');
        $this->load->library('form_validation');
        $this->notification_model = $this->Notification_model;
    }

    // --------------------------------------------------------------------
    // TEMPLATE MANAGEMENT (UC-ADM-06, BR-84, BR-85)
    // --------------------------------------------------------------------

    /**
     * List all notification templates
     */
    public function templates()
    {
        $data['page_title'] = 'Kelola Template Notifikasi';
        $data['user'] = $this->current_user;
        
        $data['templates'] = $this->notification_model->get_all_templates();

        $this->render('admin/notification/templates', $data);
    }

    /**
     * Create new template
     */
    public function create_template()
    {
        $data['page_title'] = 'Tambah Template Notifikasi';
        $data['user'] = $this->current_user;
        $data['form_action'] = site_url('notification/save_template');
        $data['template'] = NULL;
        $data['available_variables'] = $this->_get_available_variables();

        $this->render('admin/notification/template_form', $data);
    }

    /**
     * Edit template
     * @param int $id
     */
    public function edit_template($id)
    {
        $data['page_title'] = 'Edit Template Notifikasi';
        $data['user'] = $this->current_user;
        $data['form_action'] = site_url('notification/update_template/' . $id);
        $data['template'] = $this->notification_model->get_template_by_id($id);
        $data['available_variables'] = $this->_get_available_variables();

        if (!$data['template']) {
            $this->session->set_flashdata('error', 'Template tidak ditemukan.');
            redirect('notification/templates');
        }

        $this->render('admin/notification/template_form', $data);
    }

    /**
     * Save/Create template
     */
    public function save_template()
    {
        $this->form_validation->set_rules('event_key', 'Event Key', 'required|is_unique[notification_templates.event_key]');
        $this->form_validation->set_rules('event_name', 'Event Name', 'required');
        $this->form_validation->set_rules('subject_template', 'Subject', 'required');
        $this->form_validation->set_rules('body_template', 'Body', 'required');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('notification/create_template');
        }

        $data = [
            'event_key' => $this->input->post('event_key'),
            'event_name' => $this->input->post('event_name'),
            'subject_template' => $this->input->post('subject_template'),
            'body_template' => $this->input->post('body_template'),
            'is_active' => $this->input->post('is_active') ? 1 : 0,
            'language' => $this->input->post('language', 'id')
        ];

        $template_id = $this->notification_model->create_template($data);

        if ($template_id) {
            $this->session->set_flashdata('success', 'Template berhasil ditambahkan.');
            redirect('notification/templates');
        } else {
            $this->session->set_flashdata('error', 'Gagal menambahkan template.');
            redirect('notification/create_template');
        }
    }

    /**
     * Update template
     * @param int $id
     */
    public function update_template($id)
    {
        $template = $this->notification_model->get_template_by_id($id);
        if (!$template) {
            $this->session->set_flashdata('error', 'Template tidak ditemukan.');
            redirect('notification/templates');
        }

        $this->form_validation->set_rules('event_name', 'Event Name', 'required');
        $this->form_validation->set_rules('subject_template', 'Subject', 'required');
        $this->form_validation->set_rules('body_template', 'Body', 'required');

        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('notification/edit_template/' . $id);
        }

        $data = [
            'event_name' => $this->input->post('event_name'),
            'subject_template' => $this->input->post('subject_template'),
            'body_template' => $this->input->post('body_template'),
            'is_active' => $this->input->post('is_active') ? 1 : 0,
            'language' => $this->input->post('language', 'id')
        ];

        if ($this->notification_model->update_template($id, $data)) {
            $this->session->set_flashdata('success', 'Template berhasil diperbarui.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui template.');
        }

        redirect('notification/templates');
    }

    /**
     * Toggle template active status (BR-85: deactivate instead of delete)
     * @param int $id
     */
    public function toggle_template($id)
    {
        $template = $this->notification_model->get_template_by_id($id);
        if (!$template) {
            $this->json_error('Template tidak ditemukan.', 404);
            return;
        }

        $new_status = $template['is_active'] ? 0 : 1;
        
        if ($new_status === 0) {
            $result = $this->notification_model->deactivate_template($id);
            $message = 'Template dinonaktifkan. Sistem akan menggunakan template default.';
        } else {
            $result = $this->notification_model->activate_template($id);
            $message = 'Template diaktifkan.';
        }

        if ($result) {
            $this->json_response(['status' => $new_status], 200, $message);
        } else {
            $this->json_error('Gagal mengubah status template.', 500);
        }
    }

    /**
     * Send test notification to admin email
     */
    public function send_test()
    {
        $event_key = $this->input->post('event_key');
        $admin_email = $this->current_user->email;

        if (empty($event_key)) {
            $this->json_error('Event key tidak valid.', 400);
            return;
        }

        $result = $this->notification_model->send_test_notification($admin_email, $event_key);

        if ($result) {
            $this->json_response([], 200, 'Test notifikasi berhasil dikirim ke email Anda: ' . $admin_email);
        } else {
            $this->json_error('Gagal mengirim test notifikasi. Periksa konfigurasi SMTP.', 500);
        }
    }

    /**
     * Get available template variables helper
     * @return array
     */
    private function _get_available_variables()
    {
        return [
            'nama_pengguna' => 'Nama lengkap pengguna',
            'kode_booking' => 'Kode unik booking (format: B-YYYYMMDD-XXXX)',
            'nama_bengkel' => 'Nama bengkel',
            'tanggal_booking' => 'Tanggal booking',
            'waktu_booking' => 'Slot waktu booking',
            'kendaraan' => 'Informasi kendaraan (nomor polisi + merk)',
            'km_terakhir' => 'Kilometer terakhir servis',
            'km_estimasi' => 'Estimasi kilometer saat ini',
            'tanggal_servis' => 'Tanggal servis terakhir',
            'rekomendasi_bengkel' => 'Daftar rekomendasi bengkel terdekat'
        ];
    }
}

/* End of file Notification.php */
/* Location: ./application/controllers/Notification.php */
