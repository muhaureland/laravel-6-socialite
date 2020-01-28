<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\SocialAccount;
use App\User;
use Socialite;
use Auth;

class SocialiteController extends Controller
{
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }


    public function handleProviderCallback($provider)
    {
        try {
            $user = Socialite::driver($provider)->stateless()->user();
        } catch (Exception $e) {
            return redirect('/login');
        }

        $authUser = $this->findOrCreateUser($user, $provider );

        Auth::login($authUser, true);
        return redirect('/home');
    }


    public function findOrCreateUser($socialUser, $provider)
    {
        $socialAccount = SocialAccount::where('provider_id', $socialUser->getId())
                        ->where('provider_name', $provider)
                        ->first();
        if ($socialAccount) {
            return $socialAccount->user;
        } else {
            $user = User::where('email', $socialUser->getEmail())->first();
                if (!$user) {
                    $user = User::create([
                        'name' => $socialUser->getName(),
                        'email' => $socialUser->getEmail()

                    ]);
                }
            $user->socialAccounts()->create([
                'provider_id' => $socialUser->getId(),
                'provider_name' => $provider
            ]);
            return $user;
        }

    }
}
