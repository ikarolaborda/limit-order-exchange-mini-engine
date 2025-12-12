<?php

declare(strict_types=1);

use App\Actions\Activity\GetActivitiesAction;
use App\Actions\AI\AnalyzeSentimentAction;
use App\Actions\AI\AnalyzeSentimentBatchAction;
use App\Actions\AI\ClassifyTextAction;
use App\Actions\AI\GetMarketInsightAction;
use App\Actions\AI\GetMarketSentimentAction;
use App\Actions\Auth\LoginAction;
use App\Actions\Auth\LogoutAction;
use App\Actions\Auth\RegisterAction;
use App\Actions\Market\GetExchangeRatesAction;
use App\Actions\Matching\MatchOrderAction;
use App\Actions\Notification\GetNotificationsAction;
use App\Actions\Notification\MarkAllNotificationsReadAction;
use App\Actions\Notification\MarkNotificationReadAction;
use App\Actions\Order\CancelOrderAction;
use App\Actions\Order\CreateOrderAction;
use App\Actions\Order\GetMyOrdersAction;
use App\Actions\Order\GetOrderbookAction;
use App\Actions\Profile\ChangePasswordAction;
use App\Actions\Profile\ShowProfileAction;
use App\Actions\Trade\GetTradesAction;
use App\Actions\Web3\CreateWalletAction;
use App\Actions\Web3\GetTransactionStatusAction;
use App\Actions\Web3\GetWalletBalanceAction;
use App\Actions\Web3\ListUserTransactionsAction;
use App\Actions\Web3\ListUserWalletsAction;
use App\Actions\Web3\SendTransactionAction;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', RegisterAction::class)->name('api.auth.register');
Route::post('/auth/login', LoginAction::class)->name('api.auth.login');
Route::get('/market/rates', GetExchangeRatesAction::class)->name('api.market.rates');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/auth/logout', LogoutAction::class)->name('api.auth.logout');
    Route::get('/profile', ShowProfileAction::class)->name('api.profile');
    Route::post('/profile/password', ChangePasswordAction::class)->name('api.profile.password');
    Route::get('/profile/activities', GetActivitiesAction::class)->name('api.profile.activities');
    Route::get('/orders', GetOrderbookAction::class)->name('api.orders.index');
    Route::get('/orders/{order}', fn ($order) => $order)->name('api.orders.show');
    Route::get('/my-orders', GetMyOrdersAction::class)->name('api.my-orders');
    Route::post('/orders', CreateOrderAction::class)->name('api.orders.store');
    Route::post('/orders/{order}/cancel', CancelOrderAction::class)->name('api.orders.cancel');
    Route::post('/match', MatchOrderAction::class)->name('api.match');
    Route::get('/trades', GetTradesAction::class)->name('api.trades.index');
    Route::get('/notifications', GetNotificationsAction::class)->name('api.notifications.index');
    Route::post('/notifications/{notification}/read', MarkNotificationReadAction::class)->name('api.notifications.read');
    Route::post('/notifications/read-all', MarkAllNotificationsReadAction::class)->name('api.notifications.read-all');

    Route::prefix('web3')->group(function (): void {
        Route::get('/wallets', ListUserWalletsAction::class)->name('api.web3.wallets.index');
        Route::post('/wallets', CreateWalletAction::class)->name('api.web3.wallets.store');
        Route::get('/wallets/{wallet}/balance', GetWalletBalanceAction::class)->name('api.web3.wallets.balance');
        Route::get('/transactions', ListUserTransactionsAction::class)->name('api.web3.transactions.index');
        Route::post('/transactions', SendTransactionAction::class)->name('api.web3.transactions.store');
        Route::get('/transactions/{transaction}', GetTransactionStatusAction::class)->name('api.web3.transactions.show');
    });

    Route::prefix('ai')->group(function (): void {
        Route::post('/sentiment', AnalyzeSentimentAction::class)->name('api.ai.sentiment');
        Route::post('/sentiment/batch', AnalyzeSentimentBatchAction::class)->name('api.ai.sentiment.batch');
        Route::post('/market-insight', GetMarketInsightAction::class)->name('api.ai.market-insight');
        Route::get('/market-sentiment', GetMarketSentimentAction::class)->name('api.ai.market-sentiment');
        Route::post('/classify', ClassifyTextAction::class)->name('api.ai.classify');
    });
});
