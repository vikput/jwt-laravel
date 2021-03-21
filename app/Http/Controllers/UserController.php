<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use JWTAuth;
use App\User;
use JWTAuthException;
use Validator;

class UserController extends Controller
{   
    private $user;
    public function __construct(User $user){
        $this->user = $user;
    }
   
    public function register(Request $request) {
        
        $response = [];

        $validator = Validator::make($request->all(), [
        	'firstname' => 'required|max:10',
        	'lastname' => 'required|max:10',
        	'mobile' => 'required|numeric',
        	'email' => 'required|email|unique:users,email',
        	'password' => 'required|size:8',
        	'age' => 'required|min:1|max:3',
        	'gender' => 'required',
        	'city' => 'required'
        ]);

        $mobile = trim($request->input('mobile'));

        $validator->after(function($validator) use ($mobile) {
            if (strlen($mobile) < 10 || strlen($mobile) > 10) {
                $validator->errors()->add('mobile', 'The mobile must be 10.');
            }
        });

        if ($validator->fails()) {
            $response['errors']['status'] = 400;
            $response['errors']['messages'] = $validator->errors()->all();
            return response()->json($response);
        }

        $user = $this->user->create([
          'firstname' => $request->get('firstname'),
          'lastname' => $request->get('lastname'),
          'mobile' => $request->get('mobile'),
          'email' => $request->get('email'),
          'password' => bcrypt($request->get('password')),
          'age' => $request->get('age'),
          'gender' => $request->get('gender'),
          'city' => $request->get('city')
        ]);

        if ($user) {
            $response['success']['status'] = 200;
            $response['success']['message'] = 'User created successfully';
        } else {
            $response['errors']['status'] = 500;
            $response['errors']['messages'] = 'Whoops, something went wrong';
        }
        return response()->json($response);
    }
    
    public function login(Request $request) {
        $credentials = $request->only('email', 'password');
        $token = null;
        $response = [];
        try {
           if (!$token = JWTAuth::attempt($credentials)) {
           	$response['errors']['status'] = 422;
            $response['errors']['messages'] = 'Invalid email or password';
            return response()->json($response);
           }
        } catch (JWTAuthException $e) {
            $response['errors']['status'] = 500;
            $response['errors']['messages'] = $e->getMessage();
            return response()->json($response);
        }
        $response['success']['status'] = 200;
        $response['success']['message'] = 'Token created successfully!';
        $response['success']['data'] = compact('token');
        return response()->json($response);
    }

    public function getAllUsers(Request $request) {
        $response = [];
        $token = $request->bearerToken();
        $user = JWTAuth::toUser($token);
        if ($user) {
        	$usersList = $this->user->fetchAll();
            $data = [];
            if ($usersList) {
                foreach ($usersList as $key => $value) {
                	$data[] = array(
                        'firstname' => $value->firstname,
                        'lastname' => $value->lastname,
                        'age' => $value->age,
                        'gender' => $value->gender,
                        'city' => $value->city
                	);
                }
                $response['success']['status'] = 200;
                $response['success']['data'] = $data;
            } else {
                $response['errors']['status'] = 500;
                $response['errors']['message'] = 'No data available.';
            }
        } else {
            $response['errors']['status'] = 500;
            $response['errors']['message'] = 'No data available.';
        }
        return response()->json($response);
    }

    public function getUserProfile(Request $request) {
    	$response = [];
        $token = $request->bearerToken();
        $user = JWTAuth::toUser($token);
        $response['success']['code'] = 200;
        $response['success']['data'] = $user;
        return response()->json($response);
    }
}