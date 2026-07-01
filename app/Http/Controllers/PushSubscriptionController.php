<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'endpoint' => 'required|string',
                'public_key' => 'nullable|string',
                'auth_token' => 'nullable|string',
                'content_encoding' => 'nullable|string',
            ]);

            $user = auth()->user();

            if (!$user) {
                throw new \Exception('User not authenticated');
            }
            
            $user->updatePushSubscription(
                $validated['endpoint'],
                $validated['public_key'] ?? null,
                $validated['auth_token'] ?? null,
                $validated['content_encoding'] ?? null,
            );

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            \Log::error(
                'Push Subscription Error',[
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
        
    }
}
