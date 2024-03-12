<?php

namespace App\Http\Controllers;

use App\Models\AnimalList;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnimalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $status = Status::all();
        $animalList = AnimalList::all();
        return view('animal.index', compact('status', 'animalList'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request);
        // die();
        try {
            AnimalList::create([
                'animal_name' => $request->input('animal_name'),
                'status_id' => $request->input('status_id')
            ]);

            return redirect()->route('add-animal')->with('success', 'Animal Added successfully');
        } catch (\Exception $exception) {
            return redirect()->route('add-animal')->with('error', 'Error adding animal: ' . $exception->getMessage());
        }
    }

    public function updateStatus(Request $request)
    {
        DB::beginTransaction();

        try {
            // Update the status of the animal
            AnimalList::where('id', $request->ID)->update([
                'status_id' => $request->status_id
            ]);

            DB::commit();

            return response()->json('success');
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json('fail');
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $animal = AnimalList::find($id);
        $animal->delete();
        return redirect()->back()->with('delete', 'Deleted Animal From List successfully!');
    }
}
