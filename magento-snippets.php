<?php

if (!(sizeof($argv) > 1)) {
    echo "Please specify a command.\n";
    exit(-1);
}
$command = $argv[1];
echo "Running command: $command.\n";

$magentoDir = getenv('MAGE_DIR') ? getenv('MAGE_DIR') : '.';
if (empty($magentoDir)) {
    echo "Please specify a Magento installation. MAGE_DIR=blahblah \n";
    exit(-1);
}

$memory = '2048M';
ini_set('memory_limit',$memory);

require $magentoDir."/app/Mage.php";

echo "Initiating Magento app in $magentoDir\n\n";
Mage::app();

switch ($command) {
    case "getRootCategories":
        getRootCategories();
        break;
    case "getBaseUrl":
        getBaseUrl();
        break;
    case "getProductById":
        getProductById($argv[2]);
        break;
    case "getProductBySku":
        getProductBySku($argv[2]);
        break;
    case "getProductsByCategoryId":
        getProductsByCategoryId($argv[2]);
        break;
    case "getConfigurableFromSimpleById":
        getConfigurableFromSimpleById($argv[2]);
        break;
    case "getAttributeSets":
        getAttributeSets();
        break;
    case "changeIndexingMode":
        changeIndexingMode($argv[2]);
        break;
    case "getAllVisibleSkus":
        getAllVisibleSkus();
        break;
    default:
        echo "Please enter a valid command\n";
}
echo "\n";

// -------------------------------------------------
function print_hr() { echo "--------------------------------------------------\n"; }
function getRootCategories() {
    $rootCategoryId = Mage::app()->getStore()->getRootCategoryId();
    echo "Root category Id: $rootCategoryId\n";
    $_category = Mage::getModel('catalog/category')->load($rootCategoryId);
    $_subcategories = $_category->getChildrenCategories();
    echo "Subcategories: \n";
    foreach ($_subcategories as $sc) {
        echo " " . implode(', ', array($sc->getId(), $sc->getName())) . "\n";
    }
    echo "\n";
}

function getBaseUrl() {
    echo "Web:   " . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . "\n";
    echo "JS:    " . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS) . "\n";
    echo "LINK:  " . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK) . "\n";
    echo "MEDIA: " . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . "\n";
    echo "SKIN:  " . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) . "\n";
}

function getProductById($id) {
    $product = Mage::getModel('catalog/product')->load($id);
    print_r($product);
}

function getProductBySku($sku) {
    $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
    print_r($product);
}

function getProductsByCategoryId($id) {
    $category = Mage::getModel('catalog/category')->load($id);
    $productCollection = $category->getProductCollection();
    echo "Category Id: $id\n";
    print_r($category);
    echo "Product Collection Count: " . $productCollection->count() . "\n";
    if ($productCollection->count()) {
        foreach ($productCollection as $product) {
            print_hr();
            getProductBySku($product->getSku());
            echo "\n";
        }
    }
}

function getConfigurableFromSimpleById($id) {
    echo "Simple: \n";
    getProductById($id);

    $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')
        ->getParentIdsByChild($id);
    $product = Mage::getModel('catalog/product')->load($parentIds[0]);

    print_hr();
    echo "Configurable: \n";
    print_r($product);
}

function getAttributeSets() {
    $attributeSetCollection = Mage::getResourceModel('eav/entity_attribute_set_collection')->load();
    foreach ($attributeSetCollection as $attributeSet) {
        print_hr();
        print_r($attributeSet);
    }
}

function changeIndexingMode($mode) {
    $pCollection = Mage::getSingleton('index/indexer')->getProcessesCollection();
    foreach ($pCollection as $process) {
        echo "Setting " . $process->getIndexerCode() . " to ";
        if ($mode == 'manual') {
            echo "manual";
            $process->setMode(Mage_Index_Model_Process::MODE_MANUAL)->save();
        }
        else {
            echo "realtime";
            $process->setMode(Mage_Index_Model_Process::MODE_REAL_TIME)->save();
        }
        echo "\n";
    }
}

function getAllVisibleSkus() {
    $productModel = Mage::getModel('catalog/product')
        ->getCollection()
        ->addAttributeToSelect('sku')
        ->addFieldToFilter('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);

    foreach ($productModel as $product) {
        echo $product->getSku() . "\n";
    }
}