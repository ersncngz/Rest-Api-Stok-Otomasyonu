<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Basket;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Stock;

class BasketController extends Controller
{
   
    public function index()
    {
        //
    }

    public function create()
    {
        //
    }


    public function store(Request $request)
    {
        // Satış oluştur
        $newSale = new Sale();
        $newSale->save();
        $saleId = $newSale->id;


        $sale = Sale::find($saleId);
        if (!$sale) {
            return response()->json(['error' => 'Sale not found.'], 404);
        }

        $basketItems = (array) $request->input('basket_items');
        foreach ($basketItems as $item) {
            // Sepet kaydı oluştur
            $basket = new Basket();
            $basket->sale_id = $saleId;
            $basket->product_id = $item['product_id'];
            $basket->product_price = $item['product_price'];
            $basket->piece = $item['piece'];
            $basket->total_price = $basket->product_price * $basket->piece;
            $basket->save();

            // Ürün stoklarını güncelle
           // $product = Product::findOrFail($basket->product_id);
                Stock::where('product_id',  $item['product_id'])
                ->where('stock_price',$item['product_price'])
                ->decrement('quantity',$item['piece']);
                Product::where('id',$item['product_id'])
                ->decrement('stock_quantity',$item['piece']);
             $sale->total_price += $basket->total_price;
        }

        $sale->save();

        // Cevap döndür
        return response()->json([
            'sale' => $sale,
        ], 201);
    }

    public function show(string $id)
    {
        //
    }


    public function edit(string $id)
    {
        //
    }


    public function update(Request $request, string $id)
    {
        //
    }

 
    public function destroy(string $id)
    {
        //
    }
}
