<?php

namespace App\Http\Controllers;

use App\Helpers\HelperMethods;
use App\Models\RatingnReviews;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RatingnReviewsController extends Controller
{
    public function __construct()
    {
        // Apply JWT authentication and admin middleware only to store, update, and destroy methods
        $this->middleware(['auth:api', 'admin'])->only(['store', 'update', 'destroy']);
    }

    protected array $typeOfFields = ['textFields', 'numericFields'];

    protected array $textFields = [
        'user_id',
        'comment',
    ];


    protected $numericFields = ['product_id', 'rating'];

    /**
     * Validate the request data for Product creation or update.
     *
     * @param Request $request
     * @return array
     */
    protected function validateRequest(Request $request)
    {
        return $request->validate([
            'comment' => 'required|string|max:255',
            'product_id' => 'required|integer|min:0',
            'user_id' => 'required|integer|min:0',
            'rating' => 'required|integer|min:0',
        ]);
    }

    /**
     * Display a listing of the resource.
     */
   public function index(Request $request)
    {
        try {
            $validated = $request->validate([
                'paginate_count' => 'nullable|integer|min:1',
            ]);

           
            $paginate_count = $validated['paginate_count'] ?? 10;

            $query = RatingnReviews::query();

            $data = $query->paginate($paginate_count);

            return response()->json([
                'success' => true,
                'data' => $data,
                'current_page' => $data->currentPage(),
                'total_pages' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return HelperMethods::handleException($e, 'Failed to fetch data.');
        }
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(RatingnReviews $ratingnReviews)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RatingnReviews $ratingnReviews)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RatingnReviews $ratingnReviews)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $data = RatingnReviews::findOrFail($id);

            // Attempt to delete the category
            $data->delete();

            return response()->json([
                'success' => true,
                'message' => 'data deleted successfully',
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return HelperMethods::handleException($e, 'Failed to delete data.');
        }
    }
}
