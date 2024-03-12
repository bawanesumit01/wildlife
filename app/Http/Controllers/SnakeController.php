<?php

namespace App\Http\Controllers;

use App\Models\SnakeSpecies;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SnakeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $status = Status::all();
        $snakeSpecies = SnakeSpecies::all();
        return view('snake.index', compact('status', 'snakeSpecies'));
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
            SnakeSpecies::create([
                'snake_species_name' => $request->input('snake_species_name'),
                'status_id' => $request->input('status_id')
            ]);

            return redirect()->route('add-snake')->with('success', 'Snake Added successfully');
        } catch (\Exception $exception) {
            return redirect()->route('add-snake')->with('error', 'Error adding snake: ' . $exception->getMessage());
        }
    }

    public function updateStatus(Request $request)
    {
        DB::beginTransaction();

        try {
            // Update the status of the animal
            SnakeSpecies::where('id', $request->ID)->update([
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
        $snake = SnakeSpecies::find($id);
        $snake->delete();
        return redirect()->back()->with('delete', 'Deleted Snake From List successfully!');
    }
}
