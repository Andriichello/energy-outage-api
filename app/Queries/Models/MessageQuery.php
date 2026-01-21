<?php

namespace App\Queries\Models;

use App\Models\Message;
use App\Queries\BaseQuery;

/**
 * Class MessageQuery.
 *
 * @property Message $model
 *
 * @method MessageQuery select($columns = ['*'])
 * @method MessageQuery whereKey($id)
 * @method Message|null find($id, $columns = ['*'])
 * @method Message findOrFail($id, $columns = ['*'])
 * @method Message|null first($columns = ['*'])
 * @method Message firstOrFail($columns = ['*'])
 * @method Message firstOrNew(array $attributes = [], array $values = [])
 * @method Message make(array $attributes = [])
 * @method Message create(array $attributes = [])
 * @method Message updateOrCreate(array $attributes, array $values = [])
 */
class MessageQuery extends BaseQuery
{
    //
}
