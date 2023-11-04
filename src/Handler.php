<?php

namespace Hiraeth\Stash\Session;

use DateTime;
use SessionHandlerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\CacheItemInterface;

/**
 *
 * Original authors:
 *
 * (c) 2015 Aaron Scherer <aequasi@gmail.com>, Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 * @author Aaron Scherer <aequasi@gmail.com>
 */
class Handler implements SessionHandlerInterface
{
	/**
	 * @var CacheItemPoolInterface
	 */
	private $cache;

	/**
	 * @var int Time to live in seconds
	 */
	private $ttl;


	/**
	 *
	 */
	public function __construct(CacheItemPoolInterface $cache, int $ttl)
	{
		$this->ttl   = $ttl;
		$this->cache = $cache;
	}


	/**
	 * {@inheritdoc}
	 */
	public function open($path, $name): bool
	{
		return TRUE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function close(): bool
	{
		return TRUE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function read($id): string
	{
		$item = $this->getCacheItem($id);
		if ($item->isHit()) {
			return $item->get();
		}

		return '';
	}


	/**
	 * {@inheritdoc}
	 */
	public function write($id, $data): bool
	{
		$item = $this->getCacheItem($id);

		$item->set($data)->expiresAfter($this->ttl);

		return $this->cache->save($item);
	}


	/**
	 * {@inheritdoc}
	 */
	public function destroy($id): bool
	{
		return $this->cache->deleteItem($id);
	}


	/**
	 * Overload the gc function to restore basic garbage collection
	 *
	 * @return int
	 */
	public function gc($lifetime): int
	{
		$count = 0;

		foreach ($this->cache->getItems() as $key => $item) {
			if (!$item->isHit()) {
				continue;
			}

			if (!$item->getExpiration()) {
				continue;
			}

			if ($item->getExpiration() <= new DateTime()) {
				$this->cache->deleteItem($key);
				$count++;
			}
		}

		return $count;
	}


	/**
	 * @return CacheItemInterface
	 */
	private function getCacheItem(string $id)
	{
		return $this->cache->getItem($id);
	}
}
