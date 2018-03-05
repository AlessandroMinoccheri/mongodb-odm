<?php

declare(strict_types=1);

namespace Doctrine\ODM\MongoDB\Mapping\Annotations;

use Doctrine\Common\Annotations\Annotation;

abstract class AbstractIndex extends Annotation
{
    public $keys = [];
    public $name;
    public $dropDups;
    public $background;
    public $expireAfterSeconds;
    public $order;
    public $unique = false;
    public $sparse = false;
    public $options = [];
    public $partialFilterExpression = [];
}
