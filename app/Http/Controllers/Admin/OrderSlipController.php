<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderSlipController extends Controller
{
    public function __invoke(Request $request, Order $order): View
    {
        abort_unless($request->user()?->isAdmin(), 403);

        return view('admin.slip', ['order' => $order->load('items')]);
    }
}
