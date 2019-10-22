<?php


namespace App\Models\Traits;


use Exception;

trait Adminable
{

    public function getFields()
    {
        $columns = $this->newQuery()->fromQuery("SHOW FIELDS FROM " . $this->getTable())->toArray();

        $response = [];

        $constants = config('constants');

        foreach ($columns as $column) {


            $needsSlicing = \strpos($column['Type'], '(');

            $type = $needsSlicing === false ? $column['Type'] : \substr($column['Type'], 0,
                $needsSlicing);

            $elementType    = $this->elementType($column['Field'], $type);
            $element        = 'input';
            $possibleValues = null;
            if (\is_array($elementType)) {
                $element        = $elementType[0];
                $possibleValues = $elementType[1];
            } else {
                $element = $elementType;
            }

            $response[$column['Field']] = [
                'field'    => $column['Field'],
                'title'    => \ucfirst(\str_replace('_', ' ', $column['Field'])),
                'type'     => $type,
                'element'  => $element,
                'default'  => $column['Default'],
                'fillable' => \in_array($column['Field'], $this->fillable),
                'value'    => $this->{$column['Field']} ?? null,
            ];

            if ($response[$column['Field']]['element'] === 'select') {

                if (isset($constants[\strtoupper($column['Field'])])) {
                    $response[$column['Field']]['possibleValues'] = $constants[\strtoupper($column['Field'])];
                }
                if ($possibleValues !== null) {
                    $response[$column['Field']]['possibleValues'] = \array_merge(
                        $response[$column['Field']]['possibleValues'] ?? [],
                        $possibleValues
                    );
                }
            }
        }


        return $response;
    }

    /**
     * Returns the type of element we want to use
     *
     * We use "elementCasts" on the model to convert other types of fields.
     *
     * For example "gender" works like an "enum" but isnt an "enum" in the database. So we can handle those scenarios
     * on the model
     *
     * @param $field
     * @param $type
     *
     * @return string
     * @throws Exception
     */
    public function elementType($field, $type)
    {
        $possibleValues = null;

        if (isset($this->elementCasts)) {
            if (isset($this->elementCasts[$field])) {
                $type = $this->elementCasts[$field];

                if (\is_array($type)) {
                    $possibleValues = $type;
                    $type           = 'enum';
                }
            }
        }

        switch ($type) {
            case "varchar";
                return "input";
            case "text";
            case "json";
                return "textarea";
            case "int";
                return "number";
            case "decimal";
                return "decimal";
            case "date";
                return "date";
            case "datetime";
            case "timestamp";
                return "datetime";
            case "enum";
                return ["select", $possibleValues];
            case "tinyint";
                return "tinyint";
            default:
                throw new Exception("This field is not handled yet. Field: {$field} Type: {$type}");
        }
    }

}