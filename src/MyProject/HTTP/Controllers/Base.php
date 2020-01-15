<?php

namespace MyProject\HTTP\Controllers;

use Zer0\HTTP\AbstractController;
use Zer0\HTTP\Responses\Template;

abstract class Base extends AbstractController
{

    /**
     * @var string
     */
    protected $language;

    /**
     * @var \MyProject\Model\User
     */
    protected $user;

    /**
     * @return bool
     */
    public function hasUser(): bool
    {
        return (bool)$this->getUser();
    }

    /**
     * @return object|null
     */
    public function getUser(): ?object
    {
        if ($this->user !== null) {
            return $this->user;
        }
        return $this->user = $this->app->broker('User')->getCurrent();
    }

    /**
     * @param $response
     */
    public function renderResponse($response): void
    {
        if ($response instanceof Template) {
            $response->setCallback(function ($tpl) {
                $tpl->lang = $this->language;
                $tpl->assign([
                    'this' => $this,
                    'user' => $this->getUser(),
                ]);
                $tpl->register_function('file_url', function ($file) {
                    return $this->getFileStorage()->getUrl('uploads', $file);
                });

                $tpl->register_function('tr', function (...$args) {
                    return __(...$args);
                });
            });
        }
        parent::renderResponse($response);
    }

    /**
     * @return \Zer0\FileStorage\Base
     */
    public function getFileStorage()
    {
        return $this->app->factory('FileStorage');
    }

    /**
     * @return \Zer0\Queue\Pools\Base
     */
    protected function getQueue(): \Zer0\Queue\Pools\Base
    {
        return $this->app->factory('Queue');
    }
}
