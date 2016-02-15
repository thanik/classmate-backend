<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Model;

class DiscussionPost extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'discussions';

    public function course()
    {
        return $this->belongsTo('App\Course');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function comments()
    {
        return $this->hasMany('App\Comment');
    }


}
