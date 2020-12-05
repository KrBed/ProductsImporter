<?php

namespace ProductsImporter\Utils;

use ProductsImporter\Classes\ProductAttribute;

class AttributesHelper {
    public static function CreateAttributeObjects($attributesArray)
    {
        $attributes = [];
        foreach ($attributesArray as $attributeType => $attributeNames) {

            $attribute = new ProductAttribute();
            $attribute->setName($attributeType);
            foreach ($attributeNames as $name) {
                $child = new ProductAttribute();
                $child->setName($name);
                $attribute->addChild($child);
            }
            $attributes[] = $attribute;
        }

        return $attributes;
    }

    /**
     * @param ProductAttribute $attribute
     * @return array
     */
    public static function getAttributeGroupShopData($attribute)
    {
        return [
            'id_attribute_group' => $attribute->getId(),
            'id_shop'            => $_ENV['SHOP_ID'],
        ];
    }

    /**
     * @param ProductAttribute $attribute
     * @return array
     */
    public static function getAttributeGroupData($attribute)
    {
        return [
            'id_attribute_group' => $attribute->getId(),
            'is_color_group'     => $_ENV['NOT_COLOR_GROUP'],
            'group_type'         => 'radio',
            'position'           => $attribute->getPosition(),
        ];
    }

    /**
     * @param ProductAttribute $attribute
     * @return array
     */
    public static function getAttributeGroupLangData($attribute)
    {
        return [
            'id_attribute_group' => $attribute->getId(),
            'id_lang'            => $_ENV['LANG_ID'],
            'name'               => $attribute->getName(),
            'public_name'        => ucfirst(mb_strtolower($attribute->getName())),
        ];
    }

    /**
     * @param ProductAttribute $attribute
     * @return array
     */
    public static function getMlAttributeShopData($attribute)
    {
        return [
            'id_attribute' => $attribute->getId(),
            'id_shop'      => $_ENV['SHOP_ID'],
        ];
    }

    /**
     * @param ProductAttribute $attribute
     * @return array
     */
    public static function getMlAttributeLangData($attribute)
    {
        return [
            'id_attribute' => $attribute->getId(),
            'id_lang'      => $_ENV['LANG_ID'],
            'name'         => $attribute->getName(),
        ];
    }

    /**
     * @param $groupId
     * @param ProductAttribute $attribute
     * @return array
     */
    public static function getMlAttributeData($groupId, $attribute)
    {
        return [
            'id_attribute'       => $attribute->getId(),
            'id_attribute_group' => $groupId,
            'color'              => $_ENV['NOT_COLOR_GROUP'],
            'position'           => $attribute->getPosition(),
        ];
    }
}
