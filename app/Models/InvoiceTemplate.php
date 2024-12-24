

<?php

namespace App\Models;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'is_default'
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}