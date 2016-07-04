<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Freemium\Repository;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

class SubscriptionRepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->markTestSkipped(
            'The pdo mysql extension is not available.'
        );
        if (!extension_loaded('pdo_mysql')) {
            $this->markTestSkipped(
                'The pdo mysql extension is not available.'
            );
        } else {
            $this->setUpEntityManager();
        }
    }

    public function testFindBillable()
    {
        $repo = $this->em->getRepository('Model\Subscription');

        $query = $repo->findBillable();

        $this->assertEquals(
            'SELECT s0_.id AS id_0, s0_.paid_through AS paid_through_1, s0_.started_on AS started_on_2, s0_.billing_key AS billing_key_3, s0_.last_transaction_at AS last_transaction_at_4, s0_.in_trial AS in_trial_5, s0_.expire_on AS expire_on_6, s0_.rate AS rate_7, s0_.subscription_plan_id AS subscription_plan_id_8, s0_.subscribable_id AS subscribable_id_9 FROM subscriptions s0_ WHERE s0_.paid_through <= ? AND s0_.rate > ?',
            $query->getSql()
        );
    }

    public function testFindExpired()
    {
        $repo = $this->em->getRepository('Model\Subscription');

        $query = $repo->findExpired();

        $this->assertEquals(
            'SELECT s0_.id AS id_0, s0_.paid_through AS paid_through_1, s0_.started_on AS started_on_2, s0_.billing_key AS billing_key_3, s0_.last_transaction_at AS last_transaction_at_4, s0_.in_trial AS in_trial_5, s0_.expire_on AS expire_on_6, s0_.rate AS rate_7, s0_.subscription_plan_id AS subscription_plan_id_8, s0_.subscribable_id AS subscribable_id_9 FROM subscriptions s0_ WHERE s0_.expire_on >= s0_.paid_through AND s0_.expire_on > ?',
            $query->getSql()
        );
    }

    private function setUpEntityManager()
    {
        $params = include __DIR__ . '/../../../config/parameters.php';
        $paths = array(__DIR__ . '/../../../src/Model', __DIR__ . '/../../../config/metadata');
        $isDevMode = true;

        // the connection configuration
        $dbParams = array(
            'driver'   => 'pdo_mysql',
            'user'     => $params['database']['test']['username'],
            'password' => $params['database']['test']['password'],
            'dbname'   => $params['database']['test']['database'],
        );

        $config = Setup::createYAMLMetadataConfiguration($paths, $isDevMode);
        $this->em = EntityManager::create($dbParams, $config);
    }
}
