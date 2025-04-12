<?php

namespace App\Modules\User\Models;

use App\Core\Database\Field;
use App\Core\Database\Model;
use App\Modules\User\Services\SensitiveDataService;

class User extends Model
{
    public function __construct(
        #[Field]
        protected string $userDocument,

        #[Field]
        protected string $creditCardToken,

        #[Field]
        protected int $value,

        #[Field]
        protected readonly ?int $id = null,
    )
    {
        $this->setUserDocument($userDocument);
        $this->setCreditCardToken($creditCardToken);

        parent::__construct();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserDocument(): string
    {
        $sensitiveDataService = new SensitiveDataService();

        return $sensitiveDataService->decrypt($this->userDocument);
    }

    public function setUserDocument(string $userDocument): User
    {
        $sensitiveDataService = new SensitiveDataService();

        $this->userDocument = $sensitiveDataService->encrypt($userDocument);

        return $this;
    }

    public function getCreditCardToken(): string
    {
        $sensitiveDataService = new SensitiveDataService();

        return $sensitiveDataService->decrypt($this->creditCardToken);
    }

    public function setCreditCardToken(string $creditCardToken): User
    {
        $sensitiveDataService = new SensitiveDataService();

        $this->creditCardToken = $sensitiveDataService->encrypt($creditCardToken);
        return $this;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): User
    {
        $this->value = $value;
        return $this;
    }
}