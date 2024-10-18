<?php
namespace Headstart\Services;

use Auth as AuthGlobal;
use Hash;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Request;
use RainLab\User\Components\Authentication;
use RainLab\User\Models\User;
use RainLab\User\Models\UserLog;
use Underflip\Resorts\Models\UserTokens;

/**
 * Class to handle bearer with socialite and graphql
 *
 *
 **/
class BearerAPI extends Authentication {
	protected $user;

	public function __construct(User $user = null) {
		if ($user) {
			$this->user = $user;
		}

	}

	public static function getUserAuth() {
		/*Log::info('GraphQL check request:', ['request' => request()]);*/

		$token = request()->bearerToken();

		if (!$token) {
			throw new \Exception('Authorization token not provided');
			return false;
		}

		$user = AuthGlobal::checkBearerToken($token);

		$userTokens = UserTokens::where('user_id', $user->id)->where('revoked', false)->get();
		$isLogin = false;
		foreach ($userTokens as $userToken) {
			if (Hash::check($token, $userToken->token)) {
				$isLogin = true;
				break;
			}
		}
		if ($isLogin) {
			return ['user' => $user, 'token' => $token];
		} else {
			return false;
		}

	}

	protected function sendToken($token, $host = '') {
		/*Cookie::queue(
			            'authToken',
			            $token,
			            43200,
			            '/',
			            $host,
			            true,
			            true
		*/
		$host = request()->getHost();
		Cookie::queue(Cookie::make(
			'authToken',
			$token,
			43200, // 30 days
			'/',
			$host,
			false, // Set to false for HTTP (set to true for HTTPS)
			true,
			false,
			'Strict'
		));
	}

	public function refreshToken() {
		$checkAuth = $this->getUserAuth();
		if (!$checkAuth) {
			return false;
		} else {
			$this->user = $checkAuth['user'];
		}

		UserLog::createRecord($this->user->getKey(), 'regenerate API Token', [
			'user_full_name' => $this->user->full_name,
		]);

		$token = AuthGlobal::getBearerToken($this->user);
		Cookie::queue(Cookie::forget('authToken'));
		$this->sendToken($token);

		// AuthGlobal::loginUsingBearerToken($token);
		UserTokens::create([
			'user_id' => $this->user->id,
			'token' => Hash::make($token),
			'revoked' => false,
		]);
		return ['user' => $this->user, 'token' => $token];
	}

	public function loginProcess($email, $password) {
		$credentials = [
			'email' => $email,
			'password' => $password,
		];
		AuthGlobal::attempt($credentials);
		$this->user = AuthGlobal::user();

		if (!$this->user) {
			return false;
		}

		$this->recordUserLogAuthenticated($this->user);
		$token = AuthGlobal::getBearerToken($this->user);
		$this->sendToken($token);

		AuthGlobal::loginUsingBearerToken($token);

		UserTokens::create([
			'user_id' => $this->user->id,
			'token' => Hash::make($token),
			'revoked' => false,
		]);
		return ['user' => $this->user, 'token' => $token];
	}

	public static function logout() {
		$token = request()->bearerToken();

		if (!$token) {
			throw new \Exception('Authorization token not provided');
		}

		$user = AuthGlobal::checkBearerToken($token);

		$userTokens = UserTokens::where('user_id', $user->id)->where('revoked', false)->get();
		$isLogout = false;
		foreach ($userTokens as $userToken) {
			if (Hash::check($token, $userToken->token)) {

				$userToken->revoked = true;
				$userToken->save();
				Cookie::queue(Cookie::forget('authToken'));
				$isLogout = true;
				break;
			}
		}
		if ($isLogout) {
			return ['user' => $user, 'token' => $token];
		} else {
			return false;
		}

	}

	/**
	 * Get the underlying user model.
	 *
	 * @return User
	 */
	public function getUser() {
		return $this->user;
	}
}

?>