<?php
    namespace CloudFramework\Service\DataStore\Message;

    use CloudFramework\Patterns\Singleton;

    abstract class Schema extends Singleton
    {
        public $id_int_index;

        /**
         * Required method that define the specific table to manipulate datastore(kind)
         * @return mixed
         */
        abstract function getKind();

        public function __construct($id = NULL)
        {
            if (NULL === $id) {
                $id = microtime(TRUE) * 10000;
            }
            $this->id_int_index = $id;
        }

        private function createEntity()
        {
            $entity = new \Google_Service_Datastore_Entity();
            $entity->setKey($this->createKeyForEntity());
            $property_map = [];
            $property_map = $this->hydrateProperties($property_map);
            $entity->setProperties($property_map);

            return $entity;
        }

        private function createKeyForEntity()
        {
            $path = new \Google_Service_Datastore_KeyPathElement();
            $path->setKind($this->getKind());
            $path->setName(get_class($this) . "[" . $this->id_int_index . "]");
            $key = new \Google_Service_Datastore_Key();
            $key->setPath([$path]);

            return $key;
        }

        /**
         * @return \Google_Service_Datastore_CommitRequest
         */
        public function createRequestMessage()
        {
            $entity = $this->createEntity();
            $mutation = new \Google_Service_Datastore_Mutation();
            $mutation->setUpsert([$entity]);
            $req = new \Google_Service_Datastore_CommitRequest();
            $req->setMode('NON_TRANSACTIONAL');
            $req->setMutation($mutation);

            return $req;
        }

        /**
         * Mapper for types of fields
         *
         * @param string $type
         * @param mixed $value
         * @param string $field
         * @param string $index
         *
         * @return \Google_Service_Datastore_Property
         */
        private function mapProperty($type, $value, $field, $index = '')
        {
            $property = new \Google_Service_Datastore_Property();
            switch (strtolower($type)) {
                default:
                case 'string':
                    $property->setStringValue($value);
                    break;
                case 'int':
                case 'integer':
                    $property->setIntegerValue(intval($value));
                    break;
                case 'datetime':
                    if (!$value instanceof \DateTime) {
                        syslog(LOG_INFO, "$field is not a DateTime object");
                        continue;
                    }
                    $property->setDateTimeValue($value->format(\DateTime::ATOM));
                    break;
                case 'float':
                    $property->setDoubleValue(floatval($value));
                    break;
                case 'boolean':
                case 'bool':
                    $property->setBooleanValue(boolval($value ?: FALSE));
                    break;
            }
            if ('index' === strtolower($index)) {
                $property->setIndexed(TRUE);
            } else {
                $property->setIndexed(FALSE);
            }

            return $property;
        }

        /**
         * Mapper filters for types of fields
         *
         * @param string $type
         * @param mixed $value
         * @param string $field
         * @param string $operator
         *
         * @return string
         */
        private function mapFilter($type, $value, $field, $operator = '=')
        {
            switch (strtolower($type)) {
                default:
                case 'string':
                    $filter = "{$field} contains '{$value}'";
                    break;
                case 'int':
                case 'integer':
                case 'float':
                    $filter = "{$field} {$operator} {$value}";
                    break;
                case 'datetime':
                    if (!$value instanceof \DateTime) {
                        syslog(LOG_INFO, "$field is not a DateTime object");
                        continue;
                    }
                    $dateFilter = $value->format('Y-m-d');
                    $filter = "{$field} contains '{$dateFilter}'";
                    break;
                case 'boolean':
                case 'bool':
                    $filter = "{$field} = '" . ($value ? 1 : 0) . "'";
                    break;
            }

            return $filter;
        }

        /**
         * @param array $property_map
         *
         * @return mixed
         */
        private function hydrateProperties(array &$property_map)
        {
            foreach (get_object_vars($this) as $key => $value) {
                list($field, $type, $index) = explode('_', $key, 3);
                if (!in_array($field, ['loaded', 'loadTs', 'loadMem'])) {
                    $property_map[$field] = $this->mapProperty($type, $value, $field, $index);
                }
            }

            return $property_map;
        }

        /**
         * @return array
         */
        public function generateFilteredQuery()
        {
            $filters = array();
            foreach (get_object_vars($this) as $key => $value) {
                list($field, $type, $index) = explode('_', $key, 3);
                if (!in_array($field, ['id', 'loaded', 'loadTs', 'loadMem']) && 'index' === $index && !empty($value)) {
                    $filters[] = $this->mapFilter($type, $value, $field);
                }
            }

            return $filters;
        }

        /**
         * Get an unique hash for current class
         * @return string
         */
        public function generateHash()
        {
            $classFingerprint = '';
            foreach (get_object_vars($this) as $key => $value) {
                list($field, $type, $index) = explode('_', $key, 3);
                if (!in_array($field, ['id', 'loaded', 'loadTs', 'loadMem'])) {
                    $classFingerprint .= $field;
                    $classFingerprint .= $type ?: 'string';
                    $classFingerprint .= $index ?: '';
                    $classFingerprint .= '_';
                }
            }

            return sha1($this->getKind() . $classFingerprint);
        }

        /**
         * @param \Google_Service_Datastore_Entity $entity
         */
        public function hydrateFromEntity(\Google_Service_Datastore_Entity $entity)
        {
            $array = json_decode(json_encode($entity->toSimpleObject()->properties), true);
            $class = get_class($this);
            $schemaEntity = new $class();
            foreach (get_object_vars($this) as $key => $value) {
                list($field, $type, $index) = explode('_', $key, 3);
                if (array_key_exists($field, $array)) {
                    $values = array_values($array[$field]);
                    $schemaEntity->$key = $values ?: null;
                }
            }
            return $schemaEntity;
        }

    }
