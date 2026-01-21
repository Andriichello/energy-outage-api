<?php

namespace App\Models;

use App\Queries\Models\UpdatedInformationQuery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
use Illuminate\Support\Carbon;

/**
 * Class UpdatedInformation.
 *
 * @property int $id
 * @property string $provider
 * @property string $url
 * @property string $content
 * @property string $content_hash
 * @property array|null $metadata
 * @property Carbon $fetched_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static UpdatedInformationQuery query()
 */
class UpdatedInformation extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'updated_information';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'provider',
        'url',
        'content',
        'content_hash',
        'metadata',
        'fetched_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'metadata' => 'array',
        'fetched_at' => 'datetime',
    ];

    /**
     * Make an instance of UpdatedInformation from the given parameters.
     *
     * @param string $provider
     * @param string $url
     * @param string $text
     * @param Carbon|null $fetchedAt
     *
     * @return static
     */
    public static function make(string $provider, string $url, string $text, Carbon $fetchedAt = null): static
    {
        $info = new UpdatedInformation();

        $info->provider = $provider;
        $info->url = $url;
        $info->content = $text;
        $info->content_hash = hash('sha256', $text);
        $info->fetched_at = $fetchedAt ?? Carbon::now();

        return $info;
    }

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param DatabaseBuilder $query
     *
     * @return UpdatedInformationQuery
     */
    public function newEloquentBuilder($query): UpdatedInformationQuery
    {
        return new UpdatedInformationQuery($query);
    }
}
