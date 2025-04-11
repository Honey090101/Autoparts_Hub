<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.index');
    }

    public function brands()
    {
        $brands = Brand::orderBy('id', 'DESC')->paginate(10);
        return view('admin.brands', compact('brands'));
    }

    public function add_brand()
    {
        return view('admin.brand-add');
    }

    public function brand_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug',
            'image' => 'mimes:png,jpg,jpeg|max:2050'
        ]);

        $brand = new Brand();
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);
        
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $file_extension = $image->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extension;
            $this->generateBrandThumbnailImage($image, $file_name);
            $brand->image = $file_name;
        }

        $brand->save();
        return redirect()->route('admin.brands')->with('status', 'Brand has been added successfully!');
    }

    public function brand_edit($id)
    {
        $brand = Brand::find($id);
        return view('admin.brand-edit', compact('brand'));
    }

    public function brand_update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,' . $request->id,
            'image' => 'mimes:png,jpg,jpeg|max:2050'
        ]);

        $brand = Brand::find($request->id);
        
        // Check if brand exists
        if (!$brand) {
            return redirect()->route('admin.brands')->with('error', 'Brand not found!');
        }
        
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->slug);
        
        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($brand->image && File::exists(public_path('uploads/brands/' . $brand->image))) {
                File::delete(public_path('uploads/brands/' . $brand->image));
            }
            
            $image = $request->file('image');
            $file_extension = $image->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extension;
            $this->generateBrandThumbnailImage($image, $file_name);
            $brand->image = $file_name; // Assign new image name
        }

        $brand->save();
        return redirect()->route('admin.brands')->with('status', 'Brand has been updated successfully!');
    }

    public function generateBrandThumbnailImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/brands');

        // Ensure the destination path exists
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }

        // Create an image instance
        $manager = new ImageManager(new Driver());
        $img = $manager->read($image->getPathname());
        $img->resize(124, 124, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        $img->save($destinationPath . '/' . $imageName);
    }

    public function brand_delete($id)
    {
        $brand = Brand::find($id);
        if (File::exists(public_path('uploads/brands/' . $brand->image))) {
            File::delete(public_path('uploads/brands/' . $brand->image));
        }
        $brand->delete();
        return redirect()->route('admin.brands')->with('status', 'Brand has been deleted successfully!');
    }

    public function categories()
    {
        $categories = Category::orderBy('id', 'DESC')->paginate(10);
        return view('admin.categories', compact('categories'));
    }

    public function add_category()
    {
        return view('admin.category-add');
    }

    public function category_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug',
            'image' => 'mimes:png,jpg,jpeg|max:2050'
        ]);

        $category = new Category();
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $file_extension = $image->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extension;
            $this->generateCategoryThumbnailImage($image, $file_name);
            $category->image = $file_name;
        }

        $category->save();
        return redirect()->route('admin.categories')->with('status', 'Category has been added successfully!');
    }

    public function generateCategoryThumbnailImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/categories');
        
        // Ensure the destination path exists
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }

        // Create an image instance using the correct method
        $manager = new ImageManager(new Driver());
        $img = $manager->read($image->getPathname());
        $img->resize(124, 124, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        $img->save($destinationPath . '/' . $imageName);
    }

    public function category_edit($id)
    {
        $category = Category::find($id);
        return view('admin.category-edit', compact('category'));
    }

    public function category_update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,' . $request->id,
            'image' => 'mimes:png,jpg,jpeg|max:2050'
        ]);

        $category = Category::find($request->id);
        
        // Check if category exists
        if (!$category) {
            return redirect()->route('admin.categories')->with('error', 'Category not found!');
        }

        $category->name = $request->name;
        $category->slug = Str::slug($request->slug);
        
        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($category->image && File::exists(public_path('uploads/categories/' . $category->image))) {
                File::delete(public_path('uploads/categories/' . $category->image));
            }
            
            $image = $request->file('image');
            $file_extension = $image->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extension;
            $this->generateCategoryThumbnailImage($image, $file_name);
            $category->image = $file_name; // Assign new image name
        }

        $category->save();
        return redirect()->route('admin.categories')->with('status', 'Category has been updated successfully!');
    }

    public function category_delete($id)
    {
        $category = Category::find($id);
        if (File::exists(public_path('uploads/categories/' . $category->image))) {
            File::delete(public_path('uploads/categories/' . $category->image));
        }
        $category->delete();
        return redirect()->route('admin.categories')->with('status', 'Category has been deleted successfully!');
    }

    public function products()
    {
        $products = Product::orderBy('created_at', 'DESC')->paginate(10);
        return view('admin.products', compact('products'));
    }

    public function product_add()
{
    $categories = Category::select('id', 'name')->orderBy('name')->get();
    $brands = Brand::select('id', 'name')->orderBy('name')->get();
    return view('admin.product-add', compact('categories', 'brands'));
}

    public function product_store(Request $request)
{
    $request->validate([
        'name' => 'required',
        'slug' => 'required|unique:products,slug',
        'short_description' => 'required',
        'description' => 'required',
        'regular_price' => 'required|numeric',
        'sale_price' => 'required|numeric',
        'SKU' => 'required',
        'stock_status' => 'required',
        'featured' => 'required',
        'quantity' => 'required|integer',
        'image' => 'required|mimes:png,jpg,jpeg|max:2050',
        'category_id' => 'required',
        'brand_id' => 'required'
    ]);

    $product = new Product();
    $product->name = $request->name;
    $product->slug = Str::slug($request->name);
    $product->short_description = $request->short_description;
    $product->description = $request->description;
    $product->regular_price = $request->regular_price;
    $product->sale_price = $request->sale_price;
    $product->SKU = $request->SKU;
    $product->stock_status = $request->stock_status;
    $product->featured = $request->featured;
    $product->quantity = $request->quantity;
    $product->category_id = $request->category_id;
    $product->brand_id = $request->brand_id;

    $current_timestamp = Carbon::now()->timestamp;

    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $imageName = $current_timestamp . '.' . $image->extension();
        $this->GenerateProductThumbnailImage($image, $imageName);
        $product->image = $imageName;
    }

    $gallery_arr = [];
    $gallery_images = "";
    $counter = 1;

    if ($request->hasFile('images')) {
        $allowedfileExtension = ['jpg', 'png', 'jpeg'];
        $files = $request->file('images');
        foreach ($files as $file) {
            $gextension = $file->getClientOriginalExtension();
            if (in_array($gextension, $allowedfileExtension)) {
                $gfileName = $current_timestamp . "-" . $counter . "." . $gextension;
                $this->GenerateProductThumbnailImage($file, $gfileName);
                $gallery_arr[] = $gfileName;
                $counter++;
            }
        }
        $gallery_images = implode(',', $gallery_arr);
    }
    $product->images = $gallery_images;
    $product->save();
    return redirect()->route('admin.products')->with('status', 'Product has been added successfully!');
}
public function GenerateProductThumbnailImage($image, $imageName)
{
    $destinationPath = public_path('uploads/products');
    
    // Ensure the destination path exists
    if (!File::exists($destinationPath)) {
        File::makeDirectory($destinationPath, 0755, true);
    }

    // Create an image instance
    $manager = new ImageManager(new Driver());
    $img = $manager->read($image->getPathname());
    $img->resize(300, 300, function ($constraint) {
        $constraint->aspectRatio();
        $constraint->upsize();
    });
    $img->save($destinationPath . '/' . $imageName);
}

public function product_edit($id)
{
    $product = Product::find($id);
    if (!$product) {
        return redirect()->route('admin.products')->with('error', 'Product not found!');
    }
    $categories = Category::select('id', 'name')->orderBy('name')->get();
    $brands = Brand::select('id', 'name')->orderBy('name')->get();
    return view('admin.product-edit', compact('product', 'categories', 'brands'));
}

public function product_update(Request $request)
{
    $request->validate([
        'name' => 'required',
        'slug' => 'required|unique:products,slug,' . $request->id,
        'short_description' => 'required',
        'description' => 'required',
        'regular_price' => 'required|numeric',
        'sale_price' => 'required|numeric',
        'SKU' => 'required',
        'stock_status' => 'required',
        'featured' => 'required',
        'quantity' => 'required|integer',
        'image' => 'mimes:png,jpg,jpeg|max:2050',
        'category_id' => 'required',
        'brand_id' => 'required'
    ]);

    $product = Product::find($request->id);
    
    if (!$product) {
        return redirect()->route('admin.products')->with('error', 'Product not found!');
    }
    
    $product->name = $request->name;
    $product->slug = Str::slug($request->slug);
    $product->short_description = $request->short_description;
    $product->description = $request->description;
    $product->regular_price = $request->regular_price;
    $product->sale_price = $request->sale_price;
    $product->SKU = $request->SKU;
    $product->stock_status = $request->stock_status;
    $product->featured = $request->featured;
    $product->quantity = $request->quantity;
    $product->category_id = $request->category_id;
    $product->brand_id = $request->brand_id;

    $current_timestamp = Carbon::now()->timestamp;

    if ($request->hasFile('image')) {
        // Delete old image if it exists
        if ($product->image && File::exists(public_path('uploads/products/' . $product->image))) {
            File::delete(public_path('uploads/products/' . $product->image));
        }
        
        $image = $request->file('image');
        $imageName = $current_timestamp . '.' . $image->extension();
        $this->GenerateProductThumbnailImage($image, $imageName);
        $product->image = $imageName;
    }

    if ($request->hasFile('images')) {
        // Delete old gallery images if they exist
        if (!empty($product->images)) {
            $oldImages = explode(',', $product->images);
            foreach ($oldImages as $oldImage) {
                if (File::exists(public_path('uploads/products/' . $oldImage))) {
                    File::delete(public_path('uploads/products/' . $oldImage));
                }
            }
        }
        
        $gallery_arr = [];
        $counter = 1;
        $allowedfileExtension = ['jpg', 'png', 'jpeg'];
        $files = $request->file('images');
        
        foreach ($files as $file) {
            $gextension = $file->getClientOriginalExtension();
            if (in_array($gextension, $allowedfileExtension)) {
                $gfileName = $current_timestamp . "-" . $counter . "." . $gextension;
                $this->GenerateProductThumbnailImage($file, $gfileName);
                $gallery_arr[] = $gfileName;
                $counter++;
            }
        }
        
        $product->images = implode(',', $gallery_arr);
    }

    $product->save();
    return redirect()->route('admin.products')->with('status', 'Product has been updated successfully!');
}

public function product_delete($id)
{
    $product = Product::find($id);
    
    if (!$product) {
        return redirect()->route('admin.products')->with('error', 'Product not found!');
    }
    
    // Delete product image
    if (File::exists(public_path('uploads/products/' . $product->image))) {
        File::delete(public_path('uploads/products/' . $product->image));
    }
    
    // Delete gallery images
    if (!empty($product->images)) {
        $galleryImages = explode(',', $product->images);
        foreach ($galleryImages as $img) {
            if (File::exists(public_path('uploads/products/' . $img))) {
                File::delete(public_path('uploads/products/' . $img));
            }
        }
    }
    
    $product->delete();
    return redirect()->route('admin.products')->with('status', 'Product has been deleted successfully!');
}
}