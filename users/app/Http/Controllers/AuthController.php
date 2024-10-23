<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;



class AuthController extends Controller
{
     /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6'
        ]);

        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        if ($user->save()) {
            // Attribuer le rôle 'customer' par défaut
            $role = Role::where('name', 'customer')->first();
            if ($role) {
                $user->roles()->attach($role);
            }

            return response()->json(['message' => 'Successfully registered', 'user' => $user], 201);
        } else {
            return response()->json(['message' => 'Registration failed'], 500);
        }
    }

    /**
     * Authenticate a user and return a token if the provided credentials are correct.
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }

        return response()->json(compact('token'));
    }

    /**
     * Get the authenticated user.
     */
    public function getUser()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }

        return response()->json(compact('user'));
    }
}
