<?php
session_start();
require_once 'databases/config.php';

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

// ========== PROSES LOGIN ==========
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi.';
    } else {
        $result = $conn->query("SELECT id_user, username, password FROM users WHERE username = '$username' AND password = '$password'");
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['login_time'] = time();
            
            header('Location: index.php');
            exit;
        } else {
            $error = 'Username atau password salah.';
        }
    }
}

// ========== RESET PASSWORD ==========
if (isset($_POST['reset_password'])) {
    $reset_username = trim($_POST['reset_username']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    if (empty($reset_username) || empty($new_password) || empty($confirm_password)) {
        $error_reset = 'Semua field harus diisi.';
    } elseif ($new_password !== $confirm_password) {
        $error_reset = 'Password baru dan konfirmasi tidak cocok.';
    } else {
        $conn->query("UPDATE users SET password = '$new_password' WHERE username = '$reset_username'");
        
        if ($conn->affected_rows > 0) {
            $success_reset = "✅ Password berhasil diubah! Silakan login dengan password baru.";
            // Reset form
            $_POST = [];
        } else {
            $error_reset = 'Username tidak ditemukan.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — White Clean</title>
    <link rel="stylesheet" href="style/login.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <!-- HEADER -->
            <div class="auth-header">
                <div class="logo">White Clean</div>
                <h1>Laundry</h1>
                <p class="subtitle">Handal &amp; Terpercaya</p>
                <div class="divider"></div>
            </div>
            
            <!-- BODY -->
            <div class="auth-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <?php if (!empty($success_reset)): ?>
                    <div class="alert alert-success"><?= $success_reset ?></div>
                <?php endif; ?>
                
                <!-- ===== FORM LOGIN ===== -->
                <div id="formLogin">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="username">Username <span class="required">*</span></label>
                            <input type="text" id="username" name="username" 
                                   placeholder="Masukkan username" 
                                   value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
                                   required autofocus>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password <span class="required">*</span></label>
                            <input type="password" id="password" name="password" 
                                   placeholder="Masukkan password" required>
                        </div>
                        
                        <div class="form-options">
                            <a onclick="showForgot()">Lupa password?</a>
                        </div>
                        
                        <button type="submit" name="login" class="btn-primary">Masuk</button>
                    </form>
                    
                </div>
                
                <!-- ===== FORM LUPA PASSWORD ===== -->
                <div id="formForgot" style="display: none;">
                    <form method="POST" action="">
                        <?php if (!empty($error_reset)): ?>
                            <div class="alert alert-danger"><?= $error_reset ?></div>
                        <?php endif; ?>
                        
                        <p class="info-text">
                            Masukkan username dan password baru untuk mengganti password.
                        </p>
                        
                        <div class="form-group">
                            <label for="reset_username">Username <span class="required">*</span></label>
                            <input type="text" id="reset_username" name="reset_username" 
                                   placeholder="Masukkan username" 
                                   value="<?= isset($_POST['reset_username']) ? htmlspecialchars($_POST['reset_username']) : '' ?>"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">Password Baru <span class="required">*</span></label>
                            <input type="text" id="new_password" name="new_password" 
                                   placeholder="Masukkan password baru" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Konfirmasi Password <span class="required">*</span></label>
                            <input type="text" id="confirm_password" name="confirm_password" 
                                   placeholder="Ulangi password baru" required>
                        </div>
                        
                        <div class="btn-group">
                            <button type="button" onclick="hideForgot()" class="btn-danger">Batal</button>
                            <button type="submit" name="reset_password" class="btn-warning">Ganti Password</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- FOOTER -->
            <div class="auth-footer">
                &copy; <?= date('Y') ?> <span class="brand">White Clean</span> — Semua Pasti Kinclong
            </div>
        </div>
    </div>
    
    <script>
        function showForgot() {
            document.getElementById('formLogin').style.display = 'none';
            document.getElementById('formForgot').style.display = 'block';
        }
        
        function hideForgot() {
            document.getElementById('formLogin').style.display = 'block';
            document.getElementById('formForgot').style.display = 'none';
            // Reset error messages
            document.querySelectorAll('.alert').forEach(el => el.style.display = 'none');
        }
        
        // Validasi konfirmasi password
        document.addEventListener('DOMContentLoaded', function() {
            const newPass = document.getElementById('new_password');
            const confirmPass = document.getElementById('confirm_password');
            
            if (newPass && confirmPass) {
                confirmPass.addEventListener('input', function() {
                    if (this.value.length > 0 && this.value !== newPass.value) {
                        this.style.borderColor = '#dc2626';
                        this.style.boxShadow = '0 0 0 3px rgba(220, 38, 38, 0.1)';
                    } else if (this.value.length > 0) {
                        this.style.borderColor = '#065f46';
                        this.style.boxShadow = '0 0 0 3px rgba(6, 95, 70, 0.1)';
                    } else {
                        this.style.borderColor = '#dce8f5';
                        this.style.boxShadow = 'none';
                    }
                });
            }
            
            <?php if (!empty($error_reset)): ?>
                showForgot();
            <?php endif; ?>
        });
    </script>
</body>
</html>