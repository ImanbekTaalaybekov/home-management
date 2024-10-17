<?php
namespace App\Enums;

use App\Models\KnowledgeArticle;
use App\Models\KnowledgeContact;
use App\Models\KnowledgeDocument;
use App\Traits\EnumTrait;

enum KnowledgeBaseObject: string
{
    use EnumTrait;


    case ARTICLE= 'article';
    case CONTACT = 'contact';
    case DOCUMENT = 'document';

    public function objects(): string
    {
        return match ($this) {
            self::ARTICLE => KnowledgeArticle::class,
            self::CONTACT => KnowledgeContact::class,
            self::DOCUMENT => KnowledgeDocument::class,
        };
    }

    public static function fromClassName(string $className): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->objects() === $className) {
                return $case;
            }
        }
        return null;
    }

    public function title(string $lang = null): string
    {
        $value = match ($this) {
            self::ARTICLE => 'статья',
            self::CONTACT => 'контакт',
            self::DOCUMENT=> 'документ',
        };

        return __($value, locale: $lang);
    }
}
