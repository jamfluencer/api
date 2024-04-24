<?php

use App\Spotify\Facades\Spotify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json(['status' => 'OK']));

Route::get('/auth/spotify', fn () => response()->json(['url' => Spotify::authUrl()]));
//Route::post('/auth/spotify', fn (Request $request) => )
