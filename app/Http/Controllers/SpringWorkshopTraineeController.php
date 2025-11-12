<?php

namespace App\Http\Controllers;

use App\Models\SpringWorkshopTrainee;
use Auth;
use Illuminate\Http\Request;

class SpringWorkshopTraineeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // Start building query
            $query = SpringWorkshopTrainee::query();

            //  Search by title or description
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%");
                });
            }

            //  Filter by status (draft/published)
            if ($request->has('status') && !empty($request->status)) {
                if ($request->status !== 'all') {
                    // Filter only if status is not 'all'
                    $query->where('status', $request->status);
                }
            }

            //  Pagination (optional)
            $perPage = $request->get('per_page', 5);
            $springWorkshopTrainee = $query
                ->with('speaker')
                ->latest()
                ->paginate($perPage);


            return response()->json([
                'status' => 200,
                'message' => 'SpringWorkshopTrainee fetched successfully',
                'data' => $springWorkshopTrainee,
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong while fetching SpringWorkshopTrainee.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->headers->set('Accept', 'application/json');
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'date'        => 'required|date',
            'venue'       => 'required|string',
            'duration'    => 'required|string',
            'time'        => 'required|date_format:H:i',
            'description' => 'nullable|string',
            'status'      => 'required|in:upcoming,completed,cancelled',
            'category'    => 'nullable|string',
            'speaker_id'  => 'nullable|exists:speakers,id',
        ]);

        // ✅ Step 2: Add logged-in user ID
        $validated['user_id'] = Auth::id();

        // ✅ Step 3: Create conference using mass assignment
        $conference = SpringWorkshopTrainee::create($validated);

        return response()->json([
            'message' => 'SpringWorkshopTrainee created successfully',
            'data' => $conference,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(SpringWorkshopTrainee $springWorkshopTrainee)
    {
        $springWorkshopTrainee = SpringWorkshopTrainee::findOrFail($springWorkshopTrainee->id);
        return response()->json([
            'status' => 200,
            'message' => 'SpringWorkshopTrainee fetched successfully',
            'data' => $springWorkshopTrainee,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SpringWorkshopTrainee $springWorkshopTrainee)
    {
        try {
            $validated = $request->validate([
                'title'       => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string',
                // 'author'      => 'sometimes|required|string|max:100',
            ]);

            // Ensure the logged-in user owns the conference item
            if ($springWorkshopTrainee->user_id !== Auth::id()) {
                return response()->json([
                    'status'  => 403,
                    // 'message' => 'Unauthorized to update this conference.' . Auth::id() . ' ' . $conference->user_id,
                    'message' =>  $springWorkshopTrainee ?? null,
                ], 403);
            }

            // Update only the validated fields
            $springWorkshopTrainee->update($validated);

            return response()->json([
                'status'  => 200,
                'message' => 'SpringWorkshopTrainee updated successfully',
                'SpringWorkshopTrainee' => $springWorkshopTrainee,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SpringWorkshopTrainee $springWorkshopTrainee)
    {
        //
    }
}
