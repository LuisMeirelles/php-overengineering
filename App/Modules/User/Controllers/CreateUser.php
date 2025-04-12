<?php

declare(strict_types=1);

namespace App\Modules\User\Controllers;

use App\Core\Http\AbstractController;
use App\Core\Request;
use App\Modules\User\Models\User;

class CreateUser extends AbstractController
{
    /**
     * @throws \JsonException
     */
    public function __invoke(Request $request): void
    {
        $params = $request->body();

        $userDocument = $params->userDocument;
        $creditCardToken = $params->creditCardToken;
        $value = $params->value;

        $userInfo = new User(
            $userDocument,
            $creditCardToken,
            $value,
        );

        $userInfo->save();

        http_response_code(201);
    }
}