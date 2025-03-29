<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Invoice extends Model
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
        'invoice_number',
        'invoice_date',
        'due_date',
        'notes',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'total',
        'status',
        'paid_at',
        'payment_method',
        'cancelled_at',
        'cancellation_reason',
    ];

    /**
     * Les attributs qui doivent être convertis en dates.
     *
     * @var array<int, string>
     */
    protected $dates = [
        'invoice_date',
        'due_date',
        'paid_at',
        'cancelled_at',
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
     * Relation avec les lignes de facturation.
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Relation avec le devis d'origine (si converti).
     */
    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }
}
