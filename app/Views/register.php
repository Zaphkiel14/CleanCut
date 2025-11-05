<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { background: #f7f8f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif; margin: 0; padding: 0; }
        .register-container { max-width: 400px; margin: 60px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 2rem 2.5rem 2.5rem 2.5rem; }
        .register-container h2 { text-align: center; margin-bottom: 2rem; color: #dd4814; }
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; margin-bottom: 0.5rem; color: #333; }
        input[type="text"], input[type="password"] { width: 100%; padding: 0.75rem; border: 1px solid #ccc; border-radius: 4px; font-size: 1rem; }
        select { width: 100%; padding: 0.75rem; border: 1px solid #ccc; border-radius: 4px; font-size: 1rem; }
        button[type="submit"] { width: 100%; background: #dd4814; color: #fff; border: none; border-radius: 4px; padding: 0.75rem; font-size: 1.1rem; cursor: pointer; transition: background 0.2s; }
        button[type="submit"]:hover { background: #b93c10; }
        .register-footer { text-align: center; margin-top: 1.5rem; color: #888; font-size: 0.95rem; }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Register</h2>
        <form method="post" action="/register">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="customer">Customer</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit">Register</button>
        </form>
        <div class="register-footer">
            Already have an account? <a href="/login">Login</a>
        </div>
    </div>
</body>
</html>
