<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Facebook\Facebook;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Announcement;
use App\Course;
use App\User;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $course = Course::find($request->input('data.relationships.course.data.id'));
        $this_user = User::find(strval($request->attributes->get('tokendata')['uid']));

        $new_announcement = new Announcement();
        $new_announcement->title = $request->input('data.attributes.title');
        $new_announcement->content = $request->input('data.attributes.content');
        $new_announcement->course_id = $course->id;
        $new_announcement->user_id = $this_user->id;
        $new_announcement->save();

        /* send notification */
        $fb = new Facebook([
            'app_id' => env('FB_APP_ID'),
            'app_secret' => env('FB_SECRET'),
            'default_graph_version' => 'v2.5',
        ]);

        $app_access_token_request = $fb->get('/oauth/access_token?client_id='.env('FB_APP_ID').'&client_secret='.env('FB_SECRET').'&grant_type=client_credentials',$this_user->facebook_token);
        $app_access_token = $app_access_token_request->getDecodedBody()['access_token'];
        $noti_param = [
            'access_token' => $app_access_token,
            'href' => 'student/course/'.$course->id.'/announcements',
            'template' => '@['.$this_user->facebook_id.'] just posted a new announcement on '.$course->course_code,
        ];
        foreach(Course::find($request->input('data.relationships.course.data.id'))->users()->where('role',0)->get() as $student)
        {
            $fb->post('/'.$student->facebook_id.'/notifications',$noti_param);

        }


        $response = $this->show($new_announcement->id);
        return $response;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try
        {
            $announcement = Announcement::findOrFail($id);
            $response['data'] = [
                'type' => 'announcement',
                'id' => $announcement->id,
                'attributes' => [
                    'title' => $announcement->title,
                    'content' => $announcement->content,
                    'created-at' => date('c',strtotime($announcement->created_at)),
                    'updated-at' => date('c',strtotime($announcement->updated_at)),
                ],

                'relationships' => [
                    'user' => ['data' => ['type' => 'user','id' => $announcement->user_id]]
                ],
            ];

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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

    }
}
