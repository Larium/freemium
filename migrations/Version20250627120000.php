<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250627120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial billing schema';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE customers (
            customer_id VARCHAR(255) NOT NULL,
            billing_key VARCHAR(255) DEFAULT NULL,
            PRIMARY KEY(customer_id)
        )');

        $this->addSql('CREATE TABLE subscription_plans (
            token VARCHAR(255) NOT NULL,
            period INT NOT NULL,
            frequency INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            trial_days INT NOT NULL,
            grace_days INT NOT NULL,
            rate_amount VARCHAR(255) NOT NULL,
            rate_currency VARCHAR(3) NOT NULL,
            PRIMARY KEY(token),
            UNIQUE(name)
        )');

        $this->addSql('CREATE TABLE subscriptions (
            token VARCHAR(255) NOT NULL,
            customer_id VARCHAR(255) NOT NULL,
            subscription_plan_token VARCHAR(255) NOT NULL,
            in_trial BOOLEAN NOT NULL,
            trial_started_on DATE DEFAULT NULL,
            trial_ends_on DATE DEFAULT NULL,
            grace_started_on DATE DEFAULT NULL,
            grace_ends_on DATE DEFAULT NULL,
            paid_through DATE DEFAULT NULL,
            started_on DATE NOT NULL,
            last_transaction_at DATE DEFAULT NULL,
            cancel_at DATE DEFAULT NULL,
            status VARCHAR(32) NOT NULL,
            rate_amount VARCHAR(255) NOT NULL,
            rate_currency VARCHAR(3) NOT NULL,
            PRIMARY KEY(token),
            CONSTRAINT fk_subscriptions_customer FOREIGN KEY (customer_id) REFERENCES customers (customer_id),
            CONSTRAINT fk_subscriptions_plan FOREIGN KEY (subscription_plan_token) REFERENCES subscription_plans (token)
        )');

        $this->addSql('CREATE TABLE subscription_changes (
            id SERIAL NOT NULL,
            subscription_token VARCHAR(255) NOT NULL,
            reason INT NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            original_plan_token VARCHAR(255) DEFAULT NULL,
            new_plan_token VARCHAR(255) NOT NULL,
            original_rate_amount VARCHAR(255) NOT NULL,
            original_rate_currency VARCHAR(3) NOT NULL,
            new_rate_amount VARCHAR(255) NOT NULL,
            new_rate_currency VARCHAR(3) NOT NULL,
            PRIMARY KEY(id),
            CONSTRAINT fk_subscription_changes_subscription FOREIGN KEY (subscription_token) REFERENCES subscriptions (token),
            CONSTRAINT fk_subscription_changes_original_plan FOREIGN KEY (original_plan_token) REFERENCES subscription_plans (token),
            CONSTRAINT fk_subscription_changes_new_plan FOREIGN KEY (new_plan_token) REFERENCES subscription_plans (token)
        )');

        $this->addSql('CREATE TABLE coupons (
            token VARCHAR(255) NOT NULL,
            description TEXT DEFAULT NULL,
            redemption_key VARCHAR(255) NOT NULL,
            redemption_limit INT DEFAULT NULL,
            redemption_expiration DATE DEFAULT NULL,
            duration_in_months INT DEFAULT NULL,
            discount_rate INT NOT NULL,
            discount_type INT NOT NULL,
            PRIMARY KEY(token),
            UNIQUE(redemption_key)
        )');

        $this->addSql('CREATE TABLE coupon_plans (
            coupon_token VARCHAR(255) NOT NULL,
            plan_token VARCHAR(255) NOT NULL,
            PRIMARY KEY(coupon_token, plan_token),
            CONSTRAINT fk_coupon_plans_coupon FOREIGN KEY (coupon_token) REFERENCES coupons (token),
            CONSTRAINT fk_coupon_plans_plan FOREIGN KEY (plan_token) REFERENCES subscription_plans (token)
        )');

        $this->addSql('CREATE TABLE coupon_redemptions (
            token VARCHAR(255) NOT NULL,
            subscription_token VARCHAR(255) NOT NULL,
            coupon_token VARCHAR(255) NOT NULL,
            redeemed_on DATE NOT NULL,
            expired_on DATE DEFAULT NULL,
            PRIMARY KEY(token),
            CONSTRAINT fk_coupon_redemptions_subscription FOREIGN KEY (subscription_token) REFERENCES subscriptions (token),
            CONSTRAINT fk_coupon_redemptions_coupon FOREIGN KEY (coupon_token) REFERENCES coupons (token)
        )');

        $this->addSql('CREATE TABLE transactions (
            token VARCHAR(255) NOT NULL,
            subscription_token VARCHAR(255) DEFAULT NULL,
            success BOOLEAN DEFAULT NULL,
            message TEXT DEFAULT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            gateway_transaction_id VARCHAR(255) DEFAULT NULL,
            idempotency_key VARCHAR(255) DEFAULT NULL,
            amount_amount VARCHAR(255) NOT NULL,
            amount_currency VARCHAR(3) NOT NULL,
            PRIMARY KEY(token),
            UNIQUE(idempotency_key),
            CONSTRAINT fk_transactions_subscription FOREIGN KEY (subscription_token) REFERENCES subscriptions (token)
        )');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE transactions');
        $this->addSql('DROP TABLE coupon_redemptions');
        $this->addSql('DROP TABLE coupon_plans');
        $this->addSql('DROP TABLE coupons');
        $this->addSql('DROP TABLE subscription_changes');
        $this->addSql('DROP TABLE subscriptions');
        $this->addSql('DROP TABLE subscription_plans');
        $this->addSql('DROP TABLE customers');
    }
}
