<?php

namespace App\Http\Controllers;

use App\Models\conference;
use Auth;
use Illuminate\Http\Request;

class ConferenceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // $query = Conference::query();

        try {
            // Start building query
            $query = Conference::query();

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
            $conferenceAndseminar = $query
                ->with('speaker')
                ->latest()
                ->paginate($perPage);


            return response()->json([
                'status' => 200,
                'message' => 'conferenceAndseminar fetched successfully',
                'data' => $conferenceAndseminar,
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong while fetching conferenceAndseminar.',
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
            'time'        => 'required|date_format:H:i',
            'description' => 'nullable|string',
            'status'      => 'required|in:upcoming,completed,cancelled',
            'category'    => 'nullable|string',
            'speaker_id'  => 'nullable|exists:speakers,id',
        ]);

        // ✅ Step 2: Add logged-in user ID
        $validated['user_id'] = Auth::id();

        // ✅ Step 3: Create conference using mass assignment
        $conference = Conference::create($validated);

        return response()->json([
            'message' => 'Conference created successfully',
            'data' => $conference,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(conference $conference)
    {
        $conference = Conference::findOrFail($conference->id);
        return response()->json([
            'status' => 200,
            'message' => 'Conference fetched successfully',
            'data' => $conference,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, conference $conference)
    {
        try {
            $validated = $request->validate([
                'title'       => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string',
                // 'author'      => 'sometimes|required|string|max:100',
            ]);

            // Ensure the logged-in user owns the conference item
            if ($conference->user_id !== Auth::id()) {
                return response()->json([
                    'status'  => 403,
                    // 'message' => 'Unauthorized to update this conference.' . Auth::id() . ' ' . $conference->user_id,
                    'message' =>  $conference ?? null,
                ], 403);
            }

            // Update only the validated fields
            $conference->update($validated);

            return response()->json([
                'status'  => 200,
                'message' => 'News updated successfully',
                'news'    => $conference,
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
    public function destroy(conference $conference)
    {
        if ($conference->user_id !== Auth::id()) {
            return response()->json([
                'status' => 403,
                'message' => 'Unauthorized to delete this conference.'
            ], 403);
        }
        $conference->delete();
        return response()->json([
            'status' => 200,
            'message' => 'Conference deleted successfully.'
        ], 200);
    }
}
