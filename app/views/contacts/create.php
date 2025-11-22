<!-- FILE: /app/views/contacts/create.php -->
<?php $pageTitle = 'Add Contact'; ?>

<div class="page-header">
    <h2>Add New Contact</h2>
</div>

<div class="card">
    <form method="POST" action="/contacts">
        <?php echo CSRF::field(); ?>

        <div class="form-row">
            <div class="form-group">
                <label for="first_name">First Name *</label>
                <input type="text" id="first_name" name="first_name" required>
            </div>

            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="country">Country</label>
                <input type="text" id="country" name="country" maxlength="2" placeholder="US">
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="subscribed" selected>Subscribed</option>
                    <option value="unsubscribed">Unsubscribed</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="tags">Tags (comma-separated)</label>
            <input type="text" id="tags" name="tags" placeholder="customer, vip, newsletter">
        </div>

        <div class="form-group">
            <label>Add to Lists</label>
            <div class="checkbox-group">
                <?php foreach ($lists as $list): ?>
                    <label class="checkbox-label">
                        <input type="checkbox" name="list_ids[]" value="<?php echo $list['id']; ?>" <?php echo $list['is_default'] ? 'checked' : ''; ?>>
                        <?php echo View::e($list['name']); ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="form-actions">
            <a href="/contacts" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Create Contact</button>
        </div>
    </form>
</div>
