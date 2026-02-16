# WHMCS Features Documentation

This document describes the additional WHMCS-compatible features that have been added to the billing system.

## Table of Contents

1. [Webhooks & Automation](#webhooks--automation)
2. [Knowledge Base](#knowledge-base)
3. [Canned Responses](#canned-responses)
4. [Bulk Operations](#bulk-operations)
5. [Service Automation](#service-automation)

## Webhooks & Automation

### Overview

The webhook system allows you to receive real-time notifications when specific events occur in your billing system. This enables integration with external systems and automation workflows.

### Available Events

- `invoice.created` - When a new invoice is generated
- `invoice.updated` - When an invoice is modified
- `invoice.paid` - When an invoice is marked as paid
- `invoice.overdue` - When an invoice becomes overdue
- `payment.received` - When a payment is received
- `payment.failed` - When a payment fails
- `payment.refunded` - When a payment is refunded
- `subscription.created` - When a new subscription is created
- `subscription.updated` - When a subscription is modified
- `subscription.cancelled` - When a subscription is cancelled
- `subscription.renewed` - When a subscription renews
- `client.created` - When a new client is added
- `client.updated` - When client information is updated
- `service.provisioned` - When a service is provisioned
- `service.suspended` - When a service is suspended
- `service.terminated` - When a service is terminated
- `ticket.created` - When a support ticket is created
- `ticket.updated` - When a ticket is updated
- `ticket.closed` - When a ticket is closed

### API Endpoints

#### List Webhooks
```http
GET /api/webhooks
Authorization: Bearer {token}
```

#### Create Webhook
```http
POST /api/webhooks
Authorization: Bearer {token}
Content-Type: application/json

{
  "url": "https://your-domain.com/webhook",
  "secret": "your-secret-key",
  "events": ["invoice.created", "payment.received"],
  "description": "Main webhook endpoint",
  "max_retries": 3,
  "retry_interval": 60
}
```

#### Test Webhook
```http
POST /api/webhooks/{id}/test
Authorization: Bearer {token}
```

#### Get Webhook Events
```http
GET /api/webhooks/{id}/events
Authorization: Bearer {token}
```

### Webhook Payload

All webhooks include the following structure:

```json
{
  "event": "invoice.created",
  "data": {
    "invoice_id": 123,
    "amount": 100.00,
    "customer_id": 456
  },
  "timestamp": "2026-02-15T10:30:00Z",
  "id": 789
}
```

### Signature Verification

Webhooks include an `X-Webhook-Signature` header with an HMAC-SHA256 signature. Verify it using:

```php
$signature = $request->header('X-Webhook-Signature');
$payload = $request->getContent();
$secret = 'your-webhook-secret';

$isValid = hash_equals(
    hash_hmac('sha256', $payload, $secret),
    $signature
);
```

### Console Commands

Process pending webhooks manually:
```bash
php artisan webhooks:process
```

You can also schedule this in your `app/Console/Kernel.php`:
```php
$schedule->command('webhooks:process')->everyFiveMinutes();
```

## Knowledge Base

### Overview

The knowledge base system allows you to create and manage help articles for your clients. Articles are organized into categories and support full-text search.

### API Endpoints

#### Get Categories
```http
GET /api/knowledge-base/categories
```

#### Search Articles
```http
GET /api/knowledge-base/search?q=password&limit=10
```

#### Get Featured Articles
```http
GET /api/knowledge-base/featured?limit=5
```

#### Get Article by Slug
```http
GET /api/knowledge-base/articles/{slug}
```

#### Mark Article as Helpful
```http
POST /api/knowledge-base/articles/{slug}/helpful
```

#### Get Articles by Category
```http
GET /api/knowledge-base/categories/{categoryId}/articles
```

### Article Structure

```json
{
  "id": 1,
  "title": "How to Reset Your Password",
  "slug": "how-to-reset-password",
  "summary": "Step-by-step guide to reset your account password",
  "content": "Full article content in HTML...",
  "category": {
    "id": 1,
    "name": "Account Management"
  },
  "author": {
    "id": 1,
    "name": "Support Team"
  },
  "view_count": 1523,
  "helpful_count": 145,
  "not_helpful_count": 12,
  "is_featured": true,
  "published_at": "2026-01-15T10:00:00Z"
}
```

## Canned Responses

### Overview

Canned responses allow support staff to quickly insert pre-written responses with variable replacement for common support scenarios.

### API Endpoints

#### Get All Canned Responses
```http
GET /api/canned-responses
Authorization: Bearer {token}
```

#### Get by Shortcode
```http
GET /api/canned-responses/{shortcode}
Authorization: Bearer {token}
```

#### Use Canned Response
```http
POST /api/canned-responses/{shortcode}/use
Authorization: Bearer {token}
Content-Type: application/json

{
  "variables": {
    "client_name": "John Doe",
    "ticket_id": "12345"
  }
}
```

#### Search Responses
```http
GET /api/canned-responses/search?q=password
Authorization: Bearer {token}
```

#### Get Most Used
```http
GET /api/canned-responses/most-used?limit=10
Authorization: Bearer {token}
```

### Available Variables

- `{{client_name}}` - Client's full name
- `{{client_email}}` - Client's email address
- `{{ticket_id}}` - Support ticket ID
- `{{ticket_subject}}` - Ticket subject line
- `{{invoice_number}}` - Invoice number
- `{{invoice_amount}}` - Invoice amount
- `{{due_date}}` - Payment due date
- `{{company_name}}` - Your company name
- `{{support_email}}` - Support email address
- `{{support_phone}}` - Support phone number

### Example Usage

Template:
```
Hello {{client_name}},

Thank you for contacting us regarding ticket #{{ticket_id}}.

We have reviewed your request and will respond within 24 hours.

Best regards,
{{company_name}} Support Team
```

With variables:
```json
{
  "client_name": "Jane Smith",
  "ticket_id": "5678",
  "company_name": "Acme Corp"
}
```

Result:
```
Hello Jane Smith,

Thank you for contacting us regarding ticket #5678.

We have reviewed your request and will respond within 24 hours.

Best regards,
Acme Corp Support Team
```

## Bulk Operations

### Overview

Bulk operations allow you to perform actions on multiple records simultaneously, such as generating invoices, sending email campaigns, or exporting data.

### Features

- **Bulk Invoice Generation**: Generate invoices for multiple clients
- **Email Campaigns**: Send targeted email campaigns to client groups
- **Data Export**: Export client data (GDPR compliant)
- **Client Import**: Import clients from CSV files

### Bulk Operation Status

Operations have the following statuses:
- `pending` - Queued but not started
- `processing` - Currently running
- `completed` - Successfully finished
- `failed` - Encountered an error

### Monitoring Progress

Each bulk operation tracks:
- `total_items` - Total number of items to process
- `processed_items` - Number successfully processed
- `failed_items` - Number that failed
- `result_file` - Download link for exports

## Service Automation

### Overview

Service automation provides automatic suspension and management of services based on payment status and other criteria.

### Features

#### Auto-Suspension for Overdue Invoices

Automatically suspend services when invoices are overdue:

```bash
php artisan services:suspend-overdue --days=7
```

This will suspend all services with invoices overdue by 7 or more days.

#### Auto-Unsuspension

Services are automatically unsuspended when the associated invoice is paid.

#### Service Termination

Services can be terminated permanently through the API or admin interface.

### Console Commands

Schedule in `app/Console/Kernel.php`:

```php
// Check for overdue services daily
$schedule->command('services:suspend-overdue --days=7')->daily();

// Process pending webhooks every 5 minutes
$schedule->command('webhooks:process')->everyFiveMinutes();
```

### Suspension Reasons

- `overdue_payment` - Payment past due date
- `manual` - Manually suspended by admin
- `terms_violation` - Terms of service violation
- `fraud` - Suspected fraudulent activity

## Integration Examples

### PHP Example

```php
use App\Services\WebhookService;

// Dispatch a webhook when invoice is created
$webhookService = app(WebhookService::class);
$webhookService->dispatch(
    WebhookService::EVENT_INVOICE_CREATED,
    [
        'invoice_id' => $invoice->id,
        'amount' => $invoice->total_amount,
        'customer_id' => $invoice->customer_id,
    ]
);
```

### JavaScript Example

```javascript
// Fetch knowledge base articles
fetch('/api/knowledge-base/search?q=billing')
  .then(response => response.json())
  .then(data => {
    console.log('Found articles:', data.data);
  });

// Use a canned response
fetch('/api/canned-responses/welcome/use', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer ' + token
  },
  body: JSON.stringify({
    variables: {
      client_name: 'John Doe',
      ticket_id: '12345'
    }
  })
})
.then(response => response.json())
.then(data => {
  console.log('Response:', data.content);
});
```

## Security Considerations

1. **Webhook Secrets**: Always use unique, strong secrets for webhook endpoints
2. **Signature Verification**: Always verify webhook signatures before processing
3. **API Authentication**: All management endpoints require authentication
4. **Rate Limiting**: API endpoints are rate-limited to prevent abuse
5. **HTTPS Only**: Webhook URLs must use HTTPS in production

## Support

For questions or issues with these features, please:
1. Check the API documentation
2. Review the test files for usage examples
3. Open an issue on GitHub with detailed reproduction steps
