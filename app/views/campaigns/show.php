<!-- FILE: /app/views/campaigns/show.php -->
<?php $pageTitle = 'Campaign Details'; ?>

<div class="page-header">
    <h2><?php echo View::e($campaign['name']); ?></h2>
    <div class="page-actions">
        <?php if ($campaign['status'] === 'draft'): ?>
            <form method="POST" action="/campaigns/<?php echo $campaign['id']; ?>/send" style="display: inline;">
                <?php echo CSRF::field(); ?>
                <button type="submit" class="btn btn-primary" onclick="return confirm('Send this campaign now?');">
                    Send Campaign
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- Campaign Info -->
<div class="card">
    <h3>Campaign Information</h3>
    <div class="info-grid">
        <div class="info-item">
            <label>Status:</label>
            <span class="badge badge-<?php echo $campaign['status']; ?>">
                <?php echo View::e(ucfirst($campaign['status'])); ?>
            </span>
        </div>
        <div class="info-item">
            <label>Channel:</label>
            <span><?php echo View::e(ucfirst($campaign['channel'])); ?></span>
        </div>
        <div class="info-item">
            <label>Total Recipients:</label>
            <span><?php echo number_format($campaign['total_recipients']); ?></span>
        </div>
        <div class="info-item">
            <label>Created:</label>
            <span><?php echo date('M j, Y g:i A', strtotime($campaign['created_at'])); ?></span>
        </div>
        <?php if ($campaign['sent_at']): ?>
        <div class="info-item">
            <label>Sent:</label>
            <span><?php echo date('M j, Y g:i A', strtotime($campaign['sent_at'])); ?></span>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Performance Stats -->
<?php if ($campaign['status'] === 'sent'): ?>
<div class="card">
    <h3>Performance</h3>
    <div class="stats-grid">
        <div class="stat-card">
            <h4>Delivered</h4>
            <div class="stat-value"><?php echo number_format($stats['delivered'] ?? 0); ?></div>
            <div class="stat-rate"><?php echo number_format($stats['delivery_rate'] ?? 0, 1); ?>%</div>
        </div>

        <div class="stat-card">
            <h4>Opens</h4>
            <div class="stat-value"><?php echo number_format($stats['opened'] ?? 0); ?></div>
            <div class="stat-rate"><?php echo number_format($stats['open_rate'] ?? 0, 1); ?>%</div>
        </div>

        <div class="stat-card">
            <h4>Clicks</h4>
            <div class="stat-value"><?php echo number_format($stats['clicked'] ?? 0); ?></div>
            <div class="stat-rate"><?php echo number_format($stats['click_rate'] ?? 0, 1); ?>%</div>
        </div>

        <div class="stat-card">
            <h4>Bounced</h4>
            <div class="stat-value"><?php echo number_format($stats['bounced'] ?? 0); ?></div>
            <div class="stat-rate"><?php echo number_format($stats['bounce_rate'] ?? 0, 1); ?>%</div>
        </div>
    </div>
</div>

<!-- Recipients Sample -->
<div class="card">
    <h3>Recipients (Sample)</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Email</th>
                <th>Status</th>
                <th>Opens</th>
                <th>Clicks</th>
                <th>Last Event</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (array_slice($recipients, 0, 50) as $recipient): ?>
                <tr>
                    <td><?php echo View::e($recipient['email']); ?></td>
                    <td>
                        <span class="badge badge-<?php echo $recipient['status']; ?>">
                            <?php echo View::e(ucfirst($recipient['status'])); ?>
                        </span>
                    </td>
                    <td><?php echo $recipient['open_count']; ?></td>
                    <td><?php echo $recipient['click_count']; ?></td>
                    <td><?php echo $recipient['last_event_at'] ? date('M j, g:i A', strtotime($recipient['last_event_at'])) : '-'; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
