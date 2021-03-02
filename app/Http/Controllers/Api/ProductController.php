<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\FileHandler;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        $products = Product::latest()->paginate(10);
        return $this->showDataResponse('products', $products);
    }

    public function store(ProductRequest $request)
    {
        $only_this = $request->only('title', 'description', 'price');

        $product = Product::create($only_this);

        if ($request->hasFile('photo')) {
            info('requestphoto');
            $image = $request->file('photo');
            $image_name = FileHandler::upload($image, 'products', ['width' => Product::IMAGE_WIDTH, 'height' => Product::IMAGE_HEIGHT]);

            $product->image()->create([
                'url' => Storage::url($image_name),
                'base_path' => $image_name,
            ]);
        }

        return $this->showDataResponse('product', $product, 201, 'Product created success');
    }

    public function show(Product $product)
    {
        return $this->showDataResponse('product', $product, 200);
    }

    public function update(ProductRequest $request, Product $product)
    {
        if ($request->hasFile('photo')) {
            $image = $request->file('photo');
            FileHandler::delete(@$product->image->base_path);
            $image_name = FileHandler::upload($image, 'products', ['width' => Product::IMAGE_WIDTH, 'height' => Product::IMAGE_HEIGHT]);

            $image_data = [
                'url' => Storage::url($image_name),
                'base_path' => $image_name,
            ];

            if ($product->image) {
                $product->image->update($image_data);
            } else {
                $product->image->create($image_data);
            }
        }

        $only_this = $request->only('title', 'description', 'price');
        $product->update($only_this);

        return $this->showDataResponse('product', $product, 200, 'Product updated success');
    }

    public function destroy(Product $product)
    {
        if ($product->image) {
            FileHandler::delete(@$product->image->base_path);
        }
        $product->delete();

        return $this->successResponse('Product deleted success');
    }
}
