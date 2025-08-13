<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use Illuminate\Http\Request;
use App\Models\ProductService;
use Validator;
use Auth;

class InventoryController extends Controller
{
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required',
            'company_id' => 'required|numeric',
            'sale_price' => 'required|numeric',
            'purchase_price' => 'required|numeric',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $productService                 = new Inventory();
        $productService->name           = $request->name;
        $productService->description    = $request->description;
        $productService->sale_price     = $request->sale_price;
        $productService->purchase_price = $request->purchase_price;
        $productService->company_id = $request->company_id;
        // $productService->quantity = $request->quantity;
        $productService->created_by     = \Auth::user()->creatorId();;
        $productService->save();

        return response()->json([
            'message' => 'Inventory successfully created.',
            'data' => $productService
        ], 201);
    }
}
