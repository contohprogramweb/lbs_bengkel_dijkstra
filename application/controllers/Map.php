<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Map Controller
 * 
 * Handles workshop search with map visualization using Leaflet.js,
 * user geolocation, and Dijkstra shortest path algorithm.
 *
 * @package     Bengkel Terdekat
 * @version     4.0
 */
class Map extends Public_Controller {

    /**
     * Workshop model instance
     */
    private $workshop_model;

    /**
     * Road graph model instance
     */
    private $road_graph_model;

    /**
     * Dijkstra library instance
     */
    private $dijkstra;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('workshop_model');
        $this->load->model('road_graph_model');
        $this->load->library('dijkstra');
    }

    // --------------------------------------------------------------------
    // Main Map Page
    // --------------------------------------------------------------------

    /**
     * Display main map page for workshop search
     */
    public function index()
    {
        $data['page_title'] = 'Cari Bengkel Terdekat';
        $data['user'] = $this->current_user;

        // Get default radius from system settings or use 10km
        $data['default_radius'] = $this->get_setting('radius_pencarian', 10);

        $this->render('user/map_search', $data);
    }

    // --------------------------------------------------------------------
    // AJAX API Methods
    // --------------------------------------------------------------------

    /**
     * Get nearby workshops based on user location
     * POST: latitude, longitude, radius (optional)
     */
    public function get_nearby_workshops()
    {
        if (!$this->input->is_ajax_request()) {
            show_error('Akses tidak diizinkan.', 403);
        }

        $latitude = $this->input->post('latitude', TRUE);
        $longitude = $this->input->post('longitude', TRUE);
        $radius_km = $this->input->post('radius', TRUE) ?: 10;

        if (!$latitude || !$longitude) {
            $this->json_error('Koordinat lokasi tidak valid.', 400);
            return;
        }

        // Validate coordinates
        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            $this->json_error('Koordinat harus berupa angka.', 400);
            return;
        }

        // Get all active workshops
        $workshops = $this->workshop_model->get_all(['status' => 'active'], NULL, 0);

        $nearby_workshops = [];
        
        // Load Dijkstra for route calculation
        $adjacency = $this->road_graph_model->build_adjacency_list(TRUE);
        $nodes = $this->road_graph_model->get_all_nodes(TRUE);
        
        // Build node coordinates array
        $node_coords = [];
        foreach ($nodes as $node) {
            $node_coords[$node->id] = [
                'latitude' => (float)$node->latitude,
                'longitude' => (float)$node->longitude
            ];
        }

        $this->dijkstra->set_adjacency_list($adjacency);
        $this->dijkstra->set_node_coordinates($node_coords);

        // Find nearest road node to user location
        $user_node = $this->road_graph_model->get_nearest_node($latitude, $longitude, 2.0);

        foreach ($workshops as $workshop) {
            // Skip if no coordinates
            if (!$workshop->latitude || !$workshop->longitude) {
                continue;
            }

            // Calculate Euclidean distance first for filtering
            $euclidean_distance = $this->dijkstra->haversine_distance(
                $latitude, $longitude,
                $workshop->latitude, $workshop->longitude
            );

            // Only include if within radius (Euclidean filter)
            if ($euclidean_distance <= $radius_km) {
                // Try to calculate route distance using Dijkstra
                $route_distance = $euclidean_distance;
                $route_path = [];
                $travel_time = $this->dijkstra->estimate_travel_time($euclidean_distance);

                // If we have road graph data, try to find actual route
                if ($user_node && count($adjacency) > 0) {
                    // Find nearest node to workshop
                    $workshop_node = $this->road_graph_model->get_nearest_node(
                        $workshop->latitude, 
                        $workshop->longitude, 
                        0.5
                    );

                    if ($workshop_node) {
                        $path_result = $this->dijkstra->find_shortest_path(
                            $user_node->id, 
                            $workshop_node->id
                        );

                        if ($path_result['found']) {
                            $route_distance = $path_result['distance'];
                            $route_path = $path_result['edges'];
                            $travel_time = $this->dijkstra->estimate_travel_time($route_distance);
                        }
                    }
                }

                $nearby_workshops[] = [
                    'id' => $workshop->id,
                    'name' => $workshop->name,
                    'address' => $workshop->address,
                    'city' => $workshop->city,
                    'phone' => $workshop->phone,
                    'latitude' => (float)$workshop->latitude,
                    'longitude' => (float)$workshop->longitude,
                    'rating_avg' => (float)$workshop->rating_avg,
                    'total_reviews' => (int)$workshop->total_reviews,
                    'distance_km' => round($euclidean_distance, 2),
                    'route_distance_km' => round($route_distance, 2),
                    'travel_time_minutes' => $travel_time,
                    'route_path' => $route_path,
                    'description' => $workshop->description,
                    'logo' => $workshop->logo
                ];
            }
        }

        // Sort by route distance (not Euclidean)
        usort($nearby_workshops, function($a, $b) {
            return $a['route_distance_km'] <=> $b['route_distance_km'];
        });

        $this->json_response([
            'workshops' => $nearby_workshops,
            'user_location' => [
                'latitude' => (float)$latitude,
                'longitude' => (float)$longitude
            ],
            'count' => count($nearby_workshops)
        ], 200, 'Success');
    }

    /**
     * Get detailed route information to a specific workshop
     * POST: workshop_id, user_latitude, user_longitude
     */
    public function get_route_to_workshop()
    {
        if (!$this->input->is_ajax_request()) {
            show_error('Akses tidak diizinkan.', 403);
        }

        $workshop_id = $this->input->post('workshop_id', TRUE);
        $user_lat = $this->input->post('user_latitude', TRUE);
        $user_lng = $this->input->post('user_longitude', TRUE);

        if (!$workshop_id || !$user_lat || !$user_lng) {
            $this->json_error('Parameter tidak lengkap.', 400);
            return;
        }

        // Get workshop details
        $workshop = $this->workshop_model->find_by_id($workshop_id);

        if (!$workshop || !$workshop->latitude || !$workshop->longitude) {
            $this->json_error('Bengkel tidak ditemukan atau tidak memiliki koordinat.', 404);
            return;
        }

        // Build graph and run Dijkstra
        $adjacency = $this->road_graph_model->build_adjacency_list(TRUE);
        $nodes = $this->road_graph_model->get_all_nodes(TRUE);
        
        $node_coords = [];
        foreach ($nodes as $node) {
            $node_coords[$node->id] = [
                'latitude' => (float)$node->latitude,
                'longitude' => (float)$node->longitude
            ];
        }

        $this->dijkstra->set_adjacency_list($adjacency);
        $this->dijkstra->set_node_coordinates($node_coords);

        // Find nearest nodes
        $user_node = $this->road_graph_model->get_nearest_node($user_lat, $user_lng, 2.0);
        $workshop_node = $this->road_graph_model->get_nearest_node(
            $workshop->latitude, 
            $workshop->longitude, 
            0.5
        );

        // Calculate direct distance as fallback
        $direct_distance = $this->dijkstra->haversine_distance(
            $user_lat, $user_lng,
            $workshop->latitude, $workshop->longitude
        );

        $route_result = [
            'direct_distance_km' => round($direct_distance, 2),
            'route_distance_km' => round($direct_distance, 2),
            'travel_time_minutes' => $this->dijkstra->estimate_travel_time($direct_distance),
            'path' => [],
            'coordinates' => []
        ];

        // Try to find actual route if graph is available
        if ($user_node && $workshop_node && count($adjacency) > 0) {
            $path_result = $this->dijkstra->find_shortest_path(
                $user_node->id, 
                $workshop_node->id
            );

            if ($path_result['found']) {
                $route_result['route_distance_km'] = round($path_result['distance'], 2);
                $route_result['travel_time_minutes'] = $this->dijkstra->estimate_travel_time($path_result['distance']);
                $route_result['path'] = $path_result['path'];
                
                // Build coordinate array for polyline
                foreach ($path_result['edges'] as $edge) {
                    if ($edge['from_coords']) {
                        $route_result['coordinates'][] = [
                            'lat' => $edge['from_coords']['latitude'],
                            'lng' => $edge['from_coords']['longitude']
                        ];
                    }
                    if ($edge['to_coords']) {
                        $route_result['coordinates'][] = [
                            'lat' => $edge['to_coords']['latitude'],
                            'lng' => $edge['to_coords']['longitude']
                        ];
                    }
                }
            }
        }

        // Add workshop info
        $route_result['workshop'] = [
            'id' => $workshop->id,
            'name' => $workshop->name,
            'address' => $workshop->address,
            'latitude' => (float)$workshop->latitude,
            'longitude' => (float)$workshop->longitude
        ];

        $this->json_response($route_result, 200, 'Success');
    }

    /**
     * Calculate distance between two points (utility)
     * POST: lat1, lng1, lat2, lng2
     */
    public function calculate_distance()
    {
        if (!$this->input->is_ajax_request()) {
            show_error('Akses tidak diizinkan.', 403);
        }

        $lat1 = $this->input->post('lat1', TRUE);
        $lng1 = $this->input->post('lng1', TRUE);
        $lat2 = $this->input->post('lat2', TRUE);
        $lng2 = $this->input->post('lng2', TRUE);

        if (!$lat1 || !$lng1 || !$lat2 || !$lng2) {
            $this->json_error('Koordinat tidak lengkap.', 400);
            return;
        }

        $distance = $this->dijkstra->haversine_distance($lat1, $lng1, $lat2, $lng2);
        $travel_time = $this->dijkstra->estimate_travel_time($distance);

        $this->json_response([
            'distance_km' => round($distance, 2),
            'travel_time_minutes' => $travel_time,
            'method' => 'haversine'
        ], 200, 'Success');
    }
}

/* End of file Map.php */
/* Location: ./application/controllers/Map.php */
