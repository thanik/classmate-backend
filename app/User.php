<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Jenssegers\Mongodb\Eloquent\HybridRelations;

class User extends Authenticatable
{
    use HybridRelations;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function courses()
    {
        return $this->belongsToMany('App\Course','users_courses')->withTimestamps()->withPivot('role');
    }

    public function materials()
    {
        return $this->hasMany('App\Material');
    }

    public function discussion_posts()
    {
        return $this->hasMany('App\DiscussionPost');
    }

    public function comments()
    {
        return $this->hasMany('App\Comment');
    }

    public function files()
    {
        return $this->hasMany('App\File');
    }

    public function announcements()
    {
        return $this->hasMany('App\Announcement');
    }

    public function organizations()
    {
        return $this->belongsToMany('App\Organization','users_organizations')->withPivot('role','student_id','faculty_field');
    }
}
