<?php

namespace App\Scopes\TeamSpeak;

use App\Models\TeamSpeak\ChannelGroup;
use App\Models\TeamSpeak\ServerGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use InvalidArgumentException;

class GroupScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $class = get_class($model);
        if ($class === ServerGroup::class) {
            return $builder->where('type', 's');
        } elseif ($class === ChannelGroup::class) {
            return $builder->where('type', 'c');
        } else {
            throw new InvalidArgumentException(
                'Scope used with invalid model, is not one of: ServerGroup,ChannelGroup'
            );
        }
    }
}
