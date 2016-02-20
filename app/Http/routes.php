<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => ['web']], function () {
    /*Route::get('{data?}', function()
    {
        return View::make('app');
    })->where('data', '^((?!api).)*$');*/
    Route::get('/', function()
    {
       return 'eiei';
    });
});


Route::group(['middleware' => ['api'],'prefix' => 'api'], function () {
   Route::group(['prefix' => 'v0'], function() {
       Route::post('auth', 'LoginController@auth');
       Route::post('register', 'LoginController@register');
       Route::post('check_account','LoginController@checkFB');
       Route::post('list_organization','LoginController@listOrganizations');
       Route::post('refresh_token','LoginController@refreshToken');

       Route::group(['middleware' => 'auth.api.token'], function()
       {
           Route::post('join_course','CourseController@joinCourse');

           Route::post('testToken', 'LoginController@testToken');

           Route::resource('user','UserController',['except' => [
               'index','edit','create','store'
           ]]);

           Route::resource('course','CourseController',['except' => [
               'edit','create'
           ]]);

           Route::get('course/{id}/getLatestMaterial','CourseController@getLatestMaterial');
           Route::get('course/{id}/getLatestAnnouncement','CourseController@getLatestAnnouncement');
           Route::get('course/{id}/getLatestDiscussionPost','CourseController@getLatestDiscussionPost');

           Route::get('course/{id}/materials','MaterialController@getCourseMaterial');
           Route::get('course/{id}/announcements','AnnouncementController@getCourseAnnouncements');
           Route::get('course/{id}/discussionposts','DiscussionController@getCourseDiscussionPosts');

           Route::resource('material','MaterialController',['except' => [
               'edit','create'
           ]]);
           Route::resource('file','FileController',['except' => [
               'edit','create'
           ]]);
           Route::resource('announcement','AnnouncementController',['except' => [
               'edit','create'
           ]]);
           Route::resource('discussionpost','DiscussionController',['except' => [
               'edit','create'
           ]]);
           Route::resource('comment','CommentController',['except' => [
               'edit','create'
           ]]);

           Route::resource('organization','OrganizationController',['except' => [
               'edit','create'
           ]]);

           Route::get('files/avatar/{id}','FileController@getAvatarPicture');
           Route::get('files/{file_name}'.'FileController@downloadFile');
       });
   });
});
