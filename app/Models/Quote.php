<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Quote extends Model
{
    use HasFactory;

    /**
     * Les attributs assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'client_id',
        'quote_number',
        'quote_date',
        'expiry_date',
        'notes',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'total',
        'status',
        'invoice_id',
    ];

    /**
     * Les attributs qui doivent être convertis en dates.
     *
     * @var array<int, string>
     */
    protected $dates = [
        'quote_date',
        'expiry_date',
    ];

    /**
     * Relation avec l'utilisateur.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec le client.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relation avec les lignes de devis.
     */
    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    /**
     * Relation avec la facture générée (si converti).
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }
}
