<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Volunteer_work;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\CarriedOut;
use App\Models\Day_of_vlunteer;
use App\Models\Day_Of_user;
use App\Models\Favourite;
use App\Models\CategoryOfUser;
class UserApiController extends Controller
{

    public function register(Request $request){
        $registerUserData = $request->validate([
            'name'=>'required|string',
            'email'=>'required|string|email|unique:users',
            'password'=>'required|min:8'
        ]);
        $user = User::create([
            'name' => $registerUserData['name'],
            'email' => $registerUserData['email'],
            'password' => Hash::make($registerUserData['password']),
        ]);
        return response()->json([
            'message' => 'User Created ',
        ]);
    }



    public function login(Request $request){
        $loginUserData = $request->validate([
            'email'=>'required|string|email',
            'password'=>'required|min:8'
        ]);
        $user = User::where('email',$loginUserData['email'])->first();
        if(!$user || !Hash::check($loginUserData['password'],$user->password)){
            return response()->json([
                'message' => 'Invalid Credentials'
            ],401);
        }
        $token = $user->createToken($user->name.'-AuthToken')->plainTextToken;
        return response()->json([
            'access_token' => $token,
        ]);
    }




    public function logout(){
        auth()->user()->tokens()->delete();

        return response()->json([
          "message"=>"logged out"
        ]);
    }






    public function recipient_work()
    {
        $id=Auth::id();
        $carriedOutCount = CarriedOut::where('user_id', $id)
                                      ->select('volunteer_work_id')
                                      ->get();
        $volunteerIds = $carriedOutCount->pluck('volunteer_work_id');
        $volunteers = Volunteer_work::whereIn('id', $volunteerIds)
                                     ->with('Day_of_vlunteer')
                                     ->get()
                                     ->map(function ($volunteer) {
                                         return [
                                             'id' => $volunteer->id,
                                             'description' => $volunteer->description,
                                             'start_date' => $volunteer->start_date,
                                             'end_date' => $volunteer->end_date,
                                             'address' => $volunteer->address,
                                             'point' => $volunteer->point,
                                             'count_worker' => $volunteer->count_worker,
                                             'status' => $volunteer->status,
                                             'user_id' => $volunteer->user_id,
                                             'category_id' => $volunteer->category_id,
                                             'days' => $volunteer->Day_of_vlunteer->pluck('day_of_week')->toArray(),
                                         ];
                                     });
        return response()->json($volunteers, 200);
    }




    public function created_work()
    {
        $id=Auth::id();
        $volunteers = Volunteer_work::where('user_id', $id)
                                     ->with('Day_of_vlunteer')
                                     ->get()
                                     ->map(function ($volunteer) {
                                         return [
                                             'id' => $volunteer->id,
                                             'description' => $volunteer->description,
                                             'start_date' => $volunteer->start_date,
                                             'end_date' => $volunteer->end_date,
                                             'address' => $volunteer->address,
                                             'point' => $volunteer->point,
                                             'count_worker' => $volunteer->count_worker,
                                             'status' => $volunteer->status,
                                             'user_id' => $volunteer->user_id,
                                             'category_id' => $volunteer->category_id,
                                             'days' => $volunteer->Day_of_vlunteer->pluck('day_of_week')->toArray(),
                                         ];
                                     });
        return response()->json($volunteers, 200);
    }





    public function favourite()
    {
        $id=Auth::id();
        $carriedOutCount = Favourite::where('user_id', $id)
                                      ->select('volunteer_work_id')
                                      ->get();
        $volunteerIds = $carriedOutCount->pluck('volunteer_work_id');
        $volunteers = Volunteer_work::whereIn('id', $volunteerIds)
                                     ->with('Day_of_vlunteer')
                                     ->get()
                                     ->map(function ($volunteer) {
                                         return [
                                             'id' => $volunteer->id,
                                             'description' => $volunteer->description,
                                             'start_date' => $volunteer->start_date,
                                             'end_date' => $volunteer->end_date,
                                             'address' => $volunteer->address,
                                             'point' => $volunteer->point,
                                             'count_worker' => $volunteer->count_worker,
                                             'status' => $volunteer->status,
                                             'user_id' => $volunteer->user_id,
                                             'category_id' => $volunteer->category_id,
                                             'days' => $volunteer->Day_of_vlunteer->pluck('day_of_week')->toArray(),
                                         ];
                                     });
        return response()->json($volunteers, 200);
    }






    public function store_info(Request $request)
    {
        $user = Auth::id();
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'days' => 'required|array',
            'days.*' => 'string|in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $x=User::findOrFail($user);
        $x->phone = $request->input('phone');
        $x->address = $request->input('address');
        $x->save();
        $days = $request->input('days');
        $existingDays = Day_Of_user::where('user_id', $user)->pluck('day_of_week')->toArray();
        foreach ($days as $day) {
            if (!in_array($day, $existingDays)) {
                $dayRecord = new Day_Of_user();
                $dayRecord->user_id  = $user;
                $dayRecord->day_of_week = $day;
                $dayRecord->save();
            }
        }
        return response()->json([
            'message' => 'Days for user created successfully',
        ]);
            /**{
            "phone" : "0935027218",
            "address" :"aleppo" ,
            "days":
                    [
                        "sunday",
                        "tuesday",
                        "wednesday"
                    ]
        } */
    }
    public function update_info(Request $request)
    {
        $user = Auth::id();
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'days' => 'required|array',
            'days.*' => 'string|in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $existingUser = User::findOrFail($user);

        // Check if the user being updated is the currently authenticated user
        if ($existingUser->id !== $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $existingUser->phone = $request->input('phone');
        $existingUser->address = $request->input('address');
        $existingUser->save();

        $existingDays = Day_Of_user::where('user_id', $user)->get();
        foreach ($existingDays as $day) {
            $day->delete();
        }

        $days = $request->input('days');
        foreach ($days as $day) {
            $dayRecord = new Day_Of_user();
            $dayRecord->user_id  = $user;
            $dayRecord->day_of_week = $day;
            $dayRecord->save();
        }

        return response()->json([
            'message' => 'User information and days updated successfully',
        ]);
    }


    public function get_info()
    {
        $user_id = Auth::id();
        $user = User::findOrFail($user_id);
        $phone = $user->phone;
        $address = $user->address;
        $days = Day_Of_user::where('user_id', $user_id)
            ->select('day_of_week')
            ->get()
            ->pluck('day_of_week')
            ->toArray();
        $response = [
            'phone' => $phone,
            'address' => $address,
            'days' => $days,
        ];
        return response()->json($response, 200);
    }





    public function get_opportunities()
    {
        // Get the authenticated user's ID and days
        $user_id = Auth::id();
        $user_days = Day_Of_user::where('user_id', $user_id)
            ->pluck('day_of_week')
            ->toArray();

        // Get the user's favorite volunteer IDs
        $favorite_volunteer_ids = Favourite::where('user_id', $user_id)
            ->pluck('volunteer_work_id')
            ->toArray();

        $selected_category_ids = CategoryOfUser::where('user_id', $user_id)
        ->pluck('category_id')
        ->toArray();


        $volunteers = Volunteer_work::with('Day_of_vlunteer')
            ->where(function ($query) use ($user_days, $favorite_volunteer_ids) {
                $query->whereHas('Day_of_vlunteer', function ($query) use ($user_days) {
                    $query->whereIn('day_of_week', $user_days);
                })
                ->orWhereIn('id', $favorite_volunteer_ids);
            })
            ->orWhereHas('category', function ($query) use ($selected_category_ids) {
                $query->whereIn('id', $selected_category_ids);
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($volunteer) {
                return [
                    'id' => $volunteer->id,
                    'description' => $volunteer->description,
                    'start_date' => $volunteer->start_date,
                    'end_date' => $volunteer->end_date,
                    'address' => $volunteer->address,
                    'point' => $volunteer->point,
                    'count_worker' => $volunteer->count_worker,
                    'status' => $volunteer->status,
                    'user_id' => $volunteer->user_id,
                    'category_id' => $volunteer->category_id,
                    'days' => $volunteer->Day_of_vlunteer->pluck('day_of_week')->toArray(),
                ];
            });

        return response()->json($volunteers, 200);
    }
    public function change_status(Request $request, $id)
    {
        $work = Volunteer_work::findOrFail($id);
        if (Auth::id() !== $work->user_id) {
            return response()->json(['error' => 'You are not the owner of the volunteer work record'], 401);
        }
        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|required|string|in:complete,disapprove,finished',
            'user_id' => 'required|exists:users,id'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        if ($request->input('status') === 'complete') {
            $workerIds = $work->CarriedOut()->pluck('user_id')->toArray();
                        // Get the user_id from the request
            $userId = $request->input('user_id');
            // Check if the user_id is in the list of workerIds
            if (!in_array($userId, $workerIds)) {
                return response()->json(['error' => 'Invalid user_id'], 400);
            }
            // Change the status of the carried out record with the matching user_id to 'complete'
            $carriedOut = CarriedOut::where('volunteer_work_id', $work->id)
                                     ->where('user_id', $userId)
                                     ->first();
            if ($carriedOut) {
                $carriedOut->status = 'complete';
                $carriedOut->save();
            }
            return response()->json(['message' => 'Volunteer work status has been updated'], 200);

        } elseif ($request->input('status') === 'disapprove') {
            $workerIds = $work->CarriedOut()->pluck('user_id')->toArray();
            // Get the user_id from the request
            $userId = $request->input('user_id');
            // Check if the user_id is in the list of workerIds
            if (!in_array($userId, $workerIds)) {
                return response()->json(['error' => 'Invalid user_id'], 400);
            }
            // Change the status of the carried out record with the matching user_id to 'complete'
            $carriedOut = CarriedOut::where('volunteer_work_id', $work->id)
                                     ->where('user_id', $userId)
                                     ->first();
            if ($carriedOut) {
                $carriedOut->status = 'disapprove';
                $carriedOut->save();
            }
            return response()->json(['message' => 'Volunteer work status has been updated'], 200);
        }elseif ($request->input('status') === 'finished') {
            $point = $work->point;
            $user = User::findOrFail($request->input('user_id'));
            $owner = User::findOrFail($work->user_id);
            $user->point += $point; // assuming you want to add the point to the user's existing point balance
            $user->save();
            $owner->point -= $point;
            $owner->save();
            return response()->json(['message' => 'Volunteer work status has been updated'], 200);
        }
        return response()->json(['message' => 'Nothing to update'], 200);
    }


}
