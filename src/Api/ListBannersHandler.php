<?php

namespace Crm\RempCampaignModule\Api;

use Crm\ApiModule\Models\Api\ApiHandler;
use Crm\RempCampaignModule\Models\Campaign\Api;
use Nette\Http\Response;
use Tomaj\NetteApi\Response\JsonApiResponse;
use Tomaj\NetteApi\Response\ResponseInterface;

class ListBannersHandler extends ApiHandler
{
    private $campaignApi;

    public function __construct(Api $campaignApi)
    {
        $this->campaignApi = $campaignApi;
    }

    public function params(): array
    {
        return [];
    }


    public function handle(array $params): ResponseInterface
    {
        $banners = [];
        foreach ($this->campaignApi->listBanners() as $banner) {
            $banners[] = [
                'id' => $banner->id,
                'name' => $banner->name,
            ];
        }
        $response = new JsonApiResponse(Response::S200_OK, [
            'status' => 'ok',
            'banners' => $banners,
        ]);
        return $response;
    }
}
