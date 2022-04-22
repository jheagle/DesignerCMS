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
    private function applyMemberSettings(array $settings = []): static
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
     * @return array
     */
    private function getAllMembers(): array
    {
        return array_replace_recursive(get_class_vars(get_class($this)), get_object_vars($this));
    }

    /**
     * Get the value of any member by member name.
     *
     * @param string $memberKey
     *
     * @return mixed
     */
    private function getMember(string $memberKey): mixed
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
    private function setMember(string $memberKey, mixed $value): mixed
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
