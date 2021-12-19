<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Validator;
use Illuminate\Support\Facades\Hash;
use App\Notifications\VerifyApiEmail;

class AuthController extends Controller
{
    public function register(Request $request) {
        $registrationData = $request->all();
        $validate = Validator::make($registrationData, [
            'name' => 'required|max:60',
            'email' => 'required|email:rfc,dns|unique:users',
            'password' => 'required|min:8',
            'telp'=>'required|numeric|starts_with:08|digits_between:10,12'
        ]);

        if($validate->fails())
            return response(['message' => $validate->errors()], 400);

        $registrationData['password'] = bcrypt($request->password);
        $user = User::create($registrationData)->sendApiEmailVerificationNotification();
        return response([
            'message' => 'Register Success',
            'user' => $user
        ], 200);
    }

    public function login(Request $request) {
        $loginData = $request->all();
        $validate = Validator::make($loginData, [
            'email' => 'required|email:rfc,dns',
            'password' => 'required'
        ]);

        if($validate->fails())
            return response(['message' => $validate->errors()], 400);

        if(!Auth::attempt($loginData))
            return response(['message' => 'Invalid Credentials'], 401);

        $user = Auth::user();
        if($user->email_verified_at!=null){
            $token = $user->createToken('Authenticaton Token')->accessToken;
            return response([
                'message'=>'Authenticated',
                'user'=>$user,
                'token_type'=>'Bearer',
                'access_token'=>$token
            ]);
        }else{
            return response([
                'message'=>'Please Verify Email',
            ],401);
        }
    }

    public function index() {
        $users = User::all();
        if (count($users)>0) {
            return response([
                'message' => 'Retrieve All Success',
                'data' => $users
            ], 200);
        }

        return response([
            'message' => 'Empty',
            'data' => null
        ], 400);
    }

    public function show($id) {
        $user = User::find($id);
        if (!is_null($user)>0) {
            return response([
                'message' => 'Retrieve User Success',
                'data' => $user
            ], 200);
        }

        return response([
            'message' => 'User Not Found',
            'data' => null
        ], 404);
    }

    public function destroy($id) {
        $user = User::find($id);
        if(is_null($user)) {
            return response([
                'message' => 'User Not Found',
                'data' => null
            ], 404);
        }

        if($user->delete()) {
            return response([
                'message' => 'Delete User Success',
                'data' => $user
            ], 200);
        }

        return response([
            'message' => 'Delete User Failed',
            'data' => null,
        ], 400);
    }

    public function update(Request $request, $id) {
        $user = User::find($id);
        if(is_null($user)) {
            return response([
                'message' => 'User Not Found',
                'data' => null
            ], 404);
        }

        $updateData = $request->all();
        $validate = Validator::make($updateData, [
            'name' => 'required|max:60',
            'telp' => 'required|numeric|starts_with:08|digits_between:10,12',
            'address' => 'required'
        ]);

        if($validate->fails())
            return response(['message' => $validate->errors()], 400);

        $user->name = $updateData['name'];
        $user->telp = $updateData['telp'];
        $user->address = $updateData['address'];

        if($user->save()) {
            return response([
                'message' => 'Update User Success',
                'data' => $user
            ], 200);
        }
        return response([
            'message' => 'Update User Failed',
            'data' => null,
        ], 400);
    }

    public function updatePassword(Request $request,$id){
        $user = User::find($id);
        if(is_null($user)){
            return response([
                'message'=>'User Not Found',
                'data'=>null
            ],404);
        }

        $updateData = $request->all();
        $validate = Validator::make($updateData,[
            'password'=>'required|min:8',
            'newPass'=>'required|min:8',
            'confirmPass'=>'required|min:8'
        ]);

        if($validate->fails()){
            return response(['message'=>$validate->errors()],404);
        }else{
            if((Hash::check(request('password'), Auth::user()->password))==false){
                return response([
                    'message'=>'Password doesnt exist',
                    'data'=>null,
                ],404);
            }else if($updateData['newPass'] != $updateData['confirmPass']){
                return response([
                    'message'=>'Password doesnt match',
                    'data'=>null,
                ],404);
            }else{
                $user->password = bcrypt($updateData['newPass']);
            }
        }

        if($user->save()){
            return response([
                'message'=>'Update User Success',
                'data'=>$user,
            ],200);
        }

        return response([
            'message'=>'Update User Failed',
            'data'=>null,
        ],404);
    }

    public function uploadProfilePict(Request $request, $id){
        $user = User::find($id);
        if(is_null($user)){
            return response([
                'message' => 'User not found',
                'data' => null
            ],404);
        }
        if(!$request->hasFile('image')) {
            return response([
                'message' => 'Upload Profile Picture Failed',
                'data' => null,
            ],400);
        }
        $file = $request->file('image');
        if(!$file->isValid()) {
            return response([
                'message'=> 'Upload Profile Picture Failed',
                'data'=> null,
            ],400);
        }

        $image = public_path().'/profile/';
        $file -> move($image, $file->getClientOriginalName());
        $image = '/profile/';
        $image = $image.$file->getClientOriginalName();
        $updateData = $request->all();
        Validator::make($updateData, [
            'image' => $image
        ]);
        $user->image = $image;
        if($user->save()){
            return response([
                'message' => 'Upload Profile Picture Success',
                'path' => $image,
            ],200);
        }

        return response([
            'messsage'=>'Upload Profile Picture Failed',
            'data'=>null,
        ],400);
    }

    public function logout(Request $request)
    {
        $token = $request->user()->token();
        $token->revoke();
        
        return response([
            'message' => 'Logout Success',
        ],200);
    }
}
