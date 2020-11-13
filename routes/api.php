<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/weather', function () {
    $geoCode = \request('geoCode');
    $response = Http::get("https://apiprevmet3.inmet.gov.br/previsao/$geocode");
    return $response->json();
});


Route::get('/state-code', function () {
    $stateName = \request('name');
    $response = Http::get("https://servicodados.ibge.gov.br/api/v1/localidades/estados/$stateName");
    return $response->json();
});

Route::get('/city-code', function () {

    $stateCode = \request('stateCode');
    $cityName = \request('cityName');
    $response = Http::get("https://servicodados.ibge.gov.br/api/v1/localidades/estados/$stateCode/municipios");

    foreach ($response->json() as $city) {
        if ($city['nome'] === $cityName) {
            return json_encode($city);
        }
    }
});
