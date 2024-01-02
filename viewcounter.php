<?php
// Define constants for database path and table name for easier maintenance
define('DATABASE_PATH', 'viewcounts.sqlite');
define('TABLE_NAME', 'view_counts');

// Function to get the image URL without the query string
function getImageUrl() {
    return strtok($_SERVER['REQUEST_URI'], '?');
}

// Function to create and return the PDO object
function getPdo() {
    $pdo = new PDO('sqlite:' . DATABASE_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

// Function to update view counts
function updateViewCounts($pdo, $imageUrl) {
    // Use a transaction for atomicity
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO " . TABLE_NAME . " (image_url, count) VALUES (:image_url, 1)");
    $stmt->bindParam(':image_url', $imageUrl);
    $stmt->execute();

    $stmt = $pdo->prepare("UPDATE " . TABLE_NAME . " SET count = count + 1 WHERE image_url = :image_url");
    $stmt->bindParam(':image_url', $imageUrl);
    $stmt->execute();
    $pdo->commit();
}

$imageUrl = getImageUrl();
$imagePath = substr($imageUrl, 1); // Remove the leading slash

if (!file_exists($imagePath)) {
    header('HTTP/1.1 404 Not Found');
    header('Content-Type: text/plain');
    exit('Image not found');
}

$pdo = getPdo();
if (strpos($imageUrl, '/.thumb/') === false) {
    updateViewCounts($pdo, $imageUrl);
}

// Utilize readfile() for better memory usage with large files
$imageMimeType = mime_content_type($imagePath);
header('Content-Type: ' . $imageMimeType);
header('Content-Length: ' . filesize($imagePath));
readfile($imagePath);
?>
