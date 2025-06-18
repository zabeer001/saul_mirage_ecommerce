<?php

namespace App\Http\Controllers;

use App\Helpers\HelperMethods;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $validated = $request->validate([
                'paginate_count' => 'nullable|integer|min:1',
                'search' => 'nullable|string|max:255',
                'status' => 'nullable|string|max:255',
            ]);

            $search = $validated['search'] ?? null;
            $paginate_count = $validated['paginate_count'] ?? 10;
            $status = $validated['status'] ?? null;
            $query = Customer::withCount('orders')
    ->withSum('orders', 'total') // this assumes each order has a 'total' column
    ->orderBy('updated_at', 'desc');

            if ($search) {
                $query->where('email', 'like', $search . '%');
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
        $totalCustomers = Customer::count();
        $newCustomers = Customer::where('created_at', '>=', \Carbon\Carbon::now()->subDays(30))->count();
        $inactiveCustomers = Customer::where('status', 'inactive')->count();
        $averageOrderValue = Customer::count() > 0 ? Order::sum('total') / Customer::count() : 0;
        return response()->json([
            'totalCustomers' => $totalCustomers,
            'new_customers' => $newCustomers,
            'inactiveCustomers' => $inactiveCustomers,
            'averageOrderValue' => $averageOrderValue,


        ], Response::HTTP_OK);
    }



    public function updateStatus(Request $request)
    {
        try {
            $validated = $request->validate([
                'limit' => 'nullable|integer|min:1'
            ]);

            $threeMonthsAgo = now()->subMonths(3);
            $updatedCount = 0;
            $limit = $validated['limit'] ?? null;

            $query = Customer::orderBy('created_at', 'desc');

            if ($limit) {
                $query->limit($limit);
            }

            $customers = $query->get();

            foreach ($customers as $customer) {
                $hasRecentOrder = $customer->orders()
                    ->where('created_at', '>=', $threeMonthsAgo)
                    ->exists();

                $newStatus = $hasRecentOrder ? 'active' : 'inactive';

                if ($customer->status !== $newStatus) {
                    $customer->status = $newStatus;
                    $customer->save();
                    $updatedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully updated status for $updatedCount customer(s).",
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return HelperMethods::handleException($e, 'Failed to update customer statuses.');
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
    public function show(Customer $customer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        //
    }
}
