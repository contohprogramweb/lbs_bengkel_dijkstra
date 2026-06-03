-- Migration: Road Graph Tables for Dijkstra Algorithm
-- Version: 4.0
-- Description: Creates tables for road graph (nodes and edges) to support shortest path calculation

-- Table: road_nodes
-- Stores intersection points and landmarks for the road graph
CREATE TABLE IF NOT EXISTS road_nodes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL COMMENT 'Nama simpul/persimpangan',
    latitude DECIMAL(10, 8) NOT NULL COMMENT 'Latitude koordinat',
    longitude DECIMAL(11, 8) NOT NULL COMMENT 'Longitude koordinat',
    node_type ENUM('intersection', 'landmark', 'custom') DEFAULT 'intersection' COMMENT 'Tipe simpul',
    description TEXT NULL COMMENT 'Deskripsi tambahan',
    is_active TINYINT(1) DEFAULT 1 COMMENT 'Status aktif/nonaktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL COMMENT 'Soft delete timestamp',
    INDEX idx_coordinates (latitude, longitude),
    INDEX idx_active (is_active),
    INDEX idx_node_type (node_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: road_edges
-- Stores road segments connecting nodes with distance weights
CREATE TABLE IF NOT EXISTS road_edges (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    from_node_id INT UNSIGNED NOT NULL COMMENT 'Simpul asal',
    to_node_id INT UNSIGNED NOT NULL COMMENT 'Simpul tujuan',
    road_name VARCHAR(150) NULL COMMENT 'Nama jalan',
    distance_km DECIMAL(10, 4) NOT NULL COMMENT 'Jarak dalam kilometer (bobot edge)',
    is_bidirectional TINYINT(1) DEFAULT 1 COMMENT 'Apakah dua arah',
    is_active TINYINT(1) DEFAULT 1 COMMENT 'Status aktif/nonaktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL COMMENT 'Soft delete timestamp',
    FOREIGN KEY (from_node_id) REFERENCES road_nodes(id) ON DELETE CASCADE,
    FOREIGN KEY (to_node_id) REFERENCES road_nodes(id) ON DELETE CASCADE,
    INDEX idx_from_node (from_node_id),
    INDEX idx_to_node (to_node_id),
    INDEX idx_active (is_active),
    CONSTRAINT chk_distance_positive CHECK (distance_km > 0),
    CONSTRAINT chk_different_nodes CHECK (from_node_id != to_node_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample data for testing (optional - can be removed in production)
-- Insert sample nodes (intersections in Jakarta area)
INSERT INTO road_nodes (name, latitude, longitude, node_type, description) VALUES
('Simpang HI', -6.194732, 106.822917, 'intersection', 'Bundaran Hotel Indonesia'),
('Simpang Thamrin-Sudirman', -6.197444, 106.821667, 'intersection', 'Pertemuan Jl. Thamrin dan Sudirman'),
('Simpang Kuningan', -6.216667, 106.823889, 'intersection', 'Rasuna Said/Kuningan'),
('Simpang Semanggi', -6.212778, 106.805556, 'intersection', 'Simpang Susun Semanggi'),
('Simpang Blok M', -6.242222, 106.798333, 'intersection', 'Terminal Blok M');

-- Insert sample edges (road segments)
INSERT INTO road_edges (from_node_id, to_node_id, road_name, distance_km, is_bidirectional) VALUES
(1, 2, 'Jl. M.H. Thamrin', 0.35, 1),
(2, 3, 'Jl. Jend. Sudirman', 2.15, 1),
(3, 4, 'Jl. Prof. Dr. Satrio', 2.50, 1),
(4, 5, 'Jl. Jend. Sudirman', 3.20, 1),
(2, 4, 'Jl. H.R. Rasuna Said', 1.80, 1);

-- Add indexes for performance
CREATE INDEX idx_road_edges_route ON road_edges(from_node_id, to_node_id, is_active);
CREATE INDEX idx_road_nodes_location ON road_nodes(latitude, longitude, is_active);

-- Grant permissions (adjust as needed)
-- GRANT SELECT, INSERT, UPDATE, DELETE ON road_nodes TO your_app_user;
-- GRANT SELECT, INSERT, UPDATE, DELETE ON road_edges TO your_app_user;

SELECT 'Road graph tables created successfully!' AS status;
SELECT COUNT(*) AS total_nodes FROM road_nodes;
SELECT COUNT(*) AS total_edges FROM road_edges;
