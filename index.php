<?php
require_once 'config/database.php';

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['admin_id'])) {
    redirect('pages/dashboard.php');
}

$error = '';

// Proses login
if (isset($_POST['login'])) {
    $username = clean($_POST['username']);
    $password = clean($_POST['password']);
    
    $query = "SELECT * FROM admin WHERE username='$username' AND password=MD5('$password')";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $admin = mysqli_fetch_assoc($result);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['username'] = $admin['username'];
        redirect('pages/dashboard.php');
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem GAP Kompetensi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .login-header p {
            color: #666;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 12px;
            background: #fee;
            color: #c33;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Sistem GAP Kompetensi</h1>
            <p>Analisis Promosi Jabatan</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required autofocus>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit" name="login" class="btn-login">Login</button>
        </form>
    </div>
</body>
</html>