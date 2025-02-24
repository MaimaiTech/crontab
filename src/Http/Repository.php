<?php

namespace Plugin\MineAdmin\Crontab;

use App\Repository\IRepository;
use Hyperf\Database\Model\Builder;
use Plugin\MineAdmin\Crontab\Model\Crontab;

/**
 * @extends IRepository<Crontab>
 */
class Repository extends IRepository
{
    public function __construct(
        protected readonly Crontab $model
    ){}

    public function handleSearch(Builder $query, array $params): Builder
    {
        return $query->with(['execute_logs'])
            ->when(isset($params['name']), function (Builder $query) use ($params) {
                return $query->where('name', 'like', "%{$params['name']}%");
            })
            ->when(isset($params['status']), function (Builder $query) use ($params) {
                return $query->where('status', $params['status']);
            })
            ->when(isset($params['type']), function (Builder $query) use ($params) {
                return $query->where('type', $params['type']);
            })
            ->when(isset($params['is_on_one_server']), function (Builder $query) use ($params) {
                return $query->where('is_on_one_server', $params['is_on_one_server']);
            })
            ->when(isset($params['is_singleton']), function (Builder $query) use ($params) {
                return $query->where('is_singleton', $params['is_singleton']);
            })
            ->when(isset($params['created_at']), function (Builder $query) use ($params) {
                return $query->whereBetween('created_at', $params['created_at']);
            })
            ->when(isset($params['updated_at']), function (Builder $query) use ($params) {
                return $query->whereBetween('updated_at', $params['updated_at']);
            })
            ->when(isset($params['memo']), function (Builder $query) use ($params) {
                return $query->where('memo', 'like', "%{$params['memo']}%");
            })
            ->orderByDesc('created_at');
    }
}