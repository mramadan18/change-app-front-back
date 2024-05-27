<?php

namespace App\Http\Controllers\api;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\CategoryOfUser;
use Illuminate\Support\Facades\Validator;

class CategoryOfUserController extends Controller
{
    public function store(Request $request)
    {
        // Get the authenticated user's ID
        $user_id = Auth::id();
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id',
        ]);
        // If the validation fails, return a 400 response with the validation errors
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        // Get the selected category IDs
        $category_ids = $request->input('category_ids');
        // Delete any existing category associations for the user
        CategoryOfUser::where('user_id', $user_id)->delete();
        // Create a new category association for each selected category
        foreach ($category_ids as $category_id) {
            $categoryOfUser = new CategoryOfUser();
            $categoryOfUser->user_id = $user_id;
            $categoryOfUser->category_id = $category_id;
            $categoryOfUser->save();
        }
        // Return a 200 response with a success message
        return response()->json(['message' => 'Categories updated successfully'], 200);
    }
}
