<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use App\Models\Otp;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class AuthController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function Register(Request $request)
    {
        // dd($request);
        // die();
        try {
            $allowedFields = [
                'name',
                'email',
                'password',
                'password_confirmation',
                'role',
                'phone',
                'dob',
                'city',
                'state',
                'qualification',
                'gender',
                'profile_photo',
                'aadhar_photo_front', 'aadhar_photo_back'
            ];

            $requestFields = array_keys(request()->all());

            // Check for unknown fields
            $unknownFields = array_diff($requestFields, $allowedFields);

            if (!empty($unknownFields)) {

                $errorResponse = [
                    'error' => [
                        'message' => 'Unknown fields present in the request.',
                        'unknown_fields' => $unknownFields,
                    ],
                ];
                return response()->json($errorResponse, Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $request->merge(['gender' => ucfirst(strtolower($request->gender))]);

            $validationRules = [
                'name' => 'required|string|max:255',
                'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'phone' => 'required|string|min:10|max:12|unique:' . User::class,
                'dob' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        // Check if the value matches either 'd-m-Y' or 'd/m/Y' format
                        $date = null;

                        if (\DateTime::createFromFormat('d-m-Y', $value)) {
                            $date = \DateTime::createFromFormat('d-m-Y', $value)->format('d/m/Y');
                        } elseif (\DateTime::createFromFormat('d/m/Y', $value)) {
                            $date = $value; // Already in the correct format
                        }

                        if (!$date) {
                            $fail('The ' . $attribute . ' must be in the format d-m-Y or d/m/Y.');
                        }

                        // Replace the input value with the formatted date
                        request()->merge([$attribute => $date]);
                    },
                ],
                'city' => 'required|string|max:255',
                'state' => 'required|string|max:255',
                'qualification' => 'required|string|max:255',
                'gender' => 'required|in:Male,Female,Other',
                'profile_photo' => 'required|image|max:3072',
                'aadhar_photo_front' => 'required|image|max:3072',
                'aadhar_photo_back' => 'required|image|max:3072',
            ];



            $validator = Validator::make(request()->all(), $validationRules);


            if ($validator->fails()) {

                return response()->json(['error' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }


            $this->validate($request, $validationRules);

            $allowedImageExtensions = ['jpg', 'jpeg', 'png'];
            $uploadedImages = [];

            if ($request->hasFile('profile_photo')) {
                $profile_photo = $request->file('profile_photo');
                $imageName = time() . '.' . $profile_photo->getClientOriginalExtension();

                if (!in_array($profile_photo->getClientOriginalExtension(), $allowedImageExtensions)) {
                    return response()->json(['message' => 'Profile photo extension should be jpg, jpeg, png.', 'status' => false], 400);
                }

                $path = public_path('upload/profile');
                $profile_photo->move($path, $imageName);
                $uploadedImages['profile_photo'] = "upload/profile/$imageName";
            }

            if ($request->hasFile('aadhar_photo_front')) {
                $aadhar_photo_front = $request->file('aadhar_photo_front');
                $imageName = time() . '.' . $aadhar_photo_front->getClientOriginalExtension();

                if (!in_array($aadhar_photo_front->getClientOriginalExtension(), $allowedImageExtensions)) {
                    return response()->json(['message' => 'Aadhar front photo extension should be jpg, jpeg, png.', 'status' => false], 400);
                }

                $path = public_path('upload/aadhar_front');
                $aadhar_photo_front->move($path, $imageName);
                $uploadedImages['aadhar_photo_front'] = "upload/aadhar_front/$imageName";
            }

            if ($request->hasFile('aadhar_photo_back')) {
                $aadhar_photo_back = $request->file('aadhar_photo_back');
                $imageName = time() . '.' . $aadhar_photo_back->getClientOriginalExtension();

                if (!in_array($aadhar_photo_back->getClientOriginalExtension(), $allowedImageExtensions)) {
                    return response()->json(['message' => 'Aadhar back photo extension should be jpg, jpeg, png.', 'status' => false], 400);
                }

                $path = public_path('upload/aadhar_back');
                $aadhar_photo_back->move($path, $imageName);
                $uploadedImages['aadhar_photo_back'] = "upload/aadhar_back/$imageName";
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'user',
                'phone' => $request->phone,
                'dob' => $request->dob,
                'city' => $request->city,
                'state' => $request->state,
                'qualification' => $request->qualification,
                'gender' => $request->gender,
                'profile_photo' => $uploadedImages['profile_photo'],
                'aadhar_photo_front' => $uploadedImages['aadhar_photo_front'],
                'aadhar_photo_back' => $uploadedImages['aadhar_photo_back'],
            ]);

            event(new Registered($user));

            Auth::login($user);

            try {
                $otp = Otp::create([
                    'user_id' => $user->id,
                    'otp' => $this->generateOtp(),
                ]);

                \Log::info('OTP created: ' . $otp->otp);

                Mail::to($user->email)->send(new OtpMail($otp->otp, 'registration', $user));

                return response()->json(['message' => 'User registered successfully and OTP sent for email verification'], 200);
            } catch (\Exception $e) {
                \Log::error('Error sending OTP email: ' . $e->getMessage());
                return response()->json(['message' => 'An error occurred while sending OTP.', 'status' => false, 'error' => $e->getMessage()], 500);
            }

            // Mark the user as verified (for testing purposes)
            $user->update(['verified' => true]);

            return response()->json(['message' => 'User registered successfully and OTP sent for email verification'], 200);
        } catch (\Exception $e) {
            \log::error('Error during registration: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while processing your request.', 'status' => false, 'error' => $e->getMessage()], 500);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->getMessageBag();
            return response()->json(['message' => 'Validation error', 'errors' => $errors->toArray(), 'status' => false], 422);
        }
    }

    public function generateOtp()
    {
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        \Log::info('Generated OTP: ' . $otp);
        return $otp;
    }

    // Add the following function for OTP verification
    public function verifyOtp(Request $request)
    {
        try {
            // Validate the request parameters
            $validator = Validator::make($request->all(), [
                'otp' => 'required|string|size:6',
                'user_id' => 'required|exists:users,id',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                \Log::error('Validation failed during OTP verification: ' . json_encode($validator->errors()->toArray()));
                return response()->json(['error' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $user = User::find($request->user_id);
            if ($user->verified) {
                return response()->json(['message' => 'You are already verified'], 400);
            }

            // Find the OTP record with the provided OTP
            $otp = Otp::where('otp', $request->otp)->where('expired', 0)->first();

            // Check if the OTP is not found
            if (!$otp) {
                return response()->json(['message' => 'Invalid OTP'], 400);
            }

            // Check if the OTP has expired
            if ($otp->expired) {
                return response()->json(['message' => 'OTP has expired'], 400);
            }

            // Check if the OTP is verified (preventing multiple verifications)
            if ($otp->verified) {
                // Mark the OTP as expired
                $otp->update(['expired' => 1]);
                \Log::info('OTP was already verified for user_id: ' . $request->user_id);
                return response()->json(['message' => 'OTP has expired'], 400);
            }

            // Check if the user_id matches
            if ($otp->user_id != $request->user_id) {
                return response()->json(['message' => 'Invalid user for this OTP'], 400);
            }

            // Check if the OTP has passed the expiration time
            $expirationTimeInMinutes = 10; // Set the expiration time as per your requirement
            $expirationTime = Carbon::parse($otp->created_at)->addMinutes($expirationTimeInMinutes);

            if ($expirationTime->isPast()) {
                // Mark the OTP as expired
                $otp->update(['expired' => 1]);
                \Log::info('OTP expired for user_id: ' . $request->user_id);
                return response()->json(['message' => 'OTP has expired'], 400);
            }

            // Update the users table to mark the user as verified
            User::where('id', $otp->user_id)->update(['verified' => 1]);

            // Mark the OTP as verified and expired
            $otp->update(['verified' => 1, 'expired' => 1]);
            \Log::info('OTP verified successfully for user_id: ' . $request->user_id);

            return response()->json(['message' => 'OTP verification successful', 'user_id' => $otp->user_id], 200);
        } catch (\Exception $e) {
            \Log::error('Error during OTP verification: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while processing your request.', 'status' => false, 'error' => $e->getMessage()], 500);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->getMessageBag();
            return response()->json(['message' => 'Validation error', 'errors' => $errors->toArray(), 'status' => false], 422);
        }
    }

    public function forgotPassword(Request $request)
    {
        // dd($request);
        // die();
        try {
            // Validate the request parameters
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                \Log::error('Validation failed during forgot password: ' . json_encode($validator->errors()->toArray()));
                return response()->json(['error' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Find the user by email
            $user = User::where('email', $request->email)->first();

            // Check if the user is verified
            if (!$user->verified) {
                return response()->json(['message' => 'Account not verified. Please verify your account.'], 400);
            }

            // Generate OTP
            $otp = $this->generateOtp();

            // Save OTP in the database
            Otp::create([
                'user_id' => $user->id,
                'otp' => $otp,
            ]);

            // Send OTP to the user's email
            Mail::to($user->email)->send(new OtpMail($otp, 'forgot-password', $user));

            \Log::info('OTP sent for forgot password: ' . $otp);

            return response()->json(['message' => 'OTP sent successfully for password reset'], 200);
        } catch (\Exception $e) {
            \Log::error('Error during forgot password: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while processing your request.', 'status' => false, 'error' => $e->getMessage()], 500);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->getMessageBag();
            return response()->json(['message' => 'Validation error', 'errors' => $errors->toArray(), 'status' => false], 422);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            // Validate the request parameters
            $validator = Validator::make($request->all(), [
                'otp' => 'required|string|size:6',
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'user_id' => 'required|exists:users,id',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                \Log::error('Validation failed during password reset: ' . json_encode($validator->errors()->toArray()));
                return response()->json(['error' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Find the user by user_id
            $user = User::find($request->user_id);

            // Find the OTP record with the provided OTP
            $otp = Otp::where('otp', $request->otp)
                ->where('user_id', $request->user_id)
                ->where('expired', 0)
                ->first();

            // Check if the OTP is not found
            if (!$otp) {
                return response()->json(['message' => 'Invalid OTP'], 400);
            }

            // Check if the OTP has expired
            if ($otp->expired) {
                return response()->json(['message' => 'OTP has expired'], 400);
            }

            // Check if the OTP is verified (preventing multiple verifications)
            if ($otp->verified) {
                // Mark the OTP as expired
                $otp->update(['expired' => 1]);
                \Log::info('OTP was already verified for user_id: ' . $request->user_id);
                return response()->json(['message' => 'OTP has expired'], 400);
            }

            // Check if the OTP has passed the expiration time
            $expirationTimeInMinutes = 10; // Set the expiration time as per your requirement
            $expirationTime = Carbon::parse($otp->created_at)->addMinutes($expirationTimeInMinutes);

            if ($expirationTime->isPast()) {
                // Mark the OTP as expired
                $otp->update(['expired' => 1]);
                \Log::info('OTP expired for user_id: ' . $request->user_id);
                return response()->json(['message' => 'OTP has expired'], 400);
            }

            // Update the user's password
            $user->update(['password' => Hash::make($request->password)]);

            // Mark the OTP as verified and expired
            $otp->update(['verified' => 1, 'expired' => 1]);
            \Log::info('Password reset successful for user_id: ' . $request->user_id);

            return response()->json(['message' => 'Password reset successful'], 200);
        } catch (\Exception $e) {
            \Log::error('Error during password reset: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while processing your request.', 'status' => false, 'error' => $e->getMessage()], 500);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->getMessageBag();
            return response()->json(['message' => 'Validation error', 'errors' => $errors->toArray(), 'status' => false], 422);
        }
    }

    public function resendOtp(Request $request)
    {
        try {
            // Validate the request parameters
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                \Log::error('Validation failed during OTP resend: ' . json_encode($validator->errors()->toArray()));
                return response()->json(['error' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Find the user by user_id
            $user = User::find($request->user_id);

            // Check if the user is verified
            if ($user->verified) {
                return response()->json(['message' => 'User is already verified'], 400);
            }

            // Check if there is an unexpired OTP for the user
            $existingOtp = Otp::where('user_id', $request->user_id)
                ->where('expired', 0)
                ->first();

            if ($existingOtp) {
                // If an unexpired OTP exists, return an error
                return response()->json(['message' => 'An unexpired OTP already exists for this user'], 400);
            }

            // Generate a new OTP
            $otp = $this->generateOtp();

            // Save the new OTP in the database
            Otp::create([
                'user_id' => $user->id,
                'otp' => $otp,
            ]);

            // Send the new OTP to the user's email
            Mail::to($user->email)->send(new OtpMail($otp, 'registration', $user));

            \Log::info('Resent OTP successfully for user_id: ' . $request->user_id);

            return response()->json(['message' => 'OTP resent successfully'], 200);
        } catch (\Exception $e) {
            \Log::error('Error during OTP resend: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while processing your request.', 'status' => false, 'error' => $e->getMessage()], 500);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->getMessageBag();
            return response()->json(['message' => 'Validation error', 'errors' => $errors->toArray(), 'status' => false], 422);
        }
    }

    public function login(Request $request)
    {
        try {
            // Check if user is already logged in
            if (Auth::check()) {
                \Log::info('User is already logged in');
                return response()->json(['message' => 'User is already logged in'], Response::HTTP_BAD_REQUEST);
            }


            // Validate the request parameters
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Attempt to authenticate the user
            $credentials = $request->only('email', 'password');
            if (!Auth::attempt($credentials)) {
                return response()->json(['message' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
            }

            $user = Auth::user();

            // Check if the user is verified
            if (!$user->verified) {
                Auth::logout();
                return response()->json(['message' => 'Account not verified. Please verify your account.'], Response::HTTP_FORBIDDEN);
            }

            $tokenResult = $user->createToken('AuthToken');
            $token = $tokenResult->plainTextToken;

            return response()->json(['token' => $token, 'user' => $user], Response::HTTP_OK);
        } catch (\Exception $e) {
            \Log::error('Error during login: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while processing your request.', 'status' => false, 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->getMessageBag();
            return response()->json(['message' => 'Validation error', 'errors' => $errors->toArray(), 'status' => false], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function logout(Request $request)
    {
        try {
            // Ensure user is authenticated
            $user = $request->user();
            if (!$user) {
                throw new Exception('User not authenticated');
            }

            // Check for existing tokens before deletion
            if ($user->tokens()->count() === 0) {
                throw new Exception('User already logged out');
            }

            // Revoke all tokens associated with the user
            $user->tokens()->delete();

            return response()->json(['message' => 'Logout successful'], Response::HTTP_OK);
        } catch (\Exception $e) {
            \Log::error('Error during logout: ' . $e->getMessage());
            return response()->json([
                'message' => $e->getMessage(),
                'status' => false,
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
