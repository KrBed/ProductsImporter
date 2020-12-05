<?php

namespace ProductsImporter\Repositories;

use Exception;
use PDOException;
use ProductsImporter\Classes\Product;
use ProductsImporter\Classes\ProductAttribute;
use ProductsImporter\Utils\AppHelper;
use ProductsImporter\Utils\AttributesHelper;

class AttributesRepository extends RepositoryCore {

    /**
     * adds attribute names for each attribute group
     * @param  ProductAttribute[]  $attributes
     * @return array
     * @throws Exception
     */
    public function addAttributesIfNotExists(array $attributes)
    {
        self::$db->beginTransaction();
        try {
            //adds attribute names for each attribute group
            foreach ($attributes as $attributeGroup) {
                $this->addNewAttributeGroupIfNotExists($attributeGroup);
                foreach ($attributeGroup->getChilds() as $attributeName) {
                    $this->addNewAttributeName($attributeGroup->getId(), $attributeName);
                }
            }
        } catch (Exception $exception) {
            throw $exception;
        }

        self::$db->commit();

        return $attributes;
    }

    /**
     * adds new attribute group to database
     * @param  ProductAttribute  $attribute
     * @throws Exception
     */
    public function addNewAttributeGroupIfNotExists($attribute)
    {
        $exists = $this->checkExist("{$_ENV['MYSQL_PREFIX']}attribute_group_lang", ['public_name' => $attribute->getName()]);

        if ($exists) {
            $attribute->setId($exists->data['id_attribute_group']);

            return;
        }

        $values = $this->getMaxColumnValuesFromTable("{$_ENV['MYSQL_PREFIX']}attribute_group", [
            "id_attribute_group",
            'position',
        ]);

        $attribute->setId($values['id_attribute_group'] + 1);
        $attribute->setPosition($values['position'] + 1);
        //creates to insert into database
        $attributeGroupData = AttributesHelper::getAttributeGroupData($attribute);
        $attributeGroupLangData = AttributesHelper::getAttributeGroupLangData($attribute);
        $attributeGroupShopData = AttributesHelper::getAttributeGroupShopData($attribute);
        //inserts data to database
        $this->insert("{$_ENV['MYSQL_PREFIX']}attribute_group", $attributeGroupData);
        $this->insert("{$_ENV['MYSQL_PREFIX']}attribute_group_lang", $attributeGroupLangData);
        $this->insert("{$_ENV['MYSQL_PREFIX']}attribute_group_shop", $attributeGroupShopData);

    }


    /**
     * @param $groupId
     * @param  ProductAttribute  $attribute
     */
    public function addNewAttributeName($groupId, $attribute)
    {
        //check if name attribute with specyfic name exists
        $exists = $this->checkAttributeExist($groupId, ['name' => $attribute->getName()]);

        if ($exists) {
            $attribute->setId($exists['id_attribute']);
            $attribute->setPosition($exists['position']);

            return;
        }

        $values = $this->getMaxColumnValuesFromTable("{$_ENV['MYSQL_PREFIX']}attribute", [
            "id_attribute",
            'position',
        ], ['id_attribute_group' => $groupId]);

        if (is_null($values['id_attribute']) && is_null($values['position'])) {
            $id = $this->getMaxIdFromTable("{$_ENV['MYSQL_PREFIX']}attribute", 'id_attribute') + 1;
            $attribute->setId($id);
            $attribute->setPosition(0);
        } else {
            $attribute->setId($values['id_attribute'] + 1);
            $attribute->setPosition($values['position'] + 1);
        }

        // sets data for insert int database
        $mlAttributeData = AttributesHelper::getMlAttributeData($groupId, $attribute);
        $mlAttributeLangData = AttributesHelper::getMlAttributeLangData($attribute);
        $mlAttributeShopData = AttributesHelper::getMlAttributeShopData($attribute);
        // insert data to database
        $this->insert("{$_ENV['MYSQL_PREFIX']}attribute", $mlAttributeData);
        $this->insert("{$_ENV['MYSQL_PREFIX']}attribute_lang", $mlAttributeLangData);
        $this->insert("{$_ENV['MYSQL_PREFIX']}attribute_shop", $mlAttributeShopData);
    }


    /**
     * @param $groupId  // Id attribute group
     * @param $data  // assotiative table column => value to search for
     * @return array|bool
     */
    private function checkAttributeExist($groupId, $data)
    {
        $attibutes = $this->search("{$_ENV['MYSQL_PREFIX']}attribute_lang", $data);
        foreach ($attibutes as $attibute) {
            $result = $this->search("{$_ENV['MYSQL_PREFIX']}attribute", ['id_attribute' => $attibute['id_attribute']]);
            if ($result[0]['id_attribute_group'] === $groupId) {
                return reset($result);
            }
        }

        return false;
    }

    /**
     * adds attributes  to Db
     * @param $product
     * @throws \Exception
     */
    public function addAttributes($product)
    {
        $this->addProductAsAttribute($product);
        $this->addProductAttributes($product);
    }

    /**
     * Ads main product to Db as attribute
     * @param  Product  $product
     * @throws Exception
     */
    private function addProductAsAttribute($product)
    {
        $attribute = $this->getAttributeId("{$_ENV['MYSQL_PREFIX']}attribute_lang", $product->getFittingStandard());
        $attributeId = $attribute['id_attribute'];
        $productAttributeId = $this->getMaxIdFromTable("{$_ENV['MYSQL_PREFIX']}product_attribute", 'id_product_attribute') + 1;
        $product->setAttributeId($productAttributeId);
        $mlProductAttributeData = [
            'id_product_attribute' => $product->getAttributeId(),
            'id_product'           => $product->getId(),
            'reference'            => $product->getIndexLupus(),
            'ean13'                => $product->getEAN(),
            'upc'                  => $product->getUpc(),
            'price'                => 0,
            'unit_price_impact'    => 0,
            'weight'               => $product->getWeight(),
            'default_on'           => $_ENV['DEFAULT_ON'],
            'minimal_quantity'     => $_ENV['MINIMAL_QUANTITY'],
            'available_date'       => AppHelper::getActualDate(),
        ];
        $mlProductAttributeShopData = [
            'id_product_attribute' => $product->getAttributeId(),
            'id_product'           => $product->getId(),
            'id_shop'              => $_ENV['SHOP_ID'],
            'price'                => 0,
            'weight'               => $product->getWeight(),
            'unit_price_impact'    => 0,
            'default_on'           => 1,
            'minimal_quantity'     => $_ENV['MINIMAL_QUANTITY'],
            'available_date'       => AppHelper::getActualDate(),
        ];
        $mlProductAttributeCombinationData = [
            'id_attribute'         => $attributeId,
            'id_product_attribute' => $productAttributeId,
        ];
        $this->insert("{$_ENV['MYSQL_PREFIX']}product_attribute", $mlProductAttributeData);
        $this->insert("{$_ENV['MYSQL_PREFIX']}product_attribute_combination", $mlProductAttributeCombinationData);
        $this->insert("{$_ENV['MYSQL_PREFIX']}product_attribute_shop", $mlProductAttributeShopData);
    }

    /**
     * @param $table
     * @param $fittingName
     * @return mixed
     */
    private function getAttributeId($table, $fittingName)
    {
        try {
            $statement = self::$db->query("SELECT id_attribute FROM $table WHERE name='$fittingName'");

            return $statement->fetch();
        } catch (PDOException $exception) {
            $this->setErrorMessage($exception->getMessage());
            throw $exception;

        }
    }

    /**
     * addsProduct Attributes
     * @param  Product  $product
     * @throws \Exception
     */
    public function addProductAttributes($product)
    {

        foreach ($product->getAttributes() as $productAttribute) {

            $attribute = $this->getAttributeId("{$_ENV['MYSQL_PREFIX']}attribute_lang", $productAttribute->getFittingStandard());
            $attributeId = $attribute['id_attribute'];

            $productAttributeId = $this->getMaxIdFromTable("{$_ENV['MYSQL_PREFIX']}product_attribute", 'id_product_attribute') + 1;
            $productAttribute->setId($product->getId());
            $productAttribute->setAttributeId($productAttributeId);

            $mlProductAttributeCombinationData = [
                'id_attribute'         => $attributeId,
                'id_product_attribute' => $productAttributeId,
            ];
            $mlProductAttributeData = [
                'id_product_attribute' => $productAttribute->getAttributeId(),
                'id_product'           => $product->getId(),
                'reference'            => $productAttribute->getIndexLupus(),
                'ean13'                => $productAttribute->getEAN(),
                'upc'                  => $productAttribute->getUpc(),
                'price'                => $productAttribute->getNettoPriceEXWPLN() - $product->getNettoPriceEXWPLN(),
                'weight'               => $productAttribute->getWeight(),
                'default_on'           => null,
                'minimal_quantity'     => $_ENV['MINIMAL_QUANTITY'],
                'available_date'       => AppHelper::getActualDate(),
            ];

            $mlProductAttributeShopData = [
                'id_product_attribute' => $productAttribute->getAttributeId(),
                'id_product'           => $product->getId(),
                'id_shop'              => $_ENV['SHOP_ID'],
                'price'                => $productAttribute->getNettoPriceEXWPLN() - $product->getNettoPriceEXWPLN(),
                'weight'               => $productAttribute->getWeight(),
                'default_on'           => null,
                'minimal_quantity'     => $_ENV['MINIMAL_QUANTITY'],
                'available_date'       => AppHelper::getActualDate(),
            ];
            $this->insert("{$_ENV['MYSQL_PREFIX']}product_attribute_combination", $mlProductAttributeCombinationData);
            $this->insert("{$_ENV['MYSQL_PREFIX']}product_attribute", $mlProductAttributeData);
            $this->insert("{$_ENV['MYSQL_PREFIX']}product_attribute_shop", $mlProductAttributeShopData);
        }
    }
}
