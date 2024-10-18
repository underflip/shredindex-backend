<?php
namespace Headstart\Scalars;

use GraphQL\Type\Definition\ScalarType;
use GraphQL\Error\Error;

class JSONScalar extends ScalarType
{
    public $name = 'JSON';

    public function serialize($value)
    {
        return $value;  // Return the JSON value as is
    }

    public function parseValue($value)
    {
        return is_array($value) ? $value : json_decode($value, true);
    }

    public function parseLiteral($ast)
    {
        if ($ast->kind !== 'StringValue') {
            throw new Error("Can only parse string literals for JSON.");
        }
        return json_decode($ast->value, true);
    }
}

?>