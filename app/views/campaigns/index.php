<!-- FILE: /app/views/campaigns/index.php -->
<?php $pageTitle = 'Campaigns'; ?>

<div class="page-header">
    <h2>Campaigns</h2>
    <div class="page-actions">
        <a href="/campaigns/create" class="btn btn-primary">Create Campaign</a>
    </div>
</div>

<!-- Campaigns Table -->
<div class="card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Channel</th>
                <th>Status</th>
                <th>Recipients</th>
                <th>Sent At</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($campaigns)): ?>
                <?php foreach ($campaigns as $campaign): ?>
                    <tr>
                        <td>
                            <a href="/campaigns/<?php echo $campaign['id']; ?>">
                                <?php echo View::e($campaign['name']); ?>
                            </a>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $campaign['channel']; ?>">
                                <?php echo View::e(ucfirst($campaign['channel'])); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $campaign['status']; ?>">
                                <?php echo View::e(ucfirst($campaign['status'])); ?>
                            </span>
                        </td>
                        <td><?php echo number_format($campaign['total_recipients']); ?></td>
                        <td><?php echo $campaign['sent_at'] ? date('M j, Y g:i A', strtotime($campaign['sent_at'])) : '-'; ?></td>
                        <td><?php echo date('M j, Y', strtotime($campaign['created_at'])); ?></td>
                        <td>
                            <a href="/campaigns/<?php echo $campaign['id']; ?>" class="btn btn-sm">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">No campaigns found. <a href="/campaigns/create">Create your first campaign</a></td>
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
