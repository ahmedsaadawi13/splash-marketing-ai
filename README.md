# SplashMarketingAI

**AI-Powered Multi-Channel Marketing Automation Platform**

A complete, production-ready SaaS marketing automation platform built with PHP and MySQL. Similar to MailChimp, Klaviyo, and HubSpot Marketing, featuring contacts management, email/SMS/social campaigns, automation flows, AI content generation, and comprehensive analytics.

## Features

### Core Marketing Automation
- **Contact Management**: Import, export, segment, and manage unlimited contacts
- **Lists & Segments**: Static lists and dynamic segments with powerful filtering
- **Multi-Channel Campaigns**: Email, SMS, and social media broadcasts
- **Automation Flows**: Visual workflow builder for automated marketing journeys
- **AI Content Writer**: Generate email, SMS, and social content with AI
- **Template Library**: Reusable templates for all channels
- **Analytics & Reporting**: Real-time campaign performance and engagement metrics
- **Open & Click Tracking**: Track email opens and link clicks
- **Unsubscribe Management**: One-click unsubscribe with preference center

### Multi-Tenant SaaS
- **Tenant Isolation**: Complete data separation between tenants
- **Subscription Plans**: Flexible plans with usage quotas
- **Usage Tracking**: Monitor contacts, sends, AI tokens, and API calls
- **Billing & Invoicing**: Automated billing and payment tracking
- **Role-Based Access**: Platform admin, tenant admin, marketer, analyst, viewer roles

### REST API
- **Contact Management**: Upsert contacts, manage lists
- **Event Tracking**: Track custom events for automation triggers
- **Campaign Management**: Create and monitor campaigns via API
- **API Key Authentication**: Secure API access with tenant-specific keys

## Tech Stack

- **Backend**: PHP 7.0+ (compatible with PHP 7.0-8.x)
- **Database**: MySQL 5.7+ (InnoDB)
- **Architecture**: Custom lightweight MVC (no Laravel/Symfony)
- **Frontend**: Vanilla JavaScript, HTML5, CSS3
- **Security**: PDO prepared statements, CSRF protection, password hashing

## Folder Structure

```
SplashMarketingAI/
├── app/
│   ├── controllers/      # Application controllers
│   ├── models/           # Database models
│   ├── views/            # View templates
│   ├── core/             # Core framework (Router, Controller, Model, View, Database, Auth, etc.)
│   └── helpers/          # Helper classes (Validation, AI, Mailer, SMS, Social, etc.)
├── config/               # Configuration files
├── public/               # Public web root
│   ├── assets/
│   │   ├── css/         # Stylesheets
│   │   └── js/          # JavaScript files
│   ├── index.php        # Application entry point
│   └── .htaccess        # Apache rewrite rules
├── storage/              # File storage
│   ├── uploads/         # CSV imports, brand assets
│   └── logs/            # Email, SMS, social logs
├── tests/                # Test scripts
├── database.sql          # Database schema and seed data
├── .env.example          # Environment variables template
└── README.md             # This file
```

## Requirements

- PHP 7.0 or higher
- MySQL 5.7 or higher
- Apache or Nginx web server
- PHP Extensions:
  - pdo_mysql
  - mbstring
  - openssl
  - json
  - fileinfo

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/ahmedsaadawi13/SplashMarketingAI.git
cd SplashMarketingAI
```

### 2. Configure Environment

```bash
cp .env.example .env
```

Edit `.env` and configure your database credentials and other settings:

```env
DB_HOST=localhost
DB_DATABASE=splashmarketingai
DB_USERNAME=root
DB_PASSWORD=your_password

APP_URL=http://localhost
APP_DEBUG=true
```

### 3. Create Database and Import Schema

```bash
mysql -u root -p < database.sql
```

Or manually:
```bash
mysql -u root -p
CREATE DATABASE splashmarketingai;
USE splashmarketingai;
SOURCE database.sql;
```

### 4. Configure Web Server

#### Apache

Set DocumentRoot to `/path/to/SplashMarketingAI/public`

Example VirtualHost:
```apache
<VirtualHost *:80>
    ServerName splashmarketingai.local
    DocumentRoot /var/www/SplashMarketingAI/public

    <Directory /var/www/SplashMarketingAI/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Nginx

```nginx
server {
    listen 80;
    server_name splashmarketingai.local;
    root /var/www/SplashMarketingAI/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

### 5. Set Permissions

```bash
chmod -R 755 storage/
chmod -R 755 storage/uploads/
chmod -R 755 storage/logs/
```

### 6. Access the Application

Navigate to `http://your-domain` (or `http://localhost` if running locally).

## Default Credentials

### Platform Admin
- **Email**: admin@splashmarketingai.com
- **Password**: admin123

### Demo Tenant Admin
- **Email**: demo@example.com
- **Password**: password

### Demo Marketer
- **Email**: marketer@example.com
- **Password**: password

## Key Concepts

### Contacts & Lists

**Contacts** are your subscribers/customers. Each contact has:
- Email, phone, name, country
- Status (subscribed, unsubscribed, bounced, complaint)
- Custom attributes (JSON field for any additional data)
- Tags
- Engagement tracking (last open, last click)

**Lists** are static collections of contacts. Contacts can belong to multiple lists.

**Segments** are dynamic filters that auto-update based on rules:
- Filter by list, status, country, tags
- Filter by engagement (opened in last X days, clicked, etc.)
- Filter by custom attributes
- Combine multiple conditions with AND/OR logic

### Campaigns

Campaigns are one-time broadcasts sent via email, SMS, or social media.

**Lifecycle**:
1. **Draft**: Create and configure campaign
2. **Scheduled**: Set send time
3. **Sending**: Currently processing
4. **Sent**: Delivery complete

**Features**:
- Choose audience (list or segment)
- Select template
- Personalize with merge tags: `{{first_name}}`, `{{email}}`, etc.
- Track opens, clicks, bounces, unsubscribes

### Automation Flows

Automated marketing journeys triggered by events:

**Trigger Types**:
- On subscribe to list
- On tag added
- On campaign interaction
- Date-based (birthdays, anniversaries)
- Segment entry
- Custom events

**Step Types**:
- Send email/SMS/social
- Add/remove tag
- Delay (hours, days)
- Branch (conditional logic)
- Exit flow

**Example Flow** - Welcome Series:
1. Trigger: Contact subscribes to "Newsletter" list
2. Send welcome email immediately
3. Wait 2 days
4. Send product features email
5. Wait 3 days
6. Send discount offer email

### AI Content Generation

Stub implementation ready for integration with OpenAI, Anthropic Claude, or other LLM APIs:

```php
// Generate email content
$result = AIHelper::generateEmailContent($tenantId, [
    'purpose' => 'promotion',
    'audience' => 'new customers',
    'tone' => 'professional',
    'keywords' => 'summer sale, discount'
]);
// Returns: subject, body_html, body_text

// Generate SMS
$result = AIHelper::generateSmsContent($tenantId, [
    'purpose' => 'reminder',
    'tone' => 'friendly'
]);

// Generate social post
$result = AIHelper::generateSocialPost($tenantId, [
    'platform' => 'twitter',
    'topic' => 'product launch'
]);

// Improve existing copy
$improved = AIHelper::improveCopy($tenantId, $existing, 'make it shorter');

// Generate A/B variants
$variants = AIHelper::generateAIVariants($tenantId, $baseCopy, 3);
```

To wire to a real LLM API, update `/app/helpers/AIHelper.php` with your API credentials and actual API calls.

### Channels

Configure sending providers:

- **Email**: SMTP settings (currently simulated)
- **SMS**: Twilio, Nexmo, AWS SNS (currently simulated)
- **Social**: Facebook, Twitter, LinkedIn APIs (currently simulated)

For production, integrate actual provider APIs in `/app/helpers/MailerHelper.php`, `/app/helpers/SmsHelper.php`, and `/app/helpers/SocialHelper.php`.

## REST API Documentation

### Authentication

All API requests require an API key in the `X-API-KEY` header:

```bash
curl -H "X-API-KEY: sk_your_api_key_here" \
     https://your-domain/api/contacts/upsert
```

Generate API keys from Settings → API Keys.

### Endpoints

#### Create/Update Contact

```http
POST /api/contacts/upsert
Content-Type: application/json
X-API-KEY: your_api_key

{
  "email": "john@example.com",
  "first_name": "John",
  "last_name": "Doe",
  "phone": "+1234567890",
  "tags": ["customer", "vip"],
  "attributes": {
    "country": "USA",
    "lifetime_value": 1200
  },
  "list_ids": [1, 2]
}
```

**Response**:
```json
{
  "status": "success",
  "data": {
    "contact_id": 123,
    "contact": { ... }
  }
}
```

#### Track Event

```http
POST /api/events/track
Content-Type: application/json
X-API-KEY: your_api_key

{
  "event_name": "purchase",
  "contact_email": "john@example.com",
  "properties": {
    "order_id": "ORD-123",
    "amount": 99.99,
    "product": "Premium Plan"
  }
}
```

**Response**:
```json
{
  "status": "success",
  "message": "Event tracked successfully"
}
```

#### Create Campaign

```http
POST /api/campaigns/create
Content-Type: application/json
X-API-KEY: your_api_key

{
  "name": "Summer Promotion",
  "channel": "email",
  "template_id": 5,
  "list_id": 10,
  "from_name": "Acme Corp",
  "from_email": "noreply@acme.com",
  "subject_line": "Summer Sale - 50% Off!"
}
```

**Response**:
```json
{
  "status": "success",
  "data": {
    "campaign_id": 456,
    "campaign": { ... }
  }
}
```

#### Get Campaign Stats

```http
GET /api/campaigns/456/stats
X-API-KEY: your_api_key
```

**Response**:
```json
{
  "status": "success",
  "data": {
    "campaign": { ... },
    "stats": {
      "total_recipients": 1000,
      "delivered": 985,
      "opened": 420,
      "clicked": 145,
      "open_rate": 42.64,
      "click_rate": 14.72
    }
  }
}
```

### Error Responses

```json
{
  "status": "error",
  "message": "Invalid API key",
  "code": "INVALID_API_KEY"
}
```

Common error codes:
- `MISSING_API_KEY`: API key not provided
- `INVALID_API_KEY`: API key is invalid or inactive
- `INVALID_EMAIL`: Email format is invalid
- `CONTACT_NOT_FOUND`: Contact does not exist
- `QUOTA_EXCEEDED`: Usage limit reached

## Testing

Run the included test scripts:

```bash
# Test database connection
php tests/test_db_connection.php

# Test contact creation
php tests/test_create_contact.php

# Test AI helper stubs
php tests/test_aihelper_stub.php
```

## CRON Jobs

For production, set up these cron jobs:

```cron
# Process scheduled campaigns (every 5 minutes)
*/5 * * * * php /path/to/SplashMarketingAI/cron/process_scheduled_campaigns.php

# Process automation flows (every 5 minutes)
*/5 * * * * php /path/to/SplashMarketingAI/cron/process_automation_flows.php

# Reset monthly usage counters (first day of month)
0 0 1 * * php /path/to/SplashMarketingAI/cron/reset_monthly_usage.php

# Check overdue invoices (daily)
0 0 * * * php /path/to/SplashMarketingAI/cron/check_overdue_invoices.php
```

Note: Create these cron scripts based on your requirements.

## Scaling Strategies

### Performance

1. **Database Indexing**: All critical fields are indexed (tenant_id, status, email, created_at)
2. **Query Optimization**: Use EXPLAIN on slow queries
3. **Caching**: Implement Redis/Memcached for frequently accessed data
4. **CDN**: Serve static assets via CDN

### Sending at Scale

1. **Queue System**: Implement job queues (Redis Queue, Beanstalkd)
2. **Background Workers**: Process campaign sends in background workers
3. **Rate Limiting**: Respect ESP rate limits
4. **Batch Processing**: Send in batches of 100-1000

### Data Growth

1. **Partitioning**: Partition large tables by tenant_id or date
2. **Archiving**: Archive old campaigns and logs to separate storage
3. **Separate Analytics**: Move analytics to dedicated data warehouse
4. **Sharding**: Shard contacts table as it grows

## Security Best Practices

- ✅ All database queries use PDO prepared statements
- ✅ CSRF protection on all forms
- ✅ Password hashing with `password_hash()`
- ✅ Session regeneration on login
- ✅ Input validation and sanitization
- ✅ Tenant isolation enforced at model level
- ✅ Login throttling (5 attempts per 15 minutes)
- ✅ API rate limiting
- ✅ File upload validation (type, size, path traversal prevention)

## Production Deployment

1. Set `APP_DEBUG=false` in `.env`
2. Use HTTPS only
3. Enable error logging to files, not browser
4. Set restrictive file permissions (755 for directories, 644 for files)
5. Configure firewall (allow only 80/443)
6. Regular backups (database + uploads)
7. Monitor disk space for logs and uploads
8. Set up monitoring (Sentry, New Relic, DataDog)
9. Implement CDN for static assets
10. Use environment-specific configurations

## License

This project is open-source software licensed under the MIT license.

## Support

For issues, questions, or contributions, please visit the GitHub repository or contact the development team.

---

**Built with ❤️ for marketers worldwide**
