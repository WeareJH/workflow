<?php

namespace Jh\Workflow\Commands;

/**
 * @author Michael Woodward <michael@wearejh.com>
 */
class MagentoConfigure implements CommandInterface
{
    use DockerAware;

    public function __invoke(array $arguments)
    {
        $phpContainer  = $this->phpContainerName();
        $mailContainer = $this->getContainerName('mail');

        system("docker exec $phpContainer magento-configure");

        (new Pull)(['app/etc/env.php']);

        if ('prod' === array_shift($arguments)) {
            return;
        }

        $sql =  "DELETE FROM core_config_data WHERE path LIKE 'system/smtp/%'; ";
        $sql .= "INSERT INTO core_config_data (scope, scope_id, path, value) ";
        $sql .= "VALUES ";
        $sql .= "('default', 0, 'system/smtp/host', '$mailContainer'), ";
        $sql .= "('default', 0, 'system/smtp/port', '1025');";

        (new Sql)([$sql]);
    }

    public function getHelpText(): string
    {
        return <<<HELP
Adds 

  - Redis configuration for sessions, frontend cache and full page cache to the magento env.php file
  - Mailcatcher configuration ready for development

Pass argument prod to ommit the mailcatcher configuration
HELP;
    }
}
