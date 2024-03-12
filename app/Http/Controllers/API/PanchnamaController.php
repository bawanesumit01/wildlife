<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Panchnama;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Stevebauman\Location\Facades\Location;

class PanchnamaController extends Controller
{
    public function Panchnama(Request $request)
    {
        try {

            $allowedFields = [
                'rescurer_name',
                'forest_department_name',
                'date',
                'location',
                'staff_name',
                'description',
                'panchnama_image',
             //   'ip_address',
              //  'latitude',
              //  'longitude',
            ];

            $requestFields = array_keys(request()->all());

            $unknownFields = array_diff($requestFields, $allowedFields);

            if (!empty($unknownFields)) {

                $unknownFieldsString = implode(', ', $unknownFields);
                return response()->json(['message' => "$unknownFieldsString are unknown fields",'status' => false,'code' => 422], Response::HTTP_UNPROCESSABLE_ENTITY);
            
            }

            $validationRules = [
                'user_id' => 'nullable|exists:users,id',
                'rescurer_name' => 'required|string|max:255',
                'forest_department_name' => 'required|string|max:255',
                'date' => 'required|date',
                'location' => 'required|string|max:255',
                'staff_name' => 'required|string|max:255',
                'description' => 'string|max:255|nullable',
                'panchnama_image' => 'required|image|max:3072',
            //    'ip_address' => 'nullable|string|max:255',
            //    'latitude' => 'nullable|string|max:255',
             //   'longitude' => 'nullable|string|max:255',
            ];

            $validator = Validator::make(request()->all(), $validationRules);

            if ($validator->fails()) {

                return response()->json(['message' => $validator->errors(),'status' => false,'code'=> 422], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $this->validate($request, $validationRules);

            $allowedImageExtensions = ['jpg', 'jpeg', 'png'];
            $uploadedImages = [];

            if ($request->hasFile('panchnama_image')) {
                $panchnama_image = $request->file('panchnama_image');
                $imageName = time() . '.' . $panchnama_image->getClientOriginalExtension();

                if (!in_array($panchnama_image->getClientOriginalExtension(), $allowedImageExtensions)) {
                    return response()->json(['message' => 'Animal Image extension should be jpg, jpeg, png.', 'status' => false,'code'=> 400], Response::HTTP_BAD_REQUEST);
                }

                $path = public_path('upload/panchnama');
                $panchnama_image->move($path, $imageName);
                $uploadedImages['panchnama_image'] = "upload/panchnama/$imageName";
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
            Panchnama::create([
                'user_id' => $user_id,
                'rescurer_name' => $request->rescurer_name,
                'forest_department_name' => $request->forest_department_name,
                'date' => $request->date,
                'location' => $request->location,
                'staff_name' => $request->staff_name,
                'description' => $request->description,
                'panchnama_image' => $uploadedImages['panchnama_image'],
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
                $entries = Panchnama::all();
            } else {
                $entries = Panchnama::where('user_id', $user->id)->get();
            }

            return response()->json(['entries' => $entries, 'status' => true, 'code' => 200]);
        } catch (\Exception $e) {
            log::error('Error while fetching entries: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while processing your request.', 'code' => 500, 'status' => false, 'error' => $e->getMessage()]);
        }
    }
}
