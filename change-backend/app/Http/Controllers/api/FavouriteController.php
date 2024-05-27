<?php

namespace App\Http\Controllers\api;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use App\Models\Volunteer_work;
use App\Models\Favourite;

class FavouriteController extends Controller
{
    public function add($id)
    {
        $user = Auth::id();
        $volunteer = Volunteer_work::findOrFail($id);
        // Check if the user has already added the volunteer to their favorites
        $existingFavorite = Favourite::where('user_id', $user)
                                       ->where('volunteer_work_id', $volunteer->id)
                                       ->first();
        if ($existingFavorite) {
            // Return a message indicating that the volunteer is already in the user's favorites
            return response()->json(['message' => 'Volunteer already added to favorites'], 200);
        }
        // Add the volunteer to the user's favorites
        $favorite = new Favourite();
        $favorite->user_id = $user;
        $favorite->volunteer_work_id = $volunteer->id;
        $favorite->save();
        // Return a success message
        return response()->json(['message' => 'Volunteer added to favorites'], 201);
    }
    public function delete($id)
    {
        $user = Auth::id();
        $volunteer = Volunteer_work::findOrFail($id);
        // Check if the user has already added the volunteer to their favorites
        $existingFavorite = Favourite::where('user_id', $user)
                                    ->where('volunteer_work_id', $volunteer->id)
                                    ->first();
        if (!$existingFavorite) {
            // Return a message indicating that the volunteer is not in the user's favorites
            return response()->json(['message' => 'Volunteer not in favorites'], 404);
        }
        // Remove the volunteer from the user's favorites
        $existingFavorite->delete();
        // Return a success message
        return response()->json(['message' => 'Volunteer removed from favorites'], 200);

    }
}
