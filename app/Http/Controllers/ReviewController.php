<?php

namespace App\Http\Controllers;

use App\Helpers\HelperMethods;
use App\Models\Customer;
use App\Models\Review;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;


class ReviewController extends Controller
{

    public function __construct()
    {
        // Apply JWT authentication and admin middleware only to store, update, and destroy methods
        $this->middleware(['auth:api', 'admin'])->only(['destroy']);
    }

    protected array $typeOfFields = ['textFields', 'numericFields'];

    protected array $textFields = [
        'comment',
    ];


    protected $numericFields = ['product_id', 'rating'];


    protected function validateRequest(Request $request)
    {
        return $request->validate([
            'product_id' => 'required|exists:products,id',
            'comment' => 'required|string',
            'rating' => 'required|numeric|min:1|max:5',
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

            $query = Review::with(['user', 'product']);

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
        try {

            $validated = $this->validateRequest($request);
            
            $user = JWTAuth::parseToken()->authenticate();
            $productId = $request->product_id;

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated.',
                ], 401);
            }

            $customer = Customer::where('email', $user->email)->first();

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found.',
                ], 404);
            }

            // Get the last order where product_id matches
            $order = $customer->orders()
                ->where('product_id', $productId)
                ->latest()
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have no order for this product, so you cannot make a review.',
                ], 403);
            }
            $data = new Review();

            HelperMethods::populateModelFields(
                $data,
                $request,
                $validated,
                $this->typeOfFields,
                [
                    'textFields' => $this->textFields,
                    'numericFields' => $this->numericFields,
                ]
            );

            $data->save();

            return response()->json([
                'success' => true,
                'message' => 'Review created successfully.',
                'data' => $data,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return HelperMethods::handleException($e, 'Failed to create review.');
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Review $review)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Review $review)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Review $review)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $review = Review::findOrFail($id);

            // Attempt to delete the category
            $review->delete();

            return response()->json([
                'success' => true,
                'message' => 'data deleted successfully',
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return HelperMethods::handleException($e, 'Failed to delete data.');
        }
    }
}
