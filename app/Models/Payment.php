<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    protected $casts = [
        'payment_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'amount' => 'float',
        'total_amount' => 'float',
        'total_due_amount' => 'float',
        'room_price' => 'float',
        'total_discount' => 'float',
        'total_amount_amenity' => 'float',
        'total_utility_amount' => 'float',
    ];

    public function userContract()
    {
        return $this->belongsTo(UserContract::class);
    }
    
    public function paymentamenities()
    {
        return $this->hasMany(PaymentAmenity::class, 'payment_id');
    }
    
    public function paymentutilities()
    {
        return $this->hasMany(PaymentUtility::class, 'payment_id');
    }
    
    public function getRemainingDueAmount()
    {
        return max(0, $this->total_amount - $this->amount);
    }
    
    public function isFullyPaid()
    {
        return $this->payment_status === 'completed' || $this->total_due_amount <= 0;
    }
    
    public function recalculateTotals()
    {
        $this->total_amount_amenity = $this->paymentamenities->sum('amenity_price');
        $this->total_utility_amount = $this->paymentutilities->sum('total_amount');
        $this->total_amount = $this->room_price + $this->total_amount_amenity + $this->total_utility_amount;
        $this->total_due_amount = max(0, $this->total_amount - $this->amount);
        $this->payment_status = ($this->total_due_amount <= 0) ? 'completed' : 'partial';
        
        return $this;
    }
}
