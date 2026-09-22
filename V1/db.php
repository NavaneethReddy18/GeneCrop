<?php
$host = "localhost";
$port = 3307;
$username = "root";
$password = "";
$database = "genecrop_ai";

$conn = new mysqli($host, $username, $password, $database, $port);
if ($conn->connect_error) {
    die("Database connection failed: " . htmlspecialchars($conn->connect_error));
}
$conn->set_charset("utf8mb4");

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function rows($conn, $sql, $types = '', ...$params) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) die("Query preparation failed: " . h($conn->error));
    if ($types !== '') $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) die("Query failed: " . h($stmt->error));
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) $data[] = $row;
    $stmt->close();
    return $data;
}

function one($conn, $sql, $types = '', ...$params) {
    $data = rows($conn, $sql, $types, ...$params);
    return $data[0] ?? null;
}

function scalar($conn, $sql, $types = '', ...$params) {
    $r = one($conn, $sql, $types, ...$params);
    if (!$r) return 0;
    return reset($r);
}

function getDistricts($conn) {
    return rows($conn, "SELECT d.id, d.name, d.state_id, s.name AS state_name FROM districts d JOIN states s ON s.id=d.state_id ORDER BY s.name,d.name");
}

function getStates($conn) {
    return rows($conn, "SELECT id,name,code FROM states ORDER BY name");
}

function getDefaultDistrict($conn) {
    return one($conn, "SELECT d.id,d.name,d.state_id,s.name AS state_name FROM districts d JOIN states s ON s.id=d.state_id ORDER BY d.is_default DESC,d.id LIMIT 1");
}

function getDistrict($conn, $id) {
    return one($conn, "SELECT d.id,d.name,d.state_id,s.name AS state_name,d.agro_zone,d.latitude,d.longitude FROM districts d JOIN states s ON s.id=d.state_id WHERE d.id=?", 'i', $id);
}

function page_header($title, $active) {
    $nav = [
        'index' => ['dashboard.php','⌂','Dashboard'],
        'recommendation' => ['recommendation.php','🌾','Crop Recommendation'],
        'soil' => ['soil.php','🧪','Soil Analysis'],
        'weather' => ['weather.php','☁','Weather'],
        'forecast' => ['forecast.php','📈','Yield Forecast'],
        'reports' => ['reports.php','📊','Reports']
    ];
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>GeneCrop AI - '.h($title).'</title><link rel="stylesheet" href="css/style.css">';
    echo '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">';
    echo '</head><body><div class="app"><aside class="sidebar"><div class="logo"><div class="logo-icon">🌱</div><div><h2>GeneCrop</h2><span>AI Agriculture</span></div></div><nav class="navigation"><p class="nav-title">MAIN</p>';
    foreach ($nav as $key=>$item) echo '<a href="'.h($item[0]).'" class="nav-item '.($active===$key?'active':'').'" ><span>'.$item[1].'</span>'.$item[2].'</a>';
    echo '<p class="nav-title">OTHERS</p><a href="reports.php" class="nav-item '.($active==='reports'?'active':'').'" ><span>📊</span>Reports</a><a href="javascript:void(0)" class="nav-item" onclick="alert(\'Settings are not required for the academic prototype.\')"><span>⚙</span>Settings</a></nav>';
    echo '<div class="sidebar-bottom"><div class="help-box"><div class="help-icon">?</div><div><strong>Need Help?</strong><p>GeneCrop AI analysis guide</p></div></div></div></aside><main class="main"><header class="topbar"><div><p class="page-label">GENECROP AI</p><h1>'.h($title).'</h1></div><div class="top-actions"><button class="notification" type="button">🔔<span></span></button><div class="profile"><div class="avatar">U</div><div class="profile-info"><strong>User</strong><small>Farmer / Analyst</small></div><span class="arrow">⌄</span></div></div></header>';
}
function page_footer() { echo '</main></div></body></html>'; }
?>
