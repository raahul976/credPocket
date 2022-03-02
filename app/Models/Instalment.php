<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Instalment extends Model
{
    use HasFactory;

    protected $table = 'instalment_transactions';
    protected $fillable = [
        'transaction_id', 'lender_id', 'borrower_id', 'instalment_count', 'instalment_amount'
            ];
}
