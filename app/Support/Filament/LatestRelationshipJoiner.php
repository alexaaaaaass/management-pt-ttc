<?php

namespace App\Support\Filament;

use Filament\Support\Services\RelationshipJoiner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as BaseQueryBuilder;

class LatestRelationshipJoiner extends RelationshipJoiner
{
    public function prepareQueryForNoConstraints(Relation $relationship): Builder
    {
        $query = parent::prepareQueryForNoConstraints($relationship);
        $baseQuery = $query->getQuery();

        if (empty($baseQuery->orders)) {
            $qualifiedKeyName = $query->getModel()->getQualifiedKeyName();
            $query->orderByDesc($qualifiedKeyName);

            $baseQuery->beforeQuery(static function (BaseQueryBuilder $builder) use ($qualifiedKeyName): void {
                if (
                    is_array($builder->orders)
                    && count($builder->orders) > 1
                    && ($builder->orders[0]['column'] ?? null) === $qualifiedKeyName
                    && strtolower((string) ($builder->orders[0]['direction'] ?? '')) === 'desc'
                ) {
                    // Explicit orderBy() was added later (e.g. via modifyQueryUsing);
                    // drop our default fallback so explicit ordering takes primary precedence.
                    array_shift($builder->orders);
                }
            });
        }

        return $query;
    }
}
