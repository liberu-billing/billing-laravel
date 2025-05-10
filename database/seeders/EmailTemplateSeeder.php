<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;

class EmailTemplateSeeder extends Seeder
{
    public function run()
    {
        EmailTemplate::create([
            'name' => 'Default Invoice Generated',
            'type' => 'invoice_generated',
            'subject' => 'Invoice #{{invoice_number}} Generated',
            'body' => 'Dear {{customer_name}},

Your invoice #{{invoice_number}} has been generated for the amount of {{amount}}.
Due date: {{due_date}}

Thank you for your business!',
            'is_default' => true,
        ]);

        EmailTemplate::create([
            'name' => 'Default Overdue Reminder',
            'type' => 'overdue_reminder',
            'subject' => 'Overdue Invoice Reminder #{{invoice_number}}',
            'body' => 'Dear {{customer_name}},

This is a reminder that invoice #{{invoice_number}} for {{amount}} is overdue.
Please process the payment as soon as possible.

Thank you for your attention to this matter.',
            'is_default' => true,
        ]);
    }
}