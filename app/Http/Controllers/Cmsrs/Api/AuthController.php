<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cmsrs\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\JWTAuth;



class AuthController extends Controller
{

    
    public function __construct(
        private JWTAuth $jwtAuth
    ){
    }

    /**
     * API Login, on success return JWT Auth token
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        $rules = [
            'email' => 'required|email',
            'password' => 'required',
        ];

        $validator = Validator::make($credentials, $rules);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->messages()], 200);
        }

        // $credentials['is_verified'] = 1;
        return $this->getTokenByCredentials($credentials);
    }

    /**
     * Get the token by the given credentials.
     *
     * @param  array<string, string>  $credentials
     */
    private function getTokenByCredentials(array $credentials): JsonResponse
    {
        try {
            // attempt to verify the credentials and create a token for the user
            if (! $token = $this->jwtAuth->attempt($credentials)) {
                return response()->json(['success' => false, 'error' => 'We cant find an account with this credentials.'], 200);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['success' => false, 'error' => 'Failed to login, please try again.'], 500);
        }

        $user = User::where('email', '=', $credentials['email'])->firstOrFail();
        if ($user->role != User::$role_dict['admin']) {
            return response()->json(['success' => false, 'error' => 'No access.'], 401);
        }

        // all good so return the token
        return response()->json(['success' => true, 'data' => ['token' => $token]], 200);
    }

    /**
     * Log out
     * Invalidate the token, so user cannot use it anymore
     * They have to relogin to get a new token
     */
    public function logout(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
        ]);

        try {
            $this->jwtAuth
                ->setToken($request->input('token'))
                ->invalidate();            
            //JWTAuth::invalidate($request->input('token'));

            return response()->json(['success' => true, 'message' => 'You have successfully logged out.']);
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['success' => false, 'error' => 'Failed to logout, please try again.'], 500);
        }
    }

    /**
     * API Register
     */
    public function register(Request $request): JsonResponse
    {
        $demoStatus = config('cmsrs.demo'); // env('DEMO_STATUS', false);
        if ($demoStatus) {
            // echo 'Not permission';
            return response()->json(['success' => false, 'error' => 'Not permission']); // phpstan fix
        } else {
            if ($request->ip() !== '127.0.0.1' || ($request->input('secret') !== $_ENV['RS_SECRET'])) {
                return response()->json(['success' => false, 'error' => 'no access']);
            }

            $credentials = $request->only('name', 'email', 'password');

            $rules = [
                'name' => 'max:255',
                'email' => 'required|email|max:255|unique:users',
            ];
            $validator = Validator::make($credentials, $rules);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'error' => $validator->messages()]);
            }
            $name = $request->name;
            $email = $request->email;
            $password = $request->password;

            $user = new User([
                'email' => $email,
                'name' => $name,
                'role' => User::$role_dict['admin'],
            ]);

            $user->password = $password;
            $user->save();

            return $this->getTokenByCredentials($credentials);
        }
    }
}
