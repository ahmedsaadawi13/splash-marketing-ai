<!-- FILE: /app/views/auth/register.php -->
<div class="auth-card">
    <h2>Create Your Account</h2>

    <form method="POST" action="/register">
        <?php echo CSRF::field(); ?>

        <div class="form-group">
            <label for="company_name">Company Name</label>
            <input type="text" id="company_name" name="company_name" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" required>
            </div>

            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" required>
            </div>
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="8">
            </div>

            <div class="form-group">
                <label for="password_confirm">Confirm Password</label>
                <input type="password" id="password_confirm" name="password_confirm" required>
            </div>
        </div>

        <div class="form-group">
            <label for="plan_id">Select Plan</label>
            <select id="plan_id" name="plan_id" required>
                <option value="">Choose a plan...</option>
                <?php foreach ($plans as $plan): ?>
                    <option value="<?php echo $plan['id']; ?>">
                        <?php echo View::e($plan['name']); ?> - $<?php echo number_format($plan['price'], 2); ?>/<?php echo $plan['billing_cycle']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Create Account</button>
    </form>

    <div class="auth-links">
        <p>Already have an account? <a href="/login">Login here</a></p>
    </div>
</div>
