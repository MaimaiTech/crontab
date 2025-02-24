<?php

namespace Plugin\MineAdmin\Crontab;

use Composer\InstalledVersions;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ApplicationInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

class InstallScript {
    public function __invoke(){
        if (!InstalledVersions::isInstalled('mineadmin/crontab')){
            throw new \RuntimeException('mineadmin/crontab 未安装,请执行 composer require mineadmin/crontab');
        }
        $app = ApplicationContext::getContainer()->get(ApplicationInterface::class);
        $app->setAutoExit(false);
        $app->run(new ArrayInput(['crontab:migrate']),new NullOutput());
        // todo 菜单
        $menus = [
            'name' => '定时任务管理',
        ];
    }

}