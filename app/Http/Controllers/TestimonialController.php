<?php
namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class TestimonialController extends Controller
{
    /**
     * Display approved testimonials
     */
    public function index(): JsonResponse
    {
        $testimonials = Testimonial::approved()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $testimonials
        ]);
    }

    /**
     * Store a new testimonial
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'role' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $testimonial = Testimonial::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'message' => $request->message,
            'rating' => $request->rating,
            'status' => 'pending'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your testimonial! It will be reviewed and published soon.',
            'data' => $testimonial
        ], 201);
    }

    /**
     * Admin: Get all testimonials with status filter
     */
    public function adminIndex(Request $request): JsonResponse
    {
        $status = $request->query('status', 'all');
        
        $query = Testimonial::orderBy('created_at', 'desc');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $testimonials = $query->get();

        return response()->json([
            'success' => true,
            'data' => $testimonials
        ]);
    }

    /**
     * Admin: Update testimonial status
     */
    public function updateStatus(Request $request, Testimonial $testimonial): JsonResponse
    {
        $request->validate([
        'status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
        'rating' => ['nullable', 'integer', 'min:1', 'max:5']
    ]);

    $testimonial->update([
        'status' => $request->status,
        'rating' => $request->rating ?? $testimonial->rating
    ]);


        return response()->json([
            'success' => true,
            'message' => 'Testimonial status updated successfully',
            'data' => $testimonial
        ]);
    }

    /**
     * Admin: Delete testimonial
     */
    public function destroy(Testimonial $testimonial): JsonResponse
    {
        $testimonial->delete();

        return response()->json([
            'success' => true,
            'message' => 'Testimonial deleted successfully'
        ]);
    }
}