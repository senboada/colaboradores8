<?php

namespace App\Http\Controllers\AuthApps;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Auth;
use Socialite;
use App\Models\User;

use Illuminate\Support\Facades\Hash;

class AppsLoginController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Obtain the user information from Google.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback($provider)
    {
        $user = Socialite::driver($provider)->stateless()->user();
        $authUser = $this->findOrCreateUser($user, $provider);
        Auth::login($authUser, true);
        return redirect($this->redirectTo);
    }

    public function findOrCreateUser($user, $provider)
    {
        $authUser = User::where('provider_id', $user->id)->first();
        if ($authUser) {
            return $authUser;
        }
        $authEmail = User::where('email', $user->email)->first();
        if (!empty($authEmail)) {
            $authEmail->name = $user->name;
            $authEmail->provider = strtoupper($provider);
            $authEmail->provider_id = $user->id;
            $authEmail->save();
            return $authEmail;
        } else {
            return User::create([
                'name'          => $user->name,
                'email'         => $user->email,
                'password'      =>  Hash::make('Inicio$2020$'),
                'type'          => 'standard',
                'provider'      => strtoupper($provider),
                'provider_id'   => $user->id
            ]);
        }
    }
}
