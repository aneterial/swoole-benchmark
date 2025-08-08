<?php

declare(strict_types=1);

namespace App\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class Metrics extends AbstractAnnotation
{
}
