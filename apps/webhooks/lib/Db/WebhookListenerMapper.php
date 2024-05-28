<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Webhooks\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\Exception;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @template-extends QBMapper<WebhookListener>
 */
class WebhookListenerMapper extends QBMapper {
	public const TABLE_NAME = 'webhook_listeners';

	public function __construct(IDBConnection $db) {
		parent::__construct($db, self::TABLE_NAME, WebhookListener::class);
	}

	/**
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 * @throws Exception
	 */
	public function getById(int $id): WebhookListener {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));

		return $this->findEntity($qb);
	}

	/**
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 * @throws Exception
	 * @return WebhookListener[]
	 */
	public function getAll(): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName());

		return $this->findEntities($qb);
	}

	public function addWebhookListener(
		string $userId,
		string $httpMethod,
		string $uri,
		string $event,
		?array $headers,
		?string $authMethod,
		?array $authData,
	) {
		$webhookListener = WebhookListener::fromParams(
			[
				'userId' => $userId,
				'httpMethod' => $httpMethod,
				'uri' => $uri,
				'event' => $event,
				'headers' => $headers,
				'authMethod' => $authMethod ?? 'none',
				'authData' => $authData,
			]
		);
		return $this->insert($webhookListener);
	}

	public function updateWebhookListener(
		int $id,
		string $userId,
		string $httpMethod,
		string $uri,
		string $event,
		?array $headers,
		?string $authMethod,
		?array $authData,
	) {
		$webhookListener = WebhookListener::fromParams(
			[
				'id' => $id,
				'userId' => $userId,
				'httpMethod' => $httpMethod,
				'uri' => $uri,
				'event' => $event,
				'headers' => $headers,
				'authMethod' => $authMethod,
				'authData' => $authData,
			]
		);
		return $this->update($webhookListener);
	}

	/**
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 * @throws Exception
	 */
	public function deleteById(int $id): bool {
		$qb = $this->db->getQueryBuilder();

		$qb->delete($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));

		return ($qb->executeStatement() > 0);
	}

	/**
	 * @return list<string>
	 * TODO cache
	 */
	public function getAllConfiguredEvents(): array {
		$qb = $this->db->getQueryBuilder();

		$qb->selectDistinct('event')
			->from($this->getTableName());

		$result = $qb->executeQuery();

		$configuredEvents = [];

		while (($event = $result->fetchOne()) !== false) {
			$configuredEvents[] = $event;
		}

		return $configuredEvents;
	}

	public function getByEvent(string $event): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('event', $qb->createNamedParameter($event, IQueryBuilder::PARAM_STR)));

		return $this->findEntities($qb);
	}
}
