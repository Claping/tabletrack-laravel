<?php

namespace App\Http\Controllers;

use App\Models\Kot;


class KotController extends Controller
{
    protected $connector;
    protected $printer;

    public function __construct()
    {
        // Initialize printer connection

    }

    public function index()
    {
        abort_if(!in_array('KOT', restaurant_modules()), 303);
        abort_if((!user_can('Manage KOT')), 303);
        return view('kot.index');
    }

    public function printKot($id, $kotPlaceid = null)
    {

        $kot = Kot::with('items', 'order.waiter', 'table')->find($id);

        return view('pos.printKot', [
            'kot' => $kot,
            'kotPlaceId' => $kotPlaceid
        ]);
    }
}
