<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Model;

class Announcement extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'announcements';

    public function course()
    {
        return $this->belongsTo('App\Course');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }


}
