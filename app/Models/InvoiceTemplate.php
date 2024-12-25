

<?php

namespace App\Models;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class InvoiceTemplate extends Model
{
    use HasFactory;
    use HasTeam;

    protected $fillable = [
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
        'team_id'
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public static function getDefault()
    {
        return static::where('team_id', auth()->user()->currentTeam->id)
            ->where('is_default', true)
            ->first();
    }

    public function getLogoUrlAttribute()
    {
        return $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null;
    }

    public function getStyledHtmlAttribute()
    {
        return sprintf(
            '<style>
                .invoice-box { color: %1$s; }
                .invoice-header { border-color: %1$s; }
                .invoice-items th { background-color: %1$s; color: white; }
                .invoice-items td { border-color: %1$s; }
            </style>',
            $this->color_scheme
        );
    }
}