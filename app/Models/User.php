<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Queries\Models\UserQuery;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Class User.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 *
 * Telegram:
 * @property int|null $unique_id
 * @property string|null $username
 * @property boolean|null $is_bot
 * @property boolean|null $is_premium
 *
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Chat[]|Collection $chats
 *
 * @method static UserQuery query()
 * @method static UserFactory factory()
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',

        /** Telegram */
        'unique_id',
        'username',
        'is_bot',
        'is_premium',
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_bot' => 'boolean',
            'is_premium' => 'boolean',
        ];
    }

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected array $relationships = [
        'chats',
    ];

    /**
     * Associated chats relation query.
     *
     * @return HasMany
     */
    public function chats(): HasMany
    {
        return $this->hasMany(Chat::class, 'user_id', 'unique_id');
    }

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param DatabaseBuilder $query
     *
     * @return UserQuery
     */
    public function newEloquentBuilder($query): UserQuery
    {
        return new UserQuery($query);
    }
}
