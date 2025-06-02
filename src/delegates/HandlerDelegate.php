<?php

namespace Hiraeth\Session;

use Hiraeth;
use Hiraeth\Caching;

/**
 *
 */
class HandlerDelegate implements Hiraeth\Delegate
{
	/**
	 * @var Caching\PoolManager
	 */
	protected $manager;


	/**
	 * {@inheritDoc}
	 */
	static public function getClass(): string
	{
		return Handler::class;
	}


	/**
	 *
	 */
	public function __construct(Caching\PoolManager $manager)
	{
		$this->manager = $manager;
	}


	/**
	 * {@inheritDoc}
	 */
	public function __invoke(Hiraeth\Application $app): object
	{
		$pool = $this->manager->get('session');

		if (!$ttl = $app->getEnvironment('SESSION_TTL', ini_get('session.gc_maxlifetime'))) {
			//
			// Check if we're using browser based session
			//

			$ttl = $app->getEnvironment('SESSION_GCL', ini_get('session.gc_maxlifetime'));
		}

		return new Handler($pool, $ttl);
	}
}
