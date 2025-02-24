<?php

namespace Plugin\MineAdmin\Crontab\Http;

use App\Service\IService;
use Hyperf\Crontab\Strategy\StrategyInterface;
use Hyperf\Database\Model\Collection;
use Plugin\MineAdmin\Crontab\Model\Crontab;


/**
 * @extends IService<Crontab>
 */
class Service extends IService
{
    public function __construct(
        protected readonly Repository $repository,
        protected readonly StrategyInterface $strategy
    ){}

    public function execute(mixed $id): void
    {
        /**
         * @var Crontab[]|Collection $list
         */
        $list = $this->repository->getQuery()->whereKey($id)->get();
        foreach ($list as $cronEntity){
            $cron = new \Mine\Crontab\Crontab($cronEntity->id);
            $this->strategy->dispatch($cron);
        }
    }
}