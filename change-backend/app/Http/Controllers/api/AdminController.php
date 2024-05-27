<?php

namespace App\Http\Controllers\api;

use App\Models\Volunteer_work;
use App\Models\User;
use App\Models\Day_Of_user;
use App\Models\CarriedOut;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function all_volunteer()
    {
        $volunteerWorks = Volunteer_work::get();
        $json = [];
        foreach ($volunteerWorks as $volunteerWork) {
            $days = $volunteerWork->Day_of_vlunteer()->pluck('day_of_week')->toArray();
            $volunteerWork->days = $days;
            if (!empty($volunteerWork->user_id)) { // Check if volunteer is not empty
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
        }
        return response()->json($json, 200);
    }

    public function destory_volunteer($id)
    {
        $volunteerWork = Volunteer_work::findOrFail($id);
        $volunteerWork->delete();
        return response()->json(['message' => 'Volunteer work deleted successfully'], 200);
    }

    public function search(Request $request)
    {
        $categoryId = $request->query('category_id');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $userType = $request->query('user_type');
        $address = $request->query('address');
        $status = $request->query('status');

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
        } elseif ($status) {

            $query->where('status', $status);
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

    public function all_user()
    {
        $users = User::all();
        return response()->json($users, 200);
    }

    public function change_type(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $request->validate([
            'type_user' => 'required|integer|in:0,1,2',
        ]);
        $type_user = $request->input('type_user');
        $user->type_user = $type_user;
        $user->save();
        return response()->json(['message' => 'User type updated successfully'], 200);
    }

    public function change_status(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $request->validate([
            'status' => 'required|boolean',
        ]);
        $status = $request->input('status');
        $user->status = $status;
        $user->save();
        return response()->json(['message' => 'User status updated successfully'], 200);
    }
    public function get_info($user_id)
    {
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
    public function history_volunteer($id)
    {
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

    public function carried_out($id)
    {
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
    public function findUserByEmail(Request $request)
    {
        $email = $request->input('email');
        $user = User::where('email', 'like', "%{$email}%")->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json($user);
    }
    public function addPoints(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'points' => 'required|numeric|min:0',
        ]);
        $user = User::findOrFail($request->input('user_id'));
        $user->point += $request->input('points');
        $user->save();
        return response()->json(['message' => 'Points added successfully'], 200);
    }

    public function DecresePoints(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'points' => 'required|numeric|min:0',
        ]);
        $user = User::findOrFail($request->input('user_id'));

        if ($user->point < $request->input('points')) {
            return response()->json(['message' => 'Insufficient points'], 400);
        }
        $oldPoints = $user->point;
        $user->point -= $request->input('points');
        if ($user->point < 0) {
            $user->point = $oldPoints;
            return response()->json(['message' => 'Insufficient points'], 400);
        }
        $user->save();
        return response()->json(['message' => 'Points decreased successfully'], 200);
    }

    public function dash()
    {
        $all_user = User::count();
        $count_user = User::where('type_user', 0)->count();
        $count_blocked_users = User::where('status', 1)->count();
        $count_company = User::where('type_user', 2)->count();
        $count_pending_volunteers = Volunteer_work::where('status', 'pending')->count();
        $count_compleated_volunteers = Volunteer_work::where('status', 'compleated')->count();
        $count_all_volunteers = Volunteer_work::count();
        return response()->json(
            [
                'count_all_user' => $all_user,
                'count_volunteer' => $count_user,
                'count_company' => $count_company,
                'count_pending_volunteers' => $count_pending_volunteers,
                'count_compleated_volunteers' => $count_compleated_volunteers,
                'count_all_volunteers' => $count_all_volunteers,
                'count_blocked_users' => $count_blocked_users,

            ]
        );
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


    function worker_count($id)
    {
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
}
