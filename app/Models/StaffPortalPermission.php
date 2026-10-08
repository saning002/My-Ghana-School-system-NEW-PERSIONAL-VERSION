<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffPortalPermission extends Model
{
    protected $fillable = ['staff_portal_user_id', 'permission'];

    public function staffUser()
    {
        return $this->belongsTo(StaffPortalUser::class, 'staff_portal_user_id');
    }
}
