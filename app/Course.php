<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Jenssegers\Mongodb\Eloquent\HybridRelations;

class Course extends Model
{
    use HybridRelations;

    protected $connection = 'mysql';
    protected $table = 'courses';
    protected $primaryKey = 'id';

    public function organization()
    {
        return $this->belongsTo('App\Organization');
    }

    public function announcements()
    {
        return $this->hasMany('App\Announcement');
    }

    public function discussion_posts()
    {
        return $this->hasMany('App\DiscussionPost');
    }

    public function materials()
    {
        return $this->hasMany('App\Material');
    }

    public function users()
    {
        return $this->belongsToMany('App\User','users_courses')->withTimestamps()->withPivot('role');
    }
}
