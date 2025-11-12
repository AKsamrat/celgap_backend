<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BlogController extends Controller
{
    /**
     * Display a listing of all blogs.
     */
    public function index(Request $request)
    {
        try {
            // Start building query
            $query = Blog::query();

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
                // If status = all, do nothing (it will fetch all blogs)
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
     * Store a newly created blog post.  //01738450807
     */
    public function store(Request $request)
    {
        try {

            // Validate input data
            $validated = $request->validate([
                'title'       => 'required|string|max:255',
                'description' => 'required|string',
                'author'      => 'required|string|max:100',
                'status'      => 'nullable|in:draft,published',
                'image'       => 'nullable|image|mimes:jpg,jpeg,png,gif,svg|max:2048',
            ]);

            // Handle optional image upload
            if ($request->hasFile('image')) {
                $imageName = time() . '_' . $request->file('image')->getClientOriginalName();
                $request->file('image')->move(public_path('images/blogs'), $imageName);
                $validated['image'] = 'images/blogs/' . $imageName; // store path in DB
            }

            // Add authenticated user ID (Sanctum user)
            $validated['user_id'] = Auth::id();

            // Create new blog
            $blog = Blog::create($validated);

            return response()->json([
                'status'  => 201,
                'message' => 'Blog created successfully',
                'blog'    => $blog,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    //for get all blogs of logged in user

    public function userBlogs()
    {
        $userId = Auth::id(); // get currently logged-in user's ID

        $blogs = Blog::where('user_id', $userId)
            ->latest()
            ->get();

        return response()->json([
            'status' => 200,
            'message' => 'User blogs fetched successfully',
            'blogs' => $blogs,
        ]);
    }

    /**
     * Display a single blog post.
     */
    public function show(Blog $id)
    {
        return response()->json($id);
    }

    /**
     * Update an existing blog post.
     */
    public function update(Request $request, $id)
    {
        try {

            $validated = $request->validate([
                'title'       => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string',
                'author'      => 'sometimes|required|string|max:100',
                'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'status'      => 'nullable|in:draft,published',
            ]);


            $blog = Blog::where('user_id', Auth::id())->findOrFail($id);


            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($blog->image && file_exists(public_path($blog->image))) {
                    unlink(public_path($blog->image));
                }

                // Upload new image
                $imageName = time() . '_' . $request->image->getClientOriginalName();
                $request->image->move(public_path('images/blogs'), $imageName);
                $validated['image'] = 'images/blogs/' . $imageName;
            }

            // 4️⃣ Update blog
            $blog->update($validated);

            // 5️⃣ Return success response
            return response()->json([
                'status'  => 200,
                'message' => 'Blog updated successfully',
                'blog'    => $blog,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => 404,
                'message' => 'Blog not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Remove the specified blog from storage.
     */
    public function destroy($id)
    {
        try {
            $blog = Blog::find($id);

            if (!$blog) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Blog not found',
                ], 404);
            }
            if ($blog->image && file_exists(public_path($blog->image))) {
                unlink(public_path($blog->image));
            }

            $blog->delete();

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


    //update blog status
    public function updateStatus(Request $request, $id)
    {
        try {
            $blog = Blog::findOrFail($id);

            $request->validate([
                'status' => 'required|in:draft,published',
            ]);

            $blog->status = $request->status;
            $blog->save();

            return response()->json([
                'status' => 200,
                'message' => 'Blog status updated successfully',
                'blog' => $blog,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to update blog status',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
