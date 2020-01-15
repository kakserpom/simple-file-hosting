<?php

namespace MyProject\HTTP\Controllers;

use MyProject\Model\File;
use MyProject\Model\SupportMessage;
use MyProject\Model\SupportTicket;
use PHPDaemon\Utils\MIME;
use Zer0\FileStorage\Plain;
use Zer0\HTTP\Exceptions\Forbidden;
use Zer0\HTTP\Exceptions\MovedTemporarily;
use Zer0\HTTP\Exceptions\NotFound;
use Zer0\HTTP\Responses\Template;

final class Files extends Base
{
    /**
     *
     */
    public function downloadAction(): void
    {
        $file = File::where('id = ? AND md5 = ?', [$_SERVER['ROUTE_ID'], $_SERVER['ROUTE_MD5']])->first();
        if ($file === null) {
            throw new NotFound;
        }
        $this->http->header('Content-Type: ' . MIME::get($file->filename));
        $this->http->header('Content-Disposition: attachment; filename*=UTF-8\'\'' . rawurlencode($file->filename));
        $this->http->header('X-Accel-Redirect: /storage/files/' . $file->path);
    }

    /**
     * @throws NotFound
     * @throws \Zer0\HTTP\Exceptions\Redirect
     */
    public function deleteAction(): void
    {
        $file = File::where('id = ? AND deletion_key = ?', [$_SERVER['ROUTE_ID'], $_SERVER['ROUTE_KEY']])->first();
        if ($file === null) {
            throw new NotFound;
        }
        /**
         * @var $storage Plain
         */
        $storage = $this->app->factory('FileStorage');

        $storage->delete('files', $file->path);

        $file->delete();

        throw MovedTemporarily::url('/');
    }

    /**
     * @return array
     * @throws \Zer0\FileStorage\Exceptions\FileNotFoundException
     * @throws \Zer0\FileStorage\Exceptions\OperationFailedException
     */
    public function uploadAction(): array
    {
        /**
         * @var $storage Plain
         */
        $storage = $this->app->factory('FileStorage');

        $container = 'files';
        if (!$path = $storage->saveUploadedFile(key($_FILES), $container)) {
            return ['error' => true];
        }

        $fullPath = $storage->getPath($container, $path);

        $file = File::create([
            'size' => filesize($fullPath),
            'filename' => basename($path),
            'path' => $path,
            'md5' => md5_file($fullPath),
            'deletion_key' => sha1(openssl_random_pseudo_bytes(32)),
        ])->save();

        return $file->toArray();
    }

    /**
     * @return Template
     */
    public function indexAction(): Template
    {
        $files = File::whereFieldEq('user_id', $this->user->id)->load();

        return new Template('Files/index.tpl', [
            'files' => $files,
        ]);
    }
}
