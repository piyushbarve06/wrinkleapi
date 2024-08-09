<?php

namespace App\Http\Controllers\v1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Settings;
use App\Models\Stores;
use App\Models\Otp;
use Carbon\Carbon;
use Illuminate\Hashing\BcryptHasher;
use Illuminate\Support\Facades\Mail;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.',
                $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 404);
        }
        $user = User::where('email', $request->email)->first();

        if (!$user)
            return response()->json(['error' => 'User not found.'], 500);

        // Account Validation
        if (!(new BcryptHasher)->check($request->input('password'), $user->password)) {

            return response()->json(['error' => 'Email or password is incorrect. Authentication failed.'], 401);
        }

        // Login Attempt
        $credentials = $request->only('email', 'password');

        try {

            JWTAuth::factory()->setTTL(40320); // Expired Time 28days

            if (!$token = JWTAuth::attempt($credentials, ['exp' => Carbon::now()->addDays(28)->timestamp])) {

                return response()->json(['error' => 'invalid_credentials'], 401);

            }
        } catch (JWTException $e) {

            return response()->json(['error' => 'could_not_create_token'], 500);

        }
        if ($user->type == "freelancer") {
            $store = Stores::where('uid', $user->id)->first();
            return response()->json(['user' => $user, 'store' => $store, 'token' => $token, 'status' => 200], 200);
        } else {
            return response()->json(['user' => $user, 'token' => $token, 'status' => 200], 200);
        }
        // return response()->json(['user' => $user,'token'=>$token,'status'=>200], 200);
    }

    public function store_login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.',
                $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 404);
        }
        $user = User::where('email', $request->email)->first();
        if (!$user)
            return response()->json(['error' => 'User not found.'], 500);
        if (!(new BcryptHasher)->check($request->input('password'), $user->password)) {
            return response()->json(['error' => 'Email or password is incorrect. Authentication failed.'], 401);
        }
        $credentials = $request->only('email', 'password');
        try {
            JWTAuth::factory()->setTTL(40320); // Expired Time 28days
            if (!$token = JWTAuth::attempt($credentials, ['exp' => Carbon::now()->addDays(28)->timestamp])) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
        if ($user->type == "freelancer") {
            $store = Stores::where('uid', $user->id)->first();
            return response()->json(['user' => $user, 'store' => $store, 'token' => $token, 'status' => 200], 200);
        } else {
            return response()->json(['user' => $user, 'token' => $token, 'status' => 200], 200);
        }

    }

    public function admins()
    {
        $data = User::where(['type' => '0'])->get();
        if (is_null($data)) {
            $response = [
                'success' => false,
                'message' => 'Data not found.',
                'status' => 404
            ];
            return response()->json($response, 404);
        }

        $response = [
            'data' => $data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.',
                $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 404);
        }
        $data = User::find($request->id)->update($request->all());
        if (is_null($data)) {
            $response = [
                'success' => false,
                'message' => 'Data not found.',
                'status' => 404
            ];
            return response()->json($response, 404);
        }
        $response = [
            'data' => $data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function sendVerificationOnMail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'country_code' => 'required',
            'mobile' => 'required'
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.',
                $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 404);
        }

        $data = User::where('email', $request->email)->first();
        $matchThese = ['country_code' => $request->country_code, 'mobile' => $request->mobile];
        $data2 = User::where($matchThese)->first();
        if (is_null($data) && is_null($data2)) {
            $settings = Settings::take(1)->first();
            $generalInfo = Settings::take(1)->first();
            $mail = $request->email;
            $username = $request->email;
            $subject = $request->subject;
            $otp = random_int(100000, 999999);
            $savedOTP = Otp::create([
                'otp' => $otp,
                'email' => $request->email,
                'status' => 0,
            ]);
            $mailTo = $mail;
            // $mailTo = Mail::send(
            //     'mails/register',
            //     [
            //         'app_name' => $generalInfo->name,
            //         'otp' => $otp
            //     ]
            //     ,
            //     function ($message) use ($mail, $username, $subject, $generalInfo) {
            //         $message->to($mail, $username)
            //             ->subject($subject);
            //         $message->from($generalInfo->email, $generalInfo->name);
            //     }
            // );

            $response = [
                'data' => true,
                'mail' => $mailTo,
                'otp_id' => $savedOTP->id,
                'success' => true,
                'status' => 200,
            ];
            return response()->json($response, 200);
        }

        $response = [
            'data' => false,
            'message' => 'email or mobile is already registered',
            'status' => 500
        ];
        return response()->json($response, 200);
    }

    public function sendRegisterEmail(Request $request)
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
            return response()->json($response, 404);
        }

        $data = User::where('email', $request->email)->first();
        if (is_null($data)) {
            $settings = Settings::take(1)->first();
            $generalInfo = Settings::take(1)->first();
            $mail = $request->email;
            $username = $request->email;
            $subject = $request->subject;
            $otp = random_int(100000, 999999);
            $savedOTP = Otp::create([
                'otp' => $otp,
                'email' => $request->email,
                'status' => 0,
            ]);
            $mailTo = Mail::send(
                'mails/register',
                [
                    'app_name' => $generalInfo->name,
                    'otp' => $otp
                ]
                ,
                function ($message) use ($mail, $username, $subject, $generalInfo) {
                    $message->to($mail, $username)
                        ->subject($subject);
                    $message->from($generalInfo->email, $generalInfo->name);
                }
            );

            $response = [
                'data' => true,
                'mail' => $mailTo,
                'otp_id' => $savedOTP->id,
                'success' => true,
                'status' => 200,
            ];
            return response()->json($response, 200);
        }

        $response = [
            'data' => false,
            'message' => 'email already registered',
            'status' => 500
        ];
        return response()->json($response, 200);
    }

    public function verifyPhoneSignup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'country_code' => 'required',
            'mobile' => 'required'
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.',
                $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 404);
        }

        $data = User::where('email', $request->email)->first();
        $matchThese = ['country_code' => $request->country_code, 'mobile' => $request->mobile];
        $data2 = User::where($matchThese)->first();
        if (is_null($data) && is_null($data2)) {
            // $settings = Settings::take(1)->first();
            // if ($settings->sms_name == '0') { // send with twillo
            //     $payCreds = DB::table('settings')
            //         ->select('*')->first();
            //     if (is_null($payCreds) || is_null($payCreds->sms_creds)) {
            //         $response = [
            //             'success' => false,
            //             'message' => 'sms gateway issue please contact administrator',
            //             'status' => 404
            //         ];
            //         return response()->json($response, 404);
            //     }
            //     $credsData = json_decode($payCreds->sms_creds);
            //     if (is_null($credsData) || is_null($credsData->twilloCreds) || is_null($credsData->twilloCreds->sid)) {
            //         $response = [
            //             'success' => false,
            //             'message' => 'sms gateway issue please contact administrator',
            //             'status' => 404
            //         ];
            //         return response()->json($response, 404);
            //     }

            //     $id = $credsData->twilloCreds->sid;
            //     $token = $credsData->twilloCreds->token;
            //     $url = "https://api.twilio.com/2010-04-01/Accounts/$id/Messages.json";
            //     $from = $credsData->twilloCreds->from;
            //     $to = $request->country_code . $request->mobile; // twilio trial verified number
            //     try {
            //         $otp = random_int(100000, 999999);
            //         $client = new \GuzzleHttp\Client();
            //         $response = $client->request(
            //             'POST',
            //             $url,
            //             [
            //                 'headers' =>
            //                     [
            //                         'Accept' => 'application/json',
            //                         'Content-Type' => 'application/x-www-form-urlencoded',
            //                     ],
            //                 'form_params' => [
            //                     'Body' => 'Your Verification code is : ' . $otp, //set message body
            //                     'To' => $to,
            //                     'From' => $from //we get this number from twilio
            //                 ],
            //                 'auth' => [$id, $token, 'basic']
            //             ]
            //         );
            //         $savedOTP = Otp::create([
            //             'otp' => $otp,
            //             'email' => $to,
            //             'status' => 0,
            //         ]);
            //         $response = [
            //             'data' => true,
            //             'otp_id' => $savedOTP->id,
            //             'success' => true,
            //             'status' => 200,
            //         ];
            //         return response()->json($response, 200);
            //     } catch (\Throwable $e) {
            //         echo "Error: " . $e->getMessage();
            //     }

            // } else { // send with msg91
            //     $payCreds = DB::table('settings')
            //         ->select('*')->first();
            //     if (is_null($payCreds) || is_null($payCreds->sms_creds)) {
            //         $response = [
            //             'success' => false,
            //             'message' => 'sms gateway issue please contact administrator',
            //             'status' => 404
            //         ];
            //         return response()->json($response, 404);
            //     }
            //     $credsData = json_decode($payCreds->sms_creds);
            //     if (is_null($credsData) || is_null($credsData->msg) || is_null($credsData->msg->key)) {
            //         $response = [
            //             'success' => false,
            //             'message' => 'sms gateway issue please contact administrator',
            //             'status' => 404
            //         ];
            //         return response()->json($response, 404);
            //     }
            //     $clientId = $credsData->msg->key;
            //     $smsSender = $credsData->msg->sender;
            //     $otp = random_int(100000, 999999);
            //     $client = new \GuzzleHttp\Client();
            //     $to = $request->country_code . $request->mobile;
            //     $res = $client->get('http://api.msg91.com/api/sendotp.php?authkey=' . $clientId . '&message=Your Verification code is : ' . $otp . '&mobile=' . $to . '&sender=' . $smsSender . '&otp=' . $otp);
            //     $data = json_decode($res->getBody()->getContents());
            //     $savedOTP = Otp::create([
            //         'otp' => $otp,
            //         'email' => $to,
            //         'status' => 0,
            //     ]);
            //     $response = [
            //         'data' => true,
            //         'otp_id' => $savedOTP->id,
            //         'success' => true,
            //         'status' => 200,
            //     ];
            //     return response()->json($response, 200);
            // }
            $response = [
                'data' => true,
                'otp_id' => 22,
                'success' => true,
                'status' => 200,
            ];
        }

        $response = [
            'data' => true,
            'otp_id' => 22,
            'success' => true,
            'status' => 200,
        ];

        // $response = [
        //     'data' => false,
        //     'message' => 'email or mobile is already registered',
        //     'status' => 500
        // ];
        return response()->json($response, 200);
    }

    public function sendVerifyOTPMobile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_code' => 'required',
            'mobile' => 'required'
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.',
                $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 404);
        }


        $matchThese = ['country_code' => $request->country_code, 'mobile' => $request->mobile];
        $data2 = User::where($matchThese)->first();
        if (is_null($data2)) {
            $settings = Settings::take(1)->first();
            if ($settings->sms_name == '0') { // send with twillo
                $payCreds = DB::table('settings')
                    ->select('*')->first();
                if (is_null($payCreds) || is_null($payCreds->sms_creds)) {
                    $response = [
                        'success' => false,
                        'message' => 'sms gateway issue please contact administrator',
                        'status' => 404
                    ];
                    return response()->json($response, 404);
                }
                $credsData = json_decode($payCreds->sms_creds);
                if (is_null($credsData) || is_null($credsData->twilloCreds) || is_null($credsData->twilloCreds->sid)) {
                    $response = [
                        'success' => false,
                        'message' => 'sms gateway issue please contact administrator',
                        'status' => 404
                    ];
                    return response()->json($response, 404);
                }

                $id = $credsData->twilloCreds->sid;
                $token = $credsData->twilloCreds->token;
                $url = "https://api.twilio.com/2010-04-01/Accounts/$id/Messages.json";
                $from = $credsData->twilloCreds->from;
                $to = $request->country_code . $request->mobile; // twilio trial verified number
                try {
                    $otp = random_int(100000, 999999);
                    $client = new \GuzzleHttp\Client();
                    $response = $client->request(
                        'POST',
                        $url,
                        [
                            'headers' =>
                                [
                                    'Accept' => 'application/json',
                                    'Content-Type' => 'application/x-www-form-urlencoded',
                                ],
                            'form_params' => [
                                'Body' => 'Your Verification code is : ' . $otp, //set message body
                                'To' => $to,
                                'From' => $from //we get this number from twilio
                            ],
                            'auth' => [$id, $token, 'basic']
                        ]
                    );
                    $savedOTP = Otp::create([
                        'otp' => $otp,
                        'email' => $to,
                        'status' => 0,
                    ]);
                    $response = [
                        'data' => true,
                        'otp_id' => $savedOTP->id,
                        'success' => true,
                        'status' => 200,
                    ];
                    return response()->json($response, 200);
                } catch (\Throwable $e) {
                    echo "Error: " . $e->getMessage();
                }

            } else { // send with msg91
                $payCreds = DB::table('settings')
                    ->select('*')->first();
                if (is_null($payCreds) || is_null($payCreds->sms_creds)) {
                    $response = [
                        'success' => false,
                        'message' => 'sms gateway issue please contact administrator',
                        'status' => 404
                    ];
                    return response()->json($response, 404);
                }
                $credsData = json_decode($payCreds->sms_creds);
                if (is_null($credsData) || is_null($credsData->msg) || is_null($credsData->msg->key)) {
                    $response = [
                        'success' => false,
                        'message' => 'sms gateway issue please contact administrator',
                        'status' => 404
                    ];
                    return response()->json($response, 404);
                }
                $clientId = $credsData->msg->key;
                $smsSender = $credsData->msg->sender;
                $otp = random_int(100000, 999999);
                $client = new \GuzzleHttp\Client();
                $to = $request->country_code . $request->mobile;
                $res = $client->get('http://api.msg91.com/api/sendotp.php?authkey=' . $clientId . '&message=Your Verification code is : ' . $otp . '&mobile=' . $to . '&sender=' . $smsSender . '&otp=' . $otp);
                $data = json_decode($res->getBody()->getContents());
                $savedOTP = Otp::create([
                    'otp' => $otp,
                    'email' => $to,
                    'status' => 0,
                ]);
                $response = [
                    'data' => true,
                    'otp_id' => $savedOTP->id,
                    'success' => true,
                    'status' => 200,
                ];
                return response()->json($response, 200);
            }
        }

        $response = [
            'data' => false,
            'message' => 'mobile is already registered',
            'status' => 500
        ];
        return response()->json($response, 200);
    }

    public function firebaseauth(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.',
                $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 404);
        }
        $url = url('/api/v1/success_verified');
        return view('fireauth', ['mobile' => $request->mobile, 'redirect' => $url]);
    }

    public function success_verified()
    {
        return view('verified');
    }

    public function verifyPhoneForFirebaseRegistrations(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'country_code' => 'required',
            'mobile' => 'required'
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.',
                $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 404);
        }

        $data = User::where('email', $request->email)->first();
        $matchThese = ['country_code' => $request->country_code, 'mobile' => $request->mobile];
        $data2 = User::where($matchThese)->first();
        if (is_null($data) && is_null($data2)) {
            $response = [
                'data' => true,
                'success' => true,
                'status' => 200,
            ];
            return response()->json($response, 200);
        }

        $response = [
            'data' => false,
            'message' => 'email or mobile is already registered',
            'status' => 500
        ];
        return response()->json($response, 200);
    }

    public function sendRegisterMobile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_code' => 'required',
            'mobile' => 'required'
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.',
                $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 404);
        }

        $matchThese = ['country_code' => $request->country_code, 'mobile' => $request->mobile];
        $data2 = User::where($matchThese)->first();
        if (is_null($data2)) {
            $response = [
                'data' => true,
                'success' => true,
                'status' => 200,
            ];
            return response()->json($response, 200);
        }

        $response = [
            'data' => false,
            'message' => 'mobile is already registered',
            'status' => 500
        ];
        return response()->json($response, 200);
    }

    public function loginWithPhonePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required',
            'country_code' => 'required',
            'password' => 'required'
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.',
                $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 404);
        }
        $matchThese = ['country_code' => $request->country_code, 'mobile' => $request->mobile];

        $user = User::where($matchThese)->first();

        if (!$user)
            return response()->json(['error' => 'User not found.'], 500);

        // Account Validation
        if (!(new BcryptHasher)->check($request->input('password'), $user->password)) {

            return response()->json(['error' => 'Phone Number or password is incorrect. Authentication failed.'], 401);
        }

        // Login Attempt
        $credentials = $request->only('country_code', 'mobile', 'password');

        try {

            JWTAuth::factory()->setTTL(40320); // Expired Time 28days

            if (!$token = JWTAuth::attempt($credentials, ['exp' => Carbon::now()->addDays(28)->timestamp])) {

                return response()->json(['error' => 'invalid_credentials'], 401);

            }
        } catch (JWTException $e) {

            return response()->json(['error' => 'could_not_create_token'], 500);

        }
        if ($user->type == "freelancer") {
            $store = Stores::where('uid', $user->id)->first();
            return response()->json(['user' => $user, 'store' => $store, 'token' => $token, 'status' => 200], 200);
        } else {
            return response()->json(['user' => $user, 'token' => $token, 'status' => 200], 200);
        }
        // return response()->json(['user' => $user,'token'=>$token,'status'=>200], 200);
    }

    public function loginWithMobileOtp(Request $request)
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
            return response()->json($response, 404);
        }
        $matchThese = ['country_code' => $request->country_code, 'mobile' => $request->mobile];

        $user = User::where($matchThese)->first();

        if (!$user)
            return response()->json(['error' => 'User not found.'], 500);

        try {

            JWTAuth::factory()->setTTL(40320); // Expired Time 28days

            if (!$token = JWTAuth::fromUser($user, ['exp' => Carbon::now()->addDays(28)->timestamp])) {

                return response()->json(['error' => 'invalid_credentials'], 401);

            }
        } catch (JWTException $e) {

            return response()->json(['error' => 'could_not_create_token'], 500);

        }
        if ($user->type == "freelancer") {
            $store = Stores::where('uid', $user->id)->first();
            return response()->json(['user' => $user, 'store' => $store, 'token' => $token, 'status' => 200], 200);
        } else {
            return response()->json(['user' => $user, 'token' => $token, 'status' => 200], 200);
        }
        // return response()->json(['user' => $user,'token'=>$token,'status'=>200], 200);
    }

    public function verifyPhoneForFirebase(Request $request)
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
            return response()->json($response, 404);
        }
        $matchThese = ['country_code' => $request->country_code, 'mobile' => $request->mobile];

        $user = User::where($matchThese)->first();

        if (!$user)
            return response()->json(['data' => false, 'error' => 'User not found.'], 500);
        $response = [
            'data' => true,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function verifyEmailForReset(Request $request)
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
            return response()->json($response, 404);
        }
        $matchThese = ['email' => $request->email];

        $user = User::where($matchThese)->first();

        if (!$user)
            return response()->json(['data' => false, 'error' => 'User not found.'], 500);

        $settings = Settings::take(1)->first();
        $mail = $request->email;
        $username = $request->email;
        $subject = 'Reset Password';
        $otp = random_int(100000, 999999);
        $savedOTP = Otp::create([
            'otp' => $otp,
            'email' => $request->email,
            'status' => 0,
        ]);
        $mailTo = Mail::send(
            'mails/reset',
            [
                'app_name' => $settings->name,
                'otp' => $otp
            ]
            ,
            function ($message) use ($mail, $username, $subject, $settings) {
                $message->to($mail, $username)
                    ->subject($subject);
                $message->from($settings->email, $settings->name);
            }
        );

        $response = [
            'data' => true,
            'mail' => $mailTo,
            'otp_id' => $savedOTP->id,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);

    }

    public function updateUserPasswordWithEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.',
                $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 404);
        }

        $match = ['email' => $request->email, 'id' => $request->id];
        $data = Otp::where($match)->first();
        if (is_null($data)) {
            $response = [
                'success' => false,
                'message' => 'Data not found.',
                'status' => 404
            ];
            return response()->json($response, 404);
        }

        $updates = User::where('email', $request->email)->first();
        $updates->update(['password' => Hash::make($request->password)]);

        if (is_null($updates)) {
            $response = [
                'success' => false,
                'message' => 'Data not found.',
                'status' => 404
            ];
            return response()->json($response, 404);
        }

        $response = [
            'data' => true,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }
}
