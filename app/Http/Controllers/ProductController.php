<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Helpers\HelperMethods;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function __construct()
    {
        // Apply JWT authentication middleware only to store, update, and destroy methods
        $this->middleware('auth:api')->only(['store', 'update', 'destroy']);
    }

    protected array $typeOfFields = ['textFields','imageFields'];

    protected array $textFields = [
        'name',
        'description',
        'category_id'
    ];

    protected $imageFields = ['image'];


    protected $numericFields = ['price', 'category_id'];

    /**
     * Validate the request data for Product creation or update.
     *
     * @param Request $request
     * @return array
     */
    protected function validateRequest(Request $request)
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $validated = $request->validate([
                'paginate_count' => 'nullable|integer|min:1',
                'search' => 'nullable|string|max:255',
            ]);

            $search = $validated['search'] ?? null;
            $paginate_count = $validated['paginate_count'] ?? 10;

            $query = Product::query();

            if ($search) {
                $query->where('name', 'like', $search . '%');
            }

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
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validated = $this->validateRequest($request);

            $data = new Product();

            HelperMethods::populateModelFields(
                $data,
                $request,
                $validated,
                $this->typeOfFields,
                [
                    'textFields' => $this->textFields,
                ]
            );

            $data->save();

            return response()->json([
                'success' => true,
                'message' => 'data created successfully.',
                'data' => $data,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return HelperMethods::handleException($e, 'Failed to create category.');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Product $product
     */
    public function show(Category $category)
    {
        //
    }



    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Product $data)
    {
        try {
            // Validate request
            $validated = $this->validateRequest($request);

            // Populate model fields using helper method
            HelperMethods::populateModelFields(
                $data,
                $request,
                $validated,
                $this->typeOfFields,
                [
                    'textFields' => $this->textFields,
                ]
            );

            // Save updated model
            $data->save();

            return response()->json([
                'success' => true,
                'message' => 'Data updated successfully.',
                'data' => $data,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return HelperMethods::handleException($e, 'Failed to update Data.');
        }
    }


    public function destroy(Product $data)
    {
        try {
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