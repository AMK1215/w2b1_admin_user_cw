# Visual Flow Diagrams - GSC+ Custom Wallet

## 📊 System Architecture Overview

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         FRONTEND (React + Vite)                         │
│                     https://gamestar77.online                           │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐                │
│  │   Mobile     │  │   Desktop    │  │   Tablet     │                │
│  │  Interface   │  │  Interface   │  │  Interface   │                │
│  └──────────────┘  └──────────────┘  └──────────────┘                │
│                                                                         │
│  Components:                                                            │
│  • NavBar (Desktop) / BottomMenu (Mobile)                              │
│  • Game Listings (All, Hot, Favorites)                                 │
│  • Wallet (Deposit, Withdraw, Transfer, History)                       │
│  • Game Launch (iframe/new window)                                     │
│  • Profile Management                                                   │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
                                 ↓ ↑
                        HTTPS REST API (JSON)
                    Laravel Sanctum Token Auth
                                 ↓ ↑
┌─────────────────────────────────────────────────────────────────────────┐
│                         BACKEND (Laravel 10)                            │
│                           PHP 8.1+                                      │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  ┌───────────────┐  ┌────────────────┐  ┌──────────────────┐         │
│  │  API Routes   │  │  Admin Routes  │  │  Webhook Routes  │         │
│  │  /api/*       │  │  /admin/*      │  │  /api/v1/api/*   │         │
│  └───────────────┘  └────────────────┘  └──────────────────┘         │
│                                                                         │
│  Controllers:                                                           │
│  • AuthController (login, register)                                    │
│  • LaunchGameController (game launching)                               │
│  • GSCPlusProviderController (games, providers)                        │
│  • DepositController, WithdrawController (webhooks)                    │
│  • AdminController (dashboard, players, reports)                       │
│                                                                         │
│  Services:                                                              │
│  • CustomWalletService (wallet operations)                             │
│  • ApiResponseService (standardized responses)                         │
│  • ReportService (analytics)                                           │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
                                 ↓ ↑
                         MySQL Database
                                 ↓ ↑
┌─────────────────────────────────────────────────────────────────────────┐
│                           DATABASE (MySQL)                              │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  • users (players, owners, system wallet)                              │
│  • custom_transactions (wallet transactions)                           │
│  • place_bets (game bet logs)                                          │
│  • game_lists (available games)                                        │
│  • products (game providers)                                           │
│  • deposit_requests, withdraw_requests                                 │
│  • transaction_logs (audit trail)                                      │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
                                 ↕
                    External Game Provider
                                 ↕
┌─────────────────────────────────────────────────────────────────────────┐
│                      GSC PLUS GAME PROVIDER API                         │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  We call them:                                                          │
│  • POST /api/operators/launch-game (get game URL)                      │
│  • GET  /api/operators/provider-games (get game list)                  │
│  • GET  /api/operators/product-list (get providers)                    │
│                                                                         │
│  They call us (webhooks):                                              │
│  • POST /api/v1/api/seamless/balance (get player balance)              │
│  • POST /api/v1/api/seamless/withdraw (deduct balance - bet)           │
│  • POST /api/v1/api/seamless/deposit (add balance - win)               │
│  • POST /api/v1/api/seamless/pushbetdata (bet data logs)               │
│                                                                         │
│  Providers: Pragmatic Play, PG Soft, Jili, Joker, CQ9, etc.            │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 🔄 Complete Player Journey Flow

```
┌─────────────────────────────────────────────────────────────────────────┐
│                        1. PLAYER REGISTRATION                           │
└─────────────────────────────────────────────────────────────────────────┘

Player opens website
       ↓
Clicks "Register"
       ↓
Fills registration form:
   • Phone number
   • Name
   • Password
   • Referral code (from Owner)
   • Bank account details
       ↓
POST /api/register
       ↓
Backend validates referral code
       ↓
Creates new user:
   • user_name: Pi12345678 (auto-generated)
   • type: 20 (Player)
   • agent_id: owner.id
   • balance: 0.00
   • status: 1 (active)
   • Assign Player role
       ↓
Return: {user_name, name, phone}
       ↓
Player receives username
       ↓
Player can now login

═══════════════════════════════════════════════════════════════════════════

┌─────────────────────────────────────────────────────────────────────────┐
│                          2. PLAYER LOGIN                                │
└─────────────────────────────────────────────────────────────────────────┘

Player enters:
   • Username: Pi12345678
   • Password: ********
       ↓
POST /api/login
       ↓
Backend validates:
   • Credentials match?
   • Status = 1 (active)?
   • is_changed_password = 1?
   • Role = Player?
       ↓
Generate Sanctum token
Delete old tokens
Log user activity
       ↓
Return: {token, user_data, balance}
       ↓
Frontend stores:
   • localStorage.token
   • localStorage.userProfile
       ↓
Start polling /api/user every 5 seconds
   (for real-time balance updates)
       ↓
Player is now logged in

═══════════════════════════════════════════════════════════════════════════

┌─────────────────────────────────────────────────────────────────────────┐
│                       3. BROWSE & LAUNCH GAME                           │
└─────────────────────────────────────────────────────────────────────────┘

Player views homepage
       ↓
Frontend calls:
   • GET /api/game_types → [SLOT, LIVE, FISH, TABLE, ...]
   • GET /api/product-list → [Pragmatic, PG Soft, Jili, ...]
       ↓
Player selects game category (e.g., SLOT)
       ↓
GET /api/game_lists/SLOT/1001
       ↓
Backend returns game list with:
   • game_code, game_name, image_url
   • hot_status, pp_hot_status
   • provider info
       ↓
Player clicks on game (e.g., "Sweet Bonanza")
       ↓
POST /api/seamless/launch-game
Headers: Authorization: Bearer {token}
Body: {
   game_code: "vs20sbpramatic",
   product_code: 1001,
   game_type: "SLOT"
}
       ↓
Backend (LaunchGameController):
   1. Validate user is authenticated
   2. Get or generate game_provider_password
      (50-char random, encrypted, stored in DB)
   3. Build payload:
      • operator_code: from config
      • member_account: user_name
      • password: game_provider_password
      • game_code, product_code, game_type
      • currency: MMK or MMK2
      • signature: MD5(requestTime + secretKey + 'launchgame' + agentCode)
   4. POST to provider API
       ↓
Provider API validates and returns:
   {
      code: 200,
      url: "https://provider.com/game?token=...",
      content: "..." (for some providers)
   }
       ↓
Backend returns game URL/content
       ↓
Frontend opens game:
   • Desktop: iframe or new window
   • Mobile: fullscreen iframe or redirect
       ↓
Player starts playing

═══════════════════════════════════════════════════════════════════════════

┌─────────────────────────────────────────────────────────────────────────┐
│                    4. GAMEPLAY - BET FLOW (WITHDRAW)                    │
└─────────────────────────────────────────────────────────────────────────┘

Player places bet in game
       ↓
Game Provider calls our webhook:
POST /api/v1/api/seamless/withdraw
Body: {
   operator_code: "...",
   currency: "MMK2",
   sign: "..." (MD5 signature),
   request_time: 1634567890000,
   batch_requests: [{
      member_account: "Pi12345678",
      product_code: 1001,
      game_type: "SLOT",
      transactions: [{
         id: "txn_123456",
         action: "BET",
         amount: 10,  // In provider currency
         game_code: "vs20sbpramatic",
         wager_code: "round_123",
         wager_status: "UNSETTLED"
      }]
   }]
}
       ↓
Backend (WithdrawController):
   1. Validate signature:
      MD5(operatorCode + requestTime + 'withdraw' + secretKey)
   2. Validate currency (MMK2 allowed)
   3. Find user by member_account
   4. Check duplicate transaction_id
   5. Convert amount: 10 * 1000 = 10,000 internal units
   6. DB::transaction {
      • Lock user row: lockForUpdate()
      • Check balance >= 10,000
      • Deduct: balance = balance - 10,000
      • Log in custom_transactions
      • Log in place_bets
   }
   7. Format response balance: 10,000 / 1000 = 10.0000
       ↓
Return: {
   member_account: "Pi12345678",
   before_balance: 100.0000,  // In provider currency
   balance: 90.0000,           // In provider currency
   code: 200,
   message: "Transaction processed successfully"
}
       ↓
Provider receives confirmation
       ↓
Game continues with bet placed

═══════════════════════════════════════════════════════════════════════════

┌─────────────────────────────────────────────────────────────────────────┐
│                    5. GAMEPLAY - WIN FLOW (DEPOSIT)                     │
└─────────────────────────────────────────────────────────────────────────┘

Player wins in game
       ↓
Game Provider calls our webhook:
POST /api/v1/api/seamless/deposit
Body: {
   operator_code: "...",
   currency: "MMK2",
   sign: "...",
   request_time: 1634567890000,
   batch_requests: [{
      member_account: "Pi12345678",
      product_code: 1001,
      game_type: "SLOT",
      transactions: [{
         id: "txn_789012",
         action: "WIN" or "SETTLED",
         amount: 50,  // Win amount in provider currency
         game_code: "vs20sbpramatic",
         wager_code: "round_123",
         wager_status: "SETTLED",
         prize_amount: 50
      }]
   }]
}
       ↓
Backend (DepositController):
   1. Validate signature
   2. Validate currency
   3. Find user
   4. Check duplicate transaction_id
   5. Validate action (WIN, SETTLED, JACKPOT, BONUS, etc.)
   6. Convert amount: 50 * 1000 = 50,000 internal units
   7. DB::transaction {
      • Lock user row
      • Add: balance = balance + 50,000
      • Log in custom_transactions
      • Log in place_bets (action: WIN, status: completed)
   }
   8. Format response balance
       ↓
Return: {
   member_account: "Pi12345678",
   before_balance: 90.0000,
   balance: 140.0000,  // 90 + 50
   code: 200,
   message: ""
}
       ↓
Provider receives confirmation
       ↓
Game shows win animation
       ↓
Player sees updated balance in frontend
(via next polling cycle of /api/user)

═══════════════════════════════════════════════════════════════════════════

┌─────────────────────────────────────────────────────────────────────────┐
│                      6. PLAYER DEPOSIT REQUEST                          │
└─────────────────────────────────────────────────────────────────────────┘

Player wants to add funds
       ↓
Clicks "Deposit" in wallet section
       ↓
Fills deposit form:
   • Amount: 100,000
   • Payment method: KBZ Pay
   • Account name: Player Name
   • Account number: 09123456789
   • Reference/Transaction number: TXN12345
   • Upload screenshot (optional)
       ↓
POST /api/depositfinicial
Headers: Authorization: Bearer {token}
Body: {
   amount: 100000,
   payment_type_id: 1,
   account_name: "Player Name",
   account_number: "09123456789",
   reference_number: "TXN12345"
}
       ↓
Backend creates deposit_request:
   • user_id: player.id
   • amount: 100000
   • status: 'pending'
   • payment_type_id, account details
       ↓
Return: {status: "success", message: "Deposit request created"}
       ↓
Player sees "Pending approval" message

───────────────── OWNER APPROVAL ─────────────────

Owner logs in to admin panel
       ↓
Navigates to Deposits section
       ↓
GET /admin/deposits
       ↓
Sees list of pending deposits
       ↓
Clicks on deposit request
       ↓
Reviews details:
   • Player name, amount
   • Payment method
   • Reference number
   • Screenshot
       ↓
Clicks "Approve"
       ↓
POST /admin/deposits/{id}/approve
       ↓
Backend (DepositRequestController):
   1. Get SystemWallet user (admin)
   2. Get Player user
   3. CustomWalletService.transfer(
         from: systemWallet,
         to: player,
         amount: 100000
      ) {
         DB::transaction {
            • Lock both users
            • systemWallet.balance -= 100000
            • player.balance += 100000
            • Log 2 custom_transactions
         }
      }
   4. Update deposit_request:
      • status: 'approved'
      • approved_by: owner.id
      • approved_at: now()
       ↓
Return: success message
       ↓
Player sees updated balance in frontend
(via polling /api/user)

═══════════════════════════════════════════════════════════════════════════

┌─────────────────────────────────────────────────────────────────────────┐
│                     7. PLAYER WITHDRAWAL REQUEST                        │
└─────────────────────────────────────────────────────────────────────────┘

Player wants to withdraw funds
       ↓
Clicks "Withdraw" in wallet section
       ↓
Fills withdrawal form:
   • Amount: 50,000
   • Payment method: KBZ Pay
   • Account name: Player Name
   • Account number: 09123456789
       ↓
POST /api/withdrawfinicial
Headers: Authorization: Bearer {token}
Body: {
   amount: 50000,
   payment_type_id: 1,
   account_name: "Player Name",
   account_number: "09123456789"
}
       ↓
Backend validates:
   • Player has sufficient balance?
       ↓
If sufficient:
   Creates withdraw_request:
      • user_id: player.id
      • amount: 50000
      • status: 'pending'
      • payment details
       ↓
Return: {status: "success", message: "Withdrawal request created"}
       ↓
Player sees "Pending approval" message

───────────────── OWNER APPROVAL ─────────────────

Owner logs in to admin panel
       ↓
Navigates to Withdrawals section
       ↓
GET /admin/withdrawals
       ↓
Sees list of pending withdrawals
       ↓
Clicks on withdrawal request
       ↓
Reviews details and clicks "Approve"
       ↓
POST /admin/withdrawals/{id}/approve
       ↓
Backend (WithDrawRequestController):
   1. Get Player user
   2. Get SystemWallet user
   3. Validate player still has balance
   4. CustomWalletService.transfer(
         from: player,
         to: systemWallet,
         amount: 50000
      )
   5. Update withdraw_request:
      • status: 'approved'
      • approved_by: owner.id
      • approved_at: now()
       ↓
Owner manually sends money to player's bank account
       ↓
Player receives money in bank account
       ↓
Player sees reduced balance in frontend

═══════════════════════════════════════════════════════════════════════════

┌─────────────────────────────────────────────────────────────────────────┐
│                    8. OWNER CASH-IN TO PLAYER                           │
└─────────────────────────────────────────────────────────────────────────┘

Owner wants to add balance to player directly
       ↓
Owner logs in to admin panel
       ↓
Navigates to Players section
       ↓
GET /admin/players
       ↓
Clicks on a player
       ↓
Clicks "Cash In" button
       ↓
GET /admin/players/{id}/cash-in
       ↓
Fills form:
   • Amount: 200,000
   • Remarks: "Initial balance"
       ↓
POST /admin/players/{id}/cash-in
Body: {
   amount: 200000,
   remarks: "Initial balance"
}
       ↓
Backend (PlayerController):
   1. Get Owner (logged in user)
   2. Get Player
   3. Validate owner has sufficient balance
   4. CustomWalletService.transfer(
         from: owner,
         to: player,
         amount: 200000,
         type: TransactionName::CashIn
      ) {
         DB::transaction {
            • Lock both users
            • owner.balance -= 200000
            • player.balance += 200000
            • Log 2 transactions
         }
      }
       ↓
Return: success message
       ↓
Owner sees updated balances in dashboard
       ↓
Player sees updated balance in frontend

═══════════════════════════════════════════════════════════════════════════

┌─────────────────────────────────────────────────────────────────────────┐
│                    9. REAL-TIME BALANCE UPDATES                         │
└─────────────────────────────────────────────────────────────────────────┘

After player login:
       ↓
AuthContext starts interval (every 5 seconds):
       ↓
   GET /api/user
   Headers: Authorization: Bearer {token}
       ↓
   Backend returns current user data:
   {
      id, user_name, name, phone,
      balance: 140000.00,
      main_balance: 140000.00,
      ...
   }
       ↓
   Frontend compares with stored profile
       ↓
   If balance changed:
      • Update localStorage.userProfile
      • Update React state
      • UI automatically re-renders
      • Player sees new balance
       ↓
   If 401 Unauthorized:
      • Auto logout
      • Clear localStorage
      • Redirect to login
       ↓
Repeat every 5 seconds while logged in

Alternative (future): Use Laravel Reverb WebSocket
   • Backend broadcasts balance updates
   • Frontend listens to channel
   • Instant updates without polling
```

---

## 🏗️ Admin Panel Flow

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         ADMIN PANEL LOGIN                               │
└─────────────────────────────────────────────────────────────────────────┘

Owner/SystemWallet visits /login (web route)
       ↓
Enters credentials:
   • Username
   • Password
       ↓
POST /login (web route, not API)
       ↓
Backend (LoginController):
   1. Validate credentials
   2. Check status = 1 (active)
   3. Check user type:
      
      If type = 20 (Player):
         • Auth::logout()
         • Return error: "Players not allowed in admin panel"
         • Player must use React frontend
      
      If type = 10 (Owner):
         • Log user activity
         • Redirect to /admin
         • DashboardController checks type
         • Render: admin/dashboard/owner.blade.php
         • Shows:
            - Total players count
            - Active players count
            - Total player balance
            - Owner balance
            - Recent players table
            - Recent transactions
            - Quick actions
      
      If type = 30 (SystemWallet):
         • Log user activity
         • Redirect to /admin
         • DashboardController checks type
         • Render: admin/dashboard/system-wallet.blade.php
         • Shows:
            - System wallet balance
            - Total users
            - Total system balance
            - Recent system transactions
            - System statistics

═══════════════════════════════════════════════════════════════════════════

┌─────────────────────────────────────────────────────────────────────────┐
│                       OWNER DASHBOARD ACTIONS                           │
└─────────────────────────────────────────────────────────────────────────┘

From dashboard, Owner can:

1. MANAGE PLAYERS
   GET /admin/players
      ↓
   • View all players (owner's children)
   • Search by name, username, phone
   • Filter by status (active/inactive)
   • Sort by balance, created date
   • Click on player to view details
   • Actions:
      - Edit player info
      - Change password
      - Ban/unban player
      - Cash in/out
      - View transaction logs
      - View game logs

2. APPROVE DEPOSITS
   GET /admin/deposits
      ↓
   • List all deposit requests
   • Filter by status (pending/approved/rejected)
   • Filter by date range
   • Filter by player
   • Click on deposit:
      - View details
      - View screenshot
      - Approve → Transfer from SystemWallet to Player
      - Reject → Update status only

3. APPROVE WITHDRAWALS
   GET /admin/withdrawals
      ↓
   • List all withdrawal requests
   • Filter by status
   • Click on withdrawal:
      - View details
      - Check player balance
      - Approve → Transfer from Player to SystemWallet
      - Reject → Update status only
      - Manually send money to player's bank

4. VIEW REPORTS
   GET /admin/reports
      ↓
   • Select date range
   • View statistics:
      - Total players
      - New players (period)
      - Active players
      - Total deposits
      - Total withdrawals
      - Net profit/loss
      - Transaction breakdown
      - Game statistics
   • Export to PDF/Excel

5. MANAGE GAMES
   GET /admin/game-lists
      ↓
   • List all games from database
   • Filter by provider, game type
   • Enable/disable games
   • Mark games as "hot"
   • Update game images
   • Set game display order
   • Search games

6. MANAGE PROVIDERS
   GET /admin/providers
      ↓
   • List all game providers
   • Add new provider
   • Edit provider details
   • Enable/disable provider
   • Set provider order

7. MANAGE BANNERS & PROMOTIONS
   • Upload banner images
   • Create promotions
   • Set active periods
   • Order banners
   • Preview banners

8. VIEW TRANSACTION LOGS
   GET /admin/transfer-log
      ↓
   • All transfers in system
   • Filter by user, date, type
   • View transaction details
   • Export logs
```

---

## 🔐 Security & Validation Flow

```
┌─────────────────────────────────────────────────────────────────────────┐
│                     WEBHOOK SIGNATURE VALIDATION                        │
└─────────────────────────────────────────────────────────────────────────┘

Provider sends webhook request:
{
   operator_code: "OPERATOR",
   request_time: 1634567890000,
   sign: "abc123...",
   ...
}
       ↓
Backend receives request
       ↓
Get secret key from config:
   secretKey = config('seamless_key.secret_key')
       ↓
Determine endpoint type:
   • Balance → method = 'getbalance'
   • Deposit → method = 'deposit'
   • Withdraw → method = 'withdraw'
       ↓
Generate expected signature:
   expectedSign = MD5(
      operatorCode + requestTime + method + secretKey
   )
       ↓
Compare signatures (case-insensitive):
   if (strtolower($request->sign) !== strtolower($expectedSign)) {
       return ERROR: Invalid signature (code 1002)
   }
       ↓
Signature valid → Process request

═══════════════════════════════════════════════════════════════════════════

┌─────────────────────────────────────────────────────────────────────────┐
│                      DUPLICATE TRANSACTION CHECK                        │
└─────────────────────────────────────────────────────────────────────────┘

Webhook received with transaction_id: "txn_123456"
       ↓
Check in place_bets table:
   PlaceBet::where('transaction_id', 'txn_123456')->exists()
       ↓
Check in custom_transactions table:
   CustomTransaction::whereJsonContains(
      'meta->seamless_transaction_id',
      'txn_123456'
   )->exists()
       ↓
If found in either table:
   return ERROR: Duplicate transaction (code 1005)
       ↓
If not found:
   Proceed with transaction

═══════════════════════════════════════════════════════════════════════════

┌─────────────────────────────────────────────────────────────────────────┐
│                    CONCURRENT TRANSACTION HANDLING                      │
└─────────────────────────────────────────────────────────────────────────┘

Two webhooks arrive simultaneously for same player:
       ↓
Both requests start DB::transaction
       ↓
Request A:
   User::where('id', $userId)->lockForUpdate()->first()
   (Gets row lock)
       ↓
Request B:
   User::where('id', $userId)->lockForUpdate()->first()
   (Waits for lock to be released)
       ↓
Request A processes:
   • Read balance: 100,000
   • Deduct: 10,000
   • New balance: 90,000
   • Update user
   • Commit transaction
   • Release lock
       ↓
Request B gets lock:
   • Read balance: 90,000 (updated by A)
   • Deduct: 5,000
   • New balance: 85,000
   • Update user
   • Commit transaction
   • Release lock
       ↓
Final balance: 85,000 (correct)
       ↓
Without locking:
   • Both read: 100,000
   • A writes: 90,000
   • B writes: 95,000
   • Final: 95,000 (WRONG - lost 10,000)
```

---

## 📊 Data Flow Summary

```
                          ┌──────────────┐
                          │   PLAYER     │
                          └──────┬───────┘
                                 │
                    ┌────────────┴────────────┐
                    │                         │
              (Action: Play)            (Action: Deposit/Withdraw)
                    │                         │
                    ↓                         ↓
           ┌─────────────────┐       ┌─────────────────┐
           │  Launch Game    │       │ Create Request  │
           │  React Frontend │       │ React Frontend  │
           └────────┬────────┘       └────────┬────────┘
                    │                         │
                    │ POST /launch-game       │ POST /depositfinicial
                    ↓                         ↓
           ┌─────────────────────────────────────────────┐
           │        Laravel Backend (API Layer)          │
           ├─────────────────────────────────────────────┤
           │  • AuthController                           │
           │  • LaunchGameController                     │
           │  • DepositRequestController                 │
           │  • WithDrawRequestController                │
           └──────┬──────────────────┬───────────────────┘
                  │                  │
     ┌────────────┴──────┐           │
     │                   │           │
     ↓                   ↓           ↓
┌─────────┐    ┌──────────────────┐ ┌──────────────┐
│ Provider│    │ CustomWallet     │ │ Database     │
│   API   │    │    Service       │ │   MySQL      │
└────┬────┘    └────────┬─────────┘ └──────┬───────┘
     │                  │                  │
     │ Game URL         │ Balance ops      │ CRUD ops
     │                  │                  │
     ↓                  ↓                  ↓
┌─────────────────────────────────────────────────┐
│              Provider Webhooks                  │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐     │
│  │ Balance  │  │ Withdraw │  │ Deposit  │     │
│  │ Webhook  │  │ Webhook  │  │ Webhook  │     │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘     │
│       └──────────────┼──────────────┘           │
└──────────────────────┼─────────────────────────┘
                       │
                       ↓
              ┌────────────────┐
              │ CustomWallet   │
              │   Service      │
              ├────────────────┤
              │ • Deposit      │
              │ • Withdraw     │
              │ • Transfer     │
              └────────┬───────┘
                       │
                       ↓
              ┌────────────────┐
              │   Database     │
              ├────────────────┤
              │ • users        │
              │ • custom_      │
              │   transactions │
              │ • place_bets   │
              └────────────────┘
```

---

## 🎯 Key Transaction Points

### Transaction Safety
```
Every balance change follows this pattern:

DB::transaction(function() {
    ↓
    1. Lock user row(s):
       $user = User::where('id', $userId)->lockForUpdate()->first()
    ↓
    2. Read current balance:
       $oldBalance = $user->balance
    ↓
    3. Validate (if needed):
       if ($oldBalance < $amount) throw Exception
    ↓
    4. Calculate new balance:
       $newBalance = $oldBalance +/- $amount
    ↓
    5. Update user:
       $user->update(['balance' => $newBalance])
    ↓
    6. Log transaction:
       CustomTransaction::create([...])
    ↓
    7. Commit transaction
       (automatic on function end)
    ↓
    8. Release locks
});
```

### Currency Conversion
```
Provider → Our System (Incoming):
────────────────────────────────
Provider sends: 10 MMK2
Convert: 10 * 1000 = 10,000 internal units
Store: 10,000 in users.balance

Our System → Provider (Outgoing):
──────────────────────────────────
Read from DB: 10,000 internal units
Convert: 10,000 / 1000 = 10.0000 MMK2
Send: 10.0000 to provider

Why? Provider uses 1:1000 conversion for MMK2
```

---

**End of Flow Diagrams**

