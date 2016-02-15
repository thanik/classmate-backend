<?php

namespace App\Http\Controllers;

use Facebook\Facebook;
use Facebook\Exceptions;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use App\Organization;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;

use Illuminate\Validation\ValidationException;
use Validator;
use Storage;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Signer\Hmac\Sha256;

class LoginController extends Controller
{

    function auth(Request $request)
    {
        $fb = new Facebook([
            'app_id' => env('FB_APP_ID'),
            'app_secret' => env('FB_SECRET'),
            'default_graph_version' => 'v2.5',
        ]);
        if($request->has('facebook_id') && $request->has('facebook_token') && !$request->attributes->has('tokendata'))
        {
            try
            {
                $fb_response = $fb->get('/me?fields=id',$request->input('facebook_token'));
                $fb_user = $fb_response->getGraphUser();


                if($request->input('facebook_id') == $fb_user->getId())
                {
                    $user = User::where('facebook_id', $request->input('facebook_id'))->firstOrFail();

                    $signer = new Sha256();
                    $token = (new Builder())->setIssuer('https://classmateapp.xyz')// Configures the issuer (iss claim)
                    ->setAudience('https://classmateapp.xyz')// Configures the audience (aud claim)
                    //                      ->setId('4f1g23a12aa', true) // Configures the id (jti claim), replicating as a header item
                    ->setIssuedAt(time())// Configures the time that the token was issue (iat claim)
                    ->setNotBefore(time())// Configures the time that the token can be used (nbf claim)
                    ->setExpiration(time() + 10800)// Configures the expiration time of the token (exp claim)
                    ->set('uid', $user->id)// Configures a new claim, called "uid"
                    ->set('name', $user->name)
                    ->set('avatar_filename', $user->avatar_filename)
                    ->set('default_role', $user->default_role)
                    ->sign($signer, env('TOKEN_HMAC_KEY'))
                    ->getToken(); // Retrieves the generated token
                    return response()->json(['status' => 'success','token' => strval($token)], 200);
                }
                else
                {
                    return response()->json(['status' => 'failed', 'message' => 'invalid facebook id'], 400);
                }
            }
            catch(Exceptions\FacebookResponseException $e) {
                return response()->json(['status' => 'failed', 'message' => 'Graph returned an error: ' . $e->getMessage()], 422);
            }
            catch(Exceptions\FacebookSDKException $e) {
                return response()->json(['status' => 'failed', 'message' => 'Facebook SDK returned an error: ' . $e->getMessage()], 422);
            }
            catch (ModelNotFoundException $e)
            {
                return response()->json(['status' => 'failed', 'message' => 'no user found'], 422);
            }
            catch(\Exception $e)
            {
                return response()->json(['status' => 'failed', 'message' => $e->getMessage()],400);
            }
        }
        else
        {
            return response()->json(['status' => 'failed', 'message' => 'invalid request'], 400);
        }
    }

    function checkFB(Request $request)
    {
        $fb = new Facebook([
            'app_id' => env('FB_APP_ID'),
            'app_secret' => env('FB_SECRET'),
            'default_graph_version' => 'v2.5',
        ]);
        if($request->has('facebook_id') && $request->has('facebook_token') && !$request->attributes->has('tokendata'))
        {
            try
            {
                $fb_response = $fb->get('/me?fields=id',$request->input('facebook_token'));
                $fb_user = $fb_response->getGraphUser();
                if($request->input('facebook_id') == $fb_user->getId())
                {
                    $user = User::where('facebook_id', $request->input('facebook_id'))->firstOrFail();

                    return response()->json(['status' => 'success', 'hasAccount' => true], 200);
                }
                else
                {
                    return response()->json(['status' => 'failed', 'message' => 'invalid facebook id'], 422);
                }
            }
            catch(Exceptions\FacebookResponseException $e) {
                return response()->json(['status' => 'failed', 'message' => 'Graph returned an error: ' . $e->getMessage()], 422);
            }
            catch(Exceptions\FacebookSDKException $e) {
                return response()->json(['status' => 'failed', 'message' => 'Facebook SDK returned an error: ' . $e->getMessage()], 422);
            }
            catch (ModelNotFoundException $e)
            {
                return response()->json(['status' => 'success', 'hasUser' => false], 200);
            }
            catch(\Exception $e)
            {
                return response()->json(['status' => 'failed', 'message' => $e->getMessage()],400);
            }
        }
        else
        {
            return response()->json(['status' => 'failed', 'message' => 'invalid request'], 400);
        }
    }

    function refreshToken(Request $request)
    {
        if($request->has('token'))
        {
            $signer = new Sha256();
            $token = (new Parser())->parse($request->input('token'));
            $data = new ValidationData();
            $data->setIssuer('https://classmateapp.xyz');
            $data->setAudience('https://classmateapp.xyz');

            if($token->validate($data))
            {
                if($token->verify($signer,env('TOKEN_HMAC_KEY')))
                {
                    $request->attributes->add(['tokendata' => $token->getClaims()]);

                    $claimsToRemove = ['jti','iat','nbf','exp','aud','iss'];
                    $claims = array_diff_key($request->attributes->get('tokendata'), array_flip($claimsToRemove));

                    $token = new Builder();
                    $token->setIssuer('https://classmateapp.xyz'); // Configures the issuer (iss claim)
                    $token->setAudience('https://classmateapp.xyz'); // Configures the audience (aud claim)
                    //                      ->setId('4f1g23a12aa', true) // Configures the id (jti claim), replicating as a header item
                    $token->setIssuedAt(time()); // Configures the time that the token was issue (iat claim)
                    $token->setNotBefore(time()); // Configures the time that the token can be used (nbf claim)
                    $token->setExpiration(time() + 10800); // Configures the expiration time of the token (exp claim)
                    foreach($claims as $claim => $value)
                    {
                        $token->set($claim,$value);
                    }
                    $token->sign($signer,env('TOKEN_HMAC_KEY'));
                    $newtoken = $token->getToken(); // Retrieves the generated token
                    //$request->attributes->get('tokendata')
                    return response()->json(['status' => 'success','token' => strval($newtoken)], 200);
                }
                else
                {
                    return response()->json(['status' => 'unauthorized','message' => 'invalid token signature'],401);
                }
            }
            else
            {
                return response()->json(['status' => 'unauthorized','message' => 'invalid token or expired token'],401);
            }
        }
        else
        {
            return response()->json(['status' => 'unauthorized','message' => 'no token'],401);
        }
    }

    function authLegacy(Request $request)
    {

    }

    function listOrganizations(Request $request)
    {
        if($request->has('query'))
        {
            $results = Organization::where('name','like','%'.$request->input('query').'%')->orWhere('abbreviation','like','%'.$request->input('query').'%')->orderBy('name','asc')->take(5)->get();
            return response()->json(['status' => 'success','results' => $results],200);
        }
        else
        {
            return response()->json(['status' => 'failed'],400);
        }
    }


    function register(Request $request)
    {
        $fb = new Facebook([
            'app_id' => env('FB_APP_ID'),
            'app_secret' => env('FB_SECRET'),
            'default_graph_version' => 'v2.5',
        ]);

        if($request->has('facebook_id') && $request->has('facebook_token'))
        {
            try
            {
                $fb_response = $fb->get('/me?fields=id', $request->input('facebook_token'));
                $fb_user = $fb_response->getGraphUser();

                $fb_pic_response = $fb->get('/me/picture?width=640',$request->input('facebook_token'));
                $response_header = $fb_pic_response->getHeaders();

                if ($request->input('facebook_id') == $fb_user->getId())
                {

                    $validator = Validator::make($request->all(), [
                        'name' => 'required|string',
                        'email' => 'required|email|unique:users,email',
                        'facebook_id' => 'required|unique:users,facebook_id',
                        'organization_id' => 'required|exists:organizations,id',
                        'faculty_field' => 'required|string',
                        'role' => 'required',

                    ]);

                    if ($validator->fails())
                    {
                        return response()->json(['status' => 'failed', 'message' => 'Some information are incorrect. Please check your input and try again.', 'field_errors' => $validator->errors()->all()], 422);

                    }

                    /* check if there's existed student ID on this org */
                    if(Organization::find($request->input('organization_id'))->users()->where('student_id',$request->input('student_id'))->exists())
                    {
                        return response()->json(['status' => 'failed', 'message' => 'This student ID is already registered on this university/college. Please contact administrator.'], 422);
                    }

                    $new_user = new User();
                    $new_user->facebook_id = $request->input('facebook_id');
                    $new_user->facebook_token = $request->input('facebook_token');
                    $new_user->name = $request->input('name');
                    $new_user->default_organization_id = $request->input('organization_id');
                    $new_user->default_role = $request->input('role');
                    $new_user->email = $request->input('email');
                    $new_user->save();
                    $new_user->organizations()->attach($request->input('organization_id'),['role' => $request->input('role'), 'student_id' => $request->input('student_id'), 'faculty_field' => $request->input('faculty_field')]);

                    //download avatar from $response_header['Location']
                    Storage::put('avatars/'.$new_user->id.'.jpg',file_get_contents($response_header['Location']));
                    $new_user->avatar_filename = 'avatars/'.$new_user->id.'.jpg';
                    $new_user->save();

                    return response()->json(['status' => 'success'],200);
                }
            }
            catch(Exceptions\FacebookResponseException $e) {
                return response()->json(['status' => 'failed', 'message' => 'Graph returned an error: ' . $e->getMessage()], 422);
            }
            catch(Exceptions\FacebookSDKException $e) {
                return response()->json(['status' => 'failed', 'message' => 'Facebook SDK returned an error: ' . $e->getMessage()], 422);
            }
            /*catch(ValidationException $e)
            {
                return response()->json(['status' => 'failed', 'message' => 'Field Validation error: ' . ], 422);
            }*/
        }
        else
        {
            return response()->json(['status' => 'failed','message' => 'invalid request'],400);
        }
    }

    function testToken(Request $request)
    {
        $fb = new Facebook([
            'app_id' => env('FB_APP_ID'),
            'app_secret' => env('FB_SECRET'),
            'default_graph_version' => 'v2.5',
        ]);

        return response()->json(['status' => 'success','token' => $request->attributes->get('tokendata')],200);
    }
}
