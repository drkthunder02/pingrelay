<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Socialite;
use Auth;

use App\Models\User\User;
use App\Models\User\Role;
use App\Models\User\Permission;

use Seat\Eseye\Cache\NullCache;
use Seat\Eseye\Configuration;
use Seat\Eseye\Containers\EsiAuthentication;
use Seat\Eseye\Eseye;


class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout',
                                           'handleProviderCallback',
                                           'redirectToProvider');
    }

    /**
     * Logout Function
     * 
     * @return void
     */
    public function logout() {
        Auth::logout();
        return redirect('/');
    }

    /**
     * Redirect to provider's website
     * 
     * @return Socialite
     */
    public function redirectToProvider() {
        return Socialite::driver('eveonline')->redirect();
    }

    /**
     * Get token from callback
     * Redirect to the dashboard if logging in successfully
     */
    public function handleProviderCallback() {
        $ssoUser = Socialite::driver('eveonline')->user();
        $user = $this->createOrGetUser($ssoUser);

        auth()->login($user, true);

        return redirect()->to('/dashboard')->with('success', 'Successfully Logged In or Updated ESI.');
    }

    /**
     * Check if a user exists in the database, else, create, and return the user objec t.
     * 
     * @param \Laravel\Socialite\Two\User $user
     */
    private function createOrGetUser($eve_user) {
        //Search for the user in the database
        $authUser = User::where('character_id', $eve_user->id)->first();

        //If the user is found, return the user details, if not, create a new user account
        if($authUser) {
            //Check if the owner hash has changed
            $hash = OwnerHasChanged($authUser->owner_hash, $eve_user->owner_hash);

            //If the hash has changed, we need to reset the roles and permissions
            if($hash) {
                Role::where(['character_id' => $eve_user->id])->update(['role' => 'Guest']);    
            }

            return $authUser;
        } else {
            //Create the user account
            $user = User::create([
                'name' => $eve_user->getName(),
                'avatar' => $eve_user->avatar,
                'owner_hash' => $eve_user->owner_hash,
                'character_id' => $eve_user->getId(),
                'expires_in' => $eve_user->expiresIn,
                'access_token' => $eve_user->token,
            ]);

            //Set the role for the user as guest to start
            $role = new Role;
            $role->character_id = $eve_user->id;
            $role->role = 'Guest';
            $role-save();

            //Send the user back from the function
            return $user;
        }
    }

    /**
     * Get the current owner hash, and compare it with the new owner hash
     * 
     * @param hash
     * @param charId
     */
    private function OwnerHasChanged($hash, $newHash) {
        if($hash === $newHash) {
            return false;
        } else {
            return true;
        }
    }
}
