<!-- FILE: /app/views/campaigns/create.php -->
<?php $pageTitle = 'Create Campaign'; ?>

<div class="page-header">
    <h2>Create New Campaign</h2>
</div>

<div class="card">
    <form method="POST" action="/campaigns">
        <?php echo CSRF::field(); ?>

        <div class="form-group">
            <label for="name">Campaign Name *</label>
            <input type="text" id="name" name="name" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="channel">Channel *</label>
                <select id="channel" name="channel" required>
                    <option value="">Select channel...</option>
                    <option value="email">Email</option>
                    <option value="sms">SMS</option>
                    <option value="social">Social Media</option>
                </select>
            </div>

            <div class="form-group">
                <label for="template_id">Template</label>
                <select id="template_id" name="template_id">
                    <option value="">Select template...</option>
                    <?php foreach ($templates as $template): ?>
                        <option value="<?php echo $template['id']; ?>">
                            <?php echo View::e($template['name']); ?> (<?php echo ucfirst($template['channel']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="list_id">Send to List</label>
                <select id="list_id" name="list_id">
                    <option value="">Select list...</option>
                    <?php foreach ($lists as $list): ?>
                        <option value="<?php echo $list['id']; ?>">
                            <?php echo View::e($list['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="segment_id">Or Send to Segment</label>
                <select id="segment_id" name="segment_id">
                    <option value="">Select segment...</option>
                    <?php foreach ($segments as $segment): ?>
                        <option value="<?php echo $segment['id']; ?>">
                            <?php echo View::e($segment['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="from_name">From Name</label>
                <input type="text" id="from_name" name="from_name" value="<?php echo View::e($GLOBALS['auth']->user()['tenant_name'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="from_email">From Email</label>
                <input type="email" id="from_email" name="from_email">
            </div>
        </div>

        <div class="form-group">
            <label for="subject_line">Subject Line *</label>
            <input type="text" id="subject_line" name="subject_line" required>
            <small>Use {{first_name}}, {{last_name}}, etc. for personalization</small>
        </div>

        <div class="form-actions">
            <a href="/campaigns" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Create Campaign</button>
        </div>
    </form>
</div>
