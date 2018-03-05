<?php

declare(strict_types=1);

namespace Doctrine\ODM\MongoDB\Mapping\Annotations;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
final class ReadPreference extends Annotation
{
    /**
     * @var array|null
     */
    public $tags;
}
