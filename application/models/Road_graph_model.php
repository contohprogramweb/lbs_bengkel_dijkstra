<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Road Graph Model
 * 
 * Handles road graph data operations for Dijkstra algorithm.
 * Manages nodes (intersections) and edges (road segments).
 *
 * @package     Bengkel Terdekat
 * @version     4.0
 */
class Road_graph_model extends CI_Model {

    const TABLE_NODES = 'road_nodes';
    const TABLE_EDGES = 'road_edges';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // ============================================================
    // NODE OPERATIONS
    // ============================================================

    /**
     * Get all road nodes
     * @param bool $only_active Only active nodes
     * @return array Array of node objects
     */
    public function get_all_nodes($only_active = TRUE)
    {
        $this->db->from(self::TABLE_NODES);
        
        if ($only_active) {
            $this->db->where('is_active', 1);
        }
        
        $this->db->order_by('id', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Get node by ID
     * @param int $id Node ID
     * @return object|null Node object or NULL
     */
    public function get_node_by_id($id)
    {
        $this->db->from(self::TABLE_NODES);
        $this->db->where('id', $id);
        return $this->db->get()->row();
    }

    /**
     * Insert new road node
     * @param array $data Node data
     * @return int|bool New node ID or FALSE on failure
     */
    public function insert_node($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        if ($this->db->insert(self::TABLE_NODES, $data)) {
            return $this->db->insert_id();
        }
        return FALSE;
    }

    /**
     * Update road node
     * @param int $id Node ID
     * @param array $data Node data to update
     * @return bool TRUE on success, FALSE on failure
     */
    public function update_node($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        return $this->db->update(self::TABLE_NODES, $data);
    }

    /**
     * Delete road node (soft delete)
     * @param int $id Node ID
     * @return bool TRUE on success, FALSE on failure
     */
    public function delete_node($id)
    {
        $data = [
            'is_active' => 0,
            'deleted_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $this->db->where('id', $id);
        return $this->db->update(self::TABLE_NODES, $data);
    }

    /**
     * Hard delete node
     * @param int $id Node ID
     * @return bool TRUE on success, FALSE on failure
     */
    public function hard_delete_node($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete(self::TABLE_NODES);
    }

    /**
     * Get nearest node to coordinates
     * @param float $latitude Latitude
     * @param float $longitude Longitude
     * @param float $max_distance_km Maximum distance in km
     * @return object|null Nearest node or NULL
     */
    public function get_nearest_node($latitude, $longitude, $max_distance_km = 1.0)
    {
        // Simple Euclidean distance query (for MVP)
        // In production, use proper spatial index or PostGIS
        $this->db->select("*, 
            SQRT(POW((latitude - {$latitude}) * 111.32, 2) + 
                 POW((longitude - {$longitude}) * 111.32 * COS(RADIANS({$latitude})), 2)) as distance_km");
        $this->db->from(self::TABLE_NODES);
        $this->db->where('is_active', 1);
        $this->db->having('distance_km <=', $max_distance_km);
        $this->db->order_by('distance_km', 'ASC');
        $this->db->limit(1);
        
        return $this->db->get()->row();
    }

    // ============================================================
    // EDGE OPERATIONS
    // ============================================================

    /**
     * Get all road edges
     * @param bool $only_active Only active edges
     * @return array Array of edge objects
     */
    public function get_all_edges($only_active = TRUE)
    {
        $this->db->select('e.*, n1.name as from_node_name, n2.name as to_node_name');
        $this->db->from(self::TABLE_EDGES . ' e');
        $this->db->join(self::TABLE_NODES . ' n1', 'e.from_node_id = n1.id', 'left');
        $this->db->join(self::TABLE_NODES . ' n2', 'e.to_node_id = n2.id', 'left');
        
        if ($only_active) {
            $this->db->where('e.is_active', 1);
        }
        
        $this->db->order_by('e.id', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Get edge by ID
     * @param int $id Edge ID
     * @return object|null Edge object or NULL
     */
    public function get_edge_by_id($id)
    {
        $this->db->from(self::TABLE_EDGES);
        $this->db->where('id', $id);
        return $this->db->get()->row();
    }

    /**
     * Get edges connected to a node
     * @param int $node_id Node ID
     * @param string $direction 'outgoing', 'incoming', or 'both'
     * @return array Array of edge objects
     */
    public function get_edges_by_node($node_id, $direction = 'outgoing')
    {
        $this->db->from(self::TABLE_EDGES);
        $this->db->where('is_active', 1);
        
        if ($direction === 'outgoing') {
            $this->db->where('from_node_id', $node_id);
        } elseif ($direction === 'incoming') {
            $this->db->where('to_node_id', $node_id);
        } else {
            $this->db->group_start();
            $this->db->where('from_node_id', $node_id);
            $this->db->or_where('to_node_id', $node_id);
            $this->db->group_end();
        }
        
        return $this->db->get()->result();
    }

    /**
     * Insert new road edge
     * @param array $data Edge data
     * @return int|bool New edge ID or FALSE on failure
     */
    public function insert_edge($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        if ($this->db->insert(self::TABLE_EDGES, $data)) {
            return $this->db->insert_id();
        }
        return FALSE;
    }

    /**
     * Update road edge
     * @param int $id Edge ID
     * @param array $data Edge data to update
     * @return bool TRUE on success, FALSE on failure
     */
    public function update_edge($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        return $this->db->update(self::TABLE_EDGES, $data);
    }

    /**
     * Delete road edge (soft delete)
     * @param int $id Edge ID
     * @return bool TRUE on success, FALSE on failure
     */
    public function delete_edge($id)
    {
        $data = [
            'is_active' => 0,
            'deleted_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $this->db->where('id', $id);
        return $this->db->update(self::TABLE_EDGES, $data);
    }

    /**
     * Hard delete edge
     * @param int $id Edge ID
     * @return bool TRUE on success, FALSE on failure
     */
    public function hard_delete_edge($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete(self::TABLE_EDGES);
    }

    /**
     * Build adjacency list for graph algorithms
     * @param bool $bidirectional Consider edges as bidirectional
     * @return array Adjacency list [node_id => [[neighbor_id, weight], ...]]
     */
    public function build_adjacency_list($bidirectional = TRUE)
    {
        $adjacency = [];
        
        // Get all active nodes
        $nodes = $this->get_all_nodes(TRUE);
        foreach ($nodes as $node) {
            $adjacency[$node->id] = [];
        }
        
        // Get all active edges
        $edges = $this->get_all_edges(TRUE);
        foreach ($edges as $edge) {
            // Ensure both nodes exist in adjacency list
            if (!isset($adjacency[$edge->from_node_id])) {
                $adjacency[$edge->from_node_id] = [];
            }
            if (!isset($adjacency[$edge->to_node_id])) {
                $adjacency[$edge->to_node_id] = [];
            }
            
            // Add edge (directed)
            $adjacency[$edge->from_node_id][] = [
                'to' => $edge->to_node_id,
                'weight' => (float)$edge->distance_km
            ];
            
            // Add reverse edge if bidirectional
            if ($bidirectional && $edge->is_bidirectional) {
                $adjacency[$edge->to_node_id][] = [
                    'to' => $edge->from_node_id,
                    'weight' => (float)$edge->distance_km
                ];
            }
        }
        
        return $adjacency;
    }

    /**
     * Count total nodes
     * @param bool $only_active Only active nodes
     * @return int Total count
     */
    public function count_nodes($only_active = TRUE)
    {
        $this->db->from(self::TABLE_NODES);
        if ($only_active) {
            $this->db->where('is_active', 1);
        }
        return $this->db->count_all_results();
    }

    /**
     * Count total edges
     * @param bool $only_active Only active edges
     * @return int Total count
     */
    public function count_edges($only_active = TRUE)
    {
        $this->db->from(self::TABLE_EDGES);
        if ($only_active) {
            $this->db->where('is_active', 1);
        }
        return $this->db->count_all_results();
    }
}

/* End of file Road_graph_model.php */
/* Location: ./application/models/Road_graph_model.php */
