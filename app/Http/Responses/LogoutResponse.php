<?php

namespace App\Http\Responses;

use Inertia\Inertia;
use Laravel\Fortify\Http\Responses\LogoutResponse as FortifyLogoutResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class LogoutResponse extends FortifyLogoutResponse
{
    public function toResponse($request): Response
    {
        $response = parent::toResponse($request);

        return $response instanceof RedirectResponse
            ? Inertia::location($response)
            : $response;
    }
}
