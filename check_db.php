<?php
// Simple script to check database structure
$host = 'localhost';
$db   = 'dijkstrabengkelbaru';
$user = 'root';
$pass = 'root';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if activity_logs table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'activity_logs'");
    if ($stmt->rowCount() == 0) {
        echo "Tabel activity_logs tidak ada!\n";
        exit(1);
    }
    
    // Get table structure
    echo "Struktur tabel activity_logs:\n";
    $stmt = $pdo->query("DESCRIBE activity_logs");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "{$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Key']}\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
