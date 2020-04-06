<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    public function details()
    {
        return $this->hasMany('App\Model\CustomersServicesDetails', 'service_id');
    }

    /*public function customer()
    {
        return $this->hasOne('App\Model\Customer', 'id', 'customer_id');
    }*/
}
