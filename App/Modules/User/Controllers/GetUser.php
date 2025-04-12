<?php

declare(strict_types=1);

namespace App\Modules\User\Controllers;

use App\Core\Http\AbstractController;
use App\Core\Request;
use App\Exceptions\NotFoundException;
use App\Modules\User\Models\User;

class GetUser extends AbstractController
{
    /**
     * @throws \ReflectionException
     * @throws \App\Exceptions\NotFoundException
     */
    public function __invoke(Request $request): User
    {
        $params = $request->route();
        $id = $params->id;

        $user = User::find($id);

        if ($user === null) {
            throw new NotFoundException('The requested user was not found', [
                'resource' => 'user',
                'id' => $id,
            ]);
        }

        return $user;
    }
}