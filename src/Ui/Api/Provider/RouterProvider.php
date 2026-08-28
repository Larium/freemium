<?php

declare(strict_types=1);

namespace Larium\Ui\Api\Provider;

use FastRoute\RouteCollector;
use Larium\Ui\Api\Action\Billing\ApplyCouponAction;
use Larium\Ui\Api\Action\Billing\ChangePlanAction;
use Larium\Ui\Api\Action\Billing\CreateSubscriptionAction;
use Larium\Ui\Api\Action\Billing\GetPlanAction;
use Larium\Ui\Api\Action\Billing\GetPlansAction;
use Larium\Ui\Api\Action\Billing\GetSubscriptionAction;
use Larium\Ui\Api\Action\Billing\StorePaymentMethodAction;
use Larium\Ui\Api\Action\HomeAction;

class RouterProvider
{
    public function register(RouteCollector $r): void
    {
        $r->get('/', HomeAction::class);

        $r->post('/subscriptions', CreateSubscriptionAction::class);
        $r->get('/subscriptions/{id}', GetSubscriptionAction::class);
        $r->patch('/subscriptions/{id}', ChangePlanAction::class);
        $r->post('/subscriptions/{id}/coupon', ApplyCouponAction::class);
        $r->post('/customers/{id}/payment-method', StorePaymentMethodAction::class);
        $r->get('/plans', GetPlansAction::class);
        $r->get('/plans/{name}', GetPlanAction::class);
    }
}
