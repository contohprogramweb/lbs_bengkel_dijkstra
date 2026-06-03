<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Dijkstra Algorithm Library
 * 
 * Implements Dijkstra's shortest path algorithm for finding
 * the shortest route between two nodes in a weighted graph.
 * Uses non-negative weights only.
 *
 * @package     Bengkel Terdekat
 * @version     4.0
 */
class Dijkstra {

    /**
     * Adjacency list representation of the graph
     * Format: [node_id => [[neighbor_id, weight], ...]]
     * @var array
     */
    protected $adjacency_list = [];

    /**
     * Node coordinates for spatial reference
     * Format: [node_id => ['latitude' => lat, 'longitude' => lng]]
     * @var array
     */
    protected $node_coordinates = [];

    /**
     * Constructor
     * @param array $adjacency_list Graph adjacency list
     * @param array $node_coordinates Optional node coordinates
     */
    public function __construct($adjacency_list = [], $node_coordinates = [])
    {
        $this->adjacency_list = $adjacency_list;
        $this->node_coordinates = $node_coordinates;
    }

    /**
     * Set the adjacency list
     * @param array $adjacency_list Graph adjacency list
     */
    public function set_adjacency_list($adjacency_list)
    {
        $this->adjacency_list = $adjacency_list;
    }

    /**
     * Set node coordinates
     * @param array $node_coordinates Node coordinates
     */
    public function set_node_coordinates($node_coordinates)
    {
        $this->node_coordinates = $node_coordinates;
    }

    /**
     * Find shortest path from source to destination
     * 
     * @param int|string $source Source node ID
     * @param int|string $destination Destination node ID
     * @return array Path information including:
     *               - path: array of node IDs
     *               - distance: total distance
     *               - edges: array of edge details with coordinates
     *               - found: boolean indicating if path was found
     */
    public function find_shortest_path($source, $destination)
    {
        // Initialize result
        $result = [
            'path' => [],
            'distance' => 0,
            'edges' => [],
            'found' => FALSE
        ];

        // Handle empty graph
        if (empty($this->adjacency_list)) {
            return $result;
        }

        // Handle same source and destination
        if ($source === $destination) {
            $result['path'] = [$source];
            $result['found'] = TRUE;
            return $result;
        }

        // Check if nodes exist in graph
        if (!isset($this->adjacency_list[$source]) || !isset($this->adjacency_list[$destination])) {
            return $result;
        }

        // Priority queue implementation using SPL
        $pq = new SplPriorityQueue();
        $pq->setExtractFlags(SplPriorityQueue::EXTR_BOTH);

        // Distance array - initialize with infinity
        $distances = [];
        foreach (array_keys($this->adjacency_list) as $node_id) {
            $distances[$node_id] = PHP_FLOAT_MAX;
        }
        $distances[$source] = 0;

        // Previous node array for path reconstruction
        $previous = [];

        // Visited set
        $visited = [];

        // Insert source with priority 0 (negative because SplPriorityQueue extracts max)
        $pq->insert($source, 0);

        while (!$pq->isEmpty()) {
            // Extract minimum distance node
            $extracted = $pq->extract();
            $current = $extracted['data'];
            $current_priority = -$extracted['priority'];

            // Skip if already visited
            if (isset($visited[$current])) {
                continue;
            }

            $visited[$current] = TRUE;

            // If we reached destination, break early
            if ($current === $destination) {
                break;
            }

            // Skip if current distance is worse than recorded
            if ($current_priority > $distances[$current]) {
                continue;
            }

            // Explore neighbors
            if (isset($this->adjacency_list[$current])) {
                foreach ($this->adjacency_list[$current] as $neighbor_info) {
                    $neighbor = $neighbor_info['to'];
                    $weight = $neighbor_info['weight'];

                    // Validate non-negative weight
                    if ($weight < 0) {
                        continue; // Skip negative weights (Dijkstra doesn't support them)
                    }

                    // Calculate new distance
                    $new_distance = $distances[$current] + $weight;

                    // If shorter path found
                    if ($new_distance < $distances[$neighbor]) {
                        $distances[$neighbor] = $new_distance;
                        $previous[$neighbor] = [
                            'from' => $current,
                            'weight' => $weight
                        ];

                        // Insert/update in priority queue
                        $pq->insert($neighbor, -$new_distance);
                    }
                }
            }
        }

        // Check if destination was reached
        if (!isset($previous[$destination]) && $source !== $destination) {
            // No path found - try direct Euclidean calculation as fallback
            return $this->calculate_euclidean_fallback($source, $destination);
        }

        // Reconstruct path
        $path = [];
        $edges = [];
        $current = $destination;

        while ($current !== NULL) {
            array_unshift($path, $current);

            if (isset($previous[$current])) {
                $from = $previous[$current]['from'];
                $weight = $previous[$current]['weight'];

                array_unshift($edges, [
                    'from' => $from,
                    'to' => $current,
                    'distance' => $weight,
                    'from_coords' => isset($this->node_coordinates[$from]) ? $this->node_coordinates[$from] : NULL,
                    'to_coords' => isset($this->node_coordinates[$current]) ? $this->node_coordinates[$current] : NULL
                ]);

                $current = $from;
            } else {
                $current = NULL;
            }
        }

        $result['path'] = $path;
        $result['distance'] = $distances[$destination];
        $result['edges'] = $edges;
        $result['found'] = TRUE;

        return $result;
    }

    /**
     * Calculate distances from source to all reachable nodes
     * 
     * @param int|string $source Source node ID
     * @return array Distances array [node_id => distance]
     */
    public function calculate_all_distances($source)
    {
        $distances = [];
        foreach (array_keys($this->adjacency_list) as $node_id) {
            $distances[$node_id] = PHP_FLOAT_MAX;
        }
        $distances[$source] = 0;

        $pq = new SplPriorityQueue();
        $pq->setExtractFlags(SplPriorityQueue::EXTR_BOTH);
        $pq->insert($source, 0);

        $visited = [];

        while (!$pq->isEmpty()) {
            $extracted = $pq->extract();
            $current = $extracted['data'];

            if (isset($visited[$current])) {
                continue;
            }

            $visited[$current] = TRUE;

            if (isset($this->adjacency_list[$current])) {
                foreach ($this->adjacency_list[$current] as $neighbor_info) {
                    $neighbor = $neighbor_info['to'];
                    $weight = $neighbor_info['weight'];

                    if ($weight < 0) {
                        continue;
                    }

                    $new_distance = $distances[$current] + $weight;

                    if ($new_distance < $distances[$neighbor]) {
                        $distances[$neighbor] = $new_distance;
                        $pq->insert($neighbor, -$new_distance);
                    }
                }
            }
        }

        return $distances;
    }

    /**
     * Fallback to Euclidean distance when no path exists in graph
     * 
     * @param int|string $source Source node ID
     * @param int|string $destination Destination node ID
     * @return array Path information with direct line
     */
    protected function calculate_euclidean_fallback($source, $destination)
    {
        $result = [
            'path' => [$source, $destination],
            'distance' => 0,
            'edges' => [],
            'found' => FALSE
        ];

        // Calculate if we have coordinates
        if (isset($this->node_coordinates[$source]) && isset($this->node_coordinates[$destination])) {
            $lat1 = $this->node_coordinates[$source]['latitude'];
            $lng1 = $this->node_coordinates[$source]['longitude'];
            $lat2 = $this->node_coordinates[$destination]['latitude'];
            $lng2 = $this->node_coordinates[$destination]['longitude'];

            $distance = $this->haversine_distance($lat1, $lng1, $lat2, $lng2);
            
            $result['distance'] = $distance;
            $result['edges'] = [[
                'from' => $source,
                'to' => $destination,
                'distance' => $distance,
                'from_coords' => $this->node_coordinates[$source],
                'to_coords' => $this->node_coordinates[$destination],
                'is_direct' => TRUE
            ]];
            $result['found'] = TRUE; // Mark as found but note it's direct
        }

        return $result;
    }

    /**
     * Calculate Haversine distance between two coordinates
     * 
     * @param float $lat1 Latitude of point 1
     * @param float $lng1 Longitude of point 1
     * @param float $lat2 Latitude of point 2
     * @param float $lng2 Longitude of point 2
     * @return float Distance in kilometers
     */
    public function haversine_distance($lat1, $lng1, $lat2, $lng2)
    {
        $earth_radius = 6371; // Earth radius in kilometers

        $lat1_rad = deg2rad($lat1);
        $lat2_rad = deg2rad($lat2);
        $delta_lat = deg2rad($lat2 - $lat1);
        $delta_lng = deg2rad($lng2 - $lng1);

        $a = sin($delta_lat / 2) * sin($delta_lat / 2) +
             cos($lat1_rad) * cos($lat2_rad) *
             sin($delta_lng / 2) * sin($delta_lng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earth_radius * $c;
    }

    /**
     * Estimate travel time based on distance
     * 
     * @param float $distance_km Distance in kilometers
     * @param float $avg_speed_kmh Average speed in km/h (default 30 km/h for urban)
     * @return int Estimated time in minutes
     */
    public function estimate_travel_time($distance_km, $avg_speed_kmh = 30)
    {
        if ($distance_km <= 0) {
            return 0;
        }
        return (int) ceil(($distance_km / $avg_speed_kmh) * 60);
    }
}

/* End of file Dijkstra.php */
/* Location: ./application/libraries/Dijkstra.php */
