<?php

namespace App\Http\Controllers;

use App\Models\StoreConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $shop = $request->user();
        $shopDomain = $shop->getDomain()->toNative(); // Convert ShopDomain to string
    
        
        $connection = StoreConnection::firstOrCreate(
            ['shop_domain' => $shopDomain],
            ['connection_id' => Str::random(10)]
        );

        return view('dashboard', compact('connection', 'shopDomain'));
    }

    public function connect(Request $request)
    {
        $request->validate([
            'connection_id' => 'required|exists:store_connections,connection_id',
        ]);

        $shop = $request->user();
        $connection = StoreConnection::where('shop_domain', $shop->getDomain())->firstOrFail();
        $targetConnection = StoreConnection::where('connection_id', $request->connection_id)->firstOrFail();

        if ($connection->id === $targetConnection->id) {
            return response()->json(['error' => 'Cannot connect to your own store'], 400);
        }

        $connection->connected_to = $targetConnection->shop_domain;
        $connection->save();

        $targetConnection->connected_to = $shop->getDomain();
        $targetConnection->save();

        return response()->json(['success' => true, 'connected_to' => $targetConnection->shop_domain]);
    }

    public function disconnect(Request $request)
    {
        $shop = $request->user();
        $connection = StoreConnection::where('shop_domain', $shop->getDomain())->firstOrFail();

        if ($connection->connected_to) {
            $targetConnection = StoreConnection::where('shop_domain', $connection->connected_to)->first();
            if ($targetConnection) {
                $targetConnection->connected_to = null;
                $targetConnection->save();
            }

            $connection->connected_to = null;
            $connection->save();
        }

        return response()->json(['success' => true]);
    }
}