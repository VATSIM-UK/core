<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Tracks changes to the specified attributes.
 * Adapted from Illuminate\Database\Eloquent\Concerns\GuardsAttributes.
 */
trait TracksChanges
{
    /**
     * The attributes that are trackable.
     *
     * @var array
     */
    protected $tracked = [];

    /**
     * The attributes that aren't trackable.
     *
     * @var array
     */
    protected $untracked = ['*'];

    /**
     * Indicates if tracking of all attributes is enabled.
     *
     * @var bool
     */
    protected static $fullyTracked = false;

    /**
     * The model's original attributes.
     *
     * @var array
     */
    protected $originalTracked = [];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function bootTracksChanges()
    {
        static::registerModelEvent('saving', function ($model) {
            $model->rememberOriginal();
        });

        static::registerModelEvent('saved', function ($model) {
            $model->trackChanges();
        });
    }

    /**
     * Store the original values of the attributes.
     */
    public function rememberOriginal()
    {
        $this->originalTracked = array_intersect_key($this->getOriginal(), $this->getDirty());
    }

    /**
     * Store the changes in the database.
     */
    public function trackChanges()
    {
        $attributes = $this->originalTracked;

        foreach ($this->trackableFromArray($attributes) as $key => $value) {
            if ($this->isTrackable($key) && $value != $this->$key) {
                $dataChange = new \App\Models\Sys\Data\Change;
                $dataChange->data_key = $key;
                $dataChange->data_old = $value;
                $dataChange->data_new = $this->$key;
                $this->dataChanges()->save($dataChange);
            }
        }

        $this->originalTracked = [];
    }

    /**
     * Relationship for the model's data changes.
     *
     * @return mixed
     */
    public function dataChanges()
    {
        return $this->morphMany(\App\Models\Sys\Data\Change::class, 'model')
            ->orderBy('created_at', 'DESC');
    }

    /**
     * Get the tracked attributes of the model.
     *
     * @return array
     */
    public function getTracked()
    {
        return $this->tracked;
    }

    /**
     * Set the trackable attributes for the model.
     *
     * @return $this
     */
    public function tracked(array $tracked)
    {
        $this->tracked = $tracked;

        return $this;
    }

    /**
     * Get the untracked attributes for the model.
     *
     * @return array
     */
    public function getUntracked()
    {
        return $this->untracked;
    }

    /**
     * Set the untrackable attributes for the model.
     *
     * @return $this
     */
    public function untracked(array $untracked)
    {
        $this->untracked = $untracked;

        return $this;
    }

    /**
     * Determine if the model is fully tracked.
     *
     * @return bool
     */
    public static function isFullyTracked()
    {
        return static::$fullyTracked;
    }

    /**
     * Determine if the given attribute is tracked.
     *
     * @param  string  $key
     * @return bool
     */
    public function isTrackable($key)
    {
        if (static::$fullyTracked) {
            return true;
        }

        // If the key is in the "fillable" array, we can of course assume that it's
        // a fillable attribute. Otherwise, we will check the guarded array when
        // we need to determine if the attribute is black-listed on the model.
        if (in_array($key, $this->getTracked())) {
            return true;
        }

        // If the attribute is explicitly listed in the "guarded" array then we can
        // return false immediately. This means this attribute is definitely not
        // fillable and there is no point in going any further in this method.
        if ($this->isUntracked($key)) {
            return false;
        }

        return empty($this->getTracked()) &&
            ! Str::startsWith($key, '_');
    }

    /**
     * Determine if the given key is untracked.
     *
     * @param  string  $key
     * @return bool
     */
    public function isUntracked($key)
    {
        return in_array($key, $this->getUntracked()) || $this->getUntracked() == ['*'];
    }

    /**
     * Determine if the model is totally untracked.
     *
     * @return bool
     */
    public function totallyUntracked()
    {
        return count($this->getTracked()) == 0 && $this->getUntracked() == ['*'];
    }

    /**
     * Get the trackable attributes of a given array.
     *
     * @return array
     */
    protected function trackableFromArray(array $attributes)
    {
        if (count($this->getTracked()) > 0 && ! static::$fullyTracked) {
            return array_intersect_key($attributes, array_flip($this->getTracked()));
        }

        return $attributes;
    }
}
