<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Helpers\HelperMethods;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\PromoCode;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;


class OrderController extends Controller
{
    public function __construct()
    {
        // Apply JWT authentication and admin middleware only to store, update, and destroy methods
        $this->middleware(['auth:api', 'admin'])->only(['update', 'destroy', 'last_six_months_stats']);
    }




    protected array $typeOfFields = ['textFields', 'numericFields'];

    protected array $textFields = [
        'uniq_id',
        'type',
        'status',
        'shipping_method',
        'payment_method',
        'payment_status',
        'promocode_name',
    ];

    protected array $numericFields = [
        'items',
        'promocode_id',
        'customer_id',
        'shipping_price',
        'total',
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
            'products' => 'nullable|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'nullable|integer|min:1|max:100',
            'promocode_name'  => 'nullable|string|max:100',
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
        try {
            // Validate the request
            $validated = $request->validate([
                'paginate_count' => 'nullable|integer|min:1',
                'search' => 'nullable|string|max:255',
                'payment_status' => 'nullable|string|max:255',
                'status' => 'nullable|string|max:255',
            ]);

            $search = $validated['search'] ?? null;
            $paginate_count = $validated['paginate_count'] ?? 10;
            $payment_status = $validated['payment_status'] ?? null;
            $status = $validated['status'] ?? null;

            // Initialize query with relationships
            $query = Order::with(['promocode:id,name', 'customer'])->orderBy('updated_at', 'desc');

            // Check if user is authenticated and their role
            $user = null;
            try {
                $user = JWTAuth::parseToken()->authenticate();
            } catch (JWTException $e) {
                // User is not authenticated; proceed without user
            }

            // If user is authenticated and not admin, filter by customer
            if ($user && $user->role !== 'admin') { // Check role instead of is_admin
                $customer = Customer::where('email', $user->email)->first();
                if (!$customer) {
                    throw new ModelNotFoundException('Customer not found for the authenticated user.');
                }
                $query->where('customer_id', $customer->id);
            }

            // Apply search and filters
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('uniq_id', 'like', $search . '%')
                        ->orWhereHas('customer', function ($customerQuery) use ($search) {
                            $customerQuery->where('email', 'like', $search . '%')
                                         ->orWhere('phone', 'like', $search . '%');
                        });
                });
            }
            if ($payment_status) {
                $query->where('payment_status', $payment_status);
            }
            if ($status) {
                $query->where('status', $status);
            }

            // Paginate results
            $data = $query->paginate($paginate_count);

            return response()->json([
                'success' => true,
                'data' => $data,
                'current_page' => $data->currentPage(),
                'total_pages' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
            ], Response::HTTP_OK);
        } catch (ValidationException $e) {
            Log::warning('Validation failed in OrderController::index', [
                'errors' => $e->errors(),
                'request' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check the provided data.',
                'errors' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            Log::error('Model not found in OrderController::index', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Customer not found for the authenticated user.'
            ], 404);
        } catch (QueryException $e) {
            Log::error('Database error in OrderController::index', [
                'message' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Database error occurred while fetching orders.'
            ], 500);
        } catch (JWTException $e) {
            Log::warning('JWT authentication error in OrderController::index', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Allow unauthenticated access for admins or public access
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
            Log::error('Unexpected error in OrderController::index', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch orders: ' . $e->getMessage()
            ], 500);
        }
    }

    public function stats()
    {
        $totalOrders = Order::count();
        $processingOrders = Order::where('status', 'pending')->count();
        $pendingPayments =  Order::where('payment_status', 'unpaid')->count();
        $revenue = Order::sum('total');
        $averageOrderValue = $revenue / $totalOrders;


        return response()->json([
            'totalOrders' => $totalOrders,
            'processing' => $processingOrders,
            'pendingPayments' => $pendingPayments,
            'revenue' => $revenue,
            'averageOrderValue' => $averageOrderValue,
        ], Response::HTTP_OK);
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
            DB::beginTransaction();

            // Validate the request
            $validated = $this->validateRequest($request);

            // Find or create customer
            $customer = Customer::firstOrNew(['email' => $validated['email']]);
            HelperMethods::populateModelFields(
                $customer,
                $request,
                $validated,
                ['textFields'],
                ['textFields' => $this->customerInfo]
            );
            $customer->save();

            // Create order
            $order = new Order();
            $order->customer_id = $customer->id;
            $order->uniq_id = $validated['uniq_id'] ?? HelperMethods::generateUniqueId();
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

            // return $promocodeDiscount->type;

            
            // return $order->total;
            $order->save();

            // Attach products to the order and reduce stock
            $syncData = [];
            if (!empty($validated['products'])) {
                foreach ($validated['products'] as $index => $product) {
                    if (!is_array($product)) {
                        throw new \Exception("Invalid product data at index {$index}. Expected an array.");
                    }
                    $productId = $product['product_id'] ?? throw new \Exception("Missing product_id at index {$index}");
                    $quantity = $product['quantity'] ?? throw new \Exception("Quantity required for product ID {$productId}");

                    $productModel = \App\Models\Product::lockForUpdate()->findOrFail($productId);
                    if ($productModel->stock_quantity < $quantity) {
                        throw new \Exception("Insufficient stock for product ID {$productId}. Available: {$productModel->stock_quantity}, Requested: {$quantity}");
                    }

                    $productModel->stock_quantity -= $quantity;
                    $productModel->sales += $quantity;
                    $productModel->status = HelperMethods::getStockStatus($productModel->stock_quantity);
                    $productModel->save();

                    $syncData[$productId] = ['quantity' => $quantity];
                }
                $order->products()->sync($syncData);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data created successfully.',
                'data' => [
                    'customer' => $customer,
                    'order' => $order->load('products'),
                ],
            ], Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::warning('Validation failed in OrderController::store', [
                'errors' => $e->errors(),
                'request' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check the provided data.',
                'errors' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            Log::error('Model not found in OrderController::store', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Resource not found. Invalid promocode or product ID.'
            ], 404);
        } catch (QueryException $e) {
            DB::rollBack();
            Log::error('Database error in OrderController::store', [
                'message' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Database error occurred while processing the order.'
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Unexpected error in OrderController::store', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage()
            ], 500);
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
                'promocode:id,name' ,
                'products.media',// Only id and name from promocode
                'customer'
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
        $monthlySales = [];

        for ($i = 5; $i >= 0; $i--) {
            $start = Carbon::now()->subMonths($i)->startOfMonth();
            $end = Carbon::now()->subMonths($i)->endOfMonth();

            $sum = Order::whereBetween('created_at', [$start, $end])->sum('total');

            $monthlySales[] = [
                'month' => $start->format('F'),
                'sales' => (float) $sum,
            ];
        }

        $categoryWiseSales = Category::select('id', 'name')
            ->withSum('products as total_sales', 'sales')
            ->get()
            ->map(function ($category) {
                return [
                    'category'     => $category->name,
                    'total_sales'  => (float) ($category->total_sales ?? 0),
                ];
            });
        $totalOrders = Order::count(); //ok 
        $customerCount = Customer::count(); //ok
        $revenue = Order::sum('total'); //ok
        $averageOrderValue = $revenue / $totalOrders; // opk 


        return response()->json([

            //below
            'status' => 'success',
            'monthly_sales' => $monthlySales,
            'category_wise_sales' => $categoryWiseSales,
            //above
            'totalOrders' => $totalOrders,
            'customerCount' => $customerCount,
            'revenue' => $revenue,
            'averageOrderValue' => $averageOrderValue,
        ]);
    }

    public function selfOrderHistory()
    {
        try {
            // Authenticate user via JWT
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated.',
                ], 401);
            }

            // Find the corresponding customer by email
            $customer = Customer::where('email', $user->email)->first();

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found.',
                ], 404);
            }

            // Fetch paginated orders where customer_id matches the customer's id
            $orders = Order::where('customer_id', $customer->id)
                ->with(['products']) // Adjust based on your relationships
                ->paginate(10);

            // Optional: Handle case where no orders exist
            if ($orders->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'orders' => [],
                        'message' => 'No orders found.',
                    ],
                ], 200);
            }

            return response()->json([
                'success' => true,
                'data' => $orders,
            ], 200);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token.',
            ], 401);
        }
    }

    public function changeStatus(Request $request, $id)
    {

        $order = Order::find($id);

        $order->status = $request->status;

        $order->save();

       return response()->json([
    'message' => 'Status updated successfully'
]);
    }

    public function history(Request $request)
    {

        // return 0;

        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json([
                    'message' => 'you are not authenticated'
                ], Response::HTTP_OK);
            }

            $customer = Customer::where('email', $user->email)->first();

            $validated = $request->validate([
                'paginate_count' => 'nullable|integer|min:1',
                'search' => 'nullable|string|max:255',
                'payment_status' => 'nullable|string|max:255', // update values as per your DB
                'status' => 'nullable|string|max:255', // adjust as needed
            ]);


            $search = $validated['search'] ?? null;
            $paginate_count = $validated['paginate_count'] ?? 10;
            $payment_status = $validated['payment_status'] ?? null;
            $status = $validated['status'] ?? null;

            $query = Order::with(['promocode:id,name', 'customer'])
                    ->where('customer_id', $customer->id)
                    ->orderBy('updated_at', 'desc');


            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('uniq_id', 'like', $search . '%')
                        ->orWhereHas('customer', function ($customerQuery) use ($search) {
                            $customerQuery->where('email', 'like', $search . '%')
                                ->orWhere('phone', 'like', $search . '%');
                        });
                });
            }
            if ($payment_status) {
                $query->where('payment_status', $payment_status);
            }

            if ($status) {
                $query->where('status', $status);
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
}
