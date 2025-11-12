<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // Start building query
            $query = News::query();

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
            $blogs = $query->latest()->paginate($perPage);


            return response()->json([
                'status' => 200,
                'message' => 'Blogs fetched successfully',
                'data' => $blogs,
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
        $validated = $request->validate(
            [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                // 'author' => 'required|string|max:100',

            ]
        );

        // Create a new news article
        // $news = News::create($validated);
        $news = new News();
        $news->title = $validated['title'];
        $news->description = $validated['description'];
        $news->user_id = Auth::id();
        $news->save();

        return response()->json([
            'status' => 201,
            'message' => 'News created successfully',
            'data' => $news,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(News $news)
    {
        $new = News::findOrFail($news->id);
        return response()->json([
            'status' => 200,
            'message' => 'News fetched successfully',
            'data' => $new,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, News $news)
    {

        // return response()->json([
        //     'status'  => 200,
        //     'message' => 'input data',
        //     'data'    => $request->all(),
        // ], 200);
        try {
            $validated = $request->validate([
                'title'       => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string',
                // 'author'      => 'sometimes|required|string|max:100',
            ]);

            // Ensure the logged-in user owns the news item
            if ($news->user_id !== Auth::id()) {
                return response()->json([
                    'status'  => 403,
                    // 'message' => 'Unauthorized to update this news.' . Auth::id() . ' ' . $news->user_id,
                    'message' =>  $news ?? null,
                ], 403);
            }

            // Update only the validated fields
            $news->update($validated);

            return response()->json([
                'status'  => 200,
                'message' => 'News updated successfully',
                'news'    => $news,
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
    public function destroy(News $news)
    {
        $news = News::findOrFail($news->id);
        if ($news) {
            $news->delete();
            return response()->json([
                'status' => 200,
                'message' => 'News deleted successfully',
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'News not found',
            ], 404);
        }
    }
}
