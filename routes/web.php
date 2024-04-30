<?php

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::get('/', fn () => response()->json(['status' => 'OK']));

Route::get(
    '/auth/google',
    function (Request $request): JsonResponse {
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
    ), fn (User $user) => $user->update(['name' => $google->getName()]))->createToken('api')->plainTextToken]);
})->withoutMiddleware(VerifyCsrfToken::class);
