<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    use HasFactory;

    /**
     * Les attributs assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'company_name',
        'company_address',
        'company_city',
        'company_postal_code',
        'company_country',
        'company_phone',
        'company_email',
        'company_website',
        'company_siret',
        'company_vat',
        'invoice_prefix',
        'invoice_next_number',
        'quote_prefix',
        'quote_next_number',
        'payment_terms',
        'default_tax_rate',
        'currency',
        'logo_path',
        'invoice_footer',
        'quote_footer',
    ];

    /**
     * Relation avec l'utilisateur.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
