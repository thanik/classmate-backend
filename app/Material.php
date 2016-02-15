<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $connection = 'mysql';
    protected $table = 'materials';

    public function course()
    {
        return $this->belongsTo('App\Course');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function files()
    {
        return $this->hasMany('App\File');
    }
}
