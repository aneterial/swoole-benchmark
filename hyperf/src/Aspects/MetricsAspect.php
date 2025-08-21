<?php

declare(strict_types=1);

namespace App\Aspects;

use App\Annotation\Metrics as MetricsAnnotation;
use App\Utils\Metrics;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

#[Aspect]
final class MetricsAspect extends AbstractAspect
{
    #[Inject]
    private Metrics $metrics;

    public array $annotations = [
        MetricsAnnotation::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint): mixed
    {
        $result = $proceedingJoinPoint->process();

        $this->metrics->save(Metrics::MEMORY_PROCESS, memory_get_usage(true));

        return $result;
    }
}
