<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReptileEntry;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Stevebauman\Location\Facades\Location;

class ReptileEntryController extends Controller
{
    public function Reptile(Request $request)
    {
        try {

            $allowedFields = [
                'caller_name',
                'caller_number',
                'caller_address',
                'caller_aadhar_number',
                'rescued_reptile_type',
                'snake',
                'venom',
                'reptile_condition',
                'reptile_description',
                'charges',
                'reptile_image',
                'ip_address',
                'latitude',
                'longitude',
            ];

            $requestFields = array_keys(request()->all());

            $unknownFields = array_diff($requestFields, $allowedFields);

            if (!empty($unknownFields)) {
                $unknownFieldsString = implode(', ', $unknownFields);

                 return response()->json(['message' => "$unknownFieldsString are unknown fields",'status' => false,'code' => 422], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validationRules = [
                'user_id' => 'nullable|exists:users,id',
                'caller_name' => 'required|string|max:255',
                'caller_number' => 'required|string|min:10|max:12',
                'caller_address' => 'required|string|max:255',
                'caller_aadhar_number' => 'required|string|min:12|max:12',
                'rescued_reptile_type' => 'required|string|max:255',
                'reptile_condition' => 'required|string|max:255',
                //'snake' => 'nullable|string|max:255',
                //'venom' => 'nullable|string|max:255',
                'reptile_description' => 'string|max:255|nullable',
                'charges' => 'required|string|max:255',
                'reptile_image' => 'required|image|max:3072',
                //'ip_address' => 'nullable|string|max:255',
                //'latitude' => 'nullable|string|max:255',
                //'longitude' => 'nullable|string|max:255',
            ];

            $validator = Validator::make(request()->all(), $validationRules);

            if ($validator->fails()) {

                return response()->json(['message' => $validator->errors(),'status' => false,'code' => 422], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $this->validate($request, $validationRules);

            $allowedImageExtensions = ['jpg', 'jpeg', 'png'];
            $uploadedImages = [];

            if ($request->hasFile('reptile_image')) {
                $reptile_image = $request->file('reptile_image');
                $imageName = time() . '.' . $reptile_image->getClientOriginalExtension();

                if (!in_array($reptile_image->getClientOriginalExtension(), $allowedImageExtensions)) {
                return response()->json(['message' => 'Reptile Image extension should be jpg, jpeg, png.', 'status' => false,'code'=> 400], Response::HTTP_BAD_REQUEST);
                }

                $path = public_path('upload/reptile');
                $reptile_image->move($path, $imageName);
                $uploadedImages['reptile_image'] = "upload/reptile/$imageName";
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
            ReptileEntry::create([
                'user_id' => $user_id,
                'caller_name' => $request->caller_name,
                'caller_number' => $request->caller_number,
                'caller_address' => $request->caller_address,
                'caller_aadhar_number' => $request->caller_aadhar_number,
                'rescued_reptile_type' => $request->rescued_reptile_type,
                'reptile_condition' => $request->reptile_condition,
                'snake' => $request->snake,
                'venom' => $request->venom,
                'reptile_description' => $request->reptile_description,
                'charges' => $request->charges,
                'reptile_image' => $uploadedImages['reptile_image'],
                'ip_address' => $userIpAddress,
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]);
            return response()->json(['message' => 'Form Submitted Successfully','code' => 200,'status' => true],Response::HTTP_OK);

        } catch (\Exception $e) {
            log::error('Error during animal entry: ' . $e->getMessage());
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
                $entries = ReptileEntry::all();
            } else {
                $entries = ReptileEntry::where('user_id', $user->id)->get();
            }

            return response()->json(['entries' => $entries,'code' => 200,'status' => true]);
        } catch (\Exception $e) {
            log::error('Error while fetching entries: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while processing your request.', 'status' => false,'code' => 500, 'error' => $e->getMessage()]);
        }
    }
}
