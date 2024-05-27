<?php

namespace App\Http\Controllers\api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Volunteer_work;
use App\Models\Day_of_vlunteer;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
class VolunteerWorkController extends Controller
{

    public function index()
    {
        $volunteers = Volunteer_work::where('status', 'pending')->get();
        return response()->json($volunteers, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'address' => 'sometimes|required|string|max:255',
            'point' => 'required|integer|min:1',
            'count_worker' => 'required|integer|min:1',
            'category_id' => 'required|exists:categories,id', // validate category_id from request
            'days' => 'required|array',
            'days.*' => 'string|in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $user = Auth::user();
        if ($user->point < $request->input('point') *$request->input('count_worker') ) {
            return response()->json(['error' => 'You do not have enough points to create this volunteer work'], 403);
        }
        // use the authenticated user's id as the user_id for the new task
        $validatedData = $validator->validated();
        $validatedData['user_id'] = Auth::id();
        // create the Volunteer_work record
        $volunteerWork = Volunteer_work::create($validatedData);
        $user->point -= $request->input('point') * $request->input('count_worker');
        $user->save();
        // get the days of the week from the request
        $days = $request->input('days');
        // create a new Day record for each day of the week requested
        foreach ($days as $day) {
            $dayRecord = new Day_of_vlunteer();
            $dayRecord->volunteer_work_id = $volunteerWork->id;
            $dayRecord->day_of_week = $day;
            $dayRecord->save();
        }
        return response()->json($volunteerWork, 201);
    }



    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string|max:255',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after:start_date',
            'point' => 'sometimes|required|integer|min:1',
            'count_worker' => 'sometimes|required|integer|min:1',
            'category_id' => 'sometimes|required|exists:categories,id',
            'days' => 'sometimes|required|array',
            'days.*' => 'string|in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $validatedData = $validator->validated();
        $volunteerWork = Volunteer_work::findOrFail($id);
        $old_point_discount = $volunteerWork->point * $volunteerWork->count_worker;

        $user = Auth::user();

        if ($old_point_discount >= $request->input('point') * $request->input('count_worker') ) {

            $x= $old_point_discount - $request->input('point') *$request->input('count_worker');
            if($x){
            $user->point += $x;
            $user->save();
            }else{
                $x=0;
            }
        }
        if ($old_point_discount < $request->input('point') *$request->input('count_worker') ) {
            $x= $request->input('point') * $request->input('count_worker') - $old_point_discount ;
            $user = Auth::user();
            if ($user->point < $x ) {
                return response()->json(['error' => 'You do not have enough points to create this volunteer work'], 403);
            }
            $user->point -= $x;
            $user->save();
        }

        if (Auth::id() !== $volunteerWork->user_id) {
            return response()->json(['error' => 'you are not  owner of the volunteer work '], 401);
        }

        $volunteerWork->update($validatedData);
        // get the days of the week from the request
        $days = $request->input('days');
        // delete all existing Day_of_vlunteer records associated with the Volunteer_work record
        Day_of_vlunteer::where('volunteer_work_id', $volunteerWork->id)->delete();
        // create a new Day_of_vlunteer record for each day of the week requested
        foreach ($days as $day) {
            $dayRecord = new Day_of_vlunteer();
            $dayRecord->volunteer_work_id = $volunteerWork->id;
            $dayRecord->day_of_week = $day;
            $dayRecord->save();
        }
        return response()->json($volunteerWork, 200);
    }

    public function destroy(Request $request, $id)
    {
        $volunteerWork = Volunteer_work::findOrFail($id);
        // Check if the volunteer is the same as the current user
        if ($volunteerWork->user_id == $request->user()->id) {
            $volunteerWork->delete();
            return response()->json(null, 204);
        } else {
            // Return an error message if the volunteer is not the same as the current user
            return response()->json(['error' => 'Unauthorized action'], 403);
        }
    }



    public function search(Request $request)
    {
        $categoryId = $request->query('category_id');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $userType = $request->query('user_type');
        $address = $request->query('address');

        $query = Volunteer_work::query();

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        /**{
         * 'category_id = 1
         * } */

        if ($startDate && $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->where('start_date', '>=', $startDate);
        } elseif ($endDate) {
            $query->where('start_date', '<=', $endDate);
            /**
             * {
             *      'start_date'=>"2024-5-7"
             *      'endDate'=>"2026-5-7"
             * }
             */
        } elseif ($userType) {
            $query->whereHas('User', function ($query) use ($userType) {
                $query->where('type_user', $userType);
            })->where('user_id', auth()->id());
            /**
             * {
             *      'user_id' => '0'
             * }
             */
        } elseif ($address) {

            $query->where('address', 'like', '%' . $address . '%');

        }


        $volunteerWorks = $query->get();
        foreach ($volunteerWorks as $volunteerWork) {
            $days = $volunteerWork->Day_of_vlunteer()->pluck('day_of_week')->toArray();
            $volunteerWork->days = $days;
            $data = [
                'description' => $volunteerWork->description,
                'start_date' => $volunteerWork->start_date,
                'end_date' => $volunteerWork->end_date,
                'count_worker' => $volunteerWork->count_worker,
                'category_id' => $volunteerWork->category_id,
                'point' => $volunteerWork->point,
                'address' => $volunteerWork->address,
                'days' => $volunteerWork->days,
                'status' => $volunteerWork->status,
            ];
            $json[] = $data;
        }
        return response()->json($json, 200);
    }

    function showVolunteerWorkById($id)
    {
        $volunteerWork = Volunteer_work::findOrFail($id);
        // Eager load the associated day of the week records
        $volunteerWork->load('Day_of_vlunteer');
        // Transform the volunteer work record into an array with the desired keys and values
        $data = [
            'id' => $volunteerWork->id,
            'description' => $volunteerWork->description,
            'start_date' => $volunteerWork->start_date,
            'end_date' => $volunteerWork->end_date,
            'address' => $volunteerWork->address,
            'point' => $volunteerWork->point,
            'count_worker' => $volunteerWork->count_worker,
            'status' => $volunteerWork->status,
            'user_id' => $volunteerWork->user_id,
            'category_id' => $volunteerWork->category_id,
            'days' => $volunteerWork->Day_of_vlunteer->pluck('day_of_week')->toArray(),
        ];
        return response()->json($data, 200);
    }
}
