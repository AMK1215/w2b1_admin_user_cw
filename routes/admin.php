<?php

use App\Http\Controllers\Admin\AdsVedioController;

use App\Http\Controllers\Admin\BankController;
use App\Http\Controllers\Admin\BannerAdsController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BannerTextController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\DepositRequestController;
use App\Http\Controllers\Admin\PaymentTypeController;
use App\Http\Controllers\Admin\PlayerController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\TransferLogController;
use App\Http\Controllers\Admin\WinnerTextController;
use App\Http\Controllers\Admin\WithDrawRequestController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'admin',
    'as' => 'admin.',
    'middleware' => ['auth', 'checkBanned', 'preventPlayerAccess'],
], function () {

    // ==================== Dashboard ====================
    Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('home');

    // ==================== Player Management ====================
    Route::resource('players', PlayerController::class);
    Route::put('players/{player}/ban', [PlayerController::class, 'banUser'])->name('players.ban');
    Route::get('players/{player}/change-password', [PlayerController::class, 'getChangePassword'])->name('players.change-password');
    Route::post('players/{player}/change-password', [PlayerController::class, 'makeChangePassword'])->name('players.update-password');
    Route::get('players/{player}/cash-in', [PlayerController::class, 'getCashIn'])->name('players.cash-in');
    Route::post('players/{player}/cash-in', [PlayerController::class, 'makeCashIn'])->name('players.cash-in.store');
    Route::get('players/{player}/cash-out', [PlayerController::class, 'getCashOut'])->name('players.cash-out');
    Route::post('players/{player}/cash-out', [PlayerController::class, 'makeCashOut'])->name('players.cash-out.store');
    Route::get('players/{player}/logs', [PlayerController::class, 'playerLogs'])->name('players.logs');
    Route::get('players/{player}/report', [PlayerController::class, 'playerReportIndex'])->name('players.report');
    
    // ==================== Profile Management ====================
    Route::get('profile/{id}', [App\Http\Controllers\Admin\ProfileController::class, 'index'])->name('profile_index');
    Route::put('profile/{id}', [App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile_update');

    // ==================== System Wallet ====================
    Route::get('system-wallet', [App\Http\Controllers\Admin\SystemWalletController::class, 'index'])->name('system-wallet.index');
    Route::get('system-wallet-dashboard', [App\Http\Controllers\Admin\SystemWalletController::class, 'dashboard'])->name('system-wallet-dashboard.index');
    
    // ==================== Transactions & Transfers ====================
    Route::get('transfer-log', [TransferLogController::class, 'index'])->name('transfer-log.index');
    Route::get('make-transfer', [App\Http\Controllers\Admin\TransferController::class, 'index'])->name('make-transfer.index');
    Route::post('make-transfer', [App\Http\Controllers\Admin\TransferController::class, 'transfer'])->name('make-transfer.store');
    Route::get('transfer-log/detail/{id}', [TransferLogController::class, 'detail'])->name('transfer-log.detail');

    // ==================== Deposits & Withdrawals ====================
    Route::get('deposits', [DepositRequestController::class, 'index'])->name('deposits.index');
    Route::get('deposits/{deposit}', [DepositRequestController::class, 'view'])->name('deposits.view');
    Route::post('deposits/{deposit}/approve', [DepositRequestController::class, 'statusChangeIndex'])->name('deposits.approve');
    Route::post('deposits/{deposit}/reject', [DepositRequestController::class, 'statusChangeReject'])->name('deposits.reject');
    Route::get('deposits/{deposit}/log', [DepositRequestController::class, 'DepositShowLog'])->name('deposits.log');
    
    Route::get('withdrawals', [WithDrawRequestController::class, 'index'])->name('withdrawals.index');
    Route::get('withdrawals/{withdraw}', [WithDrawRequestController::class, 'WithdrawShowLog'])->name('withdrawals.view');
    Route::post('withdrawals/{withdraw}/approve', [WithDrawRequestController::class, 'statusChangeIndex'])->name('withdrawals.approve');
    Route::post('withdrawals/{withdraw}/reject', [WithDrawRequestController::class, 'statusChangeReject'])->name('withdrawals.reject');

    // ==================== Banks & Payment Types ====================
    Route::resource('banks', BankController::class);
    Route::resource('paymentTypes', PaymentTypeController::class);

    // ==================== Reports ====================
    Route::get('reports', [App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('system-reports', [App\Http\Controllers\Admin\ReportController::class, 'systemReports'])->name('system-reports.index');

    // ==================== Game Management ====================
    Route::get('game-types', [App\Http\Controllers\Admin\GameTypeController::class, 'index'])->name('game-types.index');
    Route::patch('game-types/{id}/toggle-status', [App\Http\Controllers\Admin\GameTypeController::class, 'toggleStatus'])->name('game-types.toggleStatus');
    
    // Game Lists Management
    Route::get('game-lists', [App\Http\Controllers\Admin\GameListController::class, 'GetGameList'])->name('gameLists.index');
    Route::patch('game-lists/{id}/toggle-status', [App\Http\Controllers\Admin\GameListController::class, 'toggleStatus'])->name('gameLists.toggleStatus');
    Route::patch('game-lists/{id}/hot-game-status', [App\Http\Controllers\Admin\GameListController::class, 'HotGameStatus'])->name('HotGame.toggleStatus');
    Route::patch('game-lists/{id}/pp-hot-game-status', [App\Http\Controllers\Admin\GameListController::class, 'PPHotGameStatus'])->name('PPHotGame.toggleStatus');
    Route::get('game-lists/{gameList}/order-edit', [App\Http\Controllers\Admin\GameListController::class, 'GameListOrderedit'])->name('game_list_order.edit');
    Route::put('game-lists/{id}/update-order', [App\Http\Controllers\Admin\GameListController::class, 'updateOrder'])->name('game_list.update_order');
    Route::post('game-lists/update-all-order', [App\Http\Controllers\Admin\GameListController::class, 'updateAllOrder'])->name('game_list.update_all_order');
    Route::get('game-lists/{gameList}/edit', [App\Http\Controllers\Admin\GameListController::class, 'edit'])->name('game_list.edit');
    Route::post('game-lists/{id}/update-image', [App\Http\Controllers\Admin\GameListController::class, 'updateImageUrl'])->name('game_list.update_image');
    
    Route::get('games', [App\Http\Controllers\Admin\GameController::class, 'index'])->name('games.index');

    // ==================== Provider Management ====================
    Route::resource('providers', App\Http\Controllers\Admin\ProviderController::class);
    Route::patch('providers/{id}/toggle-status', [App\Http\Controllers\Admin\ProviderController::class, 'toggleStatus'])->name('providers.toggleStatus');

    // ==================== Banner & Promotion Management ====================
    Route::resource('video-upload', AdsVedioController::class);
    Route::resource('winner_text', WinnerTextController::class);
    Route::resource('banners', BannerController::class);
    Route::resource('adsbanners', BannerAdsController::class);
    Route::resource('text', BannerTextController::class);
    Route::resource('promotions', PromotionController::class);

    // ==================== Contact Management ====================
    Route::resource('contacts', ContactController::class);
    Route::get('contact', [ContactController::class, 'playerContact'])->name('contact.index');

    // ==================== Player-specific Routes ====================
    Route::get('my-deposits', [App\Http\Controllers\Admin\PlayerDepositController::class, 'index'])->name('my-deposits.index');
    Route::get('my-withdrawals', [App\Http\Controllers\Admin\PlayerWithdrawalController::class, 'index'])->name('my-withdrawals.index');
    Route::get('my-banks', [App\Http\Controllers\Admin\PlayerBankController::class, 'index'])->name('my-banks.index');
    
    // ==================== SystemWallet-specific Routes ====================
    Route::get('system-deposits', [App\Http\Controllers\Admin\SystemDepositController::class, 'index'])->name('system-deposits.index');
    Route::get('system-withdrawals', [App\Http\Controllers\Admin\SystemWithdrawalController::class, 'index'])->name('system-withdrawals.index');
});
