<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PiliPrint POS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            background: #F1F5F9;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .login-box {
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            width: 100%;
            max-width: 400px;
            box-sizing: border-box;
        }
        .brand {
            text-align: center;
            margin-bottom: 30px;
        }
        .brand h1 {
            margin: 0;
            color: #0F172A;
            font-size: 1.8rem;
        }
        .brand p {
            margin: 5px 0 0;
            color: #64748B;
            font-size: 0.9rem;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            color: #334155;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #CBD5E1;
            border-radius: 6px;
            font-size: 1rem;
            box-sizing: border-box;
            outline: none;
            transition: border-color 0.2s;
        }
        .form-group input:focus {
            border-color: #4A7FB5;
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background: #4A7FB5;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-login:hover {
            background: #3B6794;
        }
        #errorMsg {
            color: #EF4444;
            font-size: 0.9rem;
            margin-bottom: 15px;
            text-align: center;
            display: none;
        }
    </style>
</head>
<body>

<div class="login-box">
    <div class="brand">
        <h1>PiliPrint</h1>
        <p>Sign in to your account</p>
    </div>
    
    <div id="errorMsg"></div>

    <form id="loginForm">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn-login" id="loginBtn">Sign In</button>
    </form>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('loginBtn');
    const err = document.getElementById('errorMsg');
    
    btn.textContent = 'Signing in...';
    btn.disabled = true;
    err.style.display = 'none';

    const fd = new URLSearchParams();
    fd.append('action', 'login');
    fd.append('email', document.getElementById('email').value);
    fd.append('password', document.getElementById('password').value);

    try {
        const res = await fetch('../../api/auth.php', {
            method: 'POST',
            body: fd
        });
        const data = await res.json();
        
        if (data.success) {
            window.location.href = '../../' + data.redirect;
        } else {
            err.textContent = data.message;
            err.style.display = 'block';
            btn.textContent = 'Sign In';
            btn.disabled = false;
        }
    } catch(e) {
        err.textContent = 'Network error occurred.';
        err.style.display = 'block';
        btn.textContent = 'Sign In';
        btn.disabled = false;
    }
});
</script>

</body>
</html>
