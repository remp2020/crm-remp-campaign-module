<?php

namespace Crm\RempCampaignModule\Models\Campaign;

use Crm\ApplicationModule\Tests\NowTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Nette\Utils\Json;
use Tracy\Debugger;
use Tracy\ILogger;

class Api
{
    use NowTrait;

    const BANNERS = 'api/banners';

    private $client;

    private $now; // signalizing there is $now variable when using NowTrait

    public function __construct($campaignHost, $apiToken)
    {
        $this->client = new Client([
            'base_uri' => $campaignHost,
            'headers' => [
                'Authorization' => 'Bearer ' . $apiToken,
                'Accept' => 'application/json',
            ]
        ]);
    }

    /**
     * Replace request client, useful in tests
     * @param Client $client
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    public static function showOneTimeBannerUriPath($bannerId)
    {
        return "api/banners/$bannerId/one-time-display";
    }

    /**
     * Useful for testing
     * @param \DateTime $now
     */
    public function setNow(\DateTime $now)
    {
        $this->now = $now;
    }

    public function showOneTimeBanner($userId, $bannerId, $expiresInMinutes): bool
    {
        try {
            $now = $this->getNow();
            $expiresAt = $now->add(new \DateInterval("PT{$expiresInMinutes}M"));

            $payload = [
                'user_id' => (string) $userId,
                'expires_at' => $expiresAt->format(DATE_RFC3339)
            ];

            $this->client->post(self::showOneTimeBannerUriPath($bannerId), [
                'json' => $payload,
            ]);
            return true;
        } catch (ClientException $e) {
            Debugger::log($e->getMessage(), ILogger::ERROR);
            Debugger::log($e->getResponse()->getBody()->getContents(), ILogger::INFO);
            return false;
        }
    }

    public function listBanners(): array
    {
        try {
            $response = $this->client->get(self::BANNERS, [
                'query' => [
                    // TODO currently we do not expect for campaign to have more than 10 000 banners
                    'perPage' => 10000,
                ],
            ]);
            return Json::decode($response->getBody()->getContents())->data;
        } catch (ClientException $e) {
            Debugger::log($e->getMessage(), ILogger::ERROR);
            Debugger::log($e->getResponse()->getBody()->getContents(), ILogger::INFO);
            return [];
        }
    }
}
