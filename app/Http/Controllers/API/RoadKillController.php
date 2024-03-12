<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RoadKill;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Stevebauman\Location\Facades\Location;

class RoadKillController extends Controller
{
    public function Roadkill(Request $request)
    {
        try {

            $allowedFields = [
                'user_id',
                'rescuer_name',
                'rescued_type',
                'description',
                'image',
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

            $rescuedType = strtolower($request->rescued_type);

            $validationRules = [
                'user_id' => 'nullable|exists:users,id',
                'rescuer_name' => 'required|string|max:255',
                'rescued_type' => 'in:animal,reptile|required|string|max:255',
                'description' => 'string|max:255',
                'image' => 'required|image|max:3072',
                'ip_address' => 'nullable|string|max:255',
                'latitude' => 'nullable|string|max:255',
                'longitude' => 'nullable|string|max:255',
            ];
            
            $request->merge(['rescued_type' => $rescuedType]);

            $validator = Validator::make(request()->all(), $validationRules);

            if ($validator->fails()) {

                return response()->json(['error' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $this->validate($request, $validationRules);

            $allowedImageExtensions = ['jpg', 'jpeg', 'png'];
            $uploadedImages = [];

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();

                if (!in_array($image->getClientOriginalExtension(), $allowedImageExtensions)) {
                    return response()->json(['message' => 'RoadKill Image extension should be jpg, jpeg, png.', 'status' => false, 'code' => 400]);
                }

                $path = public_path('upload/roadkill');
                $image->move($path, $imageName);
                $uploadedImages['image'] = "upload/roadkill/$imageName";
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
            RoadKill::create([
                'user_id' => $user_id,
                'rescuer_name' => $request->rescuer_name,
                'rescued_type' => $request->rescued_type,
                'description' => $request->description,
                'image' => $uploadedImages['image'],
                'ip_address' => $userIpAddress,
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]);

            return response()->json(['message' => 'Form Submitted Successfully', 'code' => 200, 'status' => true]);
        } catch (\Exception $e) {
            log::error('Error during form Submitting: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while processing your request.', 'code' => 500, 'status' => false, 'error' => $e->getMessage()]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->getMessageBag();
            return response()->json(['message' => 'Validation error', 'code' => 422, 'errors' => $errors->toArray(), 'status' => false]);
        }
    }

    public function getAllEntries(Request $request)
    {
        try {
            $user = Auth::user();
            if ($user && $user->role === 'admin') {
                $entries = RoadKill::all();
            } else {
                $entries = RoadKill::where('user_id', $user->id)->get();
            }

            return response()->json(['entries' => $entries, 'status' => true, 'code' => 200]);
        } catch (\Exception $e) {
            log::error('Error while fetching entries: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while processing your request.', 'code' => 500, 'status' => false, 'error' => $e->getMessage()]);
        }
    }
}
