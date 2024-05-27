<?php

namespace App\Http\Controllers\api;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Models\Volunteer_work;
use App\Models\Day_of_vlunteer;
use App\Models\CarriedOut;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
class CarriedOutController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([

            'volunteer_work_id' => 'required|exists:volunteer_works,id',

        ]);
        $volunteerWork = Volunteer_work::findOrFail($request->volunteer_work_id);
        $num = $volunteerWork->count_worker;
        $carriedOutCount = CarriedOut::where('volunteer_work_id', $request->volunteer_work_id)
            ->where('status', 'complete')
            ->count();
        if ($carriedOutCount < $num) {
            $carriedOut = CarriedOut::where('user_id', Auth::id())
                ->where('volunteer_work_id', $request->volunteer_work_id)
                ->first();
            if ($carriedOut) {
                return response()->json([
                    'message' => 'User has already carried out this volunteer work',
                ], 409);
            } else {
                $carriedOut = new CarriedOut();
                $carriedOut->user_id = Auth::id();
                $carriedOut->volunteer_work_id = $request->volunteer_work_id;
                $carriedOut->save();
                return response()->json([
                    'message' => 'Carried out record created successfully',
                    'carried_out' => $carriedOut,
                ]);
            }
        } else {
            $volunteerWork->status = 'completed';
            $volunteerWork->save();
            return response()->json([
                'message' => 'Volunteer work status updated to completed',
                'volunteer_work' => $volunteerWork,
            ]);
        }
    }

    public function worker_count($id)
    {
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
}
