<?php

declare(strict_types=1);

namespace App\Tests\DataProvider;

use App\Tests\Helper\EmailAddressTestHelper;

/**
 * メールアドレスのデータプロバイダー
 */
final class EmailAddressProvider
{
    /**
     * 有効なメールアドレスのデータプロバイダー
     *
     * @return array <string, array{string}>
     */
    public static function validValues(): array
    {
        return [
            'standard_format'          => [EmailAddressTestHelper::TEST_VALUE],
            'numeric_local_part'       => ['123@example.com'],
            'numeric_domain'           => ['test@123.com'],
            'dot_in_local_part'        => ['test.dot@example.com'],
            'hyphen_in_local_part'     => ['test-hyphen@example.com'],
            'underscore_in_local_part' => ['test_underscore@example.com'],
            'plus_in_local_part'       => ['test+plus@example.com'],
            'hyphen_in_domain'         => ['test@example-hyphen.com'],
            'shortest_valid_form'      => ['x@x.x'],
            'max_length_edge_case'     => [EmailAddressTestHelper::validMaxLengthValue()],

            // RFC-compliant addresses that pass the current validation.
            'percent_in_local_part'           => ['test%percent@example.com'],
            'double_dot_in_quoted_local_part' => ['"test..dot"@example.com'],
        ];
    }

    /**
     * フォーマットが無効なメールアドレスのデータプロバイダー
     *
     * @return array <string, array{string}>
     */
    public static function invalidFormatValues(): array
    {
        return [
            'missing_local_part'         => ['@example.com'],
            'missing_domain'             => ['test'],
            'missing_domain_part'        => ['test@'],
            'missing_top_level_domain'   => ['test@example'],
            'leading_dot_in_local_part'  => ['.test@example.com'],
            'trailing_dot_in_local_part' => ['test.@example.com'],
            'double_dot_in_local_part'   => ['test..dot@example.com'],
            'double_at'                  => ['test@@example.com'],
            'leading_dot_in_domain'      => ['test@.example.com'],
            'double_dot_in_domain'       => ['test@example..com'],
            'leading_hyphen_in_domain'   => ['test@-example.com'],
            'trailing_hyphen_in_domain'  => ['test@example-.com'],
            'local_part_too_long'        => [EmailAddressTestHelper::tooLongLocalPartValue()],
            'domain_label_too_long'      => [EmailAddressTestHelper::tooLongDomainLabelValue()],
            'top_level_domain_too_long'  => [EmailAddressTestHelper::tooLongTopLevelDomainValue()],
            'max_length_invalid'         => [EmailAddressTestHelper::tooLongFormatValue()],

            // RFC-compliant addresses that are rejected by the current validation.
            'numeric_top_level_domain'   => ['test@example.123'],
            'space_in_quoted_local_part' => ['"test space"@example.com'],
        ];
    }
}
