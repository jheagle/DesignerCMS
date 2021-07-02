<?php

/**
 * Get and return the first value that does not match the value to be replaced.
 *
 * @param string $className
 * @param string $method
 * @param mixed ...$args
 *
 * @return array
 * @throws ReflectionException
 */
$buildParameters = static function (string $className, string $method, ...$args): array {
    $parameters = (new ReflectionClass($className))
        ->getMethod($method)
        ->getParameters();
    $firstParamType = (count($parameters) > 0)
        ? (!is_null($parameters[0]->getType()) ? $parameters[0]->getType()->getName() : '')
        : null;
    if (count($args) === 1 && is_array($args[0])) {
        $args = $firstParamType !== 'array' || is_array(dotGet($args[0], 0))
            ? $args[0]
            : $args;
    }
    return array_reduce(
        $parameters,
        function (array $namedParams, ReflectionParameter $param) use ($method, $className, $args) {
            $type = !is_null($param->getType()) ? $param->getType()->getName() : 'undefined';
            $paramValue = castTo(
                coalesce(
                    null,
                    dotGet($args, $param->getName()),
                    dotGet($args, $param->getPosition()),
                    $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null
                ),
                $type
            );
            $namedParams[$param->getName()] = $paramValue;
            return $namedParams;
        },
        []
    );
};

if (($declareGlobal ?? false) && !function_exists('buildParameters')) {
    $GLOBALS['buildParameters'] = $buildParameters;
    function buildParameters(string $className, string $method, ...$args)
    {
        return $GLOBALS['buildParameters']($className, $method, ...$args);
    }
}
