<?php

namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use App\Models\User\User;
use App\Models\Page\Page;
use App\Models\Page\Element;
use App\Models\Catalog\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use App\Models\Order\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;


class UserController extends Controller
{
    public function account()
    {
        
        $cookieKey = 'user_key';
        $userKey = request()->cookie($cookieKey);

        if(!$userKey){
            return redirect('/account/login');
        }

        $user = User::where('id', $userKey)->first();

        
        $title = 'Особистий кабінет';
        $title_page = $title .' | ' .$user->name;
        $orders = Order::where('user_id', $user->id)->get();

        return view('account.account', compact('title', 
                                    'title_page',
                                    'orders',
                                    'user'
                                ));
    }

    public function login()
    {
        $cookieKey = 'user_key';
        $userKey = request()->cookie($cookieKey);

        if ($userKey) {
            return redirect('/account');
        }

        return view('account.login');
    }

    public function checkout(Request $request)
    {
        $cookieKey = 'user_key';
        $userKey = request()->cookie($cookieKey);

        if ($userKey) {
            return redirect('/account');
        }

        $credentials = request()->only('email', 'password');

        if (auth()->attempt($credentials)) {
            $user = User::where('email', $request->get('email'))->first();

            Cookie::queue(Cookie::make('user_key', $user->id, 60 * 24 * 180));
            Cookie::queue(Cookie::make('user_email', $user->email, 60 * 24 * 180));
            Cookie::queue(Cookie::make('user_price', $user->price_list_id, 60 * 24 * 180));
            return redirect('/account');
        }

        return back()->withErrors(['error' => 'Невірний логін або пароль']);
    }

    public function register()
    {
        $cookieKey = 'user_key';
        $userKey = request()->cookie($cookieKey);

        if ($userKey) {
            return redirect('/account');
        }
        
        return view('account.register');
    }

    public function startRegister(Request $request){
        $cookieKey = 'user_key';
        $userKey = request()->cookie($cookieKey);

        if ($userKey) {
            return redirect('/account');
        }

        $user = User::where('email', $request->get('email'))->first();
        if($user){
            if($user->is_registered){
                return back()->withErrors(['error' => 'Користувач з цією поштою вже зареєстрований']);
            }else{
                $user->update([
                    'name'=>$request->get('name'),
                    'email'=>$request->get('email'),
                    'phone'=>$request->get('phone'),
                    'password' => Hash::make($request->get('password')),
                    'price_list_id'=>1,
                    'is_registered'=>true
                ]);

                Cookie::queue(Cookie::make('user_key', $user->id, 60 * 24 * 180));
                Cookie::queue(Cookie::make('user_email', $user->email, 60 * 24 * 180));
                Cookie::queue(Cookie::make('user_price', $user->price_list_id, 60 * 24 * 180));

                return redirect('/account');
            }
            
        }else{

            $user = User::create([
                    'name'=>$request->get('name'),
                    'email'=>$request->get('email'),
                    'phone'=>$request->get('phone'),
                    'password' => Hash::make($request->get('password')),
                    'price_list_id'=>1,
                    'is_registered'=>true,
                    'send'=>0
                ]);
            
            $user->update([
                'profile_id'=>$user->id
            ]);

            Cookie::queue(Cookie::make('user_key', $user->id, 60 * 24 * 180));
            Cookie::queue(Cookie::make('user_email', $user->email, 60 * 24 * 180));
            Cookie::queue(Cookie::make('user_price', $user->price_list_id, 60 * 24 * 180));

            $text = "🚀 <b>Новий користувач!</b>\n";
            $text .= "👤 {$user->name}\n";
            $text .= "✉️ {$user->email}\n";
            $text .= "📞 {$user->phone}\n";

            Http::post("https://api.telegram.org/bot" . env('TG_TOKEN') . "/sendMessage", [
                'chat_id' => env('TG_ID_ADMIN'),
                'text' => $text,
                'parse_mode' => 'HTML'
            ]);

            return redirect('/account');
        }
    }

    public function logout(Request $request)
    {
        auth()->logout();

        Cookie::queue(Cookie::forget('user_key'));
        Cookie::queue(Cookie::forget('user_email'));
        Cookie::queue(Cookie::forget('user_price'));

        return redirect('/');
    }

    public function forgot(Request $request)
    {
        $cookieKey = 'user_key';
        $userKey = request()->cookie($cookieKey);

        if ($userKey) {
            return redirect('/account');
        }

        return view('account.forgot');
    }

    public function createCode(Request $request)
    {
        $cookieKey = 'user_key';
        $userKey = $request->cookie($cookieKey);

        if ($userKey) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ви вже авторизовані'
            ], 403);
        }

        $email = $request->get('email');

        if (!$email) {
            return response()->json([
                'status' => 'error',
                'message' => 'Поле email обовʼязкове'
            ], 400);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Користувача з такою поштою не знайдено'
            ], 404);
        }

        // генеруємо чотиризначний код
        $code = rand(1000, 9999);

        $user->send = 0;
        $user->forgot_code = $code;
        $user->save();

        $text = "🔐 <b>Відновлення паролю!</b>\n";
        $text .= "👤 {$user->name}\n";
        $text .= "✉️ {$user->email}\n";
        $text .= "📞 {$user->phone}\n";
        $text .= "🔑 {$user->forgot_code}\n";

        Http::post("https://api.telegram.org/bot" . env('TG_TOKEN') . "/sendMessage", [
            'chat_id' => env('TG_ID_ADMIN'),
            'text' => $text,
            'parse_mode' => 'HTML'
        ]);

        

        return response()->json([
            'status' => 'success',
            'message' => 'Код відновлення відправлено на електронну пошту. Код доступний протягом 20 хвилин.',
        ]);
    }

    public function checkCode(Request $request)
    {
        $cookieKey = 'user_key';
        $userKey = $request->cookie($cookieKey);

        if ($userKey) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ви вже авторизовані'
            ], 403);
        }

        $email = $request->get('email');

        if (!$email) {
            return response()->json([
                'status' => 'error',
                'message' => 'Поле email обовʼязкове'
            ], 400);
        }


        $code = $request->get('code');
        $user = User::where('email', $email)->where('forgot_code', $code)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Сталася помилка, спробуйте ще раз відновити пароль'
            ], 404);
        }

        if ($user->updated_at->lt(Carbon::now()->subMinutes(20))) {
            
            return response()->json([
                'status' => 'error',
                'message' => 'Код не дійсний'
            ], 404);
        }else{
            return response()->json([
                'status' => 'success',
                'message' => 'Укажіть новий пароль',
            ]);
        }
    }

    public function changePassword(Request $request)
    {
        $cookieKey = 'user_key';
        $userKey = $request->cookie($cookieKey);

        if ($userKey) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ви вже авторизовані'
            ], 403);
        }

        $email = $request->get('email');

        if (!$email) {
            return response()->json([
                'status' => 'error',
                'message' => 'Поле email обовʼязкове'
            ], 400);
        }


        $code = $request->get('code');
        $user = User::where('email', $email)->where('forgot_code', $code)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Сталася помилка, спробуйте ще раз відновити пароль'
            ], 404);
        }

        if ($user->updated_at->lt(Carbon::now()->subMinutes(20))) {
            
            return response()->json([
                'status' => 'error',
                'message' => 'Код не дійсний'
            ], 404);
        }else{
            $password = $request->get('password');
            $user->password = Hash::make($password);
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Пароль змінено',
            ]);
        }
    }
}
