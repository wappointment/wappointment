<?php

namespace Wappointment\Models;

trait CanSortByParent
{
    public function ScopeGetParentSorting($query)
    {
        $collectionResult = $query->get();

        $parents = $collectionResult->where('parent', 0);
        return $parents->map(function ($item) use ($collectionResult) {
            $item->children = array_values($collectionResult->where('parent', $item->id)->all());
            return $item;
        })->values();
    }
}
