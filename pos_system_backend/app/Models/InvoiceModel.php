<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceModel extends Model
{
    use HasFactory;

    protected $table = 'invoices';

    protected $fillable = ['id','sale_id'];

    public function sale() {
    return $this->belongsTo(SaleModel::class);
    }

}
