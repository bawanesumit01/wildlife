<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SnakeBite;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Stevebauman\Location\Facades\Location;

class SnakeBiteController extends Controller
{
    public function SnakeBite(Request $request)
    {
        try {

            $allowedFields = [
                'patient_name',
                'patient_number',
                'patient_address',
                'admit_date',
                'discharge_date',
                'patient_status',
                'snake_type',
                'snake_species',
                'hospital_name',
                'description',
                'patient_image',
                //'ip_address',
                //'latitude',
               // 'longitude',
            ];

            $requestFields = array_keys(request()->all());

            $unknownFields = array_diff($requestFields, $allowedFields);

            if (!empty($unknownFields)) {
                
                 $unknownFieldsString = implode(', ', $unknownFields);
                return response()->json(['message' => "$unknownFieldsString are unknown fields",'status' => false,'code' => 422], Response::HTTP_UNPROCESSABLE_ENTITY);
            
            }

            $validationRules = [
                'user_id' => 'nullable|exists:users,id',
                'patient_name' => 'required|string|max:255',
                'patient_number' => 'required|string|min:10|max:12',
                'patient_address' => 'required|string|max:255',
                'admit_date' => 'required|date',
                'discharge_date' => 'required|date',
                'patient_status' => 'required|string|max:255',
                'snake_type' => 'required|string',
                'snake_species' => 'nullable|string',
                'hospital_name' => 'required|string|max:1000',
                'description' => 'required|string|max:10000',
                'patient_image' => 'required|image|max:3072',
                //'ip_address' => 'nullable|string|max:255',
               // 'latitude' => 'nullable|string|max:255',
               // 'longitude' => 'nullable|string|max:255',
            ];

            $validator = Validator::make(request()->all(), $validationRules);

            if ($validator->fails()) {

                return response()->json(['message' => $validator->errors(),'status' => false,'code'=> 422], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $this->validate($request, $validationRules);

            $allowedImageExtensions = ['jpg', 'jpeg', 'png'];
            $uploadedImages = [];

            if ($request->hasFile('patient_image')) {
                $patient_image = $request->file('patient_image');
                $imageName = time() . '.' . $patient_image->getClientOriginalExtension();

                if (!in_array($patient_image->getClientOriginalExtension(), $allowedImageExtensions)) {
                    return response()->json(['message' => 'Animal Image extension should be jpg, jpeg, png.', 'status' => false,'code'=> 400], Response::HTTP_BAD_REQUEST);
                }

                $path = public_path('upload/patient');
                $patient_image->move($path, $imageName);
                $uploadedImages['patient_image'] = "upload/patient/$imageName";
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
            SnakeBite::create([
                'user_id' => $user_id,
                'patient_name' => $request->patient_name,
                'patient_number' => $request->patient_number,
                'patient_address' => $request->patient_address,
                'admit_date' => $request->admit_date,
                'discharge_date' => $request->discharge_date,
                'patient_status' => $request->patient_status,
                'snake_type' => $request->snake_type,
                'snake_species' => $request->snake_species,
                'hospital_name' => $request->hospital_name,
                'description' => $request->description,
                'patient_image' => $uploadedImages['patient_image'],
                'ip_address' => $userIpAddress,
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]);

            return response()->json(['message' => 'Form Submitted Successfully','code' => 200,'status' => true],Response::HTTP_OK);
        } catch (\Exception $e) {
            log::error('Error during form Submitting: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage(),'code' => 500,'status' => false],Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->getMessageBag();
            return response()->json(['message' =>  $errors->toArray(),'code' => 422,'status' => false], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function getAllEntries(Request $request)
    {
        try {
            $user = Auth::user();
            if ($user && $user->role === 'admin') {
                $entries = SnakeBite::all();
            } else {
                $entries = SnakeBite::where('user_id', $user->id)->get();
            }

            return response()->json(['entries' => $entries, 'status' => true, 'code' => 200]);
        } catch (\Exception $e) {
            log::error('Error while fetching entries: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while processing your request.', 'code' => 500, 'status' => false, 'error' => $e->getMessage()]);
        }
    }
}
