<?php

return [
    'unauthorized'         => 'You do not have permission.',
    'login_failure'        => 'The ID or password is incorrect.',
    'not_found'            => 'generic not found',
    'bad_request' => [ // for http 400
    ],
    'server_error'  => 'Server error. Please try again',
    'unauthenticated' => 'Unauthenticated',
    'regex' => 'The :attribute not include <, >, ", \' ',
    'tags' => [
        'name' => [
            'regex' => 'The name not include <, >, ", \' '
        ]
    ],
    'nothing_to_delete' => 'Select the records you want to delete',
    'delete_completed' => 'Delete completed',
    'current_password' => 'Current password does not match',
    'imports' => [
        'file_empty' => 'File is\'n empty',
        'max_row_import' => 'The number of imported lines must not exceed :max_row',
        'data_invalid' => 'The given data was invalid.',
        'columns_invalid' => 'Invalid column number',
        'branches' => [
            'name' => [
                'required_with_all' => 'The branch name field is required when branch code/organization name/organization code are present.',
                'max' => 'The branch name may not be greater than :max characters.'
            ],
            'code' => [
                'required_with_all' => 'The branch code field is required when branch name/organization name/organization code are present.',
                'max' => 'The branch code may not be greater than :max characters.'
            ],
        ],
        'organizations' => [
            'name' => [
                'required_with_all' => 'The organization name field is required when branch code/branch name/organization code are present.',
                'max' => 'The organization name may not be greater than :max characters.'
            ],
            'code' => [
                'required_with_all' => 'The organization code field is required when branch name/branch code/organization name are present.',
                'max' => 'The organization code may not be greater than :max characters.'
            ],
        ],
        'positions' => [
            'name' => [
                'required_with' => 'The position name field is required when position code is present.',
                'max' => 'The position name may not be greater than :max characters.'
            ],
            'code' => [
                'required_with' => 'The position code field is required when position name is present.',
                'max' => 'The position code may not be greater than :max characters.'
            ],
        ],
        'users' => [
            'employee_id' => [
                'required' => 'The employee ID field is required.',
                'max' => 'The employee ID may not be greater than :max characters.'
            ],
            'name' => [
                'required' => 'The user name field is required.',
                'max' => 'The user name may not be greater than :max characters.'
            ],
            "email" => [
                'required' => 'The email field is required.',
                'max' => 'The email may not be greater than :max characters.',
                'email' => 'The email must be a valid email address.',
            ],
            'role_code' => [
                'required' => 'The role field is required.',
                'max' => 'The role may not be greater than :max characters.',
                'exists' => 'The selected role is invalid.'
            ],
        ],
        'profiles' => [
            'last_name' => [
                'max' => 'The last_name may not be greater than :max characters.'
            ],
            'first_name' => [
                'max' => 'The first_name may not be greater than :max characters.'
            ],
            'phone' => [
                'max' => 'The phone may not be greater than :max characters.'
            ]
        ]
    ],
    'delete_folder_failure' => 'Can\'t delete this folder',
    'delete_document_failure' => 'You don\'t have permission to delete document',
    'delete_file_failure' => 'You don\'t have permission to delete file',
    'preview_document_failure' => 'This file can\'t preview',
    'service_user' => [
        'required_if' => 'The service user field is required when document type is in 訪問介護, 訪問看護.'
    ],
    'partner_name' => [
        'required_if' => 'The partner name field is required when document type is 締結済契約.'
    ],
    'set_permission_document_failure' => 'Can\'t set up this permission for document',
    'login' => [
        'ID' => [
            'required' => 'IDを入力してください。',
            'max' => 'The :attribute may not be greater than :max characters.'
        ],
        'password' => [
            'required' => 'パスワードを入力してください。',
            'min' => 'The :attribute must be at least :min characters.',
            'max' => 'The :attribute may not be greater than :max characters.'
        ]
    ],
    'reset_password' => [
        'confirmed' => 'パスワードが一致していません。'
    ],
    'files' => [
        'mimes' => 'The :attribute must be a file of type: pdf, docx, doc, png, jpeg, jpg.',
        'sum_uploaded_file_size' => 'The size of all files are not be greater than 50MB.',
        'max_file_upload' => 'The number of file not be greater than 30 file'
    ],
    'can_not_create_document' => 'Can\'t register document in target fileset',
    'update_document_failure' => 'The document could not be update',
    'restore_user_failure' => 'The user could not be restore',
    'fileset' => [
        'duplicated' => 'This object has been selected',
        'is_full' => 'The fileset is full objects'
    ]
];
