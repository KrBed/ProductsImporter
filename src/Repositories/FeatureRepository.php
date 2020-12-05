<?php

namespace ProductsImporter\Repositories;

use ProductsImporter\Classes\Feature;
use ProductsImporter\Utils\FeaturesHelper;
use ProductsImporter\Utils\Registry;

class FeatureRepository extends RepositoryCore {

    /**
     * @param  Feature[]  $features
     * @return mixed
     */
    public function addFeaturesIfNotExists($features)
    {
        self::$db->beginTransaction();
        foreach ($features as $feature) {
            $feature = $this->addNewFeatureTypeIfNotExists($feature);
            foreach ($feature->getChilds() as $child) {
                $this->addNewFeatureValue($feature, $child);
            }
        }
        self::$db->commit();

        return $features;
    }

    /**
     * adds new feature to Db
     * @param  Feature  $feature
     * @return Feature
     */
    private function addNewFeatureTypeIfNotExists($feature)
    {
        $exists = $this->checkExist("{$_ENV['MYSQL_PREFIX']}feature_lang", ['name' => $feature->getName()]);
        if ($exists) {
            Registry::bind($feature->getName(), $exists->data['id_feature']);
            $feature->setId($exists->data['id_feature']);

            return $feature;
        }

        $values = $this->getMaxColumnValuesFromTable("{$_ENV['MYSQL_PREFIX']}feature", [
            'id_feature',
            'position',
        ]);

        $feature->setId($values['id_feature'] + 1);
        $feature->setPosition($values['position'] + 1);
        //sets feature data to insert to database
        $featureData = FeaturesHelper::getMlFeatureData($feature);
        $featureLangData = FeaturesHelper::getMlFeatureLangData($feature);
        $featureShopData = FeaturesHelper::getMlFeatureShopData($feature);
        //inserts data to database
        $this->insert("{$_ENV['MYSQL_PREFIX']}feature", $featureData);
        $this->insert("{$_ENV['MYSQL_PREFIX']}feature_lang", $featureLangData);
        $this->insert("{$_ENV['MYSQL_PREFIX']}feature_shop", $featureShopData);

        Registry::bind($feature->getName(), $feature->getId());

        return $feature;
    }

    /**
     * adds new feature type to Db
     * @param  Feature  $featureType
     * @param  Feature  $featureValue
     */
    private function addNewFeatureValue($featureType, $featureValue)
    {
        $exists = $this->checkIfFeatureValueExists($featureType->getId(), $featureValue);

        if ($exists) {
            $featureValue->setId($exists['id_feature_value']);

            return;
        }
        $featureValueId = $this->getMaxIdFromTable("{$_ENV['MYSQL_PREFIX']}feature_value", 'id_feature_value') + 1;
        $featureValue->setId($featureValueId);
        $mlFeatureValueData = FeaturesHelper::getMlFeatureValueData($featureType, $featureValue);
        $mlFeatureValueLangData = FeaturesHelper::getMlFeatureValueLangData($featureValue);
        $this->insert("{$_ENV['MYSQL_PREFIX']}feature_value", $mlFeatureValueData);
        $this->insert("{$_ENV['MYSQL_PREFIX']}feature_value_lang", $mlFeatureValueLangData);
    }

    /**
     * @param  int  $getId
     * @param  Feature  $child
     * @return mixed
     */
    public function checkIfFeatureValueExists($parentId, $child)
    {
        $features = $this->searchFeaturesByNameAndLang($child);

        foreach ($features as $feature) {
            $result = $this->getFeatureParentId($parentId, $feature['id_feature_value']);
            if ($result['id_feature'] === $parentId) {
                return $feature;
            }
        }

        return false;
    }

    /**
     * search for features with same name and lang in Db
     * @param  Feature  $child
     * @return array
     */
    private function searchFeaturesByNameAndLang($child)
    {
        $data = [
            'value'   => $child->getName(),
            'id_lang' => $_ENV['LANG_ID'],
        ];
        $statement = self::$db->prepare("SELECT * FROM {$_ENV['MYSQL_PREFIX']}feature_value_lang WHERE value=:value AND id_lang=:id_lang");
        $statement->execute($data);

        return $statement->fetchAll();
    }

    /**
     * search for feature parentId in database
     * @param  int  $parentId
     * @param  int  $childId
     * @return mixed
     */
    public function getFeatureParentId($parentId, $childId)
    {
        $data = [
            'id_feature_value' => $childId,
            'id_feature'       => $parentId,
        ];

        $statement = self::$db->prepare("SELECT * FROM {$_ENV['MYSQL_PREFIX']}feature_value WHERE id_feature_value=:id_feature_value AND id_feature=:id_feature");
        $statement->execute($data);

        return $statement->fetch();
    }

    /**
     * adds all Product features to Db
     * @param $productId
     * @param $productFeatures
     */
    public function addProductFeatures($productId, $productFeatures)
    {
        foreach ($productFeatures as $feature) {
            $this->addSingleProductFeature($productId, $feature);
        }
    }

    /**
     * adds single product feature to Db
     * @param  int  $productId
     * @param  Feature  $feature
     */
    public function addSingleProductFeature($productId, $feature)
    {
        $data = [
            'id_feature'       => $feature->getParentId(),
            'id_feature_value' => $feature->getId(),
            'id_product'       => $productId,
        ];
        $this->insert("{$_ENV['MYSQL_PREFIX']}feature_product", $data);
    }


}




