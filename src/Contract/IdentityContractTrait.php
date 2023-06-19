<?php

declare(strict_types=1);

namespace App\Contract;

use App\Entity\Identity;
use App\Exception\InternalException;
use App\Exception\JsonWebTokenException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Cache\ItemInterface;

use function in_array;
use function property_exists;
use function sprintf;

/**
 * Trait IdentityContractTrait
 * @package App\Contract
 *
 * @author Kamal Chahid <kchahid_@outlook.com>
 */
trait IdentityContractTrait
{
    /*
     * Get identity from cache, otherwise fetch it from database and store it in the cache contract adapter
     *
     * Since it's a simple app, no need to set an external cache
     * For more performance, use solution like redis, memcached, couchbase, ...
     */
    private function getIdentityByIssuer(?string $issuer, EntityManagerInterface $em, LoggerInterface $logger): Identity
    {
        if (empty($issuer)) {
            $logger->debug(sprintf('Request object is not declared. Did you forget to inject %s', Request::class));
            throw new JsonWebTokenException(JsonWebTokenException::ISSUER);
        }
        if (!property_exists($this, 'request')) {
            $logger->debug(sprintf('Request object is not declared. Did you forget to inject %s', Request::class));
            throw new InternalException('request property is not declared');
        }

        if (!property_exists($this, 'cache')) {
            $logger->debug(sprintf('Cache object is not declared. Did you forget to inject %s', FilesystemAdapter::class));
            throw new InternalException('cache property is not declared');
        }

        if (!$this->cache instanceof FilesystemAdapter) {
            $logger->debug(sprintf('Cache object is not valid. Did you inject %s', FilesystemAdapter::class));
            throw new InternalException(sprintf('Cache is not instance of %s', FilesystemAdapter::class));
        }
        return $this->getIdentity($issuer, $em, $logger);
    }

    private function getIdentity(
        string $identifier,
        EntityManagerInterface $em,
        LoggerInterface $logger,
        bool $forBasic = false
    ): Identity {
        /** @var null|Identity $identity */
        $identity =  $this->cache->get(sprintf('%sIdentity', $identifier), function (ItemInterface $item) use ($forBasic, $identifier, $em, $logger): ?Identity {
            $logger->info('Identity not found in cache. Processing....');
            $item->expiresAfter(604800);

            /** @var Identity|null $identity */
            $identity = $em->getRepository(Identity::class)
                ->findOneBy(match ($forBasic) {
                    true => ['basicKey' => $identifier],
                    default => ['issuer' => $identifier]
                });

            $logger->info('Identity was fetch from database and cached');
            return $identity;
        }, 1.0);

        if ($identity === null) {
            $logger->notice('Cannot get identity from database.');
            throw new InternalException('Cannot fetch identity from database');
        }

        if ($identity->getStatus() === false) {
            $logger->info('Identity is not authorized to perform the action.');
            throw new InternalException('Identity is disabled');
        }
        $env = $this->request->server->get('APP_ENV');
        if (in_array($env, $identity->getAllowedEnv(), true) === false) {
            $logger->debug(sprintf('Identity is not authorized to perform the action in %s', $env));
            throw new JsonWebTokenException(JsonWebTokenException::SUBJECT);
        }
        return $identity;
    }
}
