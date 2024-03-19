<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{

    function ProductPage(){
        return view('pages.dashboard.product-page');
    }
    

    function CreateProduct(Request $request){
        $user_id=$request->header('id');

        // Prepare File Name & Path
        $img=$request->file('img');

        $t=time();
        $file_name=$img->getClientOriginalName();
        $img_name="{$user_id}-{$t}-{$file_name}";
        $img_url="uploads/{$img_name}";


        // Upload File
        $img->move(public_path('uploads'),$img_name);


        // Save To Database
        return Product::create([
            'name'=>$request->input('name'),
            'price'=>$request->input('price'),
            'unit'=>$request->input('unit'),
            'img_url'=>$img_url,
            'category_id'=>$request->input('category_id'),
            'user_id'=>$user_id
        ]);
    }


    function DeleteProduct(Request $request){
        $user_id = $request->header('id');
        $product_id = $request->input('id');
        $product = Product::find($product_id);
       
        // dd(public_path($product->img_url));
        $filePath =  public_path($product->img_url);
        if(file_exists($filePath)){
            File::delete($filePath);
        }
        // dd($filePath);
        
        return Product::where('id', $product_id)->where('user_id', $user_id)->delete();
    }


    function ProductByID(Request $request){
        $user_id = $request->header('id');
        $product_id = $request->input('id');

        return Product::where('id', $product_id)->where('user_id', $user_id)->first();
    }


    function ProductList(Request $request){
        $user_id = $request->header('id');
        return Product::where('user_id', $user_id)->get();
    }


    function UpdateProduct(Request $request){
        $user_id = $request->header('id');
        $product_id = $request->input('id');

        if ($request->hasFile('img')){
            // Upload new file
            $img = $request->file('img');
            $time = time();
            $fileName = $img->getClientOriginalName();
            $img_name = "{$user_id}-{$time}-{$fileName}";
            $img_url = "uploads/{$img_name}";

            $img->move(public_path('uploads'), $img_name);

            // Delete Database
            return Product::where('id', $product_id)->where('user_id', $user_id)->update([
                'name' => $request->input('name'),
                'price' => $request->input('price'),
                'unit' => $request->input('unit'),
                'img_url' => $img_url,
                'category_id' => $request->input('category_id')
            ]);
        }
        else{
            
            
            return Product::where('id', $product_id)->where('user_id', $user_id)->update([
                'name' => $request->input('name'),
                'price' => $request->input('price'),
                'unit' => $request->input('unit'),
                'category_id' => $request->input('category_id')
            ]);
        }
    }




}
