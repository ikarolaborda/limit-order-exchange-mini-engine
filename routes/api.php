<?php

declare(strict_types=1);

use App\Actions\Auth\LoginAction;
use App\Actions\Auth\LogoutAction;
use App\Actions\Auth\RegisterAction;
use App\Actions\Market\GetExchangeRatesAction;
use App\Actions\Matching\MatchOrderAction;
use App\Actions\Order\CancelOrderAction;
use App\Actions\Order\CreateOrderAction;
use App\Actions\Order\GetMyOrdersAction;
use App\Actions\Order\GetOrderbookAction;
use App\Actions\Profile\ShowProfileAction;
use App\Actions\Trade\GetTradesAction;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', RegisterAction::class)->name('api.auth.register');
Route::post('/auth/login', LoginAction::class)->name('api.auth.login');
Route::get('/market/rates', GetExchangeRatesAction::class)->name('api.market.rates');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/auth/logout', LogoutAction::class)->name('api.auth.logout');
    Route::get('/profile', ShowProfileAction::class)->name('api.profile');
    Route::get('/orders', GetOrderbookAction::class)->name('api.orders.index');
    Route::get('/orders/{order}', fn ($order) => $order)->name('api.orders.show');
    Route::get('/my-orders', GetMyOrdersAction::class)->name('api.my-orders');
    Route::post('/orders', CreateOrderAction::class)->name('api.orders.store');
    Route::post('/orders/{order}/cancel', CancelOrderAction::class)->name('api.orders.cancel');
    Route::post('/match', MatchOrderAction::class)->name('api.match');
    Route::get('/trades', GetTradesAction::class)->name('api.trades.index');
});
