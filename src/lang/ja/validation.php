<?php
return [
    'required' => ':attributeを入力してください',
    'email' => 'メールアドレスを入力してください',
    'attributes' => [
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'name' => 'お名前',
    ],
    'custom' => [
        'password' => [
            'min' => 'パスワードは:min文字以上で入力してください',
            'confirmed' => 'パスワードと一致しません',
        ],
    ],
];
