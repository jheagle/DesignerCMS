<?php

namespace Core\Traits;

use Error;

/**
 * Trait LazyAssignment
 *
 * @package Core\Utilities\Traits
 */
trait LazyAssignment
{
    /**
     * Provided a keyed array where the keys match names of class members, then the provided values will be applied to
     * each of the class members as specified.
     *
     * @param array $settings
     *
     * @return $this
     */
    public function applyMemberSettings(array $settings = []): self
    {
        // Retrieve all the members of this class so they can be populated lazily
        // Set this member to the incoming form data otherwise, use the default value
        foreach ($this->getAllMembers() as $classMemberName => $default) {
            if (!array_key_exists($classMemberName, $settings) && $this->getMember($classMemberName)) {
                continue;
            }
            if (is_array($default)) {
                $this->setMember(
                    $classMemberName,
                    array_replace_recursive($default, (array)($settings[$classMemberName] ?? []))
                );
                continue;
            }
            $this->setMember($classMemberName, $settings[$classMemberName] ?? $default);
        }
        return $this;
    }

    /**
     * Retrieve all of the class members.
     *
     * @param bool $useDefaults
     *
     * @return array
     */
    public function getAllMembers(bool $useDefaults = false): array
    {
        $classVars = get_class_vars(get_class($this));
        $objectVars = get_object_vars($this);
        $members = array_replace_recursive($classVars, $objectVars);
        if ($useDefaults) {
            return $members;
        }
        return array_reduce(
            array_keys($members),
            function ($memberValues, $memberName) {
                $memberValues[$memberName] = $this->getMember($memberName);
                return $memberValues;
            },
            []
        );
    }

    /**
     * Get the value of any member by member name.
     *
     * @param string $memberKey
     *
     * @return mixed
     */
    public function getMember(string $memberKey): mixed
    {
        $className = get_class($this);
        if (defined("$className::$memberKey")) {
            return constant("$className::$memberKey");
        }
        if (!property_exists($this, $memberKey)) {
            return null;
        }
        try {
            return $this::$$memberKey;
        } catch (Error) {
            return $this->$memberKey ?? null;
        }
    }

    /**
     * Set the value of almost any member by member name.
     * NOTE: You cannot set the value of a constant.
     *
     * @param string $memberKey
     * @param mixed $value
     *
     * @return mixed
     */
    public function setMember(string $memberKey, mixed $value): mixed
    {
        $className = get_class($this);
        if (defined("$className::$memberKey")) {
            return constant("$className::$memberKey");
        }
        if (!property_exists($this, $memberKey)) {
            return $value;
        }
        if ($this->isStaticMember($memberKey)) {
            return $this::$$memberKey = $value;
        }
        $this->$memberKey = $value;
        return $this->$memberKey;
    }

    /**
     * Determine if a member is static.
     *
     * @param string $memberKey
     *
     * @return bool
     */
    private function isStaticMember(string $memberKey): bool
    {
        try {
            $test = $this::$$memberKey;
            return true;
        } catch (Error $error) {
            return false;
        }
    }
}
