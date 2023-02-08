<?php

namespace App\Http\Controllers\Patients;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use GetStream\StreamChat\Client as StreamChatClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
/**
 * Process patient's registration action
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

        $patient = new User();
        $this->store($request, $patient);

        $patient->assignRole('patient');

        return response([
            'status' => true,
            'message' => 'Patient registered successfully',
            'data' => $patient,
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
    public function store($request, $patient)
    {
        $patient->first_name = ucfirst($request->first_name);
        $patient->last_name = ucfirst($request->last_name);
        $patient->email = $request->email;
        $patient->password = Hash::make($request->password);

        $patient->save();
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

        $patient = User::where('email', $email)->first();

        if (!$patient) {
            return response()->json($invalidCredentialsResponse, 401);
        }

        if (!Hash::check($password, $patient->password)) {
            return response()->json($invalidCredentialsResponse, 401);
        }

        $token = $patient->createToken('Patient Token');
        $streamServerClient = new StreamChatClient(env('STREAM_API_KEY'), env('STREAM_API_SECRET'));
        $streamToken = $streamServerClient->createToken($patient->first_name . '-' . time());

        $data = [
            'patient' => $patient,
            'token' => $token->accessToken,
            'token_type' => 'Bearer',
            'token_expires' => Carbon::parse(
                $token->token->expires_at
            )->toDateTimeString(),
            'stream_token' => $streamToken,
        ];

        return response([
            'status' => true,
            'message' => 'Login Successful',
            'data' => $data,
        ], 200);
    }

    /**
     * logout patient
     * @param Request $request - Request object
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response([
            'status' => true,
            'message' => 'Logout Successful',
        ], 200);
    }
}
