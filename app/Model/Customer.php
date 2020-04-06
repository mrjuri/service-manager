<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    public function details()
    {
        return $this->hasMany('App\Model\CustomersServicesDetails', 'customer_id');
    }
}
