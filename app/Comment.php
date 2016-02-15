<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Model;

class Comment extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'comments';

    public function post()
    {
        return $this->belongsTo('App\DiscussionPost');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

}
