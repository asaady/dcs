<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit70ff5a14963b3be2d353e74573085465
{
    public static $prefixLengthsPsr4 = array (
        'D' => 
        array (
            'Dcs\\Vendor\\Core\\Views\\' => 22,
            'Dcs\\Vendor\\Core\\Models\\' => 23,
            'Dcs\\Vendor\\Core\\Controllers\\' => 28,
            'Dcs\\App\\Templates\\' => 18,
            'Dcs\\App\\Components\\Utils\\Uploadset\\' => 35,
            'Dcs\\App\\Components\\Utils\\Uploadobject\\' => 38,
            'Dcs\\App\\Components\\Trigs\\' => 25,
            'Dcs\\App\\Components\\Reps\\' => 24,
            'Dcs\\App\\Components\\Prnforms\\CoverSheets\\' => 40,
            'Dcs\\App\\Api\\' => 12,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Dcs\\Vendor\\Core\\Views\\' => 
        array (
            0 => __DIR__ . '/..' . '/core/views',
        ),
        'Dcs\\Vendor\\Core\\Models\\' => 
        array (
            0 => __DIR__ . '/..' . '/core/models',
        ),
        'Dcs\\Vendor\\Core\\Controllers\\' => 
        array (
            0 => __DIR__ . '/..' . '/core/controllers',
        ),
        'Dcs\\App\\Templates\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app/templates',
        ),
        'Dcs\\App\\Components\\Utils\\Uploadset\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app/components/utils/uploadset',
        ),
        'Dcs\\App\\Components\\Utils\\Uploadobject\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app/components/utils/uploadobject',
        ),
        'Dcs\\App\\Components\\Trigs\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app/components/trigs',
        ),
        'Dcs\\App\\Components\\Reps\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app/components/reps',
        ),
        'Dcs\\App\\Components\\Prnforms\\CoverSheets\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app/components/prnforms/coversheets',
        ),
        'Dcs\\App\\Api\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app/api',
        ),
    );

    public static $classMap = array (
        'Dcs\\App\\Api\\ApiAcceptOtk' => __DIR__ . '/../..' . '/app/api/ApiAcceptOtk.php',
        'Dcs\\App\\Components\\Reps\\CoverSheets\\Controller_CoverSheets' => __DIR__ . '/../..' . '/app/components/reps/coversheets/Controller_CoverSheets.php',
        'Dcs\\App\\Components\\Utils\\Uploadobject\\Controller_UploadObject' => __DIR__ . '/../..' . '/app/components/utils/uploadobject/Controller_Uploadobject.php',
        'Dcs\\App\\Components\\Utils\\Uploadobject\\UploadObject' => __DIR__ . '/../..' . '/app/components/utils/uploadobject/Uploadobject.php',
        'Dcs\\App\\Templates\\Default_Template' => __DIR__ . '/../..' . '/app/templates/Default_template.php',
        'Dcs\\Vendor\\Core\\Controllers\\Controller' => __DIR__ . '/..' . '/core/controllers/Controller.php',
        'Dcs\\Vendor\\Core\\Controllers\\Controller_API' => __DIR__ . '/..' . '/core/controllers/Controller_Api.php',
        'Dcs\\Vendor\\Core\\Controllers\\Controller_Ajax' => __DIR__ . '/..' . '/core/controllers/Controller_Ajax.php',
        'Dcs\\Vendor\\Core\\Controllers\\Controller_Auth' => __DIR__ . '/..' . '/core/controllers/Controller_Auth.php',
        'Dcs\\Vendor\\Core\\Controllers\\Controller_Download' => __DIR__ . '/..' . '/core/controllers/Controller_Download.php',
        'Dcs\\Vendor\\Core\\Controllers\\Controller_Error' => __DIR__ . '/..' . '/core/controllers/Controller_Error.php',
        'Dcs\\Vendor\\Core\\Controllers\\Controller_Register' => __DIR__ . '/..' . '/core/controllers/Controller_Comp.php',
        'Dcs\\Vendor\\Core\\Controllers\\Controller_Sheet' => __DIR__ . '/..' . '/core/controllers/Controller_Sheet.php',
        'Dcs\\Vendor\\Core\\Controllers\\I_Controller' => __DIR__ . '/..' . '/core/controllers/I_Controller.php',
        'Dcs\\Vendor\\Core\\Controllers\\T_Controller' => __DIR__ . '/..' . '/core/controllers/T_Controller.php',
        'Dcs\\Vendor\\Core\\Models\\CollectionItem' => __DIR__ . '/..' . '/core/models/CollectionItem.php',
        'Dcs\\Vendor\\Core\\Models\\CollectionSet' => __DIR__ . '/..' . '/core/models/CollectionSet.php',
        'Dcs\\Vendor\\Core\\Models\\Common_data' => __DIR__ . '/..' . '/core/models/Common_data.php',
        'Dcs\\Vendor\\Core\\Models\\Cproperty' => __DIR__ . '/..' . '/core/models/Cproperty.php',
        'Dcs\\Vendor\\Core\\Models\\DataManager' => __DIR__ . '/..' . '/core/models/DataManager.php',
        'Dcs\\Vendor\\Core\\Models\\Db' => __DIR__ . '/..' . '/core/models/Db.php',
        'Dcs\\Vendor\\Core\\Models\\DcsContext' => __DIR__ . '/..' . '/core/models/DcsContext.php',
        'Dcs\\Vendor\\Core\\Models\\DcsException' => __DIR__ . '/..' . '/core/models/DcsException.php',
        'Dcs\\Vendor\\Core\\Models\\DcsHung' => __DIR__ . '/..' . '/core/models/DcsHung.php',
        'Dcs\\Vendor\\Core\\Models\\Download' => __DIR__ . '/..' . '/core/models/Download.php',
        'Dcs\\Vendor\\Core\\Models\\EProperty' => __DIR__ . '/..' . '/core/models/EProperty.php',
        'Dcs\\Vendor\\Core\\Models\\EPropertySet' => __DIR__ . '/..' . '/core/models/EPropertySet.php',
        'Dcs\\Vendor\\Core\\Models\\Entity' => __DIR__ . '/..' . '/core/models/Entity.php',
        'Dcs\\Vendor\\Core\\Models\\EntitySet' => __DIR__ . '/..' . '/core/models/EntitySet.php',
        'Dcs\\Vendor\\Core\\Models\\Filter' => __DIR__ . '/..' . '/core/models/Filter.php',
        'Dcs\\Vendor\\Core\\Models\\Hungarian' => __DIR__ . '/..' . '/core/models/Hungarian.php',
        'Dcs\\Vendor\\Core\\Models\\I_Item' => __DIR__ . '/..' . '/core/models/I_Item.php',
        'Dcs\\Vendor\\Core\\Models\\I_Model' => __DIR__ . '/..' . '/core/models/I_Model.php',
        'Dcs\\Vendor\\Core\\Models\\I_Property' => __DIR__ . '/..' . '/core/models/I_Property.php',
        'Dcs\\Vendor\\Core\\Models\\I_Set' => __DIR__ . '/..' . '/core/models/I_Set.php',
        'Dcs\\Vendor\\Core\\Models\\I_Sheet' => __DIR__ . '/..' . '/core/models/I_Sheet.php',
        'Dcs\\Vendor\\Core\\Models\\Item' => __DIR__ . '/..' . '/core/models/Item.php',
        'Dcs\\Vendor\\Core\\Models\\Mdcollection' => __DIR__ . '/..' . '/core/models/Mdcollection.php',
        'Dcs\\Vendor\\Core\\Models\\Mdentity' => __DIR__ . '/..' . '/core/models/Mdentity.php',
        'Dcs\\Vendor\\Core\\Models\\MdentitySet' => __DIR__ . '/..' . '/core/models/MdentitySet.php',
        'Dcs\\Vendor\\Core\\Models\\Mdproperty_old' => __DIR__ . '/..' . '/core/models/Mdproperty_old.php',
        'Dcs\\Vendor\\Core\\Models\\Model' => __DIR__ . '/..' . '/core/models/Model.php',
        'Dcs\\Vendor\\Core\\Models\\Property' => __DIR__ . '/..' . '/core/models/Property.php',
        'Dcs\\Vendor\\Core\\Models\\PropertySet' => __DIR__ . '/..' . '/core/models/PropertySet.php',
        'Dcs\\Vendor\\Core\\Models\\Register' => __DIR__ . '/..' . '/core/models/Register.php',
        'Dcs\\Vendor\\Core\\Models\\Route' => __DIR__ . '/..' . '/core/models/Route.php',
        'Dcs\\Vendor\\Core\\Models\\Rproperty' => __DIR__ . '/..' . '/core/models/Rproperty.php',
        'Dcs\\Vendor\\Core\\Models\\RpropertySet' => __DIR__ . '/..' . '/core/models/RpropertySet.php',
        'Dcs\\Vendor\\Core\\Models\\Sets' => __DIR__ . '/..' . '/core/models/Sets.php',
        'Dcs\\Vendor\\Core\\Models\\Sheet' => __DIR__ . '/..' . '/core/models/Sheet.php',
        'Dcs\\Vendor\\Core\\Models\\T_CProperty' => __DIR__ . '/..' . '/core/models/T_CProperty.php',
        'Dcs\\Vendor\\Core\\Models\\T_Collection' => __DIR__ . '/..' . '/core/models/T_Collection.php',
        'Dcs\\Vendor\\Core\\Models\\T_EProperty' => __DIR__ . '/..' . '/core/models/T_EProperty.php',
        'Dcs\\Vendor\\Core\\Models\\T_Entity' => __DIR__ . '/..' . '/core/models/T_Entity.php',
        'Dcs\\Vendor\\Core\\Models\\T_Item' => __DIR__ . '/..' . '/core/models/T_Item.php',
        'Dcs\\Vendor\\Core\\Models\\T_MdSet' => __DIR__ . '/..' . '/core/models/T_MdSet.php',
        'Dcs\\Vendor\\Core\\Models\\T_Mdentity' => __DIR__ . '/..' . '/core/models/T_Mdentity.php',
        'Dcs\\Vendor\\Core\\Models\\T_Mdproperty' => __DIR__ . '/..' . '/core/models/T_Mdproperty.php',
        'Dcs\\Vendor\\Core\\Models\\T_Property' => __DIR__ . '/..' . '/core/models/T_Property.php',
        'Dcs\\Vendor\\Core\\Models\\T_Set' => __DIR__ . '/..' . '/core/models/T_Set.php',
        'Dcs\\Vendor\\Core\\Models\\T_Sheet' => __DIR__ . '/..' . '/core/models/T_Sheet.php',
        'Dcs\\Vendor\\Core\\Models\\User' => __DIR__ . '/..' . '/core/models/User.php',
        'Dcs\\Vendor\\Core\\Views\\Auth_View' => __DIR__ . '/..' . '/core/views/Auth_View.php',
        'Dcs\\Vendor\\Core\\Views\\Error_View' => __DIR__ . '/..' . '/core/views/Error_View.php',
        'Dcs\\Vendor\\Core\\Views\\I_Template' => __DIR__ . '/..' . '/core/views/I_Template.php',
        'Dcs\\Vendor\\Core\\Views\\I_View' => __DIR__ . '/..' . '/core/views/I_View.php',
        'Dcs\\Vendor\\Core\\Views\\Print_View' => __DIR__ . '/..' . '/core/views/Print_View.php',
        'Dcs\\Vendor\\Core\\Views\\T_Template' => __DIR__ . '/..' . '/core/views/T_Template.php',
        'Dcs\\Vendor\\Core\\Views\\T_View' => __DIR__ . '/..' . '/core/views/T_View.php',
        'Dcs\\Vendor\\Core\\Views\\Template' => __DIR__ . '/..' . '/core/views/Template.php',
        'Dcs\\Vendor\\Core\\Views\\View' => __DIR__ . '/..' . '/core/views/View.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit70ff5a14963b3be2d353e74573085465::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit70ff5a14963b3be2d353e74573085465::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit70ff5a14963b3be2d353e74573085465::$classMap;

        }, null, ClassLoader::class);
    }
}
