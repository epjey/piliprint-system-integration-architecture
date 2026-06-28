<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>PiliPrint — Login</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: Arial, sans-serif;
      background: #0F172A;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .topbar {
      background: #1E293B;
      display: flex;
      align-items: center;
      padding: 0 24px;
      height: 68px;
      border-bottom: 2px solid #EA580C;
      gap: 14px;
    }

    .topbar img {
      width: 52px;
      height: 52px;
      object-fit: contain;
    }

    .topbar-brand strong {
      color: #E2E8F0;
      font-size: 1.25rem;
    }

    .topbar-brand small {
      display: block;
      color: #94A3B8;
      font-size: 0.72rem;
    }

    .main {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 20px;
    }

    .card {
      background: #1E293B;
      border-radius: 10px;
      padding: 36px 32px;
      width: 100%;
      max-width: 380px;
      text-align: center;
    }

    .card img {
      width: 200px;
      height: auto;
      display: block;
      margin: 0 auto -6px;
    }

    .card h1 {
      color: #E2E8F0;
      font-size: 1.7rem;
      margin-bottom: 22px;
      line-height: 1;
    }

    label {
      display: block;
      color: #94A3B8;
      font-size: 0.78rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin-bottom: 6px;
      text-align: left;
    }

    input {
      width: 100%;
      padding: 10px 13px;
      background: #0F172A;
      border: 1px solid #334155;
      border-radius: 6px;
      color: #E2E8F0;
      font-size: 0.88rem;
      outline: none;
      margin-bottom: 16px;
      transition: border-color 0.2s;
    }

    input:focus { border-color: #06B6D4; }
    input::placeholder { color: #475569; }

    .btn-signin {
      width: 100%;
      padding: 11px;
      background: #06B6D4;
      color: #0F172A;
      border: none;
      border-radius: 6px;
      font-size: 0.9rem;
      font-weight: 700;
      cursor: pointer;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin-top: 4px;
      transition: background 0.2s;
    }

    .btn-signin:hover { background: #0891B2; }
    .btn-signin:disabled { opacity: 0.6; cursor: not-allowed; }

    .error {
      display: none;
      background: rgba(239,68,68,0.1);
      border: 1px solid rgba(239,68,68,0.3);
      color: #FCA5A5;
      font-size: 0.8rem;
      padding: 9px 12px;
      border-radius: 6px;
      margin-bottom: 14px;
      text-align: center;
    }
    .error.show { display: block; }

    .footer {
      background: #1E293B;
      border-top: 1px solid #334155;
      text-align: center;
      padding: 12px;
      color: #475569;
      font-size: 0.72rem;
    }

    .footer span { color: #EA580C; }
  </style>
</head>
<body>

  <div class="topbar">
    <img src="../../assets/logo/loginlogo.png" alt="PiliPrint loginLogo"/>
    <div class="topbar-brand">
      <strong>PiliPrint</strong>
      <small>Printing Services</small>
    </div>
  </div>

  <div class="main">
    <div class="card">
      <img src="../../assets/logo/loginlogo.png" alt="PiliPrint loginLogo"/>
      <h1>PiliPrint</h1>

      <div class="error" id="errorMsg"></div>

      <form id="loginForm">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="Enter your email" required/>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Enter your password" required/>

        <button type="submit" class="btn-signin" id="loginBtn">Sign In</button>
      </form>
    </div>
  </div>

  <div class="footer">
    &copy; 2026 <span>PiliPrint</span> — Printing Services. All rights reserved.
  </div>

<script>
  document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('loginBtn');
    const err = document.getElementById('errorMsg');

    btn.textContent = 'Signing in...';
    btn.disabled = true;
    err.classList.remove('show');

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
        err.classList.add('show');
        btn.textContent = 'Sign In';
        btn.disabled = false;
      }
    } catch(e) {
      err.textContent = 'Network error occurred.';
      err.classList.add('show');
      btn.textContent = 'Sign In';
      btn.disabled = false;
    }
  });
</script>
</body>
</html>