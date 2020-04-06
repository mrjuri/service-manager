<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Input;

class CustomersServicesDetails extends Model
{
    public function service()
    {
        $s = Input::get('s');
        $s_array = explode(' ', $s);

        if (isset($s) && 1 == 2) {

            return $this->hasOne('App\Model\Service', 'id', 'service_id')
                ->where(function ($q) use ($s_array){

                    foreach ($s_array as $s) {
                        $q->orWhere('services.name', 'LIKE', '%' . $s . '%');
                    }

                });

        } else {

            return $this->hasOne('App\Model\Service', 'id', 'service_id');
        }
    }

    public function customer()
    {
        return $this->hasMany('App\Model\Customer', 'id', 'customer_id');
    }
}
