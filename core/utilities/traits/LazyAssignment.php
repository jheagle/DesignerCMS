<?php

namespace Core\Utilities\Traits;

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
    protected function applyMemberSettings(array $settings = [])
    {
        $classVars = array_replace_recursive(get_class_vars(get_class($this)), get_object_vars($this));
        // Retrieve all the members of this class so they can be populated lazily
        foreach ($classVars as $classMemberName => $default) {
            // Set this member to the incoming form data otherwise, use the default value
            $newClassMemberValue = is_array($default)
                ? array_replace_recursive($default, (array)($settings[$classMemberName] ?? []))
                : $settings[$classMemberName] ?? $default;
            $this->setMember($classMemberName, $newClassMemberValue);
        }
        return $this;
    }

    /**
     * Get the value of any member by member name.
     *
     * @param $memberKey
     *
     * @return mixed
     */
    protected function getMember($memberKey)
    {
        set_error_handler(function ($severity, $message, $file, $line) {
            throw new \ErrorException($message, $severity, $severity, $file, $line);
        });
        $memberValue = null;
        try {
            $memberValue = constant(get_class($this) . "::{$memberKey}");
        } catch (\Exception $e) {
            try {
                // Attempt to retrieve the member statically
                $memberValue = $this::$$memberKey;
            } catch (\Error $e) {
                // Failed, must not be statically accessible, retrieve as instance member
                $memberValue = $this->{$memberKey};
            }
        }
        restore_error_handler();
        return $memberValue;
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
    protected function setMember($memberKey, $value)
    {
        set_error_handler(function ($severity, $message, $file, $line) {
            throw new \ErrorException($message, $severity, $severity, $file, $line);
        });
        try {
            $value = constant(get_class($this) . "::{$memberKey}");
        } catch (\Exception $e) {
            try {
                // Attempt to assign the member statically
                $this::$$memberKey = $value;
            } catch (\Error $e) {
                // Failed, must not be statically accessible, assign as instance member
                $this->{$memberKey} = $value;
            }
        }
        restore_error_handler();
        return $value;
    }
}
