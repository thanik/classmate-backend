<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\DiscussionPost;
use App\User;
use App\Course;
use App\Comment;

class DiscussionController extends Controller
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
        $new_discussionpost = new DiscussionPost();
        $new_discussionpost->topic = $request->input('data.attributes.topic');
        $new_discussionpost->content = $request->input('data.attributes.content');
        $new_discussionpost->course_id = Course::find($request->input('data.relationships.course.data.id'))->id;
        $new_discussionpost->user_id = User::find(strval($request->attributes->get('tokendata')['uid']))->id;
        $new_discussionpost->save();

        $response = $this->show($new_discussionpost->id);
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
            $discussionpost = DiscussionPost::findOrFail($id);
            $response['data'] = [
                'type' => 'discussionpost',
                'id' => $discussionpost->id,
                'attributes' => [
                    'topic' => $discussionpost->topic,
                    'content' => $discussionpost->content,
                    'created-at' => date('c',strtotime($discussionpost->created_at)),
                    'updated-at' => date('c',strtotime($discussionpost->updated_at)),
                ],

                'relationships' => [
                    'user' => ['data' => ['type' => 'user','id' => $discussionpost->user_id]],
                    'course' => ['data' => ['type' => 'course','id' => $discussionpost->course_id]]
                ],
            ];


            $comments_array = [];
            $files_array = [];
            /*foreach($discussionpost->files()->orderBy('created_at', 'desc')->get() as $file)
            {
                $file_obj = [
                    'type' => 'file',
                    'id' => $file->id,
                ];
                array_push($files_array, $file_obj);

            }
            $response['data']['relationships']['files']['data'] = $files_array;*/

            //foreach($discussionpost->comments()->orderBy('created_at', 'desc')->get() as $comment)
            foreach(Comment::where('discussionpost_id',$discussionpost->id)->orderBy('created_at', 'desc')->get() as $comment)
            {
                $comment_obj = [
                    'type' => 'comment',
                    'id' => $comment->id,
                ];
                array_push($comments_array, $comment_obj);

            }
            $response['data']['relationships']['comments']['data'] = $comments_array;

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
        try
        {
            $discussionpost = DiscussionPost::findOrFail($id);
            $discussionpost->delete();
            return response()->json(['meta' => []],200);
        }
        catch (ModelNotFoundException $e)
        {
            return response()->json(null,404);
        }
    }
}
