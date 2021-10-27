<?php


namespace App\Helper;

use App\Models\DocumentObject;
use App\Models\DocumentType;
use App\Models\MailTemplate;

class Constant
{
    const FORMAT_DATETIME = 'Y-m-d H:i:s';
    const FORMAT_DATE_HOUR_MIN = 'Y-m-d H:i';
    const FORMAT_DATE = 'Y-m-d';
    const REGEX_MAIL = '/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix';
    const REGEX_PASSWORD = '/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])/';
    const REGEX_NOT_JAPANESE = '/^[^ぁ-んァ-ン一-龯ｧ-ﾝﾞﾟ０-９Ａ-ｚｦ-ﾟ]+$/';
    const REGEX_NUMBER_COMMA = '/^[0-9]+(,[0-9]+)+$/';
    const REGEX_JAPANESE_ZIP_CODE = '/^([0-9]{3}-[0-9]{4})?$|^[0-9]{7}+$/';
    const REGEX_JAPANESE_PHONE = '/^[0-9]{10,15}$/';
    const DOMAIN_NAME_REGEX = '/(?=^.{4,253}$)(^((?!-)[a-zA-Z0-9-]{0,62}[a-zA-Z0-9]\.)+[a-zA-Z]{2,63}$)/';
    const FLAG_ON = 1;
    const FLAG_OFF = 0;
    const ROW_OF_IMPORT = 2000;
    const COLUMN_IMPORT = 15;
    const DOCUMENT_TYPES = [
        '0001' => '訪問介護',
        '0002' => '訪問看護',
        '0003' => '締結済契約',
        '0004' => '指定通知書',
        '0005' => '各種ひな形',
        '0006' => '社内規程',
        '0007' => 'その他',
        '0008' => 'サービス付き高齢者住宅',
        '0009' => '通所',
        '0010' => '居宅支援',
        '0011' => '変更申請',
        '0012' => '助成事業・支援事業申請'
    ];

    const SORT_DOCUMENT_TYPES = [
        '0001' => '1',
        '0002' => '3',
        '0003' => '6',
        '0004' => '7',
        '0005' => '9',
        '0006' => '10',
        '0007' => '12',
        '0008' => '2',
        '0009' => '4',
        '0010' => '5',
        '0011' => '8',
        '0012' => '11',
    ];

    const IS_B2C = ['0001', '0002', '0009'];
    const DOCUMENT_IS_TEMPLATE = '0003';
    const DOCUMENT_HAVE_NOT_ATTRIBUTE = '0007';

    const ADMIN_CODE = [10, 11, 12, 13, 15, 20, 25, 30];
    const EXECUTIVE_CODE = [40, 50, 55, 56, 60, 63];
    const STAFF_CODE = [65, 66, 70, 80, 84];
    const AVATAR_FOLDER = 'avatars';

    const SUPPORT_OFFICE_CODE = '000001';
    const SUPPORT_OFFICE_EMAIL = 'support@yasashiite.com';

    const REPEAT_UNIT_DEFAULT = 2; // month
    const REPEAT_VALUE_DEFAULT = 3; // every 3 months
    const NUMBER_OF_DAYS_DEFAULT = 30; // 30 days = 1 month

    const REPEAT_UNIT_BY_WEEK = 1; // week
    const REPEAT_VALUE_BY_WEEK = 2; // twice a week
    const NUMBER_OF_DAYS_BY_WEEK = 7; // 7 days = 1 week

    const REPEAT_UNIT_BY_YEAR = 3; // year
    const REPEAT_VALUE_BY_YEAR = 1; // every year
    const NUMBER_OF_DAYS_BY_YEAR = 365; // 365 days = 1 year

    const NUMBER_OF_DAYS = [
        self::REPEAT_UNIT_BY_WEEK => self::NUMBER_OF_DAYS_BY_WEEK,
        self::REPEAT_UNIT_DEFAULT => self::NUMBER_OF_DAYS_DEFAULT,
        self::REPEAT_UNIT_BY_YEAR => self::NUMBER_OF_DAYS_BY_YEAR,
    ];

    public static $documentsIdToFilter = [];
    public static $documentsIdNoPermission = [];
    public static $foldersIdToFilter = [];

    const VARIABLE_IN_MAIL = [
        'document_name' => '[[doc]]',
        'office_name' => '[[office_name]]',
        'doc_detail_url' => '[[url]]',
        'user_service_name' => '[[user_service_name]]',
        'user_service_doc' => '[[user_service_doc]]',
        'user_service_url' => '[[user_service_url]]',
        'document_type' => '[[document_type]]',
        'document_object_type' => '[[document_object_type]]',
    ];

    const DOCUMENT_OBJECTS = [
        '000001' => '新規依頼書',
        '000002' => '退院時サマリー',
        '000003' => '主治医意見書',
        '000004' => '契約書',
        '000005' => '重要事項説明書',
        '000006' => '被保険者証',
        '000007' => '負担割合証',
        '000008' => '居宅サービス計画書',
        '000009' => '初回訪問介護計画書',
        '000010' => '口座振替依頼書',
        '000011' => 'お客様シート',
        '000012' => '障害者受給者証',
        '000013' => '紹介状',
        '000014' => '生活保護介護券',
        '000015' => '利用助成受給資格認定証',
        '000016' => '鍵預かり証',
        '000017' => '処方箋',
        '000018' => '指定通知書',
        '000019' => '規定',
        '000020' => 'サービス契約書',
        '000021' => '運営規程',
        '000022' => '生活支援サービス契約書',
        '000023' => '生活支援サービス重要事項説明書',
        '000024' => '登録事項説明書',
        '000025' => '有料老人ホーム重要事項説明書',
        '000026' => '賃貸借契約書',
        '000027' => '暮らし方ガイド',
        '000028' => '介護サポート契約書',
        '000029' => '介護サポート重要事項説明書',
        '000030' => '介護保険証',
        '000031' => '期間対応した提供票',
        '000032' => '提供票別表',
        '000033' => 'ケアプラン',
        '000034' => '通所介護計画書',
        '000035' => '照会状',
        '000036' => '運動器機能向上訓練計画書',
        '000037' => '初回アセスメントシート',
        '000038' => '個別機能訓練アセスメントシート',
        '000039' => '個人情報同意書',
        '000040' => '肖像権同意書',
        '000041' => 'ご家族様個人情報同意書',
        '000042' => '個別機能訓練計画書',
        '000043' => '訪問看護計画書',
        '000044' => '初回個別機能訓練計画書',
        '000045' => '初回運動器機能向上訓練計画書',
        '000046' => '初回通所介護計画書',
        '000047' => '初回ケアプラン',
        '000048' => '負担割合証（1年に1回）',
    ];

    const DOCUMENT_TYPE_DONT_SEND_MAIL = [
        DocumentType::TEMPLATE,
        DocumentType::RULE,
        DocumentType::OTHER,
    ];

    const CATEGORY_DOCUMENT_ALERT = [
        'B2B' => [
            'before_4_month' => ['0003'],
            'before_1_month' => ['0004'],
            'expiry_date' => ['0003', '0004'],
            'out_of_date' => ['0003', '0004'],
        ],
        'B2C' => [
            'before_1_month' => ['000006', '000007', '000008', '000009', '000012', '000014', '000015', '000034', '000036', '000042', '000047'],
            'expiry_date' => ['000006', '000007', '000008', '000009', '000012', '000014', '000015', '000034', '000036', '000042', '000047'],
            'out_of_date' => ['000005', '000006', '000007', '000008', '000009', '000012', '000014', '000015', '000034', '000036', '000042', '000047'],
        ]
    ];

    const DOCUMENT_OBJECT_HOME_CARE = [
        '000001',
        '000002',
        '000003',
        '000004',
        '000005',
        '000006',
        '000007',
        '000008',
        '000009',
        '000010',
        '000011',
        '000012',
        '000013',
        '000014',
        '000015',
        '000016',
    ];
    const DOCUMENT_OBJECT_HOME_NURSING = [
        '000001',
        '000002',
        '000003',
        '000004',
        '000005',
        '000006',
        '000007',
        '000008',
        '000009',
        '000010',
        '000011',
        '000012',
        '000013',
        '000014',
        '000015',
        '000016',
        '000017'
    ];

    const CODE_UPDATE_HOME_NURSING = '000043';
    const DOCUMENT_OBJECT_CONTRACT = ['000004'];
    const DOCUMENT_OBJECT_NOTICE = ['000018'];
    const DOCUMENT_OBJECT_RULE = ['000019'];
    const DOCUMENT_OBJECT_TEMPLATE = ['000004', '000005', '000020', '000021'];
    const DOCUMENT_OBJECT_UNKNOWN = ['000022', '000023', '000024', '000025', '000026', '000027'];

    const DOCUMENT_OBJECT_MUST_IN_FILE_SET = [
        DocumentType::HOME_CARE => [
            "000001",
            "000002",
            "000003",
            "000004",
            "000005",
            "000006",
            "000007",
            "000008",
            "000009",
            "000010",
        ],
        DocumentType::HOME_NURSING => [
            "000001",
            "000002",
            "000003",
            "000004",
            "000005",
            "000006",
            "000007",
            "000008",
            "000009",
            "000010",
            "000017",
        ],
        DocumentType::WELFARE_CENTER => [
            "000001",
            "000004",
            "000005",
            "000006",
            "000007",
            "000037",
            "000038",
            "000039",
            "000040",
            "000010",
            "000047",
        ]
    ];
    const DOCUMENT_OBJECT_SERVICED_ELDERLY_HOUSING = [
        '000022',
        '000023',
        '000024',
        '000025',
        '000026',
        '000028',
        '000029'
    ];
    const DOCUMENT_OBJECT_WELFARE_CENTER = [
        '000001',
        '000002',
        '000003',
        '000004',
        '000005',
        '000006',
        '000007',
        '000047',
        '000046',
        '000044',
        '000045',
        '000037',
        '000038',
        '000039',
        '000040',
        '000041',
        '000034',
        '000042',
        '000036',
        '000010',
        '000012',
        '000035',
        '000014',
        '000015',
        '000016',
    ];

    const DOCUMENT_OBJECT_HOUSING_SUPPORT = [];
    const DOCUMENT_OBJECT_CHANGE_REGISTATION = [];
    const DOCUMENT_OBJECT_SIGN_UP_FOR_SUPPORT_PROJECTS = [];

    const DOCUMENT_TYPE_OBJECT = [
        '0001' => self::DOCUMENT_OBJECT_HOME_CARE,
        '0002' => self::DOCUMENT_OBJECT_HOME_NURSING,
        '0003' => self::DOCUMENT_OBJECT_CONTRACT,
        '0004' => self::DOCUMENT_OBJECT_NOTICE,
        '0005' => self::DOCUMENT_OBJECT_TEMPLATE,
        '0006' => self::DOCUMENT_OBJECT_RULE,
        '0007' => self::DOCUMENT_OBJECT_UNKNOWN,
        '0008' => self::DOCUMENT_OBJECT_SERVICED_ELDERLY_HOUSING,
        '0009' => self::DOCUMENT_OBJECT_WELFARE_CENTER,
        '0010' => self::DOCUMENT_OBJECT_HOUSING_SUPPORT,
        '0011' => self::DOCUMENT_OBJECT_CHANGE_REGISTATION,
        '0012' => self::DOCUMENT_OBJECT_SIGN_UP_FOR_SUPPORT_PROJECTS,
    ];

    const TRANSFER_VERSION_1 = [
        '負担割合証_姫路土山_0001XXX' => '負担割合証_姫路土山_0001194',
        '負担割合証_宮原_0001XXX' => '負担割合証_宮原_0001091',
        '負担割合証_川口幸町_0001XXX' => '負担割合証_川口幸町_0001071',
        '負担割合証_平尾巡回_0001XXX' => '負担割合証_平尾巡回_0001103',
        '負担割合証_新宿_0001XXX' => '負担割合証_新宿_0001090',
        '負担割合証_東松原_0001XXX' => '負担割合証_東松原_0001028',
        '負担割合証_東灘巡回_0001XXX' => '負担割合証_東灘巡回_0001192',
        '負担割合証_西明石_0001XXX' => '負担割合証_西明石_0001157',
        '負担割合証_逗子訪問介護_0001XXX' => '負担割合証_逗子訪問介護_0001115',
        '負担割合証_長野高田_0001XXX' => '負担割合証_長野高田_0001182',
        '居宅サービス計画書_三鷹中原_0001XXX' => '居宅サービス計画書_三鷹中原_0001136',
        '居宅サービス計画書_三鷹北野巡回_0001XXX' => '居宅サービス計画書_三鷹北野巡回_0001128',
        '居宅サービス計画書_上越巡回_0001XXX' => '居宅サービス計画書_上越巡回_0001039',
        '居宅サービス計画書_姫路土山_0001XXX' => '居宅サービス計画書_姫路土山_0001194',
        '居宅サービス計画書_宮原_0001XXX' => '居宅サービス計画書_宮原_0001091',
        '居宅サービス計画書_川口幸町_0001XXX' => '居宅サービス計画書_川口幸町_0001071',
        '居宅サービス計画書_平尾巡回_0001XXX' => '居宅サービス計画書_平尾巡回_0001103',
        '居宅サービス計画書_新宿_0001XXX' => '居宅サービス計画書_新宿_0001090',
        '居宅サービス計画書_東松原_0001XXX' => '居宅サービス計画書_東松原_0001028',
        '居宅サービス計画書_東灘巡回_0001XXX' => '居宅サービス計画書_東灘巡回_0001192',
        '居宅サービス計画書_西明石_0001XXX' => '居宅サービス計画書_西明石_0001157',
        '居宅サービス計画書_長野高田_0001XXX' => '居宅サービス計画書_長野高田_0001182'
    ];

    const NUMBER_JAPAN = [
        '①' => 1,
        '②' => 2,
        '③' => 3,
        '④' => 4,
        '⑤' => 5,
        '⑥' => 6,
        '⑦' => 7,
        '⑧' => 8,
        '⑨' => 9,
        '⑩' => 10
    ];


    public static function isOn($flag)
    {
        return $flag == self::FLAG_ON;
    }

    const TOTAL_COLUMN_FILE_SERVICE_USER = 20;
    const TOTAL_COLUMN_FILE_OFFICE = 5;
    const YEAR_STOP_CONTRACT = 5;
    const DOCUMENT_TYPE_IMPORT = [
        '介護' => '0001',
        '訪問従来' => '0001',
        '訪問定巡' => '0001',
        '訪問高住' => '0001',
        'かえりえ' => '0002',
        'ゆめふる' => '0009',
    ];
}
