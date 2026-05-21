<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order\TtnPrint;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Http\Request;

class PrintController extends Controller
{
    public function checkttn(Request $request, $key)
    {
        if ($key !== env('TTN_KEY')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $ttn = TtnPrint::where('printed', false)->first();
        if (!$ttn) {
            return response()->json(['message' => 'No TTN found'], 404);
        }

        return response()->json([
            'id' => $ttn->id,
            'path' => $ttn->path,
        ]);
    }

    public function printed(Request $request, $key, $id)
    {
        if ($key !== env('TTN_KEY')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $ttn = TtnPrint::where('id', $id)->first();
        if (!$ttn) {
            return response()->json(['message' => 'No TTN found'], 404);
        }else{
            $ttn->printed = true;
            $ttn->save();
        }

        return response()->json([
            'status' => 200,
        ]);
    }
}
