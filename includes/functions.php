

<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


function is_logged_in() {
    return !empty($_SESSION['user_id']);
}

function clean_input($data){
    return htmlspecialchars(trim($data));
}

function redirect($url){
    header("Location: $url");
    exit();
}

function logout_user() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

/* ================= FLASH ================= */
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function display_flash() {
    $flash = get_flash();

    if ($flash) {
        $colors = [
            'success' => 'bg-green-500',
            'warning' => 'bg-yellow-500',
            'info'    => 'bg-blue-500',
            'error'   => 'bg-red-500'
        ];

        $color = $colors[$flash['type']] ?? 'bg-red-500';

        echo "<div class='p-3 mb-4 rounded text-white $color'>";
        echo htmlspecialchars($flash['message']);
        echo "</div>";
    }
}

/* ================= AUTH ================= */
function require_login($allowed_roles = []) {
    if (!is_logged_in()) {
        set_flash("warning", "You need to login first");
        redirect("../Public/login.php");
    }

    if (!empty($allowed_roles)) {
        $allowed_roles = is_array($allowed_roles) ? $allowed_roles : [$allowed_roles];
        $current_role = $_SESSION['user_role'] ?? null;

        if (!$current_role || !in_array($current_role, $allowed_roles, true)) {
            set_flash("error", "Access denied");
            redirect("../Public/login.php");
        }
    }
}

function require_guest() {
    if (is_logged_in()) {
        redirect("../candidate/dashboard.php");
    }
}

function email_exists($pdo, $email) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->rowCount() > 0;
}

function create_user($pdo, $fullname, $email, $password, $role = 'candidate') {
    $stmt = $pdo->prepare("
        INSERT INTO users (full_name, email, password, role)
        VALUES (?, ?, ?, ?)
    ");

    return $stmt->execute([
        $fullname,
        $email,
        $password,
        $role
    ]);
}