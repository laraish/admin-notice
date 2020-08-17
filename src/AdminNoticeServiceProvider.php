<?php

namespace Laraish\AdminNotice;

use Illuminate\Support\ServiceProvider;

class AdminNoticeServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        AdminNotice::init();
    }
}
