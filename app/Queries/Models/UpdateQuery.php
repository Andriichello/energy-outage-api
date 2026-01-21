<?php

namespace App\Queries\Models;

use App\Models\Update;
use App\Queries\BaseQuery;

/**
 * Class UpdateQuery.
 *
 * @property Update $model
 *
 * @method UpdateQuery select($columns = ['*'])
 * @method UpdateQuery whereKey($id)
 * @method Update|null find($id, $columns = ['*'])
 * @method Update findOrFail($id, $columns = ['*'])
 * @method Update|null first($columns = ['*'])
 * @method Update firstOrFail($columns = ['*'])
 * @method Update firstOrNew(array $attributes = [], array $values = [])
 * @method Update make(array $attributes = [])
 * @method Update create(array $attributes = [])
 * @method Update updateOrCreate(array $attributes, array $values = [])
 */
class UpdateQuery extends BaseQuery
{
    //
}
