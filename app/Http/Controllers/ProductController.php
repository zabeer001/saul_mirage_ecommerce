<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Helpers\HelperMethods;
use PhpParser\Node\NullableType;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function __construct()
    {
        // Apply JWT authentication and admin middleware only to store, update, and destroy methods
        $this->middleware(['auth:api', 'admin'])->only(['store', 'update', 'destroy']);
    }

    protected array $typeOfFields = ['textFields', 'imageFields', 'numericFields'];

    protected array $textFields = [
        'name',
        'description',
        'stock_quantity',
        'status'
    ];

    protected $imageFields = ['image'];


    protected $numericFields = ['price', 'category_id', 'cost_price','stock_quantity'];

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
        'status' => 'required|string|max:255',
        'description' => 'nullable|string',
        'image' => 'nullable|max:2048', // probably should add 'image' rule if it's an image file
        'images' => 'nullable|array',
        'images.*' => 'nullable|max:2048', // also consider adding 'image' here if these are files
        'price' => 'required|integer|min:0',
        'cost_price' => 'required|integer|min:0',
        'stock_quantity' => 'nullable|integer',
        'category_id' => 'nullable|exists:categories,id',
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

            $query = Product::with([
                'media:id,product_id,file_path',
                'category:id,name'
            ]);

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
                    'numericFields' => $this->numericFields,
                    'imageFields' => $this->imageFields,
                    'textFields' => $this->textFields,
                ]
            );
            $data->save();

            $data->save();

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $newImagePath = HelperMethods::uploadImage($image); // handles upload and returns path

                    Media::create([
                        'product_id' => $data->id,
                        'file_path' => $newImagePath,

                        // Add more fields if needed
                    ]);
                }
            }
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




    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Product $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            // Validate request
            $validated = $this->validateRequest($request);
            $data = Product::findOrFail($id);

            // Populate model fields using helper method
            HelperMethods::populateModelFields(
                $data,
                $request,
                $validated,
                $this->typeOfFields,
                [
                    'numericFields' => $this->numericFields,
                    'imageFields' => $this->imageFields,
                    'textFields' => $this->textFields,
                ]
            );

            // Save updated model
            $data->save();

            // Handle image uploads if present
            if ($request->hasFile('images')) {
                // Delete old images
                $oldImages = Media::where('product_id', $data->id)->get();
                foreach ($oldImages as $oldImage) {
                    HelperMethods::deleteImage($oldImage->file_path); // Delete image file
                    $oldImage->delete(); // Delete media record
                }

                // Upload new images
                foreach ($request->file('images') as $image) {
                    $newImagePath = HelperMethods::uploadImage($image); // Handles upload and returns path
                    if ($newImagePath) {
                        Media::create([
                            'product_id' => $data->id,
                            'file_path' => $newImagePath,
                            // Add more fields if needed
                        ]);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Data updated successfully.',
                'data' => $data,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return HelperMethods::handleException($e, 'Failed to update data.');
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