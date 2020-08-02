<?php

namespace OhDear\Status;

interface HydratorInterface
{
    /**
     * Hydrates data into an object and returns the object
     *
     * @param object $object
     * @param array $data
     * @return object
     */
    public function hydrate(object $object, array $data): object;

    /**
     * Extracts data from an object and returns an array
     *
     * @param object $object
     * @return array
     */
    public function extract(object $object): array;
}
