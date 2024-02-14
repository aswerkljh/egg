<?php
// Define the path to the SQLite database
define('DATABASE_PATH', 'viewcounts.sqlite');

// Singleton pattern for database connection
class Database {
    private static $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new PDO('sqlite:' . DATABASE_PATH);
            self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return self::$instance;
    }
}

// Function to read and parse view counts from the SQLite database
function getViewCounts($limit = 50) {
    $counts = [];

    try {
        $pdo = Database::getInstance();
        $query = 'SELECT image_url, count FROM view_counts ORDER BY count DESC LIMIT :limit';
        $statement = $pdo->prepare($query);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();
        $counts = $statement->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Log error instead of displaying it
        error_log('Database Error: ' . $e->getMessage());
    }
    return $counts;
}

// Function to calculate the total counts from all entries in the database
function getTotalCounts() {
    $pdo = Database::getInstance();
    $query = 'SELECT SUM(count) AS total FROM view_counts';
    $statement = $pdo->query($query);
    $result = $statement->fetch(PDO::FETCH_ASSOC);
    $countsTotal = $result['total'];
    $formattedCountsTotal = number_format($countsTotal, 0, '', ' ');
    return $formattedCountsTotal;
}

// Get initial view counts
$initialCounts = getViewCounts();
$countsTotal = getTotalCounts();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Viewcounter!</title>
    <style>
        body {
            font-family: monospace;
            background-color: hsl(0, 0%, 11%);
            min-width: 1000px;
        }
        
        #viewCountsContainer {
            display: flex;
            flex-wrap: wrap;
            margin-top: 25px;
        }
        
        .thumbnail-container {
            margin: 2px 4px;
            text-align: center;
            position: relative;
        }
        
        .count {
            position: absolute;
            top: 170px;
            left: 20px;
            color: #fff;
            font-size: 15px;

            background-color: hsla(0, 0%, 0%, 0.70);
            padding: 1px 20px;
        }
        
        .folder {
            margin: 4px;
            background-color: #444444;
            float: left;
            padding: 0 4px;
        }
        
        #folder-wrapper {
            min-width: 1000px;
            margin-top: 30px;
            height: 63px;
        }
        
        #folder-wrapper a, p {
            color: #f2f2f2;
            font-size: 30pt;
            text-align: center;
            text-decoration: none;
            letter-spacing: 0px;
        }
        
        p {
            color: #dcdcdc;
            text-align: justify;
        }
        
        #wrapper {
            margin: auto;
            width: 700px;
        }
        
        #trackingasof {
            color:hsla(0, 0%, 100%, 0.4);
            font-size:13pt;
            margin: 6px 4px 4px 30px;
            float: left;
            padding: 4px 4px 4px 0px;
        }
    </style>
</head>
<body>
    <div id="folder-wrapper">
        <div class="folder"><a href=".."><p style="margin-left:5px;" class="folder">< Return</p></a></div>
        <div class="folder"><a href="./viewcounts.php"><p style="margin-left:5px;" class="folder">Refresh</p></a></div>
        <?php echo '<p id="trackingasof">Tracking ' . $countsTotal . ' requests as of:<br>&nbsp;&nbsp;December 6th, 2023</p>'; ?>
    </div>
    <div id="viewCountsContainer">
    <?php foreach ($initialCounts as $count): ?>
        <div class="thumbnail-container">
            <?php
                // Create the modified URL with '/.thumb/' correctly appended
                $modifiedUrl = dirname($count['image_url']) . '/.thumb/' . basename($count['image_url']);
            ?>
            <a href="<?= htmlspecialchars($count['image_url']) ?>">
                <?php if (pathinfo($count['image_url'], PATHINFO_EXTENSION) === 'mp4'): ?>
                    <video autoplay loop muted playsinline>
                        <source src="<?= htmlspecialchars($modifiedUrl) ?>" type="video/mp4"></video>
                <?php else: ?>
                    <img src="<?= htmlspecialchars($modifiedUrl) ?>" alt="Thumbnail">
                <?php endif; ?>
            </a>
            <div class="count"><?= $count['count'] ?></div>
        </div>
    <?php endforeach; ?>
    </div>
</body>
</html>