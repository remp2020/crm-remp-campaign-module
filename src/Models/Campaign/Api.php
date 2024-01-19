<?php

namespace Crm\RempCampaignModule\Models\Campaign;

use Crm\ApplicationModule\Models\NowTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Nette\Http\IResponse;
use Nette\Utils\Json;
use Tracy\Debugger;
use Tracy\ILogger;

class Api
{
    use NowTrait;

    public const BANNERS = 'api/banners';

    private $client;

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

    public static function segmentCacheAddUserUriPath(string $segmentCode): string
    {
        return "api/segment-cache/provider/crm_segment/code/{$segmentCode}/add-user";
    }

    public static function segmentCacheRemoveUserUriPath(string $segmentCode): string
    {
        return "api/segment-cache/provider/crm_segment/code/{$segmentCode}/remove-user";
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

    public function segmentCacheAddUser(int $userId, string $segmentCode): bool
    {
        try {
            $this->client->post(self::segmentCacheAddUserUriPath($segmentCode), [
                'json' => [
                    'user_id' => $userId,
                    'segment_code' => $segmentCode,
                ],
            ]);
            return true;
        } catch (ClientException $e) {
            if ($e->getResponse() === null) {
                Debugger::log($e, ILogger::ERROR);
                return false;
            }

            if ($e->getResponse()->getStatusCode() === IResponse::S404_NOT_FOUND) {
                // no campaign is active with this segment in this moment; segment's cache will reload on activation
                return false;
            }

            Debugger::log($e->getMessage(), ILogger::ERROR);
            Debugger::log($e->getResponse()->getBody()->getContents(), ILogger::INFO);
            return false;
        }
    }

    public function segmentCacheRemoveUser(int $userId, string $segmentCode): bool
    {
        try {
            $this->client->post(self::segmentCacheRemoveUserUriPath($segmentCode), [
                'json' => [
                    'user_id' => $userId,
                    'segment_code' => $segmentCode,
                ],
            ]);
            return true;
        } catch (ClientException $e) {
            if ($e->getResponse() === null) {
                Debugger::log($e, ILogger::ERROR);
                return false;
            }

            if ($e->getResponse()->getStatusCode() === IResponse::S404_NOT_FOUND) {
                // no campaign is active with this segment in this moment; segment's cache will reload on activation
                return false;
            }

            Debugger::log($e->getMessage(), ILogger::ERROR);
            Debugger::log($e->getResponse()->getBody()->getContents(), ILogger::INFO);
            return false;
        }
    }
}
