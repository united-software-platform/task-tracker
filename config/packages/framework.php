<?php

declare(strict_types=1);

use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $framework): void {
    $framework->secret('%env(APP_SECRET)%');
    $framework->router()->utf8(true);
    $framework->cache()->app('cache.adapter.filesystem');
};
