<?php

use Illuminate\Database\Seeder;
use App\Models\MailTemplate;
use Illuminate\Support\Facades\DB;

class MailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        MailTemplate::truncate();
        $mailTemplates = [
            [
                'code' => MailTemplate::B2B_BEFORE_4_MONTH,
                'name' => '有効期間終了４か月前（企業間取引）',
                'subject' => '[[document_type]]の有効期間終了４か月前です',
                'body' => '[[office_name]]訪問介護事業所<br>
                            [[document_type]]の有効期間終了４か月前です。<br><br>
                            [[document_type]]を更新するのか終了するのか確認をお願いします。<br><br>
                            文書URL：[[url]]',
                'user_created' => 1,
                'is_system' => MailTemplate::IS_SYSTEM,
            ],
            [
                'code' => MailTemplate::B2B_BEFORE_1_MONTH,
                'name' => '有効期間終了1か月前（企業間取引）',
                'subject' => '[[document_type]]の有効期間終了1か月前です',
                'body' => '[[office_name]]訪問介護事業所<br>
                            [[document_type]]の有効期間終了1か月前です。<br><br>
                            [[document_type]]の更新の準備を進めていただけますようお願いします。<br><br>
                            文書URL：[[url]]',
                'user_created' => 1,
                'is_system' => MailTemplate::IS_SYSTEM,
            ],
            [
                'code' => MailTemplate::B2B_EXPIRATION_DATE,
                'name' => '有効期間終了日（企業間取引）',
                'subject' => '[[document_type]]の有効期間終了日です',
                'body' => '[[office_name]]訪問介護事業所<br>
                            [[document_type]]の有効期間終了日です。<br><br>
                            新しい[[document_type]]の格納を進めてくださいますようお願いします。<br><br>
                            文書URL：[[url]]',
                'user_created' => 1,
                'is_system' => MailTemplate::IS_SYSTEM,
            ],
            [
                'code' => MailTemplate::B2B_AFTER_1_MONTH,
                'name' => '有効期間超過（企業間取引）',
                'subject' => '[[document_type]]の有効期間が過ぎています',
                'body' => '[[office_name]]訪問介護事業所<br>
                            [[document_type]]の有効期間を過ぎております。<br><br>
                            新たな[[document_type]]の格納を早急に進めて下さいますようお願い致します。<br><br>
                            文書URL：[[url]]',
                'user_created' => 1,
                'is_system' => MailTemplate::IS_SYSTEM,
            ],
            [
                'code' => MailTemplate::B2C_MISSING_DOCUMENT,
                'name' => '登録時帳票不足',
                'subject' => '[[user_service_name]]様の初回登録時に不足帳票があります。',
                'body' => '[[office_name]]訪問介護事業所<br>
                            [[user_service_name]]様の初回登録時に不足帳票がありました。<br><br>
                            不足帳票は下記の通りです。<br/>
                           ・[[user_service_doc]]<br><br>
                           ファイルセットURL：[[user_service_url]]',
                'user_created' => 1,
                'is_system' => MailTemplate::IS_SYSTEM,
            ],
            [
                'code' => MailTemplate::B2C_BEFORE_1_MONTH,
                'name' => '有効期間終了1か月前（訪問介護/訪問看護）',
                'subject' => '[[document_object_type]]の有効期間終了1か月前です',
                'body' => '[[office_name]]訪問介護事業所<br>
                            [[user_service_name]]様の[[document_object_type]]の有効期間1か月前です。<br><br>
                            新しい[[document_object_type]]を確認し格納を進めて下さいますようお願い致します。<br><br>
                            文書URL：[[url]]',
                'user_created' => 1,
                'is_system' => MailTemplate::IS_SYSTEM,
            ],
            [
                'code' => MailTemplate::B2C_EXPIRATION_DATE,
                'name' => '有効期間期日（訪問介護/訪問看護）',
                'subject' => '[[document_object_type]]の有効期間期日です',
                'body' => '[[office_name]]訪問介護事業所<br>
                            [[user_service_name]]様の[[document_object_type]]の有効期間期日です。<br><br>
                            新しい[[document_object_type]]を確認し格納を進めて下さいますようお願い致します。<br><br>
                            文書URL：[[url]]',
                'user_created' => 1,
                'is_system' => MailTemplate::IS_SYSTEM,
            ],
            [
                'code' => MailTemplate::B2C_AFTER_1_MONTH,
                'name' => '有効期間超過（訪問介護/訪問看護）',
                'subject' => '[[document_object_type]]の有効期間が過ぎております',
                'body' => '[[office_name]]訪問介護事業所<br>
                            [[user_service_name]]様の[[document_object_type]]の有効期間を過ぎております。<br><br>
                            新しい[[document_object_type]]を確認し格納を進めて下さいますようお願い致します。<br><br>
                            文書URL：[[url]]',
                'user_created' => 1,
                'is_system' => MailTemplate::IS_SYSTEM,
            ],
            [
                'code' => MailTemplate::IMPORT_H2,
                'name' => 'システムデータ取り込み完了',
                'subject' => '[[YYYY/MM]]　システムデータ取り込み完了しました',
                'body' => '[[YYYY/MM]]　システムデータ取り込み完了しました<br>
                            データ更新完了しました。
                ',
                'user_created' => 1,
                'is_system' => MailTemplate::IS_SYSTEM,
            ],
        ];
        
        foreach ($mailTemplates as $mailTemplate) {
            MailTemplate::create($mailTemplate);
        }

    }
}
