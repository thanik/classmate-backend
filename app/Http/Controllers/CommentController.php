<?php

namespace App\Http\Controllers;

use App\DiscussionPost;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Comment;
use App\User;

class CommentController extends Controller
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
        $new_comment = new Comment();
        $new_comment->content = $request->input('data.attributes.content');
        $new_comment->discussionpost_id = DiscussionPost::find($request->input('data.relationships.discussionpost.data.id'))->id;
        $new_comment->user_id = User::find(strval($request->attributes->get('tokendata')['uid']))->id;
        $new_comment->save();

        $response = $this->show($new_comment->id);
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
            $comment = Comment::findOrFail($id);
            $response['data'] = [
                'type' => 'comment',
                'id' => $comment->id,
                'attributes' => [
                    'content' => $comment->content,
                    'created-at' => date('c',strtotime($comment->created_at)),
                    'updated-at' => date('c',strtotime($comment->updated_at)),
                ],

                'relationships' => [
                    'user' => ['data' => ['type' => 'user','id' => $comment->user_id]],
                    'discussionpost' => ['data' => ['type' => 'discussionpost','id' => $comment->discussionpost_id]],
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
        //
    }
}
