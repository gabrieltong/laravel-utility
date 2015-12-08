<?php
namespace Gabe\Utility\Eloquent;

trait PessimisticLockingTrait
{
    public static function getBySharedLock(array $whereCondition = [], array $updateCondition = [], $tryCount = 1)
    {
        $triedCount = 0;
        $record = null;
        $params = [$whereCondition, $updateCondition];
        while($record == null && $triedCount < $tryCount)
        {
            $triedCount++;
            $record = \DB::transaction(function($params) use ($params)
            {
                list($whereCondition, $updateCondition) = $params;
                $first = static::where($whereCondition)->orderByRaw('rand()')->first();
                $whereConditionFull = array_merge($whereCondition,[$first->getKeyName()=>$first->getKey()]);
                $result = static::where($whereConditionFull)->sharedLock()->update($updateCondition);
                if($result == 1)
                {
                    return $first;
                }else{
                    return null;
                }
            });
        }
        return $record;
    }
}