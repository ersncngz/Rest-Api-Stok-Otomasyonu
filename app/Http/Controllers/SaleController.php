<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
use App\Models\Listsale;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SaleController extends Controller
{
 
    public function index()
    {
        return response()->json([
            "status" => "success",
            "data" => Sale::all()
        ]);
    }

  
    public function create()
    {
        
    }


    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'piece' => 'required',
            'basket_price' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => "warning",
                "message" => $validator->errors()
            ]);
        }

        $product = Product::find($request->product_id);
        if (!$product) {
            return response()->json([
                "message" => "Ürün Bulunamadı",
            ], 404);
        }

        $total_price = $request->piece * $request->basket_price;

        $sale = Sale::create([
            'product_id' => $request->product_id,
            'piece' => $request->piece,
            'basket_price' => $request->basket_price,
            'total_price' => $total_price,
        ]);

        $stock = Stock::where('product_id', $request->product_id)
            ->orderBy('stock_price', 'asc')
            ->first();
        if ($stock) {
            $new_quantity = $stock->quantity - $request->piece;
            if ($new_quantity < 0) {
                $new_quantity = 0;
            }
            $stock->quantity = $new_quantity;
            $stock->save();

            $product->stock_quantity = $product->stock_quantity - $request->piece;
            $product->save();
        }

        return response()->json([
            "status" => "success",
            "data" => $sale
        ], 201);
    }


 
    public function show($id)
    {
        if (isset($id)) {
            return response()->json([
                "status" => "success",
                "data" => Sale::findOrFail($id)
            ]);
        } else {
            return response()->json([
                "message" => "Satış Bulunamadı", 404

            ]);
        }
    }

    public function edit(Sale $sale)
    {
        
    }

 
    public function update(Request $request, Sale $sale)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'piece' => 'required',
            'basket_price' => 'required',
            'total_price' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => "warning",
                "message" => $validator->errors()
            ]);
        }

        // Varsayılan değerleri atamak için $request'teki price alanlarını kontrol ediyoruz
        $request->merge([
            'basket_price' => $request->input('basket_price', 0),
            'total_price' => $request->input('total_price', 0),
        ]);

        $product = Sale::create($request->all());
        return response()->json([
            "status" => "success"
        ], 201);
    }

 
    public function destroy(Sale $sale)
    {
        $sale->delete();
        return response()->json([
            "status" => "success",
        ]);
    }
}
