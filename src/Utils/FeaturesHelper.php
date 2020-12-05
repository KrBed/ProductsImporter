<?php

namespace ProductsImporter\Utils;

use ProductsImporter\Classes\Feature;

class FeaturesHelper {
    public static function CreateFeaturesObjects($featuresArray)
    {
        $features = [];
        foreach ($featuresArray as $featureType => $featureNames) {

            $feature = new Feature($featureType);
            foreach ($featureNames as $name) {
                $child = new Feature($name);
                $feature->addChild($child);
            }
            $features[] = $feature;
        }

        return $features;
    }

    /**
     * return filtered Product features
     * @param $productFeatures
     * @return array
     */
    public static function filterProductFeatures($productFeatures)
    {
        $features = Registry::get('features');
        $featureObjects = [];
        foreach ($productFeatures as $key => $value) {
            $feature = self::findFeature($features, $key);
            $featureValue = self::getFeatureValueObject($feature, $value);
            if (!empty($featureValue)) {
                $featureObjects[] = $featureValue;
            }
        }

        return $featureObjects;
    }

    /**
     * @param  Feature[]  $features
     * @param $key
     * @return array|mixed
     */
    public static function findFeature($features, $key)
    {
        foreach ($features as $feature) {
            if ($feature->getName() === $key) {
                return $feature;
            }
        }
    }

    /**
     * @param  Feature  $feature
     * @param $value
     * @return array|mixed
     */
    private static function getFeatureValueObject($feature, $value)
    {
        foreach ($feature->getChilds() as $child) {
            if ($child->getName() === $value) {
                $child->setParentId($feature->getId());

                return $child;
            }
        }

        return [];
    }

    /**
     * @param $feature
     * @return array
     */
    public static function getMlFeatureShopData($feature)
    {
        return [
            'id_feature' => $feature->getId(),
            'id_shop'    => $_ENV['SHOP_ID'],
        ];
    }

    /**
     * @param $feature
     * @return array
     */
    public static function getMlFeatureData($feature)
    {
        return [
            'id_feature' => $feature->getId(),
            'position'   => $feature->getPosition(),
        ];
    }

    /**
     * @param $feature
     * @return array
     */
    public static function getMlFeatureLangData($feature)
    {
        return [
            'id_feature' => $feature->getId(),
            'id_lang'    => $_ENV['LANG_ID'],
            'name'       => $feature->getName(),
        ];
    }

    /**
     * @param  Feature  $featureValue
     * @return array
     */
    public static function getMlFeatureValueLangData($featureValue)
    {
        return [
            'id_feature_value' => $featureValue->getId(),
            'id_lang'          => $_ENV['LANG_ID'],
            'value'            => $featureValue->getName(),
        ];
    }

    /**
     * @param  Feature  $featureType
     * @param  Feature  $featureValue
     * @return array
     */
    public static function getMlFeatureValueData($featureType, $featureValue)
    {
        return [
            'id_feature_value' => $featureValue->getId(),
            'id_feature'       => $featureType->getId(),
            'custom'           => $_ENV['NOT_CUSTOM'],
        ];
    }
}

