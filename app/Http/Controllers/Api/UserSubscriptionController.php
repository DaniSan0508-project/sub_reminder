<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserSubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'service_id' => 'required|integer|exists:services,id',
            'price' => 'required|numeric|min:0',
            'renewal_date' => 'required|date|after:today',

            'renewal_period_value' => 'required|integer|min:1',
            'renewal_period_unit' => 'required|string|in:day,month,year',

            'notify_before_value' => 'required|integer|min:1',
            'notify_before_unit' => 'required|string|in:day,month',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $validator->errors()
            ], 422); // 422 = Unprocessable Entity
        }

        $validatedData = $validator->validated();

        $renewalDate = Carbon::parse($validatedData['renewal_date']);

        $notificationDate = $renewalDate->copy()->sub(
            $validatedData['notify_before_value'],
            $validatedData['notify_before_unit']
        );

        $subscription = UserSubscription::create([
            'user_id' => 1, // $request->user()->id, 
            'service_id' => $validatedData['service_id'],
            'price' => $validatedData['price'],
            'renewal_date' => $renewalDate->toDateString(), // Formato Y-m-d
            'renewal_period_value' => $validatedData['renewal_period_value'],
            'renewal_period_unit' => $validatedData['renewal_period_unit'],
            'notify_before_value' => $validatedData['notify_before_value'],
            'notify_before_unit' => $validatedData['notify_before_unit'],

            'notification_date' => $notificationDate->toDateString(), // Formato Y-m-d
        ]);

        return response()->json([
            'message' => 'Assinatura salva com sucesso!',
            'data' => $subscription
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
