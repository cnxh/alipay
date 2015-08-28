<?php

namespace Cnxh\Alipay;

use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider;

class AlipayServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../../configs/alipay.php' => config_path('alipay.php'),

        ], 'config');
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $config = config('alipay');

        if (!empty($config['apis'])) {
            foreach ($config['apis'] as $api) {
                $api_config = array_merge($config['common'], $config[$api]);
                $class = __NAMESPACE__.'\\'.Str::studly(str_replace('.', '_', $api)).'\Api';
                $this->app->singleton($api, function ($app) use ($api_config, $class) {
                    return new $class($api_config);
                });
            }
        }
    }

    public function providers()
    {
        return (array) config('alipay.apis');
    }
}
