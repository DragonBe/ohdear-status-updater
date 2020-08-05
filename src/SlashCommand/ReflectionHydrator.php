<?php

namespace OhDear\Status\SlashCommand;

use DateTimeImmutable;
use DateTimeInterface;
use OhDear\Status\HydratorInterface;
use ReflectionClass;

class ReflectionHydrator implements HydratorInterface
{
    /**
     * @inheritDoc
     *
     * @param MessageInterface|StatusUpdateInterface $object
     * @param array $data
     * @return MessageInterface|StatusUpdateInterface
     */
    public function hydrate(object $object, array $data): object
    {
        $reflectionClass = new ReflectionClass($object);
        $properties = $reflectionClass->getProperties();
        foreach ($properties as $property) {
            $key = $this->camelToSnakeConvert($property->getName());
            if (array_key_exists($key, $data)) {
                $property->setAccessible(true);
                $value = $data[$key];
                if ('time' === $key) {
                    $value = new DateTimeImmutable($data[$key]);
                }
                $property->setValue($object, $value);
            }
        }
        return $object;
    }

    /**
     * @inheritDoc
     */
    public function extract(object $object): array
    {
        $data = [];
        $reflectionClass = new ReflectionClass($object);
        $properties = $reflectionClass->getProperties();
        foreach ($properties as $property) {
            $key = $this->camelToSnakeConvert($property->getName());
            $property->setAccessible(true);
            $value = $property->getValue($object);
            if ($value instanceof DateTimeInterface) {
                $value = $value->format('Y-m-d H:i');
            }
            $data[$key] = $value;
        }
        return $data;
    }

    /**
     * Convert camel cased parameters into snake cased arguments
     *
     * @param string $input
     * @return string
     */
    private function camelToSnakeConvert(string $input): string
    {
        $pattern = '/(?<=\d)(?=[A-Za-z])|(?<=[A-Za-z])(?=\d)|(?<=[a-z])(?=[A-Z])/';
        return strtolower(preg_replace($pattern, '_', $input));
    }
}
