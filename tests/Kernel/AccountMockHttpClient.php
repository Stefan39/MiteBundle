<?php

declare(strict_types=1);

namespace Stejac\MiteBundle\Tests\Kernel;

use Stejac\MiteBundle\Tests\Fixtures;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class AccountMockHttpClient extends MockHttpClient
{
    public function __construct()
    {
        parent::__construct(new MockResponse(json_encode(['account' => Fixtures::account()], \JSON_THROW_ON_ERROR)));
    }
}
