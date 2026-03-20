<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attributeを承認してください。',
    'accepted_if' => ':otherが:valueの場合、:attributeを承認してください。',
    'active_url' => ':attributeは有効なURLではありません。',
    'after' => ':attributeには:date以降の日付を指定してください。',
    'after_or_equal' => ':attributeには:date以降の日付を指定してください。',
    'alpha' => ':attributeは英字のみで入力してください。',
    'alpha_dash' => ':attributeは英数字・ハイフン・アンダースコアのみで入力してください。',
    'alpha_num' => ':attributeは英数字のみで入力してください。',
    'array' => ':attributeは配列でなければなりません。',
    'before' => ':attributeには:dateより前の日付を指定してください。',
    'before_or_equal' => ':attributeには:date以前の日付を指定してください。',
    'between' => [
        'numeric' => ':attributeは:minから:maxの間で指定してください。',
        'file' => ':attributeは:minから:maxキロバイトの間で指定してください。',
        'string' => ':attributeは:min文字から:max文字の間で入力してください。',
        'array' => ':attributeは:min個から:max個の間で指定してください。',
    ],
    'boolean' => ':attributeはtrueかfalseで指定してください。',
    'confirmed' => ':attributeの確認が一致しません。',
    'current_password' => 'パスワードが正しくありません。',
    'date' => ':attributeは有効な日付ではありません。',
    'date_equals' => ':attributeは:dateと等しい日付を指定してください。',
    'date_format' => ':attributeは:format形式で入力してください。',
    'different' => ':attributeと:otherは異なる値を指定してください。',
    'digits' => ':attributeは:digits桁で入力してください。',
    'digits_between' => ':attributeは:min桁から:max桁の間で入力してください。',
    'dimensions' => ':attributeの画像サイズが無効です。',
    'distinct' => ':attributeに重複した値があります。',
    'email' => ':attributeは有効なメールアドレス形式で入力してください。',
    'ends_with' => ':attributeは:valuesのいずれかで終わる必要があります。',
    'enum' => '選択した:attributeは無効です。',
    'exists' => '選択した:attributeは無効です。',
    'file' => ':attributeはファイルでなければなりません。',
    'filled' => ':attributeは必須です。',
    'gt' => [
        'numeric' => ':attributeは:valueより大きい値を指定してください。',
        'file' => ':attributeは:valueキロバイトより大きいファイルを指定してください。',
        'string' => ':attributeは:value文字より多く入力してください。',
        'array' => ':attributeは:value個より多く指定してください。',
    ],
    'gte' => [
        'numeric' => ':attributeは:value以上の値を指定してください。',
        'file' => ':attributeは:valueキロバイト以上のファイルを指定してください。',
        'string' => ':attributeは:value文字以上で入力してください。',
        'array' => ':attributeは:value個以上指定してください。',
    ],
    'image' => ':attributeは画像ファイルを指定してください。',
    'in' => '選択した:attributeは無効です。',
    'in_array' => ':attributeは:otherに存在しません。',
    'integer' => ':attributeは整数で指定してください。',
    'ip' => ':attributeは有効なIPアドレスを指定してください。',
    'ipv4' => ':attributeは有効なIPv4アドレスを指定してください。',
    'ipv6' => ':attributeは有効なIPv6アドレスを指定してください。',
    'json' => ':attributeは有効なJSON文字列でなければなりません。',
    'lt' => [
        'numeric' => ':attributeは:valueより小さい値を指定してください。',
        'file' => ':attributeは:valueキロバイトより小さいファイルを指定してください。',
        'string' => ':attributeは:value文字より少なく入力してください。',
        'array' => ':attributeは:value個より少なく指定してください。',
    ],
    'lte' => [
        'numeric' => ':attributeは:value以下の値を指定してください。',
        'file' => ':attributeは:valueキロバイト以下のファイルを指定してください。',
        'string' => ':attributeは:value文字以下で入力してください。',
        'array' => ':attributeは:value個以下で指定してください。',
    ],
    'mac_address' => ':attributeは有効なMACアドレスを指定してください。',
    'max' => [
        'numeric' => ':attributeは:max以下の値を指定してください。',
        'file' => ':attributeは:maxキロバイト以下のファイルを指定してください。',
        'string' => ':attributeは:max文字以下で入力してください。',
        'array' => ':attributeは:max個以下で指定してください。',
    ],
    'mimes' => ':attributeは:values形式のファイルを指定してください。',
    'mimetypes' => ':attributeは:values形式のファイルを指定してください。',
    'min' => [
        'numeric' => ':attributeは:min以上の値を指定してください。',
        'file' => ':attributeは:minキロバイト以上のファイルを指定してください。',
        'string' => ':attributeは:min文字以上で入力してください。',
        'array' => ':attributeは:min個以上指定してください。',
    ],
    'multiple_of' => ':attributeは:valueの倍数で指定してください。',
    'not_in' => '選択した:attributeは無効です。',
    'not_regex' => ':attributeの形式が無効です。',
    'numeric' => ':attributeは数値で指定してください。',
    'password' => 'パスワードが正しくありません。',
    'present' => ':attributeが存在している必要があります。',
    'prohibited' => ':attributeは禁止されています。',
    'prohibited_if' => ':otherが:valueの場合、:attributeは禁止されています。',
    'prohibited_unless' => ':otherが:valuesに含まれない限り、:attributeは禁止されています。',
    'prohibits' => ':attributeが存在する場合、:otherは禁止されています。',
    'regex' => ':attributeの形式が無効です。',
    'required' => ':attributeを入力してください。',
    'required_array_keys' => ':attributeには:valuesのエントリが必要です。',
    'required_if' => ':otherが:valueの場合、:attributeを入力してください。',
    'required_unless' => ':otherが:valuesでない場合、:attributeを入力してください。',
    'required_with' => ':valuesが存在する場合、:attributeを入力してください。',
    'required_with_all' => ':valuesが存在する場合、:attributeを入力してください。',
    'required_without' => ':valuesが存在しない場合、:attributeを入力してください。',
    'required_without_all' => ':valuesが存在しない場合、:attributeを入力してください。',
    'same' => ':attributeと:otherが一致しません。',
    'size' => [
        'numeric' => ':attributeは:sizeを指定してください。',
        'file' => ':attributeは:sizeキロバイトのファイルを指定してください。',
        'string' => ':attributeは:size文字で入力してください。',
        'array' => ':attributeは:size個指定してください。',
    ],
    'starts_with' => ':attributeは:valuesのいずれかで始まる必要があります。',
    'string' => ':attributeは文字列で指定してください。',
    'timezone' => ':attributeは有効なタイムゾーンを指定してください。',
    'unique' => ':attributeは既に使用されています。',
    'uploaded' => ':attributeのアップロードに失敗しました。',
    'url' => ':attributeは有効なURLを指定してください。',
    'uuid' => ':attributeは有効なUUIDを指定してください。',

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    'attributes' => [
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'name' => '名前',
        'password_confirmation' => 'パスワード（確認）',
    ],

];
