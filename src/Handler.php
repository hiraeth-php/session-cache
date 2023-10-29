<?php

namespace Hiraeth\Stash\Session;

use SessionHandlerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Stash\Pool;

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
	 * @var Pool
	 */
	private $cache;


	/**
	 * @var int Time to live in seconds
	 */
	private $ttl;


	/**
	 * @var string Key prefix for shared environments.
	 */
	private $prefix;


	/**
	 * @param Pool $cache
	 * @param array<string, mixed> $options An array of options including 'ttl' and 'prefix
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
		$this->cache->purge();

		//
		// There is currently no way to get the number of deleted items which newer gc should
		// return.  That said, we're not solidly worried about performance, so we'll just send
		// a random number.  Improvements in stash would be good.
		//

		return rand(0, 100);
	}


	/**
	 * @return \Psr\Cache\CacheItemInterface
	 */
	private function getCacheItem(string $id)
	{
		return $this->cache->getItem($id);
	}
}
