<?php

namespace MyProject\Brokers;

use NT\API\ClientAsync;
use NT\API\Exceptions\RequestDeniedException;
use Zer0\Brokers\Base;
use Zer0\Cache\Item\Item;
use Zer0\Cache\Item\ItemAsync;
use Zer0\Config\Interfaces\ConfigInterface;

class User extends Base
{

    //@TODO needs more cowbell (config)

    public function instantiate(ConfigInterface $config)
    {
    }

    /**
     * @return null
     */
    public function getCurrent(): ?\MyProject\Model\User
    {
        $this->app->factory('Session')->startIfExists();

        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        return \MyProject\Model\User::where('id = ? AND active = 1', [$_SESSION['user_id']])->first();
    }

    /**
     * @param $cb
     */
    public function getCurrentAsync($cb): void
    {
        if (!$_SESSION) {
            $this->app->factory('SessionAsync')->startIfExists(function ($session) use ($cb) {
                if (!isset($session['user_id'])) {
                    $cb(false);
                    return;
                }
                $this->getCurrentAsync($cb);
            });
            return;
        }
        if (!isset($_SESSION['user_id'])) {
            $cb(false);
            return;
        }

        \MyProject\Model\User::where('id = ? AND active = 1', [$_SESSION['user_id']])->first($cb);
    }
}
