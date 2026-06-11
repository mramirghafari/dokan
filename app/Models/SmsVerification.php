<?php

namespace App\Models;

use App\Casts\changeDate;
use Illuminate\Database\Eloquent\Model;

class SmsVerification extends Model
{
    protected $guarded = [
        'id'
    ];
//    protected $casts=[
//        'created_at' => changeDate::class
//    ];

    public function store($data)
    {
        $this->fill($data);
        $sms = $this->save();
        return response()->json($sms, 200);
    }

    public function updateModel($request)
    {
        $this->update($request->all());
        return $this;
    }
}
