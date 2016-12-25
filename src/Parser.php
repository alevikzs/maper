<?php

namespace Mapper;

use \ReflectionClass;
use \ReflectionMethod;

/**
 * Class Parser
 * @package Mapper
 */
class Parser {

    /**
     * @const string
     */
    const PARAM_SETTER_PATTERN = '/@param\s+(.+)/';

    /**
     * @var string
     */
    private $class;

    /**
     * @var ReflectionClass
     */
    private $reflectionClass;

    /**
     * @param string $class
     */
    public function __construct(string $class) {
        $this->setClass($class);
    }

    /**
     * @return string
     */
    public function getObject() {
        return $this->class;
    }

    /**
     * @param string $class
     * @return Parser
     */
    public function setClass(string $class): Parser {
        $this->class = $class;

        return $this->setReflectionClass(new ReflectionClass($class));
    }

    /**
     * @return ReflectionClass
     */
    protected function getReflectionClass(): ReflectionClass {
        return $this->reflectionClass;
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @return Parser
     */
    private function setReflectionClass(ReflectionClass $reflectionClass): Parser {
        $this->reflectionClass = $reflectionClass;

        return $this;
    }

    /**
     * @return Setter[]
     */
    public function getSetters(): array {
        $methods = $this->getReflectionClass()
            ->getMethods();

        $setters = [];

        foreach ($methods as $method) {
            if ($this->isSetterMethod($method)) {
                $setters[] = $this->createSetter($method);
            }
        }

        return $setters;
    }

    /**
     * @param ReflectionMethod $method
     * @return bool
     */
    private function isSetterMethod(ReflectionMethod $method): bool {
        $name = $method->getName();

        return strpos($name, 'set') !== false;
    }

    /**
     * @param ReflectionMethod $method
     * @return Setter
     */
    private function createSetter(ReflectionMethod $method): Setter {
        $setter = new Setter(
            lcfirst(str_replace('set', '', $method->getName())),
            $method->getName(),
            '',
            false
        );

        $this->setSetterTypeFromAnnotation($setter, $method);

        if (empty($setter->getType())) {
            $this->setSetterTypeFromSignature($setter, $method);
        }

        $this->fixSetterIsArrayFlag($setter);

        return $setter;
    }

    /**
     * @param Setter $setter
     * @param ReflectionMethod $method
     * @return $this
     */
    private function setSetterTypeFromAnnotation(Setter $setter, ReflectionMethod $method) {
        preg_match(self::PARAM_SETTER_PATTERN, $method->getDocComment(), $paramTypeAndVariable);

        if (isset($paramTypeAndVariable[1])) {
            $paramParts = preg_split('/\s+/', $paramTypeAndVariable[1], 3, PREG_SPLIT_DELIM_CAPTURE);

            foreach ($paramParts as $paramPart) {
                if ($paramPart[0] !== '$') {
                    if (strpos($paramPart, '[]') === false) {
                        $setter->setType($paramPart);
                    } else {
                        $setter->setIsArray()
                            ->setType(str_replace('[]', '', $paramPart));
                    }
                    break;
                }
            }
        }

        return $this;
    }

    /**
     * @param Setter $setter
     * @param ReflectionMethod $method
     * @return $this
     */
    private function setSetterTypeFromSignature(Setter $setter, ReflectionMethod $method) {
        $parameters = $method->getParameters();
        if (isset($parameters[0])) {
            $type = (string) $parameters[0]->getType();

            $setter->setType($type);
        }

        return $this;
    }

    /**
     * @param Setter $setter
     * @return $this
     */
    private function fixSetterIsArrayFlag(Setter $setter) {
        if ($setter->getType() === 'array') {
            $setter->setIsArray();
        }

        return $this;
    }

}