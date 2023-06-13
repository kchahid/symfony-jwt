<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\IdentityRepository;
use Carbon\Carbon;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

/**
 * Class Identity
 * @package App\Entity
 */
#[ORM\Entity(repositoryClass: IdentityRepository::class)]
class Identity
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(length: 10)]
    private ?string $issuer = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private array $allowedEnv = [];

    #[ORM\Column(length: 100)]
    private ?string $secret = null;

    #[ORM\Column]
    private ?Carbon $createdAt = null;

    #[ORM\Column]
    private ?bool $status = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getIssuer(): ?string
    {
        return $this->issuer;
    }

    public function setIssuer(?string $issuer): Identity
    {
        $this->issuer = $issuer;
        return $this;
    }

    /**
     * @return array<string>
     */
    public function getAllowedEnv(): array
    {
        return $this->allowedEnv;
    }

    /**
     * @param array<string> $allowedEnv
     */
    public function setAllowedEnv(array $allowedEnv): Identity
    {
        $this->allowedEnv = $allowedEnv;
        return $this;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(?string $secret): Identity
    {
        $this->secret = $secret;
        return $this;
    }

    public function getCreatedAt(): ?Carbon
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?Carbon $createdAt): Identity
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(?bool $status): Identity
    {
        $this->status = $status;
        return $this;
    }
}
