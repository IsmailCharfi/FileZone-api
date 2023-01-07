<?php

namespace App\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
abstract class AbstractEntity
{
    use TimestampableEntity, SoftDeleteableEntity;

    abstract function export(): array;
}
