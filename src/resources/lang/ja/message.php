<?php

return [
    'unauthorized' => '権限がありません',
    'login_failure' => 'IDまたはパスワードが違います',
    'not_found' => 'generic not found',
    'bad_request' => [ // for http 400
    ],
    'employee_id_not_exist' => '指定したIDが存在していません',
    'server_error' => 'サーバーエラーもう一度やり直してください',
    'unauthenticated' => 'セッションタイムアウトが発生しました。再度ログインしてください',
    'status_disable' => 'IDまたはパスワードが違います',
    'regex' => 'The :attribute not include <, >, ", \' ',
    'folder_not_found' => '指定したフォルダーが存在していません',
    'document_not_found' => '対象の文書が存在していません',
    'tags' => [
        'name' => [
            'regex' => 'The name not include <, >, ", \''
        ],
        'total' => 'The number of tags not be greater than 10 tags',
    ],
    'data_invalid' => 'ファイルのデータが不正です',
    'validateDateDocument' => [
        'date' => '終了日には開始日以降の日付を指定してください',
        'executionDate' => '締結日には有効期間の終了日以前の日付を指定してください',
    ],
    'nothing_to_delete' => '削除したいものを選択してください',
    'delete_completed' => 'Delete completed',
    'current_password' => 'パスワードが違います',
    'imports' => [
        'file_empty' => 'ファイルのフォーマットが正しくありません',
        'max_row_import' => ':max_row行以下のファイルを指定してください',
        'min_column_import' => '項目数が足りていません',
        'import_success' => 'ファイルがインポートされました',
        'data_invalid' => 'ファイルのデータが不正です',
        'permission_edit_account' => 'システム管理者の情報が上書きできません',
        'columns_invalid' => 'カラム数が足りていません',
        'branches' => [
            'name' => [
                'required_with_all' => '支社名が必要です',
                'max' => '支社名は:max文字以下で指定してください',
                'required' => '支社名が必要です'
            ],
            'code' => [
                'required_with_all' => '支社コードが必要です',
                'max' => '支社コードは:max文字以下で指定してください',
                'required' => '支社コードが必要です'
            ],
        ],
        'divisions' => [
            'name' => [
                'required_with_all' => '事業部名が必要です',
                'max' => '事業部名は:max文字以下で指定してください',
                'required' => '事業部名が必要です'
            ],
            'code' => [
                'required_with_all' => '事業部コードが必要です',
                'max' => '事業部コードは:max文字以下で指定してください',
                'required' => '事業部コードが必要です'
            ],
        ],
        'offices' => [
            'name' => [
                'required_with_all' => '拠点名が必要です',
                'max' => '拠点名は:max文字以下で指定してください',
                'required' => '拠点名が必要です'
            ],
            'code' => [
                'required_with_all' => '拠点コードが必要です',
                'max' => '拠点コードは:max文字以下で指定してください',
                'required' => '拠点コードが必要です'
            ],
            'email' => [
                'required_with_all' => '拠点アドレスが必要です',
                'email' => '拠点アドレスのフォーマットが正しくありません',
                'max' => '拠点アドレス:max文字以下で指定してください',
                'required' => '拠点アドレスが必要です'
            ]
        ],
        'positions' => [
            'name' => [
                'required_with' => '役職が必要です',
                'max' => '役職は:max文字以下で指定してください.',
                'required' => '役職が必要です'
            ],
            'code' => [
                'required_with' => '役職コードが必要です',
                'max' => '役職コードは:max文字以下で指定してください',
                'required' => '役職コードが必要です',
                'exists' => '役職コードは正しくありません。'
            ],
        ],
        'users' => [
            'employee_id' => [
                'required' => '従業員IDを入力してください',
                'max' => '従業員IDは:max文字で指定してください',
                'unique' => 'この従業員IDは既に存在しています',
                'regex' => '従業員IDは半角英数字で指定してください',
            ],
            'name' => [
                'required' => 'ユーザ名を入力してください',
                'max' => 'ユーザ名は:max文字以下で指定してください',
            ],
            "email" => [
                'required' => 'メールアドレスを入力してください',
                'max' => 'メールアドレスは:max文字以下で指定してください',
                'email' => 'メールアドレスのフォーマットが正しくありません',
                'unique' => 'このメールアドレスは既に存在しています',
            ],
        ],
        'profiles' => [
            'katakana_name' => [
                'max' => '従業員名（カナ）は:max文字以下で指定してください',
            ],
            'full_name' => [
                'max' => '従業員名は:max文字以下で指定してください'
            ],
            'phone' => [
                'max' => '電話番号は9～:max文字で指定してください',
                'numeric' => '電話番号は半角数字で指定してください',
            ],
        ]
    ],
    'delete_folder_failure' => 'このフォルダーが削除できません',
    'delete_document_failure' => '文書が削除できません',
    'delete_file_failure' => 'ファイルが削除できません',
    'preview_document_failure' => 'このファイルがプレビューできません',
    'restore_file_failure' => 'Restore file failure',
    'service_user' => [
        'required_if' => 'ご利用者IDを入力してください',
        'code' => [
            'required' => 'ご利用者IDを入力してください',
            'max' => 'ご利用者IDは:max文字以下で入力してください',
            'unique' => 'ご利用者IDの値は既に存在しています',
        ],
        'name' => [
            'required' => 'ご利用者名を入力してください',
            'max' => 'ご利用者名は:max文字以下で入力してください',
        ],
        'unauthorized_delete_service_user' => 'ご利用者に関する文書があるため、ご利用者が削除できません',
        'import_service_user' => ':filenameのフォーマットが正しくありません',
        'hiragicode_not_found' => 'ひいらぎコードが存在していません',
        'max_file_upload' => 'アップロードファイルは最大:maxfileファイルです。',
        'import_format_date_error' => ':fieldの値のフォーマットが正しくありません',
        'import_office_code_error' => ':fieldの値が存在していません',
        'import_document_type_error' => ':fieldの値で利用サービスが検知できません',
        'file_upload_invalid' => ':filenameファイル拡張子が正しくありません',
        'import_data_empty' => ':attributeファイルのフォーマットが正しくありません'
    ],
    'users' => [
        'employee_id' => [
            'required' => '従業員IDを入力してください',
            'max' => 'ご利用者IDは:max文字以下で入力してください',
            'unique' => 'この従業員IDは既に存在しています',
            'regex' => '従業員IDは半角英数字で指定してください',
        ],
        'name' => [
            'required' => 'ユーザ名を入力してください',
            'max' => 'ユーザ名は:max文字以下で指定してください',
        ],
        "email" => [
            'required' => 'メールアドレスを入力してください',
            'max' => 'メールアドレスは:max文字以下で指定してください',
            'email' => 'メールアドレスのフォーマットが正しくありません',
            'unique' => 'このメールアドレスは既に存在しています',
        ],
        'role_code' => [
            'required' => 'The role field is required.',
            'max' => 'The role may not be greater than :max characters.',
            'exists' => 'The selected role is invalid.'
        ],
        'role_id' => [
            'required' => 'The role field is required.',
        ],
        'office_id' => [
            'required' => '拠点を指定してください',
        ],
        'branch_id' => [
            'required' => '支社を指定してください',
        ],
        'division_id' => [
            'required' => '事業部を指定してください',
        ],
        'position_id' => [
            'required' => '役職を指定してください',
        ],
        'store_id' => [
            'required' => '事業所・店舗を指定してください',
        ],
    ],
    'partner_name' => [
        'required_if' => 'The partner name field is required when document type is 締結済契約.'
    ],
    'set_permission_document_failure' => 'Can\'t set up this permission for document',
    'login' => [
        'ID' => [
            'required' => 'IDを入力してください',
            'max' => 'IDは:max文字以下で入力してください'
        ],
        'password' => [
            'required' => 'パスワードを入力してください',
            'between' => 'パスワードは:min～:max文字以内で入力してください',
        ]
    ],
    'profiles' => [
        'katakana_name' => [
            'max' => '従業員名（カナ）は:max文字以下で指定してください',
        ],
        'full_name' => [
            'max' => '従業員名は:max文字以下で指定してください'
        ],
        'phone' => [
            'digits_between' => '電話番号は:min～:max文字で指定してください',
            'numeric' => '電話番号は半角数字で指定してください',
        ],
        'name' => [
            'required' => 'アカウント名を入力してください',
            'max' => 'アカウント名は:max文字以下で指定してください',
        ],
        'email' => [
            'required' => 'メールアドレスを入力してください',
            'email' => 'メールアドレスのフォーマットが正しくありません',
            'max' => 'メールアドレスは:max文字以下で指定してください',
        ],
        'password' => [
            'required_with' => 'パスワードを入力してください',
        ],
        'new_password' => [
            'required_with' => 'パスワードを入力してください',
            'min' => 'パスワードは:min文字以上で指定してください',
            'max' => 'パスワードは:max文字以下で指定してください'
        ],
        'password_confirmation' => [
            'required_with' => 'パスワードを入力してください',
            'same' => '新しいパスワードと一致しません'
        ],
        'avatar' => [
            'max' => '1MB以下のファイルを指定してください',
            'mimes' => '画像のファイルを選択してください',
        ]
    ],
    'folder' => [
        'name' => [
            'required' => 'フォルダー名を入力してください',
            'unique' => 'フォルダー名が既に存在しています',
            'max' => 'フォルダー名が:max文字以下で指定してください',
        ]
    ],
    'document' => [
        'name' => [
            'required' => '文書名を入力してください',
            'max' => '文書名は:max文字以下で指定してください'
        ],
        'document_type' => [
            'required' => '文書種別を選択してください	'
        ],
        'document' => [
            'required' => '文書種別を選択してください'
        ],
        'files_info' => [
            'required' => 'ファイルを選択してください',
            'max' => 'ファイルは5MB以下で指定してください',
            'mimes' => '画像ファイル、PDFファイル、Wordファイルを選択してください',
        ],
        'branch' => [
            'required' => '支社を指定してください'
        ],
        'division' => [
            'required' => '事業部を指定してください'
        ],
        'office' => [
            'required' => '拠点を指定してください'
        ],
        'document_object' => [
            'required_if' => '対象文書を選択してください',
        ]
    ],
    'reset_password' => [
        'confirmed' => 'パスワードが一致していません'
    ],
    'template' => [
        'name' => [
            'required' => 'テンプレート名を入力してください',
            'max' => 'テンプレート名は:max文字以下で入力してください	',
        ],
        'subject' => [
            'required' => 'タイトルを入力してください',
            'max' => 'タイトルは:max文字以下で入力してください',
        ],
        'body' => [
            'required' => '内容を入力してください',
            'max' => '内容は:max文字以下で入力してください',
        ],
        'cant_delete' => '指定したテンプレートが使用されているため、削除できません',
    ],
    'files' => [
        'mimes' => '画像ファイル、excelファイル、wordファイルを選択してください',
        'sum_uploaded_file_size' => '各ファイルの合計サイズが50MB以下で指定してください',
        'max_file_upload' => 'ファイルは30個以下に選択してください'
    ],
    'password' => [
        'user_id' => [
            'required' => 'IDを入力してください',
        ],
        'password' => [
            'required' => 'パスワードを入力してください',
            'confirmed' => 'パスワードが一致していません',
        ],

    ],
    'can_not_create_document' => '対象のファイルセットに文書が登録できません',
    'can_not_find_document' => 'can not find document',
    'service_user_not_exist' => '指定したご利用者が存在しないため、文書が登録できません',
    'update_document_failure' => '文書が更新できませんでした',
    'restore_user_failure' => 'ユーザが復元できませんでした',
    'fileset' => [
        'duplicated' => '指定した対象文書は既に存在しています',
        'is_full' => 'The fileset is full objects'
    ],
    'export' => [
        'line' => '行',
        'content' => '内容が正しくありません',
        'title_office' => '拠点',
        'title_service_user' => '利用者'
    ]
];
