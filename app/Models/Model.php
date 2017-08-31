<?php

namespace App\Models;

use App\Models\Concerns\TracksChanges;
use App\Models\Concerns\TracksEvents;
use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * App\Models\Model
 *
 * @method static \Illuminate\Database\Eloquent\Model make(array $attributes = array())
 * @method static \Illuminate\Database\Eloquent\Builder withGlobalScope(string $identifier, $scope)
 * @method static \Illuminate\Database\Eloquent\Builder withoutGlobalScope($scope)
 * @method static \Illuminate\Database\Eloquent\Builder withoutGlobalScopes($scopes = NULL)
 * @method static array removedScopes()
 * @method static \Illuminate\Database\Eloquent\Builder whereKey($id)
 * @method static \Illuminate\Database\Eloquent\Builder where($column, string $operator = NULL, $value = NULL, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|static orWhere($column, string $operator = NULL, $value = NULL)
 * @method static \Illuminate\Database\Eloquent\Collection hydrate(array $items)
 * @method static \Illuminate\Database\Eloquent\Collection fromQuery(string $query, array $bindings = array())
 * @method static \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static[]|static|null find($id, array $columns = array(0=>'*',))
 * @method static \Illuminate\Database\Eloquent\Collection findMany(array $ids, array $columns = array(0=>'*',))
 * @method static \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection findOrFail($id, array $columns = array(0=>'*',))
 * @method static \Illuminate\Database\Eloquent\Model findOrNew($id, array $columns = array(0=>'*',))
 * @method static \Illuminate\Database\Eloquent\Model firstOrNew(array $attributes, array $values = array())
 * @method static \Illuminate\Database\Eloquent\Model firstOrCreate(array $attributes, array $values = array())
 * @method static \Illuminate\Database\Eloquent\Model updateOrCreate(array $attributes, array $values = array())
 * @method static \Illuminate\Database\Eloquent\Model|static firstOrFail(array $columns = array(0=>'*',))
 * @method static \Illuminate\Database\Eloquent\Model|static|mixed firstOr($columns = array(0=>'*',), $callback = NULL)
 * @method static mixed value(string $column)
 * @method static \Illuminate\Database\Eloquent\Collection|static[] get(array $columns = array(0=>'*',))
 * @method static \Illuminate\Database\Eloquent\Model[] getModels(array $columns = array(0=>'*',))
 * @method static array eagerLoadRelations(array $models)
 * @method static array eagerLoadRelation(array $models, string $name, \Closure $constraints)
 * @method static array relationsNestedUnder(string $relation)
 * @method static bool isNestedUnder(string $relation, string $name)
 * @method static \Generator cursor()
 * @method static bool chunkById(int $count, callable $callback, string $column = NULL, $alias = NULL)
 * @method static enforceOrderBy()
 * @method static \Illuminate\Support\Collection pluck(string $column, $key = NULL)
 * @method static \Illuminate\Contracts\Pagination\LengthAwarePaginator paginate(int $perPage = NULL, array $columns = array(0=>'*',), string $pageName = 'page', $page = NULL)
 * @method static \Illuminate\Contracts\Pagination\Paginator simplePaginate(int $perPage = NULL, array $columns = array(0=>'*',), string $pageName = 'page', $page = NULL)
 * @method static \Illuminate\Database\Eloquent\Model|$this create(array $attributes = array())
 * @method static \Illuminate\Database\Eloquent\Model|$this forceCreate(array $attributes)
 * @method static array addUpdatedAtColumn(array $values)
 * @method static onDelete(\Closure $callback)
 * @method static mixed scopes(array $scopes)
 * @method static \Illuminate\Database\Eloquent\Builder|static applyScopes()
 * @method static mixed callScope(callable $scope, array $parameters = array())
 * @method static addNewWheresWithinGroup(\Illuminate\Database\Query\Builder $query, int $originalWhereCount)
 * @method static groupWhereSliceForScope(\Illuminate\Database\Query\Builder $query, array $whereSlice)
 * @method static array createNestedWhere(array $whereSlice, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder without($relations)
 * @method static \Illuminate\Database\Eloquent\Model newModelInstance(array $attributes = array())
 * @method static array parseWithRelations(array $relations)
 * @method static array createSelectWithConstraint(string $name)
 * @method static array addNestedWiths(string $name, array $results)
 * @method static \Illuminate\Database\Query\Builder getQuery()
 * @method static \Illuminate\Database\Eloquent\Builder setQuery(\Illuminate\Database\Query\Builder $query)
 * @method static \Illuminate\Database\Query\Builder toBase()
 * @method static array getEagerLoads()
 * @method static \Illuminate\Database\Eloquent\Builder setEagerLoads(array $eagerLoad)
 * @method static \Illuminate\Database\Eloquent\Model getModel()
 * @method static \Illuminate\Database\Eloquent\Builder setModel(\Illuminate\Database\Eloquent\Model $model)
 * @method static \Closure getMacro(string $name)
 * @method static bool chunk(int $count, callable $callback)
 * @method static bool each(callable $callback, int $count = 1000)
 * @method static mixed first(array $columns = array(0=>'*',))
 * @method static mixed when($value, callable $callback, callable $default = NULL)
 * @method static mixed unless($value, callable $callback, callable $default = NULL)
 * @method static \Illuminate\Pagination\LengthAwarePaginator paginator(\Illuminate\Support\Collection $items, int $total, int $perPage, int $currentPage, array $options)
 * @method static \Illuminate\Pagination\Paginator simplePaginator(\Illuminate\Support\Collection $items, int $perPage, int $currentPage, array $options)
 * @method static \Illuminate\Database\Eloquent\Builder|static has(string $relation, string $operator = '>=', int $count = 1, string $boolean = 'and', $callback = NULL)
 * @method static \Illuminate\Database\Eloquent\Builder|static hasNested(string $relations, string $operator = '>=', int $count = 1, string $boolean = 'and', $callback = NULL)
 * @method static \Illuminate\Database\Eloquent\Builder|static orHas(string $relation, string $operator = '>=', int $count = 1)
 * @method static \Illuminate\Database\Eloquent\Builder|static doesntHave(string $relation, string $boolean = 'and', $callback = NULL)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereHas(string $relation, $callback = NULL, string $operator = '>=', int $count = 1)
 * @method static \Illuminate\Database\Eloquent\Builder|static orWhereHas(string $relation, \Closure $callback = NULL, string $operator = '>=', int $count = 1)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereDoesntHave(string $relation, $callback = NULL)
 * @method static \Illuminate\Database\Eloquent\Builder withCount($relations)
 * @method static \Illuminate\Database\Eloquent\Builder|static addHasWhere(\Illuminate\Database\Eloquent\Builder $hasQuery, \Illuminate\Database\Eloquent\Relations\Relation $relation, string $operator, int $count, string $boolean)
 * @method static \Illuminate\Database\Eloquent\Builder|static mergeConstraintsFrom(\Illuminate\Database\Eloquent\Builder $from)
 * @method static \Illuminate\Database\Eloquent\Builder addWhereCountQuery(\Illuminate\Database\Query\Builder $query, string $operator = '>=', int $count = 1, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Relations\Relation getRelationWithoutConstraints(string $relation)
 * @method static bool canUseExistsForExistenceCheck(string $operator, int $count)
 * @mixin \Eloquent
 */
abstract class Model extends EloquentModel
{
    use TracksChanges, TracksEvents;

    protected static function boot()
    {
        parent::boot();
        self::created([get_called_class(), 'eventCreated']);
        self::updated([get_called_class(), 'eventUpdated']);
        self::deleted([get_called_class(), 'eventDeleted']);
    }

    public static function eventCreated($model)
    {
    }

    public static function eventUpdated($model)
    {
    }

    public static function eventDeleted($model)
    {
    }

    public function toArray()
    {
        $array = parent::toArray();
        $array['status'] = ($this->deleted_at ? 'Deleted' : 'Active');
        if (isset($array['pivot'])) {
            unset($array['pivot']);
        }

        return $array;
    }
}
