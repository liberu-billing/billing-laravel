<?php

namespace App\Models;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Override;

#[Fillable([
    'name',
    'company_name',
    'company_address',
    'company_phone',
    'company_email',
    'logo_path',
    'header_text',
    'footer_text',
    'color_scheme',
    'is_default',
    'team_id',
])]
class InvoiceTemplate extends Model
{
    use HasTeam;

    #[Override]
    protected function casts(): array
    {

        return [
            'is_default' => 'boolean',
        ];

    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public static function getDefault()
    {
        return static::where(
            'team_id',
            auth()->user()->currentTeam->id
        )
            ->where(
                'is_default',
                true
            )
            ->first();
    }

    protected function logoUrl(): Attribute
    {
        return Attribute::make(get: fn() => $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null);
    }

    protected function styledHtml(): Attribute
    {
        return Attribute::make(
            get: fn(): string => sprintf(
            '<style>
                .invoice-box { color: %1$s; }
                .invoice-header { border-color: %1$s; }
                .invoice-items th { background-color: %1$s; color: white; }
                .invoice-items td { border-color: %1$s; }
            </style>',
            $this->color_scheme
        )
        );
    }
}
