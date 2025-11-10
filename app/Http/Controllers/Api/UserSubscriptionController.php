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
    public function index(Request $request)
    {
        $user = $request->user();

        $subscriptions = $user->subscriptions()->with('service')->get();

        return response()->json([
            'data' => $subscriptions
        ], 200);
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
            ], 422);
        }

        $validatedData = $validator->validated();

        $renewalDate = Carbon::parse($validatedData['renewal_date']);

        $notificationDate = $renewalDate->copy()->sub(
            $validatedData['notify_before_value'],
            $validatedData['notify_before_unit']
        );

        $subscription = UserSubscription::create([
            'user_id' => $request->user()->id,
            'service_id' => $validatedData['service_id'],
            'price' => $validatedData['price'],
            'renewal_date' => $renewalDate->toDateString(),
            'renewal_period_value' => $validatedData['renewal_period_value'],
            'renewal_period_unit' => $validatedData['renewal_period_unit'],
            'notify_before_value' => $validatedData['notify_before_value'],
            'notify_before_unit' => $validatedData['notify_before_unit'],

            'notification_date' => $notificationDate->toDateString(),
        ]);

        return response()->json([
            'message' => 'Assinatura salva com sucesso!',
            'data' => $subscription
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, UserSubscription $subscription)
    {
        if ($request->user()->id !== $subscription->user_id) {
            return response()->json([
                'message' => 'Não autorizado'
            ], 403);
        }

        $subscription->load('service');

        return response()->json([
            'data' => $subscription
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserSubscription $subscription)
    {
        if ($request->user()->id !== $subscription->user_id) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }


        $validator = Validator::make($request->all(), [
            'price' => 'sometimes|numeric|min:0',
            'renewal_date' => 'sometimes|date|after_or_equal:today',
            'renewal_period_value' => 'sometimes|integer|min:1',
            'renewal_period_unit' => 'sometimes|string|in:day,month,year',
            'notify_before_value' => 'sometimes|integer|min:1',
            'notify_before_unit' => 'sometimes|string|in:day,month',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $validator->errors()
            ], 422);
        }

        $validatedData = $validator->validated();

        $subscription->update($validatedData);

        $renewalDate = Carbon::parse($subscription->renewal_date);

        $notificationDate = $renewalDate->copy()->sub(
            $subscription->notify_before_value,
            $subscription->notify_before_unit
        );

        $subscription->update([
            'notification_date' => $notificationDate->toDateString(),
            'last_notification_sent_at' => null,
        ]);

        return response()->json([
            'message' => 'Assinatura atualizada com sucesso!',
            'data' => $subscription
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, UserSubscription $subscription)
    {
        if ($request->user()->id !== $subscription->user_id) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        $subscription->delete();

        return response()->json(null, 204);
    }
}
