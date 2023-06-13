<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2e1e83bdd18c153bc424f1b93c182f89
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
        'C' => 
        array (
            'Composer\\Installers\\' => 20,
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
        'Composer\\Installers\\' => 
        array (
            0 => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers',
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
        'Composer\\Installers\\AglInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/AglInstaller.php',
        'Composer\\Installers\\AimeosInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/AimeosInstaller.php',
        'Composer\\Installers\\AnnotateCmsInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/AnnotateCmsInstaller.php',
        'Composer\\Installers\\AsgardInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/AsgardInstaller.php',
        'Composer\\Installers\\AttogramInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/AttogramInstaller.php',
        'Composer\\Installers\\BaseInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/BaseInstaller.php',
        'Composer\\Installers\\BitrixInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/BitrixInstaller.php',
        'Composer\\Installers\\BonefishInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/BonefishInstaller.php',
        'Composer\\Installers\\CakePHPInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/CakePHPInstaller.php',
        'Composer\\Installers\\ChefInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/ChefInstaller.php',
        'Composer\\Installers\\CiviCrmInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/CiviCrmInstaller.php',
        'Composer\\Installers\\ClanCatsFrameworkInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/ClanCatsFrameworkInstaller.php',
        'Composer\\Installers\\CockpitInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/CockpitInstaller.php',
        'Composer\\Installers\\CodeIgniterInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/CodeIgniterInstaller.php',
        'Composer\\Installers\\Concrete5Installer' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/Concrete5Installer.php',
        'Composer\\Installers\\CraftInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/CraftInstaller.php',
        'Composer\\Installers\\CroogoInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/CroogoInstaller.php',
        'Composer\\Installers\\DecibelInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/DecibelInstaller.php',
        'Composer\\Installers\\DframeInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/DframeInstaller.php',
        'Composer\\Installers\\DokuWikiInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/DokuWikiInstaller.php',
        'Composer\\Installers\\DolibarrInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/DolibarrInstaller.php',
        'Composer\\Installers\\DrupalInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/DrupalInstaller.php',
        'Composer\\Installers\\ElggInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/ElggInstaller.php',
        'Composer\\Installers\\EliasisInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/EliasisInstaller.php',
        'Composer\\Installers\\ExpressionEngineInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/ExpressionEngineInstaller.php',
        'Composer\\Installers\\EzPlatformInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/EzPlatformInstaller.php',
        'Composer\\Installers\\FuelInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/FuelInstaller.php',
        'Composer\\Installers\\FuelphpInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/FuelphpInstaller.php',
        'Composer\\Installers\\GravInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/GravInstaller.php',
        'Composer\\Installers\\HuradInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/HuradInstaller.php',
        'Composer\\Installers\\ImageCMSInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/ImageCMSInstaller.php',
        'Composer\\Installers\\Installer' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/Installer.php',
        'Composer\\Installers\\ItopInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/ItopInstaller.php',
        'Composer\\Installers\\JoomlaInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/JoomlaInstaller.php',
        'Composer\\Installers\\KanboardInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/KanboardInstaller.php',
        'Composer\\Installers\\KirbyInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/KirbyInstaller.php',
        'Composer\\Installers\\KnownInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/KnownInstaller.php',
        'Composer\\Installers\\KodiCMSInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/KodiCMSInstaller.php',
        'Composer\\Installers\\KohanaInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/KohanaInstaller.php',
        'Composer\\Installers\\LanManagementSystemInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/LanManagementSystemInstaller.php',
        'Composer\\Installers\\LaravelInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/LaravelInstaller.php',
        'Composer\\Installers\\LavaLiteInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/LavaLiteInstaller.php',
        'Composer\\Installers\\LithiumInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/LithiumInstaller.php',
        'Composer\\Installers\\MODULEWorkInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/MODULEWorkInstaller.php',
        'Composer\\Installers\\MODXEvoInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/MODXEvoInstaller.php',
        'Composer\\Installers\\MagentoInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/MagentoInstaller.php',
        'Composer\\Installers\\MajimaInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/MajimaInstaller.php',
        'Composer\\Installers\\MakoInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/MakoInstaller.php',
        'Composer\\Installers\\MantisBTInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/MantisBTInstaller.php',
        'Composer\\Installers\\MauticInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/MauticInstaller.php',
        'Composer\\Installers\\MayaInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/MayaInstaller.php',
        'Composer\\Installers\\MediaWikiInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/MediaWikiInstaller.php',
        'Composer\\Installers\\MiaoxingInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/MiaoxingInstaller.php',
        'Composer\\Installers\\MicroweberInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/MicroweberInstaller.php',
        'Composer\\Installers\\ModxInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/ModxInstaller.php',
        'Composer\\Installers\\MoodleInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/MoodleInstaller.php',
        'Composer\\Installers\\OctoberInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/OctoberInstaller.php',
        'Composer\\Installers\\OntoWikiInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/OntoWikiInstaller.php',
        'Composer\\Installers\\OsclassInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/OsclassInstaller.php',
        'Composer\\Installers\\OxidInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/OxidInstaller.php',
        'Composer\\Installers\\PPIInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/PPIInstaller.php',
        'Composer\\Installers\\PantheonInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/PantheonInstaller.php',
        'Composer\\Installers\\PhiftyInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/PhiftyInstaller.php',
        'Composer\\Installers\\PhpBBInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/PhpBBInstaller.php',
        'Composer\\Installers\\PimcoreInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/PimcoreInstaller.php',
        'Composer\\Installers\\PiwikInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/PiwikInstaller.php',
        'Composer\\Installers\\PlentymarketsInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/PlentymarketsInstaller.php',
        'Composer\\Installers\\Plugin' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/Plugin.php',
        'Composer\\Installers\\PortoInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/PortoInstaller.php',
        'Composer\\Installers\\PrestashopInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/PrestashopInstaller.php',
        'Composer\\Installers\\ProcessWireInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/ProcessWireInstaller.php',
        'Composer\\Installers\\PuppetInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/PuppetInstaller.php',
        'Composer\\Installers\\PxcmsInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/PxcmsInstaller.php',
        'Composer\\Installers\\RadPHPInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/RadPHPInstaller.php',
        'Composer\\Installers\\ReIndexInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/ReIndexInstaller.php',
        'Composer\\Installers\\Redaxo5Installer' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/Redaxo5Installer.php',
        'Composer\\Installers\\RedaxoInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/RedaxoInstaller.php',
        'Composer\\Installers\\RoundcubeInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/RoundcubeInstaller.php',
        'Composer\\Installers\\SMFInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/SMFInstaller.php',
        'Composer\\Installers\\ShopwareInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/ShopwareInstaller.php',
        'Composer\\Installers\\SilverStripeInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/SilverStripeInstaller.php',
        'Composer\\Installers\\SiteDirectInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/SiteDirectInstaller.php',
        'Composer\\Installers\\StarbugInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/StarbugInstaller.php',
        'Composer\\Installers\\SyDESInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/SyDESInstaller.php',
        'Composer\\Installers\\SyliusInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/SyliusInstaller.php',
        'Composer\\Installers\\Symfony1Installer' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/Symfony1Installer.php',
        'Composer\\Installers\\TYPO3CmsInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/TYPO3CmsInstaller.php',
        'Composer\\Installers\\TYPO3FlowInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/TYPO3FlowInstaller.php',
        'Composer\\Installers\\TaoInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/TaoInstaller.php',
        'Composer\\Installers\\TastyIgniterInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/TastyIgniterInstaller.php',
        'Composer\\Installers\\TheliaInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/TheliaInstaller.php',
        'Composer\\Installers\\TuskInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/TuskInstaller.php',
        'Composer\\Installers\\UserFrostingInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/UserFrostingInstaller.php',
        'Composer\\Installers\\VanillaInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/VanillaInstaller.php',
        'Composer\\Installers\\VgmcpInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/VgmcpInstaller.php',
        'Composer\\Installers\\WHMCSInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/WHMCSInstaller.php',
        'Composer\\Installers\\WinterInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/WinterInstaller.php',
        'Composer\\Installers\\WolfCMSInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/WolfCMSInstaller.php',
        'Composer\\Installers\\WordPressInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/WordPressInstaller.php',
        'Composer\\Installers\\YawikInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/YawikInstaller.php',
        'Composer\\Installers\\ZendInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/ZendInstaller.php',
        'Composer\\Installers\\ZikulaInstaller' => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers/ZikulaInstaller.php',
        'DeepCopy\\DeepCopy' => __DIR__ . '/..' . '/myclabs/deep-copy/src/DeepCopy/DeepCopy.php',
        'DeepCopy\\Exception\\CloneException' => __DIR__ . '/..' . '/myclabs/deep-copy/src/DeepCopy/Exception/CloneException.php',
        'DeepCopy\\Exception\\PropertyException' => __DIR__ . '/..' . '/myclabs/deep-copy/src/DeepCopy/Exception/PropertyException.php',
        'DeepCopy\\Filter\\ChainableFilter' => __DIR__ . '/..' . '/myclabs/deep-copy/src/DeepCopy/Filter/ChainableFilter.php',
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
        'Vendidero\\Germanized\\DHL\\Api\\ImPartnerInformation' => __DIR__ . '/../..' . '/src/Api/ImPartnerInformation.php',
        'Vendidero\\Germanized\\DHL\\Api\\ImProductList' => __DIR__ . '/../..' . '/src/Api/ImProductList.php',
        'Vendidero\\Germanized\\DHL\\Api\\ImProductsSoap' => __DIR__ . '/../..' . '/src/Api/ImProductsSoap.php',
        'Vendidero\\Germanized\\DHL\\Api\\ImRefundSoap' => __DIR__ . '/../..' . '/src/Api/ImRefundSoap.php',
        'Vendidero\\Germanized\\DHL\\Api\\ImWarenpostIntRest' => __DIR__ . '/../..' . '/src/Api/ImWarenpostIntRest.php',
        'Vendidero\\Germanized\\DHL\\Api\\Internetmarke' => __DIR__ . '/../..' . '/src/Api/Internetmarke.php',
        'Vendidero\\Germanized\\DHL\\Api\\LabelRest' => __DIR__ . '/../..' . '/src/Api/LabelRest.php',
        'Vendidero\\Germanized\\DHL\\Api\\LabelSoap' => __DIR__ . '/../..' . '/src/Api/LabelSoap.php',
        'Vendidero\\Germanized\\DHL\\Api\\LocationFinder' => __DIR__ . '/../..' . '/src/Api/LocationFinder.php',
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
        'Vendidero\\Germanized\\DHL\\Legacy\\Helper' => __DIR__ . '/../..' . '/src/Legacy/Helper.php',
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
            $loader->prefixLengthsPsr4 = ComposerStaticInit2e1e83bdd18c153bc424f1b93c182f89::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit2e1e83bdd18c153bc424f1b93c182f89::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit2e1e83bdd18c153bc424f1b93c182f89::$classMap;

        }, null, ClassLoader::class);
    }
}
