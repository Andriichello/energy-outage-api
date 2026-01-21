<?php

namespace App\Queries;

use Closure;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;

/**
 * Class BaseQuery.
 *
 * @property Model $model
 *
 * @method BaseQuery select($columns = ['*'])
 * @method BaseQuery whereKey($id)
 * @method Model|null find($id, $columns = ['*'])
 * @method Model findOrFail($id, $columns = ['*'])
 * @method Model|null first($columns = ['*'])
 * @method Model firstOrFail($columns = ['*'])
 * @method Model make(array $attributes = [])
 * @method Model create(array $attributes = [])
 * @method Model updateOrCreate(array $attributes, array $values = [])
 */
class BaseQuery extends EloquentBuilder
{
    /**
     * Create a new query instance for wrapped where condition.
     *
     * @return static
     */
    public function forWrappedWhere(): static
    {
        /** @var static $builder */
        $builder = $this->model->newModelQuery();

        return $builder;
    }

    /**
     * Add a wrapped where statement to the query.
     *
     * @param Closure $callback
     * @param string $boolean
     *
     * @return static
     */
    public function whereWrapped(Closure $callback, string $boolean = 'and'): static
    {
        call_user_func($callback, $query = $this->forWrappedWhere());
        $this->addNestedWhereQuery($query->getQuery(), $boolean);

        return $this;
    }

    /**
     * Substitute a where column name with a different one.
     *
     * @param string $column
     * @param string $substitution
     *
     * @return static
     */
    public function substituteWhere(string $column, string $substitution): static
    {
        $substitute = function (array $wheres) use (&$substitute, $column, $substitution) {
            foreach ($wheres as &$where) {
                if ($where['type'] === 'Nested') {
                    // Recursively handle nested where clauses
                    $where['query']->wheres = $substitute($where['query']->wheres, $column, $substitution);
                } elseif (isset($where['column']) && $where['column'] === $column) {
                    $where['column'] = $substitution;
                }
            }
            return $wheres;
        };

        $this->getQuery()->wheres = $substitute($this->getQuery()->wheres);

        return $this;
    }

    /**
     * Get list of scopes that query has.
     *
     * @return array
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    /**
     * Determines if query has a given scope.
     *
     * @param string $scope
     *
     * @return bool
     */
    public function hasScope(string $scope): bool
    {
        return in_array($scope, $this->getScopes())
            || array_key_exists($scope, $this->getScopes());
    }

    /**
     * Get list of joins that query has. Keys are aliases
     * and values are table names.
     *
     * @return array
     */
    public function getJoins(): array
    {
        $joins = [];

        // @phpstan-ignore-next-line
        foreach ($this->getQuery()->joins ?? [] as $join) {
            /** @var JoinClause $join */
            $table = $join->table;
            $alias = null;

            $matches = [];
            $pattern = '/(?<table>(\w+|\w+[.]\w+))(\W+as\W+)(?<alias>(\w+|\w+[.]\w+))/';

            if (preg_match($pattern, $table, $matches)) {
                $table = data_get($matches, 'table');
                $alias = data_get($matches, 'alias');
            }

            $joins[$alias ?? $table] = $table;
        }

        return $joins;
    }

    /**
     * Get alias of given joined table. Null is returned if given
     * table is not joined. If there are multiple joins of the
     * given table then only the first alias will be returned.
     *
     *
     * @param string $table
     *
     * @return string|null
     */
    public function getJoinAlias(string $table): ?string
    {
        return array_search($table, $this->getJoins());
    }

    /**
     * Determines if query has a given join by table name and optionally
     * check if it's joined using a given alias.
     *
     * @param string $table
     * @param string|null $alias
     *
     * @return bool
     */
    public function hasJoin(string $table, ?string $alias = null): bool
    {
        $joins = $this->getJoins();

        if (empty($alias)) {
            return in_array($table, $joins);
        }

        return in_array($table, $joins)
            && array_key_exists($alias, $joins);
    }

    /**
     * Chunk the results of the query, but respect the
     * limit and offset if they are present.
     *
     * @param callable $callback
     * @param int $count
     *
     * @return void
     */
    public function chunkPaginated(callable $callback, int $count = 1000): void
    {
        // makes sure that previously set limit and offset are followed
        $originalLimit = $this->getQuery()->limit ?? 0; // @phpstan-ignore-line
        $originalOffset = $this->getQuery()->offset ?? 0; // @phpstan-ignore-line

        $retrieved = 0;
        $step = min($originalLimit, $count);

        while ($retrieved !== $originalLimit) {
            $query = $this->clone()
                ->offset($originalOffset + $retrieved)
                ->limit($step);

            $callback(($records = $query->get()));
            $count = $records->count();
            $retrieved += $count;

            if ($count < $step) {
                break;
            }
        }
    }
}
