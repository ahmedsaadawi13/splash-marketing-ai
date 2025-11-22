<!-- FILE: /app/views/dashboard/index.php -->
<?php $pageTitle = 'Dashboard'; ?>

<div class="dashboard">
    <!-- KPI Cards -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <h3>Total Contacts</h3>
            <div class="kpi-value"><?php echo number_format($dashboard['contacts']['total_contacts'] ?? 0); ?></div>
            <div class="kpi-change">+<?php echo number_format($dashboard['contacts']['new_this_month'] ?? 0); ?> this month</div>
        </div>

        <div class="kpi-card">
            <h3>Campaigns Sent</h3>
            <div class="kpi-value"><?php echo number_format($dashboard['campaigns']['sent'] ?? 0); ?></div>
            <div class="kpi-change"><?php echo number_format($dashboard['campaigns']['created_this_month'] ?? 0); ?> this month</div>
        </div>

        <div class="kpi-card">
            <h3>Open Rate</h3>
            <div class="kpi-value"><?php echo number_format($dashboard['engagement']['open_rate'] ?? 0, 1); ?>%</div>
            <div class="kpi-meta">Last 30 days</div>
        </div>

        <div class="kpi-card">
            <h3>Click Rate</h3>
            <div class="kpi-value"><?php echo number_format($dashboard['engagement']['click_rate'] ?? 0, 1); ?>%</div>
            <div class="kpi-meta">Last 30 days</div>
        </div>
    </div>

    <!-- Charts & Recent Activity -->
    <div class="dashboard-row">
        <div class="dashboard-col-8">
            <div class="card">
                <h3>Sends Over Time (Last 30 Days)</h3>
                <div class="chart-container">
                    <canvas id="sendsChart"></canvas>
                </div>
            </div>
        </div>

        <div class="dashboard-col-4">
            <div class="card">
                <h3>Recent Activity</h3>
                <div class="activity-list">
                    <?php if (!empty($recentActivity)): ?>
                        <?php foreach (array_slice($recentActivity, 0, 10) as $activity): ?>
                            <div class="activity-item">
                                <strong><?php echo View::e($activity['action']); ?></strong>
                                <p><?php echo View::e($activity['description']); ?></p>
                                <small><?php echo date('M j, Y g:i A', strtotime($activity['created_at'])); ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No recent activity</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Usage Stats -->
    <div class="card">
        <h3>Usage This Month</h3>
        <div class="usage-bars">
            <div class="usage-item">
                <label>Sends</label>
                <div class="progress-bar">
                    <?php
                    $sendUsagePercent = ($dashboard['usage']['sends_this_month'] ?? 0) / max(1, $dashboard['usage']['max_sends'] ?? 10000) * 100;
                    ?>
                    <div class="progress-fill" style="width: <?php echo min(100, $sendUsagePercent); ?>%"></div>
                </div>
                <span><?php echo number_format($dashboard['usage']['sends_this_month'] ?? 0); ?> / <?php echo number_format($dashboard['usage']['max_sends'] ?? 10000); ?></span>
            </div>

            <div class="usage-item">
                <label>Contacts</label>
                <div class="progress-bar">
                    <?php
                    $contactUsagePercent = ($dashboard['usage']['contacts_count'] ?? 0) / max(1, $dashboard['usage']['max_contacts'] ?? 1000) * 100;
                    ?>
                    <div class="progress-fill" style="width: <?php echo min(100, $contactUsagePercent); %>%"></div>
                </div>
                <span><?php echo number_format($dashboard['usage']['contacts_count'] ?? 0); ?> / <?php echo number_format($dashboard['usage']['max_contacts'] ?? 1000); ?></span>
            </div>

            <div class="usage-item">
                <label>AI Tokens</label>
                <div class="progress-bar">
                    <?php
                    $tokenUsagePercent = ($dashboard['usage']['ai_tokens_used_this_month'] ?? 0) / max(1, $dashboard['usage']['max_tokens'] ?? 50000) * 100;
                    ?>
                    <div class="progress-fill" style="width: <?php echo min(100, $tokenUsagePercent); ?>%"></div>
                </div>
                <span><?php echo number_format($dashboard['usage']['ai_tokens_used_this_month'] ?? 0); ?> / <?php echo number_format($dashboard['usage']['max_tokens'] ?? 50000); ?></span>
            </div>
        </div>
    </div>
</div>

<script>
// Simple chart rendering (in production, use Chart.js or similar)
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard loaded');
    // Add chart rendering here
});
</script>
