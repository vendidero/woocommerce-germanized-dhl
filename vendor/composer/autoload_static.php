<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit9527b24e18b1b83900e58bc8897bc773
{
    public static $files = array (
        '6124b4c8570aa390c21fafd04a26c69f' => __DIR__ . '/..' . '/myclabs/deep-copy/src/DeepCopy/deep_copy.php',
    );

    public static $prefixLengthsPsr4 = array (
        'b' => 
        array (
            'baltpeter\\Internetmarke\\' => 24,
        ),
        'W' => 
        array (
            'WsdlToPhp\\WsSecurity\\' => 21,
        ),
        'V' => 
        array (
            'Vendidero\\Germanized\\DHL\\' => 25,
        ),
        'D' => 
        array (
            'DeepCopy\\' => 9,
        ),
        'A' => 
        array (
            'Automattic\\Jetpack\\Autoloader\\' => 30,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'baltpeter\\Internetmarke\\' => 
        array (
            0 => __DIR__ . '/..' . '/baltpeter/internetmarke-php/src/baltpeter/Internetmarke',
        ),
        'WsdlToPhp\\WsSecurity\\' => 
        array (
            0 => __DIR__ . '/..' . '/wsdltophp/wssecurity/src',
        ),
        'Vendidero\\Germanized\\DHL\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'DeepCopy\\' => 
        array (
            0 => __DIR__ . '/..' . '/myclabs/deep-copy/src/DeepCopy',
        ),
        'Automattic\\Jetpack\\Autoloader\\' => 
        array (
            0 => __DIR__ . '/..' . '/automattic/jetpack-autoloader/src',
        ),
    );

    public static $classMap = array (
        'Automattic\\Jetpack\\Autoloader\\AutoloadFileWriter' => __DIR__ . '/..' . '/automattic/jetpack-autoloader/src/AutoloadFileWriter.php',
        'Automattic\\Jetpack\\Autoloader\\AutoloadGenerator' => __DIR__ . '/..' . '/automattic/jetpack-autoloader/src/AutoloadGenerator.php',
        'Automattic\\Jetpack\\Autoloader\\AutoloadProcessor' => __DIR__ . '/..' . '/automattic/jetpack-autoloader/src/AutoloadProcessor.php',
        'Automattic\\Jetpack\\Autoloader\\CustomAutoloaderPlugin' => __DIR__ . '/..' . '/automattic/jetpack-autoloader/src/CustomAutoloaderPlugin.php',
        'Automattic\\Jetpack\\Autoloader\\ManifestGenerator' => __DIR__ . '/..' . '/automattic/jetpack-autoloader/src/ManifestGenerator.php',
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'DeepCopy\\DeepCopy' => __DIR__ . '/..' . '/myclabs/deep-copy/src/DeepCopy/DeepCopy.php',
        'DeepCopy\\Exception\\CloneException' => __DIR__ . '/..' . '/myclabs/deep-copy/src/DeepCopy/Exception/CloneException.php',
        'DeepCopy\\Exception\\PropertyException' => __DIR__ . '/..' . '/myclabs/deep-copy/src/DeepCopy/Exception/PropertyException.php',
        'DeepCopy\\Filter\\Doctrine\\DoctrineCollectionFilter' => __DIR__ . '/..' . '/myclabs/deep-copy/src/DeepCopy/Filter/Doctrine/DoctrineCollectionFilter.php',
        'DeepCopy\\Filter\\Doctrine\\DoctrineEmptyCollectionFilter' => __DIR__ . '/..' . '/myclabs/deep-copy/src/DeepCopy/Filter/Doctrine/DoctrineEmptyCollectionFilter.php',
        'DeepCopy\\Filter\\Doctrine\\DoctrineProxyFilter' => __DIR__ . '/..' . '/myclabs/deep-copy/src/DeepCopy/Filter/Doctrine/DoctrineProxyFilter.php',
        'DeepCopy\\Filter\\Filter' => __DIR__ . '/..' . '/myclabs/deep-copy/src/DeepCopy/Filter/Filter.php',
        'DeepCopy\\Filter\\KeepFilter' => __DIR__ . '/..' . '/myclabs/deep-copy/src/DeepCopy/Filter/KeepFilter.php',
        'DeepCopy\\Filter\\ReplaceFilter' => __DIR__ . '/..' . '/myclabs/deep-copy/src/DeepCopy/Filter/ReplaceFilter.php',
        'DeepCopy\\Filter\\SetNullFilter' => __DIR__ . '/..' . '/myclabs/deep-copy/src/DeepCopy/Filter/SetNullFilter.php',
        'DeepCopy\\Matcher\\Doctrine\\DoctrineProxyMatcher' => __DIR__ . '/..' . '/myclabs/deep-copy/src/DeepCopy/Matcher/Doctrine/DoctrineProxyMatcher.php',
        'DeepCopy\\Matcher\\Matcher' => __DIR__ . '/..' . '/myclabs/deep-copy/src/DeepCopy/Matcher/Matcher.php',
        'DeepCopy\\Matcher\\PropertyMatcher' => __DIR__ . '/..' . '/myclabs/deep-copy/src/DeepCopy/Matcher/PropertyMatcher.php',
        'DeepCopy\\Matcher\\PropertyNameMatcher' => __DIR__ . '/..' . '/myclabs/deep-copy/src/DeepCopy/Matcher/PropertyNameMatcher.php',
        'DeepCopy\\Matcher\\PropertyTypeMatcher' => __DIR__ . '/..' . '/myclabs/deep-copy/src/DeepCopy/Matcher/PropertyTypeMatcher.php',
        'DeepCopy\\Reflection\\ReflectionHelper' => __DIR__ . '/..' . '/myclabs/deep-copy/src/DeepCopy/Reflection/ReflectionHelper.php',
        'DeepCopy\\TypeFilter\\Date\\DateIntervalFilter' => __DIR__ . '/..' . '/myclabs/deep-copy/src/DeepCopy/TypeFilter/Date/DateIntervalFilter.php',
        'DeepCopy\\TypeFilter\\ReplaceFilter' => __DIR__ . '/..' . '/myclabs/deep-copy/src/DeepCopy/TypeFilter/ReplaceFilter.php',
        'DeepCopy\\TypeFilter\\ShallowCopyFilter' => __DIR__ . '/..' . '/myclabs/deep-copy/src/DeepCopy/TypeFilter/ShallowCopyFilter.php',
        'DeepCopy\\TypeFilter\\Spl\\ArrayObjectFilter' => __DIR__ . '/..' . '/myclabs/deep-copy/src/DeepCopy/TypeFilter/Spl/ArrayObjectFilter.php',
        'DeepCopy\\TypeFilter\\Spl\\SplDoublyLinkedList' => __DIR__ . '/..' . '/myclabs/deep-copy/src/DeepCopy/TypeFilter/Spl/SplDoublyLinkedList.php',
        'DeepCopy\\TypeFilter\\Spl\\SplDoublyLinkedListFilter' => __DIR__ . '/..' . '/myclabs/deep-copy/src/DeepCopy/TypeFilter/Spl/SplDoublyLinkedListFilter.php',
        'DeepCopy\\TypeFilter\\TypeFilter' => __DIR__ . '/..' . '/myclabs/deep-copy/src/DeepCopy/TypeFilter/TypeFilter.php',
        'DeepCopy\\TypeMatcher\\TypeMatcher' => __DIR__ . '/..' . '/myclabs/deep-copy/src/DeepCopy/TypeMatcher/TypeMatcher.php',
        'Vendidero\\Germanized\\DHL\\Admin\\Admin' => __DIR__ . '/../..' . '/src/Admin/Admin.php',
        'Vendidero\\Germanized\\DHL\\Admin\\Importer\\DHL' => __DIR__ . '/../..' . '/src/Admin/Importer/DHL.php',
        'Vendidero\\Germanized\\DHL\\Admin\\Importer\\Internetmarke' => __DIR__ . '/../..' . '/src/Admin/Importer/Internetmarke.php',
        'Vendidero\\Germanized\\DHL\\Admin\\Status' => __DIR__ . '/../..' . '/src/Admin/Status.php',
        'Vendidero\\Germanized\\DHL\\Ajax' => __DIR__ . '/../..' . '/src/Ajax.php',
        'Vendidero\\Germanized\\DHL\\Api\\AuthSoap' => __DIR__ . '/../..' . '/src/Api/AuthSoap.php',
        'Vendidero\\Germanized\\DHL\\Api\\FinderSoap' => __DIR__ . '/../..' . '/src/Api/FinderSoap.php',
        'Vendidero\\Germanized\\DHL\\Api\\ImPartnerInformation' => __DIR__ . '/../..' . '/src/Api/ImPartnerInformation.php',
        'Vendidero\\Germanized\\DHL\\Api\\ImProductList' => __DIR__ . '/../..' . '/src/Api/ImProductList.php',
        'Vendidero\\Germanized\\DHL\\Api\\ImProductsSoap' => __DIR__ . '/../..' . '/src/Api/ImProductsSoap.php',
        'Vendidero\\Germanized\\DHL\\Api\\ImRefundSoap' => __DIR__ . '/../..' . '/src/Api/ImRefundSoap.php',
        'Vendidero\\Germanized\\DHL\\Api\\ImWarenpostIntRest' => __DIR__ . '/../..' . '/src/Api/ImWarenpostIntRest.php',
        'Vendidero\\Germanized\\DHL\\Api\\Internetmarke' => __DIR__ . '/../..' . '/src/Api/Internetmarke.php',
        'Vendidero\\Germanized\\DHL\\Api\\LabelSoap' => __DIR__ . '/../..' . '/src/Api/LabelSoap.php',
        'Vendidero\\Germanized\\DHL\\Api\\Paket' => __DIR__ . '/../..' . '/src/Api/Paket.php',
        'Vendidero\\Germanized\\DHL\\Api\\ParcelRest' => __DIR__ . '/../..' . '/src/Api/ParcelRest.php',
        'Vendidero\\Germanized\\DHL\\Api\\Rest' => __DIR__ . '/../..' . '/src/Api/Rest.php',
        'Vendidero\\Germanized\\DHL\\Api\\ReturnRest' => __DIR__ . '/../..' . '/src/Api/ReturnRest.php',
        'Vendidero\\Germanized\\DHL\\Api\\Soap' => __DIR__ . '/../..' . '/src/Api/Soap.php',
        'Vendidero\\Germanized\\DHL\\Install' => __DIR__ . '/../..' . '/src/Install.php',
        'Vendidero\\Germanized\\DHL\\Label\\DHL' => __DIR__ . '/../..' . '/src/Label/DHL.php',
        'Vendidero\\Germanized\\DHL\\Label\\DHLInlayReturn' => __DIR__ . '/../..' . '/src/Label/DHLInlayReturn.php',
        'Vendidero\\Germanized\\DHL\\Label\\DHLReturn' => __DIR__ . '/../..' . '/src/Label/DHLReturn.php',
        'Vendidero\\Germanized\\DHL\\Label\\DeutschePost' => __DIR__ . '/../..' . '/src/Label/DeutschePost.php',
        'Vendidero\\Germanized\\DHL\\Label\\DeutschePostReturn' => __DIR__ . '/../..' . '/src/Label/DeutschePostReturn.php',
        'Vendidero\\Germanized\\DHL\\Label\\Label' => __DIR__ . '/../..' . '/src/Label/Label.php',
        'Vendidero\\Germanized\\DHL\\Label\\ReturnLabel' => __DIR__ . '/../..' . '/src/Label/ReturnLabel.php',
        'Vendidero\\Germanized\\DHL\\Legacy\\DataStores\\Label' => __DIR__ . '/../..' . '/src/Legacy/DataStores/Label.php',
        'Vendidero\\Germanized\\DHL\\Legacy\\DownloadHandler' => __DIR__ . '/../..' . '/src/Legacy/DownloadHandler.php',
        'Vendidero\\Germanized\\DHL\\Legacy\\LabelFactory' => __DIR__ . '/../..' . '/src/Legacy/LabelFactory.php',
        'Vendidero\\Germanized\\DHL\\Legacy\\LabelQuery' => __DIR__ . '/../..' . '/src/Legacy/LabelQuery.php',
        'Vendidero\\Germanized\\DHL\\Order' => __DIR__ . '/../..' . '/src/Order.php',
        'Vendidero\\Germanized\\DHL\\Package' => __DIR__ . '/../..' . '/src/Package.php',
        'Vendidero\\Germanized\\DHL\\ParcelLocator' => __DIR__ . '/../..' . '/src/ParcelLocator.php',
        'Vendidero\\Germanized\\DHL\\ParcelServices' => __DIR__ . '/../..' . '/src/ParcelServices.php',
        'Vendidero\\Germanized\\DHL\\Product' => __DIR__ . '/../..' . '/src/Product.php',
        'Vendidero\\Germanized\\DHL\\ShippingProvider\\DHL' => __DIR__ . '/../..' . '/src/ShippingProvider/DHL.php',
        'Vendidero\\Germanized\\DHL\\ShippingProvider\\DeutschePost' => __DIR__ . '/../..' . '/src/ShippingProvider/DeutschePost.php',
        'Vendidero\\Germanized\\DHL\\ShippingProvider\\ShippingMethod' => __DIR__ . '/../..' . '/src/ShippingProvider/ShippingMethod.php',
        'WsdlToPhp\\WsSecurity\\Created' => __DIR__ . '/..' . '/wsdltophp/wssecurity/src/Created.php',
        'WsdlToPhp\\WsSecurity\\Element' => __DIR__ . '/..' . '/wsdltophp/wssecurity/src/Element.php',
        'WsdlToPhp\\WsSecurity\\Expires' => __DIR__ . '/..' . '/wsdltophp/wssecurity/src/Expires.php',
        'WsdlToPhp\\WsSecurity\\Nonce' => __DIR__ . '/..' . '/wsdltophp/wssecurity/src/Nonce.php',
        'WsdlToPhp\\WsSecurity\\Password' => __DIR__ . '/..' . '/wsdltophp/wssecurity/src/Password.php',
        'WsdlToPhp\\WsSecurity\\Security' => __DIR__ . '/..' . '/wsdltophp/wssecurity/src/Security.php',
        'WsdlToPhp\\WsSecurity\\Timestamp' => __DIR__ . '/..' . '/wsdltophp/wssecurity/src/Timestamp.php',
        'WsdlToPhp\\WsSecurity\\Username' => __DIR__ . '/..' . '/wsdltophp/wssecurity/src/Username.php',
        'WsdlToPhp\\WsSecurity\\UsernameToken' => __DIR__ . '/..' . '/wsdltophp/wssecurity/src/UsernameToken.php',
        'WsdlToPhp\\WsSecurity\\WsSecurity' => __DIR__ . '/..' . '/wsdltophp/wssecurity/src/WsSecurity.php',
        'baltpeter\\Internetmarke\\Address' => __DIR__ . '/..' . '/baltpeter/internetmarke-php/src/baltpeter/Internetmarke/Address.php',
        'baltpeter\\Internetmarke\\AddressBinding' => __DIR__ . '/..' . '/baltpeter/internetmarke-php/src/baltpeter/Internetmarke/AddressBinding.php',
        'baltpeter\\Internetmarke\\ApiResult' => __DIR__ . '/..' . '/baltpeter/internetmarke-php/src/baltpeter/Internetmarke/ApiResult.php',
        'baltpeter\\Internetmarke\\CompanyName' => __DIR__ . '/..' . '/baltpeter/internetmarke-php/src/baltpeter/Internetmarke/CompanyName.php',
        'baltpeter\\Internetmarke\\LabelCount' => __DIR__ . '/..' . '/baltpeter/internetmarke-php/src/baltpeter/Internetmarke/LabelCount.php',
        'baltpeter\\Internetmarke\\LabelSpacing' => __DIR__ . '/..' . '/baltpeter/internetmarke-php/src/baltpeter/Internetmarke/LabelSpacing.php',
        'baltpeter\\Internetmarke\\Margin' => __DIR__ . '/..' . '/baltpeter/internetmarke-php/src/baltpeter/Internetmarke/Margin.php',
        'baltpeter\\Internetmarke\\Name' => __DIR__ . '/..' . '/baltpeter/internetmarke-php/src/baltpeter/Internetmarke/Name.php',
        'baltpeter\\Internetmarke\\NamedAddress' => __DIR__ . '/..' . '/baltpeter/internetmarke-php/src/baltpeter/Internetmarke/NamedAddress.php',
        'baltpeter\\Internetmarke\\OrderItem' => __DIR__ . '/..' . '/baltpeter/internetmarke-php/src/baltpeter/Internetmarke/OrderItem.php',
        'baltpeter\\Internetmarke\\PageFormat' => __DIR__ . '/..' . '/baltpeter/internetmarke-php/src/baltpeter/Internetmarke/PageFormat.php',
        'baltpeter\\Internetmarke\\PageLayout' => __DIR__ . '/..' . '/baltpeter/internetmarke-php/src/baltpeter/Internetmarke/PageLayout.php',
        'baltpeter\\Internetmarke\\PartnerInformation' => __DIR__ . '/..' . '/baltpeter/internetmarke-php/src/baltpeter/Internetmarke/PartnerInformation.php',
        'baltpeter\\Internetmarke\\PersonName' => __DIR__ . '/..' . '/baltpeter/internetmarke-php/src/baltpeter/Internetmarke/PersonName.php',
        'baltpeter\\Internetmarke\\PortokasseCharge' => __DIR__ . '/..' . '/baltpeter/internetmarke-php/src/baltpeter/Internetmarke/PortokasseCharge.php',
        'baltpeter\\Internetmarke\\Position' => __DIR__ . '/..' . '/baltpeter/internetmarke-php/src/baltpeter/Internetmarke/Position.php',
        'baltpeter\\Internetmarke\\PublicGalleryItem' => __DIR__ . '/..' . '/baltpeter/internetmarke-php/src/baltpeter/Internetmarke/PublicGalleryItem.php',
        'baltpeter\\Internetmarke\\Service' => __DIR__ . '/..' . '/baltpeter/internetmarke-php/src/baltpeter/Internetmarke/Service.php',
        'baltpeter\\Internetmarke\\Size' => __DIR__ . '/..' . '/baltpeter/internetmarke-php/src/baltpeter/Internetmarke/Size.php',
        'baltpeter\\Internetmarke\\StampPngResult' => __DIR__ . '/..' . '/baltpeter/internetmarke-php/src/baltpeter/Internetmarke/StampPngResult.php',
        'baltpeter\\Internetmarke\\User' => __DIR__ . '/..' . '/baltpeter/internetmarke-php/src/baltpeter/Internetmarke/User.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit9527b24e18b1b83900e58bc8897bc773::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit9527b24e18b1b83900e58bc8897bc773::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit9527b24e18b1b83900e58bc8897bc773::$classMap;

        }, null, ClassLoader::class);
    }
}
