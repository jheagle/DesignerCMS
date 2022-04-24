<?php

/**
 * Assign an array of arguments to the correct positions matching a method's signature.
 *
 * @param string $className
 * @param string $method
 * @param mixed ...$args
 *
 * @return array
 * @throws ReflectionException
 */
$buildParameters = static function (string $className, string $method, ...$args): array {
    if (!method_exists($className, $method)) {
        return $args;
    }
    $parameters = (new ReflectionClass($className))
        ->getMethod($method)
        ->getParameters();
    $firstParamType = (count($parameters) > 0)
        ? (!is_null($parameters[0]->getType()) ? $parameters[0]->getType()->getName() : '')
        : null;
    if (count($args) === 1 && is_array($args[0])) {
        $args = $firstParamType !== 'array' || is_array(reset($args[0]))
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
