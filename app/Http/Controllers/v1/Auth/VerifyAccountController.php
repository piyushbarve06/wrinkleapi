<?php

namespace App\Http\Controllers\v1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use App\Models\User;

class VerifyAccountController extends Controller
{
    /**
     * Login
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'token' => 'required',
        ]);

        try {

            $token = JWTAuth::getToken();

            $apy = JWTAuth::getPayload($token)->toArray();

            $user = User::where('email', $request->email)->first();

            // Return error message if user not found.
            if (!$user)
                return response()->json(['error' => 'User not found.'], 404);

            $auth = auth()->setToken($request->token)->user();

            // Return error message if user and token mismatch.
            if ($auth->email != $user->email)
                return response()->json(['error' => 'Email and Token did not matched.'], 401);

            $user->update(['email_verified_at' => \Carbon\Carbon::now()]);

            return response()->json(['message' => 'Account has been verified.'], 201);

        } catch (TokenExpiredException $e) {

            return response()->json(['error' => 'Session Expired.', 'status_code' => 401], 401);

        } catch (TokenInvalidException $e) {

            return response()->json(['error' => 'Token invalid.', 'status_code' => 401], 401);

        } catch (JWTException $e) {

            return response()->json(['token_absent' => $e->getMessage()], 401);

        }
    }
}
