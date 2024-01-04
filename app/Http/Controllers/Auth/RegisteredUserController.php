<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['required', 'string', 'min:10', 'max:12'],
            'dob' => ['required', 'date'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'qualification' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:male,female,other'],
            'profile_photo' => ['required', 'image', 'max:3072'],
            'aadhar_photo_front' => ['required', 'image', 'max:3072'],
            'aadhar_photo_back' => ['required', 'image', 'max:3072'],
        ]);

        $allowedImageExtensions = ['jpg', 'jpeg', 'png'];
        $uploadedImages = [];

        foreach (['profile_photo', 'aadhar_photo_back', 'aadhar_photo_front'] as $imageField) {
            if ($request->hasFile($imageField)) {
                $file = $request->file($imageField);
                $imageName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $imageExtension = $file->getClientOriginalExtension();

                if (!in_array($imageExtension, $allowedImageExtensions)) {
                    session()->flash('fail', 'Image extension should be jpg, jpeg, png.');
                    return redirect()->back();
                }

                $imageName = $imageName . '_' . time() . '.' . $imageExtension;
                $file->move(public_path('uploaded'), $imageName);
                $uploadedImages[$imageField] = 'uploaded/' . $imageName;
            }
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

        return redirect(RouteServiceProvider::HOME);
    }
}
