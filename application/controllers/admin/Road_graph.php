<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Road_graph extends Admin_Controller {

    private $road_graph_model;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('road_graph_model');
        $this->load->library('form_validation');
    }

    public function index()
    {
        $data['page_title'] = 'Manajemen Graf Jalan';
        $data['stats'] = [
            'total_nodes' => $this->road_graph_model->count_nodes(),
            'total_edges' => $this->road_graph_model->count_edges()
        ];
        $this->render('admin/road_graph/dashboard', $data);
    }

    public function nodes()
    {
        $data['page_title'] = 'Daftar Simpul Jalan';
        $data['nodes'] = $this->road_graph_model->get_all_nodes(FALSE);
        $this->render('admin/road_graph/nodes', $data);
    }

    public function create_node()
    {
        $data['page_title'] = 'Tambah Simpul Baru';
        if ($this->input->post()) {
            $this->form_validation->set_rules('name', 'Nama Simpul', 'required|trim|max_length[100]');
            $this->form_validation->set_rules('latitude', 'Latitude', 'required|numeric|less_than[90]|greater_than[-90]');
            $this->form_validation->set_rules('longitude', 'Longitude', 'required|numeric|less_than[180]|greater_than[-180]');

            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
            } else {
                $node_data = [
                    'name' => $this->input->post('name', TRUE),
                    'latitude' => $this->input->post('latitude', TRUE),
                    'longitude' => $this->input->post('longitude', TRUE),
                    'node_type' => $this->input->post('node_type', TRUE) ?: 'intersection',
                    'description' => $this->input->post('description', TRUE),
                    'is_active' => 1
                ];
                $node_id = $this->road_graph_model->insert_node($node_data);
                if ($node_id) {
                    $this->session->set_flashdata('success', 'Simpul berhasil ditambahkan.');
                    redirect('admin/road_graph/nodes');
                } else {
                    $this->session->set_flashdata('error', 'Gagal menambahkan simpul.');
                }
            }
        }
        $this->render('admin/road_graph/node_form', $data);
    }

    public function edit_node($id)
    {
        $data['page_title'] = 'Edit Simpul';
        $node = $this->road_graph_model->get_node_by_id($id);
        if (!$node) {
            $this->session->set_flashdata('error', 'Simpul tidak ditemukan.');
            redirect('admin/road_graph/nodes');
        }
        $data['node'] = $node;
        if ($this->input->post()) {
            $this->form_validation->set_rules('name', 'Nama Simpul', 'required|trim|max_length[100]');
            $this->form_validation->set_rules('latitude', 'Latitude', 'required|numeric|less_than[90]|greater_than[-90]');
            $this->form_validation->set_rules('longitude', 'Longitude', 'required|numeric|less_than[180]|greater_than[-180]');
            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
            } else {
                $node_data = [
                    'name' => $this->input->post('name', TRUE),
                    'latitude' => $this->input->post('latitude', TRUE),
                    'longitude' => $this->input->post('longitude', TRUE),
                    'node_type' => $this->input->post('node_type', TRUE),
                    'description' => $this->input->post('description', TRUE),
                    'is_active' => $this->input->post('is_active', TRUE) ? 1 : 0
                ];
                if ($this->road_graph_model->update_node($id, $node_data)) {
                    $this->session->set_flashdata('success', 'Simpul berhasil diperbarui.');
                    redirect('admin/road_graph/nodes');
                } else {
                    $this->session->set_flashdata('error', 'Gagal memperbarui simpul.');
                }
            }
        }
        $this->render('admin/road_graph/node_form', $data);
    }

    public function delete_node($id)
    {
        if ($this->road_graph_model->delete_node($id)) {
            $this->session->set_flashdata('success', 'Simpul berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus simpul.');
        }
        redirect('admin/road_graph/nodes');
    }

    public function edges()
    {
        $data['page_title'] = 'Daftar Edge Jalan';
        $data['edges'] = $this->road_graph_model->get_all_edges(FALSE);
        $data['nodes'] = $this->road_graph_model->get_all_nodes(TRUE);
        $this->render('admin/road_graph/edges', $data);
    }

    public function create_edge()
    {
        $data['page_title'] = 'Tambah Edge Baru';
        $data['nodes'] = $this->road_graph_model->get_all_nodes(TRUE);
        if ($this->input->post()) {
            $this->form_validation->set_rules('from_node_id', 'Dari Simpul', 'required|integer');
            $this->form_validation->set_rules('to_node_id', 'Ke Simpul', 'required|integer');
            $this->form_validation->set_rules('distance_km', 'Jarak (km)', 'required|numeric|greater_than[0]');
            $this->form_validation->set_rules('road_name', 'Nama Jalan', 'trim|max_length[150]');
            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
            } else {
                $edge_data = [
                    'from_node_id' => $this->input->post('from_node_id', TRUE),
                    'to_node_id' => $this->input->post('to_node_id', TRUE),
                    'distance_km' => $this->input->post('distance_km', TRUE),
                    'road_name' => $this->input->post('road_name', TRUE),
                    'is_bidirectional' => $this->input->post('is_bidirectional', TRUE) ? 1 : 0,
                    'is_active' => 1
                ];
                $edge_id = $this->road_graph_model->insert_edge($edge_data);
                if ($edge_id) {
                    $this->session->set_flashdata('success', 'Edge berhasil ditambahkan.');
                    redirect('admin/road_graph/edges');
                } else {
                    $this->session->set_flashdata('error', 'Gagal menambahkan edge.');
                }
            }
        }
        $this->render('admin/road_graph/edge_form', $data);
    }

    public function edit_edge($id)
    {
        $data['page_title'] = 'Edit Edge';
        $edge = $this->road_graph_model->get_edge_by_id($id);
        if (!$edge) {
            $this->session->set_flashdata('error', 'Edge tidak ditemukan.');
            redirect('admin/road_graph/edges');
        }
        $data['edge'] = $edge;
        $data['nodes'] = $this->road_graph_model->get_all_nodes(TRUE);
        if ($this->input->post()) {
            $this->form_validation->set_rules('from_node_id', 'Dari Simpul', 'required|integer');
            $this->form_validation->set_rules('to_node_id', 'Ke Simpul', 'required|integer');
            $this->form_validation->set_rules('distance_km', 'Jarak (km)', 'required|numeric|greater_than[0]');
            if ($this->form_validation->run() === FALSE) {
                $this->session->set_flashdata('error', validation_errors());
            } else {
                $edge_data = [
                    'from_node_id' => $this->input->post('from_node_id', TRUE),
                    'to_node_id' => $this->input->post('to_node_id', TRUE),
                    'distance_km' => $this->input->post('distance_km', TRUE),
                    'road_name' => $this->input->post('road_name', TRUE),
                    'is_bidirectional' => $this->input->post('is_bidirectional', TRUE) ? 1 : 0,
                    'is_active' => $this->input->post('is_active', TRUE) ? 1 : 0
                ];
                if ($this->road_graph_model->update_edge($id, $edge_data)) {
                    $this->session->set_flashdata('success', 'Edge berhasil diperbarui.');
                    redirect('admin/road_graph/edges');
                } else {
                    $this->session->set_flashdata('error', 'Gagal memperbarui edge.');
                }
            }
        }
        $this->render('admin/road_graph/edge_form', $data);
    }

    public function delete_edge($id)
    {
        if ($this->road_graph_model->delete_edge($id)) {
            $this->session->set_flashdata('success', 'Edge berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus edge.');
        }
        redirect('admin/road_graph/edges');
    }
}
