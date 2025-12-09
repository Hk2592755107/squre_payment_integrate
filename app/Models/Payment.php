<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'square_payment_id',
        'amount',
        'currency',
        'status',
        'customer_id',
        'customer_email',
        'order_id',
        'note',
        'payment_data',
        'request_data',
        'idempotency_key',
        'location_id',
        'source_type',
        'error_message'
    ];

    protected $casts = [
        'payment_data' => 'array',
        'request_data' => 'array',
        'error_message' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Status constants
    const STATUS_COMPLETED = 'COMPLETED';
    const STATUS_PENDING = 'PENDING';
    const STATUS_FAILED = 'FAILED';
    const STATUS_CANCELED = 'CANCELED';
    const STATUS_APPROVED = 'APPROVED';

    /**
     * Check if payment is successful
     */
    public function isSuccessful()
    {
        return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_APPROVED,self::STATUS_FAILED, self::STATUS_CANCELED, self::STATUS_PENDING]);
    }

    /**
     * Get formatted amount with currency
     */
    public function getFormattedAmountAttribute()
    {
        return $this->currency . ' ' . number_format($this->amount, 2);
    }

    /**
     * Get payment date in readable format
     */
    public function getPaymentDateAttribute()
    {
        return $this->created_at->format('M d, Y h:i A');
    }
}
