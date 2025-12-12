<?php

namespace MyCore\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

trait SoftDeletesWithUser
{
    use SoftDeletes;

    /**
     * Perform the actual delete query on this model instance.
     *
     * @return void
     */

    protected function runSoftDelete()
    {
        $query = $this->setKeysForSaveQuery($this->newModelQuery());

        $time = $this->freshTimestamp();

        $columns = [$this->getDeletedAtColumn() => $this->fromDateTime($time)];

        $this->{$this->getDeletedAtColumn()} = $time;

        if ($this->timestamps && ! is_null($this->getUpdatedAtColumn())) {
            $this->{$this->getUpdatedAtColumn()} = $time;

            $columns[$this->getUpdatedAtColumn()] = $this->fromDateTime($time);
        }

        if($id = auth()->id()) {
            $columns['updated_by'] = $id;
        }

        $query->update($columns);

        $this->syncOriginalAttributes(array_keys($columns));

        $this->fireModelEvent('trashed', false);
    }
}
