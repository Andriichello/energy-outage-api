<?php

namespace App\Models;

use App\Queries\Models\UpdateQuery;
use Database\Factories\UpdateFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
use Illuminate\Support\Carbon;

/**
 * Class Update.
 *
 * @property int $id
 * @property int $unique_id
 * @property int $user_id
 * @property int $chat_id
 * @property int $message_id
 * @property string|null $type
 * @property string|null $status
 * @property object|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property User $user
 * @property Chat $chat
 * @property Message $message
 *
 * @method static UpdateQuery query()
 */
class Update extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'unique_id',
        'user_id',
        'chat_id',
        'message_id',
        'type',
        'status',
        'metadata',
    ];
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'metadata' => 'object',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected array $relationships = [
        'chat',
        'user',
        'message',
    ];

    /**
     * Associated chat relation query.
     *
     * @return BelongsTo
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class, 'chat_id', 'unique_id');
    }

    /**
     * Associated user relation query.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'unique_id');
    }

    /**
     * Associated message relation query.
     *
     * @return BelongsTo
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'message_id', 'unique_id');
    }

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param DatabaseBuilder $query
     *
     * @return UpdateQuery
     */
    public function newEloquentBuilder($query): UpdateQuery
    {
        return new UpdateQuery($query);
    }
}
