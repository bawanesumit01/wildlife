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

    public function Register(Request $request)
    {
        // dd($request);
        // die();

        try {

            $allowedFields = [
                'email',
                'phone',
            ];



            $requestFields = array_keys(request()->all());

            if (!in_array('phone', $requestFields)) {
                return response()->json([
                    'message' => 'The phone field is required.',
                    'status' => false,
                    'code' => 422,
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if (!in_array('email', $requestFields)) {
                return response()->json([
                    'message' => 'The email field is required.',
                    'status' => false,
                    'code' => 422,
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Check for unknown fields
            // $unknownFields = array_diff($requestFields, $allowedFields);

            // if (!empty($unknownFields)) {

            //     return response()->json([
            //         'message' => 'Unknown fields present in the request.',
            //         'status'=> false,'code' => 400, 
            //         'unknown_fields' => $unknownFields,
            //         ], 400);
            // }


            $validationRules = [
                'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
                'phone' => 'required|string|min:10|max:12|unique:' . User::class,
            ];


            $validator = Validator::make(request()->all(), $validationRules);


            if ($validator->fails()) {

                return response()->json(['message' => $validator->errors(), 'status' => false, 'code' => 422], Response::HTTP_UNPROCESSABLE_ENTITY);
            }


            $this->validate($request, $validationRules);



            try {
                $otp = Otp::create([
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'otp_email' => $this->generateOtp(),
                    'otp_phone' => $this->generateMobileOtp(),
                ]);

                \Log::info('OTP created: ' . $otp->otp_email);
                \Log::info('OTP created: ' . $otp->otp_phone);

                // Mail::to($user->email)->send(new OtpMail($otp->otp, 'registration', $user));

                return response()->json([
                    'status' => true,
                    'code' => 200,
                    'message' => 'User registered successfully and OTP sent for email verification',
                    'data' => [
                        'otp_email' => $otp->otp_email,
                        'otp_phone' => $otp->otp_phone
                    ],

                ], Response::HTTP_OK);
            } catch (\Exception $e) {
                \Log::error('Error sending OTP email: ' . $e->getMessage());
                return response()->json([
                    'status' => false,
                    'message' => 'An error occurred while sending OTP.' . $e->getMessage(),
                    'code' => 500,
                    'data' => (object)[],
                ],  Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            // Mark the user as verified (for testing purposes)
            $user->update(['verified' => true]);

            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => 'User registered successfully and OTP sent for email verification',
                'data' => [
                    'otp_email' => $otp->otp_email,
                    'otp_phone' => $otp->otp_phone,
                ],
            ],  Response::HTTP_OK);
        } catch (\Exception $e) {
            \log::error('Error during registration: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'code' => 500,
                'message' => 'An error occurred while processing your request.',
                'error' => $e->getMessage(),
            ],   Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->getMessageBag();
            return response()->json([
                'status' => false,
                'code' => 422,
                'message' => 'Validation error',
                'errors' => $errors->toArray(),
            ],   Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function generateOtp()
    {
        $otp_email = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        \Log::info('Generated OTP: ' . $otp_email);
        return $otp_email;
    }

    public function generateMobileOtp()
    {
        $otp_phone = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        \Log::info('Generated OTP: ' . $otp_phone);
        return $otp_phone;
    }

    public function verifyOtp(Request $request)
    {
        // dd($request);
        // die();
        
        try {

            $allowedFields = [
                'otp_email',
                'otp_phone',
                'name',
                'email',
                'password',
                'password_confirmation',
                'phone',
                'dob',
                'city',
                'state',
                'qualification',
                'gender',
                'profile_photo',
                'aadhar_photo_front',
                'aadhar_photo_back',
            ];

            $requestFields = array_keys(request()->all());

            // Check for unknown fields
            $unknownFields = array_diff($requestFields, $allowedFields);

            if (!empty($unknownFields)) {

                return response()->json(['message' => 'Unknown fields present in the request.', 'status' => false, 'code' => 400, 'unknown_fields' => $unknownFields],  Response::HTTP_BAD_REQUEST);
            }

            $request->merge(['gender' => ucfirst(strtolower($request->gender))]);



            $validationRules = [
                'otp_email' => 'required|string|size:4',
                'otp_phone' => 'required|string|size:4',
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

            $customMessages = [
                'profile_photo.max' => 'The profile photo size should not exceed 3 MB.',
                'aadhar_photo_front.max' => 'The Aadhar front photo size should not exceed 3 MB.',
                'aadhar_photo_back.max' => 'The Aadhar back photo size should not exceed 3 MB.',
            ];
            
            $validator = Validator::make(request()->all(), $validationRules, $customMessages);
            
            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                    'status' => false,
                    'code' => 422
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            
            $this->validate($request, $validationRules, $customMessages);


            $allowedImageExtensions = ['jpg', 'jpeg', 'png'];
            $uploadedImages = [];

            if ($request->hasFile('profile_photo')) {
                $profile_photo = $request->file('profile_photo');
                $imageName = time() . '.' . $profile_photo->getClientOriginalExtension();

                if (!in_array($profile_photo->getClientOriginalExtension(), $allowedImageExtensions)) {
                    return response()->json(['message' => 'Profile photo extension should be jpg, jpeg, png.', 'status' => false, 'code' => 422],  Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                $path = public_path('upload/profile');
                $profile_photo->move($path, $imageName);
                $uploadedImages['profile_photo'] = "upload/profile/$imageName";
            }

            if ($request->hasFile('aadhar_photo_front')) {
                $aadhar_photo_front = $request->file('aadhar_photo_front');
                $imageName = time() . '.' . $aadhar_photo_front->getClientOriginalExtension();

                if (!in_array($aadhar_photo_front->getClientOriginalExtension(), $allowedImageExtensions)) {
                    return response()->json(['message' => 'Aadhar front photo extension should be jpg, jpeg, png.', 'status' => false, 'code' => 422],  Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                $path = public_path('upload/aadhar_front');
                $aadhar_photo_front->move($path, $imageName);
                $uploadedImages['aadhar_photo_front'] = "upload/aadhar_front/$imageName";
            }

            if ($request->hasFile('aadhar_photo_back')) {
                $aadhar_photo_back = $request->file('aadhar_photo_back');
                $imageName = time() . '.' . $aadhar_photo_back->getClientOriginalExtension();

                if (!in_array($aadhar_photo_back->getClientOriginalExtension(), $allowedImageExtensions)) {
                    return response()->json(['message' => 'Aadhar back photo extension should be jpg, jpeg, png.', 'status' => false, 'code' => 422], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                $path = public_path('upload/aadhar_back');
                $aadhar_photo_back->move($path, $imageName);
                $uploadedImages['aadhar_photo_back'] = "upload/aadhar_back/$imageName";
            }


            // Find the OTP record with the provided OTP
            $otp = Otp::where('otp_email', $request->otp_email)->first();
            $mobileOtp = Otp::where('otp_phone', $request->otp_phone)->first();

            // Check if the user matches
            if ($otp->email != $request->email && $otp->phone != $request->phone) {
                return response()->json(['message' => 'Invalid Email And Phone Number For This OTP', 'status' => false, 'code' => 400], Response::HTTP_BAD_REQUEST);
            } elseif ($otp->email != $request->email) {
                return response()->json(['message' => 'Invalid Email For This OTP', 'status' => false, 'code' => 400], Response::HTTP_BAD_REQUEST);
            } elseif ($otp->phone != $request->phone) {
                return response()->json(['message' => 'Invalid Phone Number For This OTP', 'status' => false, 'code' => 400], Response::HTTP_BAD_REQUEST);
            }


            $user = User::where('email', $request->email)->where('phone', $request->phone)->first();

            if ($user && $user->verified == 1) {
                return response()->json(['message' => 'You are already verified', 'status' => false, 'code' => 400], Response::HTTP_BAD_REQUEST);
            }


            // Expire existing OTPs for the user
            Otp::where('email', $request->email)->where('phone', $request->phone)->where('expired', 0)->update(['expired' => 1]);


            // Check if the OTP is not found
            if (!$otp && !$mobileOtp) {
                return response()->json(['message' => 'Invalid OTP For Email And Mobile Number', 'status' => false, 'code' => 400], Response::HTTP_BAD_REQUEST);
            } elseif (!$otp) {
                return response()->json(['message' => 'Invalid OTP For Email', 'status' => false, 'code' => 400], Response::HTTP_BAD_REQUEST);
            } elseif (!$mobileOtp) {
                return response()->json(['message' => 'Invalid OTP For Mobile Number', 'status' => false, 'code' => 400], Response::HTTP_BAD_REQUEST);
            }

            // Check if the OTP has expired
            if ($otp->expired == 1 && $mobileOtp->expired == 1) {
                return response()->json(['message' => 'OTP Has Expired For Email And Mobile Number', 'status' => false, 'code' => 400], Response::HTTP_BAD_REQUEST);
            }



            // Check if the OTP has passed the expiration time
            $expirationTimeInMinutes = 10; // Set the expiration time as per your requirement
            $expirationTime = Carbon::parse($otp->created_at)->addMinutes($expirationTimeInMinutes);

            if ($expirationTime->isPast()) {
                // Mark the OTP as expired
                $otp->update(['expired' => 1]);
                \Log::info('OTP expired for user: ' . $request->email . 'and' . $request->phone);
                return response()->json(['message' => 'OTP has expired', 'status' => false, 'code' => 400], Response::HTTP_BAD_REQUEST);
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
                'verified' => 1,
                'profile_photo' => $uploadedImages['profile_photo'],
                'aadhar_photo_front' => $uploadedImages['aadhar_photo_front'],
                'aadhar_photo_back' => $uploadedImages['aadhar_photo_back'],
            ]);

            event(new Registered($user));

            Auth::login($user);

            // Update the users table to mark the user as verified
            // User::where('email', $otp->email)->where('phone', $mobileOtp->phone)->update(['verified' => 1]);

            // Mark the OTP as verified and expired
            $otp->update(['verified' => 1, 'expired' => 1]);
            \Log::info('OTP verified successfully for user: ' . $request->email . 'and' . $request->phone);

            return response()->json([
                'status' => true,
                'code' => 200,
                'message' => 'OTP verification successful',
                'data' => [
                    'email' => $request->email,
                    'phone' => $request->phone,
                ],
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error during OTP verification: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while processing your request.', 'status' => false, 'error' => $e->getMessage(), 'code' => 500], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->getMessageBag();
            return response()->json(['message' => 'Validation error', 'errors' => $errors->toArray(), 'status' => false, 'code' => 422],  Response::HTTP_UNPROCESSABLE_ENTITY);
        }catch (\Illuminate\Http\Exceptions\PostTooLargeException $e) {
            return response()->json([
                'message' => 'File size exceeds the maximum allowed limit.'.$e,
                'status' => false,
                'code' => 413,  // HTTP Payload Too Large
            ], 413);
        }
    }

    public function forgotPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'identifier' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors(), 'status' => false, 'code' => 422], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $user = User::where('email', $request->identifier)
                ->orWhere('phone', $request->identifier)
                ->first();

            if (!$user) {
                return response()->json(['message' => 'User not found', 'status' => false, 'code' => 404], Response::HTTP_NOT_FOUND);
            }

            // Check if the user is verified
            if (!$user->verified) {
                return response()->json(['message' => 'Account not verified. Please verify your account.', 'code' => 422, 'status' => false], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Generate OTP
            $otp = $this->generateOtp();

            // Determine whether the identifier is an email or a phone number
            $identifierType = filter_var($request->identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

            // Save OTP in the database
            Otp::create([
                'user_id' => $user->id,
                $identifierType => $request->identifier,
                "otp_{$identifierType}" => $otp,
            ]);

            // Send OTP to the user's email or phone
            // if ($identifierType === 'email') {
            //     Mail::to($user->email)->send(new OtpMail($otp, 'forgot-password', $user));
            // }
            // For phone, you can send OTP via SMS or any other preferred method.

            \Log::info('OTP sent for forgot password: ' . $otp);

            return response()->json(['status' => true, 'message' => 'OTP sent successfully for password reset', 'otp' => $otp, 'identifier' => $request->identifier, 'code' => 200], Response::HTTP_OK);
        } catch (\Exception $e) {
            \Log::error('Error during forgot password: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while processing your request.', 'status' => false, 'error' => $e->getMessage(), 'code' => 500], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->getMessageBag();
            return response()->json(['message' => 'Validation error', 'errors' => $errors->toArray(), 'status' => false, 'code' => 422], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            // Validation rules for the request
            $validator = Validator::make($request->all(), [
                'otp' => 'required|string|size:4',
                'password' => ['required', Rules\Password::defaults()],
                'identifier' => 'required|string',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                \Log::error('Validation failed during password reset: ' . json_encode($validator->errors()->toArray()));
                return response()->json(['error' => $validator->errors(), 'status' => false, 'code' => 422], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Find the user based on email or phone
            $user = User::where('email', $request->identifier)
                ->orWhere('phone', $request->identifier)
                ->first();

            // Check if the user is not found
            if (!$user) {
                return response()->json(['message' => 'User not found', 'status' => false, 'code' => 404], Response::HTTP_NOT_FOUND);
            }

            // Find the OTP record with the provided OTP and identifier
            $otp = Otp::where('user_id', $user->id)
                ->where('expired', 0)
                ->where(function ($query) use ($request) {
                    $identifierType = filter_var($request->identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
                    $query->where("otp_{$identifierType}", $request->otp);
                })
                ->first();

            // Check if the OTP is not found
            if (!$otp) {
                return response()->json(['message' => 'Invalid OTP', 'status' => false, 'code' => 400], Response::HTTP_BAD_REQUEST);
            }

            // Check if the OTP has expired
            if ($otp->expired) {
                return response()->json(['message' => 'OTP has expired', 'status' => false, 'code' => 400], Response::HTTP_BAD_REQUEST);
            }

            // Check if the OTP is verified (preventing multiple verifications)
            if ($otp->verified) {
                // Mark the OTP as expired
                $otp->update(['expired' => 1]);
                \Log::info('OTP was already verified for user_id: ' . $otp->user_id);
                return response()->json(['message' => 'OTP has expired', 'status' => false, 'code' => 400], Response::HTTP_BAD_REQUEST);
            }

            // Check if the OTP has passed the expiration time
            $expirationTimeInMinutes = 10; // Set the expiration time as per your requirement
            $expirationTime = Carbon::parse($otp->created_at)->addMinutes($expirationTimeInMinutes);

            if ($expirationTime->isPast()) {
                // Mark the OTP as expired
                $otp->update(['expired' => 1]);
                \Log::info('OTP expired for user_id: ' . $otp->user_id);
                return response()->json(['message' => 'OTP has expired', 'status' => false, 'code' => 400], Response::HTTP_BAD_REQUEST);
            }

            // Update the user's password
            $user->update(['password' => Hash::make($request->password)]);

            // Mark the OTP as verified and expired
            $otp->update(['verified' => 1, 'expired' => 1]);
            \Log::info('Password reset successful for user_id: ' . $user->id);

            // Return a success response
            return response()->json(['message' => 'Password reset successful', 'status' => true, 'code' => 200], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Handle any exceptions that may occur
            \Log::error('Error during password reset: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while processing your request.', 'status' => false, 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation exceptions
            $errors = $e->validator->getMessageBag();
            return response()->json(['message' => 'Validation error', 'errors' => $errors->toArray(), 'status' => false], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }


    public function resendOtp(Request $request)
    {
        try {
            $allowedFields = [
                'email',
                'phone',
            ];

            $requestFields = array_keys(request()->all());

            if (!in_array('phone', $requestFields)) {
                return response()->json([
                    'message' => 'The phone field is required.',
                    'status' => false,
                    'code' => 422,
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if (!in_array('email', $requestFields)) {
                return response()->json([
                    'message' => 'The email field is required.',
                    'status' => false,
                    'code' => 422,
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }


            // Validate the request parameters
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
                'phone' => 'required|string|min:10|max:12|unique:' . User::class,
            ]);


            // Check if validation fails
            if ($validator->fails()) {
                \Log::error('Validation failed during OTP resend: ' . json_encode($validator->errors()->toArray()));
                return response()->json(['error' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            Otp::where('email', $request->email)->where('phone', $request->phone)->delete();


            try {
                $otp = Otp::create([
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'otp_email' => $this->generateOtp(),
                    'otp_phone' => $this->generateMobileOtp(),
                ]);

                \Log::info('OTP created: ' . $otp->otp_email);
                \Log::info('Mobile OTP created: ' . $otp->otp_phone);

                // Send the OTP email
                // Mail::to($request->email)->send(new OtpMail($otp->otp_email, 'registration', $otp));

                return response()->json([
                    'status' => true,
                    'code' => 200,
                    'message' => 'OTP resent successfully',
                    'data' => [
                        'otp_email' => $otp->otp_email,
                        'otp_phone' => $otp->otp_phone,
                    ],
                ]);
            } catch (\Exception $e) {
                \Log::error('Error sending OTP: ' . $e->getMessage());
                return response()->json([
                    'status' => false,
                    'message' => 'An error occurred while sending OTP.' . $e->getMessage(),
                    'code' => 400,
                ]);
            }
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
                return response()->json([
                    'message' => 'User is already logged in',
                    'status' => false,
                    'code' => 400,], Response::HTTP_BAD_REQUEST);
            }


            // Validate the request parameters
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->errors(),
                    'status' => false,
                    'code' => 422, 兩
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Attempt to authenticate the user
            $credentials = $request->only('email', 'password');
            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'message' => 'Invalid credentials',
                    'status' => false,
                    'code' => 422
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $user = Auth::user();

            // Check if the user is verified
            if (!$user->verified) {
                Auth::logout();
                return response()->json([
                    'message' => 'Account not verified. Please verify your account.',
                    'status' => false,
                    'code' => 403
                ], Response::HTTP_FORBIDDEN);
            }

            $tokenResult = $user->createToken('AuthToken');
            $token = $tokenResult->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'User Login Successfully',
                'code' => 200,
                'data' => ([
                    'token' => $token,
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                    'role' => $user->role,
                    'phone' => $user->phone,
                    'dob' => $user->dob,
                    'city' => $user->city,
                    'state' => $user->state,
                    'qualification' => $user->qualification,
                    'gender' => $user->gender,
                    'profile_photo' => $user->profile_photo,
                    'aadhar_photo_front' => $user->aadhar_photo_front,
                    'aadhar_photo_back' => $user->aadhar_photo_back,
                    'verified' => $user->verified,
                ]),
            ], Response::HTTP_OK);
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

            return response()->json(['message' => 'Logout successful','code' => 200,'status' => true], Response::HTTP_OK);
        } catch (\Exception $e) {
            \Log::error('Error during logout: ' . $e->getMessage());
            return response()->json([
                'message' => $e->getMessage(),
                'status' => false,
                'code' => 400
            ], Response::HTTP_BAD_REQUEST);
        }
    }
    
    public function userdata(Request $request)
    {
        try {
            $user = Auth::user();
            if ($user && $user->role === 'admin') {
                $user = User::all();
            } else {
                $user = User::where('id', $user->id)->first();
            }

            return response()->json([
                "status" => true,
                "message"=> "User data fetch successfully!",
                "code"=> 200,
                "data"=> $user,
                ]);
        } catch (\Exception $e) {
            log::error('Error while fetching entries: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while processing your request.', 'code' => 500, 'status' => false, 'error' => $e->getMessage()]);
        }
    }
}
