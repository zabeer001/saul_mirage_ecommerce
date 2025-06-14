<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Helpers\HelperMethods;
use App\Models\Customer;
use App\Models\Order;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class OrderController extends Controller
{
    public function __construct()
    {
        // Apply JWT authentication and admin middleware only to store, update, and destroy methods
        $this->middleware(['auth:api', 'admin'])->only(['store', 'update', 'destroy', 'index', 'last_six_months_stats']);
    }




    protected array $typeOfFields = ['textFields', 'numericFields'];

    protected array $textFields = [
        'uniq_id',
        'type',
        'status',
        'shipping_method',
        'payment_method',
        'payment_status',
    ];

    protected array $numericFields = [
        'items',
        'customer_id',
        'total',
        'promocode_id',
    ];

    protected array $customerInfo = [

        'full_name',
        'last_name',
        'email',
        'phone',
        'full_address',
        'city',
        'state',
        'postal_code',
        'country',
    ];

    protected function validateRequest(Request $request): array
    {
        return $request->validate([
            'uniq_id'         => 'required|string|max:255',
            'full_name'       => 'required|string|max:255',
            'last_name'       => 'required|string|max:255',
            'email'           => 'required|email|max:255',
            'phone'           => 'required|string|max:20',
            'full_address'    => 'required|string|max:255',
            'city'            => 'required|string|max:100',
            'state'           => 'required|string|max:100',
            'postal_code'     => 'required|string|max:20',
            'country'         => 'required|string|max:100',
            'type'            => 'nullable|string|max:100',
            'items'           => 'nullable|integer|min:1',
            'status'          => 'required|string|in:pending,processing,completed,cancelled',
            'shipping_method' => 'nullable|string|max:100',
            'shipping_price'  => 'nullable|numeric|min:0',
            'order_summary'   => 'nullable|string', // or array/json if casted
            'payment_method'  => 'nullable|string|max:100',
            'payment_status'  => 'required|string|in:unpaid,paid',
            'promocode_id'    => 'nullable|exists:promo_codes,id',
            'total'           => 'required|numeric|min:0',
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
        // return 0;

        try {
            $validated = $request->validate([
                'paginate_count' => 'nullable|integer|min:1',
                'search' => 'nullable|string|max:255',
            ]);







            $search = $validated['search'] ?? null;
            $paginate_count = $validated['paginate_count'] ?? 10;

            $query = Order::with(['promocode:id,name']);


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

            $customer = Customer::where('email', $validated['email'])->first();

            if (!$customer) {
                // Create new customer if not found
                $customer = new Customer();
                HelperMethods::populateModelFields(
                    $customer,
                    $request,
                    $validated,
                    ['textFields'],
                    ['textFields' => $this->customerInfo]
                );
                $customer->save();
            } else {
                // Optionally update existing customer fields
                HelperMethods::populateModelFields(
                    $customer,
                    $request,
                    $validated,
                    ['textFields'],
                    ['textFields' => $this->customerInfo]
                );
                $customer->save();
            }



            $order = new Product();
            $order->customer_id = $customer->id;
            HelperMethods::populateModelFields(
                $order,
                $request,
                $validated,
                $this->typeOfFields,
                [
                    'numericFields' => $this->numericFields,
                    'textFields' => $this->textFields,
                ]
            );
            $order->save();




            return response()->json([
                'success' => true,
                'message' => 'data created successfully.',
                'data' => [
                    'customer' => $customer,
                    'order' => $order,
                ],
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
            return HelperMethods::handleException($e, 'Failed to update data.');
        }
    }

    public function show($uniq_id)
    {
        try {
            $data = Order::with([
                'products',
                'promocode:id,name' // Only id and name from promocode
            ])->where('uniq_id', $uniq_id)->first();

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found.',
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data retrieved successfully.',
                'data' => $data,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return HelperMethods::handleException($e, 'Failed to retrieve data.');
        }
    }


    public function destroy($id)
    {
        try {
            $data = Order::findOrFail($id);

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


    public function last_six_months_stats()
    {
        $data = [];

        for ($i = 0; $i < 6; $i++) {
            $start = Carbon::now()->subMonths($i)->startOfMonth();
            $end = Carbon::now()->subMonths($i)->endOfMonth();

            $sum = Order::whereBetween('created_at', [$start, $end])->sum('total');

            $data[$start->format('F')] = (float) $sum;  // cast to float here
        }

        return array_reverse($data);
    }
}
