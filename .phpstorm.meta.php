<?php
namespace PHPSTORM_META {
    registerArgumentsSet(
        'array_helper_orders',
        \BusyPHP\helper\ArrayHelper::ORDER_BY_ASC,
        \BusyPHP\helper\ArrayHelper::ORDER_BY_DESC,
        \BusyPHP\helper\ArrayHelper::ORDER_BY_NAT
    );
    expectedArguments(\BusyPHP\helper\ArrayHelper::listSortBy(), 2, argumentsSet('array_helper_orders'));
    expectedArguments(\BusyPHP\helper\ArrayHelper::sortTree(), 2, argumentsSet('array_helper_orders'));
    
    
    expectedArguments(\BusyPHP\helper\FileHelper::pathInfo(), 1,
        PATHINFO_DIRNAME |
        PATHINFO_FILENAME |
        PATHINFO_EXTENSION |
        PATHINFO_BASENAME |
        PATHINFO_ALL
    );
    
    expectedArguments(\BusyPHP\model\annotation\field\Json::__construct(), 1,
        JSON_HEX_TAG |
        JSON_HEX_AMP |
        JSON_HEX_APOS |
        JSON_NUMERIC_CHECK |
        JSON_PRETTY_PRINT |
        JSON_UNESCAPED_SLASHES |
        JSON_FORCE_OBJECT |
        JSON_UNESCAPED_UNICODE |
        JSON_THROW_ON_ERROR
    );
    
    expectedArguments(\BusyPHP\model\annotation\field\ToArrayFormat::__construct(), 0,
        \BusyPHP\model\annotation\field\ToArrayFormat::TYPE_PROPERTY |
        \BusyPHP\model\annotation\field\ToArrayFormat::TYPE_FIELD |
        \BusyPHP\model\annotation\field\ToArrayFormat::TYPE_SNAKE |
        \BusyPHP\model\annotation\field\ToArrayFormat::TYPE_CAMEL
    );
    
    expectedArguments(\BusyPHP\model\annotation\field\Column::__construct(), 2,
        \BusyPHP\model\annotation\field\Column::TYPE_DEFAULT |
        \BusyPHP\model\annotation\field\Column::TYPE_STRING |
        \BusyPHP\model\annotation\field\Column::TYPE_INT |
        \BusyPHP\model\annotation\field\Column::TYPE_FLOAT |
        \BusyPHP\model\annotation\field\Column::TYPE_BOOL |
        \BusyPHP\model\annotation\field\Column::TYPE_TIMESTAMP |
        \BusyPHP\model\annotation\field\Column::TYPE_DATETIME |
        \BusyPHP\model\annotation\field\Column::TYPE_DATE
    );
    
    expectedArguments(\BusyPHP\model\annotation\field\Column::__construct(), 5,
        \BusyPHP\model\annotation\field\Column::FEATURE_CREATE_TIME |
        \BusyPHP\model\annotation\field\Column::FEATURE_UPDATE_TIME |
        \BusyPHP\model\annotation\field\Column::FEATURE_SOFT_DELETE
    );
    
    expectedArguments(\BusyPHP\model\annotation\field\AutoTimestamp::__construct(), 0,
        \BusyPHP\model\annotation\field\AutoTimestamp::TYPE_INT |
        \BusyPHP\model\annotation\field\AutoTimestamp::TYPE_TIMESTAMP |
        \BusyPHP\model\annotation\field\AutoTimestamp::TYPE_DATETIME |
        \BusyPHP\model\annotation\field\AutoTimestamp::TYPE_DATE
    );
    
    expectedArguments(\BusyPHP\model\annotation\field\Validator::__construct(), 0,
        \BusyPHP\model\annotation\field\Validator::CONFIRM |
        \BusyPHP\model\annotation\field\Validator::DIFFERENT |
        \BusyPHP\model\annotation\field\Validator::EGT |
        \BusyPHP\model\annotation\field\Validator::GT |
        \BusyPHP\model\annotation\field\Validator::ELT |
        \BusyPHP\model\annotation\field\Validator::LT |
        \BusyPHP\model\annotation\field\Validator::EG |
        \BusyPHP\model\annotation\field\Validator::IN |
        \BusyPHP\model\annotation\field\Validator::NOT_IN |
        \BusyPHP\model\annotation\field\Validator::BETWEEN |
        \BusyPHP\model\annotation\field\Validator::NOT_BETWEEN |
        \BusyPHP\model\annotation\field\Validator::LENGTH |
        \BusyPHP\model\annotation\field\Validator::MAX |
        \BusyPHP\model\annotation\field\Validator::MIN |
        \BusyPHP\model\annotation\field\Validator::AFTER |
        \BusyPHP\model\annotation\field\Validator::BEFORE |
        \BusyPHP\model\annotation\field\Validator::EXPIRE |
        \BusyPHP\model\annotation\field\Validator::ALLOW_IP |
        \BusyPHP\model\annotation\field\Validator::DENY_IP |
        \BusyPHP\model\annotation\field\Validator::REGEX |
        \BusyPHP\model\annotation\field\Validator::TOKEN |
        \BusyPHP\model\annotation\field\Validator::IS |
        \BusyPHP\model\annotation\field\Validator::REQUIRE |
        \BusyPHP\model\annotation\field\Validator::IS_NUMBER |
        \BusyPHP\model\annotation\field\Validator::IS_ARRAY |
        \BusyPHP\model\annotation\field\Validator::IS_INTEGER |
        \BusyPHP\model\annotation\field\Validator::IS_FLOAT |
        \BusyPHP\model\annotation\field\Validator::IS_MOBILE |
        \BusyPHP\model\annotation\field\Validator::IS_ID_CARD |
        \BusyPHP\model\annotation\field\Validator::IS_CHS |
        \BusyPHP\model\annotation\field\Validator::IS_CHS_DASH |
        \BusyPHP\model\annotation\field\Validator::IS_CHS_ALPHA |
        \BusyPHP\model\annotation\field\Validator::IS_CHS_ALPHA_NUM |
        \BusyPHP\model\annotation\field\Validator::IS_DATE |
        \BusyPHP\model\annotation\field\Validator::IS_BOOL |
        \BusyPHP\model\annotation\field\Validator::IS_ALPHA |
        \BusyPHP\model\annotation\field\Validator::IS_ALPHA_DASH |
        \BusyPHP\model\annotation\field\Validator::IS_ALPHA_NUM |
        \BusyPHP\model\annotation\field\Validator::IS_ACCEPTED |
        \BusyPHP\model\annotation\field\Validator::IS_EMAIL |
        \BusyPHP\model\annotation\field\Validator::IS_URL |
        \BusyPHP\model\annotation\field\Validator::ACTIVE_URL |
        \BusyPHP\model\annotation\field\Validator::IP |
        \BusyPHP\model\annotation\field\Validator::FILE_EXT |
        \BusyPHP\model\annotation\field\Validator::FILE_SIZE |
        \BusyPHP\model\annotation\field\Validator::FILE_MIME |
        \BusyPHP\model\annotation\field\Validator::IMAGE |
        \BusyPHP\model\annotation\field\Validator::METHOD |
        \BusyPHP\model\annotation\field\Validator::DATE_FORMAT |
        \BusyPHP\model\annotation\field\Validator::UNIQUE |
        \BusyPHP\model\annotation\field\Validator::BEHAVIOR |
        \BusyPHP\model\annotation\field\Validator::FILTER |
        \BusyPHP\model\annotation\field\Validator::REQUIRE_IF |
        \BusyPHP\model\annotation\field\Validator::REQUIRE_CALLBACK |
        \BusyPHP\model\annotation\field\Validator::REQUIRE_WITH |
        \BusyPHP\model\annotation\field\Validator::MUST |
        \BusyPHP\model\annotation\field\Validator::CLOSURE |
        \BusyPHP\model\annotation\field\Validator::IS_FIRST_ALPHA_NUM_DASH
    );
    
    expectedArguments(\BusyPHP\facade\QrCode::level(), 0,
        \BusyPHP\facade\QrCode::LEVEL_LOW |
        \BusyPHP\facade\QrCode::LEVEL_MEDIUM |
        \BusyPHP\facade\QrCode::LEVEL_QUARTILE |
        \BusyPHP\facade\QrCode::LEVEL_HIGH
    );
    
    expectedArguments(\BusyPHP\QrCode::level(), 0,
        \BusyPHP\QrCode::LEVEL_LOW |
        \BusyPHP\QrCode::LEVEL_MEDIUM |
        \BusyPHP\QrCode::LEVEL_QUARTILE |
        \BusyPHP\QrCode::LEVEL_HIGH
    );
    
    expectedArguments(\BusyPHP\facade\QrCode::format(), 0,
        \BusyPHP\facade\QrCode::FORMAT_PNG |
        \BusyPHP\facade\QrCode::FORMAT_EPS |
        \BusyPHP\facade\QrCode::FORMAT_PDF |
        \BusyPHP\facade\QrCode::FORMAT_BINARY |
        \BusyPHP\facade\QrCode::FORMAT_SVG
    );
    
    expectedArguments(\BusyPHP\QrCode::format(), 0,
        \BusyPHP\QrCode::FORMAT_PNG |
        \BusyPHP\QrCode::FORMAT_EPS |
        \BusyPHP\QrCode::FORMAT_PDF |
        \BusyPHP\QrCode::FORMAT_BINARY |
        \BusyPHP\QrCode::FORMAT_SVG
    );
    
    registerArgumentsSet(
        'image_formats',
        \BusyPHP\image\parameter\FormatParameter::FORMAT_PNG |
        \BusyPHP\image\parameter\FormatParameter::FORMAT_WEBP |
        \BusyPHP\image\parameter\FormatParameter::FORMAT_JPEG |
        \BusyPHP\image\parameter\FormatParameter::FORMAT_JPG |
        \BusyPHP\image\parameter\FormatParameter::FORMAT_GIF |
        \BusyPHP\image\parameter\FormatParameter::FORMAT_BMP
    );
    expectedArguments(\BusyPHP\Image::format(), 0, argumentsSet('image_formats'));
    expectedArguments(\BusyPHP\facade\Image::format(), 0, argumentsSet('image_formats'));
    
    expectedArguments(\BusyPHP\image\traits\Gravity::setGravity(), 0,
        \BusyPHP\image\parameter\BaseParameter::GRAVITY_TOP_LEFT |
        \BusyPHP\image\parameter\BaseParameter::GRAVITY_TOP_CENTER |
        \BusyPHP\image\parameter\BaseParameter::GRAVITY_TOP_RIGHT |
        \BusyPHP\image\parameter\BaseParameter::GRAVITY_CENTER |
        \BusyPHP\image\parameter\BaseParameter::GRAVITY_LEFT_CENTER |
        \BusyPHP\image\parameter\BaseParameter::GRAVITY_RIGHT_CENTER |
        \BusyPHP\image\parameter\BaseParameter::GRAVITY_BOTTOM_LEFT |
        \BusyPHP\image\parameter\BaseParameter::GRAVITY_BOTTOM_CENTER |
        \BusyPHP\image\parameter\BaseParameter::GRAVITY_BOTTOM_RIGHT |
        \BusyPHP\image\parameter\BaseParameter::GRAVITY_RAND
    );
}