<?php

namespace Omnipay\MyCash\Tests;

use Omnipay\MyCash\MyCashGateway;
use Omnipay\Tests\GatewayTestCase;

class GatewayTest extends GatewayTestCase
{
    /** @var MyCashGateway */
    protected $gateway;

    public function setUp(): void
    {
        parent::setUp();

        $this->gateway = new MyCashGateway($this->getHttpClient(), $this->getHttpRequest());

        $this->options = [
            'amount' => '10.00',
            'card' => $this->getValidCard(),
        ];
    }

    public function testAuthorize()
    {
        $this->setMockHttpResponse('AuthorizeSuccess.txt');

        $response = $this->gateway->authorize($this->options)->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('1234', $response->getTransactionReference());
        $this->assertNull($response->getMessage());
    }
}
