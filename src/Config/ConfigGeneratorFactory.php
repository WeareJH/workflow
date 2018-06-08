<?php declare(strict_types=1);

namespace Jh\Workflow\Config;

use Jh\Workflow\Platform;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class ConfigGeneratorFactory
{
    /**
     * @var M1ConfigGenerator
     */
    private $m1ConfigGenerator;

    /**
     * @var M2ConfigGenerator
     */
    private $m2ConfigGenerator;

    public function __construct(M1ConfigGenerator $m1ConfigGenerator, M2ConfigGenerator $m2ConfigGenerator)
    {
        $this->m1ConfigGenerator = $m1ConfigGenerator;
        $this->m2ConfigGenerator = $m2ConfigGenerator;
    }

    public function create(Platform $platform) : ConfigGeneratorInterface
    {
        switch ($platform) {
            case Platform::M1():
                return $this->m1ConfigGenerator;
                break;
            case Platform::M2():
            default:
            return $this->m2ConfigGenerator;
                break;
        }
    }
}
