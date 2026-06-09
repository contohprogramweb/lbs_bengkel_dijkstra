<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Home Controller
 * 
 * Handles public/home page for general visitors.
 * Shows workshop list, how to use, and login/register links.
 * 
 * @package     Bengkel Terdekat
 * @version     4.0
 */
class Home extends CI_Controller {

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('workshop_model');
        $this->load->helper(['url', 'text']);
    }

    // ================================================================
    // PUBLIC HOME PAGE
    // ================================================================

    /**
     * Home page for public visitors
     */
    public function index()
    {
        $data['page_title'] = 'Beranda';
        $data['app_name'] = $this->config->item('app_name') ?: 'Bengkel Terdekat';
        
        // Get active workshops
        $data['workshops'] = $this->workshop_model->get_active_workshops();
        $data['workshop_count'] = count($data['workshops']);
        
        // Get service categories
        $data['categories'] = $this->workshop_model->get_service_categories();
        
        $this->load->view('home/index', $data);
    }

    /**
     * View workshop details (public)
     * @param int $id Workshop ID
     */
    public function workshop_detail($id)
    {
        $data['page_title'] = 'Detail Bengkel';
        $data['app_name'] = $this->config->item('app_name') ?: 'Bengkel Terdekat';
        
        $data['workshop'] = $this->workshop_model->find_by_id($id);
        
        if (!$data['workshop']) {
            show_404();
            return;
        }
        
        // Get workshop services
        $data['services'] = $this->workshop_model->get_services($id, TRUE);
        $data['categories'] = $this->workshop_model->get_service_categories();
        
        $this->load->view('home/workshop_detail', $data);
    }

    /**
     * How to use page
     */
    public function cara_pakai()
    {
        $data['page_title'] = 'Cara Pakai';
        $data['app_name'] = $this->config->item('app_name') ?: 'Bengkel Terdekat';
        
        $this->load->view('home/cara_pakai', $data);
    }

    /**
     * About page
     */
    public function tentang()
    {
        $data['page_title'] = 'Tentang Kami';
        $data['app_name'] = $this->config->item('app_name') ?: 'Bengkel Terdekat';
        
        $this->load->view('home/tentang', $data);
    }
}

/* End of file Home.php */
/* Location: ./application/controllers/Home.php */
