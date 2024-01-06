<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AnimalEntry;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Stevebauman\Location\Facades\Location;

class AnimalEntryController extends Controller
{
    public function Animal(Request $request)
    {
        try {

            $allowedFields = [
                'caller_name',
                'caller_number',
                'caller_address',
                'caller_aadhar_number',
                'rescued_animal_type',
                'animal_condition',
                'animal_sex',
                'animal_description',
                'charges',
                'animal_image',
                'ip_address',
                'latitude',
                'longitude',
            ];

            $requestFields = array_keys(request()->all());

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

            $validationRules = [
                'user_id' => 'nullable|exists:users,id',
                'caller_name' => 'required|string|max:255',
                'caller_number' => 'required|string|min:10|max:12',
                'caller_address' => 'required|string|max:255',
                'caller_aadhar_number' => 'required|string|min:12|max:12',
                'rescued_animal_type' => 'required|string|max:255',
                'animal_condition' => 'required|string|max:255',
                'animal_sex' => 'in:Male,Female|nullable',
                'animal_description' => 'string|max:255|nullable',
                'charges' => 'required|string|max:255',
                'animal_image' => 'required|image|max:3072',
                'ip_address' => 'nullable|string|max:255',
                'latitude' => 'nullable|string|max:255',
                'longitude' => 'nullable|string|max:255',
            ];

            $validator = Validator::make(request()->all(), $validationRules);

            if ($validator->fails()) {

                return response()->json(['error' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $this->validate($request, $validationRules);

            $allowedImageExtensions = ['jpg', 'jpeg', 'png'];
            $uploadedImages = [];

            if ($request->hasFile('animal_image')) {
                $animal_image = $request->file('animal_image');
                $imageName = time() . '.' . $animal_image->getClientOriginalExtension();

                if (!in_array($animal_image->getClientOriginalExtension(), $allowedImageExtensions)) {
                    return response()->json(['message' => 'Animal Image extension should be jpg, jpeg, png.', 'status' => false], 400);
                }

                $path = public_path('upload/animal');
                $animal_image->move($path, $imageName);
                $uploadedImages['animal_image'] = "upload/animal/$imageName";
            }

            $userIpAddress = $request->ip();
            $location = Location::get($userIpAddress);

            if ($location && $location->latitude && $location->longitude) {
                $latitude = $location->latitude;
                $longitude = $location->longitude;
            } else {
                $latitude = $request->latitude ?? 0.0;
                $longitude = $request->longitude ?? 0.0;
            }

            $user_id = Auth::check() ? Auth::id() : null;
            AnimalEntry::create([
                'user_id' => $user_id,
                'caller_name' => $request->caller_name,
                'caller_number' => $request->caller_number,
                'caller_address' => $request->caller_address,
                'caller_aadhar_number' => $request->caller_aadhar_number,
                'rescued_animal_type' => $request->rescued_animal_type,
                'animal_condition' => $request->animal_condition,
                'animal_sex' => $request->animal_sex,
                'animal_description' => $request->animal_description,
                'charges' => $request->charges,
                'animal_image' => $uploadedImages['animal_image'],
                'ip_address' => $userIpAddress,
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]);

            return response()->json(['message' => 'Animal Entry Added successfully'], 200);
        } catch (\Exception $e) {
            log::error('Error during animal entry: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while processing your request.', 'status' => false, 'error' => $e->getMessage()], 500);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->getMessageBag();
            return response()->json(['message' => 'Validation error', 'errors' => $errors->toArray(), 'status' => false], 422);
        }
    }

    public function getAllEntries(Request $request)
    {
        try {
            $user = Auth::user();
            if ($user && $user->role === 'admin') {
                $entries = AnimalEntry::all();
            } else {
                $entries = AnimalEntry::where('user_id', $user->id)->get();
            }

            return response()->json(['entries' => $entries], 200);
        } catch (\Exception $e) {
            log::error('Error while fetching entries: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while processing your request.', 'status' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
