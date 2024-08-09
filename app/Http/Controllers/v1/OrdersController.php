<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Orders;
use App\Models\User;
use App\Models\Settings;
use App\Models\Services;
use App\Models\Stores;
use App\Models\Complaints;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Carbon\Carbon;

class OrdersController extends Controller
{
    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uid' => 'required',
            'store_id' => 'required',
            'order_to' => 'required',
            'address' => 'required',
            'items' => 'required',
            'coupon_id' => 'required',
            'coupon' => 'required',
            'discount' => 'required',
            'distance_cost' => 'required',
            'total' => 'required',
            'serviceTax' => 'required',
            'grand_total' => 'required',
            'pay_method' => 'required',
            'paid' => 'required',
            'pickup_date' => 'required',
            'pickup_slot' => 'required',
            'delivery_date' => 'required',
            'delivery_slot' => 'required',
            'wallet_used' => 'required',
            'wallet_price' => 'required',
            'status' => 'required'
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

        $data = Orders::create($request->all());
        if (is_null($data)) {
            $response = [
                'data' => $data,
                'message' => 'error',
                'status' => 500,
            ];
            return response()->json($response, 200);
        }
        if ($request && $request->wallet_used == 1) {
            $redeemer = User::where('id', $request->uid)->first();
            // $redeemer->withdraw($request->wallet_price);
        }
        $response = [
            'data' => $data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getById(Request $request)
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

        $data = Orders::find($request->id);

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
        $data = Orders::find($request->id)->update($request->all());

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

    public function delete(Request $request)
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
        $data = Orders::find($request->id);
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

    public function getAll()
    {
        $data = DB::table('orders')
            ->select('orders.*', 'users.first_name as first_name', 'users.last_name as last_name', 'stores.name as store_name')
            ->join('users', 'orders.uid', 'users.id')
            ->join('stores', 'orders.store_id', 'stores.uid')
            ->orderBy('orders.id', 'desc')
            ->get();

        $response = [
            'data' => $data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getMyOrders(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'limit' => 'required'
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
        $data = DB::table('orders')->select('orders.*', 'stores.name as store_name', 'stores.cover as store_cover', 'stores.address as store_address')
            ->join('stores', 'orders.store_id', 'stores.uid')
            ->where('orders.uid', $request->id)
            ->limit($request->limit)
            ->orderBy('id', 'desc')
            ->get();
        $response = [
            'data' => $data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getStoreOrders(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'limit' => 'required'
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
        $data = DB::table('orders')->select('orders.*', 'users.first_name as first_name', 'users.last_name as last_name', 'users.cover as user_cover')
            ->join('users', 'orders.uid', 'users.id')
            ->where('orders.store_id', $request->id)
            ->limit($request->limit)
            ->orderBy('id', 'desc')
            ->get();
        $response = [
            'data' => $data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getDriverOrders(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'limit' => 'required'
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
        $data = DB::table('orders')->select('orders.*', 'user.first_name as first_name', 'user.last_name as last_name', 'user.cover as user_cover', 'store.name as store_name', 'store.cover as store_cover', 'store.address as store_address')
            ->join('users as user', 'orders.uid', 'user.id')
            ->join('stores as store', 'orders.store_id', 'store.uid')
            ->where('orders.driver_id', $request->id)
            ->limit($request->limit)
            ->orderBy('id', 'desc')
            ->get();
        $response = [
            'data' => $data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getOrderDetails(Request $request)
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
        $data = DB::table('orders')->select('orders.*', 'stores.name as store_name', 'stores.cover as store_cover', 'stores.address as store_address', 'users.mobile as store_mobile', 'users.email as store_email', 'users.fcm_token as store_fcm_token')
            ->join('stores', 'orders.store_id', 'stores.uid')
            ->join('users', 'orders.store_id', 'users.id')
            ->where('orders.id', $request->id)
            ->first();

        if ($data && $data->driver_id != 0) {
            $data->driverInfo = User::select('first_name', 'last_name', 'cover', 'mobile', 'id', 'email', 'lat', 'lng')->where('id', $data->driver_id)->first();
        }

        if ($data && $data->uid != null && $data->uid != 0) {
            $data->user = User::select('first_name', 'last_name', 'cover', 'mobile', 'id', 'email', 'fcm_token')->where('id', $data->uid)->first();
        }
        $response = [
            'data' => $data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getOrderInvoice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'token' => 'required',
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

        try {
            $headers = apache_request_headers(); //get header
            $request->headers->set('Authorization', 'Bearer ' . $request->token);// set header in request

            $user = JWTAuth::parseToken()->authenticate();

            $general = Settings::first();
            $data = DB::table('orders')->select('orders.*', 'stores.name as store_name', 'stores.cover as store_cover', 'stores.address as store_address', 'u1.mobile as store_mobile', 'u1.email as store_email', 'u2.first_name as user_first_name', 'u2.last_name as user_last_name', 'u2.mobile as user_mobile', 'u2.email as user_email')
                ->join('stores', 'orders.store_id', 'stores.uid')
                ->join('users as u1', 'orders.store_id', 'u1.id')
                ->join('users as u2', 'orders.uid', 'u2.id')
                ->where('orders.id', $request->id)
                ->first();
            $delivery_address = '';
            if ($data->order_to == 1 || $data->order_to == '1') {
                $compressed = json_decode($data->address);
                $delivery_address = $compressed->house . ' ' . $compressed->landmark . ' ' . $compressed->address . ' ' . $compressed->pincode;
            }
            $general->social = json_decode($general->social);
            $items = json_decode($data->items);
            // echo json_encode($data);
            return view('printinvoice', ['general' => $general, 'items' => $items, 'id' => $request->id, 'data' => $data, 'delivery_address' => $delivery_address]);
        } catch (TokenExpiredException $e) {

            return response()->json(['error' => 'Session Expired.', 'status_code' => 401], 401);

        } catch (TokenInvalidException $e) {

            return response()->json(['error' => 'Token invalid.', 'status_code' => 401], 401);

        } catch (JWTException $e) {

            return response()->json(['token_absent' => $e->getMessage()], 401);

        }

    }

    public function getStoreOrderDetails(Request $request)
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
        $data = DB::table('orders')->select('orders.*', 'user.first_name as first_name', 'user.last_name as last_name', 'user.cover as user_cover', 'user.email as user_email', 'user.mobile as user_mobile', 'user.fcm_token as user_fcm_token')
            ->join('users as user', 'orders.uid', 'user.id')
            ->where('orders.id', $request->id)
            ->first();


        if ($data && $data->driver_id != 0) {
            $data->driverInfo = User::select('first_name', 'last_name', 'cover', 'mobile', 'id', 'email', 'lat', 'lng', 'fcm_token')->where('id', $data->driver_id)->first();
        }
        $response = [
            'data' => $data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getDriverOrderDetails(Request $request)
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
        $data = DB::table('orders')->select('orders.*', 'user.first_name as first_name', 'user.last_name as last_name', 'user.cover as user_cover', 'user.email as user_email', 'user.mobile as user_mobile', 'user.fcm_token as user_fcm_token', 'store.fcm_token as store_fcm_token', 'store.mobile as store_mobile', 'store.email as store_email')
            ->join('users as user', 'orders.uid', 'user.id')
            ->join('users as store', 'orders.store_id', 'store.id')
            ->where('orders.id', $request->id)
            ->first();


        if ($data && $data->store_id != 0) {
            $data->storeInfo = Stores::where('uid', $data->store_id)->first();
        }
        $response = [
            'data' => $data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getStats(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'month' => 'required',
            'year' => 'required'
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

        $monthData = Orders::select(DB::raw("COUNT(*) as count"), DB::raw("DATE(created_at) as day_name"), DB::raw("DATE(created_at) as day"), DB::raw('SUM(total) AS total'))
            ->whereMonth('created_at', $request->month)
            ->whereYear('created_at', $request->year)
            ->groupBy('day_name', 'day')
            ->orderBy('day')
            ->where('store_id', $request->id)
            ->get();

        $monthResponse = [];
        foreach ($monthData as $row) {
            $monthResponse['label'][] = date('l, d', strtotime($row->day_name));
            $monthResponse['data'][] = (int) $row->count;
            $monthResponse['total'][] = (int) $row->total;
        }
        if (isset($monthData) && count($monthData) > 0) {
            $response = [
                'data' => $monthData,
                'chart' => $monthResponse,
                'success' => true,
                'status' => 200,
            ];
            return response()->json($response, 200);
        } else {
            $response = [
                'data' => [],
                'chart' => [],
                'success' => false,
                'status' => 200
            ];
            return response()->json($response, 200);
        }
    }

    public function getAllStats(Request $request)
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

        $monthData = Orders::select(DB::raw("COUNT(*) as count"), DB::raw("YEAR(created_at) as day_name"), DB::raw("YEAR(created_at) as day"), DB::raw('SUM(total) AS total'))
            ->groupBy('day_name', 'day')
            ->orderBy('day')
            ->where('store_id', $request->id)
            ->get();

        $monthResponse = [];
        foreach ($monthData as $row) {
            $monthResponse['label'][] = date('Y', strtotime($row->day_name));
            $monthResponse['data'][] = (int) $row->count;
            $monthResponse['total'][] = (int) $row->total;
        }
        if (isset($monthData) && count($monthData) > 0) {
            $response = [
                'data' => $monthData,
                'chart' => $monthResponse,
                'success' => true,
                'status' => 200,
            ];
            return response()->json($response, 200);
        } else {
            $response = [
                'data' => [],
                'chart' => [],
                'success' => false,
                'status' => 200
            ];
            return response()->json($response, 200);
        }
    }

    public function getMonthsStats(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'year' => 'required'
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

        $monthData = Orders::select(DB::raw("COUNT(*) as count"), DB::raw("MONTH(created_at) as day_name"), DB::raw("MONTH(created_at) as day"), DB::raw('SUM(total) AS total'))
            ->whereYear('created_at', $request->year)
            ->groupBy('day_name', 'day')
            ->orderBy('day')
            ->where('store_id', $request->id)
            ->get();

        $monthResponse = [];
        foreach ($monthData as $row) {
            $monthResponse['label'][] = date('F', mktime(0, 0, 0, $row->day_name, 10));
            $monthResponse['data'][] = (int) $row->count;
            $monthResponse['total'][] = (int) $row->total;
        }
        if (isset($monthData) && count($monthData) > 0) {
            $response = [
                'data' => $monthData,
                'chart' => $monthResponse,
                'success' => true,
                'status' => 200,
            ];
            return response()->json($response, 200);
        } else {
            $response = [
                'data' => [],
                'chart' => [],
                'success' => false,
                'status' => 200
            ];
            return response()->json($response, 200);
        }
    }

    public function getStoreStatsDataWithDates(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'from' => 'required',
            'to' => 'required',
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
        $from = date($request->from);
        $to = date($request->to);
        $data = Orders::whereRaw('FIND_IN_SET("' . $request->id . '",store_id)')->whereBetween('created_at', [$from, $to])->orderBy('id', 'desc')->get();
        $commission = DB::table('commission')->select('rate')->where('uid', $request->id)->first();
        $response = [
            'data' => $data,
            'commission' => $commission,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getAdminDashboard(Request $request)
    {
        $now = Carbon::now();



        $todatData = Orders::select(DB::raw("COUNT(*) as count"), DB::raw("DATE_FORMAT(created_at,'%h:%m') as day_name"), DB::raw("DATE_FORMAT(created_at,'%h:%m') as day"))
            ->whereDate('created_at', Carbon::today())
            ->groupBy('day_name', 'day')
            ->orderBy('day')
            ->get();

        $weekData = Orders::select(DB::raw("COUNT(*) as count"), DB::raw("DATE(created_at) as day_name"), DB::raw("DATE(created_at) as day"))
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->groupBy('day_name', 'day')
            ->orderBy('day')
            ->get();

        $monthData = Orders::select(DB::raw("COUNT(*) as count"), DB::raw("DATE(created_at) as day_name"), DB::raw("DATE(created_at) as day"))
            ->whereMonth('created_at', Carbon::now()->month)
            ->groupBy('day_name', 'day')
            ->orderBy('day')
            ->get();
        $monthResponse = [];
        $weekResponse = [];
        $todayResponse = [];

        foreach ($todatData as $row) {
            $todayResponse['label'][] = $row->day_name;
            $todayResponse['data'][] = (int) $row->count;
        }
        foreach ($weekData as $row) {
            $weekResponse['label'][] = $row->day_name;
            $weekResponse['data'][] = (int) $row->count;
        }

        foreach ($monthData as $row) {
            $monthResponse['label'][] = $row->day_name;
            $monthResponse['data'][] = (int) $row->count;
        }

        $todayDate = $now->format('d F');

        $weekStartDate = $now->startOfWeek()->format('d');
        $weekEndDate = $now->endOfWeek()->format('d F');

        $monthStartDate = $now->startOfMonth()->format('d');
        $monthEndDate = $now->endOfMonth()->format('d F');

        $recentOrders = DB::table('orders')
            ->select('orders.*', 'users.first_name as first_name', 'users.last_name as last_name')
            ->join('users', 'orders.uid', 'users.id')
            ->limit(10)
            ->orderBy('orders.id', 'desc')
            ->get();
        foreach ($recentOrders as $loop) {
            $store = Stores::select('name')->where('uid', $loop->store_id)->get();
            $loop->storeInfo = $store;
        }

        $complaints = Complaints::whereMonth('created_at', Carbon::now()->month)->get();

        foreach ($complaints as $loop) {
            $user = User::select('email', 'first_name', 'last_name', 'cover')->where('id', $loop->uid)->first();
            $loop->userInfo = $user;
            if ($loop && $loop->store_id && $loop->store_id != null) {
                $store = Stores::select('name', 'cover')->where('uid', $loop->store_id)->first();
                $storeUser = User::select('email', 'cover')->where('id', $loop->store_id)->first();
                $loop->storeInfo = $store;
                $loop->storeUiserInfo = $storeUser;
            }

            if ($loop && $loop->driver_id && $loop->driver_id != null) {
                $driver = User::select('email', 'first_name', 'last_name', 'cover')->where('id', $loop->driver_id)->first();
                $loop->driverInfo = $driver;
            }
            if ($loop && $loop->service_id && $loop->service_id != null) {
                $product = Services::select('name', 'cover')->where('id', $loop->service_id)->first();
                $loop->productInfo = $product;
            }

        }
        $data = [
            'today' => $todayResponse,
            'week' => $weekResponse,
            'month' => $monthResponse,
            'todayLabel' => $todayDate,
            'weekLabel' => $weekStartDate . '-' . $weekEndDate,
            'monthLabel' => $monthStartDate . '-' . $monthEndDate,
            'complaints' => $complaints,
            'users' => User::where('type', 'user')->count(),
            'stores' => User::where('type', 'freelancer')->count(),
            'orders' => Orders::count(),
            'recentOrders' => $recentOrders,
            'recentUsers' => User::where('type', 'user')->limit(10)->orderBy('id', 'desc')->get(),
            'services' => Services::count()
        ];

        $response = [
            'data' => $data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }
}
