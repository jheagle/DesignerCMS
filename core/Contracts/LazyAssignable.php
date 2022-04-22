<?php

namespace Core\Contracts;

interface LazyAssignable
{
    /**
     * Provided a keyed array where the keys match names of class members, then the provided values will be applied to
     * each of the class members as specified.
     *
     * @param array $settings
     *
     * @return $this
     */
    public function applyMemberSettings(array $settings = []): self;

    /**
     * Retrieve all of the class members.
     *
     * @param bool $useDefaults
     * @return array
     */
    public function getAllMembers(bool $useDefaults = false): array;

    /**
     * Get the value of any member by member name.
     *
     * @param string $memberKey
     *
     * @return mixed
     */
    public function getMember(string $memberKey): mixed;

    /**
     * Set the value of almost any member by member name.
     * NOTE: You cannot set the value of a constant.
     *
     * @param string $memberKey
     * @param mixed $value
     *
     * @return mixed
     */
    public function setMember(string $memberKey, mixed $value): mixed;
}