<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { background: #f7f8f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif; margin: 0; }
        .dashboard-container { max-width: 700px; margin: 60px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 2.5rem; }
        h1 { color: #dd4814; text-align: center; }
        .welcome { text-align: center; margin-bottom: 2rem; }
        .logout-btn { display: block; margin: 2rem auto 0 auto; background: #dd4814; color: #fff; border: none; border-radius: 4px; padding: 0.75rem 2rem; font-size: 1.1rem; cursor: pointer; transition: background 0.2s; }
        .logout-btn:hover { background: #b93c10; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1>Customer Dashboard</h1>
        <div class="welcome">
            <p>Welcome, Customer!</p>
        </div>
        <form method="get" action="/logout">
            <button class="logout-btn" type="submit">Logout</button>
        </form>
    </div>
</body>
</html>
