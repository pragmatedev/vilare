<?php

namespace Vilare\Core;

class Hooks
{
    private const PATTERN =
        '#\* @(?P<type>filter|action|shortcode)\s+(?P<name>[a-z0-9\-\.\/_]+)(\s+(?P<priority>\d+))?#';

    public static function init(object $instance): object
    {
        foreach (self::extract($instance) as $config) {
            call_user_func($config['hook'], $config['name'], $config['callback'], $config['priority'], $config['args']);
        }

        return $instance;
    }

    private static function extract(object $instance): array
    {
        $handlers = [];

        $reflector = new \ReflectionObject($instance);

        foreach ($reflector->getMethods() as $method) {
            if (preg_match_all(self::PATTERN, $method->getDocComment(), $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $handlers[] = [
                        'hook' => sprintf('add_%s', $match['type']),
                        'name' => $match['name'],
                        'callback' => [$instance, $method->getName()],
                        'priority' => $match['priority'] ?? 10,
                        'args' => $method->getNumberOfParameters(),
                    ];
                }
            }
        }

        return $handlers;
    }
}
