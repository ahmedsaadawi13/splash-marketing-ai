<!-- FILE: /app/views/contacts/index.php -->
<?php $pageTitle = 'Contacts'; ?>

<div class="page-header">
    <h2>Contacts</h2>
    <div class="page-actions">
        <a href="/contacts/import" class="btn btn-secondary">Import CSV</a>
        <a href="/contacts/export" class="btn btn-secondary">Export</a>
        <a href="/contacts/create" class="btn btn-primary">Add Contact</a>
    </div>
</div>

<!-- Search -->
<div class="search-bar">
    <form method="GET" action="/contacts">
        <input type="text" name="search" placeholder="Search contacts by email, name, or phone..." value="<?php echo View::e($search ?? ''); ?>">
        <button type="submit" class="btn btn-primary">Search</button>
        <?php if ($search): ?>
            <a href="/contacts" class="btn btn-secondary">Clear</a>
        <?php endif; ?>
    </form>
</div>

<!-- Contacts Table -->
<div class="card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Email</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Country</th>
                <th>Status</th>
                <th>Last Engagement</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($contacts)): ?>
                <?php foreach ($contacts as $contact): ?>
                    <tr>
                        <td><?php echo View::e($contact['email']); ?></td>
                        <td><?php echo View::e($contact['full_name'] ?: ($contact['first_name'] . ' ' . $contact['last_name'])); ?></td>
                        <td><?php echo View::e($contact['phone'] ?? '-'); ?></td>
                        <td><?php echo View::e($contact['country'] ?? '-'); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $contact['status']; ?>">
                                <?php echo View::e(ucfirst($contact['status'])); ?>
                            </span>
                        </td>
                        <td><?php echo $contact['last_engagement_at'] ? date('M j, Y', strtotime($contact['last_engagement_at'])) : 'Never'; ?></td>
                        <td><?php echo date('M j, Y', strtotime($contact['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">No contacts found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<?php if ($pagination && $pagination->hasPages()): ?>
    <div class="pagination-container">
        <?php echo $pagination->render(); ?>
    </div>
<?php endif; ?>
