<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = auth()->user()->products()->get();

        return response()->json($products, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'image' => 'nullable|image|max:2048',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if ($request->image) {
            $imagePath = $request->image->store('product_images', 'public');
            $imageArray = ['image' => $imagePath];
        }

        $product = auth()->user()->products()->create(array_merge(
            $request->all(),
            $imageArray ?? [],
        ));

        return response()->json(['success' => 'Product has been added successfully.'], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        if (is_null($product)) {
            return response()->json(['message' => 'Record not found'], 404);
        }

        return response()->json($product, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        if ($product->user_id == auth()->user()->id) {
            return response()->json($product, 200);
        } else {
            return response()->json(['message' => 'Record not found'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        if (is_null($product)) {
            return response()->json(['message' => 'Record not found'], 404);
        }

        $rules = [
            'image' => 'nullable|image|max:2048',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if ($request->image) {
            if ($product->image) {
                Storage::delete('public/' . $product->image);
            }

            $imagePath = $request->image->store('product_images', 'public');
            $imageArray = ['image' => $imagePath];

            $product = $product->update(array_merge(
                $request->all(),
                $imageArray ?? [],
            ));

        } else {

            $product = $product->update([
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
            ]);
        }

        return response()->json(['success' => 'Product has been updated successfully.'], 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        if (is_null($product)) {
            return response()->json(['message' => 'Record not found'], 404);
        }

        if ($product->image) {
            Storage::delete('public/' . $product->image);
        }

        $product->delete();

        return response()->json(null, 204);
    }
}
