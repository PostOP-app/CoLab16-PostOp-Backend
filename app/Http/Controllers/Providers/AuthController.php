<?php

namespace App\Http\Controllers\Providers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Process provider's registration action
     * @param Request $request - Request object
     *
     * @return Response
     */
    public function register(Request $request)
    {
        $validate = $this->validator($request);
        if ($validate->fails()) {
            return response([
                'status' => false,
                'errors' => $validate->errors()->messages(),
            ], 400);
        }

        $provider = new User();
        $this->store($request, $provider);

        $provider->assignRole('Providers');

        return response([
            'status' => true,
            'message' => 'Provider registered successfully',
            'data' => $provider,
        ], 201);
    }

/**
 * User data validator
 * @param Request $request
 * @param array $customRules
 *
 * @return \Illuminate\Contracts\Validation\Validator
 */
    public function validator(Request $request)
    {
        return Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|unique:users|email:filter,rfc,dns|string|max:255',
            "password" => "required|string|min:8|confirmed",
        ]);
    }

/**
 * Store a newly created resource in storage.
 *
 * @param  \Illuminate\Http\Request  $request
 * @return \Illuminate\Http\Response
 */
    public function store($request, $provider)
    {
        $provider->first_name = ucfirst($request->first_name);
        $provider->last_name = ucfirst($request->last_name);
        $provider->email = $request->email;
        $provider->password = Hash::make($request->password);

        $provider->save();
    }

/**
 * Process login action
 * @param Request $request - Request object
 *
 * @return Response
 */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email:filter',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'status' => false,
                'errors' => $validator->errors()->messages(),
            ], 400);
        }

        $invalidCredentialsResponse = [
            'status' => false,
            'message' => 'Invalid Credentials',
        ];

        $email = $request->email;
        $password = $request->password;

        $provider = User::where('email', $email)->first();

        if (!$provider) {
            return response()->json($invalidCredentialsResponse, 401);
        }

        if (!Hash::check($password, $provider->password)) {
            return response()->json($invalidCredentialsResponse, 401);
        }

        $token = $provider->createToken('Provider Token');

        $data = [
            'provider' => $provider,
            'token' => $token->accessToken,
            'token_type' => 'Bearer',
            'token_expires' => Carbon::parse(
                $token->token->expires_at
            )->toDateTimeString(),
        ];

        return response([
            'status' => true,
            'message' => 'Login Successful',
            'data' => $data,
        ], 200);
    }

}
