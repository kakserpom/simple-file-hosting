<?php

namespace MyProject\HTTP\Controllers;

use MyProject\Model\File;
use MyProject\Model\Support\Message;
use MyProject\Model\Support\Ticket;
use MyProject\Model\SupportMessage;
use MyProject\Model\SupportTicket;
use PHPDaemon\Utils\MIME;
use Zer0\Config\Interfaces\ConfigInterface;
use Zer0\FileStorage\Plain;
use Zer0\HTTP\Exceptions\BadRequest;
use Zer0\HTTP\Exceptions\Forbidden;
use Zer0\HTTP\Exceptions\MovedTemporarily;
use Zer0\HTTP\Exceptions\NotFound;
use Zer0\HTTP\Responses\Template;
use Zer0\Model\Generic;
use Zer0\Socket\Socket;

final class Crud extends Base
{
    /**
     * @var string
     */
    protected $configName = 'Crud';

    /**
     * @var string
     */
    public $entity;

    /**
     * @var ConfigInterface
     */
    protected $entityConfig;

    /**
     * @throws Forbidden
     */
    public function before(): void
    {
        parent::before();

        if (!$this->hasUser() || !$this->user->admin) {
            throw new Forbidden();
        }

        $this->entity = ucfirst((string)($_SERVER['ROUTE_ENTITY'] ?? ''));
        if ($this->entity === '') {
            throw new NotFound;
        }

        $this->entityConfig = $this->config->{$this->entity};
        if (!$this->entityConfig) {
            throw new NotFound;
        }
    }

    /**
     * @return Template
     */
    public function indexAction(): Template
    {
        $entities = [];
        foreach ($this->config->sectionsList() as $item) {
            $entity = $this->config->{ucfirst($item)}->toArray();
            $entity['name'] = $item;
            $entities[] = $entity;
        }
        return new Template('Crud/index.tpl', [
            'entities' => $entities,
        ]);
    }


    /**
     * @return array
     */
    public function createAction(): array
    {
        $class = $this->entityConfig->model;
        $record = $class::create($_POST)->save();
        return [
            'Result' => 'OK',
            'Record' => $this->record($record),
        ];
    }

    /**
     * @return array
     */
    public function updateAction(): array
    {
        $class = $this->entityConfig->model;
        $pk = $class::primaryKeyScheme();
        $record = $class::whereFieldEq($pk, $_POST[$pk] ?? '')->load()->attr($_POST)->save();
        return [
            'Result' => 'OK',
            'Record' => $this->record($record),
        ];
    }

    /**
     * @return array
     */
    public function deleteAction(): array
    {
        $class = $this->entityConfig->model;
        $pk = $class::primaryKeyScheme();
        $record = $class::whereFieldEq($pk, $_POST[$pk] ?? '')->delete();
        return [
            'Result' => 'OK',
        ];
    }

    public function record(Generic $row): array
    {
        $record = [];
        foreach ($this->entityConfig->fields as $field => $props) {
            if (($props['type'] ?? '') === 'date') {
                $record[$field] = '/Date(' . ($row[$field] * 1000) . ')/';
            } else {
                $record[$field] = $row[$field];
            }
        }
        return $record;
    }

    /**
     * @return array
     */
    public function listAction(): array
    {
        $offset = (int)($_GET['jtStartIndex'] ?? 0);
        $limit = (int)($_GET['jtPageSize'] ?? 0);
        $order = explode(' ', (string)($_GET['jtSorting'] ?? ''), 2);
        $class = $this->entityConfig->model;
        $model = $class::any()
            ->offset($offset);

        if (count($order) === 2) {
            $model->orderBy(...$order);
        }

        $records = [];
        foreach ($model->load($limit) as $row) {
            $records[] = $this->record($row);
        }

        return [
            'Result' => 'OK',
            'TotalRecordCount' => $model->count(),
            'Records' => $records,
        ];
    }

    /**
     * @return array
     */
    public function initAction(): array
    {
        $config = $this->entityConfig->toArray();

        foreach (['list', 'delete', 'update', 'create'] as $action) {
            $config['actions'][$action . 'Action'] = $this->http->buildUrl('crud', [
                'entity' => $this->entity,
                'action' => $action,
            ]);
        }

        unset($config['model']);

        return $config;
    }
}
