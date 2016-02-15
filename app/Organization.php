<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $connection = 'mysql';
    protected $table = 'organizations';
    public $timestamps = false;

    public function users()
    {
        return $this->belongsToMany('App\User', 'users_organizations')->withPivot('role','student_id','faculty_field');
    }

    public function courses()
    {
        return $this->hasMany('App\Course');
    }
}
