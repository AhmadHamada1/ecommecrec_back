<?php
namespace App\Utilities;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\InputObjectType;

class GraphQLSchemas
{
    public static function currencyInput(): InputObjectType {
        return new InputObjectType([
            'name' => 'CurrencyInput',
            'fields' => [
                'label' => Type::nonNull(Type::string()),
                'symbol' => Type::nonNull(Type::string()),
            ],
        ]);
    }

    public static function priceInput(): InputObjectType {
        return new InputObjectType([
            'name' => 'PriceInput',
            'fields' => [
                'amount' => Type::nonNull(Type::float()),
                'currency' => self::currencyInput(),
            ],
        ]);
    }

    public static function currencyType(): ObjectType {
        return new ObjectType([
            'name' => 'Currency',
            'fields' => [
                'label' => Type::nonNull(Type::string()),
                'symbol' => Type::nonNull(Type::string()),
            ],
        ]);
    }

    public static function priceType(): ObjectType {
        return new ObjectType([
            'name' => 'Price',
            'fields' => [
                'amount' => Type::nonNull(Type::float()),
                'currency' => self::currencyType(),
            ],
        ]);
    }
    public static function attributeItemType(): ObjectType {
        return new ObjectType([
            'name' => 'Attribute',
            'fields' => [
                'id'           => Type::nonNull(Type::string()),
                'value'        => Type::nonNull(Type::string()),
                'displayValue' => Type::nonNull(Type::string()),
            ],
        ]);
    }

    public static function attributeSetType(): ObjectType {
        return new ObjectType([
            'name' => 'AttributeSet',
            'fields' => [
                'id'    => Type::nonNull(Type::string()),
                'name'  => Type::nonNull(Type::string()),
                'type'  => Type::nonNull(Type::string()),
                'items' => Type::nonNull(Type::listOf(Type::nonNull(self::attributeItemType()))),
            ],
        ]);
    }

    public static function orderItemsInputType(): InputObjectType
    {
        return new InputObjectType([
            'name' => 'OrderItemInput',
            'fields' => [
                'product_id' => Type::nonNull(Type::string()),
                'quantity' => Type::nonNull(Type::int()),
                'price' => Type::nonNull(Type::float()),
            ]
        ]);
    }
}
