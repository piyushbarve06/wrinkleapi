<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Services;
use App\Models\Categories;
use App\Models\SubCategories;
use Validator;
use DB;

class ServicesController extends Controller
{
    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required',
            'cate_id' => 'required',
            'sub_cate' => 'required',
            'name' => 'required',
            'cover' => 'required',
            'original_price' => 'required',
            'sell_price' => 'required',
            'discount' => 'required',
            'variations' => 'required',
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

        $data = Services::create($request->all());
        if (is_null($data)) {
            $response = [
                'data' => $data,
                'message' => 'error',
                'status' => 500,
            ];
            return response()->json($response, 200);
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

        $data = Services::find($request->id);
        $data->category = Categories::find($data->cate_id);
        $data->sub_category = SubCategories::find($data->sub_cate);
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
        $data = Services::find($request->id)->update($request->all());

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
        $data = Services::find($request->id);
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
        $data = Services::orderBy('id', 'desc')->get();
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

    public function getActive(Request $request)
    {
        $data = Services::where('status', 1)->orderBy('id', 'desc')->get();
        $response = [
            'data' => $data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getListItems(Request $request)
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
        $data = DB::table('services')
            ->select('services.*', 'sub_categories.name as sub_cate_name')
            ->where(['services.cate_id' => $request->id, 'services.store_id' => $request->uid])
            ->join('sub_categories', 'services.sub_cate', 'sub_categories.id')
            ->get();
        // $data = Services::where(['cate_id'=>$request->id,'store_id'=>$request->uid])->get();
        $response = [
            'data' => $data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getStoreService(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'uid' => 'required',
            'cate_id' => 'required',
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
        // $data = Services::where(['sub_cate'=>$request->id,'store_id'=>$request->uid,"status"=>1,"cate_id"=>$request->cate_id])->get();
        $data = DB::table('services')
            ->select('services.*', 'categories.name as cate_name', 'sub_categories.name as sub_cate_name')
            ->join('categories', 'services.cate_id', 'categories.id')
            ->join('sub_categories', 'services.sub_cate', 'sub_categories.id')
            ->where(['services.sub_cate' => $request->id, 'services.store_id' => $request->uid, "services.status" => 1, "services.cate_id" => $request->cate_id])
            ->get();
        $response = [
            'data' => $data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    public function getAllServices(Request $request)
    {
        $data = DB::table('services')
            ->select('services.*', 'sub_categories.name as sub_cate_name', 'categories.name as cate_name', 'stores.name as store_name')
            ->join('sub_categories', 'services.sub_cate', 'sub_categories.id')
            ->join('categories', 'services.cate_id', 'categories.id')
            ->join('stores', 'services.store_id', 'stores.uid')
            ->get();
        $response = [
            'data' => $data,
            'success' => true,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }
}
