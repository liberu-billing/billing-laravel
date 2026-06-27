<?php

declare(strict_types=1);

namespace App\Enums;

enum TokenAbility: string
{
    case InvoicesRead = 'invoices:read';
    case InvoicesWrite = 'invoices:write';
    case SubscriptionsRead = 'subscriptions:read';
    case SubscriptionsWrite = 'subscriptions:write';
    case CustomersRead = 'customers:read';
    case CustomersWrite = 'customers:write';
    case QuotesRead = 'quotes:read';
    case QuotesWrite = 'quotes:write';
    case WebhooksManage = 'webhooks:manage';
    case CannedResponsesRead = 'canned-responses:read';
    case ClientNotesWrite = 'client-notes:write';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $ability): string => $ability->value, self::cases());
    }
}
