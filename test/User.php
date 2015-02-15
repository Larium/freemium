<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

use Freemium\SubscribableInterface;
use Freemium\AbstractEntity;

class User extends AbstractEntity implements SubscribableInterface
{
    protected $name;

    protected $email;
}
