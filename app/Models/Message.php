<?php

namespace App\Models;

use App\Queries\Models\MessageQuery;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
use Illuminate\Support\Carbon;

/**
 * Class Message.
 *
 * @property int $id
 * @property int $unique_id
 * @property int $chat_id
 * @property string|null $type
 * @property string|null $text
 * @property object|null $metadata
 * @property Carbon|null $sent_at
 * @property Carbon|null $edited_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Chat $chat
 *
 * @method static MessageQuery query()
 */
class Message extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'unique_id',
        'chat_id',
        'type',
        'text',
        'metadata',
        'sent_at',
        'edited_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'metadata' => 'array',
        'sent_at' => 'datetime',
        'edited_at' => 'datetime',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected array $relationships = [
        'chat',
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
     * Create a new Eloquent query builder for the model.
     *
     * @param DatabaseBuilder $query
     *
     * @return MessageQuery
     */
    public function newEloquentBuilder($query): MessageQuery
    {
        return new MessageQuery($query);
    }
}
