<?php
require_once 'include/database.php';

$complexId = isset($_GET['complex_id']) ? (int)$_GET['complex_id'] : null;

if ($complexId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE residential_complex_id = ?");
    $stmt->execute([$complexId]);
} else {
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
}
$usersCount = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM residential_complexes");
$complexesCount = $stmt->fetchColumn();

if ($complexId) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM debts 
        INNER JOIN users ON debts.user_id = users.id 
        WHERE users.residential_complex_id = ? 
        AND debts.due_date IS NOT NULL 
        AND debts.due_date < NOW()
    ");
    $stmt->execute([$complexId]);
} else {
    $stmt = $pdo->query("
        SELECT COUNT(*) FROM debts 
        WHERE due_date IS NOT NULL 
        AND due_date < NOW()
    ");
}
$debtsCount = $stmt->fetchColumn();

$currentMonth = date('Y-m');
if ($complexId) {
    $stmtNew = $pdo->prepare("
        SELECT COUNT(*) FROM complaints 
        INNER JOIN users ON complaints.user_id = users.id
        WHERE users.residential_complex_id = ?
        AND DATE_TRUNC('month', complaints.created_at) = DATE_TRUNC('month', CURRENT_DATE)
        AND status = 'pending'
    ");
    $stmtNew->execute([$complexId]);

    $stmtDone = $pdo->prepare("
        SELECT COUNT(*) FROM complaints 
        INNER JOIN users ON complaints.user_id = users.id
        WHERE users.residential_complex_id = ?
        AND DATE_TRUNC('month', complaints.created_at) = DATE_TRUNC('month', CURRENT_DATE)
        AND status = 'done'
    ");
    $stmtDone->execute([$complexId]);
} else {
    $stmtNew = $pdo->query("
        SELECT COUNT(*) FROM complaints 
        WHERE DATE_TRUNC('month', created_at) = DATE_TRUNC('month', CURRENT_DATE)
        AND status = 'pending'
    ");
    $stmtDone = $pdo->query("
        SELECT COUNT(*) FROM complaints 
        WHERE DATE_TRUNC('month', created_at) = DATE_TRUNC('month', CURRENT_DATE)
        AND status = 'done'
    ");
}
$complaintsNew = $stmtNew->fetchColumn();
$complaintsDone = $stmtDone->fetchColumn();

if ($complexId) {
    $stmtNew = $pdo->prepare("
        SELECT COUNT(*) FROM suggestions 
        INNER JOIN users ON suggestions.user_id = users.id
        WHERE users.residential_complex_id = ?
        AND DATE_TRUNC('month', suggestions.created_at) = DATE_TRUNC('month', CURRENT_DATE)
        AND status = 'pending'
    ");
    $stmtNew->execute([$complexId]);

    $stmtDone = $pdo->prepare("
        SELECT COUNT(*) FROM suggestions 
        INNER JOIN users ON suggestions.user_id = users.id
        WHERE users.residential_complex_id = ?
        AND DATE_TRUNC('month', suggestions.created_at) = DATE_TRUNC('month', CURRENT_DATE)
        AND status = 'done'
    ");
    $stmtDone->execute([$complexId]);
} else {
    $stmtNew = $pdo->query("
        SELECT COUNT(*) FROM suggestions 
        WHERE DATE_TRUNC('month', created_at) = DATE_TRUNC('month', CURRENT_DATE)
        AND status = 'pending'
    ");
    $stmtDone = $pdo->query("
        SELECT COUNT(*) FROM suggestions 
        WHERE DATE_TRUNC('month', created_at) = DATE_TRUNC('month', CURRENT_DATE)
        AND status = 'done'
    ");
}
$suggestionsNew = $stmtNew->fetchColumn();
$suggestionsDone = $stmtDone->fetchColumn();

if ($complexId) {
    $stmtNew = $pdo->prepare("
        SELECT COUNT(*) FROM service_requests 
        INNER JOIN users ON service_requests.user_id = users.id
        WHERE users.residential_complex_id = ?
        AND DATE_TRUNC('month', service_requests.created_at) = DATE_TRUNC('month', CURRENT_DATE)
        AND status = 'pending'
    ");
    $stmtNew->execute([$complexId]);

    $stmtDone = $pdo->prepare("
        SELECT COUNT(*) FROM service_requests 
        INNER JOIN users ON service_requests.user_id = users.id
        WHERE users.residential_complex_id = ?
        AND DATE_TRUNC('month', service_requests.created_at) = DATE_TRUNC('month', CURRENT_DATE)
        AND status = 'done'
    ");
    $stmtDone->execute([$complexId]);
} else {
    $stmtNew = $pdo->query("
        SELECT COUNT(*) FROM service_requests 
        WHERE DATE_TRUNC('month', created_at) = DATE_TRUNC('month', CURRENT_DATE)
        AND status = 'pending'
    ");
    $stmtDone = $pdo->query("
        SELECT COUNT(*) FROM service_requests 
        WHERE DATE_TRUNC('month', created_at) = DATE_TRUNC('month', CURRENT_DATE)
        AND status = 'done'
    ");
}
$servicesNew = $stmtNew->fetchColumn();
$servicesDone = $stmtDone->fetchColumn();

if ($complexId) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM announcements 
        WHERE residential_complex_id = ? 
        AND DATE_TRUNC('month', created_at) = DATE_TRUNC('month', CURRENT_DATE)
    ");
    $stmt->execute([$complexId]);
} else {
    $stmt = $pdo->query("
        SELECT COUNT(*) FROM announcements 
        WHERE DATE_TRUNC('month', created_at) = DATE_TRUNC('month', CURRENT_DATE)
    ");
}
$announcementsCount = $stmt->fetchColumn();

echo json_encode([
    'users' => (int)$usersCount,
    'complexes' => (int)$complexesCount,
    'debts' => (int)$debtsCount,
    'complaints_new' => (int)$complaintsNew,
    'complaints_done' => (int)$complaintsDone,
    'suggestions_new' => (int)$suggestionsNew,
    'suggestions_done' => (int)$suggestionsDone,
    'services_new' => (int)$servicesNew,
    'services_done' => (int)$servicesDone,
    'announcements' => (int)$announcementsCount
]);
?>
