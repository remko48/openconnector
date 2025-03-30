<?php

namespace OCA\OpenConnector\Db;

use DateTime;
use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Mapping extends Entity implements JsonSerializable
{

    /**
     * The unique identifier of the mapping.
     *
     * @var string|null
     */
    protected ?string $uuid = null;

    /**
     * The reference identifier for the mapping.
     *
     * @var string|null
     */
    protected ?string $reference = null;

    /**
     * The version of the mapping.
     *
     * @var string|null
     */
    protected ?string $version = '0.0.0';

    /**
     * The name of the mapping.
     *
     * @var string|null
     */
    protected ?string $name = null;

    /**
     * The description of the mapping.
     *
     * @var string|null
     */
    protected ?string $description = null;

    /**
     * The mapping configuration.
     *
     * @var array|null
     */
    protected ?array $mapping = [];

    /**
     * The unset configuration.
     *
     * @var array|null
     */
    protected ?array $unset = [];

    /**
     * The cast configuration.
     *
     * @var array|null
     */
    protected ?array $cast = [];

    /**
     * Indicates if the mapping is a pass-through.
     *
     * @var bool|null
     */
    protected ?bool $passThrough = null;

    /**
     * The date and time the mapping was created.
     *
     * @var DateTime|null
     */
    protected ?DateTime $dateCreated = null;

    /**
     * The date and time the mapping was last modified.
     *
     * @var DateTime|null
     */
    protected ?DateTime $dateModified = null;


    /**
     * Get the mapping configuration
     *
     * @return array The mapping configuration or empty array if null
     */
    public function getMapping(): array
    {
        return ($this->mapping ?? []);

    }//end getMapping()


    /**
     * Get the unset configuration
     *
     * @return array The unset configuration or empty array if null
     */
    public function getUnset(): array
    {
        return ($this->unset ?? []);

    }//end getUnset()


    /**
     * Get the cast configuration
     *
     * @return array The cast configuration or empty array if null
     */
    public function getCast(): array
    {
        return ($this->cast ?? []);

    }//end getCast()


    public function __construct()
    {
        $this->addType('uuid', 'string');
        $this->addType('reference', 'string');
        $this->addType('version', 'string');
        $this->addType('name', 'string');
        $this->addType('description', 'string');
        $this->addType('mapping', 'json');
        $this->addType('unset', 'json');
        $this->addType('cast', 'json');
        $this->addType('passThrough', 'boolean');
        $this->addType('dateCreated', 'datetime');
        $this->addType('dateModified', 'datetime');

    }//end __construct()


    public function getJsonFields(): array
    {
        return array_keys(
            array_filter(
                $this->getFieldTypes(),
                function ($field) {
                    return $field === 'json';
                }
            )
        );

    }//end getJsonFields()


    public function getUpdated(): ?DateTime
    {
        return $this->dateModified;

    }//end getUpdated()


    public function hydrate(array $object): self
    {
        $jsonFields = $this->getJsonFields();

        foreach ($object as $key => $value) {
            if (in_array($key, $jsonFields) === true && $value === []) {
                $value = [];
            }

            $method = 'set'.ucfirst($key);

            try {
                $this->$method($value);
            } catch (\Exception $exception) {
                // ("Error writing $key");
            }
        }

        return $this;

    }//end hydrate()


    public function jsonSerialize(): array
    {
        return [
            'id'           => $this->id,
            'uuid'         => $this->uuid,
            'name'         => $this->name,
            'description'  => $this->description,
            'version'      => $this->version,
            'reference'    => $this->reference,
            'mapping'      => $this->mapping,
            'unset'        => $this->unset,
            'cast'         => $this->cast,
            'passThrough'  => $this->passThrough,
            'dateCreated'  => isset($this->dateCreated) ? $this->dateCreated->format('c') : null,
            'dateModified' => isset($this->dateModified) ? $this->dateModified->format('c') : null,
        ];

    }//end jsonSerialize()


}//end class
