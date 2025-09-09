<?php

namespace App\Http\Controllers;

use App\AccountDeletionRequest;
use App\Mail\AccountDeletionConfirmationMail;
use App\Shared\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class AccountDeletionController extends Controller
{
    /**
     * Request account deletion.
     */
    public function requestDeletion(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
        ], [
            'password.required' => 'Please enter your password to confirm.',
            'password.current_password' => 'The password is incorrect.',
        ]);

        $user = Auth::user();

        // Create deletion request
        $deletionRequest = AccountDeletionRequest::createForUser($user);

        // Send confirmation email
        Mail::to($user->email)->send(new AccountDeletionConfirmationMail($deletionRequest));

        return response()->json([
            'success' => true,
            'message' => 'Account deletion confirmation email has been sent to your email address. Please check your inbox and follow the instructions to confirm the deletion.',
        ]);
    }

    /**
     * Confirm account deletion.
     */
    public function confirmDeletion($token)
    {
        $deletionRequest = AccountDeletionRequest::where('token', $token)
            ->where('confirmed', false)
            ->first();

        if (!$deletionRequest) {
            return view('account-deletion.invalid-token');
        }

        if ($deletionRequest->isExpired()) {
            return view('account-deletion.expired-token');
        }

        // Mark as confirmed
        $deletionRequest->markAsConfirmed();

        // Delete the user and all associated data
        DB::transaction(function () use ($deletionRequest) {
            $user = $deletionRequest->user;
            
            // Delete user and all related data (cascade will handle most)
            $user->delete();
            
            // Clean up the deletion request
            $deletionRequest->delete();
        });

        return view('account-deletion.confirmed');
    }

    /**
     * Show account deletion request form.
     */
    public function showRequestForm()
    {
        return view('account-deletion.request');
    }
}
