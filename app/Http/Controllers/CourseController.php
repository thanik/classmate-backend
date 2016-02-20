<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Course;
use App\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if(is_numeric($request->role))
        {
            $role_filter = $request->role;
        }
        else
        {
            $role_filter = 0;
        }
        $courses = User::find($request->attributes->get('tokendata')['uid'])->courses()->wherePivot('role',$role_filter)->get();
        $response = [
            'data' => []
        ];

        $i=0;
        foreach($courses as $course)
        {
            $response['data'][] = [
                'type' => 'course',
                'id' => $course->id,
                'attributes' => [
                    'course-code' => $course->course_code,
                    'name' => $course->name,
                    'join-code' => $course->join_code,
                    'created-at' => date('c',strtotime($course->created_at)),
                    'updated-at' => date('c',strtotime($course->updated_at)),
                    'role' => $course->pivot->role,
                ],
                'links' => [
                    'announcements' => '/courses/'.$course->id.'/announcements',
                    'materials' => '/courses/'.$course->id.'/materials',
                    'discussionposts' => '/courses/'.$course->id.'/discussionposts',
                ],

                'relationships' => [
                    'announcements' => [],
                    'materials' => [],
                    'discussionposts' => [],
                ],

            ];

            $announcements_array = [];
            $materials_array = [];
            $discusspost_array = [];
            foreach($course->announcements()->orderBy('created_at', 'desc')->get() as $announcement)
            {
                $announcement_obj = [
                    'type' => 'announcement',
                    'id' => $announcement->id,
                ];
                array_push($announcements_array, $announcement_obj);

            }
            $response['data'][$i]['relationships']['announcements']['data'] = $announcements_array;

            foreach($course->materials()->orderBy('updated_at', 'desc')->get() as $material)
            {
                $material_obj = [
                    'type' => 'material',
                    'id' => $material->id,
                ];
                array_push($materials_array, $material_obj);

            }
            $response['data'][$i]['relationships']['materials']['data'] = $materials_array;

            foreach($course->discussion_posts()->orderBy('updated_at', 'desc')->get() as $discusspost)
            {
                $discusspost_obj = [
                    'type' => 'discussionpost',
                    'id' => $discusspost->id,
                ];
                array_push($discusspost_array, $discusspost_obj);

            }
            $response['data'][$i]['relationships']['discussionposts']['data'] = $discusspost_array;
            $i++;
        }

        return response()->json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $new_course = new Course();
        $new_course->course_code = $request->input('data.attributes.course-code');
        $new_course->name = $request->input('data.attributes.name');
        $new_course->join_code = substr(str_shuffle(MD5(microtime())), 0, 6);
        $new_course->organization_id = User::find(strval($request->attributes->get('tokendata')['uid']))->default_organization_id;
        $new_course->save();

        $new_course->users()->attach(strval($request->attributes->get('tokendata')['uid']),['role' => 1]);

        $response = $this->show($new_course->id, $request);
        return $response;

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        try
        {
            $course = Course::findOrFail($id);

            $response['data'] = [
                'type' => 'course',
                'id' => $course->id,
                'attributes' => [
                    'course-code' => $course->course_code,
                    'name' => $course->name,
                    'join-code' => $course->join_code,
                    'created-at' => date('c',strtotime($course->created_at)),
                    'updated-at' => date('c',strtotime($course->updated_at)),
                    'role' => $course->users()->find(strval($request->attributes->get('tokendata')['uid']))->pivot->role,
                ],
                'links' => [
                    'announcements' => '/courses/'.$course->id.'/announcements',
                    'materials' => '/courses/'.$course->id.'/materials',
                    'discussionposts' => '/courses/'.$course->id.'/discussionposts',
                ],

                'relationships' => [
                    'announcements' => [],
                    'materials' => [],
                    'discussionposts' => [],
                ],

            ];

            $announcements_array = [];
            $materials_array = [];
            $discusspost_array = [];
            foreach($course->announcements()->orderBy('created_at', 'desc')->get() as $announcement)
            {
                $announcement_obj = [
                    'type' => 'announcement',
                    'id' => $announcement->id,
                ];
                array_push($announcements_array, $announcement_obj);

            }
            $response['data']['relationships']['announcements']['data'] = $announcements_array;

            foreach($course->materials()->orderBy('updated_at', 'desc')->get() as $material)
            {
                $material_obj = [
                    'type' => 'material',
                    'id' => $material->id,
                ];
                array_push($materials_array, $material_obj);

            }
            $response['data']['relationships']['materials']['data'] = $materials_array;

            foreach($course->discussion_posts()->orderBy('updated_at', 'desc')->get() as $discusspost)
            {
                $discusspost_obj = [
                    'type' => 'discussionpost',
                    'id' => $discusspost->id,
                ];
                array_push($discusspost_array, $discusspost_obj);

            }
            $response['data']['relationships']['discussionposts']['data'] = $discusspost_array;

            /*
             * 'relationships' => [
                    'latest_announcement' => ['data' => [
                        'type' => 'announcement',
                        'id' => $latest_announcement->id,
                    ]],

                    'latest_material' => ['data' => [
                        'type' => 'material',
                        'id' => $latest_material->id,
                    ]],

                    'latest_thread' => ['data' => [
                        'type' => 'discussionpost',
                        'id' => $latest_thread->id,
                    ]],

                ],
             */
            return response()->json($response,200);
        }
        catch (ModelNotFoundException $e)
        {
            return response()->json(null,404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if($request->attributes->get('tokendata')['default_role'] == '1')
        {

        }
        else
        {
            return response(null,403);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if($request->attributes->get('tokendata')['default_role'] == '1')
        {

        }
        else
        {
            return response(null,403);
        }
    }

    public function getLatestMaterial($id, Request $request)
    {
        try
        {
            $course = Course::findOrFail($id);
            $latest_material = $course->materials()->orderBy('updated_at', 'desc')->firstOrFail();
            return response()->json(['status' => 'success',
                'id' => $latest_material->id,
                'title' => $latest_material->title,
                'time' => date('c',strtotime($latest_material->updated_at)),
                'name' => $latest_material->user()->first()->name,
                'user_pic' => env('USERDATA_DOMAIN').'\\avatars\\'.$latest_material->user()->first()->avatar_filename,
            ],200);
        }
        catch(ModelNotFoundException $e)
        {
            return response()->json(['status' => 'nopost'],200);
        }
    }

    public function getLatestAnnouncement($id, Request $request)
    {
        try
        {
            $course = Course::findOrFail($id);
            $latest_announcement = $course->announcements()->orderBy('created_at', 'desc')->firstOrFail();
            return response()->json(['status' => 'success',
                'id' => $latest_announcement->id,
                'title' => $latest_announcement->title,
                'content' => $latest_announcement->content,
                'time' => date('c',strtotime($latest_announcement->updated_at)),
                'name' => $latest_announcement->user()->first()->name,
                'user_pic' => env('USERDATA_DOMAIN').'\\avatars\\'.$latest_announcement->user()->first()->avatar_filename,
            ],200);
        }
        catch(ModelNotFoundException $e)
        {
            return response()->json(['status' => 'nopost'],200);
        }
    }

    public function getLatestDiscussionPost($id, Request $request)
    {
        try
        {
            $course = Course::findOrFail($id);
            $latest_discussionpost = $course->discussion_posts()->orderBy('updated_at', 'desc')->firstOrFail();
            return response()->json(['status' => 'success',
                'id' => $latest_discussionpost->id,
                'title' => $latest_discussionpost->topic,
                'time' => date('c',strtotime($latest_discussionpost->updated_at)),
                'name' => $latest_discussionpost->user()->first()->name,
                'user_pic' => env('USERDATA_DOMAIN').'\\avatars\\'.$latest_discussionpost->user()->first()->avatar_filename,
            ],200);
        }
        catch(ModelNotFoundException $e)
        {
            return response()->json(['status' => 'nopost'],200);
        }
    }

    public function joinCourse(Request $request)
    {
        try
        {
            $course = Course::where('join_code', $request->input('joinCode'))->firstOrFail();
            //echo($request->attributes->get('tokendata')['uid']);
            if($course->users->contains(strval($request->attributes->get('tokendata')['uid'])))
            {
                return response()->json(['status' => 'fail', 'message' => 'You already joined the course with this code.'],200);
            }
            else
            {
                $course->users()->attach(strval($request->attributes->get('tokendata')['uid']), ['role' => 0]);
                return response()->json(['status' => 'success'],200);
            }

        }
        catch(ModelNotFoundException $e)
        {
            return response()->json(['status' => 'fail', 'message' => 'There\'s no course with this join code. Please check it again.'],200);
        }
    }
}
