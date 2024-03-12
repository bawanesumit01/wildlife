<?php

namespace App\Http\Controllers;

use App\Models\ReptileList;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReptileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $status = Status::all();
        $reptileList = ReptileList::all();
        return view('reptile.index', compact('status', 'reptileList'));
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
        try {
            ReptileList::create([
                'reptile_name' => $request->input('reptile_name'),
                'status_id' => $request->input('status_id')
            ]);

            return redirect()->route('add-reptile')->with('success', 'Reptile Added successfully');
        } catch (\Exception $exception) {
            return redirect()->route('add-reptile')->with('error', 'Error adding reptile: ' . $exception->getMessage());
        }
    }

    public function updateStatus(Request $request)
    {
        DB::beginTransaction();

        try {
            // Update the status of the animal
            ReptileList::where('id', $request->ID)->update([
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
        $reptile = ReptileList::find($id);
        $reptile->delete();
        return redirect()->back()->with('delete', 'Deleted Reptile From List successfully!');
    }
}
