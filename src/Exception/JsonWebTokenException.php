<?php

declare(strict_types=1);

namespace App\Exception;

use Throwable;

use function sprintf;

/**
 * Class JsonWebTokenException
 * @package App\Exception
 *
 * @author Kamal Chahid <kchahid_@outlook.com>
 */
final class JsonWebTokenException extends ApplicationException
{
    protected const HTTP_CODE = 4030;
    protected const DEFAULT = 0;
    public const JWT = 1;
    public const ISSUER = 2;
    public const SUBJECT = 3;
    public const ISSUE_DATE = 4;
    public const AUDIENCE = 5;
    public const SECRET = 6;
    public const JTI = 7;
    public const EXP = 8;
    public const NBF = 9;

    /**
     * @var array<int,string>
     */
    protected static array $messageMap = [
        self::DEFAULT => 'Unexcpected error',
        self::JWT => 'JWT is invalid/missing',
        self::ISSUER => 'Issuer is invalid/missing',
        self::SUBJECT => 'Subject is invalid/missing',
        self::ISSUE_DATE => 'Iat is invalid/missing',
        self::AUDIENCE => 'Audience is invalid/missing',
        self::SECRET => 'Secret is invalid/missing',
        self::JTI => 'JWT already used',
        self::EXP => 'JWT expiration date is not valid',
        self::NBF => 'JWT is not yet valid'
    ];

    public function __construct(int $code, ?Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Request is blocked. %s.', self::$messageMap[$code] ?? self::$messageMap[self::DEFAULT]),
            self::HTTP_CODE + $code,
            $previous
        );
    }
}
