<?php

namespace App\Queries\Models;

use App\Models\Chat;
use App\Queries\BaseQuery;

/**
 * Class ChatQuery.
 *
 * @property Chat $model
 *
 * @method ChatQuery select($columns = ['*'])
 * @method ChatQuery whereKey($id)
 * @method Chat|null find($id, $columns = ['*'])
 * @method Chat findOrFail($id, $columns = ['*'])
 * @method Chat|null first($columns = ['*'])
 * @method Chat firstOrFail($columns = ['*'])
 * @method Chat firstOrNew(array $attributes = [], array $values = [])
 * @method Chat make(array $attributes = [])
 * @method Chat create(array $attributes = [])
 * @method Chat updateOrCreate(array $attributes, array $values = [])
 */
class ChatQuery extends BaseQuery
{
    //
}
