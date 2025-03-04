<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Message;
use Illuminate\Http\Request;
use App\Events\MessageReceived;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Notifications\TelegramNotification;
use Illuminate\Support\Facades\Notification;

class TelegramLoginController extends Controller
{
    public function telegramLogin(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if ($user) {
            $user->update([
                'phone' => $request->phone,
                'telegram_id' => $request->telegram_id
            ]);
        } else {
            $user = new User();
            $user->name = $request->name;
            $user->username = '@' . $request->username;
            $user->avatar = $request->avatar;
            $user->access_token = $request->hash;
            $user->password = bcrypt($request->email);
            $user->phone = $request->email;
            $user->user_type = 'login_with_telegram';
            $user->email = $request->email;
            $user->save();

            $role = Role::findOrFail(8);
            $user->assignRole($role->name);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->email])) {
            return redirect()->route('home');
        } else {
            return response()->json(['message' => 'Login failed.'], 401);
        }
    }
    public function telegramAuthCallback(Request $request)
    {
        $telegram_user = Socialite::driver('telegram')->user();
        $user = User::where('telegram_id', $telegram_user->id)->first();
        if ($user) {
            Auth::attempt(['email' => $user->email, 'password' => $user->email]);
            $data = [
                'success' => __('Login with Telegram Successfully')
            ];
            return redirect()->route('home')->with($data);
        } else {
            Notification::route('telegram', $telegram_user->id)
                ->notify(new TelegramNotification());
            return view('auth.telegram_confirm_login', compact('telegram_user'));
        }
    }   
    public function webhook(Request $request)
    {
        Log::info($request->all());

        $update = $request->input();
        $messageData = $update['message'] ?? null;
        if ($messageData) {
            $userData = $messageData['from'];
            $chat = Chat::create([
                'telegram_user_id' => $userData['id'],
                'first_name' => $userData['first_name'],
                'username' => $userData['username'] ?? null,
                'message' => $messageData['text'],
            ]);

            broadcast(new MessageReceived($chat));

            return response()->json(['status' => 'Message stored successfully'], 200);
        }

        return response()->json(['status' => 'No message found'], 400);
    }

}

