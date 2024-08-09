<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Stores;
use App\Models\Commission;
use App\Models\Categories;
use App\Models\User;
use App\Models\Cities;
use App\Models\Settings;
use App\Models\Favourite;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class StoresController extends Controller
{
    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uid' => 'required',
            'name' => 'required',
            'cover' => 'required',
            'categories' => 'required',
            'address' => 'required',
            'lat' => 'required',
            'lng' => 'required',
            'about' => 'required',
            'rating' => 'required',
            'total_rating' => 'required',
            'timing' => 'required',
            'images' => 'required',
            'zipcode' => 'required',
            'cid' => 'required',
            'status' => 'required',
            'in_home' => 'required',
            'popular' => 'required',
            'rate' => 'required'
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

        $data = Stores::create($request->all());
        if (is_null($data)) {
            $response = [
                'data' => $data,
                'message' => 'error',
                'status' => 500,
            ];
            return response()->json($response, 200);
        }
        Commission::create([
            'uid' => $request->uid,
            'rate' => $request->rate,
            'status' => 1,
        ]);
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

        $data = Stores::find($request->id);

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
        $data = Stores::find($request->id)->update($request->all());

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
        $data = Stores::find($request->id);
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

    public function getByUID(Request $request)
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

        $data = Stores::where('uid', $request->id)->first();
        if ($data && $data->categories && $data->categories != null) {
            $ids = explode(',', $data->categories);
            $cats = Categories::WhereIn('id', $ids)->get();
            $data->web_cates_data = $cats;
        }
        if ($data && $data->cid && $data->cid != null) {
            $data->city_data = Cities::find($data->cid);
        }
        $data->rate = Commission::where('uid', $request->id)->first();
        $data->user_info = User::find($request->id);
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

    public function getStoreInfo(Request $request)
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

        $data = Stores::where('uid', $request->id)->first();
        $user = User::where('id', $request->id)->first();
        $response = [
            'data' => $data,
            'user' => $user,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getMyProfile(Request $request)
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
        $storeInfo = Stores::where('uid', $request->id)->first();
        $userInfo = User::find($request->id);
        if ($storeInfo && $storeInfo->categories && $storeInfo->categories != null) {
            $ids = explode(',', $storeInfo->categories);
            $cats = Categories::WhereIn('id', $ids)->get();
            $storeInfo->web_cates_data = $cats;
        }
        $response = [
            'storeInfo' => $storeInfo,
            'userInfo' => $userInfo,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function updateMyProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'uid' => 'required',
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
        $data = Stores::find($request->id)->update($request->all());
        $data = User::find($request->uid)->update(['first_name' => $request->first_name, 'last_name' => $request->last_name, 'cover' => $request->cover]);
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

    public function updateInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'uid' => 'required',
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
        $data = Stores::find($request->id)->update($request->all());
        $data = User::find($request->uid)->update(['first_name' => $request->first_name, 'last_name' => $request->last_name, 'cover' => $request->cover]);
        Commission::where('uid', $request->uid)->update(['rate' => $request->rate]);
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

    public function getAll()
    {
        $data = Stores::all();
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

    public function getList(Request $request)
    {
        $data = DB::table('stores')->select(
            'stores.*',
            'users.first_name as user_first_name',
            'cities.name as city_name',
            'users.last_name as user_last_name',
            'users.cover as user_cover',
            'commission.rate as commission_rate'
        )
            ->join('users', 'stores.uid', 'users.id')
            ->join('cities', 'stores.cid', 'cities.id')
            ->join('commission', 'stores.uid', 'commission.uid')->get();

        foreach ($data as $loop) {
            if ($loop && $loop->categories && $loop->categories != null) {
                $ids = explode(',', $loop->categories);
                $cats = Categories::WhereIn('id', $ids)->get();
                $loop->web_cates_data = $cats;
            }
        }
        $response = [
            'data' => $data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getStoresList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lat' => '',
            'lng' => '',
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
        $data = Stores::where('status', 1)->orderBy('id', 'desc')->get();
        
        // $searchQuery = Settings::select('allowDistance', 'searchResultKind')->first();
        // if ($searchQuery->searchResultKind == 1) {
        //     $values = 3959; // miles
        //     $distanceType = 'miles';
        // } else {
        //     $values = 6371; // km
        //     $distanceType = 'km';
        // }
        // $ids = explode(',', $request->uid);
        // DB::enableQueryLog();
        // $data = Stores::select(DB::raw('stores.*, ( ' . $values . ' * acos( cos( radians(' . $request->lat . ') ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(' . $request->lng . ') ) + sin( radians(' . $request->lat . ') ) * sin( radians( lat ) ) ) ) AS distance'))
        //     ->having('distance', '<', (int) $searchQuery->allowDistance)
        //     ->orderBy('distance')
        //     ->where(['stores.status' => 1])
        //     ->get();

        // foreach ($data as $loop) {
        //     $loop->distance = round($loop->distance, 2);
        //     if ($loop && $loop->categories && $loop->categories != null) {
        //         $ids = explode(',', $loop->categories);
        //         $cats = Categories::select('name')->WhereIn('id', $ids)->get();
        //         $loop->web_cates_data = $cats;
        //     }

        //     if ($request->uid != 'NA') {
        //         $temp = Favourite::where(['uid' => $request->uid, 'store_uid' => $loop->uid])->first();
        //         if (isset($temp) && $temp->id) {
        //             $loop['liked'] = true;
        //         } else {
        //             $loop['liked'] = false;
        //         }
        //     } else {
        //         $loop['liked'] = false;
        //     }
        // }
        $response = [
            'data' => $data,
            'distanceType' => 10,
            'success' => true,
            'status' => 200,
            'havedata' => true,
        ];
        return response()->json($response, 200);
    }

    public function searchResult(Request $request)
    {
        $str = "";
        if ($request->has('param') && $request->has('lat') && $request->has('lng')) {
            $str = $request->param;
            $lat = $request->lat;
            $lng = $request->lng;
        }

        $searchQuery = Settings::select('allowDistance', 'searchResultKind')->first();

        if ($searchQuery->searchResultKind == 1) {
            $values = 3959; // miles
            $distanceType = 'miles';
        } else {
            $values = 6371; // km
            $distanceType = 'km';
        }
        $ids = explode(',', $request->uid);
        DB::enableQueryLog();
        $data = Stores::select(DB::raw('stores.*, ( ' . $values . ' * acos( cos( radians(' . $request->lat . ') ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(' . $request->lng . ') ) + sin( radians(' . $request->lat . ') ) * sin( radians( lat ) ) ) ) AS distance'))
            ->having('distance', '<', (int) $searchQuery->allowDistance)
            ->orderBy('distance')
            ->where('stores.name', 'like', '%' . $str . '%')
            ->where(['stores.status' => 1])
            ->get();

        foreach ($data as $loop) {
            $loop->distance = round($loop->distance, 2);
            if ($loop && $loop->categories && $loop->categories != null) {
                $ids = explode(',', $loop->categories);
                $cats = Categories::select('name')->WhereIn('id', $ids)->get();
                $loop->web_cates_data = $cats;
            }

            if ($request->uid != 'NA') {
                $temp = Favourite::where(['uid' => $request->uid, 'store_uid' => $loop->uid])->first();
                if (isset($temp) && $temp->id) {
                    $loop['liked'] = true;
                } else {
                    $loop['liked'] = false;
                }
            } else {
                $loop['liked'] = false;
            }
        }
        $response = [
            'data' => $data,
            'distanceType' => $distanceType,
            'success' => true,
            'status' => 200,
            'havedata' => true,
        ];
        return response()->json($response, 200);
    }
}
