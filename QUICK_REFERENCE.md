# Quick Reference Guide - GSC+ Custom Wallet

## 🚀 Quick Start

### Access Points
- **Player Frontend:** `https://gamestar77.online` (React SPA)
- **Admin Panel:** `https://gamestar77.online/login` (Laravel Blade)
- **API Base:** `https://gamestar77.online/api`

### User Types
| Type | Value | Access | Purpose |
|------|-------|--------|---------|
| **Owner** | 10 | Admin Panel (Full) | Manages players, approves transactions |
| **Player** | 20 | React Frontend | Plays games, requests deposits/withdrawals |
| **SystemWallet** | 30 | Admin Panel (Limited) | System operations, accounting |

---

## 📁 Project Structure

```
gsc_admin_user_cw/
├── app/                        # Laravel backend
│   ├── Http/Controllers/       # API & Admin controllers
│   │   ├── Api/               # Player API controllers
│   │   └── Admin/             # Admin panel controllers
│   ├── Services/              # Business logic
│   ├── Models/                # Database models
│   └── Enums/                 # Enumerations
├── client_react/              # React frontend
│   └── src/
│       ├── components/        # UI components
│       ├── contexts/          # React contexts
│       ├── pages/             # Page components
│       └── routes/            # Route definitions
├── routes/
│   ├── api.php               # Player API routes
│   ├── web.php               # Admin web routes
│   └── admin.php             # Admin panel routes
└── config/
    ├── seamless_key.php      # GSC Plus config
    └── broadcasting.php      # Laravel Reverb
```

---

## 🔑 Key Files

### Backend
| File | Purpose |
|------|---------|
| `app/Services/CustomWalletService.php` | Core wallet operations |
| `app/Http/Controllers/Api/V1/Auth/AuthController.php` | Player login/register |
| `app/Http/Controllers/Api/V1/Game/LaunchGameController.php` | Game launching |
| `app/Http/Controllers/Api/V1/gplus/Webhook/DepositController.php` | Win webhook |
| `app/Http/Controllers/Api/V1/gplus/Webhook/WithdrawController.php` | Bet webhook |
| `app/Models/User.php` | User model (players, owners) |

### Frontend
| File | Purpose |
|------|---------|
| `client_react/src/contexts/AuthContext.jsx` | Authentication state |
| `client_react/src/hooks/baseUrl.jsx` | API endpoint |
| `client_react/src/routes/index.jsx` | Route definitions |
| `client_react/src/components/Layout.jsx` | Main layout wrapper |

---

## 🔐 Authentication

### Player (React Frontend)
```javascript
// Login
POST /api/login
Body: { user_name, password }
Response: { token, user_data }

// Use token
Headers: { Authorization: "Bearer {token}" }

// Auto-refresh balance every 5s
GET /api/user
```

### Admin (Web)
```php
// Login
POST /login
Body: { user_name, password }

// Middleware protects all admin routes
'middleware' => ['auth', 'checkBanned', 'preventPlayerAccess']
```

---

## 🎮 Game Flow

### 1. Browse Games
```javascript
GET /api/game_types           // Categories (SLOT, LIVE, etc.)
GET /api/product-list         // Providers (Pragmatic, PG Soft, etc.)
GET /api/game_lists/{type}/{provider}  // Games
```

### 2. Launch Game
```javascript
POST /api/seamless/launch-game
Body: {
  game_code: "vs20sbpramatic",
  product_code: 1001,
  game_type: "SLOT"
}
Response: { url: "https://provider.com/game?token=..." }
```

### 3. Provider Calls Webhooks
```
Player bets → POST /api/v1/api/seamless/withdraw (deduct balance)
Player wins → POST /api/v1/api/seamless/deposit (add balance)
```

---

## 💰 Wallet Operations

### CustomWalletService Methods
```php
// Deposit (add balance)
$walletService->deposit($user, $amount, TransactionName::Deposit, $meta);

// Withdraw (deduct balance)
$walletService->withdraw($user, $amount, TransactionName::Withdraw, $meta);

// Transfer between users
$walletService->transfer($fromUser, $toUser, $amount, TransactionName::Transfer, $meta);

// Get balance
$balance = $walletService->getBalance($user);

// Check sufficient balance
$hasBalance = $walletService->hasBalance($user, $amount);
```

### All operations are:
- ✅ Atomic (DB::transaction)
- ✅ Row-locked (lockForUpdate)
- ✅ Logged (custom_transactions + place_bets)
- ✅ Safe from race conditions

---

## 📊 Database Tables

### Core Tables
| Table | Purpose | Key Fields |
|-------|---------|------------|
| `users` | Players, owners, system | id, user_name, balance, type, agent_id |
| `custom_transactions` | Wallet transaction log | user_id, amount, type, old_balance, new_balance |
| `place_bets` | Game bet logs | transaction_id, member_account, action, amount, balance |
| `game_lists` | Available games | game_code, game_name, product_code, status, hot_status |
| `products` | Game providers | code, name, status |
| `deposit_requests` | Player deposit requests | user_id, amount, status |
| `withdraw_requests` | Player withdrawal requests | user_id, amount, status |

### Key Relationships
```
users (Owner)
  └─ hasMany → users (Players) via agent_id

users (Player)
  ├─ hasMany → custom_transactions
  ├─ hasMany → place_bets
  ├─ hasMany → deposit_requests
  └─ hasMany → withdraw_requests
```

---

## 🔄 Common Workflows

### Player Deposit (Finicial)
```
1. Player: POST /api/depositfinicial {amount, payment_info}
2. Backend: Create deposit_request (status: pending)
3. Owner: View in admin panel (/admin/deposits)
4. Owner: Click "Approve"
5. Backend: Transfer from SystemWallet to Player
6. Backend: Update deposit_request (status: approved)
7. Player: See updated balance
```

### Player Withdrawal
```
1. Player: POST /api/withdrawfinicial {amount, payment_info}
2. Backend: Validate balance, create withdraw_request
3. Owner: View in admin panel (/admin/withdrawals)
4. Owner: Click "Approve"
5. Backend: Transfer from Player to SystemWallet
6. Owner: Manually send money to player's bank
7. Player: Receive money, see reduced balance
```

### Owner Cash-In to Player
```
1. Owner: Navigate to /admin/players/{id}/cash-in
2. Owner: Enter amount
3. Backend: Transfer from Owner to Player
4. Both balances updated immediately
```

### Game Bet & Win
```
Bet:
1. Player places bet in game
2. Provider → POST /api/v1/api/seamless/withdraw
3. Backend: Validate, deduct balance, log
4. Return updated balance to provider

Win:
1. Player wins in game
2. Provider → POST /api/v1/api/seamless/deposit
3. Backend: Validate, add balance, log
4. Return updated balance to provider
5. Player sees update (via polling /api/user)
```

---

## 🛡️ Security Checklist

### Webhooks
- ✅ Signature validation (MD5)
- ✅ Timestamp validation
- ✅ Duplicate transaction check
- ✅ Currency validation
- ✅ Balance validation

### Transactions
- ✅ Row-level locking (lockForUpdate)
- ✅ Database transactions
- ✅ Before/after balance logging
- ✅ Idempotency (transaction_id unique)

### Authentication
- ✅ Sanctum token authentication
- ✅ Role-based access control
- ✅ Middleware protection
- ✅ Player blocked from admin panel
- ✅ Password hashing (bcrypt)

---

## 🐛 Common Issues & Solutions

### Issue: Balance mismatch
**Solution:** Check `custom_transactions` and `place_bets` tables for transaction history. Verify all transactions have matching before/after balances.

### Issue: Duplicate transaction error
**Solution:** This is correct behavior - prevents duplicate processing. Provider should not retry with same transaction_id.

### Issue: Game won't launch
**Solution:** Check:
1. User is authenticated (valid token)
2. `game_provider_password` is set in users table
3. Signature is correct
4. Provider API is accessible

### Issue: Webhook signature fails
**Solution:** Verify:
1. Secret key matches provider's config
2. Signature algorithm: MD5(operatorCode + requestTime + method + secretKey)
3. Case-insensitive comparison
4. Timestamp is in milliseconds

### Issue: Player can't deposit/withdraw
**Solution:** Check:
1. deposit_request/withdraw_request created?
2. Status is 'pending'?
3. Owner has approved?
4. SystemWallet has sufficient balance (for deposits)?
5. Player has sufficient balance (for withdrawals)?

---

## 📈 Monitoring

### Check System Health
```sql
-- Total system balance
SELECT SUM(balance) FROM users;

-- Total players
SELECT COUNT(*) FROM users WHERE type = 20;

-- Pending deposits
SELECT COUNT(*) FROM deposit_requests WHERE status = 'pending';

-- Pending withdrawals
SELECT COUNT(*) FROM withdraw_requests WHERE status = 'pending';

-- Recent transactions
SELECT * FROM custom_transactions ORDER BY created_at DESC LIMIT 100;

-- Failed bets
SELECT * FROM place_bets WHERE status = 'failed' ORDER BY created_at DESC;
```

### Log Locations
- Laravel logs: `storage/logs/laravel.log`
- Transaction logs: `transaction_logs` table
- User logs: `user_logs` table
- Bet logs: `place_bets` table

---

## 🔧 Configuration

### Environment Variables (.env)
```env
APP_URL=https://gamestar77.online
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# GSC Plus Provider (config/seamless_key.php)
SEAMLESS_AGENT_CODE=your_operator_code
SEAMLESS_SECRET_KEY=your_secret_key
SEAMLESS_API_URL=https://provider-api.com
SEAMLESS_API_CURRENCY=MMK2
```

### Currency Config
```php
// config/seamless_key.php
'api_currency' => 'MMK2',  // Default currency

// Special conversions (1:1000 for MMK2)
MMK2: Incoming × 1000, Outgoing ÷ 1000
```

---

## 🚀 Deployment

### Backend (Laravel)
```bash
# Install dependencies
composer install

# Generate key
php artisan key:generate

# Run migrations
php artisan migrate

# Run seeders
php artisan db:seed

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Create storage link
php artisan storage:link

# Start queue worker (if using queues)
php artisan queue:work

# Start Reverb (WebSocket)
php artisan reverb:start
```

### Frontend (React)
```bash
cd client_react

# Install dependencies
npm install

# Build for production
npm run build

# Or run development server
npm run dev
```

---

## 📞 API Quick Reference

### Player APIs
| Method | Endpoint | Purpose | Auth |
|--------|----------|---------|------|
| POST | `/api/register` | Register new player | No |
| POST | `/api/login` | Player login | No |
| POST | `/api/logout` | Logout | Yes |
| GET | `/api/user` | Get user info (balance) | Yes |
| GET | `/api/game_types` | Get game categories | No |
| GET | `/api/game_lists/{type}/{provider}` | Get games | No |
| POST | `/api/seamless/launch-game` | Launch game | Yes |
| POST | `/api/depositfinicial` | Create deposit request | Yes |
| GET | `/api/depositlogfinicial` | Get deposit history | Yes |
| POST | `/api/withdrawfinicial` | Create withdrawal request | Yes |
| GET | `/api/withdrawlogfinicial` | Get withdrawal history | Yes |
| GET | `/api/player/game-logs` | Get game logs | Yes |

### Webhook APIs (Provider → Us)
| Method | Endpoint | Purpose | Validation |
|--------|----------|---------|------------|
| POST | `/api/v1/api/seamless/balance` | Get player balance | Signature |
| POST | `/api/v1/api/seamless/withdraw` | Deduct balance (bet) | Signature |
| POST | `/api/v1/api/seamless/deposit` | Add balance (win) | Signature |
| POST | `/api/v1/api/seamless/pushbetdata` | Push bet data | Signature |

---

## 💡 Tips & Best Practices

### For Developers
1. Always use `CustomWalletService` for balance operations
2. Never directly update `users.balance` without locking
3. Always log transactions in both `custom_transactions` and `place_bets`
4. Check for duplicate `transaction_id` before processing
5. Use `DB::transaction` for atomic operations
6. Validate webhooks signatures

### For Admins
1. Monitor pending deposits/withdrawals daily
2. Check transaction logs for anomalies
3. Verify SystemWallet balance matches total player balances
4. Review failed transactions regularly
5. Keep game provider password secure
6. Test new games before enabling

### For Players
1. Keep username and password secure
2. Use correct payment details for deposits/withdrawals
3. Wait for approval before expecting balance updates
4. Check transaction history regularly
5. Contact support if balance seems incorrect

---

## 📚 Documentation Files

- `APPLICATION_FLOW_DOCUMENTATION.md` - Complete system documentation
- `FLOW_DIAGRAMS.md` - Visual flow diagrams
- `IMPLEMENTATION_SUMMARY.md` - Implementation details
- `NO_AGENT_SYSTEM_CLARIFICATION.md` - Agent system clarification
- `QUICK_REFERENCE.md` - This file

---

**Last Updated:** October 20, 2025  
**Version:** 1.0.0

