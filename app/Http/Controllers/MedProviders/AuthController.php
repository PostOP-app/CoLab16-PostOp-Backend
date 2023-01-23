<?php

namespace App\Http\Controllers\MedProviders;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Process med_provider's registration action
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

        $med_provider = new User();
        $this->store($request, $med_provider);

        $med_provider->assignRole('med_provider');

        return response([
            'status' => true,
            'message' => 'med_provider registered successfully',
            'data' => $med_provider,
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
    public function store($request, $med_provider)
    {
        $med_provider->first_name = ucfirst($request->first_name);
        $med_provider->last_name = ucfirst($request->last_name);
        $med_provider->email = $request->email;
        $med_provider->password = Hash::make($request->password);

        $med_provider->save();
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

        $med_provider = User::where('email', $email)->first();

        if (!$med_provider) {
            return response()->json($invalidCredentialsResponse, 401);
        }

        if (!Hash::check($password, $med_provider->password)) {
            return response()->json($invalidCredentialsResponse, 401);
        }

        $token = $med_provider->createToken('med_provider Token');

        $data = [
            'med_provider' => $med_provider,
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
