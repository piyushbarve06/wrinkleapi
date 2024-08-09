<?php

namespace App\Http\Controllers\v1\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Settings;
use App\Models\Services;
use App\Models\Stores;
use Illuminate\Support\Facades\Mail;
use Artisan;
use DB;
use Validator;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class ProfileController extends Controller
{
    public function get_admin(Request $request)
    {

        $data = User::where('type', '=', 'admin')->first();

        if (is_null($data)) {
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

    public function uploadImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image:jpeg,png,jpg,gif,svg|max:2048'
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation Error.',
                $validator->errors(),
                'status' => 500
            ];
            return response()->json($response, 505);
        }
        Artisan::call('storage:link', []);
        $uploadFolder = 'images';
        $image = $request->file('image');
        $image_uploaded_path = $image->store($uploadFolder, 'public');
        $uploadedImageResponse = array(
            "image_name" => basename($image_uploaded_path),
            "mime" => $image->getClientMimeType()
        );

        $response = [
            'data' => $uploadedImageResponse,
            'success' => true,

            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getMyWalletBalance(Request $request)
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
        $data = User::find($request->id);
        $data['balance'] = $data->balance;
        $response = [
            'data' => $data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getProfile(Request $request)
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
        $data = User::find($request->id);
        $response = [
            'data' => $data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function updateProfile(Request $request)
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

    public function destroy_driver(Request $request)
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
        $data = User::find($request->id);
        if ($data) {
            $data->delete();
            $response = [
                'data' => $data,
                'success' => true,
                'status' => 200,
            ];
            return response()->json($response, 200);
        }
        $response = [
            'success' => false,
            'message' => 'Data not found.',
            'status' => 404
        ];
        return response()->json($response, 404);
    }

    public function getMyWallet(Request $request)
    {
        // $data = Auth::user();
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
        $data = User::find($request->id);
        $data['balance'] = $data->balance;

        $transactions = DB::table('transactions')
            ->select('amount', 'uuid', 'type', 'created_at', 'updated_at')
            ->where('payable_id', $request->id)
            ->get();
        $response = [
            'data' => $data,
            'transactions' => $transactions,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getAllDriver(Request $request)
    {
        $data = User::where('type', 'driver')->get();
        $response = [
            'data' => $data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function nearMeDrivers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required',
            'lng' => 'required',
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
        $searchQuery = Settings::select('allowDistance', 'searchResultKind')->first();

        if ($searchQuery->searchResultKind == 1) {
            $values = 3959; // miles
            $distanceType = 'miles';
        } else {
            $values = 6371; // km
            $distanceType = 'km';
        }
        $data = User::select(DB::raw('users.*, ( ' . $values . ' * acos( cos( radians(' . $request->lat . ') ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(' . $request->lng . ') ) + sin( radians(' . $request->lat . ') ) * sin( radians( lat ) ) ) ) AS distance'))
            ->having('distance', '<', (int) $searchQuery->allowDistance)
            ->orderBy('distance')
            ->where(['users.status' => 1, 'users.type' => 'driver'])
            ->get();
        foreach ($data as $loop) {
            $loop->distance = round($loop->distance, 2);
        }

        $response = [
            'data' => $data,
            'distanceType' => $distanceType,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getAll(Request $request)
    {
        $data = User::where('type', 'user')->orderBy('id', 'desc')->get();
        $response = [
            'data' => $data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getInfo(Request $request)
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

        $user = DB::table('users')->select('first_name', 'last_name', 'cover', 'email', 'country_code', 'mobile')->where('id', $request->id)->first();
        $address = DB::table('address')->where('uid', $request->id)->get();
        $orders = DB::table('orders')
            ->select('orders.*', 'stores.name as store_name', 'stores.cover as store_cover', 'stores.address as store_address')
            ->join('stores', 'orders.store_id', 'stores.uid')
            ->where('orders.uid', $request->id)
            ->orderBy('orders.id', 'desc')
            ->get();
        $rating = DB::table('rating')->where('uid', $request->id)->get();
        foreach ($rating as $loop) {
            if ($loop && $loop->driver_id && $loop->driver_id != 0) {
                $loop->driverInfo = User::where('id', $loop->driver_id)->select('first_name', 'last_name', 'cover', 'email', 'country_code', 'mobile')->first();
            }

            if ($loop && $loop->service_id && $loop->service_id != 0) {
                $loop->productInfo = Services::where('id', $loop->service_id)->first();
            }

            if ($loop && $loop->store_id && $loop->store_id != 0) {
                $loop->storeInfo = Stores::where('uid', $loop->store_id)->first();
            }
        }
        $data = [
            'user' => $user,
            'address' => $address,
            'orders' => $orders,
            'rating' => $rating
        ];
        $response = [
            'data' => $data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function sendNotification(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'message' => 'required',
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

            $data = DB::table('settings')
                ->select('*')->first();
            if (is_null($data)) {
                $response = [
                    'data' => false,
                    'message' => 'Data not found.',
                    'status' => 404
                ];
                return response()->json($response, 200);
            }

            $firebase = (new Factory)
                ->withServiceAccount(__DIR__ . '/../../../../../config/firebase_credentials.json');

            $messaging = $firebase->createMessaging();

            $deviceToken = $request->id;
            $message = CloudMessage::withTarget('token', $deviceToken)
                ->withNotification([
                    'title' => $request->title,
                    'body' => $request->message,
                ]);

            $messaging->send($message);
            return response()->json(['message' => 'Push notification sent successfully']);

        } catch (\Throwable $e) {
            return response()->json($e->getMessage(), 200);
        }
    }

    function generateRandomString($length = 10)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    function generateRandomNumber($length = 10)
    {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function sendNoficationGlobal(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'message' => 'required',
                'cover' => 'required'
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

            $data = DB::table('settings')
                ->select('*')->first();
            $allIds = DB::table('users')->select('fcm_token')->get();
            $fcm_ids = array();
            foreach ($allIds as $i => $i_value) {
                if ($i_value->fcm_token != 'NA' && $i_value->fcm_token != null) {
                    array_push($fcm_ids, $i_value->fcm_token);
                }
            }

            if (is_null($data)) {
                $response = [
                    'data' => false,
                    'message' => 'Data not found.',
                    'status' => 404
                ];
                return response()->json($response, 200);
            }

            $topicName = $this->generateRandomString(5) . $this->generateRandomNumber(5);
            $firebase = (new Factory)
                ->withServiceAccount(__DIR__ . '/../../../../../config/firebase_credentials.json');

            $messaging = $firebase->createMessaging();
            $messaging->subscribeToTopic($topicName, $fcm_ids);
            $message = CloudMessage::fromArray([
                'notification' => [
                    'title' => $request->title,
                    'body' => $request->message,
                ],
                'topic' => $topicName
            ]);

            $messaging->send($message);
            $messaging->unsubscribeFromTopic($topicName, $fcm_ids);
            $response = [
                'data' => '',
                'ids',
                $fcm_ids,
                'success' => true,
                'status' => 200,
            ];
            return response()->json($response, 200);

        } catch (\Throwable $e) {
            return response()->json($e->getMessage(), 200);
        }
    }

    public function sendToAllUsers(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'message' => 'required',
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

            $data = DB::table('settings')
                ->select('*')->first();
            $ids = explode(',', $request->id);
            $allIds = DB::table('users')->select('fcm_token')->get();
            $fcm_ids = array();
            foreach ($allIds as $i => $i_value) {
                if ($i_value->fcm_token != 'NA' && $i_value->fcm_token != null) {
                    array_push($fcm_ids, $i_value->fcm_token);
                }
            }

            if (is_null($data)) {
                $response = [
                    'data' => false,
                    'message' => 'Data not found.',
                    'status' => 404
                ];
                return response()->json($response, 200);
            }
            $regIdChunk = array_chunk($fcm_ids, 1000);
            foreach ($regIdChunk as $RegId) {
                $topicName = $this->generateRandomString(5) . $this->generateRandomNumber(5);
                $firebase = (new Factory)
                    ->withServiceAccount(__DIR__ . '/../../../../../config/firebase_credentials.json');

                $messaging = $firebase->createMessaging();
                $messaging->subscribeToTopic($topicName, $RegId);
                $message = CloudMessage::fromArray([
                    'notification' => [
                        'title' => $request->title,
                        'body' => $request->message,
                    ],
                    'topic' => $topicName
                ]);

                $messaging->send($message);
                $messaging->unsubscribeFromTopic($topicName, $RegId);
            }
            $response = [
                'success' => true,
                'status' => 200,
            ];
            return response()->json($response, 200);


        } catch (\Throwable $e) {
            return response()->json($e->getMessage(), 200);
        }
    }

    public function sendToUsers(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'message' => 'required',
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

            $data = DB::table('settings')
                ->select('*')->first();
            $ids = explode(',', $request->id);
            $allIds = DB::table('users')->where('type', 'user')->select('fcm_token')->get();
            $fcm_ids = array();
            foreach ($allIds as $i => $i_value) {
                if ($i_value->fcm_token != 'NA' && $i_value->fcm_token != null) {
                    array_push($fcm_ids, $i_value->fcm_token);
                }
            }


            if (is_null($data)) {
                $response = [
                    'data' => false,
                    'message' => 'Data not found.',
                    'status' => 404
                ];
                return response()->json($response, 200);
            }
            $regIdChunk = array_chunk($fcm_ids, 1000);
            foreach ($regIdChunk as $RegId) {
                $topicName = $this->generateRandomString(5) . $this->generateRandomNumber(5);
                $firebase = (new Factory)
                    ->withServiceAccount(__DIR__ . '/../../../../../config/firebase_credentials.json');

                $messaging = $firebase->createMessaging();
                $messaging->subscribeToTopic($topicName, $RegId);
                $message = CloudMessage::fromArray([
                    'notification' => [
                        'title' => $request->title,
                        'body' => $request->message,
                    ],
                    'topic' => $topicName
                ]);

                $messaging->send($message);
                $messaging->unsubscribeFromTopic($topicName, $RegId);
            }
            $response = [
                'success' => true,
                'status' => 200,
            ];
            return response()->json($response, 200);


        } catch (\Throwable $e) {
            return response()->json($e->getMessage(), 200);
        }
    }

    public function sendToStores(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'message' => 'required',
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

            $data = DB::table('settings')
                ->select('*')->first();
            $ids = explode(',', $request->id);
            $allIds = DB::table('users')->where('type', 'freelancer')->select('fcm_token')->get();
            $fcm_ids = array();
            foreach ($allIds as $i => $i_value) {
                if ($i_value->fcm_token != 'NA' && $i_value->fcm_token != null) {
                    array_push($fcm_ids, $i_value->fcm_token);
                }
            }


            if (is_null($data)) {
                $response = [
                    'data' => false,
                    'message' => 'Data not found.',
                    'status' => 404
                ];
                return response()->json($response, 200);
            }
            $regIdChunk = array_chunk($fcm_ids, 1000);
            foreach ($regIdChunk as $RegId) {
                $topicName = $this->generateRandomString(5) . $this->generateRandomNumber(5);
                $firebase = (new Factory)
                    ->withServiceAccount(__DIR__ . '/../../../../../config/firebase_credentials.json');

                $messaging = $firebase->createMessaging();
                $messaging->subscribeToTopic($topicName, $RegId);
                $message = CloudMessage::fromArray([
                    'notification' => [
                        'title' => $request->title,
                        'body' => $request->message,
                    ],
                    'topic' => $topicName
                ]);

                $messaging->send($message);
                $messaging->unsubscribeFromTopic($topicName, $RegId);
            }
            $response = [
                'success' => true,
                'status' => 200,
            ];
            return response()->json($response, 200);


        } catch (\Throwable $e) {
            return response()->json($e->getMessage(), 200);
        }
    }

    public function sendToDrivers(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'message' => 'required',
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

            $data = DB::table('settings')
                ->select('*')->first();
            $ids = explode(',', $request->id);
            $allIds = DB::table('users')->where('type', 'driver')->select('fcm_token')->get();
            $fcm_ids = array();
            foreach ($allIds as $i => $i_value) {
                if ($i_value->fcm_token != 'NA' && $i_value->fcm_token != null) {
                    array_push($fcm_ids, $i_value->fcm_token);
                }
            }

            if (is_null($data)) {
                $response = [
                    'data' => false,
                    'message' => 'Data not found.',
                    'status' => 404
                ];
                return response()->json($response, 200);
            }
            $regIdChunk = array_chunk($fcm_ids, 1000);
            foreach ($regIdChunk as $RegId) {
                $topicName = $this->generateRandomString(5) . $this->generateRandomNumber(5);
                $firebase = (new Factory)
                    ->withServiceAccount(__DIR__ . '/../../../../../config/firebase_credentials.json');

                $messaging = $firebase->createMessaging();
                $messaging->subscribeToTopic($topicName, $RegId);
                $message = CloudMessage::fromArray([
                    'notification' => [
                        'title' => $request->title,
                        'body' => $request->message,
                    ],
                    'topic' => $topicName
                ]);

                $messaging->send($message);
                $messaging->unsubscribeFromTopic($topicName, $RegId);
            }
            $response = [
                'success' => true,
                'status' => 200,
            ];
            return response()->json($response, 200);


        } catch (\Throwable $e) {
            return response()->json($e->getMessage(), 200);
        }
    }

    public function sendMailToUsers(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'subjects' => 'required',
                'content' => 'required',
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
            $users = User::select('email', 'first_name', 'last_name')->where('type', 'user')->get();
            $general = DB::table('settings')->select('name', 'email')->first();
            foreach ($users as $user) {
                Mail::send([], [], function ($message) use ($request, $user, $general) {
                    $message->to($user->email)
                        ->from($general->email, $general->name)
                        ->subject($request->subjects)
                        ->setBody($request->content, 'text/html');
                });
            }

            $response = [
                'success' => true,
                'message' => 'success',
                'status' => 200
            ];
            return $response;

        } catch (\Throwable $e) {
            return response()->json($e->getMessage(), 200);
        }
    }

    public function sendMailToAll(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'subjects' => 'required',
                'content' => 'required',
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
            $users = User::select('email', 'first_name', 'last_name')->get();
            $general = DB::table('settings')->select('name', 'email')->first();
            foreach ($users as $user) {
                Mail::send([], [], function ($message) use ($request, $user, $general) {
                    $message->to($user->email)
                        ->from($general->email, $general->name)
                        ->subject($request->subjects)
                        ->setBody($request->content, 'text/html');
                });
            }

            $response = [
                'success' => true,
                'message' => 'success',
                'status' => 200
            ];
            return $response;

        } catch (\Throwable $e) {
            return response()->json($e->getMessage(), 200);
        }
    }

    public function sendMailToStores(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'subjects' => 'required',
                'content' => 'required',
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
            $users = User::select('email', 'first_name', 'last_name')->where('type', 'freelancer')->get();
            $general = DB::table('settings')->select('name', 'email')->first();
            foreach ($users as $user) {
                Mail::send([], [], function ($message) use ($request, $user, $general) {
                    $message->to($user->email)
                        ->from($general->email, $general->name)
                        ->subject($request->subjects)
                        ->setBody($request->content, 'text/html');
                });
            }

            $response = [
                'success' => true,
                'message' => 'success',
                'status' => 200
            ];
            return $response;

        } catch (\Throwable $e) {
            return response()->json($e->getMessage(), 200);
        }
    }

    public function sendMailToDrivers(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'subjects' => 'required',
                'content' => 'required',
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
            $users = User::select('email', 'first_name', 'last_name')->where('type', 'driver')->get();
            $general = DB::table('settings')->select('name', 'email')->first();

            foreach ($users as $user) {
                Mail::send([], [], function ($message) use ($request, $user, $general) {
                    $message->to($user->email)
                        ->from($general->email, $general->name)
                        ->subject($request->subjects)
                        ->setBody($request->content, 'text/html');
                });
            }

            $response = [
                'success' => true,
                'message' => 'success',
                'status' => 200
            ];
            return $response;

        } catch (\Throwable $e) {
            return response()->json($e->getMessage(), 200);
        }
    }
}
