<?php

namespace AiBundle\Prompting\MessageStore;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Uid\Uuid;

class Psr6CacheMessageStore implements MessageStoreInterface {

  public function __construct(
    private readonly CacheItemPoolInterface $cache,
    private readonly ?int $ttl = null,
    private readonly ?string $keyPrefix = ''
  ) {}

  /**
   * @inheritDoc
   */
  public function store(Uuid $uid, array $messages): void {
    $item = $this->cache->getItem($this->keyPrefix.$uid->toRfc4122());
    $item->set($messages);
    if ($this->ttl !== null) {
      $item->expiresAfter($this->ttl);
    }
    $this->cache->save($item);
  }

  /**
   * @inheritDoc
   */
  public function retrieve(Uuid $uid): array {
    $item = $this->cache->getItem($this->keyPrefix.$uid->toRfc4122());
    return $item->isHit() ? $item->get() : [];
  }

  /**
   * @inheritDoc
   */
  public function delete(Uuid $uid): void {
    $this->cache->deleteItem($this->keyPrefix.$uid->toRfc4122());
  }

}
