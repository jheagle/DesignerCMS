<?php

namespace Core\Entity;

/**
 * Class Entity
 *
 * @package Core\Entity
 */
abstract class Entity implements EntityObject
{
    protected $db;

    protected $do_output;

    /**
     * Entity constructor.
     *
     * @param $db
     */
    public function __construct(&$db)
    {
        $ob_vars = get_object_vars($this);
        $args = func_get_args();
        $count = count($args);
        $i = 0;

        foreach (array_keys($ob_vars) as $prop) {
            if ($prop === 'db') {
                $this->db = $args[$i] instanceof DBConnect ? $db : null;
            } elseif ($i < $count && isset($args[$i]) && !empty($args[$i])) {
                $this->set($prop, $args[$i]);
            }
            ++$i;
        }
        if ($this->db->testing || !$this->db->production) {
            $this->do_output = true;
        }
    }

    /**
     * @param $property
     *
     * @return bool
     */
    public function __get($property)
    {
        if (property_exists($this, $property) && isset($this->{$property})) {
            return true;
        }

        return false;
    }

    /**
     * @param $property
     * @param $value
     *
     * @return bool
     */
    public function __set($property, $value)
    {
        if (property_exists($this, $property) && isset($this->{$property})) {
            return true;
        }

        return false;
    }

    /**
     * @param $property
     *
     * @return bool
     */
    public function get($property)
    {
        if (preg_match('/^(db|do_output)$/', $property)) {
            return false;
        }
        if (isset($this->{$property})) {
            if (is_array($this->{$property})) {
                return $this->{$property};
            }

            return $this->db->sanitizeOutput($this->{$property}->getValue());
        }

        return false;
    }

    /**
     * @param $property
     * @param $value
     *
     * @return bool
     */
    public function set($property, $value)
    {
        if (preg_match('/^(db|do_output)$/', $property) || !property_exists(
                $this,
                $property
            )) {
            return false;
        }
        if (is_array($value)) {
            if (!is_array($this->{$property})) {
                $this->{$property} = empty($this->{$property}) ? [] : [$this->{$property}];
            }
            foreach ($value as $val) {
                if ($val instanceof self) {
                    $this->{$property}[] = $val;
                } else {
                    $this->db->consoleOut(
                        "Invalid Entity Type for {$property} as " . json_encode($value),
                        strtoupper(get_class($this))
                    );

                    return false;
                }
            }
        }
        if ($this->do_output) {
            $this->db->consoleOut(
                "Setting {$property} to " . json_encode($value),
                strtoupper(get_class($this))
            );
        }
        if ($value instanceof self) {
            $this->{$property} = [$value];
        } else {
            $this->{$property}->setValue($this->db->sanitizeInput($value));
        }

        return $this->{$property};
    }

    /**
     * @return $this
     */
    public function createEntity()
    {
        $ob_vars = get_object_vars($this);
        $columns = $values = $children = [];
        $idProp = '';

        foreach ($ob_vars as $prop => $val) {
            if (is_array($val)) {
                $children[$prop] = $val;
                continue;
            }
            if (!($val instanceof Field)) {
                continue;
            }
            if ($val->hasAttr(Field::AUTO_INCREMENT) && $val->hasAttr(
                    Field::PRIMARY_KEY
                )) {
                $idProp = $prop;
                continue;
            }
            //TODO: add checks for other keys and primary keys which are not AUTO_INCREMENT
            if (($val->hasAttr(Field::REQUIRED) && empty(
                    $val->getValue()
                    )) || ($prop->hasAttr(Field::UNSIGNED) && $val->getValue < 0)) {
                return false;
            } else {
                $columns[] = "`{$prop}`";
                $values[] = "'{$val->getValue()}'";
            }
        }

        $table = $this->db->camelToUnderscore(get_class($this));
        $cols = implode(',', $columns);
        $vals = implode(',', $values);
        $this->db->insert("INSERT INTO `{$table}` ({$cols}) VALUES ({$vals})");
        if (!empty($idProp)) {
            $this->{$idProp}->setValue($this->db->lastInsertId());
        }
        //TODO: set $idProp to a key that represents the ForeignKey
        $GLOBALS['tracking']->add_event(
            "Created {$this->first_name} {$this->middle_name} {$this->last_name}",
            $this,
            $this->id
        );
        foreach ($children as $child) {
            foreach ($this->{$child} as $entity) {
                if ($entity instanceof self) {
                    //TODO: if there is no ForeignKey available skip this, check availability, improve ForeignKey logic in child class
                    $entity->setForeignKey(
                        $table,
                        $this->{$idProp}->getValue()
                    );
                    $entity->createEntity();
                }
            }
        }

        return $this;
    }

    public function get_as_json($array = null)
    {
        $ob_vars = is_array($array) ? $array : get_object_vars($this);
        if (isset($ob_vars['db'])) {
            unset($ob_vars['db'], $ob_vars['do_output']);
        }
        foreach ($ob_vars as &$val) {
            if (is_array($val)) {
                $val = $this->get_as_json($val);
            } elseif (is_object($val) && method_exists($val, 'get_as_json')) {
                $val = $val->get_as_json();
            }
        }

        return $ob_vars;
    }

    public function get_all_contacts($summary = true)
    {
        $table = $this->db->camelToUnderscore(get_class($this));
        $contact_ids = [];
        while ($row = $this->db->select_assoc("SELECT `id` FROM `{$table}`")) {
            $contact_ids[] = $row['id'];
        }

        $contact_list = $this->retrieve_contacts_by_ids($contact_ids, $summary);

        if (is_array($contact_list)) {
            usort($contact_list, [$this, 'compare_contacts']);
        }

        return $contact_list;
    }

    public function get_contact()
    {
        $contact = new Contact($this->db);
        if (isset($this->id) && $this->id > 0) {
            $contact = $this->retrieve_contact_by_id($this->id, false);
        } else {
            $contacts = $this->search_contact();
            if (is_array($contacts)) {
                $contact = end($contacts);
            }
        }
        $ob_vars = get_object_vars($contact);

        foreach ($ob_vars as $prop => $val) {
            if ($prop !== 'db') {
                $this->set($prop, $val);
            }
        }

        return $this;
    }

    public function search_contact($name = '')
    {
        $contact_list = $this->retrieve_contacts_by_ids(
            $this->search_contact_ids($name)
        );
        if (is_array($contact_list)) {
            usort($contact_list, [$this, 'compare_contacts']);
        }

        return $contact_list;
    }

    private function search_contact_ids($nameIn = '', $only_contact = false)
    {
        $ob_vars = get_object_vars($this);
        $name = $this->db->sanitizeInput($nameIn);
        $contact_ids = $have_value = [];

        foreach ($ob_vars as $prop => $val) {
            if (!empty($name) && $prop === 'first_name') {
                $have_value[] = "MATCH(`first_name`,`middle_name`,`last_name`) AGAINST('{$name}')";
            } elseif ((!preg_match(
                        '/^(db|id|first_name|middle_name|last_name|address|phone_number)/',
                        $prop
                    ) || (empty($name) && preg_match(
                            '/^(first_name|middle_name|last_name)/',
                            $prop
                        ))) && isset($val) && !empty($val)) {
                $have_value[] = "`{$prop}` LIKE '%{$val}%'";
            }
        }

        if (!empty($have_value)) {
            $table = $this->db->camelToUnderscore(get_class($this));
            $have = implode(' AND ', $have_value);
            $query = "SELECT `id` FROM `{$table}` WHERE {$have}";

            while ($row = $this->db->select_assoc($query)) {
                $contact_ids[] = $row['id'];
            }
        }

        return $only_contact ? $contact_ids : $this->search_contact_id_by_contact_info(
            'phone_number',
            $this->search_contact_id_by_contact_info('address', $contact_ids)
        );
    }

    private function search_contact_id_by_contact_info($type, $contact_ids = [], $arrayIn = null)
    {
        $array = is_array($arrayIn) ? $arrayIn : $this->{$type};
        $ids = [];
        if (isset($array) && !empty($array)) {
            foreach ($array as $contact_info) {
                if (isset($contact_info) && !empty($contact_info)) {
                    $ids = is_array(
                        $contact_info
                    ) ? $this->search_contact_id_by_contact_info(
                        $type,
                        $contact_ids,
                        $contact_info
                    ) : $contact_info->{"get_all_contact_{$type}"}(true);
                }
            }
        }
        if (!empty($ids)) {
            return empty($contact_ids) ? $ids : array_intersect(
                $contact_ids,
                $ids
            );
        } else {
            return $contact_ids;
        }
    }

    private function retrieve_contacts_by_ids($contact_ids = [], $summary = true)
    {
        if (empty($contact_ids)) {
            return;
        }

        $contacts = [];

        foreach ($contact_ids as $contact_id) {
            $contacts[] = $this->retrieve_contact_by_id($contact_id, $summary);
        }

        return $contacts;
    }

    private function retrieve_contact_by_id($idIn, $summary = true, $getMultiArray = false)
    {
        $ob_vars = get_object_vars($this);
        $table = $this->db->camelToUnderscore(get_class($this));
        $id = isset($idIn) ? $idIn : $this->id;
        $columns = $values = $numbers = [];

        foreach ($ob_vars as $prop => $val) {
            if (!preg_match(
                    '/^(db|address|phone_number|email|notes)/',
                    $prop
                ) || (!$summary && preg_match('/^(email|notes)/', $prop))) {
                $columns[] = "`{$prop}`";
            }
        }
        $cols = implode(', ', $columns);
        $row = $this->db->select_assoc(
            "SELECT {$cols} FROM `{$table}` WHERE `id`={$id}"
        );

        if (!$summary) {
            $address = new ContactAddress($this->db, -1, $id);
            $row['address'] = $address->get_all_contact_address();
            $phone_number = new ContactPhoneNumber($this->db, -1, $id);
            $phone_numbers = $phone_number->get_all_contact_phone_number();
            $row['phone_number'] = $phone_numbers;
            if ($getMultiArray) {
                foreach ($phone_numbers as $phone) {
                    if (!isset($numbers[$phone->phone_type])) {
                        $numbers[$phone->phone_type] = [];
                    }
                    $numbers[$phone->phone_type][] = $phone;
                }
                $row['phone_number'] = $numbers;
            }
        }

        foreach ($ob_vars as $prop => &$val) {
            $values[$prop] = isset($row[$prop]) ? $row[$prop] : $val;
        }

        $reflect = new ReflectionClass($this);

        return $reflect->newInstanceArgs($values);
    }

    public function update_contact()
    {
        if (!isset($this->id) || $this->id < 1 || empty($this->first_name) || empty($this->last_name) || empty($this->address) || empty($this->phone_number['work'])) {
            return;
        }
        $ob_vars = get_object_vars($this);
        $table = $this->db->camelToUnderscore(get_class($this));
        $set_to = $columns = $changed_col = [];

        foreach ($ob_vars as $prop => $val) {
            if (!preg_match('/^db|address|phone_number/', $prop)) {
                $columns[] = "`{$prop}`";
            }
        }
        $cols = implode(',', $columns);
        $orig_cols = $this->db->select_assoc(
            "SELECT {$cols} FROM `{$table}` WHERE `id` = {$this->id}"
        );

        foreach ($ob_vars as $prop => $val) {
            if (!preg_match(
                    '/^db|address|phone_number/',
                    $prop
                ) && $val != $orig_cols[$prop]) {
                $changed_col[$prop] = $val;
                $GLOBALS['tracking']->add_event(
                    "Modified {$this->first_name} {$this->middle_name} {$this->last_name} {$prop} from {$orig_cols[$prop]} to {$val}",
                    $this,
                    $this->id
                );
            }
        }

        foreach ($changed_col as $prop => $val) {
            if ((!isset($val) || empty($val)) && preg_match(
                    '/^(id|first_name|last_name)/',
                    $prop
                )) {
                return;
            }
            $set_to[] = "`{$prop}` = '{$val}'";
        }

        if (!empty($set_to)) {
            $set = implode(',', $set_to);
            $this->db->update("UPDATE `{$table}` SET {$set} WHERE `id`={$this->id}");
        }

        $this->update_contact_info('address');
        $this->update_contact_info('phone_number');

        return $this;
    }

    public function delete_contact()
    {
        if (!isset($this->id) || $this->id < 1) {
            return;
        }

        foreach ($this->address as &$address) {
            $address->delete_contact_address();
        }

        foreach ($this->phone_number as &$phone_number) {
            foreach ($phone_number as &$number) {
                $number->delete_contact_phone_number();
            }
        }

        $table = $this->db->camelToUnderscore(get_class($this));
        $this->db->delete("DELETE FROM `{$table}` WHERE `id`={$this->id}");
        $GLOBALS['tracking']->add_event(
            "Deleted {$this->first_name} {$this->middle_name} {$this->last_name}",
            $this,
            $this->id
        );

        return $this;
    }

    private function compare_contacts($a, $b)
    {
        for ($i = 0; $i < 4; ++$i) {
            switch ($i) {
                case 0:
                    $result = strcasecmp($a->last_name, $b->last_name);
                    break;
                case 1:
                    $result = strcasecmp($a->first_name, $b->first_name);
                    break;
                case 2:
                    $result = strcasecmp($a->middle_name, $b->middle_name);
                    break;
                default:
                    $result = $a->id < $b->id ? -1 : 1;
            }
            if ($result !== 0) {
                return $result;
            }
        }

        return $result;
    }

    public function compareTo($entity)
    {
        $result = 0;
        foreach (get_object_vars($this) as $k => $v) {
            $result = strcasecmp((string)$this->{$k}, (string)$entity->{$k});
            if ($result !== 0) {
                break;
            }
        }

        return $result;
    }

    public function __toString(): string
    {
        $string = '';
        foreach (get_object_vars($this) as $k => $v) {
            if (empty($string)) {
                $string = __CLASS__ . '( ';
            } else {
                $string .= ', ';
            }
            $string .= "{$k}: {$v}";
        }

        return $string . ' )';
    }

}
