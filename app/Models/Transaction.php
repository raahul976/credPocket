<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
'sender_id', 'receiver_id', 'price', 'no_of_installments', 'status', 'approved'
    ];
}

// CREATE TABLE `cred-pocket`.`instalment_transactions` ( `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT , `transaction_id` BIGINT UNSIGNED NOT NULL , `lender_id` BIGINT UNSIGNED NOT NULL , `borrower_id` BIGINT UNSIGNED NOT NULL , `instalment_count` BIGINT NOT NULL , `instalment_amount` DECIMAL NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
// Query for instalment_transactions