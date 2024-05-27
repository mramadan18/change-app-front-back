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

class CompanyController extends Controller
{
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




    public function search(Request $request)
    {
        $categoryId = $request->query('category_id');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $status = $request->query('status');
        $address = $request->query('address');

        $query = Volunteer_work::query();

        if ($categoryId) {
            $query->where('category_id', $categoryId)
                  ->where('user_id', Auth::id());
        }

        if ($startDate && $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate])
                  ->where('user_id', Auth::id());

        } elseif ($startDate) {
            $query->where('start_date', '>=', $startDate)
                  ->where('user_id', Auth::id());
        } elseif ($endDate) {
            $query->where('start_date', '<=', $endDate)
                  ->where('user_id', Auth::id());
        } elseif ($address) {
            $query->where('address', 'like', '%' . $address . '%')
                  ->where('user_id', Auth::id());
        } else {
            $query->where('user_id', Auth::id());
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



    public function index()
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
            return response()->json(['error' => 'you are not  owner of the volunteer work'], 403);
        }
    }


    function showVolunteerWorkById($id)
    {

        $volunteerWork = Volunteer_work::findOrFail($id);

        // Check if the authenticated user is the owner of the volunteer work record
        if (Auth::id() !== $volunteerWork->user_id) {
            return response()->json(['error' => 'you are not  owner of the volunteer work'], 401);
        }
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


    function worker_count($id)
    {
        // Check if the authenticated user is the owner of the volunteer work record
        if (Auth::id() !== Volunteer_work::findOrFail($id)->user_id) {
            return response()->json(['error' => 'You are not the owner of the volunteer work record'], 401);
        }
        $volunteerWork = Volunteer_work::findOrFail($id);
        $workerIds = $volunteerWork->CarriedOut()->pluck('user_id');
        $workers = User::whereIn('id', $workerIds)->get();
        $json = [];
        foreach ($workers as $worker) {
            $carriedOut = $worker->carried_out()->where('volunteer_work_id', $id)->first();
            $data = [
                'id' => $worker->id,
                'name' => $worker->name,
                'email' => $worker->email,
                'phone' => $worker->phone,
                'address' => $worker->address,
                'status' => $carriedOut ? $carriedOut->status : null,
            ];
            $json[] = $data;
        }
        return response()->json($json, 200);
    }

    public function get_info($user_id)
    {
        $user = User::findOrFail($user_id);
        $phone = $user->phone;
        $address = $user->address;
        $company_id=Auth::id();

        // Check if the user has a carried_out record with the authenticated volunteer
        $carriedOutRecord = CarriedOut::where('user_id', $user_id)
        ->where('volunteer_work_id', function ($query) use ($company_id) {
            $query->select('id')
                ->from('volunteer_works')
                ->where('user_id', $company_id);
        })->first();

        if ($carriedOutRecord) {
            // User has a carried_out record with the authenticated volunteer, return the information
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
        } else {
            // User does not have a carried_out record with a volunteer_work created by the authenticated company
            return response()->json(['error' => 'User does not have a carried_out record with a volunteer_work created by the authenticated company'], 404);
        }
    }

    public function history_volunteer($id)
    {
        $company_id=Auth::id();
        // Check if the user has a carried_out record with the authenticated volunteer
        $carriedOutRecord = CarriedOut::where('user_id', $id)
        ->where('volunteer_work_id', function ($query) use ($company_id) {
            $query->select('id')
                ->from('volunteer_works')
                ->where('user_id', $company_id);
        })->first();
        if ($carriedOutRecord) {
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
        } else {
            // User does not have a carried_out record with a volunteer_work created by the authenticated company
            return response()->json(['error' => 'User does not have a carried_out record with a volunteer_work created by the authenticated company'], 404);
        }
    }




    public function carried_out($id)
    {
        $company_id=Auth::id();
        // Check if the user has a carried_out record with the authenticated volunteer
        $carried = CarriedOut::where('user_id', $id)
        ->where('volunteer_work_id', function ($query) use ($company_id) {
            $query->select('id')
                ->from('volunteer_works')
                ->where('user_id', $company_id);
        })->first();
        if ($carried) {
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
        } else {
            // User does not have a carried_out record with a volunteer_work created by the authenticated company
            return response()->json(['error' => 'User does not have a carried_out record with a volunteer_work created by the authenticated company'], 404);
        }
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
