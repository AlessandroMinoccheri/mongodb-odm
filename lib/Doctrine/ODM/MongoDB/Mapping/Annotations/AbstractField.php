<?php

declare(strict_types=1);

namespace Doctrine\ODM\MongoDB\Mapping\Annotations;

use Doctrine\Common\Annotations\Annotation;
use function get_class;
use function sprintf;

abstract class AbstractField extends Annotation
{
    public $name;
    public $type = 'string';
    public $nullable = false;
    public $options = [];
    public $strategy;

    /**
     * Gets deprecation message. The method *WILL* be removed in 2.0.
     *
     * @return string
     *
     * @internal
     */
    public function getDeprecationMessage()
    {
        return sprintf('%s will be removed in ODM 2.0. Use `@ODM\Field(type="%s")` instead.', get_class($this), $this->type);
    }

    /**
     * Gets whether the annotation is deprecated. The method *WILL* be removed in 2.0.
     *
     * @return bool
     *
     * @internal
     */
    public function isDeprecated()
    {
        return false;
    }
}
