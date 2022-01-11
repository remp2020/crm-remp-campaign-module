<?php

namespace Crm\RempCampaignModule\Api;

use Crm\ApiModule\Api\ApiHandler;
use Crm\ApiModule\Api\JsonResponse;
use Crm\ApiModule\Authorization\ApiAuthorizationInterface;
use Crm\RempCampaignModule\Models\Campaign\Api;
use Nette\Http\Response;

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

    /**
     * @param ApiAuthorizationInterface $authorization
     * @return \Nette\Application\Response
     */
    public function handle(ApiAuthorizationInterface $authorization)
    {
        $banners = [];
        foreach ($this->campaignApi->listBanners() as $banner) {
            $banners[] = [
                'id' => $banner->id,
                'name' => $banner->name,
            ];
        }
        $response = new JsonResponse([
            'status' => 'ok',
            'banners' => $banners,
        ]);
        $response->setHttpCode(Response::S200_OK);
        return $response;
    }
}
