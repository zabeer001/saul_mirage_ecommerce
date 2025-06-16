<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Helpers\HelperMethods;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class OrderController extends Controller
{
    public function __construct()
    {
        // Apply JWT authentication and admin middleware only to store, update, and destroy methods
        $this->middleware(['auth:api', 'admin'])->only(['update', 'destroy', 'index', 'last_six_months_stats']);
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
            'products' => 'nullable|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'nullable|integer|min:1|max:100',
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
                'payment_status' => 'nullable|string|max:255', // update values as per your DB
                'status' => 'nullable|string|max:255', // adjust as needed
            ]);


            $search = $validated['search'] ?? null;
            $paginate_count = $validated['paginate_count'] ?? 10;
            $payment_status = $validated['payment_status'] ?? null;
            $status = $validated['status'] ?? null;

            $query = Order::with(['promocode:id,name', 'customer'])->orderBy('updated_at', 'desc');


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
            // Start a database transaction
            DB::beginTransaction();

            // Validate the request
            $validated = $this->validateRequest($request);

            // Find or create customer
            $customer = Customer::where('email', $validated['email'])->first();

            if (!$customer) {
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
                HelperMethods::populateModelFields(
                    $customer,
                    $request,
                    $validated,
                    ['textFields'],
                    ['textFields' => $this->customerInfo]
                );
                $customer->save();
            }

            // Create new order
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
            $order->save();

            // Attach products to the order and reduce stock
            $syncData = [];
            if (!empty($validated['products'])) {
                foreach ($validated['products'] as $index => $product) {
                    // Ensure $product is an array
                    if (!is_array($product)) {
                        throw new \Exception("Invalid product data at index {$index}. Expected an array.");
                    }

                    $productId = $product['product_id'] ?? null;
                    $quantity = $product['quantity'] ?? rand(1, 5);

                    if (!$productId) {
                        throw new \Exception("Missing product_id at index {$index}.");
                    }

                    // Fetch the product and check stock
                    $productModel = \App\Models\Product::findOrFail($productId);
                    if ($productModel->stock_quantity < $quantity) {
                        throw new \Exception("Insufficient stock for product ID {$productId}. Available: {$productModel->stock_quantity}, Requested: {$quantity}.");
                    }

                    // Reduce stock quantity
                    $productModel->stock_quantity -= $quantity;
                    $productModel->sales += $quantity;

                    //stockwise status
                   $productModel->status = HelperMethods::getStockStatus($productModel->stock_quantity);

                    $productModel->save();

                    $syncData[$productId] = ['quantity' => $quantity];
                }
                $order->products()->sync($syncData);
            }

            // Commit the transaction
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data created successfully.',
                'data' => [
                    'customer' => $customer,
                    'order' => $order->load('products'),
                ],
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            // Roll back the transaction on error
            DB::rollBack();
            return HelperMethods::handleException($e, 'Failed to create order.');
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
}
