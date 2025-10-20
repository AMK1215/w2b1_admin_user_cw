<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserType;
use App\Models\Admin\Banner;
use App\Models\Admin\BannerAds;
use App\Models\Admin\BannerText;
use App\Models\Admin\Permission;
use App\Models\Admin\Promotion;
use App\Models\Admin\ReportTransaction;
use App\Models\Admin\Role;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_name',
        'name',
        'phone',
        'email',
        'email_verified_at',
        'password',
        'profile',
        'balance',
        'status',
        'is_changed_password',
        'agent_id',
        'payment_type_id',
        'account_name',
        'account_number',
        'type',
        'game_provider_password',
        'user_agent'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'balance' => 'decimal:2',
        'status' => 'integer',
        'is_changed_password' => 'integer',
    ];


    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_user');
    }

    public function hasRole($role)
    {
        return $this->roles->contains('title', $role);
    }

    // Owner->Player Relationship Methods
    // Note: agent_id field is actually owner_id (no agent system, only Owner->Player)
    
    // A user can have children (Owner has many Players)
    public function children()
    {
        return $this->hasMany(User::class, 'agent_id');
    }

    // A user belongs to an owner (Players belong to Owner)
    public function owner()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    // Alias for owner() - for backward compatibility
    public function agent()
    {
        return $this->owner();
    }

    // Fetch players managed by an owner
    public function players()
    {
        return $this->hasMany(User::class, 'agent_id')
                    ->where('type', UserType::Player->value);
    }

    // Alias for owner() - for backward compatibility
    public function parent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function banners()
    {
        return $this->hasMany(Banner::class, 'admin_id'); // Banners owned by this admin
    }

    public function bannertexts()
    {
        return $this->hasMany(BannerText::class, 'admin_id'); // Banners owned by this admin
    }

    public function bannerads()
    {
        return $this->hasMany(BannerAds::class, 'admin_id'); // Banners owned by this admin
    }

    public function promotions()
    {
        return $this->hasMany(Promotion::class, 'admin_id'); // Banners owned by this admin
    }

   

    /**
     * Recursive relationship to get all ancestors up to senior.
     */
    public function ancestors()
    {
        return $this->parent()->with('ancestors');
    }

    /**
     * Recursive relationship to get all descendants down to players.
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    public function agents()
    {
        return $this->hasMany(User::class, 'agent_id');
    }

    

    public static function adminUser()
    {
        return self::where('type', UserType::SystemWallet)->first();
    }

    /**
     * Get the game provider password for this user.
     */
    public function getGameProviderPassword(): ?string
    {
        if ($this->game_provider_password) {
            try {
                return Crypt::decryptString($this->game_provider_password);
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                // Log the error or handle it as appropriate (e.g., return null to regenerate)
                \Log::error('Failed to decrypt game_provider_password for user '.$this->id, ['error' => $e->getMessage()]);

                return null;
            }
        }

        return null;
    }

    /**
     * Set the game provider password for this user.
     */
    public function setGameProviderPassword(string $password): void
    {
        $this->game_provider_password = Crypt::encryptString($password);
        $this->save(); // Save the user model to persist the password
    }

    public function placeBets()
    {
        return $this->hasMany(PlaceBet::class, 'member_account', 'user_name', 'player_id');
    }

    public function hasPermission($permission)
    {
        // Check if any of the user's roles have this permission
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('title', $permission);
            })
            ->exists();
    }

    

    
    // Custom Wallet Methods (replacing Laravel Wallet package)
    
    /**
     * Get balance attribute as float
     */
    public function getBalanceAttribute($value): float
    {
        return (float) $value;
    }

    /**
     * Get wallet balance as float (alias for balance)
     */
    public function getBalanceFloatAttribute(): float
    {
        return (float) $this->getAttributes()['balance'] ?? 0.0;
    }

    /**
     * Check if user has sufficient balance
     */
    public function hasBalance(float $amount): bool
    {
        return $this->balance >= $amount;
    }

    /**
     * Get custom transactions for this user
     */
    public function customTransactions()
    {
        return $this->hasMany(CustomTransaction::class, 'user_id');
    }

    /**
     * Get custom transactions where this user is the target
     */
    public function customTransactionsAsTarget()
    {
        return $this->hasMany(CustomTransaction::class, 'target_user_id');
    }
}
