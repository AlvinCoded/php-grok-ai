<?php

declare(strict_types=1);

namespace Tests\Unit;

use GrokPHP\Client\GrokClient;
use GrokPHP\Config;
use GrokPHP\Endpoints\Chat;
use GrokPHP\Endpoints\Completions;
use GrokPHP\Endpoints\Images;
use GrokPHP\Exceptions\GrokException;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    private string $apiKey = 'test-api-key';
    private GrokClient $client;

    protected function setUp(): void
    {
        $this->client = new GrokClient($this->apiKey);
    }

    public function testClientInitialization(): void
    {
        $this->assertInstanceOf(GrokClient::class, $this->client);
    }

    public function testClientThrowsExceptionWithEmptyApiKey(): void
    {
        $this->expectException(GrokException::class);
        $this->expectExceptionMessage('The API key is required');
        new GrokClient('');
    }

    public function testChatEndpointInitialization(): void
    {
        $chat = $this->client->chat();
        $this->assertInstanceOf(Chat::class, $chat);
    }

    public function testCompletionsEndpointInitialization(): void
    {
        $completions = $this->client->completions();
        $this->assertInstanceOf(Completions::class, $completions);
    }

    public function testImagesEndpointInitialization(): void
    {
        $images = $this->client->images();
        $this->assertInstanceOf(Images::class, $images);
    }

    public function testCustomBaseUrl(): void
    {
        $customUrl = 'https://jsonplaceholder.typicode.com/todos/1';
        $this->client->setBaseUrl($customUrl);
        
        $config = $this->client->getConfig();
        $this->assertEquals($customUrl, $config->getBaseUrl());
    }

    public function testApiVersionManagement(): void
    {
        $newVersion = 'v2';
        $this->client->setApiVersion($newVersion);
        $this->assertEquals($newVersion, $this->client->getApiVersion());
    }

    public function testConfigurationOptions(): void
    {
        $options = [
            'timeout' => 60,
            'max_retries' => 5,
        ];

        $client = new GrokClient($this->apiKey, $options);
        $config = $client->getConfig();

        $this->assertEquals(60, $config->get('timeout'));
        $this->assertEquals(5, $config->get('max_retries'));
    }

    public function testDefaultConfigurationValues(): void
    {
        $config = $this->client->getConfig();
        
        $this->assertEquals(30, $config->get('timeout'));
        $this->assertEquals(10, $config->get('connect_timeout'));
        $this->assertEquals(3, $config->get('max_retries'));
    }

    public function testModelConfigurationAccess(): void
    {
        $config = $this->client->getConfig();
        
        $this->assertTrue($config->modelSupportsStreaming('grok-2-1212'));
        $this->assertEquals(128000, $config->getModelMaxTokens('grok-2-1212'));
    }

    public function testInvalidModelConfiguration(): void
    {
        $this->expectException(GrokException::class);
        $this->client->getConfig()->getModelConfig('invalid-model');
    }
}
