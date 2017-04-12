<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * App\Models\aModel.
 *
 * @method static \Illuminate\Database\Eloquent\Builder withGlobalScope(string $identifier, mixed $scope)
 * @method static \Illuminate\Database\Eloquent\Builder withoutGlobalScope(mixed $scope)
 * @method static \Illuminate\Database\Eloquent\Builder withoutGlobalScopes(mixed $scopes = NULL)
 * @method static \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|null find(mixed $id, array $columns = array(0=>'*',))
 * @method static \Illuminate\Database\Eloquent\Collection findMany(array $ids, array $columns = array(0=>'*',))
 * @method static \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection findOrFail(mixed $id, array $columns = array(0=>'*',))
 * @method static \Illuminate\Database\Eloquent\Model|static|null first(array $columns = array(0=>'*',))
 * @method static \Illuminate\Database\Eloquent\Model|static firstOrFail(array $columns = array(0=>'*',))
 * @method static \Illuminate\Database\Eloquent\Collection|static[] get(array $columns = array(0=>'*',))
 * @method static mixed value(string $column)
 * @method static bool chunk(int $count, callable $callback)
 * @method static bool each(callable $callback, int $count = 1000)
 * @method static \Illuminate\Support\Collection pluck(string $column, mixed $key = NULL)
 * @method static \Illuminate\Support\Collection lists(string $column, string $key = NULL)
 * @method static \Illuminate\Contracts\Pagination\LengthAwarePaginator paginate(int $perPage = NULL, array $columns = array(0=>'*',), string $pageName = 'page', mixed $page = NULL)
 * @method static \Illuminate\Contracts\Pagination\Paginator simplePaginate(int $perPage = NULL, array $columns = array(0=>'*',), string $pageName = 'page')
 * @method static array addUpdatedAtColumn(array $values)
 * @method static  onDelete(\Closure $callback)
 * @method static \Illuminate\Database\Eloquent\Model[] getModels(array $columns = array(0=>'*',))
 * @method static array eagerLoadRelations(array $models)
 * @method static array loadRelation(array $models, string $name, \Closure $constraints)
 * @method static array nestedRelations(string $relation)
 * @method static bool isNested(string $name, string $relation)
 * @method static \Illuminate\Database\Eloquent\Builder where(string $column, string $operator = NULL, mixed $value = NULL, string $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder|static orWhere(string $column, string $operator = NULL, mixed $value = NULL)
 * @method static \Illuminate\Database\Eloquent\Builder|static has(string $relation, string $operator = '>=', int $count = 1, string $boolean = 'and', mixed $callback = NULL)
 * @method static \Illuminate\Database\Eloquent\Builder|static hasNested(string $relations, string $operator = '>=', int $count = 1, string $boolean = 'and', mixed $callback = NULL)
 * @method static \Illuminate\Database\Eloquent\Builder|static doesntHave(string $relation, string $boolean = 'and', mixed $callback = NULL)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereHas(string $relation, \Closure $callback, string $operator = '>=', int $count = 1)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereDoesntHave(string $relation, mixed $callback = NULL)
 * @method static \Illuminate\Database\Eloquent\Builder|static orHas(string $relation, string $operator = '>=', int $count = 1)
 * @method static \Illuminate\Database\Eloquent\Builder|static orWhereHas(string $relation, \Closure $callback, string $operator = '>=', int $count = 1)
 * @method static \Illuminate\Database\Eloquent\Builder addHasWhere(\Illuminate\Database\Eloquent\Builder $hasQuery, \Illuminate\Database\Eloquent\Relations\Relation $relation, string $operator, int $count, string $boolean)
 * @method static bool shouldRunExistsQuery(string $operator, int $count)
 * @method static \Illuminate\Database\Eloquent\Builder whereCountQuery(\Illuminate\Database\Query\Builder $query, string $operator = '>=', int $count = 1, string $boolean = 'and')
 * @method static  mergeModelDefinedRelationWheresToHasQuery(\Illuminate\Database\Eloquent\Builder $hasQuery, \Illuminate\Database\Eloquent\Relations\Relation $relation)
 * @method static \Illuminate\Database\Eloquent\Relations\Relation getHasRelationQuery(string $relation)
 * @method static array parseWithRelations(array $relations)
 * @method static array parseNestedWith(string $name, array $results)
 * @method static \Illuminate\Database\Query\Builder callScope(string $scope, array $parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|static applyScopes()
 * @method static  applyScope(mixed $scope, \Illuminate\Database\Eloquent\Builder $builder)
 * @method static bool shouldNestWheresForScope(\Illuminate\Database\Query\Builder $query, int $originalWhereCount)
 * @method static  nestWheresForScope(\Illuminate\Database\Query\Builder $query, mixed $whereCounts)
 * @method static  sliceWhereConditions(\Illuminate\Database\Query\Builder $query, array $wheres, int $sliceFrom, int $sliceTo)
 * @method static array nestWhereSlice(array $whereSlice)
 * @method static \Illuminate\Database\Query\Builder|static getQuery()
 * @method static \Illuminate\Database\Query\Builder toBase()
 * @method static \Illuminate\Database\Eloquent\Builder setQuery(\Illuminate\Database\Query\Builder $query)
 * @method static array getEagerLoads()
 * @method static \Illuminate\Database\Eloquent\Builder setEagerLoads(array $eagerLoad)
 * @method static \Illuminate\Database\Eloquent\Model getModel()
 * @method static \Illuminate\Database\Eloquent\Builder setModel(\Illuminate\Database\Eloquent\Model $model)
 * @method static  macro(string $name, \Closure $callback)
 * @method static \Closure getMacro(string $name)
 */
abstract class Model extends EloquentModel
{
    protected $doNotTrack = [];

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

    public function save(array $options = [])
    {
        // Let's check the old data vs new data, so we can store data changes!
        // We check for the presence of the dataChanges relationship, to warrent tracking changes.
        if (get_called_class() != "App\Models\Sys\Data\Change" && method_exists($this, 'dataChanges')) {
            // Get the changed values!
            foreach ($this->getDirty() as $attribute => $value) {
                // There are some values we might want to remove.  They may be stored in a variable
                // called doNotTrack
                if (isset($this->doNotTrack) && is_array($this->doNotTrack)) {
                    if (in_array($attribute, $this->doNotTrack)) {
                        continue; // We don't wish to track this :(
                    }
                }

                $original = $this->getOriginal($attribute);

                $dataChange = new \App\Models\Sys\Data\Change();
                $dataChange->data_key = $attribute;
                $dataChange->data_old = $original;
                $dataChange->data_new = $value;
                $this->dataChanges()->save($dataChange);
            }
        }

        return parent::save($options);
    }
}
