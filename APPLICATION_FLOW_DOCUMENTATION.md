# Application Flow Documentation - GSC+ Custom Wallet

## 📋 Table of Contents
1. [System Overview](#system-overview)
2. [Architecture](#architecture)
3. [User Types & Roles](#user-types--roles)
4. [Backend Flow](#backend-flow)
5. [Frontend Flow](#frontend-flow)
6. [Authentication Flow](#authentication-flow)
7. [Game Launch Flow](#game-launch-flow)
8. [Wallet & Transaction Flow](#wallet--transaction-flow)
9. [Admin Panel Flow](#admin-panel-flow)
10. [API Documentation](#api-documentation)
11. [Database Structure](#database-structure)
12. [Key Services](#key-services)

---

## 🎯 System Overview

**Project Name:** GSC+ Custom Wallet (GameStar Admin User CW)  
**Tech Stack:**
- **Backend:** Laravel 10.x (PHP)
- **Frontend:** React 18 + Vite
- **Real-time:** Laravel Reverb (WebSocket broadcasting)
- **Database:** MySQL
- **API:** RESTful API with Laravel Sanctum authentication
- **Game Provider:** GSC Plus API Integration

**Purpose:** A gaming platform with custom wallet system that manages players, game launches, deposits/withdrawals, and real-time game transactions through seamless wallet integration.

---

## 🏗️ Architecture

### System Architecture
```
┌─────────────────────────────────────────────────────────┐
│                    CLIENT LAYER                         │
├─────────────────────────────────────────────────────────┤
│  React Frontend (client_react/)                         │
│  - Mobile-first responsive design                       │
│  - Context API (Auth, Game, General, Language)          │
│  - React Router for navigation                          │
│  - Real-time balance updates (5s polling)               │
└─────────────────────────────────────────────────────────┘
                          ↓ HTTPS/REST API
┌─────────────────────────────────────────────────────────┐
│                    API LAYER                            │
├─────────────────────────────────────────────────────────┤
│  Laravel Backend (routes/api.php, routes/web.php)       │
│  - Sanctum Authentication                               │
│  - RESTful API endpoints                                │
│  - Middleware: auth:sanctum, checkBanned, etc.          │
└─────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────┐
│                  CONTROLLER LAYER                       │
├─────────────────────────────────────────────────────────┤
│  Controllers (app/Http/Controllers/)                    │
│  - AuthController (login, register)                     │
│  - LaunchGameController (game launching)                │
│  - Webhook Controllers (deposit, withdraw, balance)     │
│  - Admin Controllers (dashboard, players, reports)      │
└─────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────┐
│                   SERVICE LAYER                         │
├─────────────────────────────────────────────────────────┤
│  Services (app/Services/)                               │
│  - CustomWalletService (wallet operations)              │
│  - ApiResponseService (standardized responses)          │
│  - BuffaloGameService (custom game logic)               │
│  - ShanApiService (Shan game integration)               │
│  - ReportService (reporting functionality)              │
└─────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────┐
│                    MODEL LAYER                          │
├─────────────────────────────────────────────────────────┤
│  Models (app/Models/)                                   │
│  - User (players, owners, system wallet)                │
│  - CustomTransaction (wallet transactions)              │
│  - PlaceBet (game bet logs)                             │
│  - GameList, Product, GameType                          │
│  - DepositRequest, WithdrawRequest                      │
└─────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────┐
│                   DATABASE LAYER                        │
├─────────────────────────────────────────────────────────┤
│  MySQL Database                                         │
│  - users, custom_transactions, place_bets               │
│  - game_lists, products, game_types                     │
│  - deposit_requests, withdraw_requests                  │
└─────────────────────────────────────────────────────────┘
                          ↕
┌─────────────────────────────────────────────────────────┐
│              EXTERNAL INTEGRATIONS                      │
├─────────────────────────────────────────────────────────┤
│  GSC Plus Game Provider API                             │
│  - Launch game endpoint                                 │
│  - Product/Game list endpoints                          │
│                                                         │
│  Seamless Wallet Webhooks (Provider → Our System)       │
│  - GET balance webhook                                  │
│  - POST deposit webhook                                 │
│  - POST withdraw webhook                                │
│  - POST pushBetData webhook                             │
└─────────────────────────────────────────────────────────┘
```

---

## 👥 User Types & Roles

### 1. **Owner (UserType: 10)**
- **Purpose:** Manages players and system operations
- **Access:** Full admin panel access
- **Capabilities:**
  - Create and manage players
  - Approve/reject deposits and withdrawals
  - Transfer money to/from players
  - View comprehensive reports
  - Manage games, providers, banners, promotions
  - System configuration

### 2. **Player (UserType: 20)**
- **Purpose:** End users who play games
- **Access:** React frontend only (blocked from admin panel)
- **Capabilities:**
  - Register and login
  - Play games
  - Deposit/withdraw funds
  - View transaction history
  - View game logs
  - Contact support

### 3. **SystemWallet (UserType: 30)**
- **Purpose:** System-level operations and accounting
- **Access:** Limited admin panel access
- **Capabilities:**
  - View system dashboard
  - View system-wide reports
  - Manage system deposits/withdrawals
  - System transaction oversight

**⚠️ Important:** NO AGENT SYSTEM exists. The `agent_id` field in the database is actually used as `owner_id` for Owner→Player relationships.

---

## 🔧 Backend Flow

### Directory Structure
```
app/
├── Console/
│   └── Commands/          # Artisan commands
├── Enums/                # Enumerations (UserType, TransactionName, etc.)
├── Exceptions/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/       # Admin panel controllers
│   │   └── Api/         # API controllers for frontend
│   ├── Middleware/      # Auth, CORS, etc.
│   └── Requests/        # Form request validation
├── Models/              # Eloquent models
├── Providers/           # Service providers
├── Services/            # Business logic services
└── Traits/              # Reusable traits

routes/
├── api.php             # API routes for React frontend
├── web.php             # Web routes (admin login)
└── admin.php           # Admin panel routes

config/
├── seamless_key.php    # GSC Plus API configuration
├── shan_key.php        # Shan game configuration
└── broadcasting.php    # Laravel Reverb config
```

### Key Backend Components

#### 1. **Routes (routes/api.php)**

**Public Routes:**
```php
POST /api/login                    # Player login
POST /api/register                 # Player registration
POST /api/logout                   # Logout
GET  /api/product-list             # Get game providers
GET  /api/operators/provider-games # Get games by provider
GET  /api/promotion                # Get promotions
GET  /api/banner                   # Get banners
GET  /api/game_types               # Get game categories
GET  /api/providers/{type}         # Get providers by type
GET  /api/game_lists/{type}/{provider} # Get games
```

**Authenticated Routes (auth:sanctum):**
```php
GET  /api/user                     # Get current user info
POST /api/seamless/launch-game     # Launch a game
GET  /api/player/game-logs         # Get player's game logs
POST /api/depositfinicial          # Create deposit request
GET  /api/depositlogfinicial       # Get deposit history
POST /api/withdrawfinicial         # Create withdrawal request
GET  /api/withdrawlogfinicial      # Get withdrawal history
POST /api/change-password          # Change password
GET  /api/contact                  # Get contact info
```

**Webhook Routes (Called by Game Provider):**
```php
POST /api/v1/api/seamless/balance      # Get player balance
POST /api/v1/api/seamless/withdraw     # Debit player (bet placed)
POST /api/v1/api/seamless/deposit      # Credit player (win)
POST /api/v1/api/seamless/pushbetdata  # Push bet data logs
```

**Buffalo Game Routes:**
```php
POST /api/buffalo/get-user-balance  # Get balance (webhook)
POST /api/buffalo/change-balance    # Change balance (webhook)
GET  /api/buffalo/game-auth         # Generate game auth (authenticated)
POST /api/buffalo/game-url          # Generate game URL (authenticated)
POST /api/buffalo/launch-game       # Launch Buffalo game (authenticated)
```

#### 2. **Admin Routes (routes/admin.php)**

Protected by middleware: `['auth', 'checkBanned', 'preventPlayerAccess']`

```php
# Dashboard
GET /admin/                        # Role-based dashboard

# Player Management
GET    /admin/players              # List players
POST   /admin/players              # Create player
GET    /admin/players/{id}         # View player
PUT    /admin/players/{id}         # Update player
DELETE /admin/players/{id}         # Delete player
POST   /admin/players/{id}/cash-in # Deposit to player
POST   /admin/players/{id}/cash-out # Withdraw from player
GET    /admin/players/{id}/logs    # Player transaction logs

# Deposits & Withdrawals
GET  /admin/deposits               # List deposits
POST /admin/deposits/{id}/approve  # Approve deposit
POST /admin/deposits/{id}/reject   # Reject deposit
GET  /admin/withdrawals            # List withdrawals
POST /admin/withdrawals/{id}/approve # Approve withdrawal
POST /admin/withdrawals/{id}/reject  # Reject withdrawal

# Game Management
GET   /admin/game-types            # List game types
PATCH /admin/game-types/{id}/toggle-status # Enable/disable
GET   /admin/game-lists            # List games
PATCH /admin/game-lists/{id}/toggle-status # Enable/disable game
PATCH /admin/game-lists/{id}/hot-game-status # Mark as hot

# Reports
GET /admin/reports                 # Owner reports
GET /admin/system-reports          # System-wide reports

# Banks, Promotions, Banners, Contacts (CRUD operations)
```

---

## 🎨 Frontend Flow

### Directory Structure
```
client_react/
├── public/
│   └── images/           # Static images
├── src/
│   ├── assets/           # Images, fonts, etc.
│   ├── components/       # Reusable components
│   │   ├── desktop/      # Desktop-specific components
│   │   └── mobile/       # Mobile-specific components
│   ├── contexts/         # React Context providers
│   │   ├── AuthContext.jsx      # Authentication state
│   │   ├── GameContext.jsx      # Game-related state
│   │   ├── GeneralContext.jsx   # General app state
│   │   └── LanguageContext.jsx  # i18n support
│   ├── hooks/            # Custom hooks
│   │   └── baseUrl.jsx   # API base URL
│   ├── pages/            # Page components
│   ├── routes/           # React Router setup
│   │   └── index.jsx     # Route definitions
│   ├── lang/             # Language files
│   ├── App.jsx           # Root component
│   └── main.jsx          # Entry point
├── package.json
└── vite.config.js        # Vite configuration
```

### Key Frontend Components

#### 1. **Layout Component**
```jsx
// client_react/src/components/Layout.jsx
<AuthContextProvider>
  <GeneralContextProvider>
    <GameContextProvider>
      <NavBar />           // Top navigation
      <Outlet />           // Page content
      <BottomMenu />       // Mobile bottom navigation
    </GameContextProvider>
  </GeneralContextProvider>
</AuthContextProvider>
```

#### 2. **Routes**
```javascript
// Main routes
/                 → HomePage (game listings)
/games            → GamesPage
/wallet           → WalletPage (deposit, withdraw, transfer)
/wallet/internal-transfer → InternalTransfer
/wallet-history   → WalletHistoryPage
/transactions     → TransactionsPage
/promotion        → Promotion
/contact          → ContactPage
/digitbet         → DigitBetGame (custom game)
/shan             → ShanGame (custom game)
/buffalo          → BuffaloGame (custom game)
/ponewine         → PoneWineGame (custom game)
/2d               → TwoDPage (2D lottery game)
/2d/bet           → TwoDBetPage
/2d/confirm       → TwoDConfirmPage
/3d               → ThreeDPage (3D lottery game)
/3d/confirm       → ThreeDConfirmPage
```

#### 3. **Context Providers**

**AuthContext:**
- Manages authentication token
- Stores user profile in localStorage
- Real-time balance updates (polls every 5 seconds)
- Auto-logout on 401 responses

**GameContext:**
- Manages game-related state
- Stores selected games
- Game launch URLs

**GeneralContext:**
- Banners, promotions, contacts
- Global app settings

**LanguageContext:**
- Multi-language support
- Language switching

---

## 🔐 Authentication Flow

### 1. **Player Registration Flow**
```
┌──────────┐      ┌──────────┐      ┌──────────┐      ┌──────────┐
│  Player  │      │  React   │      │ Laravel  │      │ Database │
│          │      │ Frontend │      │ Backend  │      │          │
└────┬─────┘      └────┬─────┘      └────┬─────┘      └────┬─────┘
     │                 │                 │                 │
     │ Fill registration form           │                 │
     │───────────────>│                 │                 │
     │                 │                 │                 │
     │                 │ POST /api/register               │
     │                 │ {phone, name, password,          │
     │                 │  referral_code, payment_info}    │
     │                 │──────────────>│                 │
     │                 │                 │                 │
     │                 │                 │ Validate referral_code
     │                 │                 │ (find Owner)    │
     │                 │                 │──────────────>│
     │                 │                 │                 │
     │                 │                 │ Create User     │
     │                 │                 │ - Generate username (Pi12345678)
     │                 │                 │ - Hash password │
     │                 │                 │ - Set agent_id = owner.id
     │                 │                 │ - Set type = Player (20)
     │                 │                 │ - balance = 0   │
     │                 │                 │──────────────>│
     │                 │                 │                 │
     │                 │                 │ Assign Role     │
     │                 │                 │ (Player role ID: 2)
     │                 │                 │──────────────>│
     │                 │                 │                 │
     │                 │  Success response                │
     │                 │  {user_name, token, user_data}   │
     │                 │<──────────────│                 │
     │                 │                 │                 │
     │  Show success   │                 │                 │
     │<───────────────│                 │                 │
```

### 2. **Player Login Flow**
```
┌──────────┐      ┌──────────┐      ┌──────────┐      ┌──────────┐
│  Player  │      │  React   │      │ Laravel  │      │ Database │
└────┬─────┘      └────┬─────┘      └────┬─────┘      └────┬─────┘
     │                 │                 │                 │
     │ Enter credentials                │                 │
     │───────────────>│                 │                 │
     │                 │                 │                 │
     │                 │ POST /api/login                  │
     │                 │ {user_name, password}            │
     │                 │──────────────>│                 │
     │                 │                 │                 │
     │                 │                 │ Validate credentials
     │                 │                 │──────────────>│
     │                 │                 │                 │
     │                 │                 │ Check status == 1
     │                 │                 │ Check is_changed_password == 1
     │                 │                 │ Check role == Player
     │                 │                 │                 │
     │                 │                 │ Create Sanctum token
     │                 │                 │ Delete old tokens
     │                 │                 │ Log UserLog     │
     │                 │                 │──────────────>│
     │                 │                 │                 │
     │                 │  Success response                │
     │                 │  {token, user_data}              │
     │                 │<──────────────│                 │
     │                 │                 │                 │
     │  Store token in localStorage     │                 │
     │  Start balance polling (5s)      │                 │
     │<───────────────│                 │                 │
     │                 │                 │                 │
     │                 │ GET /api/user (every 5s)         │
     │                 │ Authorization: Bearer {token}    │
     │                 │──────────────>│                 │
     │                 │                 │                 │
     │                 │  Updated user data               │
     │                 │  {balance, main_balance, ...}    │
     │                 │<──────────────│                 │
```

### 3. **Admin Login Flow**
```
Owner/SystemWallet login via web form
    ↓
POST /login (web route, not API)
    ↓
LoginController validates credentials
    ↓
Check user type:
    - If Player → Logout + Error "Players not allowed"
    - If Owner/SystemWallet → Redirect to admin dashboard
    ↓
Dashboard renders based on role:
    - Owner → admin/dashboard/owner.blade.php
    - SystemWallet → admin/dashboard/system-wallet.blade.php
```

---

## 🎮 Game Launch Flow

### Complete Game Launch Sequence
```
┌──────────┐      ┌──────────┐      ┌──────────┐      ┌──────────┐
│  Player  │      │  React   │      │ Laravel  │      │ GSC Plus │
│          │      │ Frontend │      │ Backend  │      │ Provider │
└────┬─────┘      └────┬─────┘      └────┬─────┘      └────┬─────┘
     │                 │                 │                 │
     │ Browse games    │                 │                 │
     │───────────────>│                 │                 │
     │                 │                 │                 │
     │                 │ GET /api/game_lists/{type}/{provider}
     │                 │──────────────>│                 │
     │                 │                 │                 │
     │                 │  Game list      │                 │
     │                 │<──────────────│                 │
     │                 │                 │                 │
     │ Click game      │                 │                 │
     │───────────────>│                 │                 │
     │                 │                 │                 │
     │                 │ POST /api/seamless/launch-game   │
     │                 │ Authorization: Bearer {token}    │
     │                 │ {game_code, product_code,        │
     │                 │  game_type}     │                 │
     │                 │──────────────>│                 │
     │                 │                 │                 │
     │                 │                 │ Get/Generate game_provider_password
     │                 │                 │ (50-char random, encrypted, stored)
     │                 │                 │                 │
     │                 │                 │ Build payload:  │
     │                 │                 │ - operator_code │
     │                 │                 │ - member_account (user_name)
     │                 │                 │ - password (game_provider_password)
     │                 │                 │ - game_code     │
     │                 │                 │ - product_code  │
     │                 │                 │ - currency (MMK/MMK2)
     │                 │                 │ - signature (MD5)
     │                 │                 │                 │
     │                 │                 │ POST /api/operators/launch-game
     │                 │                 │──────────────>│
     │                 │                 │                 │
     │                 │                 │  Game URL/Content
     │                 │                 │<──────────────│
     │                 │                 │                 │
     │                 │  {url, content} │                 │
     │                 │<──────────────│                 │
     │                 │                 │                 │
     │  Open game in iframe/new window  │                 │
     │<───────────────│                 │                 │
     │                 │                 │                 │
     │ Game starts → Provider calls webhooks              │
     │                 │                 │                 │
```

### Game Provider Password System
- First game launch: Generate random 50-character password
- Encrypt with Laravel's `Crypt::encryptString()`
- Store in `users.game_provider_password` field
- Subsequent launches: Decrypt and reuse same password
- **Important:** Same password must be used for the same player consistently

---

## 💰 Wallet & Transaction Flow

### 1. **Seamless Wallet Integration**

The system uses a "seamless wallet" where the game provider directly interacts with our wallet via webhooks.

#### A. Get Balance Webhook
```
Game Provider calls: POST /api/v1/api/seamless/balance
    ↓
GetBalanceController processes:
    - Validate signature (MD5 hash)
    - Find user by member_account (user_name)
    - Get user balance
    - Convert balance based on currency (MMK2: divide by 1000)
    - Return balance
```

**Request:**
```json
{
  "operator_code": "OPERATOR",
  "member_account": "Pi12345678",
  "currency": "MMK2",
  "sign": "...",
  "request_time": 1634567890000
}
```

**Response:**
```json
{
  "code": 200,
  "message": "Success",
  "member_account": "Pi12345678",
  "balance": 1000.5000,
  "currency": "MMK2"
}
```

#### B. Withdraw Webhook (Bet Placed)
```
Game Provider calls: POST /api/v1/api/seamless/withdraw
When: Player places a bet
    ↓
WithdrawController processes:
    - Validate signature
    - Validate currency
    - Find user
    - Check for duplicate transaction_id
    - Check sufficient balance
    - Process each transaction in batch:
        * Lock user row (lockForUpdate)
        * Deduct amount using CustomWalletService
        * Log in PlaceBet table
        * Log in CustomTransaction table
    - Return updated balance
```

**Request:**
```json
{
  "operator_code": "OPERATOR",
  "currency": "MMK2",
  "sign": "...",
  "request_time": 1634567890000,
  "batch_requests": [
    {
      "member_account": "Pi12345678",
      "product_code": 1001,
      "game_type": "SLOT",
      "transactions": [
        {
          "id": "txn_123456",
          "action": "BET",
          "amount": 100,
          "game_code": "SL001",
          "wager_code": "round_123",
          "wager_status": "UNSETTLED"
        }
      ]
    }
  ]
}
```

**Response:**
```json
{
  "code": 200,
  "message": "",
  "data": [
    {
      "member_account": "Pi12345678",
      "product_code": 1001,
      "before_balance": 1000.5000,
      "balance": 900.5000,
      "code": 200,
      "message": "Transaction processed successfully"
    }
  ]
}
```

#### C. Deposit Webhook (Win/Bonus)
```
Game Provider calls: POST /api/v1/api/seamless/deposit
When: Player wins, gets bonus, or bet is cancelled
    ↓
DepositController processes:
    - Validate signature
    - Validate currency
    - Find user
    - Check for duplicate transaction_id
    - Validate action (WIN, SETTLED, JACKPOT, BONUS, CANCEL, etc.)
    - Process each transaction:
        * Lock user row
        * Add amount using CustomWalletService
        * Log in PlaceBet table
        * Log in CustomTransaction table
    - Return updated balance
```

**Actions:**
- `WIN`: Player wins bet
- `SETTLED`: Round settled with winnings
- `JACKPOT`: Jackpot won
- `BONUS`: Bonus awarded
- `CANCEL`: Bet cancelled (refund)
- `PROMO`: Promotional credit

### 2. **CustomWalletService**

Core wallet operations service that handles all balance changes.

#### Key Methods:

**deposit(User $user, float $amount, TransactionName $type, array $meta)**
```php
DB::transaction(function() {
    // Lock user row
    $lockedUser = User::where('id', $user->id)->lockForUpdate()->first();
    
    // Calculate new balance
    $newBalance = $lockedUser->balance + $amount;
    
    // Update balance
    $lockedUser->update(['balance' => $newBalance]);
    
    // Log transaction
    CustomTransaction::create([...]);
});
```

**withdraw(User $user, float $amount, TransactionName $type, array $meta)**
```php
DB::transaction(function() {
    // Lock user row
    $lockedUser = User::where('id', $user->id)->lockForUpdate()->first();
    
    // Check balance
    if ($lockedUser->balance < $amount) {
        throw new Exception('Insufficient balance');
    }
    
    // Calculate new balance
    $newBalance = $lockedUser->balance - $amount;
    
    // Update balance
    $lockedUser->update(['balance' => $newBalance]);
    
    // Log transaction
    CustomTransaction::create([...]);
});
```

**transfer(User $from, User $to, float $amount, ...)**
```php
DB::transaction(function() {
    // Lock both users (ordered by ID to prevent deadlocks)
    $fromUser = User::where('id', $from->id)->lockForUpdate()->first();
    $toUser = User::where('id', $to->id)->lockForUpdate()->first();
    
    // Validate
    if ($fromUser->balance < $amount) {
        throw new Exception('Insufficient balance');
    }
    
    // Update both balances
    $fromUser->update(['balance' => $fromUser->balance - $amount]);
    $toUser->update(['balance' => $toUser->balance + $amount]);
    
    // Log both transactions
});
```

### 3. **Currency Conversion**

The system handles multiple currencies with conversion:

**Incoming (Provider → Our System):**
```php
private function getCurrencyValue(string $currency): int {
    return match($currency) {
        'IDR2' => 100,
        'KRW2' => 10,
        'MMK2' => 1000,  // 1 provider unit = 1000 internal units
        'VND2' => 1000,
        'LAK2' => 10,
        'KHR2' => 100,
        default => 1
    };
}

// Provider sends 10 MMK2 → Store as 10,000 internally
$internalAmount = $providerAmount * getCurrencyValue('MMK2');
```

**Outgoing (Our System → Provider):**
```php
private function formatBalanceForResponse(float $balance, string $currency): float {
    if (in_array($currency, ['MMK2', 'IDR2', 'KRW2', 'VND2', 'LAK2', 'KHR2'])) {
        return round($balance / 1000, 4);  // 10,000 internal → 10.0000 MMK2
    }
    return round($balance, 2);
}
```

### 4. **Deposit/Withdrawal Request Flow (Player Initiated)**

```
Player creates deposit/withdrawal request
    ↓
POST /api/depositfinicial or /api/withdrawfinicial
    ↓
Request stored in deposit_requests or withdraw_requests table
    Status: 'pending'
    ↓
Owner reviews in admin panel
    ↓
Owner approves or rejects
    ↓
If approved:
    - Update request status to 'approved'
    - Use CustomWalletService to credit/debit player
    - Create transaction log
If rejected:
    - Update request status to 'rejected'
    - No balance change
```

---

## 🎛️ Admin Panel Flow

### 1. **Owner Dashboard**
```php
// app/Http/Controllers/Admin/DashboardController.php

Owner logs in → Redirected to /admin
    ↓
DashboardController@index checks user type
    ↓
If Owner:
    - Count total players (owner's children)
    - Count active players (status = 1)
    - Sum total player balances
    - Get owner balance
    - Get recent players (last 10)
    - Get recent transactions
    - Render: admin/dashboard/owner.blade.php
    
Dashboard displays:
    - Player statistics
    - Balance overview
    - Recent activity
    - Quick actions (add player, transfer, reports)
```

### 2. **Player Management**
```php
// app/Http/Controllers/Admin/PlayerController.php

GET /admin/players
    - List all owner's players
    - Search/filter capabilities
    - Pagination

POST /admin/players
    - Create new player
    - Assign to logged-in owner
    - Set initial balance (optional)
    - Generate random username

GET /admin/players/{id}/cash-in
    - Show deposit form
    
POST /admin/players/{id}/cash-in
    - Validate amount
    - Transfer from owner to player
    - Use CustomWalletService.transfer()
    - Log transaction
    
GET /admin/players/{id}/cash-out
    - Show withdrawal form
    
POST /admin/players/{id}/cash-out
    - Validate amount
    - Transfer from player to owner
    - Check player has sufficient balance
    - Use CustomWalletService.transfer()
```

### 3. **Deposit/Withdrawal Approval**
```php
// Admin Deposit Controller

GET /admin/deposits
    - List all pending deposits
    - Filter by status, date, player
    
POST /admin/deposits/{id}/approve
    - Get SystemWallet user (admin user)
    - Transfer from SystemWallet to Player
    - CustomWalletService.transfer(systemWallet, player, amount)
    - Update deposit_request.status = 'approved'
    - Send notification (optional)
    
POST /admin/deposits/{id}/reject
    - Update deposit_request.status = 'rejected'
    - No balance change
```

### 4. **Reports**
```php
// app/Http/Controllers/Admin/ReportController.php

GET /admin/reports (Owner Reports)
    - Filter by date range
    - Owner's player statistics
    - Deposit/withdrawal totals
    - Transaction breakdown
    - Win/loss reports
    - Game play statistics
    
GET /admin/system-reports (System Reports)
    - System-wide statistics
    - Total users, balances
    - Transaction volumes
    - Provider statistics
    - Game popularity
```

### 5. **Game Management**
```php
GET /admin/game-lists
    - List all games from database
    - Filter by provider, game type
    - Enable/disable games
    - Mark games as "hot"
    - Update game images
    - Set game order
    
PATCH /admin/game-lists/{id}/toggle-status
    - Enable or disable game
    - Affects frontend game availability
    
PATCH /admin/game-lists/{id}/hot-game-status
    - Mark/unmark as hot game
    - Hot games shown prominently in frontend
```

---

## 📡 API Documentation

### Authentication APIs

#### POST /api/register
**Description:** Register new player  
**Auth:** None (public)  
**Body:**
```json
{
  "phone": "09123456789",
  "name": "Player Name",
  "password": "password123",
  "password_confirmation": "password123",
  "referral_code": "OWNER123",
  "payment_type_id": 1,
  "account_name": "Player Name",
  "account_number": "1234567890"
}
```
**Response:**
```json
{
  "status": "success",
  "message": "User register successfully.",
  "data": {
    "user_name": "Pi12345678",
    "name": "Player Name",
    "phone": "09123456789",
    "balance": 0
  }
}
```

#### POST /api/login
**Description:** Player login  
**Auth:** None (public)  
**Body:**
```json
{
  "user_name": "Pi12345678",
  "password": "password123"
}
```
**Response:**
```json
{
  "status": "success",
  "message": "User login successfully.",
  "data": {
    "id": 123,
    "user_name": "Pi12345678",
    "name": "Player Name",
    "balance": 5000.00,
    "token": "1|abcdef..."
  }
}
```

### Game APIs

#### GET /api/product-list
**Description:** Get list of game providers  
**Auth:** None (public)  
**Response:**
```json
{
  "code": 200,
  "message": "Success",
  "data": [
    {
      "code": 1001,
      "name": "Pragmatic Play",
      "status": 1
    },
    ...
  ]
}
```

#### GET /api/game_lists/{type}/{provider}
**Description:** Get games by type and provider  
**Auth:** None (public)  
**Params:**
- `type`: SLOT, LIVE, FISH, TABLE, etc.
- `provider`: Provider code (1001, 1002, etc.)

**Response:**
```json
{
  "code": 200,
  "data": [
    {
      "game_code": "SL001",
      "game_name": "Sweet Bonanza",
      "game_type": "SLOT",
      "product_code": 1001,
      "provider": "Pragmatic Play",
      "image_url": "https://...",
      "hot_status": 1
    },
    ...
  ]
}
```

#### POST /api/seamless/launch-game
**Description:** Launch a game  
**Auth:** Required (Bearer token)  
**Body:**
```json
{
  "game_code": "SL001",
  "product_code": 1001,
  "game_type": "SLOT"
}
```
**Response:**
```json
{
  "code": 200,
  "message": "Game launched successfully",
  "url": "https://provider.com/game?token=...",
  "content": "..." // For some providers
}
```

### Wallet APIs

#### GET /api/user
**Description:** Get current user info (including balance)  
**Auth:** Required  
**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 123,
    "user_name": "Pi12345678",
    "name": "Player Name",
    "balance": 5000.00,
    "main_balance": 5000.00,
    "phone": "09123456789",
    "email": "player@example.com"
  }
}
```

#### POST /api/depositfinicial
**Description:** Create deposit request  
**Auth:** Required  
**Body:**
```json
{
  "amount": 10000,
  "payment_type_id": 1,
  "account_name": "Player Name",
  "account_number": "1234567890",
  "reference_number": "TXN123456"
}
```

#### GET /api/depositlogfinicial
**Description:** Get deposit history  
**Auth:** Required  
**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "amount": 10000,
      "status": "approved",
      "created_at": "2024-01-01 12:00:00"
    },
    ...
  ]
}
```

#### POST /api/withdrawfinicial
**Description:** Create withdrawal request  
**Auth:** Required  
**Body:**
```json
{
  "amount": 5000,
  "payment_type_id": 1,
  "account_name": "Player Name",
  "account_number": "1234567890"
}
```

### Webhook APIs (Called by Provider)

#### POST /api/v1/api/seamless/balance
**Description:** Get player balance  
**Auth:** Signature verification  
**Body:**
```json
{
  "operator_code": "OPERATOR",
  "member_account": "Pi12345678",
  "currency": "MMK2",
  "sign": "...",
  "request_time": 1634567890000
}
```

#### POST /api/v1/api/seamless/withdraw
**Description:** Deduct balance (bet)  
**Auth:** Signature verification  
**Body:**
```json
{
  "operator_code": "OPERATOR",
  "currency": "MMK2",
  "sign": "...",
  "request_time": 1634567890000,
  "batch_requests": [...]
}
```

#### POST /api/v1/api/seamless/deposit
**Description:** Add balance (win)  
**Auth:** Signature verification  
**Body:**
```json
{
  "operator_code": "OPERATOR",
  "currency": "MMK2",
  "sign": "...",
  "request_time": 1634567890000,
  "batch_requests": [...]
}
```

---

## 🗄️ Database Structure

### Core Tables

#### `users`
```sql
- id: bigint (PK)
- user_name: varchar (unique, auto-generated)
- name: varchar
- phone: varchar (unique)
- email: varchar (nullable)
- password: varchar (hashed)
- balance: decimal(20,4) [Main balance field]
- type: int [10=Owner, 20=Player, 30=SystemWallet]
- status: tinyint [0=inactive, 1=active]
- is_changed_password: tinyint [0=must change, 1=changed]
- agent_id: bigint (FK to users.id) [Actually owner_id]
- payment_type_id: bigint (nullable)
- account_name: varchar (nullable)
- account_number: varchar (nullable)
- game_provider_password: text (encrypted) [For game launches]
- profile: varchar (nullable) [Profile image]
- referral_code: varchar (unique) [For inviting players]
- user_agent: text (nullable)
- created_at, updated_at: timestamp
```

#### `custom_transactions`
```sql
- id: bigint (PK)
- user_id: bigint (FK to users.id)
- target_user_id: bigint (FK to users.id)
- amount: decimal(20,4)
- type: varchar [deposit, withdraw, transfer]
- transaction_name: varchar [enum value]
- old_balance: decimal(20,4)
- new_balance: decimal(20,4)
- meta: json [Additional data]
- uuid: varchar (unique)
- confirmed: boolean
- created_at, updated_at: timestamp
```

#### `place_bets`
```sql
- id: bigint (PK)
- transaction_id: varchar (unique) [From provider]
- member_account: varchar [user_name]
- player_id: bigint (FK to users.id)
- player_agent_id: bigint (FK to users.id)
- product_code: int [Provider code]
- provider_name: varchar
- game_type: varchar
- game_code: varchar
- game_name: varchar
- operator_code: varchar
- currency: varchar
- action: varchar [BET, WIN, SETTLED, etc.]
- amount: decimal(20,4)
- valid_bet_amount: decimal (nullable)
- bet_amount: decimal (nullable)
- prize_amount: decimal (nullable)
- wager_code: varchar (nullable)
- wager_status: varchar (nullable)
- round_id: varchar (nullable)
- payload: json (nullable)
- status: varchar [completed, failed, duplicate, etc.]
- before_balance: decimal(20,4)
- balance: decimal(20,4) [After transaction]
- error_message: text (nullable)
- request_time: timestamp
- settle_at: timestamp (nullable)
- created_at_provider: timestamp (nullable)
- created_at, updated_at: timestamp
```

#### `deposit_requests`
```sql
- id: bigint (PK)
- user_id: bigint (FK to users.id)
- amount: decimal(20,4)
- payment_type_id: bigint
- account_name: varchar
- account_number: varchar
- reference_number: varchar (nullable)
- status: enum [pending, approved, rejected]
- approved_by: bigint (FK to users.id, nullable)
- approved_at: timestamp (nullable)
- remarks: text (nullable)
- created_at, updated_at: timestamp
```

#### `withdraw_requests`
```sql
- id: bigint (PK)
- user_id: bigint (FK to users.id)
- amount: decimal(20,4)
- payment_type_id: bigint
- account_name: varchar
- account_number: varchar
- status: enum [pending, approved, rejected]
- approved_by: bigint (FK to users.id, nullable)
- approved_at: timestamp (nullable)
- remarks: text (nullable)
- created_at, updated_at: timestamp
```

#### `game_lists`
```sql
- id: bigint (PK)
- game_code: varchar (unique)
- game_name: varchar
- game_type: varchar [SLOT, LIVE, FISH, TABLE, etc.]
- product_code: int [Provider]
- provider: varchar
- image_url: varchar (nullable)
- status: tinyint [0=disabled, 1=enabled]
- hot_status: tinyint [0=normal, 1=hot]
- pp_hot_status: tinyint (nullable)
- order: int (default 0)
- created_at, updated_at: timestamp
```

#### `products`
```sql
- id: bigint (PK)
- code: int (unique) [Provider code]
- name: varchar [Provider name]
- image_url: varchar (nullable)
- status: tinyint [0=disabled, 1=enabled]
- order: int (default 0)
- created_at, updated_at: timestamp
```

### Relationships

```
users (Owner)
    ├─ hasMany → users (Players) via agent_id
    ├─ hasMany → custom_transactions
    ├─ hasMany → place_bets
    ├─ hasMany → deposit_requests
    └─ hasMany → withdraw_requests

users (Player)
    ├─ belongsTo → users (Owner) via agent_id
    ├─ hasMany → custom_transactions
    ├─ hasMany → place_bets
    ├─ hasMany → deposit_requests
    └─ hasMany → withdraw_requests

game_lists
    └─ belongsTo → products via product_code

place_bets
    ├─ belongsTo → users (player) via player_id
    └─ belongsTo → users (owner) via player_agent_id
```

---

## 🔧 Key Services

### 1. **CustomWalletService**
**Location:** `app/Services/CustomWalletService.php`

**Purpose:** Centralized wallet operations with transaction safety

**Key Features:**
- Row-level locking (lockForUpdate) to prevent race conditions
- Atomic transactions with DB::transaction
- Comprehensive logging
- Balance validation
- Support for deposit, withdraw, transfer operations

**Methods:**
```php
getBalance(User $user): float
deposit(User $user, float $amount, TransactionName $type, array $meta): bool
withdraw(User $user, float $amount, TransactionName $type, array $meta): bool
transfer(User $from, User $to, float $amount, TransactionName $type, array $meta): bool
forceTransfer(...): bool  // Admin override
hasBalance(User $user, float $amount): bool
getTransactionHistory(User $user, int $limit, int $offset)
getWalletStats(): array
```

### 2. **ApiResponseService**
**Location:** `app/Services/ApiResponseService.php`

**Purpose:** Standardized API response format

**Methods:**
```php
success(array $data, string $message = 'Success'): JsonResponse
error(SeamlessWalletCode $code, string $message, $data = null): JsonResponse
```

**Response Format:**
```json
{
  "code": 200,
  "message": "Success",
  "data": {...}
}
```

### 3. **ReportService**
**Location:** `app/Services/ReportService.php`

**Purpose:** Generate reports and analytics

**Capabilities:**
- Player statistics
- Transaction summaries
- Deposit/withdrawal totals
- Win/loss calculations
- Date range filtering
- Export functionality

### 4. **BuffaloGameService**
**Location:** `app/Services/BuffaloGameService.php`

**Purpose:** Custom game logic for Buffalo game

**Features:**
- Game session management
- Custom balance handling
- Game state persistence

### 5. **ShanApiService**
**Location:** `app/Services/ShanApiService.php`

**Purpose:** Integration with Shan game provider

**Features:**
- Shan API communication
- Token generation
- Transaction processing

---

## 🔐 Security Features

### 1. **Authentication & Authorization**
- Laravel Sanctum for API authentication
- Role-based access control (Owner, Player, SystemWallet)
- Permission-based sidebar menu
- Middleware protection on all admin routes
- Player access restriction to admin panel

### 2. **Middleware Stack**
```php
// All admin routes
'middleware' => ['auth', 'checkBanned', 'preventPlayerAccess']

// API routes
'middleware' => ['auth:sanctum']
```

### 3. **Database Security**
- Row-level locking for concurrent transactions
- Encrypted game provider passwords
- Hashed user passwords (bcrypt)
- Prepared statements (Eloquent ORM)
- SQL injection protection

### 4. **Webhook Security**
- Signature verification (MD5 hash with secret key)
- Timestamp validation
- Duplicate transaction prevention
- IP whitelisting (optional)

**Signature Generation:**
```php
// For balance endpoint
$signature = md5($operatorCode . $requestTime . 'getbalance' . $secretKey);

// For deposit endpoint
$signature = md5($operatorCode . $requestTime . 'deposit' . $secretKey);

// For withdraw endpoint
$signature = md5($operatorCode . $requestTime . 'withdraw' . $secretKey);
```

### 5. **Transaction Safety**
- Idempotency checks (duplicate transaction_id)
- Balance validation before operations
- Atomic database transactions
- Transaction logging for audit trail
- Before/after balance tracking

---

## 🌐 Real-time Features

### Laravel Reverb (WebSocket)
**Configuration:** `config/broadcasting.php`

**Purpose:** Real-time communication (though currently using polling)

**Potential Uses:**
- Real-time balance updates
- Live game notifications
- Admin notifications
- Chat support

**Current Implementation:**
- Frontend polls `/api/user` every 5 seconds for balance updates
- Can be optimized with WebSocket broadcasting

---

## 📱 Mobile-First Design

### Responsive Components
- Desktop components: `client_react/src/components/desktop/`
- Mobile components: `client_react/src/components/mobile/`
- Responsive layout with Tailwind CSS
- Mobile bottom menu
- Desktop sidebar navigation

### Device Detection
- TelegramBrowserDetector component
- User agent detection
- Responsive breakpoints

---

## 🎯 Key Features

### Player Features
- ✅ Register and login
- ✅ Browse games by category/provider
- ✅ Launch and play games
- ✅ Deposit/withdrawal requests
- ✅ Transaction history
- ✅ Game logs
- ✅ Contact support
- ✅ Multi-language support
- ✅ Real-time balance updates
- ✅ Custom games (2D, 3D, Digit, Shan, Buffalo, PoneWine)

### Owner Features
- ✅ Dashboard with statistics
- ✅ Create and manage players
- ✅ Approve/reject deposits and withdrawals
- ✅ Transfer money to/from players
- ✅ View comprehensive reports
- ✅ Manage games and providers
- ✅ Configure banners and promotions
- ✅ Manage payment methods
- ✅ View transaction logs

### SystemWallet Features
- ✅ System dashboard
- ✅ System-wide reports
- ✅ Monitor all transactions
- ✅ System balance overview

### Admin Features
- ✅ Game management (enable/disable)
- ✅ Hot game marking
- ✅ Game image updates
- ✅ Provider management
- ✅ Banner/promotion management
- ✅ Contact information
- ✅ Video ads management

---

## 🐛 Error Handling

### API Error Codes
```php
enum SeamlessWalletCode: int {
    Success = 200;
    InternalServerError = 500;
    InvalidSignature = 1002;
    MemberNotExist = 1003;
    InsufficientBalance = 1001;
    DuplicateTransaction = 1005;
    BetNotExist = 1006;
}
```

### Error Response Format
```json
{
  "code": 1001,
  "message": "Insufficient balance",
  "data": null
}
```

### Logging
- All webhook requests logged to `transaction_logs` table
- All balance changes logged to `custom_transactions` table
- All bets logged to `place_bets` table
- Laravel Log facade for application logs
- User login logs in `user_logs` table

---

## 🚀 Performance Optimizations

### Database
- Indexes on frequently queried columns
- Row-level locking only when needed
- Efficient queries with Eloquent relationships
- Connection pooling

### Frontend
- Vite for fast builds
- Code splitting
- Lazy loading of components
- Asset optimization

### Caching
- Laravel cache for static data
- Game list caching
- Provider list caching

---

## 📊 Transaction Flow Summary

```
Player Action                 → API Call → Backend Process → Database Update → Response
───────────────────────────────────────────────────────────────────────────────────────
Login                        → POST /api/login → Validate → users → Token
Launch Game                  → POST /api/seamless/launch-game → Get/Generate password → Call provider → Game URL
Place Bet                    → [Provider calls] POST /webhook/withdraw → Lock user → Deduct balance → custom_transactions + place_bets → Updated balance
Win/Bonus                    → [Provider calls] POST /webhook/deposit → Lock user → Add balance → custom_transactions + place_bets → Updated balance
Request Deposit              → POST /api/depositfinicial → Create request → deposit_requests → Pending
Approve Deposit (Owner)      → POST /admin/deposits/{id}/approve → Transfer from SystemWallet → users + custom_transactions → Approved
Request Withdrawal           → POST /api/withdrawfinicial → Create request → withdraw_requests → Pending
Approve Withdrawal (Owner)   → POST /admin/withdrawals/{id}/approve → Transfer to SystemWallet → users + custom_transactions → Approved
Owner Transfer to Player     → POST /admin/players/{id}/cash-in → Transfer → users + custom_transactions → Updated balances
```

---

## 📝 Notes & Best Practices

### Important Reminders
1. **NO AGENT SYSTEM**: The `agent_id` field is used as `owner_id` for Owner→Player relationships only
2. **Game Provider Password**: Must remain consistent for each player across all game launches
3. **Currency Conversion**: Always convert between provider currency and internal currency
4. **Transaction Idempotency**: Always check for duplicate `transaction_id` before processing
5. **Balance Locking**: Use `lockForUpdate()` when reading balance before modification
6. **Signature Validation**: Always validate webhook signatures before processing

### Configuration Files
- `.env`: Database, API keys, app settings
- `config/seamless_key.php`: GSC Plus provider configuration
- `config/shan_key.php`: Shan game configuration
- `config/broadcasting.php`: Laravel Reverb configuration

### Testing Checklist
- [ ] Player registration and login
- [ ] Game launch (multiple providers)
- [ ] Bet placement and balance deduction
- [ ] Win and balance addition
- [ ] Deposit request and approval
- [ ] Withdrawal request and approval
- [ ] Owner to player transfer
- [ ] Admin panel access control
- [ ] Real-time balance updates
- [ ] Transaction history accuracy
- [ ] Webhook signature validation
- [ ] Concurrent transaction handling

---

## 🔗 External Dependencies

### Backend
- Laravel Framework 10.x
- Laravel Sanctum (API authentication)
- Laravel Reverb (WebSocket)
- MySQL Database
- Guzzle HTTP Client (for provider API calls)

### Frontend
- React 18
- React Router DOM
- Vite
- Tailwind CSS
- React Hot Toast (notifications)
- React Icons

### Game Provider
- GSC Plus API
- Seamless Wallet Integration
- Multiple game providers (Pragmatic Play, PG Soft, etc.)

---

## 📚 Additional Resources

### Documentation Files
- `IMPLEMENTATION_SUMMARY.md`: Implementation details
- `NO_AGENT_SYSTEM_CLARIFICATION.md`: Agent system clarification
- `README.md`: Laravel documentation

### Key Directories
- `app/Http/Controllers/`: All controllers
- `app/Services/`: Business logic services
- `app/Models/`: Database models
- `app/Enums/`: Enumerations
- `routes/`: Route definitions
- `client_react/src/`: Frontend source code
- `database/migrations/`: Database schema
- `database/seeders/`: Database seeders

---

**Last Updated:** October 20, 2025  
**Version:** 1.0.0  
**Maintainer:** Development Team

