<?php
session_start();
if (isset($_SESSION['id_user'])) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'penyewa/dashboard.php'));
    exit;
}
require 'includes/koneksi.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = md5($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM user WHERE username = ? AND password = ?");
    $stmt->bind_param('ss', $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['id_user'] = $user['id_user'];
        $_SESSION['nama']    = $user['nama'];
        $_SESSION['role']    = $user['role'];
        header('Location: ' . ($user['role'] === 'admin' ? 'admin/dashboard.php' : 'penyewa/dashboard.php'));
        exit;
    } else {
        $error = 'Username atau password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — RentalKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #FF5722;
            --dark:    #0D0D0D;
            --card-bg: #1A1A1A;
            --border:  #2A2A2A;
            --text:    #E8E8E8;
            --muted:   #777;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: var(--dark);
            font-family: 'DM Sans', sans-serif;
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        body::before {
            content: '';
            position: fixed; inset: 0;
            background:
                radial-gradient(ellipse 60% 50% at 80% 20%, rgba(255,87,34,.18) 0%, transparent 60%),
                radial-gradient(ellipse 40% 40% at 10% 80%, rgba(255,87,34,.08) 0%, transparent 55%);
            pointer-events: none;
        }
        .brand {
            font-family: 'Syne', sans-serif;
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -1px;
            color: #fff;
        }
        .brand span { color: var(--primary); }
        .login-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 30px 80px rgba(0,0,0,.5);
            animation: fadeUp .5s ease both;
        }
        @keyframes fadeUp {
            from { opacity:0; transform:translateY(24px); }
            to   { opacity:1; transform:translateY(0); }
        }
        .form-label { color: var(--muted); font-size: .85rem; letter-spacing: .05em; text-transform: uppercase; }
        .form-control {
            background: #111;
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text);
            padding: .75rem 1rem;
            transition: border-color .2s;
        }
        .form-control:focus {
            background: #111;
            border-color: var(--primary);
            color: var(--text);
            box-shadow: 0 0 0 3px rgba(255,87,34,.15);
        }
        .btn-primary {
            background: var(--primary);
            border: none;
            border-radius: 10px;
            padding: .8rem;
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: .03em;
            transition: opacity .2s, transform .15s;
        }
        .btn-primary:hover { opacity:.88; transform:translateY(-1px); }
        .divider { border-color: var(--border); margin: 1.5rem 0; }
        .hint-box {
            background: #111;
            border: 1px dashed var(--border);
            border-radius: 10px;
            padding: .8rem 1rem;
            font-size: .8rem;
            color: var(--muted);
        }
        .hint-box strong { color: var(--text); }
    </style>
</head>
<body>
<div class="login-card">
    <div class="text-center mb-4">
        <div class="brand mb-1">Rental<span>Ku</span></div>
        <p style="color:var(--muted); font-size:.9rem;">Sistem Manajemen Rental Mobil</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2 rounded-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <div class="input-group">
                <span class="input-group-text" style="background:#111;border-color:var(--border);color:var(--muted);">
                    <i class="bi bi-person"></i>
                </span>
                <input type="text" name="username" class="form-control" placeholder="Masukkan username" required
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
        </div>
        <div class="mb-4">
            <label class="form-label">Password</label>
            <div class="input-group">
                <span class="input-group-text" style="background:#111;border-color:var(--border);color:var(--muted);">
                    <i class="bi bi-lock"></i>
                </span>
                <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
        </button>
    </form>

    <hr class="divider">
    <div class="hint-box">
        <div class="mb-1"><i class="bi bi-shield-lock me-1"></i> <strong>Admin:</strong> admin / admin123</div>
        <div><i class="bi bi-person-circle me-1"></i> <strong>Penyewa:</strong> budi / budi123</div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
