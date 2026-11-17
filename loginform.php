<?php
session_start(); // Added this, as alertMessage() usually requires a session
$error = "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | AJ&A Divi-Things</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
          --color-brand-gold: #b5975b;
          --color-dark-text: #222222;
          --color-light-text: #555555;
          --color-background: #f9f9f9;
          --color-white: #ffffff;
          --color-border: #e0e0e0;
        }

        body {
          background-color: var(--color-background);
          font-family: "Segoe UI", Tahoma, sans-serif;
        }

        /* This container centers the login card */
        .login-container {
          display: flex;
          justify-content: center;
          align-items: center;
          padding: 50px 20px;
          min-height: calc(100vh - 80px); /* Full height minus approx navbar height */
        }

        /* The login card itself, styled like the checkout */
        .login-card {
          background-color: var(--color-white);
          padding: 35px 40px;
          border-radius: 8px;
          box-shadow: 0 4px 15px rgba(0, 0, 0, 0.07);
          width: 100%;
          max-width: 420px;
        }

        .login-card h2 {
          font-size: 24px;
          font-weight: 600;
          color: var(--color-dark-text);
          margin-bottom: 25px;
        }
        
        /* Form styling from checkout.php */
        .form-group {
          margin-bottom: 18px;
        }
        .form-group label {
          display: block;
          font-weight: 600;
          font-size: 14px;
          color: var(--color-dark-text);
          margin-bottom: 6px;
        }

        /* Light grey background text boxes */
        .form-control {
          padding: 12px;
          font-size: 15px;
          border-radius: 6px;
          background-color: #f5f5f5; 
          border: 1px solid #f5f5f5;
          width: 100%;
        }
        
        .form-control:focus {
          background-color: #fff;
          border-color: var(--color-brand-gold);
          box-shadow: 0 0 0 3px rgba(181, 151, 91, 0.2);
          outline: 0;
        }

        /* Branded login button */
        .btn-brand-login {
          background-color: var(--color-brand-gold);
          color: var(--color-white);
          font-size: 16px;
          font-weight: bold;
          text-transform: uppercase;
          letter-spacing: 0.5px;
          padding: 12px 20px;
          border: none;
          border-radius: 6px;
          width: 100%;
          cursor: pointer;
          transition: all 0.3s ease;
          margin-top: 10px;
        }
        .btn-brand-login:hover {
          background-color: #a0844f;
          color: var(--color-white);
          box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <?php include('./include2/navbarrr.php'); ?>
    
    <div class="login-container">
        <div class="login-card">
            
            <?php alertMessage(); ?> 

            <h2 class="text-center mb-4">Admin Login</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="login-code.php" autocomplete="off">
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input 
                        type="email" 
                        class="form-control" 
                        id="email" 
                        name="email" 
                        placeholder="Enter admin email" 
                        required
                    >
                </div>
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input 
                        type="password" 
                        class="form-control" 
                        id="password" 
                        name="password" 
                        placeholder="Enter password" 
                        required
                    >
                </div>
                <button type="submit" name="loginBtn" class="btn-brand-login">Login</button>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>