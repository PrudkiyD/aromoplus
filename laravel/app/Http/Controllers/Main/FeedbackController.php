<?php

namespace App\Http\Controllers\Main;
use App\Http\Controllers\Controller;
use App\Models\User\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FeedbackController extends Controller
{
    public function feedback(Request $request)
    {
        
        $data = $request->only([
            'name',
            'phone',
            'message'
        ]);

        $feedback = Feedback::create([
            'name'        => $data['name'] ?? null,
            'phone'       => $data['phone'] ?? null,
            'message'     => $data['message'] ?? null,
            'send'        => 0,
            'subject'     => '-'
        ]);

        $text = "📩 <b>Нове звернення!</b>\n";
        $text .= "👤 " . ($data['name'] ?? 'Без імені') . "\n";
        $text .= "📞 " . ($data['phone'] ?? 'Не вказано') . "\n";
        $text .= "💬 " . ($data['message'] ?? 'Не вказано') . "\n";

        Http::post("https://api.telegram.org/bot" . env('TG_TOKEN') . "/sendMessage", [
            'chat_id' => env('TG_ID_ADMIN'),
            'text' => $text,
            'parse_mode' => 'HTML'
        ]);
        
        return response()->json(['feedback'=>'200']);
    }
}
