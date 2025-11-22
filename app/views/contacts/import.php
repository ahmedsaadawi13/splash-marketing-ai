<!-- FILE: /app/views/contacts/import.php -->
<?php $pageTitle = 'Import Contacts'; ?>

<div class="page-header">
    <h2>Import Contacts from CSV</h2>
</div>

<div class="card">
    <h3>Upload CSV File</h3>
    <p>Upload a CSV file containing your contacts. The file should include columns for email, first_name, last_name, phone, etc.</p>

    <form method="POST" action="/contacts/import" enctype="multipart/form-data">
        <?php echo CSRF::field(); ?>

        <div class="form-group">
            <label for="csv_file">CSV File *</label>
            <input type="file" id="csv_file" name="csv_file" accept=".csv,.txt" required>
            <small>Maximum file size: 10MB</small>
        </div>

        <div class="form-group">
            <label for="list_id">Add to List</label>
            <select id="list_id" name="list_id">
                <option value="">Don't add to any list</option>
                <?php foreach ($lists as $list): ?>
                    <option value="<?php echo $list['id']; ?>" <?php echo $list['is_default'] ? 'selected' : ''; ?>>
                        <?php echo View::e($list['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="alert alert-info">
            <strong>CSV Format:</strong> Your CSV file should have a header row with column names like: email, first_name, last_name, phone, country, tags
        </div>

        <div class="form-actions">
            <a href="/contacts" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Import Contacts</button>
        </div>
    </form>
</div>
