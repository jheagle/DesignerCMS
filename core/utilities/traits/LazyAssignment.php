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
            $value = constant("self::{$memberKey}");
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
