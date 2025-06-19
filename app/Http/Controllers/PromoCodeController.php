<?php

namespace App\Http\Controllers;

use App\Helpers\HelperMethods;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PromoCodeController extends Controller
{
    protected array $typeOfFields = ['textFields', 'numericFields'];

    protected array $textFields = [
        'name',
        'description',
        'type',
        'status',
    ];


    protected array $numericFields = [
        'usage_limit',
        'amount'
    ];

    protected function validateRequest(Request $request)
    {

        return $request->validate([
            // 🔤 Text Fields
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string', // customize as needed
            'status' => 'required|string', // customize as needed

            // 🔢 Numeric Fields
            'usage_limit' => 'nullable|integer|min:0',
            'amount' => 'nullable|integer|min:0',
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
                'search' => 'nullable|string|max:255',
                'for' => 'nullable|string|max:255',
                'status' => 'nullable|string|max:255',
            ]);
            // return 'ok';
            $search = $validated['search'] ?? null;
            $paginate_count = $validated['paginate_count'] ?? 10;
            $for = $validated['for'] ?? null;
            $status = $validated['status'] ?? null;

            $query = PromoCode::withCount('orders');

            if ($search) {
                if ($for == "use_in_order") {

                    $query->where('name', $search); // Exact match

                } else {
                    $query->where('name', 'like', $search . '%');
                }
            }
            if($status){
                 $query->where('status', $status);
            }

            $categories = $query->paginate($paginate_count);

            return response()->json([
                'success' => true,
                'data' => $categories,
                'current_page' => $categories->currentPage(),
                'total_pages' => $categories->lastPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return HelperMethods::handleException($e, 'Failed to fetch categories.');
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

            $data = new PromoCode();

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
                'message' => 'Category created successfully.',
                'data' => $data,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return HelperMethods::handleException($e, 'Failed to create category.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PromoCode $promoCode)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PromoCode $promoCode)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            // Validate request
            $validated = $this->validateRequest($request);
            $data = PromoCode::find($id);


            // Populate model fields using helper method
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

            // Save updated model
            $data->save();

            return response()->json([
                'success' => true,
                'message' => 'data updated successfully.',
                'data' => $data,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return HelperMethods::handleException($e, 'Failed to update category.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $promoCode = PromoCode::findOrFail($id);

            // Attempt to delete the category
            $promoCode->delete();

            return response()->json([
                'success' => true,
                'message' => 'data deleted successfully',
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return HelperMethods::handleException($e, 'Failed to delete data.');
        }
    }
    public function stats()
    {
        $activePromocodeCount = PromoCode::where('status', 'active')->count();
        $inactivePromocodeCount = PromoCode::where('status', 'inactive')->count();

        return response()->json([
            'active' => $activePromocodeCount,
            'inactive' => $inactivePromocodeCount,
        ]);
    }
}
