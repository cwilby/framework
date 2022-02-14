<?php

namespace Illuminate\Database\Eloquent\Relations\Concerns;

trait InteractsWithMany
{
    /**
     * Sync items with has-many-like relationships.
     *
     * @param mixed $items
     */
    public function sync($items)
    {
        $relatedLocalKey = $this->getRelated()->getKeyName();

        // First we should determine what is to be performed by spinning through $items to check whether the item has the related model's local key.
        // If an item has that key, it will later be updated and "kept" to prevent deletion.
        // If an item does not have that key, it will be created.
        $keeps = [];
        $updates = [];
        $creates = [];

        foreach ($items as $item) {
            if (isset($item[$relatedLocalKey])) {
                $keeps[] = $item[$relatedLocalKey];
                $updates[$item[$relatedLocalKey]] = $item;
            } else {
                $creates[] = $item;
            }
        }

        // Then, we will create a new query to efficiently spin through related models we're updating
        // with eachById to perform update operations for each model, to ensure events are fired.
        $this->clone()
            ->whereIn($relatedLocalKey, array_keys($updates))
            ->eachById(fn ($updatable) => $updatable->update($updates[$updatable->{$relatedLocalKey}]));

        // Next, we create a similar query to efficiently spin through related models we're not keeping
        // with eachById to perform delete operations for each model, to ensure events are fired.
        $this->clone()
            ->whereNotIn($relatedLocalKey, $keeps)
            ->eachById(fn ($deletable) => $deletable->delete());

        // We then create the new related models for the current relationship.
        foreach ($creates as $attributes) {
            $this->create($attributes);
        }

        // Finally, we return the performed operations back to developers.
        return [
            'keeps' => $keeps,
            'updates' => $updates,
            'creates' => $creates
        ];
    }
}
