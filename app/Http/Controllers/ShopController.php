<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request) 
    {
        $size = $request->query('size') ? $request->query('size') : 12;  // for page size sorting
        $f_brands = $request->query('brands');   // for brand filter
        $f_categories = $request->query('categories');  // for category filter
        $min_price = $request->query('min') ? $request->query('min') : 1;
        $max_price = $request->query('max') ? $request->query('max') : 10000;
        $o_column = "";
        $o_order = "";
        $order = $request->query('order') ? $request->query('order') : -1; // for order by sort
        switch($order)
        {
            case 1: 
                $o_column = 'created_at';
                $o_order = 'DESC';
                break;
            case 2:
                $o_column = 'created_at';
                $o_order = 'ASC';
                break;
            case 3:
                $o_column = 'sale_price';
                $o_order = 'ASC';
                break;
            case 4:
                $o_column = 'sale_price';
                $o_order = 'DESC';
                break;
            default:
                $o_column = 'id';
                $o_order = 'DESC';
        }

        // $brands = Brand::orderBy('name', 'ASC')->get();
        // $products = Product::where(function($query) use($f_brands){
        //     $query->whereIn('brand_id', explode(',', $f_brands))->orWhereRaw("'".$f_brands."'=''");
        // })
        //                     ->orderBy($o_column, $o_order)->paginate($size);
        $products = Product::query();
        if ($f_brands) {
            $brandsArray = explode(',', $f_brands);
            $products->whereIn('brand_id', $brandsArray);
        }
        if ($f_categories) {
            $categoriesArray = explode(',', $f_categories);
            $products->whereIn('category_id', $categoriesArray);
        }
    
        // Apply price filter before paginate
        $products->where(function($query) use($min_price, $max_price){
            $query->whereBetween('regular_price', [$min_price, $max_price])
                ->orWhereBetween('sale_price', [$min_price, $max_price]);
        });

        $products = $products->orderBy($o_column, $o_order)->paginate($size);
        $brands = Brand::orderBy('name', 'ASC')->get();
        $categories = Category::orderBy('name', 'ASC')->get();

        return view('shop', compact('products', 'size', 'order', 'brands', 'f_brands', 'categories', 'f_categories', 'min_price', 'max_price'));
    }

    public function product_details($product_slug) 
    {
        $product = Product::where('slug', $product_slug)->first();
        // it fetches all records from db based on condn, then it takes first 8 
        // $rproducts = Product::where('slug', '<>', $product_slug)->get()->take(8);
        // more efficient (only return 8 records from db)
        $rproducts = Product::where('slug', '<>', $product_slug)->limit(8)->get();

        return view('details', compact('product', 'rproducts'));
    }
}
