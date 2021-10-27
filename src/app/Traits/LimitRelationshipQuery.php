<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait LimitRelationshipQuery
{
    public function scopeLimitPerGroup($query, $group, $n)
    {
        // queried table
        $table = ($this->getTable());

        // initialize MySQL variables inline
        $query->from(DB::raw("(SELECT @rank:=0, @group:=0) as vars, {$table}"));

        // if no columns already selected, select *
        if (!$query->getQuery()->columns) {
            $query->select("{$table}.*");
        }

        // make sure column aliases are unique
        $groupAlias = 'group_' . md5(time());
        $rankAlias  = 'rank_' . md5(time());

        // apply mysql variables
        $query->addSelect(DB::raw(
            "@rank := IF(@group = {$group}, @rank+1, 1) as {$rankAlias}, @group := {$group} as {$groupAlias}"
        ));

        // make sure first order clause is the group order
        $query->getQuery()->orders = (array) $query->getQuery()->orders;
        array_unshift($query->getQuery()->orders, ['column' => $group, 'direction' => 'asc']);

        // prepare subquery
        $subQuery = $query->toSql();

        // prepare new main base Query\Builder
        $newBase = $this->newQuery()
            ->from(DB::raw("({$subQuery}) as {$table}"))
            ->mergeBindings($query->getQuery())
            ->where($rankAlias, '<=', $n)
            ->getQuery();

        // replace underlying builder to get rid of previous clauses
        $query->setQuery($newBase);
    }

    public function scopeLimitPerGroupUnion($query, $group, $n)
    {
        // queried table
        $table =  ($this->getTable());

        // make sure column aliases are unique
        $groupAlias = 'group_' . md5(time());
        $rankAlias  = 'rank_' . md5(time());

        // prepare subquery
        $subQuery = $query->toSql();

        // prepare new main base Query\Builder
        $newBase = $this->newQuery()
            ->select(DB::raw("*"))
            ->from(DB::raw(
                "(SELECT"
                    . "*,"
                    . "@rank := IF(@group = union_table.{$group}, @rank+1, 1) as {$rankAlias},"
                    . "@group := union_table.{$group} as {$groupAlias} FROM ({$subQuery}) as union_table "
                    . "JOIN (SELECT @group:= 0, @rank:= 0) as vars order by {$group} ASC, `sent_at` DESC) as {$table}"
            ))
            ->where("{$table}.{$rankAlias}", '<=', $n)
            ->setBindings([
                $query->getBindings(),
                $n
            ])
            ->getQuery();

        // replace underlying builder to get rid of previous clauses
        $query->setQuery($newBase);
    }
}
