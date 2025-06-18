<?php

namespace App\Http\Controllers;

use App\Mail\SubscribeMail;
use App\Mail\SubscribeMailForUser;
use App\Models\Subscribe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SubscribeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function show(Subscribe $subscribe)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subscribe $subscribe)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subscribe $subscribe)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subscribe $subscribe)
    {
        //
    }

    public function sendSubscribeMail(Request $request)
    {
        $email = $request->email;

        Mail::to('binzabirtareq@gmail.com')->send(new SubscribeMail($email));
        Mail::to($email)->send(new SubscribeMailForUser());

        return response()->json([
            'success' => true,
            'message' => 'Email sent!',
        ]);
    }
}
