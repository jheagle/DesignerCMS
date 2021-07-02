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
    protected function applyMemberSettings(array $settings = []): static
    {
        // Retrieve all the members of this class so they can be populated lazily
        foreach ($this->getAllMembers() as $classMemberName => $default) {
            // Set this member to the incoming form data otherwise, use the default value
            $newClassMemberValue = is_array($default)
                ? array_replace_recursive($default, (array)($settings[$classMemberName] ?? []))
                : $settings[$classMemberName] ?? $default;
            $this->setMember($classMemberName, $newClassMemberValue);
        }
        return $this;
    }

    /**
     * Retrieve all of the class members.
     *
     * @return array
     */
    protected function getAllMembers(): array
    {
        return array_replace_recursive(get_class_vars(get_class($this)), get_object_vars($this));
    }

    /**
     * Get the value of any member by member name.
     *
     * @param $memberKey
     *
     * @return mixed
     */
    protected function getMember($memberKey): mixed
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
            return $this->$memberKey;
        }
    }

    /**
     * Set the value of almost any member by member name.
     * NOTE: You cannot set the value of a constant.
     *
     * @param $memberKey
     * @param $value
     *
     * @return mixed
     */
    protected function setMember($memberKey, $value): mixed
    {
        $className = get_class($this);
        if (defined("$className::$memberKey")) {
            return constant("$className::$memberKey");
        }
        if (!property_exists($this, $memberKey)) {
            return $value;
        }
        try {
            $this::$$memberKey = $value;
            return $this::$$memberKey;
        } catch (Error) {
            $this->$memberKey = $value;
            return $this->$memberKey;
        }
    }
}
