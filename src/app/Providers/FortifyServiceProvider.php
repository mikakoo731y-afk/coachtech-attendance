<?php

namespace App\Providers;

use App\Http\Responses\LoginResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Actions\AttemptToAuthenticate;
use Laravel\Fortify\Actions\EnsureLoginIsNotThrottled;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;


class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);
        Fortify::authenticateThrough(function (Request $request) {
            return [
                function ($request, $next) {
                    $request->validate([
                        'email'    => 'required|string', // Fortify::username() ではなく直接 email と書く
                        'password' => 'required|string',
                    ], [
                        'email.required'    => 'メールアドレスを入力してください',
                        'password.required' => 'パスワードを入力してください',
                    ]);

                    return $next($request);
                },
                // 以降はFortifyの標準パイプライン
                EnsureLoginIsNotThrottled::class,
                RedirectIfTwoFactorAuthenticatable::class,
                AttemptToAuthenticate::class,
                PrepareAuthenticatedSession::class,
            ];
        });

            // 会員登録画面に使用するビューを指定
        Fortify::registerView(function () {
            return view('auth.register');
        });

        // （ついでに）ログイン画面も指定
        Fortify::loginView(function () {
            return view('auth.login');
        });

        Fortify::verifyEmailView(function () {
            return view('auth.verify-email');
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
        config(['fortify.home' => '/attendance']);
    }
}
