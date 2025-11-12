<?php

namespace App\Http\Controllers;

use App\Models\Speaker;
use Auth;
use Illuminate\Http\Request;

class SpeakerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {


        try {
            // Start building query
            $query = Speaker::query();

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
            $speakers = $query->latest()->paginate($perPage);


            return response()->json([
                'status' => 200,
                'message' => 'Speakers fetched successfully',
                'data' => $speakers,
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong while fetching blogs.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {


        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'organization' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            // 'photo' => 'nullable|image|mimes:jpg,jpeg,png,gif,svg|max:4048',
            'topic' => 'nullable|string|max:255',
            // 'conference_id' => 'required|exists:conferences,id',
        ]);

        if ($request->hasFile('photo')) {
            $imageName = time() . '_' . $request->file('photo')->getClientOriginalName();
            $request->file('photo')->move(public_path('images/speaker'), $imageName);
            $validated['photo'] = 'images/speaker/' . $imageName; // store path in DB
        }
        // return response()->json([
        //     'message' => 'Speaker added successfully',
        //     'data' => $request->all(),
        // ]);
        $speaker = new Speaker();
        $speaker->name = $validated['name'];
        $speaker->designation = $validated['designation'];
        $speaker->organization = $validated['organization'];
        $speaker->bio = $validated['bio'];
        $speaker->photo = $validated['photo'] ?? null;
        $speaker->topic = $validated['topic'];
        // $speaker->user_id = Auth::id();
        // $speaker->conference_id = $request->conference_id ?? 1;
        $speaker->save();

        return response()->json([
            'status' => 201,
            'message' => 'Speaker added successfully',
            'data' => $speaker,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Speaker $speaker)
    {
        $conference = Speaker::findOrFail($speaker->id);
        return response()->json([
            'status' => 200,
            'message' => 'Conference fetched successfully',
            'data' => $speaker,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Speaker $speaker)
    {
        $speaker = Speaker::findOrFail($speaker->id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'designation' => 'nullable|string|max:255',
            'organization' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'photo' => 'nullable|string',
            'topic' => 'nullable|string|max:255',
            'conference_id' => 'sometimes|exists:conferences,id',
        ]);
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($speaker->image && file_exists(public_path($speaker->image))) {
                unlink(public_path($speaker->image));
            }

            // Upload new image
            $imageName = time() . '_' . $request->image->getClientOriginalName();
            $request->image->move(public_path('images/speakers'), $imageName);
            $validated['image'] = 'images/speakers/' . $imageName;
        }

        $speaker->update($validated);

        return response()->json([
            'message' => 'Speaker updated successfully',
            'data' => $speaker,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Speaker $speaker)
    {
        try {
            $speaker = Speaker::findOrFail($speaker->id);

            if (!$speaker) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Speaker not found',
                ], 404);
            }
            // if ($speaker->user_id !== Auth::id()) {
            //     return response()->json([
            //         'status' => 403,
            //         'message' => 'Unauthorized to delete this speaker.'
            //     ], 403);
            // }
            if ($speaker->image && file_exists(public_path($speaker->image))) {
                unlink(public_path($speaker->image));
            }

            $speaker->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Blog deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong while deleting the blog',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
