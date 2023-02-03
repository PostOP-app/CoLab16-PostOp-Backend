<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\RecoveryPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RecoveryPlanController extends Controller
{
    /**
     * setup middleware
     * @return void
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function __construct()
    {
        $this->middleware('verify_patient_id')->only(['createRecoveryPlan', 'updateRecoveryPlan']);
    }

    /**
     * fetch all recovery plans
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchAllRecoveryPlans()
    {
        $recoveryPlans = RecoveryPlan::where('provider_id', auth()->user()->id)->latest()->paginate(15);
        return response([
            'status' => true,
            'data' => $recoveryPlans,
        ]);
    }

    /**
     * fetch all recovery plans for a patient
     * @param User $patient
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchAllRecoveryPlansForPatient()
    {
        $recoveryPlans = RecoveryPlan::where('patient_id', auth()->user()->id)->latest()->paginate(15);
        return response([
            'status' => true,
            'data' => $recoveryPlans,
        ]);
    }

    /**
     * fetch a recovery plan
     * @param RecoveryPlan $recoveryPlan
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchRecoveryPlan(RecoveryPlan $recoveryPlan)
    {
        if (!$recoveryPlan) {
            return response([
                'status' => false,
                'message' => 'Recovery plan not found',
            ], 404);
        }

        return response([
            'status' => true,
            'data' => $recoveryPlan,
        ]);
    }

    /**
     * create a new recovery plan
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createRecoveryPlan(Request $request)
    {
        $validate = $this->validateRecoveryPlanData($request);
        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validate->errors()->messages(),
            ], 400);
        }

        $recoveryPlan = new RecoveryPlan();
        $this->store($request, $recoveryPlan);

        return response([
            'status' => true,
            'message' => 'Recovery plan created successfully',
            'data' => $recoveryPlan,
        ]);
    }

    /**
     * validate recovery plan data
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validateRecoveryPlanData(Request $request)
    {
        return Validator::make($request->all(), [
            'patient_id' => 'required|exists:users,id',
            'tracker' => 'required|string|max:101',
            'details' => 'required|string|max:255',
            'frequency' => 'required|string|max:50',
            'times' => 'required|date_format:H:i',
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
    }

    /**
     * store recovery plan data
     * @param Request $request
     * @param RecoveryPlan $recoveryPlan
     * @return void
     */
    public function store(Request $request, RecoveryPlan $recoveryPlan)
    {
        $recoveryPlan->provider_id = auth()->user()->id;
        $recoveryPlan->patient_id = $request->patient_id;
        $recoveryPlan->tracker = $request->tracker;
        $recoveryPlan->slug = Str::slug($request->tracker) . '-' . time();
        $recoveryPlan->details = $request->details;
        $recoveryPlan->frequency = $request->frequency;
        $recoveryPlan->times = $request->times;
        // // store array of times
        // $times = [];
        // foreach ($request->times as $key => $time) {
        //     array_push($times, [
        //         $recoveryPlan->times => $time,
        //     ]);
        //     dd($recoveryPlan->times, $times);
        // }
        $recoveryPlan->start_date = $request->start_date;
        $recoveryPlan->end_date = $request->end_date;

        $recoveryPlan->save();
    }

    public function updateRecoveryPlan(Request $request, RecoveryPlan $recoveryPlan)
    {
        if (!$recoveryPlan) {
            return response([
                'status' => false,
                'message' => 'Recovery plan not found',
            ], 404);
        }

        if ($recoveryPlan->provider_id !== auth()->user()->id) {
            return response([
                'status' => false,
                'message' => 'You are not authorized to update this recovery plan',
            ], 401);
        }

        if ($request->tracker) {
            $validateTracker = Validator::make($request->all(), [
                'tracker' => 'string|max:101',
            ]);

            if ($validateTracker->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validateTracker->errors()->messages(),
                ], 400);
            }

            $recoveryPlan->tracker = $request->tracker;
        }

        if ($request->details) {
            $validateDetails = Validator::make($request->all(), [
                'details' => 'string|max:255',
            ]);

            if ($validateDetails->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validateDetails->errors()->messages(),
                ], 400);
            }

            $recoveryPlan->details = $request->details;
        }

        if ($request->frequency) {
            $validateFrequency = Validator::make($request->all(), [
                'frequency' => 'string|max:50',
            ]);

            if ($validateFrequency->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validateFrequency->errors()->messages(),
                ], 400);
            }

            $recoveryPlan->frequency = $request->frequency;
        }

        if ($request->times) {
            $validateTimes = Validator::make($request->all(), [
                'times' => 'date_format:H:i',
            ]);

            if ($validateTimes->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validateTimes->errors()->messages(),
                ], 400);
            }

            $recoveryPlan->times = $request->times;
        }

        if ($request->start_date) {
            $validateStartDate = Validator::make($request->all(), [
                'start_date' => 'date|before_or_equal:end_date',
            ]);

            if ($validateStartDate->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validateStartDate->errors()->messages(),
                ], 400);
            }

            $recoveryPlan->start_date = $request->start_date;
        }

        if ($request->end_date) {
            $validateEndDate = Validator::make($request->all(), [
                'end_date' => 'date|after_or_equal:start_date',
            ]);

            if ($validateEndDate->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validateEndDate->errors()->messages(),
                ], 400);
            }

            $recoveryPlan->end_date = $request->end_date;
        }

        $recoveryPlan->save();

        return response([
            'status' => true,
            'message' => 'Recovery plan updated successfully',
        ]);
    }

    public function deleteRecoveryPlan(RecoveryPlan $recoveryPlan)
    {
        if (!$recoveryPlan) {
            return response([
                'status' => false,
                'message' => 'Recovery plan not found',
            ], 404);
        }

        if ($recoveryPlan->provider_id !== auth()->user()->id) {
            return response([
                'status' => false,
                'message' => 'You are not authorized to delete this recovery plan',
            ], 401);
        }

        $recoveryPlan->delete();

        return response([
            'status' => true,
            'message' => 'Recovery plan deleted successfully',
        ]);
    }
}
