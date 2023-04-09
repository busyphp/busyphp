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
    
    registerArgumentsSet('order_types', 'desc' | 'asc');
    expectedArguments(\think\db\BaseQuery::order(), 1, argumentsSet('order_types'));
    expectedArguments(\BusyPHP\Model::order(), 1, argumentsSet('order_types'));
    
    expectedArguments(\BusyPHP\office\excel\Export::type(), 0,
        \BusyPHP\office\excel\Export::TYPE_XLSX |
        \BusyPHP\office\excel\Export::TYPE_XLS |
        \BusyPHP\office\excel\Export::TYPE_CSV |
        \BusyPHP\office\excel\Export::TYPE_ODS |
        \BusyPHP\office\excel\Export::TYPE_HTML |
        \BusyPHP\office\excel\Export::TYPE_MPDF |
        \BusyPHP\office\excel\Export::TYPE_TCPDF |
        \BusyPHP\office\excel\Export::TYPE_DOMPDF
    );
    
    registerArgumentsSet(
        'excel_number_format',
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_GENERAL |
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT |
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER |
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_0 |
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00 |
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1 |
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2 |
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE |
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_0 |
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00 |
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_YYYYMMDD |
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DDMMYYYY |
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DMYSLASH |
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DMYMINUS |
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DMMINUS |
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_MYMINUS |
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_XLSX14 |
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_XLSX15 |
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_XLSX16 |
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_XLSX17 |
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_XLSX22 |
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DATETIME |
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_TIME1 |
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_TIME2 |
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_TIME3 |
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_TIME4 |
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_TIME5 |
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_TIME6 |
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_TIME7 |
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_TIME8 |
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_YYYYMMDDSLASH |
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_INTEGER |
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD |
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_EUR_INTEGER |
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_EUR |
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING_USD |
        \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_ACCOUNTING_EUR
    );
    
    registerArgumentsSet(
        'excel_data_type',
        \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING2,
        \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING,
        \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA,
        \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC,
        \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_BOOL,
        \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL,
        \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_INLINE,
        \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_ERROR,
        \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_ISO_DATE,
    );
    
    registerArgumentsSet(
        'excel_export_column_filter',
        \BusyPHP\office\excel\export\ExportColumn::FILTER_STRING |
        \BusyPHP\office\excel\export\ExportColumn::FILTER_DATE
    );
    
    expectedArguments(\BusyPHP\office\excel\export\ExportColumn::__construct(), 3, argumentsSet('excel_export_column_filter'));
    expectedArguments(\BusyPHP\office\excel\export\ExportColumn::init(), 3, argumentsSet('excel_export_column_filter'));
    expectedArguments(\BusyPHP\office\excel\export\ExportColumn::numberFormat(), 0, argumentsSet('excel_number_format'));
    expectedArguments(\BusyPHP\office\excel\export\ExportColumn::dataType(), 0,argumentsSet('excel_data_type'));
    expectedArguments(\BusyPHP\model\annotation\field\Export::__construct(), 2, argumentsSet('excel_export_column_filter'));
    expectedArguments(\BusyPHP\model\annotation\field\Export::__construct(), 3, argumentsSet('excel_number_format'));
    expectedArguments(\BusyPHP\model\annotation\field\Export::__construct(), 5,argumentsSet('excel_data_type'));
    
    registerArgumentsSet(
        'excel_import_column_filter',
        \BusyPHP\office\excel\import\ImportColumn::FILTER_INT |
        \BusyPHP\office\excel\import\ImportColumn::FILTER_FLOAT |
        \BusyPHP\office\excel\import\ImportColumn::FILTER_BOOL |
        \BusyPHP\office\excel\import\ImportColumn::FILTER_DATE |
        \BusyPHP\office\excel\import\ImportColumn::FILTER_TIMESTAMP |
        \BusyPHP\office\excel\import\ImportColumn::FILTER_SPLIT |
        \BusyPHP\office\excel\import\ImportColumn::FILTER_TRIM
    );
    expectedArguments(\BusyPHP\office\excel\import\ImportColumn::init(), 2, argumentsSet('excel_import_column_filter'));
    expectedArguments(\BusyPHP\office\excel\import\ImportColumn::__construct(), 2, argumentsSet('excel_import_column_filter'));
    expectedArguments(\BusyPHP\model\annotation\field\Import::__construct(), 1, argumentsSet('excel_import_column_filter'));
    expectedArguments(\BusyPHP\office\excel\Import::on(), 0,
        \BusyPHP\office\excel\Import::EVENT_ROW_SUCCESS |
        \BusyPHP\office\excel\Import::EVENT_ROW_ERROR |
        \BusyPHP\office\excel\Import::EVENT_LIST_HANDLED |
        \BusyPHP\office\excel\Import::EVENT_SAVE_SUCCESS |
        \BusyPHP\office\excel\Import::EVENT_SAVE_ERROR
    );
    expectedArguments(\BusyPHP\office\excel\Export::on(), 0,
        \BusyPHP\office\excel\Export::EVENT_ROW_WRITTEN |
        \BusyPHP\office\excel\Export::EVENT_SHEET_START |
        \BusyPHP\office\excel\Export::EVENT_SHEET_WRITTEN |
        \BusyPHP\office\excel\Export::EVENT_START |
        \BusyPHP\office\excel\Export::EVENT_EXPORTED
    );
}