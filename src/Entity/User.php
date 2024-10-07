<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enum\UserRole;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfiguration;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfigurationInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface, TwoFactorInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 255, nullable: false)]
    private string $username;

    #[ORM\Column(length: 255, nullable: false)]
    private string $email;

    #[ORM\Column(length: 255, nullable: false)]
    private string $password;

    #[ORM\Column(type: UserRole::class, nullable: false)]
    private UserRole $role = UserRole::USER;

    #[ORM\Column(nullable: false, options: ['default' => true])]
    private bool $enabled = true;

    #[ORM\Column(nullable: false)]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $resetPasswordToken = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $passwordChangedAt = null;

    #[ORM\Column(nullable: false, options: ['default' => true])]
    private bool $enforceResetPassword = true;

    #[ORM\Column(nullable: false, options: ['default' => true])]
    private bool $enforceTotpAuth = true;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $TotpToken = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getRole(): UserRole
    {
        return $this->role;
    }

    public function setRole(UserRole $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getResetPasswordToken(): ?string
    {
        return $this->resetPasswordToken;
    }

    public function setResetPasswordToken(?string $resetPasswordToken): static
    {
        $this->resetPasswordToken = $resetPasswordToken;

        return $this;
    }

    public function getPasswordChangedAt(): ?DateTimeImmutable
    {
        return $this->passwordChangedAt;
    }

    public function setPasswordChangedAt(?DateTimeImmutable $passwordChangedAt): static
    {
        $this->passwordChangedAt = $passwordChangedAt;

        return $this;
    }

    public function isEnforceResetPassword(): bool
    {
        return $this->enforceResetPassword;
    }

    public function setEnforceResetPassword(bool $enforceResetPassword): static
    {
        $this->enforceResetPassword = $enforceResetPassword;

        return $this;
    }

    public function isEnforceTotpAuth(): bool
    {
        return $this->enforceTotpAuth;
    }

    public function setEnforceTotpAuth(bool $enforceTotpAuth): static
    {
        $this->enforceTotpAuth = $enforceTotpAuth;

        return $this;
    }

    public function getTotpToken(): ?string
    {
        return $this->TotpToken;
    }

    public function setTotpToken(?string $totpToken): static
    {
        $this->TotpToken = $totpToken;

        return $this;
    }



    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    /**
     * @return array<string>
     */
    public function getRoles(): array
    {
        // You can return a default role or an empty array if roles are not used
        return [$this->getRole()->value];
    }

    public function eraseCredentials(): void
    {
        // You can leave this method empty if you do not need to clear sensitive data
    }

    public function isTotpAuthenticationEnabled(): bool
    {
        return $this->TotpToken;
    }

    public function getTotpAuthenticationUsername(): string
    {
        return $this->username;
    }

    public function getTotpAuthenticationConfiguration(): ?TotpConfigurationInterface
    {
        if ($this->TotpToken) {
            return new TotpConfiguration($this->TotpToken, TotpConfiguration::ALGORITHM_SHA1, 30, 6);
        }
        return null;
    }
}
