<?php

namespace App\Core\Architecture\Abstracts;

use App\Core\Architecture\Interfaces\DTOInterface;
use App\Exceptions\AntiCorruptionValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Throwable;

class AbstractDTO implements DTOInterface
{
    /**
     * Array containing translator dictionary.
     */
    protected array $translatorDictionary = [];

    protected ?MessageBag $validationErrors = null;

    /**
     * Hidden data when convert object to array.
     */
    protected array $hiddenAttributes = [
        'hiddenAttributes',
        'translatorDictionary',
        'validationErrors',
    ];

    /**
     * AbstractDto constructor.
     */
    public function __construct(?array $params = [])
    {
        if (! empty($params)) {
            $this->fill($params);
        }
    }

    /**
     * Fill properties of the Dto with the given params.
     */
    public function fill(array $params): self
    {
        $translatedParams = $this->translate($params);
        $keys = array_keys($translatedParams);
        $objectAttributes = get_class_vars(get_called_class());

        foreach ($keys as $key) {
            if (! array_key_exists($this->snakeToCamelCase($key), $objectAttributes)) {
                continue;
            }
            $this->{$this->snakeToCamelCase($key)} = $translatedParams[$key];
        }

        return $this;
    }

    /**
     * Update properties of the Dto with the given params.
     */
    public function update(array $params): self
    {
        return $this->fill($params);
    }

    /**
     * Validate the Dto.
     */
    public function validate(bool $toUpdate = false): bool
    {
        $rules = $toUpdate ? $this->updateRules() : $this->rules();
        $validator = Validator::make($this->toArray(), $rules);

        if ($validator->fails()) {
            $this->validationErrors = $validator->errors();
        }

        return $validator->passes();
    }

    /**
     * Return a collect of the current Dto or if have a keyName, it add in the key
     * a collection of the Dto that have received as a parameter.
     */
    public function collect(array $collectParams, ?string $dtoClass = null, ?string $keyName = null): self|array
    {
        if (isset($keyName)) {
            $attributes = get_class_vars(get_called_class());

            throw_if(
                ! array_key_exists($keyName, $attributes),
                AntiCorruptionValidationException::class,
                "The key {$keyName} not exists in Dto"
            );
        }

        $collect = [];

        for ($i = 0; $i < count($collectParams); $i++) {
            $currentDto = isset($dtoClass) ? new $dtoClass : new $this;
            $collect[] = $currentDto->make($collectParams[$i])->toArray();
        }

        if (isset($keyName)) {
            $this->$keyName = $collect;

            return $this;
        }

        return $collect;
    }

    /**
     * Fill and validate Dto.
     *
     * @throws Throwable
     */
    public function make(array $params, bool $toUpdate = false): self
    {
        throw_if(
            ! $this->fill($params)->validate($toUpdate),
            AntiCorruptionValidationException::class,
            $this->getErrors()
        );

        return $this;
    }

    /**
     * Return the errors of the Dto validation.
     */
    public function getErrors(): ?MessageBag
    {
        return $this->validationErrors;
    }

    /**
     * Get the Dto as an array.
     */
    public function toArray(): array
    {
        $result = [];
        foreach ($this as $key => $value) {
            if (in_array($key, $this->hiddenAttributes)) {
                continue;
            }
            $key = $this->camelToSnakeCase($key);
            $value = (is_array($value) || is_object($value)) ? $this->objectToArray($value) : $value;
            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * Get the Dto as a JSON string.
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Translate keys to fill if receive array format [ payloadKey => DTOkey ].
     */
    public function translate(array $params): array
    {
        if (empty($this->translatorDictionary)) {
            return $params;
        }

        foreach ($this->translatorDictionary as $payloadKey => $dtoKey) {
            if (array_key_exists($payloadKey, $params)) {
                $params[$dtoKey] = $params[$payloadKey];
                unset($params[$payloadKey]);
            }
        }

        return $params;
    }

    /**
     * Set array used by translate the payload to Dto format.
     */
    public function setTranslatorDictionary(array $translatorDictionary): self
    {
        $this->translatorDictionary = $translatorDictionary;

        return $this;
    }

    /**
     * Transforms object into array.
     */
    private function objectToArray(mixed $param): array
    {
        return json_decode(json_encode($param), true);
    }

    /**
     * Convert a string from camel case to snake case.
     */
    private function camelToSnakeCase(string $string): string
    {
        return strtolower(preg_replace_callback('/[A-Z]/', function ($match) {
            return '_'.strtolower($match[0]);
        }, $string));
    }

    /**
     * Convert a string from snake case to camel case.
     */
    protected function snakeToCamelCase(string $string): string
    {
        return lcfirst(str_replace('_', '', ucwords($string, '_')));
    }

    /**
     * Rules for validation.
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Rules for validation when updates.
     */
    public function updateRules(): array
    {
        return [];
    }
}