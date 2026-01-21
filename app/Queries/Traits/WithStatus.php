<?php

namespace App\Queries\Traits;

use App\Queries\BaseQuery;
use BackedEnum;

/**
 * Class WithStatus.
 *
 * @mixin BaseQuery
 *
 * @author Andrii Prykhodko <andriichello@gmail.com>
 */
trait WithStatus
{
    /**
     * Filter down to records to the ones with the given statuses.
     *
     * @param BackedEnum ...$enum
     *
     * @return static
     */
    public function withStatus(BackedEnum ...$enum): static
    {
        $values = array_map(fn(BackedEnum $e) => $e->value, $enum);

        $this->whereIn('status', $values);

        return $this;
    }
}
