<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialLogin;
use App\Models\User;
use Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;
use Log;

class SocialLoginController extends Controller
{
    public function redirect($provider = 'microsoft365')
    {
        $provider = $this->getProviderName($provider);
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider)
    {
        $provider = $this->getProviderName($provider);

        try {
            $socialiteUser = Socialite::driver($provider)->user();

            $socialLogin = SocialLogin::with('user')
                ->provider($provider)
                ->where('provider_id', $socialiteUser->id)
                ->first();

            if ($socialLogin && $socialLogin->user) {
                Auth::login($socialLogin->user);
                return redirect('/dashboard');
            }


            DB::beginTransaction();

            try {
                $user = User::firstOrCreate(
                    ['email' => $socialiteUser->email],
                    [
                        'first_name' => str($socialiteUser->name)->before(' '),
                        'last_name' => str($socialiteUser->name)->after(' '),
                        'email' => $socialiteUser->email,
                        'password' => encrypt('gitpwd059'),
                    ]
                );

                SocialLogin::create([
                    'user_id' => $user->id,
                    'provider' => $provider,
                    'provider_id' => $socialiteUser->id
                ]);
            } catch (\Exception $e){
                DB::rollBack();
                Log::error($e->getMessage());
                return redirect()->route('login')->with('error', 'Something went wrong');
            }

            DB::commit();

            Auth::login($user);

            return redirect('/dashboard');

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->route('login')->with('error', 'Something went wrong');
        }
    }

    private function getProviderName($provider)
    {
        $providers = [
            'microsoft365' => 'azure'
        ];

        return $providers[$provider] ?? $provider;
    }
}
