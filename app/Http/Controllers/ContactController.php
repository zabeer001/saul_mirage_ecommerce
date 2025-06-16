<?php

namespace App\Http\Controllers;

use App\Helpers\HelperMethods;
use App\Mail\ContactMail;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

class ContactController extends Controller
{

   

    protected array $typeOfFields = ['textFields'];


    protected array $textFields = [
        'name',
        'email',
        'how_can_we_help',
    ];





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
            'email' => 'required|string|max:255',
            'how_can_we_help' => 'nullable|string',
        ]);
    }
    /**
     * Display a listing of the resource.
     */
    // public function index(Request $request)
    // {
    //     try {
    //         $validated = $request->validate([
    //             'paginate_count' => 'nullable|integer|min:1',
    //             'search' => 'nullable|string|max:255',
    //             'status' => 'nullable|string|max:255', // adjust as needed
    //         ]);

    //         $search = $validated['search'] ?? null;
    //         $paginate_count = $validated['paginate_count'] ?? 10;

    //         $query = Contact::query();

    //         if ($search) {
    //             $query->where('email', 'like', $search . '%');
    //         }

    //         $data = $query->orderBy('created_at', 'desc')->paginate($paginate_count);

    //         return response()->json([
    //             'success' => true,
    //             'data' => $data,
    //             'current_page' => $data->currentPage(),
    //             'total_pages' => $data->lastPage(),
    //             'per_page' => $data->perPage(),
    //             'total' => $data->total(),
    //         ], Response::HTTP_OK);
    //     } catch (\Exception $e) {
    //         return HelperMethods::handleException($e, 'Failed to fetch data.');
    //     }
    // }

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
    public function show(Contact $contact)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contact $contact)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contact $contact)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact)
    {
        //
    }

    public function sendContactMessage(Request $request)
    {

        $name = $request->name;
        $msg = $request->how_can_we_help;
        $email = $request->email;
        Mail::to('binzabirtareq@gmail.com')->send(new ContactMail($name, $email ,$msg));
        return "Email sent!";
    }
}
