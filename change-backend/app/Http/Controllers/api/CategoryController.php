<?php

namespace App\Http\Controllers\api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return response()->json($categories, 200);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'discription' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $category = new Category;
        $category->name = $request->name;
        $category->discription = $request->discription;
        $category->save();
        return response()->json($category, 201);

    }


    public function update(Request $request , $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'discription' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $category = Category::findOrFail($id);
        $category->name = $request->name;
        $category->discription = $request->discription;
        $category->save();
        return response()->json($category, 200);
    }


    public function destroy(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json(null, 204);
    }
}
