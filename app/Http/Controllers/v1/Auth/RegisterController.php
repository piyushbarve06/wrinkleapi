<?php

namespace App\Http\Controllers\v1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ReferralCodes;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function user_register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => '',
            'first_name' => '',
            'last_name' => '',
            'mobile' => '',
            'country_code' => '',
            'password' => '',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.',
                $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 500);
        }
        $emailValidation = User::where('email', $request->email)->first();
        if (is_null($emailValidation) || !$emailValidation) {

            $matchThese = ['country_code' => $request->country_code, 'mobile' => $request->mobile];
            $data = User::where($matchThese)->first();
            if (is_null($data) || !$data) {

                $user = User::create([
                    'email' => $request->email,
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'type' => 'user',
                    'status' => 1,
                    'mobile' => $request->mobile,
                    'lat' => 0,
                    'lng' => 0,
                    'cover' => 'NA',
                    'country_code' => $request->country_code,
                    'gender' => 1,
                    'password' => Hash::make($request->password),
                    'fcm_token' => $request->fcm_token
                ]);

                $token = JWTAuth::fromUser($user);
                function clean($string)
                {
                    $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

                    return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
                }
                function generateRandomString($length = 10)
                {
                    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    $charactersLength = strlen($characters);
                    $randomString = '';
                    for ($i = 0; $i < $length; $i++) {
                        $randomString .= $characters[rand(0, $charactersLength - 1)];
                    }
                    return $randomString;
                }
                $code = generateRandomString(13);
                $code = strtoupper($code);
                ReferralCodes::create(['uid' => $user->id, 'code' => $code]);
                return response()->json(['user' => $user, 'token' => $token, 'status' => 200], 200);

            }

            $response = [
                'success' => false,
                'message' => 'Mobile is already registered.',
                'status' => 500
            ];
            return response()->json($response, 500);
        }
        $response = [
            'success' => false,
            'message' => 'Email is already taken',
            'status' => 500
        ];
        return response()->json($response, 500);
    }

    public function create_admin_account(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'mobile' => 'required',
            'country_code' => 'required',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.',
                $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 500);
        }
        $emailValidation = User::where('email', $request->email)->first();
        if (is_null($emailValidation) || !$emailValidation) {

            $matchThese = ['country_code' => $request->country_code, 'mobile' => $request->mobile];
            $data = User::where($matchThese)->first();
            if (is_null($data) || !$data) {
                $checkExistOrNot = User::where('type', '=', 'admin')->first();

                if (is_null($checkExistOrNot)) {
                    $user = User::create([
                        'email' => $request->email,
                        'first_name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'type' => 'admin',
                        'status' => 1,
                        'mobile' => $request->mobile,
                        'lat' => 0,
                        'lng' => 0,
                        'cover' => 'NA',
                        'country_code' => $request->country_code,
                        'gender' => 1,
                        'password' => Hash::make($request->password),
                    ]);

                    $token = JWTAuth::fromUser($user);
                    return response()->json(['user' => $user, 'token' => $token, 'status' => 200], 200);
                }

                $response = [
                    'success' => false,
                    'message' => 'Account already setuped',
                    'status' => 500
                ];
                return response()->json($response, 500);
            }

            $response = [
                'success' => false,
                'message' => 'Mobile is already registered.',
                'status' => 500
            ];
            return response()->json($response, 500);
        }
        $response = [
            'success' => false,
            'message' => 'Email is already taken',
            'status' => 500
        ];
        return response()->json($response, 500);
    }

    public function createFreelancerAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'mobile' => 'required',
            'country_code' => 'required',
            'password' => 'required',
            'gender' => 'required',
            'cover' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.',
                $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 500);
        }
        $emailValidation = User::where('email', $request->email)->first();
        if (is_null($emailValidation) || !$emailValidation) {

            $matchThese = ['country_code' => $request->country_code, 'mobile' => $request->mobile];
            $data = User::where($matchThese)->first();
            if (is_null($data) || !$data) {
                $user = User::create([
                    'email' => $request->email,
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'type' => 'freelancer',
                    'status' => 1,
                    'mobile' => $request->mobile,
                    'cover' => $request->cover,
                    'country_code' => $request->country_code,
                    'gender' => $request->gender,
                    'password' => Hash::make($request->password),
                ]);
                return response()->json(['user' => $user, 'status' => 200], 200);
            }
            $response = [
                'success' => false,
                'message' => 'Mobile is already registered.',
                'status' => 500
            ];
            return response()->json($response, 500);
        }
        $response = [
            'success' => false,
            'message' => 'Email is already taken',
            'status' => 500
        ];
        return response()->json($response, 500);
    }

    public function adminNewAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'mobile' => 'required',
            'country_code' => 'required',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.',
                $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 500);
        }
        $emailValidation = User::where('email', $request->email)->first();
        if (is_null($emailValidation) || !$emailValidation) {

            $matchThese = ['country_code' => $request->country_code, 'mobile' => $request->mobile];
            $data = User::where($matchThese)->first();
            if (is_null($data) || !$data) {
                $user = User::create([
                    'email' => $request->email,
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'type' => 'admin',
                    'status' => 1,
                    'mobile' => $request->mobile,
                    'lat' => 0,
                    'lng' => 0,
                    'cover' => 'NA',
                    'country_code' => $request->country_code,
                    'password' => Hash::make($request->password),
                ]);

                $token = JWTAuth::fromUser($user);
                return response()->json(['user' => $user, 'token' => $token, 'status' => 200], 200);
            }

            $response = [
                'success' => false,
                'message' => 'Mobile is already registered.',
                'status' => 500
            ];
            return response()->json($response, 500);
        }
        $response = [
            'success' => false,
            'message' => 'Email is already taken',
            'status' => 500
        ];
        return response()->json($response, 500);
    }

    public function create_driver_account(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'mobile' => 'required',
            'country_code' => 'required',
            'password' => 'required',
            'lat' => 'required',
            'lng' => 'required',
            'gender' => 'required',
            'cover' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.',
                $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 500);
        }

        $emailValidation = User::where('email', $request->email)->first();
        if (is_null($emailValidation) || !$emailValidation) {
            $matchThese = ['country_code' => $request->country_code, 'mobile' => $request->mobile];
            $data = User::where($matchThese)->first();
            if (is_null($data) || !$data) {
                $user = User::create([
                    'email' => $request->email,
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'type' => 'driver', // driver
                    'status' => 1,
                    'lat' => $request->lat,
                    'lng' => $request->lng,
                    'mobile' => $request->mobile,
                    'country_code' => $request->country_code,
                    'gender' => $request->gender,
                    'cover' => $request->cover,
                    'password' => Hash::make($request->password),
                ]);

                return response()->json(['data' => $user, 'status' => 200], 200);
            }
            $response = [
                'success' => false,
                'message' => 'Mobile is already registered.',
                'status' => 500
            ];
            return response()->json($response, 500);
        }

        $response = [
            'success' => false,
            'message' => 'Email is already taken',
            'status' => 500
        ];
        return response()->json($response, 500);
    }

    public function verifyEmailRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.',
                $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 500);
        }

        $emailValidation = User::where('email', $request->email)->first();
        if (is_null($emailValidation) || !$emailValidation) {
            $response = [
                'success' => true,
                'data' => 'ok',
                'status' => 200
            ];
            return response()->json($response, 200);
        }

        $response = [
            'success' => false,
            'message' => 'Email is already taken',
            'status' => 500
        ];
        return response()->json($response, 500);
    }

    public function verifyMobileRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required',
            'country_code' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.',
                $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 500);
        }

        $matchThese = ['country_code' => $request->country_code, 'mobile' => $request->mobile];
        $data = User::where($matchThese)->first();
        if (is_null($data) || !$data) {
            $response = [
                'success' => true,
                'data' => 'ok',
                'status' => 200
            ];
            return response()->json($response, 200);
        }

        $response = [
            'success' => false,
            'message' => 'Mobile is already registered.',
            'status' => 500
        ];
        return response()->json($response, 500);
    }
}
