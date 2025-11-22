<!-- FILE: /app/views/auth/login.php -->
<div class="auth-card">
    <h2>Login to Your Account</h2>

    <form method="POST" action="/login">
        <?php echo CSRF::field(); ?>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required autofocus>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Login</button>
    </form>

    <div class="auth-links">
        <p>Don't have an account? <a href="/register">Register here</a></p>
    </div>

    <div class="demo-credentials">
        <h4>Demo Credentials:</h4>
        <p><strong>Email:</strong> demo@example.com<br>
        <strong>Password:</strong> password</p>
    </div>
</div>
