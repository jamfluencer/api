<?php

use App\Models\User;
use App\Slack\Event\Envelope;
use App\Slack\Event\Handler;
use App\Slack\Event\Type;
use App\Slack\VerifySlackSignature;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::get('/', fn () => response()->json(['status' => 'OK']));

Route::get(
    '/auth/google',
    static function (Request $request): JsonResponse {
        $request->validate([
            'redirect' => ['required', 'url'],
        ]);

        return response()
            ->json([
                'url' => Socialite::driver('google')
                    ->stateless()
                    ->redirectUrl($request->input('redirect'))
                    ->redirect()
                    ->getTargetUrl(),
            ]);
    }
);
Route::post('/auth/google', function (Request $request): JsonResponse {
    $request->validate([
        'code' => ['required', 'string'],
        'redirect' => ['required', 'url'],
    ]);
    $google = Socialite::driver('google')
        ->stateless()
        ->redirectUrl($request->input('redirect'))
        ->user();

    return response()->json(['token' => tap(User::query()->firstOrCreate(
        [
            'email' => $google->getEmail(),
        ],
        ['email_verified_at' => now()]
    ), fn (User $user) => $user->update([
        'name' => $google->getName(),
        'email_verified_at' => $user->email_verified_at ?? now(),
    ]))->createToken('api')->plainTextToken]);
})->withoutMiddleware(VerifyCsrfToken::class);

Route::middleware([VerifySlackSignature::class])
    ->prefix('/slack')
    ->group(function () {
        Route::post(
            '/events',
            fn (Envelope $envelope): JsonResponse => match ($envelope->type) {
                Type::URL_VERIFICATION => response()->json(['challenge' => $envelope->challenge]),
                default => (new Handler)($envelope->event)
            }
        )->withoutMiddleware(VerifyCsrfToken::class);
    });
